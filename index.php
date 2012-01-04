<?php

// Enforce https on production
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == "http" && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  exit();
}

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to Facebook specific utilities defined in 'FBUtils.php'
require_once('FBUtils.php');
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');
// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

// Log the user in, and get their access token
$token = FBUtils::login(AppInfo::getHome());
if ($token) {

  // Fetch the viewer's basic information, using the token just provided
  $basic = FBUtils::fetchFromFBGraph("me?access_token=$token");
  $my_id = assertNumeric(idx($basic, 'id'));

  // Fetch the basic info of the app that they are using
  $app_id = AppInfo::appID();
  $app_info = FBUtils::fetchFromFBGraph("$app_id?access_token=$token");

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = array_values(
    idx(FBUtils::fetchFromFBGraph("me/likes?access_token=$token&limit=4"), 'data')
  );

  // This fetches 4 of your friends.
  $friends = array_values(
    idx(FBUtils::fetchFromFBGraph("me/friends?access_token=$token&limit=4"), 'data')
  );

  // And this returns 16 of your photos.
  $photos = array_values(
    idx($raw = FBUtils::fetchFromFBGraph("me/photos?access_token=$token&limit=16"), 'data')
  );

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = FBUtils::fql(
    "SELECT uid, name, is_app_user, pic_square FROM user WHERE uid in (SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1",
    $token
  );

  // This formats our home URL so that we can pass it as a web request
  $encoded_home = urlencode(AppInfo::getHome());
  $redirect_url = $encoded_home . 'close.php';

  // These two URL's are links to dialogs that you will be able to use to share
  // your app with others.  Look under the documentation for dialogs at
  // developers.facebook.com for more information
  $send_url = "https://www.facebook.com/dialog/send?redirect_uri=$redirect_url&display=popup&app_id=$app_id&link=$encoded_home";
  $post_to_wall_url = "https://www.facebook.com/dialog/feed?redirect_uri=$redirect_url&display=popup&app_id=$app_id";
} else {
  // Stop running if we did not get a valid response from logging in
  exit("Invalid credentials");
}
?>

<!-- This following code is responsible for rendering the HTML   -->
<!-- content on the page.  Here we use the information generated -->
<!-- in the above requests to display content that is personal   -->
<!-- to whomever views the page.  You would rewrite this content -->
<!-- with your own HTML content.  Be sure that you sanitize any  -->
<!-- content that you will be displaying to the user.  idx() by  -->
<!-- default will remove any html tags from the value being      -->
<!-- and echoEntity() will echo the sanitized content.  Both of  -->
<!-- these functions are located and documented in 'utils.php'.  -->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">

    <!-- We get the name of the app out of the information fetched -->
    <title><?php echo(idx($app_info, 'name')) ?></title>
    <link rel="stylesheet" href="css/screen.css" media="screen">

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content=""/>
    <meta property="og:type" content=""/>
    <meta property="og:url" content=""/>
    <meta property="og:image" content=""/>
    <meta property="og:site_name" content=""/>
    <?php echo('<meta property="fb:app_id" content="' . AppInfo::appID() . '" />'); ?>
	<link rel="stylesheet" media="all" type="text/css" href="css/jquery-ui-1.8.16.custom.css" />
	<style>
	/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
	</style>
    <script>
      function popup(pageURL, title,w,h) {
        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);
        var targetWin = window.open(
          pageURL,
          title,
          'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left
          );
      }
    </script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js" type="text/javascript"></script>
			<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>
  <script>
	$(function(){
		$('#date1').datetimepicker();
		$('#date2').datetimepicker();
		$('#date2').datetimepicker('setDate', (new Date()) );
	});
  </script>
    <!--[if IE]>
      <script>
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <header class="clearfix">
      <!-- By passing a valid access token here, we are able to display -->
      <!-- the user's images without having to download or prepare -->
      <!-- them ahead of time -->
      <p id="picture" style="background-image: url(https://graph.facebook.com/me/picture?type=normal&access_token=<?php echoEntity($token) ?>)"></p>

      <div>
        <h1>Welcome, <strong><?php echo idx($basic, 'name'); ?></strong></h1>
        <p class="tagline">
			Pick a starting and ending time, they should not be separated by more than 48 hours.<br/>
			Click on Go and you will be presented with list of all posts in that time range.<br/>
			You can reply to all or some selected posts at once.<br/>
        </p>
      </div>
      <div style="display:block;margin-top:50px;">
      <br/>
      Start Date/Time: <input type="text" name="date1" id="date1" value="" />
      End Date/Time: <input type="text" name="date2" id="date2" value="" />
      <form id="target" method="post" action="show.php?code=<?php echo($_REQUEST["code"]);?>&state=<?php echo($_REQUEST["state"]);?>#showAvailable-tab" style="display:inline">
		<input type="hidden" name="startdate" id="startdate"/>
		<input type="hidden" name="enddate" id="enddate"/>
		<input type="submit" name="submit" value="Go"/>
      </form>
      </div>
    </header>
<script>
	$('#target').submit(function() {
		var ErrorMsg = "";
		var date1 = $('#date1').datetimepicker('getDate');
		if(date1 == null){
			ErrorMsg += "Start Date/Time cannot be empty";			
		}
		var date2 = $('#date2').datetimepicker('getDate');
		if(date2 == null){
			if(ErrorMsg.length > 0) ErrorMsg += "\n";
			ErrorMsg += "End Date/Time cannot be empty";			
		}		
		if(ErrorMsg.length > 0){
			alert(ErrorMsg);	
			return false;
		}
		var ts1 = Math.round(date1.getTime()/1000);
		var ts2 = Math.round(date2.getTime()/1000);
		if(ts1 >= ts2){
			alert("Ending Time should be before starting time");
			return false;
		}
		if(ts2-ts1 > 172817){
			alert("Starting and Ending times should not differ by more than 48 hours");
			return false;
		}
		$('#startdate').val(ts1);
		$('#enddate').val(ts2);
		return true;
	});
</script>
  </body>
</html>
