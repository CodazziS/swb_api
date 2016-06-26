<?php
/* 
**      configs.inc.php need to be rename to configs.php
*/

/*
*       SITE_NAME can be user for prefixed title pages, descriptions, ...
*/
define("SITE_NAME", "SWB API");

/*
*		SITEVERSION is the config of your site.
*
*		dev : Show error in HTML + reset OPcache (for all sites)
*		test : No error in HTML + reset OPcache (for all sites)
*		prod : No error
*/
define("SITEVERSION", 'dev'); // dev test prod

/*      @TODO
*       Choose addons to load at eatch requests.
*       For better performance choose only the addons who are used.
*       For load an addon for a specific request, you can use the function "@TODO" for load it manualy
*/
$GLOBALS['ADDONS_ENABLE'] = [
    'apy',
    'authentication',
    'crypto',
    //'date',
    'lang',
    'phpar',
    //'random',
    'render'
    //'exemple'
];

/*
*		Default file, class and method to load
*		This class will be load when 404 error, or no param in the URL
*
*/
define('ERROR404_CLASS', "AppIndex");
define('ERROR404_CLASSFILE', "Index");
define('ERROR404_METHOD', "index");

/*
*	TODO
*/

/*
*		Here is the various paths, 
*		you are not suppose to modify them
*/

define("ROOT_PATH", dirname(__FILE__));
define("ADDON_PATH", ROOT_PATH.'/addons');
define("SOURCES_PATH", ROOT_PATH.'/src');
define("CORE_PATH", ROOT_PATH.'/core');
define("CONTENT", ROOT_PATH.'/content');
define("RESOURCES_PATH", ROOT_PATH.'/res');
define("LOCALE_PATH", RESOURCES_PATH.'/locales');

/* 
*	Your's Defines
*
*/

define("PHPAR_ADDON_MODEL_DIR", SOURCES_PATH . "/models/");

define("PHPAR_ADDON_DB_DEV", 'pgsql://database:password@localhost/localhost');
define("PHPAR_ADDON_DB_TEST", 'pgsql://database:password@localhost/localhost');
define("PHPAR_ADDON_DB_PROD", 'pgsql://database:password@localhost/localhost');

define("PHPAR_ADDON_DB_ENV", 'development');
define("MIN_PASSWORD_LEN", 5);
define("LOG_ALL", true);
define("DEFAULT_LANG", 'fr');



?>