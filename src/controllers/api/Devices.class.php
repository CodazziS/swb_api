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
			}
			$device->model = $this->data['model'];
			$device->last_sync = 0;
			$device->save();
			
			$this->error = 0;
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
		
	}

}