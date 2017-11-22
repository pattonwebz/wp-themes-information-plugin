<?php

class WPTIP_Theme_Info {

	/**
	 * This is used to hold a base to use for the transient for holding the data.
	 *
	 * @var string
	 */
	public static $transient_base = 'WPTIP_themeinfo_';

	public function __construct() {
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

				$theme_transient = WPTIP_Theme_Info::$transient_base . $slug;
				$data_timeout = get_option( '_transient_timeout_' . $theme_transient );

				/**
				 * If we have a seemingly valid url and an existing transient
				 * for this themes information doesn't exist, or it is expired,
				 * then we'll use the url to make a GET request for the theme
				 * info in json format.
				 */
				if ( $data_timeout && ! $data_timeout < time() ) {

					$info = get_transient( $theme_transient );

					return $info;

				} else {

					if ( $url ) {
						// since we have a valid url make a get request.
						$response = wp_remote_get( $url );

						// the response should be an array.
						if ( is_array( $response ) ) {
							// first check if we got a status code of 200 = success.
							if ( 200 === $response['response']['code'] ) {
								$info = $response['body']; // this should be a json object with theme info.

								$info = json_decode( $info );

								// save this info as a transient for 24 hours.
								$saved = set_transient( $theme_transient, $info, 60 * 60 * 24 );

								return $info;
							}
						}
					}
				}
			} // End if().
		} else {
			// we didn't get a slug passed.
			return false;
		} // End if().
	}

	public function get_with_shortcode( $atts ) {
		$defaults = array(
			'slug'  => '',
			'field' => 'name',
		);
		$atts = wp_parse_args( $atts, $defaults );
		// we always need a slug passed.
		if ( '' !== $atts['slug'] ) {
			$info = $this->get_theme_info( $atts['slug'] );
			if ( is_object( $info ) ) {
				if ( $atts['field'] ) {
					// TODO: check field is valid.
					$field = $atts['field'];
					$data = $info->$field;
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
					if ( $html ) {
						return $html;
					}
				} // End if().
			} // End if().
		} // End if().
	}
}
