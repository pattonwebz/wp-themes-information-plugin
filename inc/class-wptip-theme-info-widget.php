<?php

class WPTIP_Theme_Info_Widget extends WP_Widget {
	public function __construct() {
		$widget_options = array(
			'classname' => 'wptip-widget',
			'description' => 'A widget to putput some theme information if a slug is set.',
		);
		parent::__construct( 'wptip-widget', 'Theme Info Widget', $widget_options );
	}


	public function widget( $args, $instance ) {
		global $post;
		error_log( print_r( $post, true ), 0 );
		$slug = get_post_meta( $post->ID, '_my_meta_value_key', false );
		$slug = $slug[0];
		if ( ! $slug ) {
			return false;
		}

		$info = WPTIP_Theme_info::get_theme_info( $slug );
		if ( is_object( $info ) ) {
			$fields = array(
				'name' => 'Theme Name:',
				'version' => 'Current Version:',
				'last_updated' => 'Last Updated:',
				'downloaded' => 'Times Downloaded:',
				'preview_url' => 'Demo Url:',
				'homepage' => 'Download Link:',
			);
			// allow these fields to be filtered to add/remove items and to
			// reorder if wanted.
			$fields = apply_filters( 'wptip_widget_fields', $fields );
			// make and echo the title wrapped in sidebar args.
			$title = apply_filters( 'widget_title', $instance['title'] );
			ob_start();
			echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title'];
			?>
			<table class="wptip-theme-details-widget">
				<?php
				foreach ( $fields as $key => $field ) {
					error_log( 'widget3', 0 );
					?>
					<tr class="wptip-widget-row">
						<td class="wptip-widget-col-header"><?php echo esc_html( $field ); ?></td>
						<?php
						if ( 'preview_url' === $field || 'homepage' === $field ) { ?>
							<td class="wptip-widget-col-info"><a href="<?php esc_url( $info->$field ); ?>" class="wptip-info"><?php esc_html( $info->name ); ?></a></td>
						<?php
						} else { ?>
							<td class="wptip-widget-col-info"><?php echo esc_html( $info->$key ); ?></td>
						<?php
						} ?>
					</tr>
				<?php
				}
			?>
			</table>
			<?php // echo the after widget args.
			echo $args['after_widget'];
			$html = ob_get_clean();
			echo $html;
		}
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title_themename = ! empty( $instance['title_themename'] ) ? $instance['title_themename'] : '0'; ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
		<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
		</p><?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
}
