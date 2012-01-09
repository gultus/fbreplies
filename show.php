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

if(!isset($_POST['submit']) || !isset($_POST['startdate']) || !isset($_POST['enddate'])){
	
	echo('You have not reached this page in a valid form. Kindly go to the homepage by <a href="index.php?code='.$_REQUEST['code'].'&state='.$_REQUEST["state"].'">clicking here</a> and fill in the appropriate values and click on Go');
	//echo("</header></body></html>");
	exit();
}

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
idx(FBUtils::fetchFromFBGraph("me/friends?access_token=$token"), 'data')
);

//Posts on the user's wall
//Need to add filter_key = 'others' in WHERE clause to not show posts 
$posts = FBUtils::fql(
"SELECT post_id, permalink,actor_id, target_id, message,description,comments,likes,app_id,action_links,attachment FROM stream WHERE source_id = me() AND strlen(description) = 0 AND created_time >= ".$_REQUEST['startdate']." AND created_time <= ".$_REQUEST['enddate'],
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

<link rel="stylesheet" href="css/example.css" TYPE="text/css" MEDIA="screen">
<link href="css/facebox.css" media="screen" rel="stylesheet" type="text/css" />
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
<p id="picture" style="background-image: url(https://graph.facebook.com/me/picture?type=square&access_token=<?php echoEntity($token) ?>)"></p>
<div>
	<h1>Welcome, <strong><?php echo idx($basic, 'name'); ?></strong></h1>
	<p class="tagline">Add the required posts to Selected tab and click on selected tab<br/>
	<?php 
		echo('<a href="index.php?code='.$_REQUEST['code'].'&state='.$_REQUEST["state"].'">Click here</a> to go back to homepage to reselect the start and end times<br/>');	
	?>	
	</p>
</div>

<br/><br/>

<div id="wrapper">
	<ul id="menu">
		<li class="active"><a href="#showSelected">Selected</a></li>
		<li><a href="#showAvailable">Available</a></li>
		<li><a href="#showUnwanted">Unwanted</a></li>
	</ul>
 
    <div class="content" id="showSelected">
		<div id="selected">
			<div id="selected-msg"></div>
			<div class="button-container">
				<a href="javascript:(void);" onclick="move('selected','available');" class="facebook-button">Add to Available Tab</a>
				<a href="javascript:(void);" onclick="move('selected','unwanted');" class="facebook-button">Add to Unwanted Tab</a>
				<a href="#moveinfo" rel="facebox">?</a>
			</div>
			<div class="match-container">
				<input type="text" id="selected-regex"/>
				<select id="selected-andor">
					<option value="and" selected="selected">AND</option>
					<option value="or">OR</option>
				</select>
				<input type="text" id="selected-regex-1"/>
				<a href="javascript:(void);" onclick="matchPosts('selected');" class="facebook-button">Match</a>
				<a href="#matchinfo" rel="facebox">?</a>
			</div>
			<form id="target" action="post.php?code=<?php echo($_REQUEST["code"]);?>&state=<?php echo($_REQUEST["state"]);?>" method="post">
				Default Message for reply:<br/><textarea rows="1" cols="50" id="default-msg" name="default-msg"></textarea>
				<input type="checkbox" name="action-like" id="action-like" /> Like
				<input type="checkbox" name="action-comment" id="action-comment" /> Comment
				<input type="hidden" name="post-list" id="post-list"/>
				<input type="submit" name="submit" value="Go" />
				<a href="#postinfo" rel="facebox">?</a>
			</form>	
			
		</div>		    
    </div>
    <div class="content" id="showAvailable">
		<div id="available">
			<div id="available-msg"></div>
			<div class="button-container">
				<a href="javascript:(void);" onclick="move('available','selected');" class="facebook-button">Add to Selected Tab</a>
				<a href="javascript:(void);" onclick="move('available','unwanted');" class="facebook-button">Add to Unwanted Tab</a>
				<a href="#moveinfo" rel="facebox">?</a>
			</div>
			<div class="match-container">
				<input type="text" id="available-regex"/>
				<select id="available-andor">
					<option value="and" selected="selected">AND</option>
					<option value="or">OR</option>
				</select>
				<input type="text" id="available-regex-1"/>
				<a href="javascript:(void);" onclick="matchPosts('available');" class="facebook-button">Match</a>
				<a href="#matchinfo" rel="facebox">?</a>
			</div>
			<?php
			$friendlist = array();
			$friendlist[$my_id]="You";
			foreach ($friends as $friend) {
				$id = checkNum((string)idx($friend, 'id'));
				$name = idx($friend, 'name');
				$friendlist[$id]=$name;
			}
			?>

			<?php

			foreach($posts as $post){
				$id = checkNum((string)idx($post, 'actor_id'));
				$message = idx($post,'message');
				$post_id = idx($post,'post_id');
				$desc = idx($post,'description');
				$permalink = idx($post,'permalink');
				$comments = idx($post,'comments');
				$likes = idx($post,'likes');
				$attachment = idx($post,'attachment');
				if(!array_key_exists($id,$friendlist)){
					$id = nearestfriend($id,$friendlist);
				}
				if(array_key_exists('media',$attachment)){
					$minheight = '73px';
					$msgwidth = '520px';
				}else{
					$minheight = '53px';
					$msgwidth = '600px';
				}
				echo('<div class="post-container deselected" id="'.$post_id.'" style="min-height:'.$minheight.';background-image:url(\'https://graph.facebook.com/'.$id.'/picture?type=square\');">');
					echo('<div id="'.$post_id.'-content" class="post-content" style="min-height:'.$minheight.';">');
						echo('<div class="actor-name">');
							echo('<a href="http://www.facebook.com/'.$id.'" target="_blank">'.$friendlist[$id].'</a>');
						echo('</div>');
						echo('<div class="actual-post-content">');
							if(array_key_exists('media',$attachment)){
								$media = array_values(idx($attachment,'media'));
								$href = $media[0]['href'];
								$src = $media[0]['src'];
								echo('<div class="post-attachment">');
									echo('<a href="'.$href.'" target="_blank"><img src="'.$src.'" class="attachment-thumb"/></a>');
								echo('</div>');
							}
							echo('<div id="'.$post_id.'-msg" class="post-message" style="width:'.$msgwidth.';">');
								echo($message);
							echo('</div>');
						echo('</div>');
					echo('</div>');
					if(array_key_exists('media',$attachment))
						echo('<div style="display:block;width:100%">&nbsp;</div>');
					echo('<div class="post-stats">');
						echo('<div class="post-chkbox">');
							echo('<input type="checkbox" name="postid" value="'.$post_id.'" />');
						echo('</div>');
						echo('<div class="post-counts" style="display:inline-block;">');
							echo('<a href="'.$permalink.'" target="_blank" class="post-counts-link">likes('.$likes['count'].') comments('.$comments['count'].')</a>');
						echo('</div>');
						echo('<div id="'.$post_id.'-toggle-link" class="hidebox">');
							echo('<a id="'.$post_id.'-msg-link" href="javascript:void;" onclick="toggleCustomMessage(\''.$post_id.'\');" style="margin-left:5px" class="post-counts-link">Add Custom Message</a>');
						echo('</div>');
					echo('</div>');
					echo('<div style="margin-left:55px;" id="'.$post_id.'-custom-msg-box-container" class="hidebox"">');
						echo('<textarea rows="1" cols="70" id="'.$post_id.'-custom-msg-box"></textarea>');
					echo('</div>');
					echo('<hr/>');
				echo('</div>');
				
				/*echo('<script>');
					echo('var el = document.getElementById("'.$post_id.'-content");');
					echo('el.addEventListener("click", handle, false);');
				echo('</script>');*/
			}
			?>
		</div>
    </div>
    <div class="content" id="showUnwanted">
		<div id="unwanted">
			<div id="unwanted-msg"></div>
			<div class="button-container">
				<a href="javascript:(void);" onclick="move('unwanted','selected');" class="facebook-button">Add to Selected Tab</a>
				<a href="javascript:(void);" onclick="move('unwanted','available');" class="facebook-button">Add to Available Tab</a>
				<a href="#moveinfo" rel="facebox">?</a>
			</div>
			<div class="match-container">
				<input type="text" id="unwanted-regex"/>
				<select id="unwanted-andor">
					<option value="and" selected="selected">AND</option>
					<option value="or">OR</option>
				</select>
				<input type="text" id="unwanted-regex-1"/>
				<a href="javascript:(void);" onclick="matchPosts('unwanted');" class="facebook-button">Match</a>
				<a href="#matchinfo" rel="facebox">?</a>
			</div>		
		</div>    
    </div>
</div>
</header>
<div id="matchinfo" style="display:none;">
<b>Rules for matching and filtering</b>
<ul>
<li>- You can enter two search queries to filter your posts which match the criteria.</li>
<li>- Searching is case-insensitive.</li>
<li>- You can leave one of them empty in which case only the other one that is filled is used.</li>
<li>- You can leave both of them blank to match all the posts in the current view.</li>
<li>- If using two search terms you can combine the search terms by an AND or OR.</li>
	<ul>
		<li>- AND - posts matching both the search criteria are selected.<br/>
		"Happy" AND "Birthday" selects "Happy Birthday", "Wish you a happy birthday" but not "Hope you had a great birthday", "Happy New Year"</li>
		<li>- OR - posts matching any one of the two search criteria are selected<br/>
		"Happy" OR "Birthday" selects "Happy Birthday", "Wish you a happy birthday", "Hope you had a great birthday", "Happy New Year"</li>
	</ul>
</ul>
<br/><br/><br/>
<b>Advanced Filtering - Regular Expressions</b>
<ul>
	<li>- In the search terms you can give a regular expression instead of a string</li>
	<li>- Simply enter a regular expression like b.*day. Don't enter in /b.*day/ style as the slashes are automatically added before searching.</li>
	<li>- One basic regular expression even novices can use is .* if you are searching for <b>abc.*def</b> this indicates that you want all the matching posts which have abc and def in it and .* denotes any no. of characters present in between them. </li>
	<li>- For instance h.*pp matches posts containing any of happy, happie, hippo.</li>
</ul>
</div>
<div id="moveinfo" style="display:none;">
<ul>
	<li>- Select the posts from the current move and click one of the buttons to move to that particular tab.</li>
	<li>- You can select posts by either manually selecting them or searching for patterns by using the Match button.</li>
	<li>- By default all the messages are shown in the Available tab.</li>
	<li>- Selected tab is the tab from where you will have the option to reply to your posts.</li>
	<li>- Only posts that are added to the Selected tab are processed, the rest two tabs are ignored.</li>
	<li>- The Unwanted tab really doesn't have any significant purpose. It is just an auxilary space provided to filter out posts which you are almost sure you don't want to process. This is of great help in manual selection.</li>
</ul>
</div>

<div id="postinfo" style="display:none">
	<ul>
		<li>- Select the like option if you want to like all the posts displayed in the current tab.</li>
		<li>- Select the comment option if you want to reply to the posts by posting comments on them.</li>
		<li>- You can add a default comment to all the posts by adding the desired text to textbox titled "Default Message for reply".</li>
		<li>- You can choose a custom message to a particular post by clicking the link "Add Custom Message" beneath the desired post.</li>
		<li>- Once you have set the desired texts for replies click on the Go button.</li>
	</ul>
	<br/>
	Note: The checkbox present under each post has no impact on processing the posts, it is there just to allow you to move the posts to other tabs.
</div>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
	<script src="js/jquery.tabify.js" type="text/javascript" charset="utf-8"></script>
	<script src="js/jquery.autoresize.js"></script>
	<script src="js/script.js"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#menu').tabify();
	});
	
	$('.post-content').click(function() {
		var chkbox = $(this).parent().find(':checkbox');
		if(chkbox.attr('checked')){
			chkbox.attr('checked',false);
			$(this).parent().removeClass("selected");
			$(this).parent().addClass("deselected");
		}else{
			chkbox.attr('checked',true);
			$(this).parent().removeClass("deselected");
			$(this).parent().addClass("selected");
		}
	});
