<?php
/* 
**      configs.inc.php need to be rename to configs.php
*/

/*
*       SITE_NAME can be user for prefixed title pages, descriptions, ...
*/
define("SITE_NAME", "My awersome website !");

/*      @TODO
*       Choose addons to load at eatch requests.
*       For better performance choose only the addons who are used.
*       For load an addon for a specific request, you can use the function "@TODO" for load it manualy
*/
$ADDONS_ENABLE = [
    //'authentication',
    //'connector',
    //'crypto',
    //'date',
    //'designer',
    //'phpactiverecord',
    //'random',
];
?>