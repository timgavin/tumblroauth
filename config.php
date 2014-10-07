<?php
	
	session_start();
	
	// create a fake user account for this example
	$_SESSION['usr']['id'] = 1;
	
	// define path to root and site url
	defined('SITE_ROOT') ? null : define('SITE_ROOT', dirname(__FILE__));
	defined('SITE_URL')  ? null : define('SITE_URL',  'http://YOUR-DOMAIN-NAME.com');
	
	// define database credentials
	defined('DB_HOST') ? null : define('DB_HOST', 'localhost');
	defined('DB_USER') ? null : define('DB_USER', '');
	defined('DB_PASS') ? null : define('DB_PASS', '');
	defined('DB_NAME') ? null : define('DB_NAME', '');
	
	// Include ezSQL core (database class)
	require_once SITE_ROOT.'/includes/classes/ezSQL/shared/ez_sql_core.php';
	require_once SITE_ROOT.'/includes/classes/ezSQL/mysqli/ez_sql_mysqli.php';
	$db = new ezSQL_mysqli(DB_USER,DB_PASS,DB_NAME,DB_HOST);
	
	// include the tumblr OAuth class
	require_once SITE_ROOT.'/includes/classes/oauth/tumblroauth.php';
	
	// include functions
	require_once SITE_ROOT.'/includes/functions.php';
	
	// define Tumblr OAuth
	// register your app and get key & token at http://www.tumblr.com/oauth/apps
	defined('TUMBLR_OAUTH_KEY')      ? null : define('TUMBLR_OAUTH_KEY', 'YOUR-OAUTH-KEY');
	defined('TUMBLR_OAUTH_SECRET')   ? null : define('TUMBLR_OAUTH_SECRET', 'YOUR-OAUTH-SECRET');
	defined('TUMBLR_OAUTH_CALLBACK') ? null : define('TUMBLR_OAUTH_CALLBACK', SITE_URL.'/auth/tumblr_oauth.php?callback=true');