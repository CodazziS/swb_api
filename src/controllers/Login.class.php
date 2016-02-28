<?php
class Login extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home API';
		$this->view			= "login.html";
	}
	
	private function parseActions() {
		if (isset($this->data['action'])) {
			if ($this->data['action'] === 'signin') {
				$res = $this->addons['Authentication']->signin($this, $this->data['email'], $this->data['password']);
			} else if ($this->data['action'] === 'login') {
				$res = $this->addons['Authentication']->login($this, $this->data['email'], $this->data['password']);
			}
			var_dump($res);
		}
	}
	
	public function index() {
		$this->parseActions();
	}
}