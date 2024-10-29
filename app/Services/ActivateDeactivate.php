<?php

namespace Athena\Services;

use Athena\Utils\Constants;

/**
 * Handles any activation or deactivation tasks
 *
 * @author erik@brightthought.co
 * @since 2.0.0
 */
class ActivateDeactivate
{
	/**
	 * Handles the removal of all data associated with the plugin
	 *
	 * @return void
	 */
	public static function deactivate()
	{
		global $wpdb, $table_prefix;

		// Gets all post metadata that needs to be removed
		$meta = $wpdb->get_results( 'SELECT * FROM ' . $table_prefix . 'postmeta WHERE meta_key LIKE "%athena%"', ARRAY_A );
		if ( is_array( $meta ) ) {
			foreach ( $meta as $value ) {
				delete_post_meta( $value['post_id'], $value['meta_key'] );
			}
		}

		// Get posts and profile option data
		$profiles = get_option( Constants::$ATHENA_PROFILE, false );
		$posts    = get_option( Constants::$ATHENA_POST, false );

		// Checks if profiles were found and clears the hooks
		if ( ! empty( $profiles ) && is_array( $profiles ) ) {
			foreach ( $profiles as $p ) {
				wp_clear_scheduled_hook( 'athena_profile_ex_' . $p['id'], array( $p['id'] ) );

				if ( isset( $p['email'] ) && $p['email'] === true ) {
					wp_clear_scheduled_hook( 'athena_profile_email_' . $p['id'], array( $p['id'] ) );
				}
			}
		}

		// Checks if posts were found and clears the hooks
		if ( ! empty( $posts ) && is_array( $posts ) ) {
			foreach ( $posts as $p ) {
				wp_clear_scheduled_hook( 'athena_post_ex_' . $p, array( $p ) );
			}
		}

		// Queries for all athena profiles
		$args     = [
			'post_type'      => 'athenaprofile',
			'posts_per_page' => - 1

		];
		$profiles = get_posts( $args );

		// Deletes all profiles that were created.
		foreach ( $profiles as $key ) {
			delete_option( 'athena_profile_' . $key->ID );
			wp_delete_post( $key->ID );
		}

		// Delete options on deactivation
		foreach ( [Constants::$ATHENA_OPT,Constants::$ATHENA_POST, Constants::$ATHENA_PROFILE] as $c ) {
			delete_option($c);
		}
	}
}
