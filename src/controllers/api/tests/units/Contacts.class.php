<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/TestsUtils.class.php');
require_once (CORE_PATH.'/framzod.php');


$framzod = new \Framzod;
$framzod->prepareclass("api/Contacts", 'ApiContacts');
		
use atoum;

/**
 * @engine inline
 */
class ApiContacts extends atoum
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
    	$this->fz_inst = $framzod->prepareclass("api/Contacts", 'ApiContacts');
    }

    public function testadd () {
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
        $this->testedInstance->data['contacts'] = json_encode(array(
        	array(
        		'address'   => '1111111111', 
        		'image'     => '', 
        		'name'      => 'First Contact', 
        		),
        	array(
        		'address'   => 'SWB', 
        		'image'     => '', 
        		'name'      => 'SWB', 
        		),	
        	));
        $this
        	->given($this->testedInstance->add())
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
        $this->testedInstance->data['contacts'] = json_encode(array(
        	array(
        		'address'   => '1111111111', 
        		'image'     => '', 
        		'name'      => 'First Contact', 
        		),
        	array(
        		'address'   => 'SWB', 
        		'image'     => '', 
        		'name'      => 'SWB', 
        		),	
        	));
        $this
        	->given($this->testedInstance->add())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
    }
    
    public function testgetcontacts () {
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
        	->given($this->testedInstance->getcontacts())
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
        	->given($this->testedInstance->getcontacts())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->phparray['address']->phparray[0]
	            		->string['android_id']->isEqualTo($this->infos['android_id'])
            		->phparray['address']->phparray[0]
	            		->string['address']->isEqualTo('1111111111')
	            	->phparray['address']->phparray[0]
	            		->string['name']->isEqualTo('First Contact')
	            	->phparray['address']->phparray[1]
	            		->string['android_id']->isEqualTo($this->infos['android_id'])
            		->phparray['address']->phparray[1]
	            		->string['address']->isEqualTo('SWB')
	            	->phparray['address']->phparray[1]
	            		->string['name']->isEqualTo('SWB')
	            	->integer['error']->isEqualTo(0)
        ;
    }

    public function testgetactive() {
        
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
        	->given($this->testedInstance->getactive())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */    
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['key'] = $this->infos['key'];
    
    
        //$this->testedInstance->getactive();
        //var_dump($this->testedInstance->get_result());
        /*
        $this
        	->given($this->testedInstance->getactive())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->phparray['address']->phparray[0]
	            		->string['android_id']->isEqualTo($this->infos['android_id'])
            		->phparray['address']->phparray[0]
	            		->string['address']->isEqualTo('1111111111')
	            	->phparray['address']->phparray[0]
	            		->string['name']->isEqualTo('First Contact')
	            	->phparray['address']->phparray[1]
	            		->string['android_id']->isEqualTo($this->infos['android_id'])
            		->phparray['address']->phparray[1]
	            		->string['address']->isEqualTo('SWB')
	            	->phparray['address']->phparray[1]
	            		->string['name']->isEqualTo('SWB')
	            	->integer['error']->isEqualTo(0)
        ;
        */
        
    	\TestsUtils::deleteAccount($this->infos);
    }
}