<?php

class TestsUtils {
	
	
	static function createAccount ($infos) {
		$framzod = new \Framzod;
    	$usr_instance = $framzod->prepareclass("api/Users", 'ApiUsers');
    	
    	$usr_instance->request->method = "POST";
    	$usr_instance->data['type'] = 'test';
        $usr_instance->data['password'] = $infos['password'];
        $usr_instance->data['email'] = $infos['email'];
        $usr_instance->create();
	}
	
	static function connectAccount ($infos) {
		$framzod = new \Framzod;
    	$usr_instance = $framzod->prepareclass("api/Users", 'ApiUsers');
    	
    	$usr_instance->request->method = "GET";
    	$usr_instance->data['type'] = 'test';
        $usr_instance->data['password'] = $infos['password'];
        $usr_instance->data['email'] = $infos['email'];
        $usr_instance->gettoken();
        $res = $usr_instance->get_result();
        $infos ['user'] = $res['user'];
        $infos ['token'] = $res['token'];
        $infos ['key'] = $res['key'];
        return $infos;
	}
	
	static function addDevice($infos) {
		$framzod = new \Framzod;
    	$usr_instance = $framzod->prepareclass("api/Devices", 'ApiDevices');
    	
		$usr_instance->request->method = "POST";
        $usr_instance->data['token'] = $infos['token'];
        $usr_instance->data['user'] = $infos['user'];
        $usr_instance->data['device_id'] = $infos['device_id'];
        $usr_instance->data['model'] = 'Unit Tests Model';
        $usr_instance->add();
	}
	
	static function createAndConnect ($infos) {
		TestsUtils::createAccount($infos);
		return TestsUtils::connectAccount($infos);
	}
	
	static function deleteAccount ($infos) {
		$framzod = new \Framzod;
    	$usr_instance = $framzod->prepareclass("api/Users", 'ApiUsers');
    	
    	$usr_instance->request->method = "POST";
    	$usr_instance->data['type'] = 'test';
        $usr_instance->data['password'] = $infos['password'];
        $usr_instance->data['email'] = $infos['email'];
        $usr_instance->delete();
	}
}