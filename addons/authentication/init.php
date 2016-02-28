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
    
    private $session_user_id = 'auth_user_id';
    private $session_token = 'auth_token';
    private $auth = false;
    
    function __construct () {
    	
    }
    
    /*
    *	Return the current authentication status of the user
    */
    function is_auth () {
    	return $auth;
    }
    
    /*
    *	Call the API for authentified the user.
    *	Return boolean
    */
    function login ($parent, $email, $password) {
    	return Apy::call($parent, 'Users', 'GetToken', 'GET', array('email' => $email, 'password'  => $password));
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