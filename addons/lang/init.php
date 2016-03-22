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

    function __construct () {
    }
    
    public function getLang () {
    
    	/* TODO : Get default lang (cf PHP) */
    	
    	/* Look if lang is forced */
    	
    	/* Get default Lang */
    	
    	/* Get file */
    	$string = file_get_contents(LOCALE_PATH . '/fr.json');
		
    	/* Json to object */
    	return json_decode($string, true);
    }
}
?>