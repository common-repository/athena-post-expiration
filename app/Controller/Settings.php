<?php

namespace Athena\Controller;

use Athena\Utils\Config;
use Athena\Utils\Constants;
use DI\Container;

/**
 * The Athena settings controller
 *
 * @version 2.0.0
 * @author erik@brightthought.co
 */
class Settings
{
	private $saved;

	private $post_types = [];

	private $fields;

    private $config;

	public function __construct( Config $config, $saved, $post_types )
    {
        $this->config = $config;
		$this->saved = $saved;
		$this->post_types = $post_types;
		$this->fields = $this->config->get('settings')();

		add_action( 'admin_menu', [$this, 'add_athena_page']);
	    add_action( 'admin_enqueue_scripts', [$this, 'admin_scripts']);
	}

	/**
	 * Establish the Athena settings page
	 *
	 * @return void
	 */
	public function add_athena_page()
    {
		add_menu_page(
			'Athena Post Expiration',
			'Athena',
			'administrator',
			'athena',
			[$this, 'create_settings_page'],
			'dashicons-sos'
		);
	    add_submenu_page(
		    'athena',
		    'Athena Settings',
		    'Settings',
		    'administrator',
		    'athena'
	    );
	}

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function create_settings_page()
    {
		wp_enqueue_script('athena-js');

		ob_start();
		include(Constants::$ATHENA_DIR . '/resources/views/settings.php');
		echo ob_get_clean();
	}

	/**
	 * Enqueues the necessary scripts
	 *
	 * @return void
	 */
	public function admin_scripts()
	{
		$css = Constants::$ATHENA_URI . 'resources/dist/css/app.css';
		$js = Constants::$ATHENA_URI . 'resources/dist/js/app.js';
		if ( file_exists(Constants::$ATHENA_DIR . '/resources/dist/mix-manifest.json') ) {
			$files = json_decode(file_get_contents(Constants::$ATHENA_DIR . '/resources/dist/mix-manifest.json'), true);
			$css = Constants::$ATHENA_URI . 'resources/dist' .  $files['/css/app.css'];
			$js = Constants::$ATHENA_URI . 'resources/dist' . $files['/js/app.js'];
		}

		wp_enqueue_style('athena_settings', $css, false);
		wp_register_script('athena-js', $js, [], '2.0.0', true);
		wp_add_inline_script('athena-js', "var adminjsvars = " . json_encode(array(
					'ajax' => admin_url( 'admin-ajax.php' ),
				)
			), 'before');
	}

	/**
     * Returns the post type fields array for the meta data
     *
	 * @param $post_type
	 * @param $title
	 *
	 * @return array
	 */
	private function post_type_fields( $post_type, $title ): array
    {
        return $this->config->get('post_type_fields')( $title, $post_type );
	}
}
