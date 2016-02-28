<?php
class ApiIndex extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home API';
		$this->view			= "api/index.html";
	}
	
	public function index() {
		$this->result = array('lines' => array());
		
		$this->result['lines'][] = '<a href="/Api/Contacts">Contacts</a> : Contacts management.';
		$this->result['lines'][] = '<a href="/Api/Devices">Devices</a> : Devices management.';
		$this->result['lines'][] = '<a href="/Api/Errors">Errors</a> : Errors codes list.';
		$this->result['lines'][] = '<a href="/Api/Messages">Messages</a> : Sync messages.';
		$this->result['lines'][] = '<a href="/Api/Users">Users</a> : Users management.';
	}
}