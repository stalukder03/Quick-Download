<?php 

namespace Quick\Download;
 
/**
 * Scripts and Styles Class
 */
class Assets {

    function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'add_theme_download_links' ] );
    }  

    public function add_theme_download_links( $hook ) {
        if( $hook == 'themes.php' ){
            wp_enqueue_script(
                'quick-theme-download-links',
                QUICK_DOWNLOAD_ASSETS . '/js/script.js',
                [ 'jquery' ],
                md5_file( QUICK_DOWNLOAD_ASSETS . '/js/script.js' ),
                true
            );

            wp_localize_script( 'quick-theme-download-links', 'quick_download_obj', array(
                'download_button_label' => __( 'Download ZIP', 'quick-download' ),
            ) );
        }
	}
}