<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/TestsUtils.class.php');
require_once (CORE_PATH.'/framzod.php');


$framzod = new \Framzod;
$framzod->prepareclass("api/Devices", 'ApiDevices');
		
use atoum;

/**
 * @engine inline
 */
class ApiDevices extends atoum
{
	private $fz_inst;
	private $fz_tests_utils;
	private $infos = array(
		'email' => 'testphp@swb.ovh',
		'password' => 'azertyuiop',
		'user' => null,
		'token' => null,
		'key' => null,
		'user' => null,
		);
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->fz_inst = $framzod->prepareclass("api/Devices", 'ApiDevices');
    }
    
    public function testadd () {
    	$this->infos = \TestsUtils::createAndConnect($this->infos);
    	
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Bad Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['android_id'] = 'test123456';
        $this->testedInstance->data['model'] = 'Unit Tests Model';
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
        $this->testedInstance->data['android_id'] = 'test123456';
        $this->testedInstance->data['model'] = 'Unit Tests Model';
        $this
        	->given($this->testedInstance->add())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
    }

	public function testchangename () {
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Bad Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['new_name'] = 'Awersome name';
        $this->testedInstance->data['android_id'] = 'test123456';

        $this
        	->given($this->testedInstance->changename())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['new_name'] = 'Awersome name';
        $this->testedInstance->data['android_id'] = 'test123456';
		
        $this
        	->given($this->testedInstance->changename())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
	}
	
    public function testgetdevices () {
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Bad Credentials */    
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];

        $this
        	->given($this->testedInstance->getdevices())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */    
        $this->testedInstance->request->method = "GET";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
		
        $this
        	->given($this->testedInstance->getdevices())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->phparray['devices']->phparray[0]
	            		->string['android_id']->isEqualTo('test123456')
	            	->phparray['devices']->phparray[0]	
	            		->string['model']->isEqualTo('Unit Tests Model')
	            	->phparray['devices']->phparray[0]	
	            		->string['name']->isEqualTo('Awersome name')
	            	->integer['error']->isEqualTo(0)
        ;
    }
        
    
    public function testremove() {
    	$this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst));
            
        /* Bad Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = 'fff';
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['android_id'] = 'test123456';

        $this
        	->given($this->testedInstance->remove())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isNotEqualTo(0)
        ;
        
        /* Good Credentials */    
        $this->testedInstance->request->method = "POST";
        $this->testedInstance->data['token'] = $this->infos['token'];
        $this->testedInstance->data['user'] = $this->infos['user'];
        $this->testedInstance->data['android_id'] = 'test123456';
		
        $this
        	->given($this->testedInstance->remove())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['error']->isEqualTo(0)
        ;
        
    	\TestsUtils::deleteAccount($this->infos);
    }
}