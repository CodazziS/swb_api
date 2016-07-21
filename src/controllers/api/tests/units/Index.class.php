<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (CORE_PATH.'/framzod.php');

$framzod = new \Framzod;
$framzod->prepareclass("api/Index", 'ApiIndex');
		
use atoum;

class ApiIndex extends atoum
{
	private $fz_inst;
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->fz_inst = $framzod->prepareclass("api/Index", 'ApiIndex');
    }
    
    public function testgetversion ()
    {
        $this
            ->given($this->newTestedInstance)
            ->given($this->testedInstance->copy_attrs($this->fz_inst))
            ->given($this->testedInstance->getversion())
            ->then
	            ->phparray($this->testedInstance->get_result())
	            	->integer['api_version']->isEqualTo(2)
        ;
    }
}