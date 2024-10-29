<?php
/**
 * Build the settings fields
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
return function () {
	return [
		'general' => [
			[
				'type' => 'text',
				'name' => __('Email Address', 'athena-post-expiration'),
				'desc' => __('This is the email address to use for notifications.', 'athena-post-expiration'),
				'id' => 'notify_email',
				'default' => get_option('admin_email'),
				'display' => false,
				'required' => true
			],
			[
				'type' => 'checkbox',
				'name' => __('Bypass Trash', 'athena-post-expiration'),
				'desc' => __('Enabling this will permanently delete posts that are set to delete on post expiration bypasing the trash.', 'athena-post-expiration'),
				'id' => 'trash_bypass',
				'default' => ''
			]
		],
		'schedule' => [
			[
				'type' => 'cron',
			]
		]
	];
};
