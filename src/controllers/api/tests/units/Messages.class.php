<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/TestsUtils.class.php');
require_once (CORE_PATH.'/framzod.php');


$framzod = new \Framzod;
$framzod->prepareclass("api/Messages", 'ApiMessages');
		
use atoum;

/**
 * @engine inline
 */
class ApiMessages extends atoum
{
    
	private $fz_inst;
	private $fz_tests_utils;
	private $infos = array(
		'email'         => 'testphp@swb.ovh',
		'password'      => 'azertyuiop',
		'user'          => null,
		'token'         => null,
		'key'           => null,
		'user'          => null,
		'android_id'    => "123654789A",
		);
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->fz_inst = $framzod->prepareclass("api/Messages", 'ApiMessages');
    }

    public function testresync () {
    	$this->infos = \TestsUtils::createAndConnect($this->infos);
    	\TestsUtils::addDevice($this->infos);
    	
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
				
        /* Bad Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['android_id'] = $this->infos['android_id'];
        $this->testedInstance->data['key'] = $this->infos['key'];
        $this->testedInstance->data['messages'] = json_encode(array(
        	array(
        	    'id' => '1',
        	    'date'      => '1467635393000',
        		'read'      => '1', 
        		'body'      => 'Hello!', 
        		'address'   => '1111111111', 
        		'type'      => '1', 
        		'date_sent' => '1460731813606', 
        		),
        	array(
        		'id'        => '2',
        	    'date'      => '1467635493000',
        		'read'      => '1', 
        		'body'      => 'Hi ! How are yyou ?!', 
        		'address'   => '1245145', 
        		'type'      => '2', 
        		'date_sent' => '1467635493000',  
        		),
        	array(
        		'id'        => '3',
        	    'date'      => '1467635693000',
        		'read'      => '0', 
        		'body'      => 'Welcome !', 
        		'address'   => 'SWB', 
        		'type'      => '1', 
        		'date_sent' => '1467635693000',  
        		),	
        	));
        $this
        	->given($this->testedInstance->resync())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['android_id'] = $this->infos['android_id'];
        $this->testedInstance->data['key'] = $this->infos['key'];
        $this->testedInstance->data['messages'] = json_encode(array(
        	array(
        	    'id' => '1',
        	    'date'      => '1467635393000',
        		'read'      => '1', 
        		'body'      => 'Hello!', 
        		'address'   => '1111111111', 
        		'type'      => '1', 
        		'date_sent' => '1460731813606', 
        		),
        	array(
        		'id'        => '2',
        	    'date'      => '1467635493000',
        		'read'      => '1', 
        		'body'      => 'Hi ! How are you ?!', 
        		'address'   => '1111111111', 
        		'type'      => '2', 
        		'date_sent' => '1467635493000',  
        		),
        	array(
        		'id'        => '3',
        	    'date'      => '1467635693000',
        		'read'      => '0', 
        		'body'      => 'Welcome !', 
        		'address'   => 'SWB', 
        		'type'      => '1', 
        		'date_sent' => '1467635693000',  
        		),
        	array(
        		'id'        => '4',
        	    'date'      => '1467636693000',
        		'read'      => '1', 
        		'body'      => 'Fine !', 
        		'address'   => '1111111111', 
        		'type'      => '1', 
        		'date_sent' => '1467636693000',  
        		),
        	));
        $this
        	->given($this->testedInstance->resync())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
    }
    
    public function testgetlastsync() {
        
        $this->infos = \TestsUtils::createAndConnect($this->infos);
    	\TestsUtils::addDevice($this->infos);
    	
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
				
        /* Bad Credentials */    
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['key'] = $this->infos['key'];
        $this
        	->given($this->testedInstance->getlastsync())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */  
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['key'] = $this->infos['key'];
        
        $this
        	->given($this->testedInstance->getlastsync())
            ->then
	            ->phparray($this->testedInstance->get_result())
	                ->string['last_message']->isEqualTo('1467636693000')
	                ->string['last_message_unread']->isEqualTo('1467635693000')
	            	->integer['error']->isEqualTo(0)
        ;
    }
    
    // public function testsync() {
        
    // }
    
    // public function testconfirmsent() {
        
    // }
    
    
    
    // public function testgetlastsyncmessage() {
        
    // }
    
    public function testgetmessages() {
        
        
        \TestsUtils::deleteAccount($this->infos);
    }

}