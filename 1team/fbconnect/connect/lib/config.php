<?php

/*   
 *   FACEBOOK CONNECT LIBRARY FUNCTIONS/CLASSES
 */

/*   
 *   FILE INCLUDE PATHS
 *   MAKE SURE THESE PATHS ALL END WITH A FORWARD SLASH
 */

define("appdir", "../");
define("CONNECT_APPLICATION_PATH", appdir."connect/");
define("CONNECT_JAVASCRIPT_PATH", appdir."javascript/");
define("CONNECT_CSS_PATH", appdir."css/");
define("CONNECT_IMG_PATH", appdir."img/");

include_once CONNECT_APPLICATION_PATH . 'facebook-client/facebook.php';
include_once CONNECT_APPLICATION_PATH . 'lib/fbconnect.php';
include_once CONNECT_APPLICATION_PATH . 'lib/core.php';
include_once CONNECT_APPLICATION_PATH . 'lib/user.php';
include_once CONNECT_APPLICATION_PATH . 'lib/display.php';

/*   
 *   FB CONNECT APPLICATION DATA
 */

//$callback_url    = 'https://www.1teamweb.com';
$callback_url    = 'http://localhost/1team/';
$api_key         = '134315679923894';
$api_secret      = '6eed50b5b89d5c7707cfbc175e98ac97';
$base_fb_url     = 'connect.facebook.com';
$feed_bundle_id  = 'your template bundle id';

/*   
 *   SAMPLE BUNDLE DATA
 */

$sample_post_title = "FB Connect Demo";
//$sample_post_url = "https://www.1teamweb.com/fbconnect/connect/";
$sample_post_url  = 'http://localhost/1team/fbconnect/connect/';
$sample_one_line_story = '{*actor*} posted a comment on <a href="{*post-url*}">{*post-title*}</a> and said {*post*}.';
$sample_template_data = '{"post-url":"https://www.1teamweb.com/fbconnect/connect/", "post-title":"FB Connect Demo", "post":"This is so easy to use!"}';

?>