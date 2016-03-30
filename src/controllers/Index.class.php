<?php
class Index extends FzController {
	function __construct() {
		$this->render_class 	= 'Render';
		$this->title 			= 'Home';
		$this->view				= "default.html";
		$this->result['html']	= "default";
		$this->result['logged'] = false;
	}
	
	private function init() {
		$this->result['title'] = $this->lang['home_title'];
		$this->result['description'] = $this->lang['home_description'];
	}
	
	private function parseActions() {
		if (isset($this->data['action'])) {
			if ($this->data['action'] === 'login') {
				$this->addons['Authentication']->login($this, $this->data['email_login'], $this->data['password_login']);
				if ($this->addons['Authentication']->is_auth()) {
					header('Location: /Account'); 
				}
				//$this->addons['Authentication']->signin($this->data['email_login'], $this->data['password_login']);
			}
		}
	}
	
	public function index() {
		$this->init();
		$this->parseActions();
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
	}
	
	public function logout() {
		$this->init();
		$this->addons['Authentication']->logout();
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
	}
	
	public function allowcookies() {
		$this->render_class = 'Json';
		setcookie("cookies_ok", "true",	time() + 864000, '/');
	}
}