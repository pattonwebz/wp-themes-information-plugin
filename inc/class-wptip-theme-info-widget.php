<?php

/**
 * Class to render a widget, it's form and to deal with saving it's data.
 *
 * This widget is used to output theme information obtained from the official
 * wordpress.org themes API and format it into a nicely presented table.
 *
 * The data is sourced from another class which is needed in order for this
 * class to properly function.
 */
class WPTIP_Theme_Info_Widget extends WP_Widget {

	/**
	 * Constructor function that uses parent contructor to add it's widget.
	 */
	public function __construct() {
		$widget_options = array(
			'classname' => 'wptip-widget',
			'description' => __( 'A widget to putput some theme information if a slug is set.', 'wp_themes_information_plugin' ),
		);
		parent::__construct( 'wptip-widget', 'Theme Info Widget', $widget_options );
	}

	/**
	 * This is the main render function for the widget. It handles ouputing
	 * the markup and getting any date for use in that markup. Echos the $html
	 * once it is generated. Returns 'false' on fail.
	 *
	 * @param  array $args     an array of the widget args.
	 * @param  array $instance an array with the current widget instance data.
	 */
	public function widget( $args, $instance ) {
		// Get the theme slug from current posts post_meta.
		global $post;
		$slug = get_post_meta( $post->ID, '_wptip_theme_slug', true );

		// if we don't have a slug then return. Fail.
		if ( ! $slug ) {
			return false;
		}

		// Get all the info about the theme, by slug.
		$info = WPTIP_Theme_info::get_theme_info( $slug );

		// make sure we got an object (should be a json object with theme info).
		if ( is_object( $info ) ) {

			/**
			 * This is the array of field keys and nicenames that we want info
			 * for, in the order we will use them.
			 *
			 * @var array
			 */
			$fields = array(
				'name'         => __( 'Theme Name:', 'wp_themes_information_plugin' ),
				'version'      => __( 'Current Version:', 'wp_themes_information_plugin' ),
				'last_updated' => __( 'Last Updated:', 'wp_themes_information_plugin' ),
				'downloaded'   => __( 'Times Downloaded:', 'wp_themes_information_plugin' ),
				'preview_url'  => __( 'Demo Url:', 'wp_themes_information_plugin' ),
				'homepage'     => __( 'Download Link:', 'wp_themes_information_plugin' ),
			);
			// allow these fields to be filtered to add/remove items or reorder.
			$fields = apply_filters( 'wptip_widget_fields', $fields );

			// before using the title apply any widget_title filters.
			ob_start();
			echo $args['before_widget'] . $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			?>
			<table class="wptip-theme-details-widget table">
				<tbody class="table-hover">
					<?php
					foreach ( $fields as $key => $field ) {	?>
						<tr class="wptip-widget-row">
							<td class="wptip-widget-col-header"><?php echo esc_html( $field ); ?></td>
							<?php
							/**
							 * Some of the items we might output could be links.
							 * This tests for that and outputs correct markup
							 * and modified text for them in those cases.
							 */
							if ( 'preview_url' === $key || 'homepage' === $key ) {
								$text = '';
								/**
								 * This default should never fire, if it does
								 * there is a fallback in place to output just
								 * the theme name as text.
								 */
								switch ( $key ) {
									case 'preview_url':
										$text = $info->name . ' demo';
										break;
									case 'homepage':
										$text = 'Download ' . $info->name;
										break;
									default:
										$text = $info->name;
								}
								// this is a link with some text. ?>
								<td class="wptip-widget-col-info"><a href="<?php echo esc_url( $info->$key ); ?>" class="wptip-info btn"><?php echo esc_html( apply_filters( 'filter_theme_link_text', $text, $info, $key ) ); ?></a></td>
							<?php
							} else {
								// this is just text. ?>
								<td class="wptip-widget-col-info"><?php echo esc_html( $info->$key ); ?></td>
							<?php
							} ?>
						</tr>
					<?php
					} // End foreach(). ?>
				</tbody>
			</table>
			<?php // echo the after widget args.
			echo $args['after_widget'];
			// get all of the buffered content that we injected/echoed.
			$html = ob_get_clean();
			// echo the full $html.
			echo $html;
		} // End if().
	}

	/**
	 * A render method to output a form for widget settings in the widget UI.
	 *
	 * @param  array $instance array of data attached to the widget instance.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title_themename = ! empty( $instance['title_themename'] ) ? $instance['title_themename'] : '0'; ?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Title:</label>
		<input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p><?php
	}

	/**
	 * Sanitize new_instance data, merge with old instance data and return the
	 * maybe updated instance.
	 *
	 * @param  array $new_instance new instance values.
	 * @param  array $old_instance values from the old instance.
	 * @return array               a maybe updated instance array.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
}
