<?php
 /**
  * The class-wptip-theme-info.php file.
  *
  * This holds a main class that can be used to get information about a theme
  * that comes from the wordpress.org themes API. It caches calles on a theme
  * by theme bases.
  *
  * @package WP_Themes_Information_Plugin
  */

/**
 * This is a singleton class with methods to perform API calls and return
 * formatted strings via shortcode containing information about a specific
 * theme defined by a slug.
 */
class WPTIP_Theme_Info {

	/**
	 * This is used to hold a base to use for the transient for holding the data.
	 *
	 * @var string
	 */
	public static $transient_base = 'WPTIP_themeinfo_';

	/**
	 * Initiates the class as singleton
	 *
	 * @return object an instance of this class.
	 */
	public static function init() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new WPTIP_Theme_Info;
		}
		return $instance;
	}

	/**
	 * Constructor function for the class. It adds some things like scripts,
	 * shortcodes and widgets.
	 */
	private function __construct() {
		add_shortcode( 'theme-info', array( $this, 'get_with_shortcode' ) );
	}

	/**
	 * Function used to either retrieve some theme information from the
	 * wordpress.org themes API or to return a transient with the data if it
	 * already exists.
	 *
	 * @param  string $slug should be a valid theme slug.
	 *
	 * @return object       a json encoded object containing all the theme infroamtion avaiable through the API. Returns a WP error object on failure.
	 */
	public static function get_theme_info( $slug = '' ) {
		// To operate we need to have a slug that isn't an empty string.
		if ( '' !== $slug ) {

			if ( $slug ) {
				// if we have a slug then form our url.
				$url = esc_url_raw( 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=' . esc_attr( $slug ) );

				// get what this themes transient name is from the base + slug.
				$theme_transient = WPTIP_Theme_Info::$transient_base . $slug;
				// get the expiery time on the transient.
				$data_timeout = get_option( '_transient_timeout_' . $theme_transient );

				// data_timeout will exist if a transient exists for this theme
				// slug. Also test if it's expired.
				if ( $data_timeout && ! $data_timeout < time() ) {
					// get the transient as it's saved and not expired.
					$info = get_transient( $theme_transient );
					// transient should hold a json object.
					if ( is_object( $info ) ) {
						// return the full json object from the transient.
						return $info;
					}
				} else {
					/**
					 * If we have a seemingly valid url and an existing transient
					 * for this themes information doesn't exist, or is expired,
					 * then we'll use the url to make a GET request for the theme
					 * info in json format.
					 */
					if ( $url ) {
						// since we have a valid url make a get request.
						$info = WPTIP_Theme_Info::get_remote_themeinfo( $url, $slug );

						return $info;

					}
				}
			} // End if().
		} else {
			// we didn't get a slug passed.
			return false;
		} // End if().
	}

	/**
	 * Use wp_remote_get to make a request to ,org themes API asking for
	 * information about a specific theme by slug.
	 *
	 * @param  string $url  this should already be a valid url with paramiters attached.
	 * @param  string $slug a string containing the slugh of theme to get.
	 * @return object       a json object with theme information.
	 */
	public static function get_remote_themeinfo( $url = '', $slug ) {
		if ( $url && $slug ) {
			// since we have a valid url make a get request.
			$response = wp_remote_get( $url ); // WPCS: OK!

			// the response should be an array.
			if ( is_array( $response ) ) {
				// first check if we got a status code of 200 = success.
				if ( 200 === $response['response']['code'] ) {
					// this should be a json object with theme info.
					$info = $response['body'];
					// decode the json.
					$info = json_decode( $info );

					// save this info as a transient for 24 hours.
					$saved = set_transient( WPTIP_Theme_Info::$transient_base . $slug, $info, 60 * 60 * 24 );

					// return the full json object.
					return $info;
				}
			}
		}
	}

	/**
	 * Function to generate some markup for a shortcode to output various pieces
	 * of information about at heme from a json object.
	 *
	 * @param  array $atts array of options expecting at least 'slug' of a theme in the array. Can also pass a specific field, deafult is 'name'.
	 * @return string      a string with html markup representing an item of information about a theme.
	 */
	public function get_with_shortcode( $atts = array() ) {
		$defaults = array(
			'slug'  => '',
			'field' => 'name',
		);
		$atts = wp_parse_args( $atts, $defaults );
		// we always need a slug passed.
		if ( '' !== $atts['slug'] ) {
			/**
			 * Since we have a slug then try get the theme info for it. The
			 * call here should return a json object containing theme info.
			 * It will either be pulled from a transient or make a GET request
			 * to pull the info from remote API.
			 */
			$info = $this->get_theme_info( $atts['slug'] );

			// $info should be an object (json object).
			if ( is_object( $info ) ) {
				// confirm we have a 'field' string passed.
				if ( $atts['field'] ) {

					/**
					 * Check that the filed string passed is a valid field key.
					 */
					$field = $atts['field'];
					if ( ! $this->validate_field_id( $field ) ) {
						// Not valid. This is a fail. Return false.
						return false;
					}
					// get the specific fields contents for use directly later.
					$data = $info->$field;
					/**
					 * Depending on the type of 'field' we have sanitization
					 * function and output methods may be different.
					 *
					 * This switch statement decides on the appropriate
					 * sanitization. Defaults to 'esc_html'.
					 */
					switch ( $atts['field'] ) {

						case 'preview_url':
							$sanitizer = 'esc_url';
							break;
						case 'screenshot_url':
							$sanitizer = 'esc_url';
							break;
						case 'homepage':
							$sanitizer = 'esc_url';
							break;
						case 'download_link':
							$sanitizer = 'esc_url';
							break;

						case 'ratings':
							$sanitizer = 'absint';
							break;
						case 'num_ratings':
							$sanitizer = 'absint';
							break;
						case 'downloaded':
							$sanitizer = 'absint';
							break;

						default:
							$sanitizer = 'esc_html';

					}

					/**
					 * The kind of sanitizer used lets us know the specific
					 * html markup and sanitization filters to use before
					 * returning the markup to the shortcode requester.
					 *
					 * Some items are numbers, others urls, defaults to html.
					 */
					switch ( $sanitizer ) {
						case 'absint':
							$html = '<span class="wptip-info">' . absint( $data ) . '</span>';
							break;
						case 'esc_url':
							$html = '<a href=" ' . esc_url( $data ) . '" class="wptip-info" alt="' . esc_attr( $info->sections->description ) . '">' . esc_html( $info->name ) . '</a>';
							break;

						default:
							$html = '<span class="wptip-info">' . esc_html( $data ) . '</span>';
					}
					/**
					 * If we were able to generate a string of html then return
					 * it or else return false for failure.
					 */
					if ( $html ) {
						// Success.
						return $html;
					} else {
						// Fail.
						return false;
					}
				} // End if().
			} // End if().
		} // End if().
	}

	/**
	 * An array of supported field keys.
	 *
	 * @return array the array of supported field keys.
	 */
	private function valid_field_ids() {
		// TODO: Add ability to get 'sections', 'description' and 'tags' array.
		$array = array(
			'name',
			'slug',
			'version',
			'preview_url',
			'author',
			'screenshot_url',
			'rating',
			'num_ratings',
			'downloaded',
			'last_updated',
			'homepage',
			'download_link',
		);
		return $array;
	}
	/**
	 * Validate a field key string against the array of valid keys.
	 *
	 * @param  string $field  a field key.
	 * @return booleen        true for valid, false for not valid.
	 */
	private function validate_field_id( $field ) {
		// if the key is in the valid keys array then it's valid, otherwise
		// it's invalid and should fail.
		if ( in_array( $field, $this->valid_field_ids(), true ) ) {
			return true;
		} else {
			return false;
		}
	}
}
