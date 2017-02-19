<?php

/**
 * Contacts Management
 * CRUD contacts
 */
class ApiContacts extends FzController {
    
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Contacts';
		$this->view			= "api/doc.html";
	}

	/**
	 * @method POST
	 * @name add
	 * @description Add users list for a device
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $contacts (json array) : TODO comment
	 * @param $reset (boolean) : If true, remove all contacts before insert the news
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function add () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'authentication' => true,
			'fields' => array('contacts', 'key', 'device_id')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
		    $connexion = Contact::connection();
		    try {
		        $connexion->transaction();
    			if (empty($this->data['reset']) || $this->data['reset'] == "true") {
    			    Contact::delete_all(array('conditions' => array('user_id = ? and device_id = ? ', $this->user_id, $this->data['device_id'])));
    			}
    			
    			$contacts = json_decode($this->data['contacts']);
    			foreach ($contacts as $contact) {
    				$address = $contact->address;
    				$format_address = $this->addons['Crypto']->formatPhoneNumber($address);
    				$c_address = $this->addons['Crypto']->encrypt($address, $this->data['key']);
    				$c_format_address = $this->addons['Crypto']->encrypt($format_address, $this->data['key']);
                    
                    $opt = array(
                		'conditions' => array('user_id = ? AND device_id = ? AND format_address = ?', $this->user_id, $this->data['device_id'], $c_format_address), 
                	);
    
        		    $contact_bdd = Contact::find('first', $opt);
        		    if (empty($contact_bdd)) {
        				$contact_bdd = new Contact();
        				$contact_bdd->user_id = $this->user_id;
        				$contact_bdd->format_address = $c_format_address;
        				$contact_bdd->device_id = $this->data['device_id'];
        		    }
    
    				$contact_bdd->address = $c_address;
    				$contact_bdd->name = $this->addons['Crypto']->encrypt($contact->name, $this->data['key']);
    				if (isset($contact->photo)) {
    				    $contact_bdd->have_img = true;
    					$contact_bdd->image = $this->addons['Crypto']->encrypt($contact->photo, $this->data['key']);
    				}
    				$contact_bdd->save();
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
	 * @method GET
	 * @name getContactImg
	 * @description Get contact profil picture
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @param $format_address (string) : Contact address number
	 * @return picture: png image
	 */
	public function getContactImg() {
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
			'fields' => array('key', 'device_id', 'format_address')
		);
		
		if ($this->addons['Apy']->check($this, $conditions)) {
		    $opt = array(
    			'select' => 'image',
    			'conditions' => array(
    			    'user_id = ? AND device_id = ? AND format_address = ?',
                    $this->user_id,
                    $this->data['device_id'],
                    $this->addons['Crypto']->encrypt($this->data['format_address'], $this->data['key'])
                ), 
    		);
    		$contact = Contact::find('first', $opt);
    
    		if (!empty($contact)) {
    		    if ($contact->image != '') {
    		        $this->result['text'] = base64_decode($this->addons['Crypto']->decrypt($contact->image, $this->data['key']));
    		    }
    		}
		}
	}
	
	private function getContactInfos($format_address, $device_id) {
		$opt = array(
			'select' => 'name, address, have_img',
			'conditions' => array('user_id = ? AND device_id = ? AND format_address = ?', $this->user_id, $device_id, $format_address), 
		);
		$contact = Contact::find('first', $opt);
		$result = array('name' => '', 'address' => '', 'have_img' => false);
		if (empty($contact)) {
			/* Contact doesn't exist, so, we have only his address on messages */
			$opt = array(
				'select' => 'address',
				'conditions' => array('user_id = ? AND device_id = ? AND format_address = ?', $this->user_id, $device_id, $format_address), 
			);
			$mess = Message::find('first', $opt);
			$result['name'] 	= $this->addons['Crypto']->decrypt($mess->address, $this->data['key']);
			$result['address']	= $this->addons['Crypto']->decrypt($mess->address, $this->data['key']);
		} else {
			$result['name'] 	= $this->addons['Crypto']->decrypt($contact->name, $this->data['key']);
			$result['address']	= $this->addons['Crypto']->decrypt($contact->address, $this->data['key']);
			$result['have_img']	= ($contact->have_img) ? true: false;
		}
		return $result;
	}
	
	/**
	 * @method GET
	 * @name getcontacts
	 * @description Get all contacts
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $address (array) : Array of address
	 */
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
			    'select' => 'name, address, have_img, device_id, format_address',
				'conditions' => array('user_id = ?', $this->user_id), 
				'order' => 'device_id ASC'
			);
			$address = Contact::find('all', $opt);

			$addr_arr = array();
			foreach ($address as $addr) {
				$addr_cur = array();
				$addr_cur['device_id'] = $addr->device_id;
				$addr_cur['have_img'] = $addr->have_img;
				$addr_cur['address'] = $this->addons['Crypto']->decrypt($addr->address, $this->data['key']);
				$addr_cur['format_address'] = $this->addons['Crypto']->decrypt($addr->format_address, $this->data['key']);
				$addr_cur['model'] = \ApiDevices::getDeviceName($this->user_id, $addr->device_id);
				$addr_cur['name'] = $this->addons['Crypto']->decrypt($addr->name, $this->data['key']);
				$addr_arr[] = $addr_cur;
			} 
			$this->result['address'] = $addr_arr;
			$this->error = 0;
		}
	}

    /**
	 * @method GET
	 * @name getactive
	 * @description Get all contacts who have at least one message (sms/mms). Phone number without contact name too.
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @param $key (string) : User key
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $address (array) : Array of address
	 */
	public function getactive () {
		$this->error = -1;
		
		require_once ('Devices.class.php');

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array('key')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$opt = array(
				'select' => 'format_address, device_id, MAX(date_message) as date_message, SUM(unread) as unread',
				'conditions' => array('user_id = ?', $this->user_id), 
				'order' => 'date_message desc',
				'group' => 'device_id, format_address',
			);
			$address = Message::find('all', $opt);

			$addr_arr = array();
			foreach ($address as $addr) {
				$contact_infos = $this->getContactInfos($addr->format_address, $addr->device_id);
				$addr_cur = array();
				$addr_cur['time'] = $addr->date_message;
				$addr_cur['device_id'] = $addr->device_id;
				$addr_cur['unread'] = $addr->unread;
				$addr_cur['address'] = $contact_infos['address'];
				$addr_cur['format_address'] = $this->addons['Crypto']->decrypt($addr->format_address, $this->data['key']);
				$addr_cur['model'] = ApiDevices::getDeviceName($this->user_id, $addr->device_id);
				$addr_cur['name'] = $contact_infos['name'];
				$addr_cur['have_img'] = $contact_infos['have_img'];
				$addr_arr[] = $addr_cur;
			} 
			$this->result['address'] = $addr_arr;
			$this->error = 0;
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