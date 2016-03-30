<?php

/*
*
*/

class Lang extends Framaddons {
    public $name = 'Lang';
    public $author = 'Stéphane Codazzi';
    public $version = '1.0';
    public $website = 'https://codazzi.fr';
    public $description = "Add internationnalisation with JSON files.";
    public $licence = 'MIT';
	
	private $content_file;
	
    function __construct () {
    	$lang = DEFAULT_LANG;
    	
    	/* Get default lang */
    	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    	}
    	/* Look if lang is forced */
    	if (isset($_COOKIE['lang']) && $_COOKIE['lang'] !== null) {
    		$lang = $_COOKIE['lang'];
    	}
    	/* Get file */
    	$file_path = LOCALE_PATH . '/' . $lang . '.json';
    	if (file_exists($file_path)) {
    		$this->content_file = file_get_contents($file_path);
    	} else {
    		$lang = DEFAULT_LANG;
	    	$file_path = LOCALE_PATH . '/' . $lang . '.json';
	    	if (file_exists($file_path)) {
	    		$this->content_file = file_get_contents($file_path);
	    	}
    	}
    	setcookie("lang", $lang, time() + 86400, '/');
    }
    
    public function getLang () {
    	/* Json to object */
    	return json_decode($this->content_file, true);
    }
}
?>