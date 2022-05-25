jQuery(document).ready( function($){
    "use strict";
    
    if ( $('.themes .theme .theme-actions').length > 0 ){
        $( '.themes .theme .theme-actions' ).each( function() {
            var theme_slug = $( this ).parents( '.theme' ).attr( 'data-slug' );
            $( this ).append( '<a class="button button-primary quick_download_theme" href="?quick_download_theme=' + theme_slug + '">' + quick_download_obj.download_button_label + '</a>' );
        } );
    }    
});
