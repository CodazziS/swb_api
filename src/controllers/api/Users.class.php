<?php
class ApiUsers extends FzController {
	function __construct() {
		$this->render_class = 'Json';
		$this->title 		= 'Users';
		$this->view			= "api/doc.html";
	}
	
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
	
	public function gettoken () {
		$this->error = -1;
		
		$conditions = array(
			'method' => 'GET',
			'fields' => array('email', 'password', 'type')
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			/* hash password (and email ?) */
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
				$token->expire_date = time() + 60 * 60 * 24; // 1 day
				$token->user_id = $account->id;
				$token->type = $this->data['type'];
				$token->save();
				
				$this->result['key'] = $key;
				$this->result['token'] = $token->token;
				$this->result['user'] = $account->id;
				$this->error = 0;
			} else {
				$this->error = 6;
			}
		}
	}
	
	public function getInfos () {
		$this->error = -1;

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			
			$this->result['messages'] 			= intval(Message::count(array('conditions' => array('user_id = ?', $this->user_id))));
			$this->result['messages_unread'] 	= Message::count(array('conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1")));
			$this->result['contacts'] 			= Contact::count(array('conditions' => array('user_id = ?', $this->user_id)));

			$this->error = 0;
		}
	}
	
	public function getUnread () {
		$this->error = -1;

		$conditions = array(
			'method' => 'GET',
			'authentication' => true,
			'fields' => array()
		);
		if ($this->addons['Apy']->check($this, $conditions)) {
			$this->result['messages_unread'] 	= Message::count(array('conditions' => array('user_id = ? AND unread = ?', $this->user_id, "1")));
			$this->error = 0;
		}
	}

	public function index() {
		$this->render_class = 'Render';
		
		$this->result = array('name' => $this->title, 'docs' => array());
		
		/* Create */
		$this->result['docs'][] = array(
			'name' => 'Create',
			'type' => 'POST',
			'description' => 'Add one user in the database, if conditions is good (unique email, good password).',
			'args' => array(
				'Email (string)',
				'Password (string)'
			),
			'results' => array(
				'Created (boolean)',
				'Error (interger)'
			)
		);
		
		/* GetToken */
		$this->result['docs'][] = array(
			'name' => 'GetToken',
			'type' => 'GET',
			'description' => 'Get token for the current user (Used by all functions).',
			'args' => array(
				'Email (string)',
				'Password (string)'
			),
			'results' => array(
				'Token (string)',
				'User (int)',
				'Key (string)',
				'Error (interger)'
			)
		);
		
		/* GetInfos */
		$this->result['docs'][] = array(
			'name' => 'GetInfos',
			'type' => 'GET',
			'description' => 'Get infos of the current user',
			'args' => array(
				'Email (string)',
				'Password (string)'
			),
			'results' => array(
				'Messages (int)',
				'Messages_unread (int)',
				'Contacts (int)',
				'Error (interger)'
			)
		);
	}

}