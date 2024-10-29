<?php
/**

Plugin Name: Athena Post Expiration

Plugin URI: http://brightthought.co/athena

Description: Set expiration date on various post type or create a expiration profile and assign it to any available post type.

Author: Bright Thought, LLC

Author URI: http://brightthought.co

Version: 2.0.1

Text Domain: athena-post-expiration

Domain Path: /languages

License: GPLv3

License URI: https://www.gnu.org/licenses/gpl.html

*/

// Exits on direct access
if ( ! defined('ABSPATH') ) { exit('Permission Denied'); }

// Get the composer autoload file
require_once( __DIR__ . '/vendor/autoload.php');

// Sets the necessary constants
\Athena\Utils\Constants::$ATHENA_DIR = __DIR__;
\Athena\Utils\Constants::$ATHENA_URI = plugin_dir_url(__FILE__ );

// Registers the un-install hook for clean up after deactivation
register_uninstall_hook(\Athena\Utils\Constants::$ATHENA_DIR, ['\Athena\Services\ActivateDeactivate', 'deactivate'] );

try {
	// Create the dependency container
	$builder = new \DI\ContainerBuilder();
	$container = $builder->build();

	// Initiates the application
	$app = $container->make( \Athena\Services\App::class, [
		'container' => $container
	]);

	// Builds the application
	add_action('init', function() use ($app) {
		$app->start();
	});
} catch ( \Exception $e ) {
	wp_die($e->getMessage());
}


