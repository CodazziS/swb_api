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
		
		/* INIT */
		$api_class->request = $this->request;
		$api_class->data = $this->data;
		$api_class->addons = $this->addons;
		
		/* LOG */
		if (LOG_ALL && isset($_GET['args'])) {
			$params = $this->data;
			if (isset($params['password'])) {
				$params['password'] = '****';
			}
			
			$log = new Log();
			$log->time = time();
			$log->date = date("d/m/Y H:i:s");
			$log->fonction = $method . '/' . $_GET['args'];
			$log->json = json_encode($params);
			$log->save();
		}
		
		/* CALL */
		if (isset($_GET['args'])) {
			$api_class->{$_GET['args']}();
		} else {
			$api_class->index();
		}
		
		
		
		$this->result 			= $api_class->result;
		$this->result['error'] 	= $api_class->error;
		$this->render_class 	= $api_class->render_class;
		$this->title 			= $api_class->title;
		$this->view				= $api_class->view;
    }

}


?>