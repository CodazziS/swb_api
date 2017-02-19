<?php

/**
 * User Management
 * CRUD and get token for all others methods
 */
class ApiUsers extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Users';
		$this->view			= "api/doc.html";
	}
	
	/**
	 * @method POST
	 * @name create
	 * @description User creation
	 * @param $email (string) : User email
	 * @param $password (string) : Password 
	 * @return key (string): Key used for encryption
	 * @return created (boolean): Flag if user has been created
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function create () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'fields' => array('email', 'password')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$this->result['created'] = false;
			
			/* Verification mail */
			if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
				$this->error = 3;
				return;
			}
			
			/* Vérification password (size) */
			if (strlen($this->data['password']) < MIN_PASSWORD_LEN) {
				$this->error = 4;
				return;
			}
			
			/* hash password (and email ?) */
			$password = $this->addons['Crypto']->hash1($this->data['password']);
			$key = $this->addons['Crypto']->hash2($this->data['password']);
			
			/* Email unicity verification */
			$opt = array(
				'conditions' => array('email = ?', $this->data['email'])
			);
			$account = User::find('first', $opt);
			if (isset($account) && $account != null) {
				$this->error = 5;
				return;
			}
			
			/* Sauvegarde en BDD */
			$new_account = new User();
			$new_account->email = $this->data['email'];
			$new_account->password = $password;
			$new_account->creation_date = time();
			$new_account->save();
			
			$this->error = 0;
			$this->result['key'] = $key;
			$this->result['created'] = true;
		} else {
			$this->result['created'] = false;
		}
	}
	
	/**
	 * @method POST
	 * @name delete
	 * @description Delete user and all user's content (messages, devices, contacts)
	 * @param $email (string) : User email
	 * @param $password (string) : Password 
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function delete () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'POST',
			'fields' => array('email', 'password')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$password = $this->addons['Crypto']->hash1($this->data['password']);
			$opt = array(
				'conditions' => array('email = ? AND password = ?', $this->data['email'], $password)
			);
			$account = User::find('first', $opt);
			if (isset($account) && $account != null) {
				Message::delete_all(array('conditions' => array('user_id = ?', $account->id)));
				Contact::delete_all(array('conditions' => array('user_id = ?', $account->id)));
				Device::delete_all(array('conditions' => array('user_id = ?', $account->id)));
				$account->delete();
				
				$this->error = 0;
			} else {
				$this->error = 6;
			}
		}
	}
    
    /**
	 * @method GET
	 * @name getToken
	 * @description Get authentification token
	 * @param $email (string) : User email
	 * @param $password (string) : Password 
	 * @param $type (string) : Connexion type : (Eg: "android", "web", ...). If type is android, need to complete device_id, device_model and rev_name
	 * @param $device_id (string) : [Optional] Device identification
	 * @param $device_model (string) : [Optional] Device model
	 * @param $rev_name (string) : [Optional] Revision name (for creation only, not replace)
	 * @return key (string): Key used for encryption
	 * @return token (string): Authentication token
	 * @return user (int): User id
	 * @return api_version (int): Current version of API
	 * @return rev_name (string): Current revision attached at the device_id (may be differt from rev_name passed at param)
	 * @return revision (int): Current revision number attached at the device_id
	 * @return $error (int) : Error code. Details in /api/error
	 */
	public function getToken () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'fields' => array('email', 'password', 'type')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$password = $this->addons['Crypto']->hash1($this->data['password']);
			$opt = array(
				'conditions' => array('LOWER(email) = LOWER(?) AND password = ?', $this->data['email'], $password)
			);
			$account = User::find('first', $opt);
			if (isset($account) && $account != null) {
				$key = $this->addons['Crypto']->hash2($this->data['password']);
				$account->connexion_date = time();
				$account->save();
				
				$token = new Token();
				$token->token = Crypto::random(64);
				$token->user_id = $account->id;
				
				$token->user_id = $account->id;
				$token->type = $this->data['type'];
				$expire = time() + 60 * 60 * 24; // 1 day
				$token->expire_date = $expire;
				$token->save();
				
				$this->result['key'] = $key;
				$this->result['token'] = $token->token;
				$this->result['user'] = $account->id;
				$this->result['api_version'] = API_VERSION;
				$this->error = 0;
				
				if ($this->data['type'] == 'android') {
				    require_once(__DIR__ . "/Devices.class.php");
				    $device = ApiDevices::getOrAddDevice($account->id, $this->data['device_id'], $this->data['device_model'], $this->data['rev_name'], true);
				    $this->result['revision'] = $device->revision;
				    $this->result['rev_name'] = $device->rev_name;
				}
			} else {
				$this->error = 6;
			}
		}
	}

    /**
	 * @method GET
	 * @name getInfos
	 * @description Get user global informations
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $messages (int) : Number of messages saved
	 * @return $messages_unread (int) : Number of messages unread
	 * @return $contacts (int) : Number of contacts saved
	 */
	public function getInfos () {
		$this->error = -1;

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$this->result['messages'] 			= intval(Message::count(array('conditions' => array('user_id = ?', $this->user_id))));
			$this->result['messages_unread'] 	= intval(Message::count(array('conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1"))));
			$this->result['contacts'] 			= intval(Contact::count(array('conditions' => array('user_id = ?', $this->user_id))));

			$this->error = 0;
		}
	}
	
	/**
	 * @method GET
	 * @name getUnread
	 * @description Get user number unread messages
	 * @param $user (int) : User identifiant
	 * @param $token (string) : Token 
	 * @return $error (int) : Error code. Details in /api/error
	 * @return $messages_unread (int) : Number of messages unread
	 */
	public function getUnread () {
		$this->error = -1;

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$this->result['messages_unread'] 	= intval(Message::count(array('conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1"))));
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