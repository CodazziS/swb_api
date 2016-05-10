<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (CORE_PATH.'/framzod.php');

$framzod = new \Framzod;
$framzod->prepareclass("api/Users", 'ApiUsers');
		
use atoum;

/**
 * @engine inline
 */
class ApiUsers extends atoum
{
	private $fz_inst;
	private $email = 'testphp@swb.ovh';
	private $password = 'azertyuiop';
	private $token = null;
	private $key = null;
	private $user = null;
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->fz_inst = $framzod->prepareclass("api/Users", 'ApiUsers');
    }
    
    public function createAccount() {
    	$this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
    	$this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->create())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
    }
    
    public function testcreate () {
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Bad Email, google password */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = 'a';
        $this
        	->given($this->testedInstance->create())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Bad password, Good email */
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = 'a';
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->create())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good password, Good email */
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->create())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
        
        /* Account already exist */
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->create())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
    }
    
    public function testgettoken () {
    	$this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
    	/* Get fake token */
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['password'] = 'addddd';
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->gettoken())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(6)
        ;
      
        /* Get token */
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->gettoken())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
	            	->integer['user']->isGreaterThan(0)
	            	->string['token']->isNotEmpty()
        ;
        
        $this->user = $this->testedInstance->get_result()['user'];
        $this->token = $this->testedInstance->get_result()['token'];
        $this->key = $this->testedInstance->get_result()['key'];
    }
    
    public function testgetinfos () {
    	$this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Get fake */
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['user'] = $this->user;
        $this->testedInstance->data['token'] = "444";
        $this->testedInstance->data['key'] = $this->key;
        $this
        	->given($this->testedInstance->getinfos())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
            
    	/* Get infos */
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['user'] = $this->user;
        $this->testedInstance->data['token'] = $this->token;
        $this->testedInstance->data['key'] = $this->key;
        $this
        	->given($this->testedInstance->getinfos())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
	            	->integer['messages']->isGreaterThan(-1)
	            	->integer['messages_unread']->isGreaterThan(-1)
	            	->integer['contacts']->isGreaterThan(-1)
        ;
    }
    
    public function testdelete () {
    	$this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
    	/* Delete Account */
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->delete())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
        
        /* Delete inexistant Account */
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['password'] = $this->password;
        $this->testedInstance->data['email'] = $this->email;
        $this
        	->given($this->testedInstance->delete())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
    }
}