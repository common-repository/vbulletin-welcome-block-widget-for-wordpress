<?php
/*
Plugin Name: vBulletin Welcome Block Widget
Plugin URI: http://www.whatsuplift.net/playground/vbulletin-welcome-block-widget-for-wordpress/
Description: Adds a vBulletin welcome block/login block widget to your blog.
Version: 1.0
Author: Michael Forcer
Author URI: http://www.whatsuplift.net
License: GPL
*/

register_activation_hook(__FILE__,'vbulletin_login_widget_install'); 
register_deactivation_hook( __FILE__, 'vbulletin_login_widget_remove' );

function vbulletin_login_widget_install() {

	add_option("vb_widget_forumpath", '', '', 'yes');
	add_option("vb_widget_avatar_dimensions", '100', '', 'yes');
	add_option("vb_widget_forum_version", '', '', 'yes');
	add_option("vb_widget_absolute_path", '', '', 'yes');

}

function vbulletin_login_widget_remove() {
	
	delete_option('vb_widget_forumpath');
	delete_option('vb_widget_avatar_dimensions');
	delete_option('vb_widget_forum_version');
	delete_option('vb_widget_absolute_path');
	
}

if (is_admin()) {

	function vb_widget_init() {
	
		vb_widget_admin_warning();
	
	}
	
	add_action('init', 'vb_widget_init');

	function vb_widget_admin_menu() {
		
		add_options_page(__('vBulletin Widget', ''), __('vBulletin Widget', ''), 8, str_replace("\\", "/", __FILE__), vb_widget_html_page);

	}
	
	add_action('admin_menu', 'vb_widget_admin_menu');
	
}

function vb_widget_admin_warning() {

	if ( !get_option('vb_widget_forumpath') && !isset($_POST['submit']) ) {
		
		function vb_widget_warning() {
			
			$siteurl = get_option('siteurl');
			
			echo "<div id='vb_widget_warning' class='updated fade'><p><strong>The vBulletin widget is almost ready</strong>. You must enter your forums version and path to avoid errors. <a href='".$siteurl."/wp-admin/options-general.php?page=vbulletin-welcome-block-widget-for-wordpress/vbulletin_login_widget.php'>Click Here</a></p></div>";
		}
		
		add_action('admin_notices', 'vb_widget_warning');
		return;
		
	}
	
}

