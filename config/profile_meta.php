<?php
/**
 * The base config for the profile meta separated out for easier management
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
return function ( $settings ) {
	$timestamp = strtotime('+1 week', current_time('timestamp'));

	return [
		'config' => [
			'title' => 'Profile Settings',
			'id' => 'athena-profile-settings',
			'pages' => [
				'athenaprofile'
			],
			'callback' => '',
			'context' => 'normal',
			'priority' => 'high'
		],
		'options' => [
			[
				'type' => 'date_box',
				'name' => __('Expiration Date & Time', 'athena-post-expiration'),
				'desc' => __('Choose the date and time that you would like for this profile to apply.', 'athena-post-expiration'),
				'id' => '_post_expiration',
				'default' => $timestamp
			],
			[
				'type' => 'select',
				'name' => __('Expiration Action', 'athena-post-expiration'),
				'desc' => __('Choose the action to take on the expiring post type', 'athena-post-expiration'),
				'id' => '_post_type_ex',
				'default' => 'draft',
				'options' => [
					'delete' => 'Delete',
					'draft' => 'Draft',
					'private' => 'Private',
					'password' => 'Password Protected',
					'category' => 'Change Category'
				],
				'onChange' => true,
				'class' => 'athena-ex-type'
			],
			[
				'type' => 'text',
				'name' => __('Post Password', 'athena-post-expiration'),
				'desc' => __('Adds a password to the post', 'athena-post-expiration'),
				'id' => '_post_password',
				'default' => '',
				'activate' => 'athena-ex-type',
				'select' => 'password',
				'required' => true,
				'max' => 20
			],
			[
				'type' => 'category',
				'name' => __('Post Category', 'athena-post-expiration'),
				'desc' => __('Select the Categories to assign to the post', 'athena-post-expiration'),
				'id' => '_post_category',
				'default' => '',
				'activate' => 'athena-ex-type',
				'select' => 'category',
				'required' => true
			],
			[
				'type' => 'checkbox',
				'name' => __('Enable Email Notification', 'athena-post-expiration'),
				'desc' => __('Send email prior to your profile expiring.', 'athena-post-expiration'),
				'default' => '',
				'id' => 'enable_email'
			],
			[
				'type' => 'future_date',
				'name' => __('Email Notification Time', 'athena-post-expiration'),
				'desc' => __('The amount of time prior to your profile expiring to send email notification.', 'athena-post-expiration'),
				'default' => ['count' => 1, 'type' => 'h'],
				'id' => 'email_time',
				'times' => 'hour'
			]
		]
	];
};
