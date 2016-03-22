<?php
class ApiMessages extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Messages';
		$this->view			= "api/doc.html";
	}
	
	public function resync () {
		$this->error = -1;
		

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('messages', 'key', 'android_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			Message::delete_all(array('conditions' => array('user_id = ? and device = ? ', $this->user_id, $this->data['android_id'])));
			$messages = json_decode($this->data['messages']);
			$contacts = array();
			foreach ($messages as $message) {
				
				$format_address = $this->addons['Crypto']->formatPhoneNumber($message->address);

				$message_bdd 					= new Message();
				$message_bdd->user_id 			= $this->user_id;
				$message_bdd->android_id 		= $message->id;
				$message_bdd->device 			= $this->data['android_id'];
				$message_bdd->date_message 		= $message->date;
				$message_bdd->date_sync 		= time();
				$message_bdd->read 				= $message->read;
				$message_bdd->body 				= $this->addons['Crypto']->encrypt($message->body, $this->data['key']);
				$message_bdd->address 			= $this->addons['Crypto']->encrypt($message->address, $this->data['key']);
				$message_bdd->format_address	= $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
				$message_bdd->type 				= $message->type;
				$message_bdd->date_sent 		= $message->date_sent;
				$message_bdd->save();
				if (!isset($contacts[$message_bdd->format_address]) || $contacts[$message_bdd->format_address] < $message_bdd->date_message) {
					$contacts[$this->addons['Crypto']->encrypt($format_address, $this->data['key'])] = $message_bdd->date_message;
				}
			}
			
			foreach($contacts as $contact => $value) {
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ? AND android_id = ?', $contact, $this->user_id, $this->data['android_id'])
				);
				$contact_bdd = Contact::find('first', $opt);
				if (!empty($contact_bdd)) {
					$contact_bdd->last_message = $value;
					$contact_bdd->save();
				}
			}
			
			$this->error = 0;
		}
	}
	
	public function sync () {
		$this->error = -1;
		

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('messages', 'key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$messages = json_decode($this->data['messages']);
			$contacts = array();
			foreach ($messages as $message) {
				
				$opt = array(
					'conditions' => array(
						'user_id = ? AND device = ? AND android_id = ?',
						$this->user_id,
						$this->data['android_id'],
						$message->id)
				);
				$message_bdd = Message::find('first', $opt);
				
				$format_address = $this->addons['Crypto']->formatPhoneNumber($message->address);
				if (empty($message_bdd)) {
					$message_bdd 				= new Message();
					$message_bdd->user_id 		= $this->user_id;
					$message_bdd->android_id 	= $message->id;
					$message_bdd->device 		= $this->data['android_id'];
				}
				$message_bdd->date_message 		= $message->date;
				$message_bdd->date_sync 		= time();
				$message_bdd->read 				= $message->read;
				$message_bdd->body 				= $this->addons['Crypto']->encrypt($message->body, $this->data['key']);
				$message_bdd->address 			= $this->addons['Crypto']->encrypt($message->address, $this->data['key']);
				$message_bdd->format_address	= $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
				$message_bdd->type 				= $message->type;
				$message_bdd->date_sent 		= $message->date_sent;
				$message_bdd->save();
				if (!isset($contacts[$message_bdd->format_address]) || $contacts[$message_bdd->format_address] < $message_bdd->date_message) {
					$contacts[$this->addons['Crypto']->encrypt($format_address, $this->data['key'])] = $message_bdd->date_message;
				}
			}
			
			foreach($contacts as $contact => $value) {
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ? AND android_id = ?', $contact, $this->user_id, $this->data['android_id'])
				);
				$contact_bdd = Contact::find('first', $opt);
				if (!empty($contact_bdd)) {
					$contact_bdd->last_message = $value;
					$contact_bdd->save();
				}
			}
			
			$opt = array(
				'conditions' => array(
					'user_id = ? AND android_id = ?',
					$this->user_id,
					$this->data['android_id'])
			);
			$device = Device::find('first', $opt);
			$device->last_sync = time();
			$device->save();
			
			$this->error = 0;
		}
	}
	
	public function getmessages () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key', 'android_id', 'address')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$address = $this->addons['Crypto']->encrypt($this->data['address'], $this->data['key']);
			$opt = array(
				'conditions' => array('format_address = ? AND user_id = ? AND device = ?', $address, $this->user_id, $this->data['android_id'])
			);
			$messages = Message::find('all', $opt);
			$mess_arr = array();
			foreach ($messages as $mess) {
				$mess_cur = array();
				$mess_cur['time'] = $mess->date_message;
				$mess_cur['read'] = $mess->read;
				$mess_cur['type'] = $mess->type;
				$mess_cur['body'] = $this->addons['Crypto']->decrypt($mess->body, $this->data['key']);
				$mess_arr[] = $mess_cur;
			} 
			
			$this->result['messages'] = $mess_arr;
			$this->result['opt'] = $opt;

			$this->error = 0;
		}
	}
	
	public function index() {
		$this->render_class = 'Render';
		$this->result = array('name' => $this->title, 'docs' => array());
		
		/* Create */
		$this->result['docs'][] = array(
			'name' => 'Resync',
			'type' => 'POST',
			'description' => 'Sync SMS/MMS (Delete older SMS, with the same Android_id)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (String)',
				'Android_id (String)',
				'Messages (json string)',
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
		$this->result['docs'][] = array(
			'name' => 'Sync',
			'type' => 'POST',
			'description' => 'Sync SMS/MMS',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (String)',
				'Android_id (String)',
				'Messages (json string)',
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
		$this->result['docs'][] = array(
			'name' => 'GetMessages',
			'type' => 'GET',
			'description' => 'Get messages of one address',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (String)',
				'Android_id (String)',
				'address (String)',
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
	}

}