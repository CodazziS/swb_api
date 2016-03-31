<?php 
	function is_ext($name, $err='ERROR') {
		if (extension_loaded($name)) {
			echo 'OK';
		} else {
			echo $err;
		}
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
		
		</style>
	</head>
	
	<body>
		<h1>Welcome to Framzod Framework</h1>
		
		<h2>Requierements</h2>
		<div class="explain">
			[<?php is_ext('mcrypt'); ?>] MCRYPT extention<br />
			[<?php is_ext('mysqli', 'WARN'); ?>] MYSQLI extention (optionnal)<br />
			[<?php is_ext('pgsql', 'WARN'); ?>] PGSQL extention (optionnal)<br />
		</div>
		
		
		<h2>Configuration.</h2>
		<div class="explain">
		<?php
			if (file_exists('config.php')) {
		?>
			[OK] The configuration is ready
		<?php
			} else {
		?>
			[ERROR] You need to copy config.inc.php to config.php and edit for your convenience.
		<?php
			}
		?>
		</div>
		
		<h2>Serveur configuration</h2>
		<h3>Apache configuration</h3>
		<div class="explain">
			Rewrite mod need to be enable. <br />
			After, you need to create (or add in existant) an htaccess file with rewrites rules: <br />
			
			<pre>
rewrite ^/(.+\..+)$ /$1 last;
rewrite ^/(.*)/(.*)$ /index.php?class=$1&method=$2 last;
rewrite ^/(.+)$ /index.php?class=$1 last;
			</pre>
		</div>
		<h3>Nginx configuration</h3>
		<div class="explain">
			Add the rewrites rules in your site configuration :
			
			<pre>
rewrite ^/$ /main.php last;
rewrite ^/(.+\..+)$ /$1 last;
rewrite ^/(.*)/(.*)$ /main.php?class=$1&method=$2 last;
rewrite ^/(.+)$ /main.php?class=$1 last;
			</pre>
		</div>
	</body>
</html>