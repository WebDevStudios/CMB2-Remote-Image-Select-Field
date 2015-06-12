<?php
/*
Plugin Name: WDS Image Grabber
Version: 0.1
Description: Class to grab images from website's body.
Author: WebDevStudios
Author URI: http://webdevstudios.com/
Plugin URI: http://webdevstudios.com/
Text Domain: wds-image-grabber
Domain Path: /languages
*/

class WDS_Image_Grabber {

	/**
	 * Site domain.
	 * @var string
	 */
	public $domain = '';

	/**
	 * Array of images
	 * @var array
	 */
	public $images = array();

	/**
	 * Class constructor.
	 *
	 * @param string $url URL of the site.
	 */
	public function __construct( $url ) {
		$this->domain = $this->get_domain( $url );

		// Get the body for this site.
		$body = $this->get_site_body( $url );

		// Search for images.
		$this->parse_images( $body );
	}

	/**
	 * Grabs body of the given site.
	 *
	 * @param  string      $url URL of the site.
	 * @return string|bool $res Body of the site or false.
	 */
	public function get_site_body( $url ) {
		$res = wp_remote_get( $url );

		if ( is_wp_error( $res ) ) {
			return false;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $res );
	}

	/**
	 * Parses images for give site body.
	 *
	 * @param string $body Body of the site.
	 */
	public function parse_images( $body ) {
		if ( ! function_exists('str_get_html') ) {
			require_once plugin_dir_path( __FILE__ ) . 'lib/simple-html-dom.php';
		}

		$dom = str_get_html( $body );

		if ( ! method_exists( $dom, 'find' ) ) {
			$this->images = array();
			return;
		}

		// Check of OG image first
		$og_image = $dom->find( 'meta[property=og:image]', 0 );

		// Don't make the domain amazon specific, just grab any img tag
		$this->images = $this->img_tags( $dom );
		if ( ! $this->images || empty( $this->images ) ) {
			$this->images = array();
		}
		if ( $og_image ) {
			array_unshift( $this->images, $og_image->content );
		}

		return $this->images;
	}

	/**
	 * Handle Amazon specific image search.
	 *
	 * @param  object $dom Instance of Simple_HTML_Dom
	 * @return array  $images Array of images.
	 */
	public function img_tags( $dom ) {
		// Target Amazon specific DOM elements.
		$images = $dom->find( 'img' );
		if ( ! empty( $images ) ) {
			foreach( $images as $key => $element ) {
				if ( ! isset( $element->height ) || ! isset( $element->width ) || ! isset( $element->src ) ) {
					// We need to filter out icons, sorry, but if it doesn't have a size, it must go
					unset( $images[ $key ] );
					continue;
				}
				$height = intval( $element->height );
				$width  = intval( $element->width );

				if ( 70 >= $height || 70 >= $width ) {
					// Image may be an icon, so remove it.
					unset( $images[ $key ] );
					continue;
				}

				if ( 0 !== stripos( $element->src, 'http' ) ) {
					// This is a relative image src, ignore it
					unset( $images[ $key ] );
					continue;
				}
			}
		}

		// Extract image sources.
		$images = wp_list_pluck( $images, 'src' );

		return $images;
	}

	/**
	 * Simple getter method.
	 * @return array $images Array of images.
	 */
	public function get_images() {
		return $this->images;
	}

	/**
	 * Get the domain for current URL
	 * @param  string $url Current site's URL
	 * @return string $domain Domain for the current site.
	 */
	public function get_domain( $url ) {
		return parse_url( $url, PHP_URL_HOST );
	}
}

function WDS_Image_Grabber( $url ) {
	return new WDS_Image_Grabber( $url );
}