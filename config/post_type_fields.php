<?php
/**
 * Builds the post type fields
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
return function ( $title, $post_type ) {

	$options = processOptions( $post_type );

	return [
		[
			'type' => 'header',
			'name' => $title
		],
		[
			'type' => 'checkbox',
			'name' => __('Enable', 'athena-post-expiration'),
			'desc' => __('This will enable Athena Post Expiration options on this particular post type', 'athena-post-expiration'),
			'default' => '',
			'id' => 'enable_posttype_' . $post_type
		],
		[
			'type' => 'select',
			'name' => __('Expiration Action', 'athena-post-expiration'),
			'desc' => __('Choose the action to take on the expiring post type', 'athena-post-expiration'),
			'id' => 'expire_action_' . $post_type,
			'default' => 'draft',
			'options' => $options,
			'onChange' => true,
			'class' => 'athena-ex-type_' . $post_type
		],
		[
			'type' => 'checkbox',
			'name' => __('Auto Apply', 'athena-post-expiration'),
			'desc' => __('This automatically apply expiration to new posts of this post type.', 'athena-post-expiration'),
			'default' => '',
			'id' => 'auto_apply_' . $post_type
		],
		[
			'type' => 'future_date',
			'name' => __('Default Date', 'athena-post-expiration'),
			'desc' => __('The default amount of time in the future the post will expire.', 'athena-post-expiration'),
			'default' => ['count' => 1, 'type' => 'd'],
			'id' => 'future_date_' . $post_type,
			'times' => 'day'
		],
		[
			'type' => 'text',
			'name' => __('Post Password', 'athena-post-expiration'),
			'desc' => __('Adds a password to the post', 'athena-post-expiration'),
			'id' => 'post_password_' . $post_type,
			'default' => '',
			'activate' => 'athena-ex-type_' . $post_type,
			'select' => 'password',
			'required' => true,
			'max' => 20
		],
		[
			'type' => 'category',
			'name' => __('Post Category', 'athena-post-expiration'),
			'desc' => __('Select the Categories to assign to the post', 'athena-post-expiration'),
			'id' => 'post_category_' . $post_type,
			'default' => '',
			'activate' => 'athena-ex-type_' . $post_type,
			'select' => 'category',
			'required' => true
		]
	];
};
