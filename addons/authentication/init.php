<?php

/*
*
*/

class Authentication extends Framaddons {
    public $name = 'authentication';
    public $author = 'Stéphane Codazzi';
    public $version = '1.0';
    public $website = 'https://codazzi.fr';
    public $description = "Method for authentication, with API call";
    public $licence = 'MIT';
    
    /*
	*	private $session_user_id = 'auth_user_id';
	*   private $session_token = 'auth_token';
    */
    private $auth = false;
    
    function __construct () {
    	date_default_timezone_set('Europe/Paris');
    	if (isset($_COOKIE['token']) && $_COOKIE['token'] != null) {
    		$this->auth = true;
    	}
    }
    
    /*
    *	Return the current authentication status of the user
    */
    function is_auth () {
    	return $this->auth;
    }
    
    /*
    *	
    */
    function logout () {
    	/* session_destroy ();*/
     	setcookie("key",	"", time() - 1, '/');
    	setcookie("token",	"", time() - 1, '/');
    	setcookie("email",	"", time() - 1, '/');
    	setcookie("user",	"", time() - 1, '/');
    	$this->auth = false;
    }
    
    /*
    *	Call the API for authentified the user.
    *	Return boolean
    */
    function login ($parent, $email, $password) {
    	$res = Apy::call($parent, 'Users', 'GetToken', 'GET', array('email' => $email, 'password'  => $password, 'type' => 'Web'));
    	if (isset($res['error']) && $res['error'] === 0) {
    		setcookie("key", 	$res['key'],	time() + 86400, '/');
    		setcookie("token", 	$res['token'],	time() + 86400, '/');
    		setcookie("email", 	$email,			time() + 86400, '/');
    		setcookie("user", 	$res['user'],	time() + 86400, '/');
    		$this->auth = true;
    	}
    	return $this->auth;
    }
    
    /*
    *	Call the API for create an user
    *	Return true, or error code(CF API)
    */
    function signin ($parent, $email, $password) {
    	return Apy::call($parent, 'Users', 'Create', 'POST', array('email' => $email, 'password'  => $password));
    }
}
?>