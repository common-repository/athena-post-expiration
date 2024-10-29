<?php

namespace Athena\Services;

use Athena\Utils\Constants;

/**
 * Cron handler for expiring posts and emails
 *
 * @author erik@brightthought.co
 * @since 1.0.0
 */
class Cron
{
	/**
	 * Pre-defined settings
	 *
	 * @var
	 */
	private $_settings;

	/**
	 * All active posts
	 *
	 * @var
	 */
	private $_active_posts;

	/**
	 * All active profiles
	 *
	 * @var
	 */
	private $_active_profiles;

	/**
	 * @param $settings
	 * @param $post
	 * @param $profile
	 */
	public function __construct( $settings, $post, $profile ){

		$this->_settings = $settings;
		$this->_active_posts = $post;
		$this->_active_profiles = $profile;

		$this->setActions();
	}

	/**
	 * Set cron actions
	 *
	 * @return void
	 */
	private function setActions()
	{
		if ( is_array($this->_active_posts) ) {
			foreach( $this->_active_posts as $id ) {
				add_action('athena_post_ex_' . $id, [$this, 'post_cron'], 10, 1);
			}
		}

		if ( is_array($this->_active_profiles) ) {
			foreach( $this->_active_profiles as $id ) {
				add_action('athena_profile_ex_' . $id['id'], [$this, 'profile_cron'], 10, 1);

				if (isset($id['email']) && $id['email'] === true ) {
					add_action('athena_profile_email_' . $id['id'], [$this, 'email_cron'], 10, 1);
				}
			}
		}
	}

	/**
	 * Profile post type cron
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function profile_cron( $post_id )
	{
		$profile_actions = get_post_meta( $post_id );
		$attached_posts = get_option('athena_profile_' . $post_id);

		if ( $attached_posts !== false ) {
			foreach($attached_posts as $post) {

				$action['id'] = $post;
				$action['action'] = $profile_actions['_post_type_ex'][0];

				if (
					isset($profile_actions['_post_password'][0]) &&
					! empty($profile_actions['_post_password'][0]))
				{
					$action['password'] = $profile_actions['_post_password'][0];
				}

				if (
					isset($profile_actions['_post_category'][0]) &&
					! empty($profile_actions['_post_category'][0]))
				{
					$action['category'] = unserialize($profile_actions['_post_category'][0]);
				}


				$this->post_action( $action );
			}
		}
	}

	/**
	 * Non-profile post type cron
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function post_cron( $post_id )
	{
		$post_actions = get_post_meta( $post_id );

		$action['id'] = $post_id;
		$action['action'] = $post_actions['_athena_ex_type'][0];

		if ( isset($post_actions['_athena_password'][0]) && !empty($post_actions['_athena_password'][0])) {
			$action['password'] = $post_actions['_athena_password'][0];
		}

		if ( isset($post_actions['_athena_category'][0]) && !empty($post_actions['_athena_category'][0])){
			$action['category'] = unserialize($post_actions['_athena_category'][0]);
		}


		$this->post_action( $action );
	}

	/**
	 * Handles the actions to take on expiration
	 *
	 * @param $action
	 *
	 * @return void
	 */
	private function post_action( $action )
	{
		switch( $action['action'] )
		{
			case 'delete' :
				$bypass = isset($this->_settings['trash_bypass']) && $this->_settings['trash_bypass'] == true;

				wp_delete_post($action['id'], $bypass);
				break;
			case 'draft' :
				$args = array(
					'ID' => $action['id'],
					'post_status' => 'draft'
				);

				wp_update_post( $args );
				break;
			case 'private' :
				$args = array(
					'ID' => $action['id'],
					'post_status' => 'private'
				);

				wp_update_post( $args );
				break;
			case 'password' :
				$args = array(
					'ID' => $action['id'],
					'post_status' => 'publish',
					'post_password' => $action['password']
				);

				wp_update_post( $args );
				break;
			case 'category' :
				$args = array(
					'ID' => $action['id'],
					'post_category' => $action['category']
				);

				wp_update_post( $args );
				break;
		}
	}

	/**
	 * Handles sending the cron email
	 *
	 * @todo Clean up this method
	 * @param $post_id
	 *
	 * @return void
	 */
	public function email_cron( $post_id )
	{
		// Update the email content type
		$original = '';
		add_filter('wp_mail_content_type',  function( $type ) use (&$original) {
			$original = $type;
			return $type !== 'text/html' ? 'text/html' : $type;
		} );

		$profile = get_post($post_id, OBJECT);
		$profile_time = get_post_meta($post_id, '_post_expiration', true);

		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		$from_email = 'noreply@' . $sitename;

		$email = wp_remote_get(Constants::$ATHENA_URI . 'resources/dist/templates/email.html');
		$email = wp_remote_retrieve_body($email);

		$subject = 'Athena Profile Expiring Soon!';
		$headers = 'From: ' . get_option('blogname') .' <' . $from_email .'>';

		$body = '<header style="background:#fff;padding:15px 15px 0;position:relative"><div align=center><img alt="Up To Logo" src="'. Constants::$ATHENA_URI .'resources/dist/images/email_logo.png" style=max-width:150px></div></header>';
		$body .= '<main style="background:#fff;padding:0 15px 15px">';

		$body .= '<p>Hello,</p>';
		$body .= '<p>You\'re Athena profile <strong>'. $profile->post_title .'</strong> will be expiring soon. If you need to make a change to your profile you should do so prior to it\'s expiration date of <strong>' . athena_date_timezone($profile_time) . '</strong>.';
		$body .= '<p>Sincerely, <br><strong>The Athena Team</strong></p>';

		$body .= '</main>';

		$body = str_replace('%ATHENACONTENT%', $body, $email);


		wp_mail($this->_settings['notify_email'], $subject, $body, $headers);

		// Set the content type to its original state
		add_filter('wp_mail_content_type', function( $type ) use ($original) {
			return $original === 'text/plain' ? 'text/plain' : $type;
		});
	}

	/**
	 * Gets all the cronjobs to be display on the setting page
	 *
	 * @return array
	 */
	public static function getCronJobs()
	{
		$cron_jobs = get_option('cron');
		$expirations = [];

		foreach($cron_jobs as $key => $value) {
			if ( is_array($value) ) {
				foreach($value as $k => $i){

					if (strpos($k, 'athena_profile') !== false && ($post_id = strrchr($k, '_')) !== false) {
						$post_id = substr($post_id, 1);

						if ( !isset($expirations['profiles']) ) $expirations['profiles'] = [];
						$expirations['profiles'][$key] = get_post( $post_id );
						$expirations['profiles'][$key]->meta = get_post_meta( $post_id, '_athena_ex_type', true );
					}

					if ( strpos($k, 'athena_post') !== false && ($post_id = strrchr($k, '_')) !== false ) {
						$post_id = substr($post_id, 1);
						$post = get_post( $post_id );

						if ( !isset($expirations[$post->post_type]) ) $expirations[$post->post_type] = [];
						$expirations[$post->post_type][$key] = get_post( $post_id );
						$expirations[$post->post_type][$key]->meta = get_post_meta( $post_id, '_athena_ex_type', true );
					}
				}
			}
		}

		return $expirations;
	}
}
