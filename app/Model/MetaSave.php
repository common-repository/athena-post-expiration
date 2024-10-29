<?php

namespace Athena\Model;

use Athena\Utils\Constants;

/**
 * The class to save all post and profile meta data
 *
 * @todo Rewrite this to be cleaner and more manageable
 *
 * @author erik@brightthought.co
 * @version 1.0.0
 */
class MetaSave {

	private $_active_ex = [];

	public function __construct($options, $id, $post_type, $active)
	{
		$this->_active_ex = empty($active) ? [] : $active;
		$this->meta_save($options, $id, $post_type);
	}

	/**
	 * Determines how to handle saving the metadata
	 *
	 * @param $options
	 * @param $id
	 * @param $post_type
	 *
	 * @return void
	 */
	private function meta_save($options, $id, $post_type)
	{
		foreach ( $options as $option) {
			if ( $post_type === 'athenaprofile' ) {
				$this->athena_post_type($option['id'], $id, $option['type'], count($options));
			} else {
				$break = $this->other_post_type($option['id'], $id);
				if ( $break ) {
					break;
				}
			}
		}
	}

	/**
	 * Handles saving the data for profiles
	 *
	 * @param $option
	 * @param $id
	 * @param $type
	 * @param $count
	 *
	 * @return void
	 */
	private function athena_post_type($option, $id, $type, $count)
	{
		static $email_enabled, $timestamp, $current = 1;

		if ( isset( $option ) && ! empty( $option ) ) {

			$value = isset($_POST[$option])? $_POST[$option] : '';

			if ( ! empty($value) ) {
				if ( $type === 'date_box' ) {
					try {
						$ts = new \DateTime( $value, new \DateTimeZone( get_option( 'timezone_string' ) ) );
						$ts->setTimezone( new \DateTimeZone( date_default_timezone_get() ) );
						$value = $timestamp = $ts->getTimestamp();

						$this->schedule_profile_cron( $id, $timestamp );
					} catch ( \Exception $e ) {}
				}

				update_post_meta( $id, $option, $value );
			} else {
				delete_post_meta( $id, $option, get_post_meta( $id, $option, true ) );
			}

			if ($option === 'enable_email') {
				$email_enabled = isset( $_POST[$option] );
			}

			if ($option === 'email_time' && $email_enabled) {
				$this->schedule_email_cron($id, $timestamp, $_POST[$option]);
			}
		}

		if ( $current === $count ) {

			if ( is_array($this->_active_ex) && ( $key = array_search($id, array_column($this->_active_ex, 'id')) ) !== false ) {

				if ($email_enabled) {

					if( ! array_key_exists('email', $this->_active_ex[$key]) ){

						$this->_active_ex[ $key ]['email'] = true;
						update_option('athena_active_profiles', $this->_active_ex);
					}
				} else {
					if ( array_key_exists('email', $this->_active_ex[$key]) ){

						unset( $this->_active_ex[$key]['email'] );
						update_option('athena_active_profiles', $this->_active_ex);

						$this->cancel_email_cron( $id );
					}
				}
			} else {

				if ($email_enabled) {
					$this->_active_ex[] = ['id' => $id, 'email' => true];
				} else {
					$this->_active_ex[] = ['id' => $id ];
				}

				update_option(Constants::$ATHENA_PROFILE, $this->_active_ex);
			}
		}

		$current++;
	}


