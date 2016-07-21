<?php 
    $conf = false;
    
	function is_ext($name, $err='ERROR') {
		if (extension_loaded($name)) {
			echo 'OK';
		} else {
			echo $err;
		}
	}
	
    if (!empty($_POST)) {
        $conf = true;
    }	

?>

<html>
	<head>
		<title>Ready for install Framzod</title>
		
		<style>
		
			* {
				margin: 0;
				padding: 0;
			}
			
			body {
				background-color: #ECEFF1;
			}
			
			h1 {
				width: 100%;
				text-align: center;
				background-color: #607D8B;
				color: white;
				border-bottom: 2px solid #263238;
				margin-bottom: 20px;
			}
			
			h2 {
				width: 100%;
				text-align: center;
				background-color: #78909C;
				color: white;
				border-bottom: 2px solid #37474F;
				margin-bottom: 0px;
			}
			h3 {
				width: calc(100% - 30px);
				padding-left: 30px;
				text-align: left;
				background-color: #B0BEC5;
				color: black;
				margin-bottom: 0px;
			}
			
			.explain {
				width: 60%;
				margin-right: auto;
				margin-left: auto;
				background-color: white;
				border: 1px solid #546E7A;
				box-shadow: 2px 2px 2px #546E7A;
				margin-top: 10px;
				margin-bottom: 10px;
				padding: 10px;
			}
			
			.form {
			    width: 600px;
			    padding: 20px;
			    box-shadow: 2px 2px 5px #000000;
			    margin-left: auto;
			    margin-right: auto;
			}
			
			label {
			    width: 200px;
			    display: inline-block;
			    margin-top: 5px;
			    margin-bottom: 5px;
			}
			
			input[type="text"], input[type="password"], input[type="number"] {
			    width: 350px;
			}
			select {
			    margin-left: -4px; /* :) */
			    width : 350px;
			}
		
		</style>
	</head>
	
	<body>
		<h1>Welcome to SmsOnline Installer</h1>
		
		<h2>Requierements</h2>
		<div class="explain">
			[<?php is_ext('mcrypt'); ?>] MCRYPT extention<br />
			[<?php is_ext('mysqli'); ?>] MYSQLI extention<br />
			
		</div>
		
		
		<?php if ($conf): ?>
		    <h2>Configuration created</h2>
		    <h3>Config file</h3>
    		<div class="explain">
    			Copy paste lines in your config.php file:
    			<br />
    			<br />
    			<pre>
                    define("SITE_NAME", "<?php echo $_POST['site_name']; ?>");
                    define("SITEVERSION", '<?php echo $_POST['site_version']; ?>'); // dev test prod
                    $GLOBALS['ADDONS_ENABLE'] = [
                        'apy',
                        'authentication',
                        'crypto',
                        'lang',
                        'phpar',
                        'render'
                    ];

                    define('ERROR404_CLASS', "AppIndex");
                    define('ERROR404_CLASSFILE', "Index");
                    define('ERROR404_METHOD', "index");

                    define("ROOT_PATH", dirname(__FILE__));
                    define("ADDON_PATH", ROOT_PATH.'/addons');
                    define("SOURCES_PATH", ROOT_PATH.'/src');
                    define("CORE_PATH", ROOT_PATH.'/core');
                    define("CONTENT", ROOT_PATH.'/content');
                    define("RESOURCES_PATH", ROOT_PATH.'/res');
                    define("LOCALE_PATH", RESOURCES_PATH.'/locales');

                    define("PHPAR_ADDON_MODEL_DIR", SOURCES_PATH . "/models/");

                    define("PHPAR_ADDON_DB_PROD", 'mysql://<?php echo $_POST['db_login']; ?>:<?php echo $_POST['db_password']; ?>@<?php echo $_POST['db_host']; ?>/<?php echo $_POST['db_name']; ?>');
                    define("PHPAR_ADDON_DB_ENV", 'production');
                    define("MIN_PASSWORD_LEN", <?php echo $_POST['pass_len']; ?>);
                    define("LOG_ALL", false);
                    define("DEFAULT_LANG", '<?php echo $_POST['default_lang']; ?>');
    			</pre>
    		</div>
		
		<?php else: ?>
	        <h2>Configuration</h2>
	        <div class="form">
    	        <form method="post" action="#">
    	            <h3>Default config</h3>
    	            <label for="site_name">Site name (Api)</label><input type="text" name="site_name" id ="site_name" value="SmsOnline Api" /><br />
    	            <label for="site_version">Site version</label>
    	            <select name="site_version" id="site_version">
    	                <option value="prod">Production (no error displayed)</option>
    	                <option value="test">Test (no error displayed, reset OPcache)</option>
    	                <option value="dev">Development (Error displayed, reset OPcache)</option>
    	            </select>
    	            <br />
    	            <label for="pass_len">Password length</label><input type="number" name="pass_len" id ="pass_len" value="5" /><br />
    	            <label for="default_lang">Default language (Need exist)</label><input type="text" name="default_lang" id ="default_lang" value="en" /><br />
                    <br />
    	            <h3>Database config</h3>
    	            <label for="db_login">Database login</label><input type="text" name="db_login" id ="db_login" value="swb_online" /><br />
    	            <label for="db_password">Database password</label><input type="password" name="db_password" id ="db_password" value="" /><br />
    	            <label for="db_host">Database host</label><input type="text" name="db_host" id ="db_host" value="localhost" /><br />
    	            <label for="db_name">Database name</label><input type="text" name="db_name" id ="db_name" value="swb_online" /><br />

    	            <input type="submit" value="Execute" />
    	        </form>
	        </div>
	        
		<?php endif; ?>
	</body>
</html>