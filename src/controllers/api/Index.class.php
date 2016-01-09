<?php
class ApiIndex extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home API';
		$this->view			= "api/index.html";

	}

	public function index() {
		echo "NO GOOD";
	}
	
	public function ok() {
		echo "OK GOOD";
	}
}