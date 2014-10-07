<?php
	
	// include our configuration file
	require_once '../config.php';
	
	// step 1: user has clicked the sign in link, redirect to tumblr for authorization
	if(isset($_GET['redirect'])) {
		
		// build a TumblrOAuth object using our application's keys
		$connection = new TumblrOAuth(TUMBLR_OAUTH_KEY, TUMBLR_OAUTH_SECRET);
		
		// get temporary credentials from tumblr
		$request_token = $connection->getRequestToken(TUMBLR_OAUTH_CALLBACK);
		
		// save temporary credentials to a session for use in step 2
		$_SESSION['oauth_token'] = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		
		// if connection is successful, build authorization link and redirect to tumblr so the user can authorize our app
		if($connection->http_code == 200) {
			header('location:'.$connection->getAuthorizeURL($request_token['oauth_token']));
		} else {
			// could not connect to tumblr. is the API down? did we forget to register our application?
			die('Could not connect to Tumblr.');
		}
	}
	
	
	// step 2: user returns from tumblr...
	if(isset($_GET['callback'])) {
		if(!isset($_GET['oauth_verifier'])) {
			// chances are the user rejected the authorization by clicking the deny button on tumblr's auth page
			die('<a href="tumblr_oauth.php?redirect=true">Connect with tumblr</a>');
		} else {
			// if the oauth_token is old redirect to the connect page and get a new one
			if(!empty($_REQUEST['oauth_token']) && ($_SESSION['oauth_token'] !== $_REQUEST['oauth_token'])) {
				header('location:tumblr_oauth.php?redirect=true');
			} else {
				// user is attempting to authorize our app with their tumblr blog
				// create TumblroAuth object with temporary credentials
				$connection = new TumblrOAuth(TUMBLR_OAUTH_KEY, TUMBLR_OAUTH_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
				
				// request the user's access tokens from tumblr
				$user_access_tokens = $connection->getAccessToken($_REQUEST['oauth_verifier']);
				
				// save the user's access tokens to a session. We'll save them in the database in the next step
				$_SESSION['access_tokens'] = $user_access_tokens;
				
				// discard temporary tokens
				unset($_SESSION['oauth_token']);
				unset($_SESSION['oauth_token_secret']);
				
				// the user has been verified!
				if($connection->http_code == 200) {
					
					// now we create a new TumblrOauth object with the user's tokens
					$connection = new TumblrOAuth(TUMBLR_OAUTH_KEY, TUMBLR_OAUTH_SECRET, $user_access_tokens['oauth_token'], $user_access_tokens['oauth_token_secret']);
					
					// next we retrieve their tumblr info
					$user = $connection->get('http://api.tumblr.com/v2/user/info');
					
					// if the user has been found, we insert the user's tokens and tumblr username into our database
					// you could also check to see if the user has already registered your app with their tumblr blog and refresh their tokens instead
					if($user) {
						$query = "
						INSERT IGNORE users_oauth (
							 uo_usr_id
							,oauth_provider
							,oauth_username
							,oauth_token
							,oauth_secret
						) VALUES (
							".$db->escape($_SESSION['usr']['id']).",
							'tumblr',
							'".$db->escape((string)$user->response->user->name)."',
							'".$db->escape($user_access_tokens['oauth_token'])."',
							'".$db->escape($user_access_tokens['oauth_token_secret'])."'
						)";
						$db->query($query);
						
						// transaction successful, redirect to account page, home page, post page, etc.
						header('location:'.SITE_URL.'/post.php');
					} else {
						die('Failed to connect to Tumblr. <a href="tumblr_oauth.php?redirect=true">Try again</a>');
					}
				} else {
					die('Tumblr authorization failed. <a href="tumblr_oauth.php?redirect=true">Try again</a>');
				}
			}
		}
	}