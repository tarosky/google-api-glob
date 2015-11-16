<?php
/*
Plugin Name: Google API Glob
Plugin URI: https://github.com/tarosky/google-api-glob
Description: Plugin to dump google analytics data
Author: Takahashi Fumiki<ftakahashi@tarosky.co.jp>
Version: 1.0
Author URI: https://tarosky.co.jp
*/

// Works only on WP_CLI.
require __DIR__ . '/vendor/autoload.php';

\Hametuha\GapiWP\Loader::load();

if ( defined( 'GAG_SRC_DIR' ) ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\Tarosky\GoogleApiGlob\Bootstrap::init();
	}
} else {
	add_action( 'admin_notices', function(){
		echo '<div class="error"><p>You should define constant <code>GAG_SRC_DIR</code>.</p></div>';
	});
}
