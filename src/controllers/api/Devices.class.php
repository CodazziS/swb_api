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
			'fields' => array('android_id', 'model')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$opt = array(
				'conditions' => array('android_id = ? AND user_id = ?', $this->data['android_id'], $this->user_id)
			);
			$device = Device::find('first', $opt);
			if (!isset($device) || $device == null) {
				$device = new Device();
				$device->user_id = $this->user_id;
				$device->android_id = $this->data['android_id'];
				$device->name = $this->data['model'];
			}
			$device->model = $this->data['model'];
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
				if ($device->last_sync != 0) {
					$current_device['last_sync'] = date("d/m/Y G:i", $device->last_sync);
				} else {
					'-';
				}
				
				$current_device['android_id'] = $device->android_id;
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
			'fields' => array('android_id', 'new_name')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {

			$opt = array(
				'conditions' => array('user_id = ? AND android_id = ?', $this->user_id, $this->data['android_id'])
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
			'fields' => array('android_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {

			$opt = array(
				'conditions' => array('user_id = ? AND android_id = ?', $this->user_id, $this->data['android_id'])
			);
			$device = Device::find('first', $opt);
			if (empty($device)) {
				$this->error = 7;
			} else {
				Message::delete_all(array('conditions' => array(
					'user_id = ? and device = ? ', $this->user_id, $this->data['android_id'])
					));
				Contact::delete_all(array('conditions' => array(
					'user_id = ? and android_id = ? ', $this->user_id, $this->data['android_id'])
					));
				$device->delete();
			}
			$this->error = 0;
		}
	}
	
	static public function getDeviceName($user_id, $android_id) {
		$opt = array(
			'select' => 'name',
			'conditions' => array('user_id = ? AND android_id = ?', $user_id, $android_id), 
		);
		$device = Device::find('first', $opt);
		if (empty($device)) {
			return $android_id;
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
			'description' => 'Create a device with android_id. If the device exist, delete all information about the device (SMS/MMS/...)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Android_id (string)',
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
				'Android_id (string)'
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
				'Android_id (string)'
			),
			'results' => array(
				'Error (interger)'
			)
		);
	}

}