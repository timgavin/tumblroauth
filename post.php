<?php
	
	require_once 'config.php';
	
	// process submitted form...
	if(isset($_POST['submit'])) {
	
		// clean post data
		$title = filter_var($_POST['title'],FILTER_SANITIZE_STRING);
		$text  = filter_var($_POST['text'],FILTER_SANITIZE_STRING);

		// if the post to tumblr checkbox has been ticked...
		if(isset($_POST['tumble'])) {
			
			// get user's tumblr tokens from our database
			if($oauth = $db->get_row("SELECT oauth_token,oauth_secret FROM users_oauth WHERE uo_usr_id = ".$db->escape($_SESSION['usr']['id'])." AND oauth_provider = 'tumblr'")) {
			
				// build the tumblr oauth object with the user's tokens from database
				if($tumblroauth = new TumblrOAuth(TUMBLR_OAUTH_KEY, TUMBLR_OAUTH_SECRET, $oauth->oauth_token, $oauth->oauth_secret)) {
												
					// build the POST data into an array for sending to tumblr
					// type = 'regular' tells tumblr this is a text post
					// tumblr will not accept the post if the values are empty
					// you could also accept meta tags, photos, videos, etc., as well as linking back to your site
					// see the tumblr api for more: https://www.tumblr.com/docs/en/api/v2#posting
					$tumblr_post = array(
						'type'		=> 'regular',
						'title'		=> $title,
						'body' 		=> $text,
						'generator' => SITE_URL
					);
					
					// get the user's tumblr username
					$user = $tumblroauth->get('http://api.tumblr.com/v2/user/info');
					
					// get the user's tumblr blog url
					foreach($user->response->user->blogs as $item){
						$blog_url = $item->url;
					}
					
					// create the post url
					// remove the protocal for posting to the api
					// note: $blog_url already has a trailing slash...
					$post_url = str_replace('http://', '', $blog_url);
				
					// send post data to tumblr
					$response = $tumblroauth->post('http://api.tumblr.com/v2/blog/'.$post_url.'post', $tumblr_post);
					
					// make sure we get a 'Created' response from tumblr
					if($response->meta->msg == 'Created') {
						
						// this is where you could redirect the user or display success message after a successful post
						echo '<div class="alert alert-success">';
						echo '	<h3>Success</h3>';
						// let's insert a link so the user can view their post on tumblr
						echo '	<p><a href="'.$blog_url.'.post/'.$response->response->id.'" target="_blank"><button class="btn btn-success">View Post</button></a></p>';
						echo '</div>';
					} else {
						echo '<div class="alert alert-danger">Could not post to Tumblr. Perhaps the API is down?</div>';
					}
				} else {
					echo '<div class="alert alert-danger">Could not authenticate user.</div>';
				}
			} else {
				// user does not have tumblr tokens
				echo '<div class="alert alert-warning">You need to authenticate with Tumblr first!</div>';
			}
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Tumblr PHP OAuth Example</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
		<link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
		<style>
			.btn-tumblr {
				background-color: #3E5A70;
				border-color: #3E5A70;
				color: #fff;
			}
			.btn-tumblr:hover,
			.btn-tumblr:focus,
			.btn-tumblr:active,
			.btn-tumblr.active,
			.open .dropdown-toggle.btn-tumblr {
				background-color: #2E485D;
				border-color: #2E485D;
				color: #fff;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<?php if(check_tumblr_oauth_access($db)): ?>
						<!-- user has authenticated with tumblr, show the form -->
						<label class="label label-success">You're connected!</label>
						<h2>Post Something</h2>
						<form action="post.php" method="post" accept-charset="UTF-8" role="form">
							<div class="form-group">
								<input type="text" name="title" class="form-control" placeholder="Title" value="My New Post">
							</div>
							<div class="form-group">
								<textarea name="text" class="form-control" placeholder="Text" rows="5">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</textarea>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" name="tumble" id="tumble" value="1" checked> Send to Tumblr
								</label>
							</div>
							<button type="submit" name="submit" class="btn btn-primary">Submit</button>
						</form>
					<?php else: ?>
						<!-- user has not authenticated with tumblr. show 'connect with tumblr' button -->
						<h2>Please connect to Tumblr</h2>
						<p><a href="auth/tumblr_oauth.php?redirect=true" title="Authorize our site with Tumblr"><button class="btn btn-tumblr btn-lg"><i class="fa fa-tumblr"></i> connect with tumblr</button></a></p>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</body>
</html>