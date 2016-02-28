<?php
class Index extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home API';
		$this->view			= "index.html";
	}
	
	private function parseActions() {
		if (isset($this->data['action'])) {
			if ($this->data['action'] === 'signin') {
				$this->addons['Authentication']->signin($this->data['email'], $this->data['password']);
			} else if ($this->data['action'] === 'connexion') {
				$this->addons['Authentication']->login($this->data['email'], $this->data['password']);
			}
		}
	}
	
	public function index() {
		$this->parseActions();
	}
}