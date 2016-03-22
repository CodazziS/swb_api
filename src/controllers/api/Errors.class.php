<?php
class ApiErrors extends FzController {
	function __construct() {
		$this->render_class = 'Render';
		$this->title 		= 'Errors codes';
		$this->view			= "api/error.html";
	}
	
	public function index() {
		
		$this->result = array('name' => $this->title, 'docs' => array());
		$this->result['docs'][] = array(
			'name' => 'Error codes',
			'description' => 'List all error codes.',
			'lines' => array(
				
				'-1: Unknow error',
				'0: OK',
				'1: Bad http method',
				'2: Missing argument',
				'3: Email is not valid',
				'4: Password is too short ('.MIN_PASSWORD_LEN.' min)',
				'5: This email is already exist',
				'6: Bad credentials or user not found',
			),
		);
	}
	
	public function resetall() {
		Log::delete_all();
		Message::delete_all();
		Contact::delete_all();
		Device::delete_all();
		Token::delete_all();
	}

}