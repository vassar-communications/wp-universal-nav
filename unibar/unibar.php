<?php
/*
Plugin Name: Vassar Unibar
Description: Displays universal nav on WordPress blogs
Version: 1.0
Author: Chris Silverman
Author URI: http://csilverman.com/
*/

defined( 'ABSPATH' ) or die( 'You are not supposed to be doing this.');

wp_enqueue_style("style", plugin_dir_url( __FILE__ )."assets/css/unibar.css");

function get_markup($filename, $wrap_start = '', $wrap_end = '') {
	$template_path = plugin_dir_url( __FILE__ );

	$markup = file_get_contents($template_path.$filename);
	if(($wrap_start) && ($wrap_end)) {
		$markup = $wrap_start.$markup.$wrap_end;
	}
	return $markup;
}


/*	Basic function: 
	- set up a buffer
	- grab all generated content
	- inject custom markup right after the opening body tag
	- display it as the last thing PHP does before quitting. 
	
	I'm targeting plugins_loaded and shutdown because these are the earliest/latest hooks there are, and I want to be reasonably sure that my buffer encapsulates any others generated by themes/plugins (although maybe I should start with muplugins_loaded?) 
*/


add_action('plugins_loaded', function() {
	ob_start();
});
add_action('shutdown', function() {
	
	//	Some themes don't add a "logged-in" class to the body tag
	//	This makes sure the admin bar doesn't overlap the content too much

	
	if (!is_user_logged_in()) {
		// echo '<style>.unibar {top: 32px;} html {margin-top:calc(4rem + 32px) !important}</style>';
	}

	$page_markup = ob_get_clean();

	$universal_header_markup = get_markup('header_markup.html', '<div class="unibar">', '</div>');
	$universal_header_markup = '<style>:root{--footer-bg: url("'.plugin_dir_url( __FILE__ ).'assets/images/global-footer-bg.jpg");}</style>'.$universal_header_markup;
	$universal_footer_markup = get_markup('footer_markup.html');
	$page_markup_with_unibar = $page_markup;
	
	$page_markup_with_unibar = preg_replace("/(\<body.*\>)/", "$1".$universal_header_markup, $page_markup_with_unibar);
	$page_markup_with_unibar = preg_replace("/(\<\/body.*\>)/", $universal_footer_markup."$1", $page_markup_with_unibar);

	//	Make sure we don't display the unibar in the admin section
	//	That's just awkward
		
	if (is_admin()) {
		echo $page_markup;
	}
	else echo $page_markup_with_unibar; //$markup_with_unibar;

}, 0);


