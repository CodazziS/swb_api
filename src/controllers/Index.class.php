<?php
class Index extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Home Website';
		$this->view			= "index.html";

	}

	public function index() {
		$this->result['name'] = 'Stephane';
	}
}