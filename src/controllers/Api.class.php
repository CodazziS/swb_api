<?php


class Api extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home API';
		$this->view			= "api/index.html";
	}
	
	public function __call ($method, $args) {
		$class_file = 'api/'.ucfirst($method).'.class.php';
		require ($class_file);
		$api_class_name = 'Api'.ucfirst($method);
		$api_class = new $api_class_name();
		$api_class->{$this->data['args']}();
		
		$this->result 		= $api_class->result;
		$this->render_class = $api_class->render_class;
		$this->title 		= $api_class->title;
		$this->view			= $api_class->view;
    }

}


?>