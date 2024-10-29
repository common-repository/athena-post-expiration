<?php

namespace Athena\Controller;

use Athena\Model\MetaSave;

class PostTypeMeta {

	/**
	 * @var array $config Accepts the array for the configuration settings
	 */

	private $_config = [];

	/**
	 * @var array $options Accepts the array for the fields to render
	 */
	private $_options = [];

	private $_settings = [];

	private $_active_ex = [];


	function __construct( $settings, $core, $active ) {
		$this->_settings = $core;

		$this->_active_ex = $active;

		$this->_config = $settings['config'];
		$this->_options = $settings['options'];

		add_action( 'add_meta_boxes', [$this, 'create_meta_boxes'] );
		add_action( 'save_post', [$this, 'save_meta_boxes'] );
	}

	function create_meta_boxes() {

		if ( function_exists( 'add_meta_box' ) ) {

			add_meta_box( $this->_config['id'], $this->_config['title'], array($this, 'render_box'), $this->_config['pages'], $this->_config['context'], $this->_config['priority'] );

		}
	}

	/**
	 * Saves the meta data
	 *
	 * @param $post_id
	 *
	 * @return mixed|void
	 */
	function save_meta_boxes( $post_id )
	{
		global $post_type;

		if ( ! isset( $_POST[$this->_config['id'] . '_noncename'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST[$this->_config['id'] . '_noncename'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}


		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		new MetaSave( $this->_options, $post_id, $post_type, $this->_active_ex);
	}

	/**
	 * Generates the meta box
	 *
	 * @return void
	 */
	function render_box()
	{
		global $post;

		wp_enqueue_script( 'athena-js' );
		echo '<div class="athena-menu-content">';

		foreach( $this->_options as $value ) {
			$saved = get_post_meta($post->ID, $value['id'], true );

			if ( empty($saved) ) {
				$saved = $value['default'];
			}

			new Fields($value, $saved, $this->_settings);
		}

		echo '<input type="hidden" name="' . $this->_config['id'] . '_noncename" id="' . $this->_config['id'] . '_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
		echo '</div>';
	}
}
