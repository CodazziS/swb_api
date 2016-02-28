<?php
class Framzod {
    public $fz_request = null;
    public $addons = array();

    function __construct ($cron = false) {
        global $FZ_ADDONS;

		/*
		*	Check the version of the website
		*	and show or not the log in html
		*	Reset OPCACHE if site is not in production
		*/
        if (SITEVERSION == 'dev') {
        	opcache_reset();
        	error_reporting(E_ALL);
            ini_set("display_errors", 1);
        } else if (SITEVERSION == 'test') {
        	opcache_reset();
        	ini_set("display_errors", 0);
            error_reporting(0);
        } else {
            ini_set("display_errors", 0);
            error_reporting(0);
        }
        
        
		/*
		*	If the request is a cron job, 
		*	the differents class is not loaded
		*/
        if (!$cron) {
            ini_set("url_rewriter.tags", "");

            //Class
            require_once ('class/framaddons.class.php');
            require_once ('class/request.class.php');
            require_once ('class/controller.class.php');

            $this->fz_request = new FzRequest();
            if (isset($_SERVER['HTTP_HOST']))
                $this->fz_request->url = $_SERVER['HTTP_HOST'];

            $this->fz_request->path = getcwd();

            if (isset($_GET['class']) && $_GET['class'] != '') {
                $this->fz_request->class = ucwords($_GET['class']);
            }

            if (isset($_GET['method']) && $_GET['method'] != '') {
                $this->fz_request->method_name = strtolower($_GET['method']);
            }
        }
        // Include all addons
        foreach ($GLOBALS['ADDONS_ENABLE'] as $addon) {
        	$addon_file = ADDON_PATH.'/'.$addon.'/init.php';
        	if (file_exists($addon_file)) {
        		require_once($addon_file);
        		$addon_class = ucwords($addon);
                $this->addons[$addon_class] = new $addon_class();
        	} else {
        		die('Addon '.$addon.' not found.');
        	}
        }
    }

    function main () {
    	session_start();
        if (file_exists(SOURCES_PATH.'/controllers/'.$this->fz_request->class.'.class.php'))
            require_once(SOURCES_PATH.'/controllers/'.$this->fz_request->class.'.class.php');
        else {
            $this->fz_request->class = ERROR404_CLASSFILE;
            $this->fz_request->method_name = ERROR404_METHOD;
            require_once(SOURCES_PATH.'/controllers/'.$this->fz_request->class.'.class.php');
        }
        /* Création de la classe du site */
        $fz_class = new $this->fz_request->class();
        $fz_class->request = $this->fz_request;
        $fz_class->data = $fz_class->request->data;
        $fz_class->addons = $this->addons;
        $fz_class->{$this->fz_request->method_name}();
        if ($fz_class->render_class == 'Render') {
        	$this->addons[$fz_class->render_class]->render($fz_class);
        } else if ($fz_class->render_class == 'Text') {
        	echo $fz_class->result;
        } else if ($fz_class->render_class == 'Json') {
        	echo json_encode($fz_class->result);
        }

    }
}

