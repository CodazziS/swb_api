<?php
class ApiContacts extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Contacts';
		$this->view			= "api/doc.html";
	}
	
	public function add () {
		$this->error = -1;
		

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('contacts', 'key', 'android_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			Contact::delete_all(array('conditions' => array('user_id = ? and android_id = ? ', $this->user_id, $this->data['android_id'])));
			
			$contacts = json_decode($this->data['contacts']);
			foreach ($contacts as $contact) {
				
				$address = $contact->address;
				$format_address = $this->addons['Crypto']->formatPhoneNumber($address);
				$c_address = $this->addons['Crypto']->encrypt($address, $this->data['key']);
				$c_format_address = $this->addons['Crypto']->encrypt($format_address, $this->data['key']);

				$contact_bdd = new Contact();
				$contact_bdd->user_id = $this->user_id;
				$contact_bdd->format_address = $c_format_address;
				$contact_bdd->android_id = $this->data['android_id'];

				$contact_bdd->address = $c_address;
				$contact_bdd->name = $this->addons['Crypto']->encrypt($contact->name, $this->data['key']);
				if (isset($contact->image)) {
					$contact_bdd->image = $contact->image;
				}
				$contact_bdd->save();
			}
			$this->error = 0;
		}
	}
	
	private function getContactInfos($address, $device) {
		$opt = array(
			'select' => 'name, address',
			'conditions' => array('user_id = ? AND android_id = ? AND format_address = ?', $this->user_id, $device, $address), 
		);
		$contact = Contact::find('first', $opt);
		$result = array('name' => '', 'address' => '');
		if (empty($contact)) {
			$opt = array(
				'select' => 'address',
				'conditions' => array('user_id = ? AND device = ? AND format_address = ?', $this->user_id, $device, $address), 
			);
			$mess = Message::find('first', $opt);
			$result['name'] 	= $this->addons['Crypto']->decrypt($mess->address, $this->data['key']);
			$result['address']	= $this->addons['Crypto']->decrypt($mess->address, $this->data['key']);
		} else {
			$result['name'] 	= $this->addons['Crypto']->decrypt($contact->name, $this->data['key']);
			$result['address']	= $this->addons['Crypto']->decrypt($contact->address, $this->data['key']);
		}
		return $result;
	}
	
	public function getcontacts() {
		$this->error = -1;
		
		require_once ('Devices.class.php');

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$opt = array(
				'conditions' => array('user_id = ?', $this->user_id), 
				'order' => 'android_id ASC, name ASC'
			);
			$address = Contact::find('all', $opt);

			$addr_arr = array();
			foreach ($address as $addr) {
				$addr_cur = array();
				$addr_cur['android_id'] = $addr->android_id;
				$addr_cur['address'] = $this->addons['Crypto']->decrypt($addr->address, $this->data['key']);
				$addr_cur['format_address'] = $this->addons['Crypto']->decrypt($addr->format_address, $this->data['key']);
				$addr_cur['model'] = \ApiDevices::getDeviceName($this->user_id, $addr->android_id);
				$addr_cur['name'] = $this->addons['Crypto']->decrypt($addr->name, $this->data['key']);
				$addr_arr[] = $addr_cur;
			} 
			$this->result['address'] = $addr_arr;
			$this->error = 0;
		}
	}
	
	public function getactive () {
		$this->error = -1;
		
		$class_file = 'Devices.class.php';
		require ($class_file);

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$opt = array(
				'select' => 'format_address, device, MAX(date_message) as date_message, SUM(unread) as unread',
				'conditions' => array('user_id = ?', $this->user_id), 
				'order' => 'date_message desc',
				'group' => 'device, format_address',
			);
			$address = Message::find('all', $opt);

			$addr_arr = array();
			foreach ($address as $addr) {
				$contact_infos = $this->getContactInfos($addr->format_address, $addr->device);
				$addr_cur = array();
				$addr_cur['time'] = $addr->date_message;
				$addr_cur['android_id'] = $addr->device;
				$addr_cur['unread'] = $addr->unread;
				$addr_cur['address'] = $contact_infos['address'];
				$addr_cur['format_address'] = $this->addons['Crypto']->decrypt($addr->format_address, $this->data['key']);
				$addr_cur['model'] = ApiDevices::getDeviceName($this->user_id, $addr->device);
				$addr_cur['name'] = $contact_infos['name'];
				$addr_arr[] = $addr_cur;
			} 
			$this->result['address'] = $addr_arr;
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
			'description' => 'Add a contact list or replace existing contacts (by phone number)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Contacts (json string)',
				'Android_id (string)',
				'Key (string)'
				
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
		/* GetActive */
		$this->result['docs'][] = array(
			'name' => 'GetActive',
			'type' => 'GET',
			'description' => 'Get the active contacts of the current user (who have messages)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (string)'
			),
			'results' => array(
				'Contacts (array)', 
				'Error (interger)'
			)
		);
		
	}

}