<?php

/*
*       Defines to use : (lines need to be copied in config.php)
*           define(EXEMPLE_ADDON_AWERSOME, "%s is a good developper !");
*
*       Defines addons need to have this format : [addon name in uppercase]_ADDON_[var name in uppercase]
*/

class Exemple extends Framaddons {
    public $name = 'Exemple';
    public $author = 'Stéphane Codazzi';
    public $version = '1.0';
    public $website = 'https://codazzi.fr';
    public $description = "Exemple is not a functionnal addon: it's just for have a skelleton for create new addon";
    public $licence = 'MIT';
    
    function __construct () {
        
    }
    
    function init () {
        
    }
    
    function __destruct () {

    }
    
    function myAwersomeFunction () {
        return sprintf(EXEMPLE_ADDON_AWERSOME, $this->author);
    }
}
?>