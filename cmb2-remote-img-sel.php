<?php
/**
* Plugin Name: CMB2 Remote Image Select
* Plugin URI:  http://webdevstudios.com
* Description: Allows users to enter a URL in a text field and select a single image for use in post meta.  Similar to Facebook's featured image selector.
* Version:     0.1.0
* Author:      WebDevStudios
* Author URI:  http://webdevstudios.com
* Donate link: http://webdevstudios.com
* License:     GPLv2
* Text Domain: cmb2-remote-img-sel
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2015 WebDevStudios (email : contact@webdevstudios.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/**
 * Built using generator-plugin-wp
 */


/**
 * Autoloads files with classes when needed
 *
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested
 * @return  null
 */
function cmb2_remote_img_sel_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'CMB2RIS_' ) ) {
		return;
	}

	$filename = strtolower( str_ireplace(
		array( 'CMB2RIS_', '_' ),
		array( '', '-' ),
		$class_name
	) );

	CMB2_Remote_Image_Select::include_file( $filename );
}
spl_autoload_register( 'cmb2_remote_img_sel_autoload_classes' );


/**
 * Main initiation class
 *
 * @since  0.1.0
 * @var  string $version  Plugin version
 * @var  string $basename Plugin basename
 * @var  string $url      Plugin URL
 * @var  string $path     Plugin Path
 */
class CMB2_Remote_Image_Select {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  0.1.0
	 */
	const VERSION = '0.1.0';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $url      = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $path     = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var CMB2_Remote_Image_Select
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * A nonce value for verification
	 * @var string
	 */
	protected  $nonce = 'cmb2_remote_img_sel_nonce';

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  0.1.0
	 * @return CMB2_Remote_Image_Select A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  0.1.0
	 * @return  null
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );

		$this->plugin_classes();
		$this->hooks();
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since 0.1.0
	 * @return  null
	 */
	function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		// $this->admin = new CMB2RIS_Admin( $this );
	}

	/**
	 * Add hooks and filters
	 *
	 * @since 0.1.0
	 * @return null
	 */
	public function hooks() {
		register_activation_hook( __FILE__, array( $this, '_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, '_deactivate' ) );

		add_action( 'cmb2_render_remote_image_select', array( $this, 'remote_image_select' ), 10, 5 );
		add_action( 'cmb2_sanitize_remote_image_select', array( $this, 'sanitize_remote_image_select' ), 10, 2 );
		add_action( 'init', array( $this, 'init' ) );

		// Ajax stuffs
		add_action( 'wp_ajax_nopriv_cmb2_remote_img_sel', array( $this, 'handle_ajax' ) );
		add_action( 'wp_ajax_cmb2_remote_img_sel', array( $this, 'handle_ajax' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  0.1.0
	 * @return null
	 */
	function _activate() {
		// Make sure any rewrite functionality has been loaded
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  0.1.0
	 * @return null
	 */
	function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 * @return null
	 */
	public function init() {

		$min = defined( "SCRIPT_DEBUG" ) && SCRIPT_DEBUG ? '' : '.min';
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'cmb2-remote-img-sel', false, dirname( $this->basename ) . '/languages/' );

			wp_register_script( 'cmb2-remote-img-sel', $this->url( "assets/js/cmb2-remote-img-sel{$min}.js"), array( 'jquery' ), self::VERSION, true );
			wp_localize_script( 'cmb2-remote-img-sel', 'cmb2_remote_img_sel', array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'action'       => 'cmb2_remote_img_sel',
				'nonce'        => wp_create_nonce( $this->nonce ),
				'script_debug' => defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG : false
			) );
		}
	}

	public function remote_image_select( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

		// @TODO: add checks here for PHPCS
		$config_array = $field_type_object->value();
		$field_name   = $field_type_object->_name();
		$field_id     = $field_type_object->_id();

		echo $field_type_object->input( array(
			'type'        => 'text',
			'class'       => 'cmb2-remote-image-select url',
			'id'          => $field_id,
			'name'        => $field_name.'[url]',
			'desc'        => '',
			'placeholder' => 'http://',
		) );

		echo "<button class='cmb2-remote-image-select search button' id='$field_id-search'>" . __( 'Search', 'cmb2-remote-img-sel' ) . "</button>";
		echo "<div class='cmb2-remote-image-select images-loading-icon'><img src='" . $this->url( "assets/images/ajax-loader.gif" ) . "' class='ajax-loader'></div>";
		echo $field_type_object->_desc( true );

		// Loaded images
		$output = "<div class='cmb2-remote-image-select images placeholder' style='display:none;'></div>";

		echo $output;

		wp_enqueue_script( 'cmb2-remote-img-sel' );
	}

	public function sanitize_remote_image_select( $override = '', $value = array() ) {
		// Sanitize the data.
		if ( ! isset( $value['image'] ) || ! isset( $value['url'] ) ) {
			return '';
		}

		return array(
			'image' => ! empty( $value['image'] ) ? esc_url( $value['image'] ) : '',
			'url' => ! empty( $value['url'] ) ? esc_url( $value['url'] ) : '',
		);
	}

	public function handle_ajax() {
		if ( ! class_exists( 'WDS_Image_Grabber' ) ) {
			require_once 'includes/wds-image-grabber/wds-image-grabber.php';
		}

		$args = wp_parse_args( $_POST, array(
			'nonce'      => '',
			'field_id'   => '',
			'field_name' => '',
			'url'        => '',
		) );

		if ( ! wp_verify_nonce( $args['nonce'], $this->nonce ) ) {
			wp_send_json_error( __( "Verification error.", 'cmb2-remote-img-sel' ) );
		}

		if ( empty( $args['field_id'] ) || empty( $args['url'] ) || empty( $args['field_name'] ) ) {
			wp_send_json_error( __( "Required fields not met.", 'cmb2-remote-img-sel' ) );
		}

		$grabber = new WDS_Image_Grabber( $args['url'] );
		$images = $grabber->get_images();
		if ( empty( $images ) ) {
			wp_send_json_error( __( "No qualifying images were available for use.", 'cmb2-remote-img-sel' ) );
		}

		$output = '';
		$count = 0;
		$field_name = str_replace( '[url]', '', $args['field_name'] );
		$output .= "<ul class='image-list'>";
		foreach ( $images as $url ) {
			$output .= "<li class='image-item'><input type='radio' id='{$args['field_id']}-{$count}' name='{$field_name}[image]' value='$url' />";
			$output .= "<label for='{$args['field_id']}-{$count}'>";
			$output .= "<img src='{$url}'>";
			$output .= "</label></li>";
			$count++;
		}
		$output .= "</ul>";

		wp_send_json_success( $output );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  0.1.0
	 * @return boolean
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('')
		return true;
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'CMB2 Remote Image Select is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'cmb2-remote-img-sel' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';
			// Deactivate our plugin
			deactivate_plugins( $this->basename );

			return false;
		}

		return true;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 * @param string $field
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  0.1.0
	 * @param  string  $filename Name of the file to be included
	 * @return bool    Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/'. $filename .'.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  0.1.0
	 * @param  string $path (optional) appended path
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the CMB2_Remote_Image_Select object and return it.
 * Wrapper for CMB2_Remote_Image_Select::get_instance()
 *
 * @since  0.1.0
 * @return CMB2_Remote_Image_Select  Singleton instance of plugin class.
 */
function cmb2_remote_img_sel() {
	return CMB2_Remote_Image_Select::get_instance();
}

// Kick it off
cmb2_remote_img_sel();
