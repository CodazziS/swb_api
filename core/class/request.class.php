<?php

class FzRequest {
	public $method		= '';
	public $data		= '';
    public $url         = '';
    public $path        = '';
    public $class       = 'Index';
    public $method_name  = 'index';

	function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];

		if($this->method == 'GET') {
			$this->data = $_GET;
		} else if($this->method == 'PUT') {
			parse_str(file_get_contents("php://input"), $post_vars);
			$this->data = $post_vars;
		} else {
			$this->data = $_POST;
		}
		
		if (!isset($this->data['args'])) {
			$this->data['args'] = 'index';
		}
	}
}
