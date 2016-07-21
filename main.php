<?php
if (file_exists('config.php')) {
    require_once ('config.php');
    require_once (CORE_PATH.'/framzod.php');
    $framzod = new Framzod();
    $framzod->main();
} else {
    header('Location: /install/index.php');   
}

?>