<?php

class Connector {
    function __construct() {
        if (DB_PDO_LIB == "phpactiverecord") {
            $this->launch_phpactiverecord();
        }
    }

    function launch_phpactiverecord () {
        require_once LIBS_PATH.'/phpactiverecord/ActiveRecord.php';
        if (DB_ADAPTER == "mysql") {
            $connections = array(
                'dev'   => DB_ADAPTER.'://'.DB_DEV_USERNAME.':'.DB_DEV_PASSWORD.'@'.DB_DEV_HOST.'/'.DB_DEV_NAME.';charset='.DB_CHARSET,
                'prod'  => DB_ADAPTER.'://'.DB_PROD_USERNAME.':'.DB_PROD_PASSWORD.'@'.DB_PROD_HOST.'/'.DB_PROD_NAME.';charset='.DB_CHARSET,
                'beta'  => DB_ADAPTER.'://'.DB_PROD_USERNAME.':'.DB_PROD_PASSWORD.'@'.DB_PROD_HOST.'/'.DB_PROD_NAME.';charset='.DB_CHARSET,
            );
        } else {
            $connections = array(
                'dev'   => DB_ADAPTER.'://'.DB_DEV_USERNAME.':'.DB_DEV_PASSWORD.'@'.DB_DEV_HOST.'/'.DB_DEV_NAME,
                'prod'  => DB_ADAPTER.'://'.DB_PROD_USERNAME.':'.DB_PROD_PASSWORD.'@'.DB_PROD_HOST.'/'.DB_PROD_NAME,
                'beta'  => DB_ADAPTER.'://'.DB_PROD_USERNAME.':'.DB_PROD_PASSWORD.'@'.DB_PROD_HOST.'/'.DB_PROD_NAME,
            );
        }
        ActiveRecord\Config::initialize(function($cfg) use ($connections) {
            $cfg->set_connections($connections);
            $cfg->set_default_connection(SITEVERSION);
        });
    }
}
$notorm = null;