function vb_widget_html_page() {
?>
	<div>
	<h2>vBulletin Login Widget Options</h2>

	<form method="post" action="options.php">
	<?php wp_nonce_field('update-options'); ?>

	<table width="800">
	
		<tr valign="top">
			<th width="300" scope="row">URL of your forum</th>
			<td width="500">
				<input name="vb_widget_forumpath" type="text" id="vb_widget_forumpath" value="<?php echo get_option('vb_widget_forumpath'); ?>" />
				example: http://www.yoursite.com/forum/
			</td>
		</tr>

		<tr valign="top">
			<th width="300" scope="row">Absolute path to your forum</th>
			<td width="500">
				<input name="vb_widget_absolute_path" type="text" id="vb_widget_absolute_path" value="<?php echo get_option('vb_widget_absolute_path'); ?>" />
				example: /home/username/public_html/forum/
			</td>
		</tr>

		<tr valign="top">
			<th width="300" scope="row">Forum version</th>
			<td width="500">
				<select name="vb_widget_forum_version" id="vb_widget_forum_version">
					<option value="3.x.x" <?php if (get_option('vb_widget_forum_version')=="3.x.x") { echo "selected"; } ?>>3.x.x</option>
					<option value="4.x.x" <?php if (get_option('vb_widget_forum_version')=="4.x.x") { echo "selected"; } ?>>4.x.x</option>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th width="300" scope="row">Maximum avatar dimensions</th>
			<td width="500">
				<input name="vb_widget_avatar_dimensions" type="text" id="vb_widget_avatar_dimensions" value="<?php echo get_option('vb_widget_avatar_dimensions'); ?>" />
			</td>
		</tr>
		
	</table>

	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="vb_widget_forumpath, vb_widget_avatar_dimensions, vb_widget_forum_version, vb_widget_absolute_path" />

	<p></p><input type="submit" value="<?php _e('Save Changes') ?>" /></p>

	</form>
	
	<p>This plugin is still a work in progress. Please send bugs and feature suggestions to: bugs@whatsuplift.net<br /><br /><b>Known issues:</b><br />- Full vBulletin 4 support is still in development.</p>
	</div>
<?php 
}
// begin widget
function widget_vbulletin_login($args, $widget_args = 1) {

	extract( $args, EXTR_SKIP );

	if ( is_numeric($widget_args) )
		
		$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('widget_vbulletin_login');
	
	if ( !isset($options[$number]) )
    	return;

	$title = $options[$number]['title'];
	
	
	echo $before_widget;
	
	if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

	//set some variables
    $forumpath = get_option('vb_widget_forumpath');  
    $maxdimensions = get_option('vb_widget_avatar_dimensions');
    $blogname = get_option('blogname');
    $blogdescription = get_option('blogdescription');
    $vbversion = get_option('vb_widget_forum_version');
    $absolutepath = get_option('vb_widget_absolute_path');

    global $vbulletin, $db, $pmbox, $vbphrase, $session;
    
    //grab path from forum
    //$forumpath = $vbulletin->options['bburl']."/";
    
	$curdir = getcwd ();
	chdir($absolutepath);
	require_once('global.php');
	chdir($curdir);
	
    
    if ($vbulletin->userinfo['userid']!=0) {

    	$userid = $vbulletin->userinfo['userid'];
    	$avatarrev = $vbulletin->userinfo['avatarrevision'];
    	
	
		if ($vbulletin->options['usefileavatar']=="1") {
		
			$file = $forumpath."customavatars/avatar".$userid."_".$avatarrev.".gif";
		
		} else if ($vbulletin->options['usefileavatar']=="0") {
		
			$file = $forumpath."image.php?u=$userid";
	
		}    	

		list($width, $height, $type) = getimagesize($file);

		if ( $width <= $maxdimensions AND $height <= $maxdimensions ) {
			
			echo "<img src=\"$file\" align=\"center\" border=\"0\"><br /><br />";
       
		} else {  
      
		if ($width > $maxdimensions) {
        
        	$ratio = $width / $maxdimensions;
        	$newwidth = $maxdimensions;
        	$newheight = ($height / $ratio);
        
        } else {
        
        	$newheight = $height;
        	$newwidth = $width;
        
        }
        
        if ($newheight <= $maxdimensions ) {
        
        // if current height is ok, were done.
        
        } else if( $newheight >= $maxdimensions ) {

			$ratio2 = $newheight / $maxdimensions;
			$newheight = $maxdimensions;
			$newwidth = ($newwidth / $ratio2);

		} else {

			$ratio2 = $newheight / $maxdimensions;
			$newheight = $maxdimensions; 
			$newwidth = ($newwidth / $ratio2);
		
		}

		echo "<img src=\"$file\" border=\"0\" width=\"$newwidth\" height=\"$newheight\" align=\"center\"><br /><br />"; // display resized pic

	}

	echo "Welcome Back, <b>";
    echo $vbulletin->userinfo['username'];
    echo "!</b>&nbsp;&nbsp;";

    // logout link
    echo "<a href=\"".$forumpath."login.php?$session[sessionurl]do=logout&amp;logouthash=$logouthash";
    echo $vbulletin->userinfo['logouthash'];
    echo "\">";
    echo "Log Out</a><br /><br />";
    
    if ($vbversion == "3.x.x") {
    
    	// Display last visit time and date
    	echo "You last visited: $pmbox[lastvisitdate] at $pmbox[lastvisittime]";
    	echo "<br />";

    	// Display PM Details and generate link to PM box
   		echo "<a href=\"".$forumpath."private.php?$session[sessionurl] \">Private Messages</a>: $vbphrase[unread_x_nav_compiled] $vbphrase[total_x_nav_compiled]";
    	echo "<br /><br />";
    
    } else if ($vbversion == "4.x.x") {
    
    	// Display PM Details and generate link to PM box
    	echo "<a href=\"".$forumpath."private.php\">Private Messages</a>";
    	echo "<br /><br />";
    }

    } else { 

		// not logged in

		echo "<p>Welcome to ".$blogname.", ".$blogdescription.".</p><p>You must <a href=\"".$forumpath."register.php?s=$session[sessionhash]\" target=\"_parent\"><b>register</b></a> before you can use all of the features of the website.</p>";


    	echo "<form action=\"".$forumpath."login.php\" method=post onsubmit=md5hash(vb_login_password,vb_login_md5password,vb_login_md5password_utf)>
		<script type=text/javascript src=\"".$forumpath."/clientscript/vbulletin_md5.js\"></script>
		<table width=\"269\" border=\"0\" cellspacing=\"2\" cellpadding=\"2\">
			<tr>
				<td width=\"135\"><label for=navbar_username>Username:</td>
				<td width=\"120\"><input name=vb_login_username type=text id=navbar_username onfocus=\"if (this.value == '$vbphrase[username]') this.value = '';\" size=20 style=\"width: 155px\" /></td>
			</tr>
			<tr>
				<td><label for=navbar_password>Password:</label></td>
				<td><input name=vb_login_password id=navbar_password type=password size=20 style=\"width: 155px\" /></td>
			</tr>
			<tr>
				<td><label for=cb_cookieuser_navbar>Remember me:</label></td>
				<td><input name=cookieuser type=checkbox id=cb_cookieuser_navbar value=1 checked=checked /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type=submit title=$vbphrase[enter_username_to_login_or_register] value=\"Log in\" /></td>
			</tr>
		</table>
		<input type=hidden name=s value=$session[sessionhash] />
		<input type=hidden name=do value=login />		
		<input type=hidden name=vb_login_md5password />
		<input type=hidden name=vb_login_md5password_utf />
		</form><br />";

	}

    // display stats

    // get total number of threads and posts
	
	//$getstats = $db->query_read('SELECT threadcount, replycount FROM ' . TABLE_PREFIX . 'forum');
	
	$getstats = $vbulletin->db->query_read('SELECT threadcount, replycount FROM ' . TABLE_PREFIX . 'forum');
    
    while ($forum = $db->fetch_array($getstats)) {
	
		$totthreads += $forum['threadcount'];
		$totposts += $forum['replycount'];
    
	}
    
	$totthreads = vb_number_format($totthreads);
	$totposts = vb_number_format($totposts);

    // display total threads and total posts - Uses vB phrases, but change if you like
    echo "Threads: $totthreads &nbsp;&nbsp;&nbsp; Posts: $totposts<br /> ";

    // Only display link and number of new posts if logged in
    if ($vbulletin->userinfo['userid']!=0) {

		// finds number of new posts
		$newposts = $db->query_first("SELECT COUNT(*) AS count FROM " . TABLE_PREFIX . "post AS post " . iif($vbulletin->options['threadmarking'], 'LEFT JOIN ' . TABLE_PREFIX . 'threadread AS threadread ON (threadread.threadid = post.threadid AND threadread.userid = ' . $vbulletin->userinfo['userid'] . ')') . " WHERE dateline >= " . $vbulletin->userinfo['lastvisit'] . iif($vbulletin->options['threadmarking'], ' AND dateline > IF(threadread.readtime IS NULL, ' . (TIMENOW - ($vbulletin->options['markinglimit'] * 86400)) . ', threadread.readtime)'));

		$newposts = vb_number_format($newposts['count']);

		echo "<a href=\"".$forumpath."search.php?$session[sessionurl]do=getnew\">New Posts</a>: $newposts<br />";

	}
    //  end of number of new posts stuff   
    
    echo $after_widget;

}

