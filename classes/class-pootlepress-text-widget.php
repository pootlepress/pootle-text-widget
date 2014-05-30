<?php


/* Widget class */
class Pootle_Text_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_pootle_tinymce', 'description' => __( 'Arbitrary text or HTML with visual editor', 'pp-ptw' ) );
		$control_ops = array( 'width' => 800, 'height' => 800 );
		parent::__construct( 'pootle-text-widget', __( 'Pootle Text Widget', 'pp-ptw' ), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		if ( get_option( 'embed_autourls' ) ) {
			$wp_embed = $GLOBALS['wp_embed'];
			add_filter( 'widget_text', array( $wp_embed, 'run_shortcode' ), 8 );
			add_filter( 'widget_text', array( $wp_embed, 'autoembed' ), 8 );
		}
		extract( $args );
		$title = apply_filters( 'widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base );
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		if ( function_exists( 'icl_t' ) ) {
			$title = icl_t( "Widgets", 'widget title - ' . md5 ( $title ), $title );
			$text = icl_t( "Widgets", 'widget body - ' . $this->id_base . '-' . $this->number, $text );
		}
		$text = do_shortcode( $text );
		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
?>
			<div class="textwidget"><?php echo $text; ?></div>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		if ( current_user_can('unfiltered_html') ) {
			$instance['text'] =  $new_instance['text'];
		}
		else {
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		}
		$instance['type'] = strip_tags( $new_instance['type'] );
		if ( function_exists( 'icl_register_string' )) {
			//icl_register_string( "Widgets", 'widget title - ' . $this->id_base . '-' . $this->number /* md5 ( apply_filters( 'widget_title', $instance['title'] ))*/, apply_filters( 'widget_title', $instance['title'] ) ); // This is handled automatically by WPML
			icl_register_string( "Widgets", 'widget body - ' . $this->id_base . '-' . $this->number, apply_filters( 'widget_text', $instance['text'] ) );
		}
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'type' => 'visual' ) );
		$title = strip_tags( $instance['title'] );
		if ( function_exists( 'esc_textarea' ) ) {
			$text = esc_textarea( $instance['text'] );
		}
		else {
			$text = stripslashes( wp_filter_post_kses( addslashes( $instance['text'] ) ) );
		}
		$type = esc_attr( $instance['type'] );
		if ( get_bloginfo( 'version' ) < "3.5" ) {
			$toggle_buttons_extra_class = "editor_toggle_buttons_legacy";
			$media_buttons_extra_class = "editor_media_buttons_legacy";
		}
		else {
			$toggle_buttons_extra_class = "wp-toggle-buttons";
			$media_buttons_extra_class = "wp-media-buttons";
		}
?>
		<input id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" type="hidden" value="<?php echo esc_attr( $type ); ?>" />
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<div class="editor_toggle_buttons hide-if-no-js <?php echo $toggle_buttons_extra_class; ?>">
			<a id="widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-html"<?php if ( $type == 'html' ) {?> class="active"<?php }?>><?php _e( 'HTML' ); ?></a>
			<a id="widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-visual"<?php if ( $type == 'visual' ) {?> class="active"<?php }?>><?php _e(' Visual' ); ?></a>
		</div>
		<div class="editor_media_buttons hide-if-no-js <?php echo $media_buttons_extra_class; ?>">
			<?php do_action( 'media_buttons' ); ?>
		</div>
		<div class="editor_container">
			<textarea class="widefat" rows="20" cols="40" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
		</div>
		<div class="editor_links"></div>
<?php
	}
}
