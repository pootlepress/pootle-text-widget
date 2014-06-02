<?php

global $pootle_text_widget_version;
global $pootle_text_widget_dev_mode;
$pootle_text_widget_version = "1.0.0"; // This is used internally - should be the same reported on the plugin header
$pootle_text_widget_dev_mode = true;

/* Widget initialization */
add_action( 'widgets_init', 'pootlepress_text_widgets_init' );
function pootlepress_text_widgets_init() {
    if ( ! is_blog_installed() )
        return;
    register_widget( 'Pootle_Text_Widget' );
}

/* Add actions and filters (only in widgets admin page) */
add_action( 'admin_init', 'pootlepress_text_widget_admin_init' );
function pootlepress_text_widget_admin_init() {
    global $pagenow;
    $load_editor = false;
    if ( $pagenow == "widgets.php" || $pagenow == "customize.php" ) {
        $load_editor = true;
    }
    // Compatibility for WP Page Widget plugin
    if ( is_plugin_active('wp-page-widget/wp-page-widgets.php' ) && (
            ( in_array( $pagenow, array( 'post-new.php', 'post.php') ) ) ||
            ( in_array( $pagenow, array( 'edit-tags.php' ) ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) ||
            ( in_array( $pagenow, array( 'admin.php' ) ) && isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'pw-front-page', 'pw-search-page' ) ) )
        ) ) {
        $load_editor = true;
    }
    if ( $load_editor ) {
        add_action( 'admin_head', 'pootle_text_widget_load_tiny_mce' );
        add_filter( 'tiny_mce_before_init', 'pootle_text_widget_init_editor', 20 );
        add_action( 'admin_print_scripts', 'pootle_text_widget_scripts' );
        add_action( 'admin_print_styles', 'pootle_text_widget_styles' );
        add_action( 'admin_print_footer_scripts', 'pootle_text_widget_footer_scripts' );
        add_filter( 'atd_load_scripts', '__return_true'); // Compatibility with Jetpack After the deadline
    }
}

