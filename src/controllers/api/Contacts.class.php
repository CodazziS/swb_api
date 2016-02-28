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
			'fields' => array('contacts', 'key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
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
				'Key (string)'
				
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
	}

}