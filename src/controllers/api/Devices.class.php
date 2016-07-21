<?php
class ApiDevices extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Devices';
		$this->view			= "api/doc.html";
	}
	
	public function add () {
		$this->error = -1;

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('device_id', 'model')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$opt = array(
				'conditions' => array('device_id = ? AND user_id = ?', $this->data['device_id'], $this->user_id)
			);
			$device = Device::find('first', $opt);
			if (!isset($device) || $device == null) {
				$device = new Device();
				$device->user_id = $this->user_id;
				$device->device_id = $this->data['device_id'];
				$device->name = $this->data['model'];
			}
			$device->model = $this->data['model'];
			$device->resync_date = time();
			$device->last_sync = 0;
			$device->save();
			
			$this->error = 0;
		}
	}
	
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
				'order' => 'last_sync desc'
			);
			$devices = Device::find('all', $opt);
			$devices_arr = array();
			foreach ($devices as $device) {
				$current_device = array();
				$current_device['model'] = $device->model;
				$current_device['name'] = $device->name;
				$current_device['resync_date'] = date("d/m/Y G:i", $device->resync_date);
				if ($device->last_sync != 0) {
					$current_device['last_sync'] = date("d/m/Y G:i", $device->last_sync);
				} else {
					'-';
				}
				
				$current_device['device_id'] = $device->device_id;
				$devices_arr[] = $current_device;
			}
			
			$this->result['devices'] = $devices_arr;
			$this->error = 0;
		}
	}
	
	public function changename () {
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
	
	public function index() {
		$this->render_class = 'Render';
		$this->result = array('name' => $this->title, 'docs' => array());
		
		/* Create */
		$this->result['docs'][] = array(
			'name' => 'Add',
			'type' => 'POST',
			'description' => 'Create a device with device_id. If the device exist, delete all information about the device (SMS/MMS/...)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Device_id (string)',
				'Model (string)',
				
			),
			'results' => array(
				'Created (boolean)',
				'Error (interger)'
			)
		);
		
		/* Create */
		$this->result['docs'][] = array(
			'name' => 'GetDevices',
			'type' => 'GET',
			'description' => 'Get Devices for the current account',
			'args' => array(
				'User (string)',
				'Token (string)'
				
			),
			'results' => array(
				'Devices (Array)',
				'Error (interger)'
			)
		);
		
		/* Change name */
		$this->result['docs'][] = array(
			'name' => 'ChangeName',
			'type' => 'POST',
			'description' => 'Change device name',
			'args' => array(
				'User (string)',
				'Token (string)',
				'New_name (string)',
				'Device_id (string)'
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
		/* Remove */
		$this->result['docs'][] = array(
			'name' => 'Remove',
			'type' => 'POST',
			'description' => 'Remove a device with all informations',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Device_id (string)'
			),
			'results' => array(
				'Error (interger)'
			)
		);
	}

}