</script>
	
<script>
	$('#target').submit(function() {
	if($('#selected').find('.post-content').size() == 0){
		alert("There are no posts in the selected tab");
		return false;
	}else{
		var default_empty = false;
		var custom_one_empty = false;
		var custom_all_empty = true;
		if($('#default-msg').val().length == 0){
			default_empty = true;
		}
		$('#selected').find('textarea[id*="-custom-msg-box"]').each(function(index) {
			if($(this).val().length == 0){
				custom_one_empty = true;
			}else{
				custom_all_empty = false;
			}
		});
		if(!$('#action-comment').attr('checked') && !$('#action-like').attr('checked')){
			alert("You need to select at least one of the two actions like/comment");
			return false;
		}
		if($('#action-comment').attr('checked')){
			if(default_empty && custom_all_empty){
				alert("You don't have a default message set and all your custom messages are also empty.\n\n Set at least one of the messages or uncheck the option to post comments");
				return false;
			}
			
			if(default_empty && custom_one_empty){
				if(!confirm("You don't have have a default message and at least one of your custom messages is empty.\n Do you wish to continue?"))
					return false;
			}
		}
	}
	var jsonmsg = '{';
	$('#selected').find('.post-container').size();
	$('#selected').find('.post-container').each(function(index) {
		var id = $(this).attr('id');
		if($('#action-comment').attr('checked')){
			var msg = $(this).find('textarea[id*="-custom-msg-box"]').val();
		}else{
			var msg = '';
		}
		if(index>0){
			jsonmsg += ',';
		}
		jsonmsg += '"'+id+'":"'+msg+'"';
	});
	jsonmsg += '}';
	$('#post-list').val(jsonmsg);
	return true;
	});
</script>	
<script>
	$('input:checkbox[name="postid"]').click(function(){
		var id = $(this).attr('value');
		var div = $('#'+id);
		if($(this).attr('checked')){
			div.removeClass("deselected");
			div.addClass("selected");			
		}else{
			div.removeClass("selected");
			div.addClass("deselected");	
		}
	});	
	
</script>
<script>

$('textarea').autoResize({
	onBeforeResize: function(){
		console.log('Before');
		$(this).css('background', 'red');
	},
	onAfterResize: function(){
		console.log('After');
		$(this).css('background', '');
	}
});

</script>

  <script src="js/facebox.js" type="text/javascript"></script>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      $('a[rel*=facebox]').facebox({
        loadingImage : 'images/loading.gif',
        closeImage   : 'images/closelabel.png'
      })
    })
  </script>
  
</body>
</body>
</html>
