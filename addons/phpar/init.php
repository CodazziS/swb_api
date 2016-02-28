<?php

/*
*       Defines to use : (lines need to be copied in config.php)
*           define(PHPAR_ADDON_MODEL_DIR, SOURCES_PATH . "/models/");
*           define(PHPAR_ADDON_DB_DEV, 'mysql://username:password@localhost/development_database_name');
*           define(PHPAR_ADDON_DB_TEST, 'pgsql://username:password@localhost/development');
*           define(PHPAR_ADDON_DB_PROD, 'sqlite://my_database.db');
*           define(PHPAR_ADDON_DB_ENV, 'development'); // development test production
*
*           For OCI database : 'oci://username:passsword@localhost/xe'
*
*/

class Phpar extends Framaddons {
    public $name = 'Phpar';
    public $author = '-';
    public $version = '1.0';
    public $website = 'http://www.phpactiverecord.org/';
    public $description = "Phpar is an adaptator for init phpActiveRecord";
    public $licence = 'PhpActiveRecord licence';
    
    function __construct () {
    	require_once('ActiveRecord.php');
        ActiveRecord\Config::initialize (function ($cfg) {
            $cfg->set_model_directory (PHPAR_ADDON_MODEL_DIR);
            $cfg->set_connections(
                array(
                    'development'   => PHPAR_ADDON_DB_DEV,
                    'test'          => PHPAR_ADDON_DB_TEST,
                    'production'    => PHPAR_ADDON_DB_PROD
                )
            );
        });
        ActiveRecord\Config::initialize(function($cfg) {
            $cfg->set_default_connection(PHPAR_ADDON_DB_ENV);
        });

        if (is_dir(PHPAR_ADDON_MODEL_DIR)) {
            $models_dir = opendir(PHPAR_ADDON_MODEL_DIR);
            while ($model = readdir($models_dir)) {
                if (!is_dir(PHPAR_ADDON_MODEL_DIR . '/' . $model)) {
                    require_once(PHPAR_ADDON_MODEL_DIR . '/' . $model);
                }
            }
        }
    }
}
?>