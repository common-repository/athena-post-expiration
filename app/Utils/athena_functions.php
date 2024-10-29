<?php

/* Returns correct array for strtotime function */

function athena_date_time( $time )
{
	return $time === 1 ?
		[
			'd' => 'day',
			'w' => 'week',
			'm' => 'month'
		] :
		[
			'd' => 'days',
			'w' => 'weeks',
			'm' => 'months'
		];
}


/**
 * Formats the date to the correct timezone
 *
 * @param $date
 *
 * @return string
 */
function athena_date_timezone( $date )
{
	try {
		$date_update = new \DateTime( date( 'c', $date ), new \DateTimeZone( date_default_timezone_get() ) );

		$timezone = get_option( 'timezone_string' );
		$timezone = ! empty( $timezone ) ? $timezone : get_option( 'gmt_offset' );
		$date_update->setTimezone( new \DateTimeZone( $timezone ) );

		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return $date_update->format( $format );
	} catch ( \Exception $e ) {
		return '';
	}
}

/**
 * Get post type taxonomies
 *
 * @param $post_type
 *
 * @return string[]|WP_Taxonomy[]
 */
function check_for_taxonomies( $post_type )
{
	$taxonomies = get_object_taxonomies( $post_type );
	return array_filter($taxonomies, function( $e ) {
		return ! in_array($e, ['post_tag', 'post_format']);
	});
}


function processOptions( $post_type ) {
	$taxonomies = check_for_taxonomies($post_type);

	$options = [
		'delete' => 'Delete',
		'draft' => 'Draft',
		'private' => 'Private',
		'password' => 'Password Protected',
		'category' => 'Change Category'
	];

	if ( empty( $taxonomies ) ) {
		unset($options['category']);
	}

	return $options;
}
