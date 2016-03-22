<?php
class Messages extends FzController {
	function __construct() {
		$this->render_class 	= 'Render';
		$this->title 			= '';
		$this->view				= "default.html";
		$this->result['html']	= "messages";
		$this->result['logged'] = false;
	}
	
	private function init() {
		$this->result['title'] = $this->lang['messages_title'];
		$this->result['description'] = $this->lang['messages_description'];
	}
	
	public function index() {
		$this->init();
		$this->result['logged'] = $this->addons['Authentication']->is_auth();
		if (!$this->result['logged']) {
			header('Location: /'); 
		}
		
	}
	
}