<?php

namespace tests\units;

require_once (__DIR__ . '/../../../../../config.php');
require_once (__DIR__ . '/Common.class.php');
require_once (CORE_PATH.'/framzod.php');

$framzod = new \Framzod;
$framzod->prepareclass("api/Users", 'ApiUsers');
		
use atoum;

/**
 * @engine inline
 */
class ApiUsers extends atoum
{
	private $class_tested;
	private $token = null;
	private $key = null;
	private $user = null;
	
	function __construct() {
        parent::__construct();
        $framzod = new \Framzod;
    	$this->class_tested = $framzod->prepareclass("api/Users", 'ApiUsers');
    	\Common::deleteAccount("atoum2@smsonline.fr", "@z3rtYu");
    	\Common::initAccount();
    	
    }
    
    public function testcreate () {
        /* Bad Email, good password */    
        $this->class_tested->request->method      = "POST";
        $this->class_tested->data['type']         = 'test';
        $this->class_tested->data['password']     = "@z3rtYu";
        $this->class_tested->data['email']        = 'a';
        
        $this->class_tested->create();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(3);
        
        /* Bad password, Good email */
        $this->class_tested->request->method      = "POST";
        $this->class_tested->data['type']         = 'test';
        $this->class_tested->data['password']     = 'a';
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->create();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(4);
        
        /* Good password, Good email */
        $this->class_tested->request->method      = "POST";
        $this->class_tested->data['type']         = 'test';
        $this->class_tested->data['password']     = "@z3rtYu";
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->create();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        
        /* Account already exist */
        $this->class_tested->request->method      = "POST";
        $this->class_tested->data['type']         = 'test';
        $this->class_tested->data['password']     = "@z3rtYu";
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->create();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(5);
    }
    
    public function testgettoken () {
        /* Bad password */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['type']         = 'web';
        $this->class_tested->data['password']     = "@z3rtfYu";
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->gettoken();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(6);
        
        /* good password, web */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['type']         = 'web';
        $this->class_tested->data['password']     = "@z3rtYu";
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->gettoken();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        $this->integer($res['user'])->isNotEqualTo(0);
        $this->integer($res['api_version'])->isEqualTo(API_VERSION);
        $this->string($res['token'])->isNotEmpty();
        $this->string($res['key'])->isNotEmpty();
        
        /* good password, android */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['device_id']    = 'atoumid';
        $this->class_tested->data['device_model'] = 'atoummodelx';
        $this->class_tested->data['type']         = 'android';
        $this->class_tested->data['password']     = "@z3rtYu";
        $this->class_tested->data['rev_name']     = "aifdjfdsm";
        $this->class_tested->data['email']        = "atoum2@smsonline.fr";
        
        $this->class_tested->gettoken();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        $this->integer($res['user'])->isNotEqualTo(0);
        $this->integer($res['revision'])->isEqualTo(0); // New Device
        $this->integer($res['api_version'])->isEqualTo(API_VERSION);
        $this->string($res['token'])->isNotEmpty();
        $this->string($res['key'])->isNotEmpty();
        $this->token = $res['token'];
        $this->key = $res['key'];
        $this->user = $res['user'];
    }
    
    public function testgetinfos () {
        /* Bad credentials */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['user']         = $this->user;
        $this->class_tested->data['token']        = "fdfgghd";
        $this->class_tested->data['key']          = $this->key;

        $this->class_tested->GetInfos();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(6);
        
        /* Good credentials */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['user']         = $this->user;
        $this->class_tested->data['token']        = $this->token;
        $this->class_tested->data['key']          = $this->key;

        $this->class_tested->GetInfos();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        $this->integer($res['messages'])->isEqualTo(0);
        $this->integer($res['messages_unread'])->isEqualTo(0);
        $this->integer($res['contacts'])->isEqualTo(0);
    }
    
    public function testunread () {
        /* Bad credentials */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['user']         = $this->user;
        $this->class_tested->data['token']        = "fdfgghd";
        $this->class_tested->data['key']          = $this->key;

        $this->class_tested->GetUnread();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(6);
        
        /* Good credentials */
        $this->class_tested->request->method      = "GET";
        $this->class_tested->data['user']         = $this->user;
        $this->class_tested->data['token']        = $this->token;
        $this->class_tested->data['key']          = $this->key;

        $this->class_tested->GetUnread();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        $this->integer($res['messages_unread'])->isEqualTo(0);
    }
    
    public function testdelete () {
        /* Bad password */
        $this->class_tested->request->method = "POST";
        $this->class_tested->data['password'] = "@z3rfdffff";
        $this->class_tested->data['email'] = "atoum2@smsonline.fr";
        $this->class_tested->delete();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(6);
        
        /* good */
        $this->class_tested->request->method = "POST";
        $this->class_tested->data['password'] = "@z3rtYu";
        $this->class_tested->data['email'] = "atoum2@smsonline.fr";
        $this->class_tested->delete();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(0);
        
        /* account not found */
        $this->class_tested->request->method = "POST";
        $this->class_tested->data['password'] = "@z3rtYu";
        $this->class_tested->data['email'] = "atoum2@smsonline.fr";
        $this->class_tested->delete();
        $res = $this->class_tested->get_result();
        $this->integer($res['error'])->isEqualTo(6);
    }
}