function widget_vbulletin_login_control($widget_args) {

	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )

		$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
		extract( $widget_args, EXTR_SKIP );

		$options = get_option('widget_vbulletin_login');
		
		if ( !is_array($options) )

			$options = array();

			if ( !$updated && !empty($_POST['sidebar']) ) {
				
				$sidebar = (string) $_POST['sidebar'];

				$sidebars_widgets = wp_get_sidebars_widgets();

				if ( isset($sidebars_widgets[$sidebar]) )
	
					$this_sidebar =& $sidebars_widgets[$sidebar];
					
				else

					$this_sidebar = array();

				foreach ( $this_sidebar as $_widget_id ) {

					if ( 'widget_vbulletin_login' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {

						$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					
					if ( !in_array( "vbulletin_login-$widget_number", $_POST['widget-id'] ) ) unset($options[$widget_number]);
				}
		}

		foreach ( (array) $_POST['widget-vbulletin_login'] as $widget_number => $widget_text ) {
			
			$title = strip_tags(stripslashes($widget_text['title']));
			$options[$widget_number] = compact( 'title' );

		}

		update_option('widget_vbulletin_login', $options);
		$updated = true;

	}

	if ( -1 == $number ) {
		
		$title = '';
		$number = '%i%';

	} else {
		
		$title = attribute_escape($options[$number]['title']);
	
	}
?>
		<p>
			<label for="vbulletin_login-title-<?php echo $number; ?>">Title:</label>
			<input class="widefat" id="vbulletin_login-title-<?php echo $number; ?>" name="widget-vbulletin_login[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			<input type="hidden" id="vbulletin_login-submit-<?php echo $number; ?>" name="vbulletin_login-submit-<?php echo $number; ?>" value="1" />
		</p>
<?php
}

function widget_vbulletin_login_register() {

	// Check for the required API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('widget_vbulletin_login') )
		
		$options = array();
		$widget_ops = array('classname' => 'widget_vbulletin_login', 'description' => '');
		$control_ops = array('id_base' => 'vbulletin_login');
		$name = __('vBulletin Welcome Block');

		$id = false;
		
		foreach ( array_keys($options) as $o ) {
		
    		// Old widgets can have null values for some reason
			if ( !isset($options[$o]['title']) )
				continue;
    
		$id = "vbulletin_login-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'widget_vbulletin_login', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_vbulletin_login_control', $control_ops, array( 'number' => $o ));
	}
  
	// If there are none, we register the widget's existance with a generic template
	if ( !$id ) {
		
		wp_register_sidebar_widget( 'vbulletin_login-1', $name, 'widget_vbulletin_login', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'vbulletin_login-1', $name, 'widget_vbulletin_login_control', $control_ops, array( 'number' => -1 ) );
	}
  
}

add_action( 'widgets_init', 'widget_vbulletin_login_register' );
?>