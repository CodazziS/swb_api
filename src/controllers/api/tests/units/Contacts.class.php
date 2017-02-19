<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/Common.class.php');
require_once (CORE_PATH.'/framzod.php');


$framzod = new \Framzod;
$framzod->prepareclass("api/Contacts", 'ApiContacts');
		
use atoum;

/**
 * @engine inline
 */
class ApiContacts extends atoum
{
    private $class_contact;
	private $token = null;
	private $key = null;
	private $user = null;
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->class_contact = $framzod->prepareclass("api/Contacts", 'ApiContacts');
    	\Common::deleteAccount("atoum2@smsonline.fr", "@z3rtYu");
    	\Common::initAccount();
    }
}