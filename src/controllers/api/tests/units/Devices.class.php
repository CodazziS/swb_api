<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/Common.class.php');
require_once (CORE_PATH.'/framzod.php');


$framzod = new \Framzod;
$framzod->prepareclass("api/Devices", 'ApiDevices');
		
use atoum;

/**
 * @engine inline
 */
class ApiDevices extends atoum
{
	private $class_tested;
	private $token = null;
	private $key = null;
	private $user = null;
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->class_tested = $framzod->prepareclass("api/Devices", 'ApiDevices');
    	\Common::deleteAccount("atoum2@smsonline.fr", "@z3rtYu");
    	\Common::initAccount();
    }

}