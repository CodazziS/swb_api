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
		$this->result['Test1'] = 'ok';
		if ($this->addons['Apy']->check($this, $conditions)) {
			$this->result['Test2'] = 'ok';
			$opt = array(
				'conditions' => array('android_id = ? AND user_id = ?', $this->data['android_id'], $this->user_id)
			);
			$device = Device::find('first', $opt);
			if (!isset($device) || $device == null) {
				$device = new Device();
				$device->user_id = $this->user_id;
				$device->android_id = $this->data['android_id'];
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
				'conditions' => array('user_id = ?', $this->user_id)
			);
			$devices = Device::find('all', $opt);
			$devices_arr = array();
			foreach ($devices as $device) {
				$current_device = array();
				$current_device['model'] = $device->model;
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
	
	static public function getDeviceName($user_id, $android_id) {
		$opt = array(
			'select' => 'model',
			'conditions' => array('user_id = ? AND android_id = ?', $user_id, $android_id), 
		);
		$device = Device::find('first', $opt);
		if (empty($device)) {
			return $android_id;
		} else {
			return $device->model;
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
		
	}

}