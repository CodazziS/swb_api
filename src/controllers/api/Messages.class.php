<?php
class ApiMessages extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Messages';
		$this->view			= "api/doc.html";
	}
	
	/**
	 * @method POST
	 * @name addSmsList
	 * @description Add sms list
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $messages (json array) : TODO comment
	 * @param $device_id (string) : Device identification
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function addSmsList() {
        $this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('messages', 'key', 'device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$messages = json_decode($this->data['messages']);
			$contacts = array();
			
            $connexion = Message::connection();
            try {
                $connexion->transaction();
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
    			$connexion->commit();
                $this->error = 0;
            } catch(Exception $e) {
                throw $e;
                $connexion->rollback();
            }
		}
	}
	
	/**
	 * @method POST
	 * @name addMmsList
	 * @description Add mms list
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $messages (json array) : TODO comment
	 * @param $device_id (string) : Device identification
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function addMmsList() {
	    $this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('messages', 'key', 'device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$messages = json_decode($this->data['messages']);
			$contacts = array();
			
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
			$this->error = 0;
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
        	$message_bdd->unread 		= ($message->read == '1' || $message->read == 1) ? 0 : 1;
        }
        $message_bdd->body 				= $this->addons['Crypto']->encrypt($message->body, $this->data['key']);
        $message_bdd->address 			= $this->addons['Crypto']->encrypt($message->address, $this->data['key']);
        $message_bdd->format_address	= $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
        $message_bdd->type 				= $message->type;
        $message_bdd->date_sent 		= $message->date_sent;
        $message_bdd->date_message 		= $message->date;
        $message_bdd->date_sync 		= time();

        if ($message->mess_type == "mms") {
            $message_bdd->mess_type = "mms";
            // Save message for SQL contraint
            $message_bdd->save();
            $part_nb = 1;
            foreach ($message->parts as $part) {
                $opt_part = array(
                	'conditions' => array(
            		'user_id = ? AND device_id = ? AND message_id = ? AND part_nb = ?',
                		$this->user_id,
                		$this->data['device_id'],
                		$message_bdd->message_id,
                		$part_nb
        	    	)
                );
                $part_bdd = Part::find('first', $opt_part);
                if (empty($part_bdd)) {
                    $part_bdd = new Part();
                    $part_bdd->user_id = $this->user_id;
                    $part_bdd->device_id = $this->data['device_id'];
                    $part_bdd->message_id = $message_bdd->message_id;
                    $part_bdd->part_nb = $part_nb++;
                }
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
	
	private function getQueueMessages() {
	    $opt = array(
				'conditions' => array(
					'user_id = ? AND device_id = ?',
					$this->user_id,
					$this->data['device_id'])
			);
			$queue = Queue::find('all', $opt);
			$queue_arr = array();
			foreach ($queue as $item) {
			    $item_arr = array(
			        'id'        => $item->id,
			        'user_id'   => $item->user_id,
			        'type'      => $item->type,
			        'address'   => $this->addons['Crypto']->decrypt($item->address, $this->data['key']),
			        'body'      => $this->addons['Crypto']->decrypt($item->body, $this->data['key']),
			        'device_id' => $item->device_id
		        );
			    $queue_arr[] = $item_arr;
			}
			
			return $queue_arr;
	}
	
	/**
	 * @method GET
	 * @name getQueue
	 * @description Get message queue for device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $queue (JSON array) : Message array
	 */
	public function getQueue() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key', 'device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$this->result['queue'] = $this->getQueueMessages();
			$this->error = 0;
		}
	}
	
	/**
	 * @method POST
	 * @name addQueue
	 * @description Get message queue for device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $address (string) : Address to send the message
	 * @param $body (string) : Message body
	 * @param $type (string) : Message type (only "sms" supported for now)
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $queue (JSON array) : Message array
	 */
	public function addQueue() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('key', 'device_id', 'address', 'body', 'type')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
		    $queue = new Queue();
		    $queue->user_id = $this->user_id;
		    $queue->type = $this->data['type'];
		    $queue->address = $this->addons['Crypto']->encrypt($this->data['address'], $this->data['key']);
		    $queue->body = $this->addons['Crypto']->encrypt($this->data['body'], $this->data['key']);
		    $queue->device_id = $this->data['device_id'];
		    $queue->save();

			$this->error = 0;
		}
	}
	
	/**
	 * @method POST
	 * @name validQueue
	 * @description Valide that message is sended (and remove it from queue list)
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $id (int) : Message queue id
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function validQueue() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('key', 'device_id', 'id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$opt = array(
				'conditions' => array(
					'user_id = ? AND device_id = ? AND id = ?',
					$this->user_id,
					$this->data['device_id'],
					$this->data['id'])
			);
			$queue = Queue::find('first', $opt);
			if (!empty($queue)) {
			    $queue->delete();
			    $this->error = 0;
			} else {
			    $this->error = 7;
			}
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
				'select' => 'date_sync',
				'conditions' => array('user_id = ?', $this->user_id),
				'order' => 'date_sync desc',
			);
			$message = Message::find('first', $opt);
			if (!empty($message)) {
				$this->result['last_message'] = $message->date_sync;
			} else {
				$this->result['last_message'] = 0;
			}
			
			$opt = array(
				'select' => 'date_message',
				'conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1"),
				'order' => 'date_message desc',
			);
			$unread_message = Message::find('first', $opt);
			if (!empty($unread_message)) {
				$this->result['last_message_unread'] = $unread_message->date_message;
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
	
	/**
	 * @method POST
	 * @name markAllAsRead
	 * @description Mark all message as read
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function markAllAsRead() {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
		    Message::update_all(array(
                'set' => array(
                    'unread' => 0
                ),
                'conditions' => array(
                    'user_id' => $this->user_id
                )
            ));
	        $this->error = 0;
		}
	}
	
	/**
	 * @method GET
	 * @name getmessages
	 * @description Get messages for a contact
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $format_address (string) : Contact address
	 * @return $error (int) : Error code. Details in /api/error
	 */
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
			$this->result['queue'] = $this->getQueueMessages();
			$this->error = 0;
		}
	}
	
	/**
	 * @method GET
	 * @name getPart
	 * @description Get message part (image for mms)
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $device_id (string) : Device identification
	 * @param $message_id (int) : Message id
	 * @param $part_nb (int) : Part identification 
	 * @return $error (int) : Error code. Details in /api/error
	 */
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
			'fields' => array('key', 'device_id', 'message_id', 'part_nb')
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