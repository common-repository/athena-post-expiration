<?php

namespace Athena\Services;

use Athena\Controller\PostTypeMeta;
use Athena\Controller\Settings;
use Athena\Model\MetaSave;
use Athena\Utils\Config;
use Athena\Utils\Constants;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

/**
 * Initiating Athena App Class
 *
 * This class wraps the application in a container and initiates all the necessary
 * components needed to run this plugin.
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
class App {

	/**
	 * Instance of the configuration class
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * DI container
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Registered post types
	 *
	 * @var array
	 */
	protected $postTypes = [];

	/**
	 * Active posts that need to run
	 *
	 * @var array
	 */
	protected $posts = [];

	/**
	 * Active profiles that will run
	 *
	 * @var array
	 */
	protected $profiles = [];

	/**
	 * Construct with dependency injection
	 *
	 * @param Config $config
	 * @param Container $container
	 */
	public function __construct( Config $config, Container $container )
	{
		$this->config = $config;
		$this->container = $container;

		add_action('admin_menu', [$this, 'profileMenuAdd']);

		add_filter( "manage_athenaprofile_posts_columns", [$this, 'profileColumn'] );
		add_action( "manage_athenaprofile_posts_custom_column", [$this, 'profileColumnContent'], 10, 2 );

		add_action('wp_ajax_athena_settings', [$this, 'saveSettings']);
		add_action('before_delete_post', array($this, 'preDeleteAction'));
		add_action( 'wp_insert_post', array($this, 'autoEnabledCheck'), 10, 3 );
	}

	/**
	 * Starts the application
	 *
	 * @return void
	 */
	public function start()
	{
		try {
			// Get all the active profiles and posts
			$this->getActivePostOrProfiles();;

			// Builds out the settings
			$this->buildGeneralSettings();

			// Get post types
			$this->getPostTypes();

			// Builds the views for the application
			$this->establishViews();

			// Builds the post type profile
			$this->postTypeProfile();

			// Establishes the meta boxes
			$this->setMetaForPostTypes();

			// Starts the cron handler
			$this->startCron();
		} catch ( \Exception $e ) {
			wp_die($e->getMessage());
		}
	}

	/**
	 * Build the views for the plugin
	 *
	 * @return void
	 */
	private function establishViews()
	{
		// Establish Settings Page
		$this->container->make(Settings::class, [
			'saved' => $this->settings,
			'post_types' => $this->postTypes,
		]);
	}

	/**
	 * Creates the cron job
	 *
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function startCron()
	{
		$this->container->make(Cron::class, [
			'settings' => $this->settings,
			'post' => $this->posts,
			'profile' => $this->profiles
		]);
	}

	/**
	 * Establishes the general settings for the application
	 *
	 *
	 * @return void
	 */
	private function buildGeneralSettings()
	{
		$this->settings = get_option( Constants::$ATHENA_OPT );
		if ( $this->settings === false ) {
			$info = [];

			foreach( $this->config->get('settings')['general'] as $value ) {
				if ( ! empty($value['default']) ) {
					$info[$value['id']] = $value['default'];
				}
			}

			update_option(Constants::$ATHENA_OPT, $info, false);
			$this->settings = $info;
		}
	}

	/**
	 * Gets all the registered post types
	 *
	 * @return void
	 */
	private function getPostTypes()
	{
		$this->postTypes = get_post_types([
			'exclude_from_search' => false,
			'show_ui' => true,
			'show_in_nav_menus' => true

		], 'objects');

		Constants::$ATHENA_POSTTYPES = $this->postTypes;
	}

	/**
	 * Builds the meta fields for activated post types
	 *
	 * @return void
	 */
	private function setMetaForPostTypes ()
	{
		// Sets post meta for the profile post type
		new PostTypeMeta($this->config->get('profile_meta')($this->settings), $this->settings, $this->profiles);

		// Sets post meta for all other active post types
		foreach ( $this->postTypes as $pt ) {
			if ( isset($this->settings['enable_posttype_' . $pt->name]) ) {
				new PostTypeMeta($this->config->get('post_meta')($pt->name), $this->settings, $this->posts);
			}
		}
	}

	/**
	 * Adds the profile link to the Athena settings link
	 *
	 * @return void
	 */
	public function profileMenuAdd()
	{
		add_submenu_page('athena', 'Profiles', 'Profiles', 'administrator', 'edit.php?post_type=athenaprofile',  null);
	}

	/**
	 * Builds the profile post type
	 *
	 * @return void
	 */
	private function postTypeProfile()
	{
		$this->config->get('profile_post_type')();
	}

	/**
	 * Get all the active posts and profiles
	 *
	 * @return void
	 */
	private function getActivePostOrProfiles()
	{
		$this->posts = get_option(Constants::$ATHENA_POST, false);
		$this->profiles = get_option(Constants::$ATHENA_PROFILE, false);
	}

	/**
	 * Adds columns to the profile post types
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function profileColumn( $columns ): array
	{
		unset($columns['date']);
		$columns['athena_timestamp'] = __( 'Expiration', 'athena-post-expiration' );

		return $columns;
	}

	/**
	 * Adds the content to the profile date column
	 *
	 * @param $column_name
	 * @param $post_id
	 *
	 * @return void
	 */
	public function profileColumnContent( $column_name, $post_id )
	{
		$date = get_post_meta($post_id, '_post_expiration', true);

		if ( empty($date) ) {
			echo 'No Expiration Set';
			return;
		} else {
			$current_time = current_time( 'timestamp', 1 );
		}

		$date_formatted = athena_date_timezone($date);

		if ( $current_time > $date ) {
			$date_formatted = '<span style="color:#ff0000">' . $date_formatted . '</span>';
		}

		echo $date_formatted;
	}

	/**
	 * Handles the saving of the settings data
	 *
	 * @return void
	 */
	public function saveSettings()
	{
		$data = $_POST;
		$data = array_filter($data, function( $k ) {
			return ! in_array($k, ['action', 'search_terms']);
		}, ARRAY_FILTER_USE_KEY);

		echo $data === $this->settings || update_option(Constants::$ATHENA_OPT, $data) !== false;
		exit();
	}

	/**
	 * Actions to handle before the deletion of a post
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function preDeleteAction( $post_id )
	{
		global $post_type;

		if ( $post_type === 'athenaprofile' ) {
			if ( wp_next_scheduled( 'athena_profile_ex_' . $post_id, array($post_id) ) !== false) {
				wp_clear_scheduled_hook( 'athena_profile_ex_' . $post_id , array($post_id) );
			}

			if (wp_next_scheduled('athena_profile_email_' . $post_id, array($post_id) ) !== false) {
				wp_clear_scheduled_hook( 'athena_profile_email_' . $post_id , array($post_id) );
			}

			delete_option('athena_profile_'.$post_id);

			if (is_array($this->profiles) && ($key = array_search($post_id, array_column($this->profiles, 'id'))) !== false) {

				unset($this->profiles[$key]);
				update_option('athena_active_profiles', $this->profiles, true);
			}
		}
	}

	/**
	 * Checks if the post being created should auto expire
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 *
	 * @return void
	 */
	public function autoEnabledCheck( $post_id, $post, $update)
	{
		if ( !$update ) {
			if (
				(isset($this->settings['enable_posttype_' . $post->post_type]) &&
				 $this->settings[ 'enable_posttype_' . $post->post_type ] ) &&
				( isset($this->settings['auto_apply_'. $post->post_type]) &&
				  $this->settings[ 'auto_apply_' . $post->post_type ] )
			)
			{
				$options = [
					[
						'id' => '_athena_options'
					],
					[
						'id' => '_athena_ex_type'
					],
					[
						'id' => '_athena_password'
					],
					[
						'id' => '_athena_category'
					]
				];

				$count = $this->settings['future_date_'.$post->post_type]['count'];
				$type = athena_date_time($count);

				$_POST['_athena_options']['enabled'] = '1';
				$_POST['_athena_options']['timestamp'] = date('c', strtotime('+'. $count .' ' . $type[$this->settings['future_date_'.$post->post_type]['type']]));
				$_POST['_athena_ex_type'] = $this->settings['expire_action_'.$post->post_type];

				switch($_POST['_athena_ex_type']) {
					case 'password' :
						$_POST['_athena_password'] = $this->settings['post_password_'.$post->post_type];
						break;
					case 'category' :
						$_POST['_athena_category'] = $this->settings['post_category_'.$post->post_type];
						break;
				}

				new MetaSave($options, $post_id, $post->post_type, $this->posts);
			}
		}
	}
}
