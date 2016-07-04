<?php
class Account extends FzController {
	function __construct() {
		$this->render_class 	= 'Render';
		$this->title 			= '';
		$this->view				= "default.html";
		$this->result['html']	= "account";
		$this->result['logged'] = false;
	}
	
	private function init() {
		$this->result['title'] = $this->lang['account_title'];
		$this->result['description'] = $this->lang['account_description'];
	}
	
	public function signin() {
		$this->init();
		$this->result['html'] = "signin";
		if (isset($this->data['email'])) {
			// Empty email
			if (empty($this->data['email'])) {
				$this->result['errorstr'] = $this->lang['home_signin_error_email'];
				return;
			}
			// Passwords not match
			if ($this->data['password1'] !== $this->data['password2']) {
				$this->result['errorstr'] = $this->lang['home_signin_error_password'];
				return;
			}
			
			$signin = $this->addons['Authentication']->signin ($this, $this->data['email'], $this->data['password1']);
			if ($signin['created']) {
				$this->addons['Authentication']->login($this, $this->data['email'], $this->data['password1']);
			} else {
				$this->result['errorstr'] = /*"Error " . $signin['error'] . ": " . */$this->lang['home_signin_api_error_' . $signin['error']];
			}
		}
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
	}
	
	public function index() {
		$this->init();
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
		if (!$this->result['logged']) {
			header('Location: /'.$this->lang['code']); 
		}
		
		$res = Apy::call($this, 'Devices', 'GetDevices', 'GET', array('user' => $_COOKIE['user'], 'token' => $_COOKIE['token']));
	
		if ($res['error'] == 0) {
			$this->result['devices'] = $res['devices'];
		} else {
			header('Location: /'.$this->lang['code'].'/index/logout');
		}
		$res = Apy::call($this, 'Users', 'GetInfos', 'GET', array('user' => $_COOKIE['user'], 'token' => $_COOKIE['token']));
		
		if ($res['error'] == 0) {
			$this->result['messages'] 			= $res['messages'];
			$this->result['messages_unread'] 	= $res['messages_unread'];
			$this->result['contacts'] 			= $res['contacts'];
		} else {
			header('Location: /'.$this->lang['code'].'/index/logout');
		}
	}
	
}