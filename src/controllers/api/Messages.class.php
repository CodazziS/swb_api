<?php
class ApiMessages extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Messages';
		$this->view			= "api/doc.html";
	}
	
	/*
		Messages types
		-2: Todo send  
		-1: sending (in phone)
		1: ...
		2: ...
	*/
	public function resync () {
		$this->error = -1;

		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('messages', 'key', 'device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$mess_transac = Message::connection();
			$mess_transac->transaction();
			
			Message::delete_all(array('conditions' => array('user_id = ? and device_id = ? ', $this->user_id, $this->data['device_id'])));
			$messages = json_decode($this->data['messages']);
			$contacts = array();
			
			foreach ($messages as $message) {
				$format_address = $this->addons['Crypto']->formatPhoneNumber($message->address);
				$message_bdd = $this->updateOrCreateMessage($message, $format_address);
				if (!isset($contacts[$message_bdd->format_address]) || $contacts[$message_bdd->format_address] < $message->date) {
					$contacts[$this->addons['Crypto']->encrypt($format_address, $this->data['key'])] = $message->date;
				}
			}
			$mess_transac->commit();
			
			foreach($contacts as $contact => $value) {
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ? AND device_id = ?', $contact, $this->user_id, $this->data['device_id'])
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
			
			/* Delete all messages type -1 */
			Message::delete_all(
				array('conditions' =>
					array(
						'user_id = ? and device_id = ? AND type = ? ',
						$this->user_id,
						$this->data['device_id'],
						-1)
					));
			foreach ($messages as $message) {
			    $format_address = $this->addons['Crypto']->formatPhoneNumber($message->address);
				$message_bdd = $this->updateOrCreateMessage($message, $format_address);
				if (!isset($contacts[$message_bdd->format_address]) || $contacts[$message_bdd->format_address] < $message_bdd->date_message) {
					$contacts[$this->addons['Crypto']->encrypt($format_address, $this->data['key'])] = $message_bdd->date_message;
				}
			}
			
			foreach($contacts as $contact => $value) {
				$opt = array(
					'conditions' => array('format_address = ? AND user_id = ? AND device_id = ?', $contact, $this->user_id, $this->data['device_id'])
				);
				$contact_bdd = Contact::find('first', $opt);
				if (!empty($contact_bdd)) {
					$contact_bdd->last_message = $value;
					$contact_bdd->save();
				}
			}
			
			$opt = array(
				'conditions' => array(
					'user_id = ? AND device_id = ?',
					$this->user_id,
					$this->data['device_id'])
			);
			$device = Device::find('first', $opt);
			$device->last_sync = time();
			$device->save();
			
			/*
				The update is ended.
				Now, we check if the phone have to send messages
			*/
			
			$opt = array(
				'conditions' => array(
					'user_id = ? AND device_id = ? AND type = ?',
					$this->user_id,
					$this->data['device_id'],
					-2),
				'order' => 'message_id asc'
			);
			$messages_to_send = Message::find('all', $opt);
			$messages_arr = array();
			foreach($messages_to_send as $mess) {
				$message_arr = array();
				$message_arr['id'] = $mess->message_id;
				//$message_arr['body'] = $this->addons['Crypto']->decrypt($mess->body, $this->data['key']);
				//$message_arr['address'] = $this->addons['Crypto']->decrypt($mess->address, $this->data['key']);
				$messages_arr[] = $message_arr;
			}
			
			$this->error = 0;
			$this->result['messages_to_send'] = $messages_arr;
		}
	}
	
	private function updateOrCreateMessage($message, $format_address) {
        $opt = array(
        	'conditions' => array(
        		'user_id = ? AND device_id = ? AND message_id = ?',
        		$this->user_id,
        		$this->data['device_id'],
        		$message->mess_type . '_' . $message->id
    		)
        );
        $message_bdd = Message::find('first', $opt);
        
        
        if (empty($message_bdd)) {
            // Create new message
        	$message_bdd 				= new Message();
        	$message_bdd->user_id 		= $this->user_id;
        	$message_bdd->message_id 	= $message->mess_type . '_' . $message->id;
        	$message_bdd->device_id		= $this->data['device_id'];
        }
        
        if ($message_bdd->unread !== 0) {
        	$message_bdd->unread 			= ($message->read == '1') ? 0 : 1;
        }
        $message_bdd->body 				= $this->addons['Crypto']->encrypt($message->body, $this->data['key']);
        $message_bdd->address 			= $this->addons['Crypto']->encrypt($message->address, $this->data['key']);
        $message_bdd->format_address	= $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
        $message_bdd->type 				= $message->type;
        $message_bdd->date_sent 		= $message->date_sent;
        $message_bdd->date_message 		= $message->date;
        $message_bdd->date_sync 		= time();
       
        if ($message_bdd->type == -2) {
            $message_bdd->date_sent 	= time();
        }
        //var_dump($message->mess_type);
        if ($message->mess_type == "mms") {
            $message_bdd->mess_type = "mms";
            // Save message for SQL contraint
            $message_bdd->save();
            $part_nb = 1;
            foreach ($message->parts as $part) {
                $part_bdd = new Part();
                $part_bdd->user_id = $this->user_id;
                $part_bdd->device_id = $this->data['device_id'];
                $part_bdd->message_id = $message_bdd->message_id;
                $part_bdd->part_nb = $part_nb++;
                $part_bdd->data_type = "image/png"; // Todo 
                $part_bdd->data = $part;
                $part_bdd->save();
            }
        } else {
            $message_bdd->mess_type = "sms";
            $message_bdd->save();
        }
        
        
        return $message_bdd;
	}
	
	public function confirmsent() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('message_id', 'key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$opt = array(
				'conditions' => array(
					'user_id = ? AND device_id = ? AND message_id = ?',
					$this->user_id,
					$this->data['device_id'],
					$this->data['message_id']),
				'order' => 'date_message asc'
			);
			$message = Message::find('first', $opt);
			if (!empty($message)) {
				$message->type = -1;
				$message->date_sync = time();
				$message->save();
				$this->result['body'] = $this->addons['Crypto']->decrypt($message->body, $this->data['key']);
				$this->result['address'] = urlencode($this->addons['Crypto']->decrypt($message->address, $this->data['key']));
			} else {
				$this->error = 7;
			}
			$this->error = 0;
		}
	}
	
	public function getlastsync() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$opt = array(
				'select' => 'date_sent',
				'conditions' => array('user_id = ?', $this->user_id),
				'order' => 'date_sent desc',
			);
			$message = Message::find('first', $opt);
			if (!empty($message)) {
				$this->result['last_message'] = $message->date_sent;
			} else {
				$this->result['last_message'] = 0;
			}
			
			$opt = array(
				'select' => 'date_sent',
				'conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1"),
				'order' => 'date_sent desc',
			);
			$unread_message = Message::find('first', $opt);
			if (!empty($unread_message)) {
				$this->result['last_message_unread'] = $unread_message->date_sent;
			} else {
				$this->result['last_message_unread'] = 0;
			}
			
			$this->result['messages_unread'] = Message::count(array('conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1")));
			$this->error = 0;
		}
	}
	
	public function getlastsyncmessage() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key', 'device_id', 'format_address')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$format_address = $this->addons['Crypto']->encrypt($this->data['format_address'], $this->data['key']);
			$opt = array(
				'select' => 'date_sync',
				'conditions' => array('user_id = ? AND device_id = ? AND format_address = ?', $this->user_id, $this->data['device_id'], $format_address),
				'order' => 'date_sync desc',
			);
			$message = Message::find('first', $opt);
			if (!empty($message)) {
				$this->result['last_message'] = $message->date_sync;
			} else {
				$this->result['last_message'] = 0;
			}
			
			$opt = array(
				'select' => 'date_sync',
				'conditions' => array('user_id = ? AND device_id = ? AND format_address = ? AND unread = ?', $this->user_id, $this->data['device_id'], $format_address, "1"),
				'order' => 'date_sync desc',
			);
			$unread_message = Message::find('first', $opt);
			if (!empty($unread_message)) {
				$this->result['last_message_unread'] = $unread_message->date_sync;
			} else {
				$this->result['last_message_unread'] = 0;
			}
			$this->error = 0;
		}
	}
	
	public function getmessages () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key', 'device_id', 'format_address')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$format_address = $this->addons['Crypto']->encrypt($this->data['format_address'], $this->data['key']);
			$opt = array(
				'conditions' => array('format_address = ? AND user_id = ? AND device_id = ?', $format_address, $this->user_id, $this->data['device_id']),
				'order' => 'date_message asc'
			);
			$messages = Message::find('all', $opt);
			$mess_arr = array();
			foreach ($messages as $mess) {
				$mess_cur = array();
				$mess_cur['message_id'] = $mess->message_id;
				$mess_cur['time'] = $mess->date_message;
				$mess_cur['mess_type'] = $mess->mess_type;
				$mess_cur['type'] = $mess->type;
				$mess_cur['unread'] = $mess->unread;
				if ($mess->unread) {
					$mess->unread = 0;
					$mess->save();
				}
				$mess_cur['body'] = $this->addons['Crypto']->decrypt($mess->body, $this->data['key']);
				if ($mess_cur['body'] == null) {
				    $mess_cur['body'] = "";
				}
				if ($mess->mess_type == 'mms') {
				    $mess_cur['parts'] = array();
				    $opt = array(
            			'conditions' => array(
            			    'user_id = ? AND device_id = ? AND message_id = ?',
            			    $this->user_id,
            			    $this->data['device_id'],
            			    $mess->message_id
        			    )
            		);
            		$parts = Part::find('all', $opt);
				    foreach($parts as $part) {
				        $mess_cur['parts'][] = $part->part_nb;
				    }
				}
				$mess_arr[] = $mess_cur;
			} 
			$this->result['messages'] = $mess_arr;
			$this->error = 0;
		}
	}
	
	public function getPart() {
	    $this->render_class = 'Text';
	    $this->error = 0;
	    $this->result['text'] = '';
	    
        header('Pragma: public');
        header('Cache-Control: max-age=86400');
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Type: image/png');
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key', 'device_id')
		);

		if ($this->addons['Apy']->check($this, $conditions)) {
		    $opt = array(
    			'conditions' => array(
    			    'user_id = ? AND device_id = ? AND message_id = ? AND part_nb = ?',
    			    $this->user_id,
    			    $this->data['device_id'],
    			    $this->data['message_id'],
    			    $this->data['part_nb']
		        )
    		);
    		$parts = Part::find('first', $opt);
    		if (!empty($parts)) {
    		    if ($parts->data != '') {
    		        //$this->result['text'] = base64_decode($this->addons['Crypto']->decrypt($contact->image, $this->data['key']));
    		        $this->result['text'] = base64_decode($parts->data);
    		    }
    		}
		}
	}
	
	public function index() {
		$this->render_class = 'Render';
		$this->result = array('name' => $this->title, 'docs' => array());
		
		/* Create */
		$this->result['docs'][] = array(
			'name' => 'Resync',
			'type' => 'POST',
			'description' => 'Sync SMS/MMS (Delete older SMS, with the same device_id)',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (String)',
				'Device_id (String)',
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
				'Device_id (String)',
				'Messages (json string)',
			),
			'results' => array(
				'Error (interger)',
				'Messages_to_send (message)'
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
				'Device_id (String)',
				'Address (String)',
			),
			'results' => array(
				'Error (interger)'
			)
		);
		
		$this->result['docs'][] = array(
			'name' => 'getLastSync',
			'type' => 'GET',
			'description' => 'Get time to last message',
			'args' => array(
				'User (string)',
				'Token (string)',
				'Key (String)',
			),
			'results' => array(
				'Error (interger)',
				'Last_message (long)',
				'Last_message_unread (long)',
			)
		);
	}

}