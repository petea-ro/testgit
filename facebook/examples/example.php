<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require '../src/facebook.php';
Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

// Create our Application instance (replace this with your appId and secret).
$facebook = new Facebook(array(
  'appId'  => '377900518581',
  'secret' => 'e3451e3d3a3f7f7986046176fe866538',
));

try {

 	//$fql = 'SELECT url, id, type, site FROM object_url WHERE url = "http://poftabuna.ro/restaurant/gambinos-pizza-sectorul-3/avis.html?c=188#188"';
      $fql = 'SELECT share_count, like_count, comment_count, total_count
				FROM link_stat
				WHERE  "http://poftabuna.ro/restaurant/gambinos-pizza-sectorul-3/avis.html?c=188#188" IN url
			  	ORDER BY like_count';
		$ret_obj = $facebook->api(array(
                                   'method' => 'fql.query',
                                   'query' => $fql,
                                 ));

        // FQL queries return the results in an array, so we have
        //  to get the user's name from the first element in the array.
       // echo '<pre>Name: ' . $ret_obj[0]['name'] . '</pre>';

	   var_dump($ret_obj);
      } catch(FacebookApiException $e) {
	  	//var_dump($e);
			echo $e->getMessage();
    }

	die();

/*
$user_profile = $facebook->api('/127053782629');
echo '<pre>'.print_r($user_profile,true).'</pre>';
*/

//$tocken = $facebook->getAccessToken();
/*$result = $facebook->api('/100001733300875/feed/', array('limit'=>'10'));
var_dump($result);
*/
	/*
foreach($result['data'] as $post)
{
	$postMore =  $facebook->api('/'.$post['id'].'/likes',
		array('access_token' => $facebook->access_token));
	echo '<pre>'.print_r($postMore,true).'</pre>';
	echo $post['id'] . ' has '.count($postMore['data']).' likes<br/>';
}
          */




// Get User ID
$user = $facebook->getUser();




// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

// Login or logout url will be needed depending on current user state.
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
} else {
  $loginUrl = $facebook->getLoginUrl(array('scope'=>'read_stream'));  //
}

// This call will always work since we are fetching public data.
$naitik = $facebook->api('/navalny');

?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
  	<meta charset='utf-8'>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>

    <?php if ($user): ?>
      <a href="<?php echo $logoutUrl; ?>">Logout</a>
    <?php else: ?>
      <div>
        <a href="<?php echo $loginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre></pre>

    <?php if ($user): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">
      <h3>Your User Object (/me)</h3>

    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>




     <hr/>
 	<?php
	 if($user) {
        $user_id = $user_profile['id'];

      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.

       // $fql = 'SELECT name, email FROM user WHERE uid = ' . $user_id;
        //$fql = 'SELECT name FROM user WHERE uid = me()';
        //$fql = 'SELECT uid2, uid1 FROM friend WHERE uid1 =me()';
        //$fql = 'SELECT uid, name, pic_square FROM user WHERE uid = me() OR uid IN (SELECT uid2 FROM friend WHERE uid1 = me())';
       // $fql = 'SELECT  uid, name, first_name, middle_name, last_name, sex, locale, pic_small_with_logo, pic_big_with_logo, pic_square_with_logo, pic_with_logo, username
	 // FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1 = me())';
        //$fql = 'SELECT user_id FROM like WHERE object_id="poftabunaro"';

		//$fql = 'SELECT name, fan_count FROM page WHERE page_id = 265587713452742';
		//$fql = 'SELECT name, fan_count,personal_info FROM page WHERE username = "poftabunaro"';
		//$fql = 'SELECT user_id FROM like WHERE object_id="265587713452742"';
		//$fql = ' SELECT url FROM url_like WHERE user_id = me() ';

		/*$fql = 'SELECT share_count, like_count, comment_count, total_count
				FROM link_stat
				WHERE  "http://poftabuna.ro/restaurant/gambinos-pizza-sectorul-3/avis.html?c=188#188" IN url
			  	ORDER BY like_count
				LIMIT 10';   */

		/*$fql = 'SELECT url FROM link WHERE link_id
			IN ( SELECT object_id FROM like WHERE "http://poftabuna.ro" IN url)';*/

		   /*	$fql = 'SELECT link_id FROM link WHERE "http://poftabuna.ro" IN url'; */
		  //	$fql = 'SELECT url, id, type, site FROM object_url WHERE url = "http://poftabuna.ro/restaurant/gambinos-pizza-sectorul-3/avis.html?c=188#188"';

		//$fql = "SELECT domain_id FROM domain WHERE domain_name='poftabuna.ro'"; //10150262949737647

		//$fql = "SELECT metric, value FROM insights WHERE object_id=265587713452742 AND metric='domain_widget_likes' AND period=period('lifetime') AND end_time=end_time_date('2011-06-26') " ;

		//$fql = 'SELECT user_id FROM like WHERE object_id="265587713452742"';

		//$fql = 'SELECT url, id, type, site FROM object_url WHERE id = 10150262949737647';

    }
	?>

   <!--	<h3>Public profile of Naitik</h3>
    <img src="https://graph.facebook.com/536890991/picture">
    <?php echo $naitik['name']; ?>
   -->
  </body>
</html>
