<?php

/**
 * Device management 
 * Revision management (linked to device)
 */ 
class ApiDevices extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Devices';
		$this->view			= "api/doc.html";
	}
	
	static function getOrAddDevice($user_id, $device_id, $model_id, $rev_name, $creation) {
	    $opt = array(
			'conditions' => array('device_id = ? AND user_id = ?', $device_id, $user_id)
		);
		$device = Device::find('first', $opt);
		if (empty($device) && $creation) {
			$device = new Device();
			$device->user_id = $user_id;
			$device->device_id = $device_id;
			$device->name = $model_id;
			$device->model = $model_id;
			$device->rev_name = $rev_name;
    		$device->save();
		}
		return $device;
	}
	
	/**
	 * @method POST
	 * @name add
	 * @description Add a user device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $model (string) : Device model
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function add () {
		$this->error = -1;

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('device_id', 'model')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
            ApiDevices::getOrAddDevice($this->user_id, $this->data['device_id'], $this->data['model'], true);
			$this->error = 0;
		}
	}
	
	/**
	 * @method GET
	 * @name getDevices
	 * @description Get user number unread messages
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $devices (Array) : User's devices array : [model, name, revision, device_id]
	 */
	public function getDevices () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {

			$opt = array(
				'conditions' => array('user_id = ?', $this->user_id),
			);
			$devices = Device::find('all', $opt);
			$devices_arr = array();
			foreach ($devices as $device) {
				$current_device = array();
				$current_device['model'] = $device->model;
				$current_device['name'] = $device->name;
				$current_device['revision'] = $device->revision;
				$current_device['device_id'] = $device->device_id;

				if ($device->last_sync != 0) {
					$current_device['last_sync'] = date("d/m/Y G:i", $device->last_sync);
				} else {
					'-';
				}
				$devices_arr[] = $current_device;
			}
			
			$this->result['devices'] = $devices_arr;
			$this->error = 0;
		}
	}
	
	/**
	 * @method POST
	 * @name changeName
	 * @description Change name of device. Default name is the device model
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $new_name (string) : New device name
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function changeName () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('device_id', 'new_name')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {

			$opt = array(
				'conditions' => array('user_id = ? AND device_id = ?', $this->user_id, $this->data['device_id'])
			);
			$device = Device::find('first', $opt);
			if (empty($device)) {
				$this->error = 7;
			} else {
				$device->name = $this->data['new_name'];
				$device->save();
			}
			$this->error = 0;
		}
	}
	
	/**
	 * @method POST
	 * @name remove
	 * @description Remove a device and all his content (contacts, messages, queue)
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function remove () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {

			$opt = array(
				'conditions' => array('user_id = ? AND device_id = ?', $this->user_id, $this->data['device_id'])
			);
			$device = Device::find('first', $opt);
			if (empty($device)) {
				$this->error = 7;
			} else {
				Message::delete_all(array('conditions' => array(
					'user_id = ? and device_id = ? ', $this->user_id, $this->data['device_id'])
					));
				Contact::delete_all(array('conditions' => array(
					'user_id = ? and device_id = ? ', $this->user_id, $this->data['device_id'])
					));
				Queue::delete_all(array('conditions' => array(
					'user_id = ? and device_id = ? ', $this->user_id, $this->data['device_id'])
					));
				$device->delete();
			}
			$this->error = 0;
		}
	}
	
	static public function getDeviceName($user_id, $device_id) {
		$opt = array(
			'select' => 'name',
			'conditions' => array('user_id = ? AND device_id = ?', $user_id, $device_id), 
		);
		$device = Device::find('first', $opt);
		if (empty($device)) {
			return $device_id;
		} else {
			return $device->name;
		}
	}
	
	/**
	 * @method GET
	 * @name getRevisionId
	 * @description Get revision name and number of a device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $revision (string) : Revision number
	 * @return $rev_name (string) : Revision name
	 */
	public function getRevisionId () {
		$this->error = -1;

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
		    $device = $this->getOrAddDevice($this->user_id, $this->data['device_id'], "", "", false);
		    $this->result['revision'] = $device->revision;
		    $this->result['rev_name'] = $device->rev_name;
		    $this->error = 0;
		}
	}
	
	/**
	 * @method POST
	 * @name validRevision
	 * @description Valid a new revision for device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $revision (int) : Revision number
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $revision (string) : Revision number
	 */
	public function validRevision () {
		$this->error = -1;

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('device_id', 'revision')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
		    $device = $this->getOrAddDevice($this->user_id, $this->data['device_id'], "", "", false);
		    $device->revision = $this->data['revision'];
		    $device->last_sync = time();
		    $device->save();
		    $this->result['revision'] = $device->revision;
		    $this->error = 0;
		}
	}
	
	public function index() {
		$this->render_class = 'Render';
		$this->result = array('name' => $this->title, 'docs' => array());
		
		$reflector = new ReflectionClass(get_class($this));
        $this->result['class_description'] = $this->parseClass($reflector->getDocComment());
        $class_methods = get_class_methods(get_class($this));
        foreach ($class_methods as $method_name) {
            $doc = $reflector->getMethod($method_name)->getDocComment();
            if ($doc != null) {
                $this->result['docs'][] = $this->parseDoc($doc);
            }
        }
	}

}