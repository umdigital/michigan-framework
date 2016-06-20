(function($){
    $(document).ready(function(){
        $('.mfwWidget').each(function(){
            var id = $(this).attr( 'id' );
            var name = id.replace( /^(zone|widget)-/, '' ).replace( /[-_]/g, ' ' );

            if( $(this).find('.row .columns').length ) {
                var elem = $(this).find('.row .columns');
            }
            else {
                var elem = $(this);
            }

            elem.prepend('<div id="'+ id +'-description" class="mfwWidgetDescription" title="'+ name +'"><h6>'+ name +'</h6><p>debugging block</p></div>');
        });
    });
}(jQuery));

function mfwToggleWidgets()
{
    jQuery('body').toggleClass( 'mfwDebugWidgets' );

    if( jQuery( 'body' ).hasClass( 'mfwDebugWidgets' ) ) {
        jQuery( '#wp-admin-bar-toggle-widgets' ).find( 'a.ab-item' ).text( 'Hide Widget Areas' );
    }
    else {
        jQuery( '#wp-admin-bar-toggle-widgets' ).find( 'a.ab-item' ).text( 'Show Widget Areas' );
    }

    return false;
}

function mfwToggleGrid()
{
    jQuery('body').toggleClass( 'mfwDebugGrid' );

    if( jQuery( 'body' ).hasClass( 'mfwDebugGrid' ) ) {
        jQuery( '#wp-admin-bar-toggle-grid' ).find( 'a.ab-item' ).text( 'Hide Grid' );
    }
    else {
        jQuery( '#wp-admin-bar-toggle-grid' ).find( 'a.ab-item' ).text( 'Show Grid' );
    }

    return false;
}
