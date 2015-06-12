/**
 * CMB2 Remote Image Select
 * http://webdevstudios.com
 *
 * Licensed under the GPLv2+ license.
 */

window.CMB2_Remote_Image_Select = ( function( window, document, $, undefined ) {
	'use strict';

	var app   = {},
        cmb2_ris = cmb2_remote_img_sel || {};

    app.cache = function() {
        app.$window = $( window );
        app.$body = $( document.body );
        app.$button = $( '.cmb2-remote-image-select.search.button' );
        app.$url = $( '.cmb2-remote-image-select.url' );
        app.$placeholder = $( '.cmb2-remote-image-select.images.placeholder' );
        app.$loader = $( '.cmb2-remote-image-select.images-loading-icon' );
    };

	app.init = function() {

        if ( ! cmb2_ris.ajaxurl || ! cmb2_ris.nonce || ! cmb2_ris.action ) {
            return false;
        }

		app.cache();

        $( 'body' ).on( 'click', '.cmb2-remote-image-select.search.button', app.search );
	};

    app.search = function( evt ){
        evt.preventDefault();
        app.$loader.show();
        jQuery.ajax({
            url:     cmb2_ris.ajaxurl,
            method:  'POST',
            timeout: 30000,
            data:    {
                nonce:      cmb2_ris.nonce,
                action:     cmb2_ris.action,
                field_name: app.$url.attr( 'name' ),
                field_id:   app.$url.attr( 'id' ).replace( '-url', '' ),
                url:        app.$url.val()
            },
            dataType: 'json'
        } ).done( app.finished_request ).fail( app.failed_request );
    };

    app.finished_request = function( data, textStatus, jqXHR ) {
        app.$loader.hide();
        app.$placeholder.html( '' );
        if ( data.success ) {
            app.$placeholder.append( data.data );
            app.$placeholder.show();
        } else {
            app.log( data );
        }
    };

    app.failed_request = function( jqXHR, textStatus, errorThrown ){
        app.$loader.hide();
        app.log( errorThrown );
    };

    app.log = function ( log_item ) {
        if ( window.console && cmb2_ris.script_debug ) {
            window.console.log( log_item );
        }
    };

	jQuery( document ).ready( app.init );

} ) ( window, document, jQuery );
