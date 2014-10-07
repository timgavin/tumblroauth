<?php
	// check if user is authorized with Tumblr
	function check_tumblr_oauth_access($db) {
		if($oauth = $db->get_row("SELECT oauth_username,oauth_token,oauth_secret FROM users_oauth WHERE uo_usr_id = ".$db->escape($_SESSION['usr']['id'])." AND oauth_provider = 'tumblr'")) {
			$tumblroauth = new TumblrOAuth(TUMBLR_OAUTH_KEY, TUMBLR_OAUTH_SECRET, $oauth->oauth_token, $oauth->oauth_secret);
			$user = $tumblroauth->get('http://api.tumblr.com/v2/user/info');
			if($user->response->user->name == $oauth->oauth_username){
				return true;
			} else {
				return false;
			}
		}
	}