	private function other_post_type($option, $id) {

		if ( isset( $option ) && ! empty( $option ) ) {

			if ($option === '_athena_options' && isset($_POST[$option]['enabled']) && $_POST[$option]['enabled'] == true ) {

				$profile_data = isset($_POST[$option]['profile'])?get_option('athena_profile_'.$_POST[$option]['profile']) : false;

				if (isset($_POST[$option]['profile']) && !empty($_POST[$option]['profile'])) {

					$value['enabled'] = $_POST[$option]['enabled'];
					$value['profile'] = $_POST[$option]['profile'];


					if ( is_array($profile_data) && !in_array($id, $profile_data) ) {

						$profile_data[] = $id;
						update_option('athena_profile_'.$value['profile'], $profile_data);
					} else if ( !is_array($profile_data) ) {
						$profile_data = array($id);
						update_option('athena_profile_'.$value['profile'], $profile_data);
					}

					$break = true;

				} else {

					if ( is_array($profile_data) && ($key = array_search($id, $profile_data)) !== false ) {

						unset($profile_data[$key]);
						update_option('athena_profile_'. $_POST[$option]['profile'], $profile_data);

					}

					$value = $_POST[ $option ] ?? '';

					if ( isset($value['timestamp']) ) {
						$ts = new \DateTime($value['timestamp'], new \DateTimeZone(get_option('timezone_string')));
						$ts->setTimezone(new \DateTimeZone(date_default_timezone_get()));
						$value['timestamp'] = $ts->getTimestamp();
					}

					$post_status = get_post_status($id);
					if ( $post_status !== 'auto-draft' && $post_status !== 'inherit') {

						$this->schedule_post_cron($id, $value['timestamp']);

						if (is_array($this->_active_ex)) {
							$this->_active_ex[] = $id;
						} else {
							$this->_active_ex = [$id];
						}

						update_option(Constants::$ATHENA_POST, $this->_active_ex, true);
					}

				}

				if ( !empty($value) ) {
					update_post_meta( $id, $option, $value );
				} else {
					delete_post_meta( $id, $option, get_post_meta( $id, $option, true ) );
				}

				return $break ?? false;

			} else if ($option !== '_athena_options') {

				$value = $_POST[ $option ] ?? '';

				if ( !empty($value) ) {
					update_post_meta( $id, $option, $value );
				} else {
					delete_post_meta( $id, $option, get_post_meta( $id, $option, true ) );
				}

			} else {

				$value = $_POST[ $option ]['enabled'] ?? '';

				$this->cancel_post_cron( $id );

				if (is_array($this->_active_ex) && ($key = array_search($id, $this->_active_ex)) !== false) {
					unset($this->_active_ex[$key]);
				}

				update_option(Constants::$ATHENA_POST, $this->_active_ex, true);

				if ( !empty($value) ) {
					update_post_meta( $id, $option, $value );
				} else {
					delete_post_meta( $id, $option, get_post_meta( $id, $option, true ) );
				}

				return true;
			}
		}
	}

	private function schedule_profile_cron( $post_id, $time)
	{
		if ( $time > current_time('timestamp') ) {

			if ( ($timestamp = wp_next_scheduled( 'athena_profile_ex_'.$post_id, array($post_id) )) === false){

				wp_schedule_single_event( $time, 'athena_profile_ex_'.$post_id, array($post_id));

			} else {
				if ($timestamp !== $time) {
					wp_clear_scheduled_hook( 'athena_profile_ex_'.$post_id, array($post_id) );
					wp_schedule_single_event( $time, 'athena_profile_ex_'.$post_id, array($post_id));
				}
			}
		}
	}

	private function schedule_email_cron( $post_id, $time, $type )
	{
		if ($type['type'] == 'h') {

			$time = $time - (3600 * $type['count']);

		} elseif ($type['type'] == 'd') {

			$time_frame = athena_date_time( $type['count'] );
			$time = strtotime('-'. $type['count'] . ' ' . $time_frame[$type['type']], $time);

		} else {
			return;
		}

		if ( $time >= current_time('timestamp') + 3600 ) {

			if( ($timestamp = wp_next_scheduled('athena_profile_email_' . $post_id, array($post_id) ) ) === false){

				wp_schedule_single_event($time, 'athena_profile_email_' . $post_id, array($post_id));

			} else {

				if($timestamp !== $time){
					wp_clear_scheduled_hook( 'athena_profile_email_' . $post_id, array($post_id) );
					wp_schedule_single_event( $time, 'athena_profile_email_' . $post_id, array($post_id));

				}
			}
		}
	}

	private function schedule_post_cron( $post_id, $time )
	{
		if ( $time > current_time('timestamp') ) {

			if ( ($timestamp = wp_next_scheduled( 'athena_post_ex_'.$post_id, array($post_id) )) === false ) {

				wp_schedule_single_event( $time, 'athena_post_ex_'.$post_id, array($post_id));

			} else {

				if ($timestamp !== $time ) {
					wp_clear_scheduled_hook( 'athena_post_ex_'.$post_id, array($post_id) );
					wp_schedule_single_event( $time, 'athena_post_ex_'.$post_id, array($post_id));
				}
			}
		}
	}

	private function cancel_post_cron( $post_id )
	{
		if ( ( $timestamp = wp_next_scheduled('athena_post_ex_'.$post_id, array($post_id) ) ) !== false ) {
			wp_clear_scheduled_hook( 'athena_post_ex_'.$post_id, array($post_id) );
		}
	}

	private function cancel_email_cron( $post_id )
	{
		if ( ( $timestamp = wp_next_scheduled('athena_profile_email_'.$post_id, [$post_id] ) ) !== false ) {

			wp_clear_scheduled_hook( 'athena_profile_email_' . $post_id, [$post_id] );

		}
	}
}
