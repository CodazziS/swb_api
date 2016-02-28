<?php

/*
*
*/

class Apy extends Framaddons {
    public $name = 'Apicheck';
    public $author = 'Stéphane Codazzi';
    public $version = '1.0';
    public $website = 'https://codazzi.fr';
    public $description = "Check if all conditions are valids.";
    public $licence = 'MIT';
    
    function __construct () {
    	
    }
    
    function check ($obj, $conditions) {
    	
    	if (isset($conditions['method'])) {
    		if ($conditions['method'] !== $obj->request->method) {
    			$obj->error = 1;
    			return false;
    		}
    	}
    	
    	if (isset($conditions['fields'])) {
    		foreach($conditions['fields'] as $field) {
    			if (!isset($obj->data[$field])) {
    				$obj->error = 2;
    				$obj->result['missing field'] = $field;
    				return false;
    			}
    		}
    	}
    	
    	if (isset($conditions['authentication'])) {
    		
    		if (isset($obj->data['user']) && isset($obj->data['token'])) {
    			$opt = array(
					'conditions' => array('user_id = ? AND token = ?', $obj->data['user'], $obj->data['token'])
				);
    			$token = Token::find('first', $opt);
    			if (isset($token) && $token != null) {
    				$obj->user_id = $token->user_id;
    			} else {
    				$obj->error = 6;
    				return false;
    			}
    			
    		} else {
    			$obj->error = 2;
    			return false;
    		}
    	}
    	
    	return true;
    }
    
    /*
    *	Simulate a API JSON call
    *	Faster than a real call (no network traffic)
    *		parent: parent class ($this)
    *		api_class: Class to call
    *		api_method: fucntion to call
    *		http_method: Http method (POST,GET,DELETE,UPDATE)
    *		data: array format
    *
    *	Exemple: Apy::call($this, 'users', 'create', 'POST', array());
    * 	Result: The API function result, in json format
    */
    public static function call($parent, $api_class, $api_method, $http_method, $data) {
    	$class_file = SOURCES_PATH.'/controllers/api/' . ucfirst($api_class) . '.class.php';
		require ($class_file);
		$api_class_name = 'Api'.ucfirst($api_class);
		$api_class = new $api_class_name();
		
		/* INIT */
		$api_class->request = new FzRequest();
		$api_class->request->method = $http_method;
		$api_class->data = $data;
		$api_class->addons = $parent->addons;
		
		/* CALL */
		$api_class->{$api_method}();
		$result 			= $api_class->result;
		$result['error'] 	= $api_class->error;
		return $result;
    }

}
?>