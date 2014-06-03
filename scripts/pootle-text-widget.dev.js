// TinyMCE initialization parameters
var tinyMCEPreInit;
// Current editor
var wpActiveEditor;

(function( $ ) {
	// Activate visual editor
	function pootle_activate_visual_editor(id) {
		$( '#' + id ).addClass( 'mceEditor' );
		if ( typeof tinyMCE == 'object' && typeof tinyMCE.execCommand == 'function' ) {
			pootle_deactivate_visual_editor( id );
			tinyMCEPreInit.mceInit[id] = tinyMCEPreInit.mceInit['pootle-text-widget-widget'];
			tinyMCEPreInit.mceInit[id]['selector'] = '#' + id;
			try {
				// Instantiate new TinyMCE editor
				tinymce.init( tinymce.extend( {}, tinyMCEPreInit.mceInit['pootle-text-widget-widget'], tinyMCEPreInit.mceInit[id] ) );
				tinyMCE.execCommand( 'mceAddControl', false, id );
			} catch( e ) {
				alert( e );
			}
			if ( typeof tinyMCE.get( id ).on == 'function' ) {
				tinyMCE.get( id ).on( 'keyup change', function() {
					var content = tinyMCE.get( id ).getContent();
					$( 'textarea#' + id ).val( content ).change();
				});
			}
		}
	}
	// Deactivate visual editor
	function pootle_deactivate_visual_editor( id ) {
		if ( typeof tinyMCE == 'object' && typeof tinyMCE.execCommand == 'function' ) {
			if ( tinyMCE.get( id ) != null && typeof tinyMCE.get( id ).getContent == 'function' ) {
				var content = tinyMCE.get( id ).getContent();
				// tinyMCE.execCommand('mceRemoveControl', false, id);
				tinyMCE.get( id ).remove();
				$( 'textarea#' + id ).val( content );
			}
		}
	}
	// Activate editor deferred (used upon opening the widget)
	function pootle_open_deferred_activate_visual_editor( id ) {
		$( 'div.widget-inside:has(#' + id + ') input[id^=widget-pootle-text-widget][id$=type][value=visual]' ).each(function() {
			// If textarea is visible and animation/ajax has completed (or in accessibility mode) then trigger a click to Visual button and enable the editor
			if ( $('div.widget:has(#' + id + ') :animated' ).size() == 0 && tinyMCE.get( id ) == null && $( '#' + id ).is( ':visible' ) ) {
				$( 'a[id^=widget-pootle-text-widget][id$=visual]', $( this ).closest( 'div.widget-inside' ) ).click();
			}
			// Otherwise wait and retry later (animation ongoing)
			else if ( tinyMCE.get( id ) == null ) {
				setTimeout(function() {
					pootle_open_deferred_activate_visual_editor( id );
					id = null;
				}, 100 );
			}
			// If editor instance is already existing (i.e. dragged from another sidebar) just activate it
			else {
				$( 'a[id^=widget-pootle-text-widget][id$=visual]', $( this ).closest( 'div.widget-inside' ) ).click();
			}
		});
	}
	
	// Activate editor deferred (used upon ajax requests)
	function pootle_ajax_deferred_activate_visual_editor( id ) {
		$( 'div.widget-inside:has(#' + id + ') input[id^=widget-pootle-text-widget][id$=type][value=visual]' ).each(function() {
			// If textarea is visible and animation/ajax has completed then trigger a click to Visual button and enable the editor
			if ( $.active == 0 && tinyMCE.get( id ) == null && $( '#' + id ).is( ':visible' ) ) {
				$( 'a[id^=widget-pootle-text-widget][id$=visual]', $( this ).closest( 'div.widget-inside' ) ).click();
			}
			// Otherwise wait and retry later (animation ongoing)
			else if ( $( 'div.widget:has(#' + id + ') div.widget-inside' ).is( ':visible' ) && tinyMCE.get( id ) == null ) {
				setTimeout(function() {
					pootle_ajax_deferred_activate_visual_editor( id );
					id=null;
				}, 100 );
			}
		});
	}
	

	
	// Document ready stuff
	$( document ).ready(function() {
		// Event handler for widget opening button
		$( document ).on( 'click', 'div.widget:has(textarea[id^=widget-pootle-text-widget]) .widget-title, div.widget:has(textarea[id^=widget-pootle-text-widget]) a.widget-action', function( event ) {
			//event.preventDefault();
			var $widget = $( this ).closest( 'div.widget' );
			var $text_area = $( 'textarea[id^=widget-pootle-text-widget]', $widget );
			// Event handler for widget saving button (for new instances)
			$( 'input[name=savewidget]', $widget ).on( 'click', function( event ) {
				var $widget = $( this ).closest( 'div.widget' )
				var $text_area = $( 'textarea[id^=widget-pootle-text-widget]', $widget );
				if ( tinyMCE.get( $text_area.attr( 'id' ) ) != null ) {
					pootle_deactivate_visual_editor( $text_area.attr( 'id' ) );
				}
				// Event handler for ajax complete
				$( this ).unbind( 'ajaxSuccess' ).ajaxSuccess( function( event, xhr, settings ) {
					var $text_area = $( 'textarea[id^=widget-pootle-text-widget]', $( this ).closest( 'div.widget-inside') );
					pootle_ajax_deferred_activate_visual_editor( $text_area.attr( 'id' ) );
				});
			});
			$( '#wpbody-content' ).css( 'overflow', 'visible' ); // needed for small screens
			$widget.css( 'position', 'relative' ).css( 'z-index', '100' ); // needed for small screens
			pootle_open_deferred_activate_visual_editor( $text_area.attr( 'id' ) );
			$( '.insert-media', $widget ).data( 'editor', $text_area.attr( 'id' ) );
		});
		// Event handler for widget saving button (for existing instances)
		$( 'div.widget[id*=pootle-text-widget] input[name=savewidget]').on( 'click', function( event ) {
			var $widget = $( this ).closest( 'div.widget' )
			var $text_area = $( 'textarea[id^=widget-pootle-text-widget]', $widget );
			if ( tinyMCE.get( $text_area.attr( 'id' ) ) != null ) {
				pootle_deactivate_visual_editor( $text_area.attr( 'id' ) );
			}
			// Event handler for ajax complete
			$( this ).unbind( 'ajaxSuccess' ).ajaxSuccess( function( event, xhr, settings ) {
				var $text_area = $( 'textarea[id^=widget-pootle-text-widget]', $( this ).closest( 'div.widget-inside' ) );
				pootle_ajax_deferred_activate_visual_editor( $text_area.attr( 'id' ) );
			});
		});
		// Event handler for visual switch button
		$( document ).on( 'click', 'a[id^=widget-pootle-text-widget][id$=visual]', function( event ) {
			//event.preventDefault();
			var $widget_inside = $( this ).closest( 'div.widget-inside,div.panel-dialog' );
			$( 'input[id^=widget-pootle-text-widget][id$=type]', $widget_inside ).val( 'visual' );
			$( this ).addClass( 'active' );
			$( 'a[id^=widget-pootle-text-widget][id$=html]', $widget_inside ).removeClass( 'active' );
			pootle_activate_visual_editor( $( 'textarea[id^=widget-pootle-text-widget]', $widget_inside ).attr( 'id' ) );
		});
		// Event handler for html switch button
		$( document ).on( 'click', 'a[id^=widget-pootle-text-widget][id$=html]', function( event ) {
			//event.preventDefault();
			var $widget_inside = $( this ).closest( 'div.widget-inside,div.panel-dialog' );
			$( 'input[id^=widget-pootle-text-widget][id$=type]', $widget_inside ).val( 'html' );
			$( this ).addClass( 'active' );
			$( 'a[id^=widget-pootle-text-widget][id$=visual]', $widget_inside ).removeClass( 'active' );
			pootle_deactivate_visual_editor( $( 'textarea[id^=widget-pootle-text-widget]', $widget_inside ).attr( 'id' ) );
		});
		// Set wpActiveEditor variables used when adding media from media library dialog
		$( document ).on( 'click', '.editor_media_buttons a', function() {
			var $widget_inside = $( this ).closest( 'div.widget-inside' );
			wpActiveEditor = $( 'textarea[id^=widget-pootle-text-widget]', $widget_inside ).attr( 'id' );
		});
		// Activate editor when in accessibility mode
		if ( $( 'body.widgets_access' ).size() > 0) {
			var $text_area = $( 'textarea[id^=widget-pootle-text-widget]' );
			pootle_open_deferred_activate_visual_editor( $text_area.attr( 'id' ) );
		}

	});
})( jQuery ); // end self-invoked wrapper function