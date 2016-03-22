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
				/*
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ?', $c_format_address, $this->user_id)
				);
				$contact_bdd = Contact::find('first', $opt);
				if (!isset($contact_bdd) or $contact_bdd == null) {
				*/
					$contact_bdd = new Contact();
					$contact_bdd->user_id = $this->user_id;
					$contact_bdd->format_address = $c_format_address;
					$contact_bdd->android_id = $this->data['android_id'];
				//}
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
	
	public function getactive () {
		$this->error = -1;
		

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$opt = array(
				//'select' => 'format_address',
				'conditions' => array('user_id = ? AND last_message is not NULL', $this->user_id), 
				//'order' => 'last_message desc'
				//'group' => 'format_address',
			);
			$address = Contact::find('all', $opt);
			$addr_arr = array();
			foreach ($address as $addr) {
				$addr_cur = array();
				$addr_cur['time'] = $addr->last_message;
				$addr_cur['android_id'] = $addr->android_id;
				$addr_cur['address'] = $this->addons['Crypto']->decrypt($addr->format_address, $this->data['key']);
				$addr_cur['name'] = $this->addons['Crypto']->decrypt($addr->name, $this->data['key']);
				$addr_arr[] = $addr_cur;
			} 
			
			$this->result['address'] = $addr_arr;
			/*
			$contacts = json_decode($this->data['contacts']);
			foreach ($contacts as $contact) {
				
				$address = $contact->address;
				$format_address = $this->addons['Crypto']->formatPhoneNumber($address);
				$c_address = $this->addons['Crypto']->encrypt($address, $this->data['key']);
				$c_format_address = $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ?', $c_format_address, $this->user_id)
				);
				$contact_bdd = Contact::find('first', $opt);
				if (!isset($contact_bdd) or $contact_bdd == null) {
					$contact_bdd = new Contact();
					$contact_bdd->user_id = $this->user_id;
					$contact_bdd->format_address = $c_format_address;
				}
				$contact_bdd->address = $c_address;
				$contact_bdd->name = $this->addons['Crypto']->encrypt($contact->name, $this->data['key']);
				if (isset($contact->image)) {
					$contact_bdd->image = $contact->image;
				}
				$contact_bdd->save();
			}
			*/
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