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
		if (!empty($this->data['email'])) {
			if ($this->addons['Authentication']->signin ($this, $this->data['email'], $this->data['password'])) {
				header('Location: /#home_use_app'); 
			}
		}
	}
	
	public function index() {
		$this->init();
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
		if (!$this->result['logged']) {
			header('Location: /'); 
		}
		
		$res = Apy::call($this, 'Devices', 'GetDevices', 'GET', array('user' => $_COOKIE['user'], 'token' => $_COOKIE['token']));
	
		if ($res['error'] == 0) {
			$this->result['devices'] = $res['devices'];
		} else {
			header('Location: /index/logout');
		}
		$res = Apy::call($this, 'Users', 'GetInfos', 'GET', array('user' => $_COOKIE['user'], 'token' => $_COOKIE['token']));
		
		if ($res['error'] == 0) {
			$this->result['messages'] 			= $res['messages'];
			$this->result['messages_unread'] 	= $res['messages_unread'];
			$this->result['contacts'] 			= $res['contacts'];
		} else {
			header('Location: /index/logout');
		}
	}
	
}