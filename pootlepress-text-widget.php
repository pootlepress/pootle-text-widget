<?php
/*
Plugin Name: PootlePress Visual Editor Widget
Plugin URI: http://pootlepress.com/
Description: A WordPress widget that has a visual and HTML editor and WooThemes & WooCommerce shortcode buttons
Version: 0.9
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'pootlepress-text-widget-functions.php' );
require_once( 'classes/class-pootlepress-text-widget-plugin.php' );
require_once( 'classes/class-pootlepress-text-widget.php' );

$GLOBALS['pootlepress_text_widget'] = new Pootlepress_Text_Widget_Plugin( __FILE__ );
$GLOBALS['pootlepress_text_widget']->version = '0.9';

?>
