<?php
/**
 * Build the profile post type in a more contained way
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
return function () {
	$labels = array(
		'name'                  => _x( 'Profiles', 'Post Type General Name', 'athena-post-expiration' ),
		'singular_name'         => _x( 'Profile', 'Post Type Singular Name', 'athena-post-expiration' ),
		'menu_name'             => __( 'Profiles', 'athena-post-expiration' ),
		'name_admin_bar'        => __( 'Profiles', 'athena-post-expiration' ),
		'archives'              => __( 'Profile Archives', 'athena-post-expiration' ),
		'attributes'            => __( 'Profile Attributes', 'athena-post-expiration' ),
		'parent_item_colon'     => __( 'Parent Item:', 'athena-post-expiration' ),
		'all_items'             => __( 'All Profiles', 'athena-post-expiration' ),
		'add_new_item'          => __( 'Add New Profile', 'athena-post-expiration' ),
		'add_new'               => __( 'Add Profile', 'athena-post-expiration' ),
		'new_item'              => __( 'New Profile', 'athena-post-expiration' ),
		'edit_item'             => __( 'Edit Profile', 'athena-post-expiration' ),
		'update_item'           => __( 'Update Profile', 'athena-post-expiration' ),
		'view_item'             => __( 'View Profile', 'athena-post-expiration' ),
		'view_items'            => __( 'View Profiles', 'athena-post-expiration' ),
		'search_items'          => __( 'Search Profiles', 'athena-post-expiration' ),
		'not_found'             => __( 'Not found', 'athena-post-expiration' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'athena-post-expiration' ),
		'featured_image'        => __( 'Featured Image', 'athena-post-expiration' ),
		'set_featured_image'    => __( 'Set featured image', 'athena-post-expiration' ),
		'remove_featured_image' => __( 'Remove featured image', 'athena-post-expiration' ),
		'use_featured_image'    => __( 'Use as featured image', 'athena-post-expiration' ),
		'insert_into_item'      => __( 'Insert into item', 'athena-post-expiration' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'athena-post-expiration' ),
		'items_list'            => __( 'Items list', 'athena-post-expiration' ),
		'items_list_navigation' => __( 'Items list navigation', 'athena-post-expiration' ),
		'filter_items_list'     => __( 'Filter items list', 'athena-post-expiration' ),
	);
	$args = array(
		'label'                 => __( 'Profile', 'athena-post-expiration' ),
		'description'           => __( 'Profiles', 'athena-post-expiration' ),
		'labels'                => $labels,
		'supports'              => array( 'title'),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => false,
		'menu_position'         => 5,
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);

	register_post_type( 'athenaprofile', $args );
};
