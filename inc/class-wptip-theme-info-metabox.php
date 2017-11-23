<?php
 /**
  * The class-wptip-theme-info-metabox.php file.
  *
  * Adds a metabox with some options on certain post type edit screens.
  *
  * @since 0.2.0
  * @package WP_Themes_Information_Plugin
  */

/**
 * Class to hold various metabox settings and render/save functions.
 */
class WPTIP_Theme_Info_Metabox {

	/**
	 * Holds the current instance of this class.
	 *
	 * @var object
	 */
	public static $instance = null;

	/**
	 * Initiates the class as singleton
	 *
	 * @return object an instance of this class.
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new WPTIP_Theme_Info_Metabox;
		}
		return self::$instance;
	}

	/**
	 * Constructor function for the class. It adds some things like scripts,
	 * shortcodes and widgets.
	 */
	private function __construct() {
		// adds a metabox for calling on edit screen.
		add_action( 'add_meta_boxes', array( $this, 'add_themeslug_metabox' ) );
		// an action to fire a save function for the metabox we add.
		add_action( 'save_post',      array( $this, 'save_themeslug_metabox' ) );
	}

	/**
	 * Function for adding a metabox in editor.
	 *
	 * @param string $post_type current post_type in edit screen.
	 */
	public function add_themeslug_metabox( $post_type ) {

		// this is a hardcoded list of post types to put the metabox on.
		$post_types = array( 'post', 'page', 'jetpack-portfolio' );
		// make the list of post_types filterable.
		$post_types = apply_filters( 'filter_wptip_metabox_post_types', $post_types );
		/**
		 * If the current post type we're editing is in the array of supported
		 * types then add the metabox to the edit screen. In sidebar, high.
		 */
		if ( in_array( $post_type, $post_types, true ) ) {
			add_meta_box(
				'wptip_themeslug_metabox',
				__( 'Theme Slug', 'wptip_themeslug_metabox_nonce' ),
				array( $this, 'render_themeslug_metabox_content' ),
				$post_type,
				'side',
				'high'
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_themeslug_metabox( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper
		 * authorization, because save_post can be triggered at other times.
		 */
		if ( ! isset( $_POST['wptip_themeslug_metabox_nonce'] ) ) {
			return $post_id;
		}
		$nonce = $_POST['wptip_themeslug_metabox_nonce'];
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wptip_themeslug_metabox_inner_nonce' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		/**
		 * Check if the users permissions allow the to edit and return if not.
		 */
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/**
		 * It's safe to sanitize and update post meta now.
		 */
		$data_slug = sanitize_text_field( $_POST['wptip_slug_field'] );
		// Update the meta field.
		update_post_meta( $post_id, '_wptip_theme_slug', $data_slug );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_themeslug_metabox_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wptip_themeslug_metabox_inner_nonce', 'wptip_themeslug_metabox_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, '_wptip_theme_slug', true );

		// Display the form, using the current value.
		?>
		<label for="wptip_slug_field">
			<?php echo esc_html( 'Enter a slug for the theme to attache to this page/post/CPT.', 'wp_themes_information_plugin' ); ?>
		</label>
		<input type="text" id="wptip_slug_field" name="wptip_slug_field" value="<?php echo esc_attr( $value ); ?>" size="25" />
		<?php
	}
}
