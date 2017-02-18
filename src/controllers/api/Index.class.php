<?php
namespace {
	
	class ApiIndex extends FzController {
		function __construct() {
			$this->render_class = 'Render';
			$this->title 		= 'Home API';
			$this->view			= "api/index.html";
		}
		
		function tester() {
			$this->render_class = 'Json';
			
			$this->result['data'] = $this->data;
			$this->result['request'] = $this->request;
		}
		
		function getversion() {
			$this->render_class = 'Json';
			
			/* Need to be here for git's commits */
			$this->result['api_version'] = API_VERSION;
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
};