/* Instantiate tinyMCE editor */
function pootle_text_widget_load_tiny_mce() {
    // Remove filters added from "After the deadline" plugin, to avoid conflicts
    // Add support for thickbox media dialog
    add_thickbox();
    // New media modal dialog (WP 3.5+)
    if ( function_exists( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }
}

/* TinyMCE setup customization */
function pootle_text_widget_init_editor( $initArray ) {
    global $pagenow;
    // Remove WP fullscreen mode and set the native tinyMCE fullscreen mode
    if ( get_bloginfo( 'version' ) < "3.3" ) {
        $plugins = explode(',', $initArray['plugins']);
        if ( isset( $plugins['wpfullscreen'] ) ) {
            unset( $plugins['wpfullscreen'] );
        }
        if ( ! isset( $plugins['fullscreen'] ) ) {
            $plugins[] = 'fullscreen';
        }
        $initArray['plugins'] = implode( ',', $plugins );
    }
    // Remove the "More" toolbar button (only in widget screen)
    if ( $pagenow == "widgets.php" ) {
        $initArray['theme_advanced_buttons1'] = str_replace( ',wp_more', '', $initArray['theme_advanced_buttons1'] );
    }
    // Do not remove linebreaks
    $initArray['remove_linebreaks'] = false;
    // Convert newline characters to BR tags
    $initArray['convert_newlines_to_brs'] = false;
    // Force P newlines
    $initArray['force_p_newlines'] = true;
    // Force P newlines
    $initArray['force_br_newlines'] = false;
    // Do not remove redundant BR tags
    $initArray['remove_redundant_brs'] = false;
    // Force p block
    $initArray['forced_root_block'] = 'p';
    // Apply source formatting
    $initArray['apply_source_formatting '] = true;
    // Return modified settings
    return $initArray;
}


/* Widget js loading */
function pootle_text_widget_scripts() {
    global $pootle_text_widget_version, $pootle_text_widget_dev_mode;
    wp_enqueue_script('media-upload');
    if ( get_bloginfo( 'version' ) >= "3.3" ) {
        wp_enqueue_script( 'wplink' );
        wp_enqueue_script( 'wpdialogs-popup' );
        wp_enqueue_script( 'pootle-text-widget-widget', plugins_url('scripts/pootle-text-widget' . ($pootle_text_widget_dev_mode ? '.dev' : '' ) . '.js', __FILE__ ), array( 'jquery', 'editor' ), $pootle_text_widget_version, true );
    }
    else {
        //wp_enqueue_script( 'pootle-text-widget-widget-legacy', plugins_url('scripts/pootle-text-widget-legacy' . ($pootle_text_widget_dev_mode? '.dev' : '' ) . '.js', __FILE__ ), array( 'jquery', 'editor' ), $pootle_text_widget_version, true );
    }
}

/* Widget css loading */
function pootle_text_widget_styles() {
    global $pootle_text_widget_version;
    if ( get_bloginfo( 'version' ) < "3.3" ) {
        wp_enqueue_style( 'thickbox' );
    }
    else {
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
    }
    wp_print_styles( 'editor-buttons' );

    global $woo_shortcode_generator;
    if (isset($woo_shortcode_generator)) {
        wp_register_style( 'woo-shortcode-icon', esc_url( $woo_shortcode_generator->framework_url() . 'css/shortcode-icon.css' ) );
        wp_enqueue_style( 'woo-shortcode-icon' );
    }

    wp_enqueue_style( 'pootle-text-widget', plugins_url( 'styles/pootle-text-widget.css', __FILE__ ), array(), $pootle_text_widget_version );
}


/* Footer script */
function pootle_text_widget_footer_scripts() {
    // Setup for WP 3.1 and previous versions
    if ( get_bloginfo( 'version' ) < "3.2" ) {
        if ( function_exists( 'wp_tiny_mce' ) ) {
            wp_tiny_mce( false, array() );
        }
        if ( function_exists( 'wp_tiny_mce_preload_dialogs' ) ) {
            wp_tiny_mce_preload_dialogs();
        }
    }
    // Setup for WP 3.2.x
    else if ( get_bloginfo( 'version' ) < "3.3" ) {
        if ( function_exists( 'wp_tiny_mce' ) ) {
            wp_tiny_mce( false, array() );
        }
        if ( function_exists( 'wp_preload_dialogs') ) {
            wp_preload_dialogs( array( 'plugins' => 'wpdialogs,wplink,wpfullscreen' ) );
        }
    }
    // Setup for WP 3.3 - New Editor API
    else {
        wp_editor( '', 'pootle-text-widget-widget' );
    }
}

/* Support for Smilies */
add_filter( 'widget_text', 'pootle_text_widget_apply_smilies_to_widget_text' );
function pootle_text_widget_apply_smilies_to_widget_text( $text ) {
    if ( get_option( 'use_smilies' ) ) {
        $text = convert_smilies( $text );
    }
    return $text;
}

/* Hack needed to enable full media options when adding content form media library */
/* (this is done excluding post_id parameter in Thickbox iframe url) */
add_filter( '_upload_iframe_src', 'pootle_text_widget_upload_iframe_src' );
function pootle_text_widget_upload_iframe_src ( $upload_iframe_src ) {
    global $pagenow;
    if ( $pagenow == "widgets.php" || ( $pagenow == "admin-ajax.php" && isset ( $_POST['id_base'] ) && $_POST['id_base'] == "pootle-text-widget" ) ) {
        $upload_iframe_src = str_replace( 'post_id=0', '', $upload_iframe_src );
    }
    return $upload_iframe_src;
}

/* Hack for widgets accessibility mode */
add_filter( 'wp_default_editor', 'pootle_text_widget_editor_accessibility_mode' );
function pootle_text_widget_editor_accessibility_mode($editor) {
    global $pagenow;
    if ( $pagenow == "widgets.php" && isset( $_GET['editwidget'] ) && strpos( $_GET['editwidget'], 'pootle-text-widget' ) === 0 ) {
        $editor = 'html';
    }
    return $editor;
}

add_action( 'admin_init', 'pootle_text_widget_init_woo_shortcode' );

function pootle_text_widget_init_woo_shortcode() {
    global $pagenow;

    global $woo_shortcode_generator;

    if (!isset($woo_shortcode_generator)) {
        return;
    }

    if (get_user_option( 'rich_editing' ) == 'true' && ( in_array( $pagenow, array( 'widgets.php' ) ) ) )  {

        // Add the tinyMCE buttons and plugins.
        add_filter( 'mce_buttons', array( &$woo_shortcode_generator, 'filter_mce_buttons' ) );
        add_filter( 'mce_external_plugins', array( &$woo_shortcode_generator, 'filter_mce_external_plugins' ) );

        // Register the colourpicker JavaScript.
        wp_register_script( 'woo-colourpicker', esc_url( $woo_shortcode_generator->framework_url() . 'js/colorpicker.js' ), array( 'jquery' ), '3.6', true ); // Loaded into the footer.
        wp_enqueue_script( 'woo-colourpicker' );

        // Register the colourpicker CSS.
        wp_register_style( 'woo-colourpicker', esc_url( $woo_shortcode_generator->framework_url() . 'css/colorpicker.css' ) );
        wp_enqueue_style( 'woo-colourpicker' );

        // Register the custom CSS styles.
        wp_register_style( 'woo-shortcode-generator', esc_url( $woo_shortcode_generator->framework_url() . 'css/shortcode-generator.css' ) );
        wp_enqueue_style( 'woo-shortcode-generator' );

    } // End IF Statement
}
