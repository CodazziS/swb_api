<?php

/*
*       Defines to use : (lines need to be copied in config.php)
*
*/

class Render extends Framaddons {
    public $name = 'Render';
    public $author = '-';
    public $version = '1.0';
    public $website = 'http://twig.sensiolabs.org/';
    public $description = "The render class use the Twig engine.";
    public $licence = 'Cf twig Licence';
    
    private $twig;
    
    function __construct () {
        require_once 'lib/Twig/Autoloader.php';
        Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem(SOURCES_PATH.'/views');
		$this->twig = new Twig_Environment($loader, array(
			'cache' => false,
		));
    }
    
    function __destruct () {

    }
    
    function render ($fzclass) {
    	echo $this->twig->render($fzclass->view, $fzclass->result);
    }
}
?>