<?php
/**
 * Plugin Name: Quick Download
 * Description: Download Themes and Pluigns directly from WordPress Dashboard.
 * Plugin URI: http://sajib.me/plugins/quick-download
 * Author: Sajib Talukder
 * Author URI: http://sajib.me
 * Version: 1.0.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-download 
 * Domain Path: /i18n
 */

defined( 'ABSPATH' ) || exit;

//  Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class Quick_Download {

	/**
	 * plugin version
	 * @var string
	 */
	const version = '1.0.0';
	
	/**
	 * class constructor
	 */
	function __construct(){
		$this->define_constants();
		register_activation_hook( __FILE__ , [ $this, 'activation' ] ); 
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

    /**
     * Initializes a singletone instance 
     * @return \Quick_Download
     */
	public static function init () {
		static $instance = false;
		if( ! $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Define the required plugin constants
	 * @return void
	 */
	public function define_constants() {
		define( 'QD_VERSION', self::version );    
		define( 'QD_FILE', __FILE__ );    
		define( 'QD_PATH', __DIR__ );    
		define( 'QD_URL', plugins_url( '', QD_FILE ) );    
		define( 'QD_ASSETS', QD_URL . '/assets' );    
	}


	public function init_plugin() {
 
		if( is_admin() ){
            // enqueue assets
            new Quick\Download\Assets();
            // plugin downlaod acctions 
            add_filter('plugin_action_links', [$this, 'add_plugin_action_links'], 10, 4);
            // download plugin functionality
            add_action( 'admin_init', [ $this, 'download_plugin' ] );
            // downlaod theme functionality
            add_action( 'admin_init', [ $this, 'download_theme' ] );
		}
	}
    // add dowload action link
    public function add_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
        $plugin_file = explode( '/', $plugin_file );
        if ( isset( $plugin_file[0] ) ) {
            $extra_params = ( isset( $_GET['plugin_status'] ) && in_array( $_GET['plugin_status'], array( 'mustuse', 'dropins' ) ) ? '&quick_download_plugin_status=' . $_GET['plugin_status'] : '' );
            $actions = array_merge( $actions, array( '<a href="' . admin_url( 'plugins.php?quick_download_plugin=' . $plugin_file[0] . $extra_params ) . '">' . __( 'Download ZIP', 'quick-download' ) . '</a>' ) );
        }
        return $actions;
    }

    /**
     * download theme
     */
    public function download_theme() {
        $themes = wp_get_themes();
        if( is_user_logged_in() && current_user_can( 'switch_themes' ) && isset( $_GET['quick_download_theme'] ) && !empty( $_GET['quick_download_theme'] ) && array_key_exists( $_GET['quick_download_theme'], $themes ) ){

            // The name of theme slug
            $theme_slug = $_GET['quick_download_theme'];
        
            // theme direcotry 
            $theme_dir = $this->get_theme_dir();

            $this->download($theme_slug, $theme_dir);
                 
        }
    }

    /**
     * download plugin
     */
    public function download_plugin() {
        if ( isset( $_GET['quick_download_plugin'] ) && is_user_logged_in() && current_user_can( 'activate_plugins' ) ) {

            // The name of plugin slug
            $plugin_slug = $_GET['quick_download_plugin'];
        
            // plugin direcotry
            $plugin_dir = $this->get_plugin_dir();

            $this->download($plugin_slug, $plugin_dir);
                 
        }
    }

    /** 
     * Download Start
     */
    public function download($file_slug, $root_dir) {
        
        // Prepare full path do the desired object
        $path = $root_dir . '/' . $file_slug;
        // Filename for a zip package
        $fileName = $file_slug . '.zip';
        
        // Temporary file name in upload dir
        $upload_dir = wp_upload_dir();
        $tmpFile = trailingslashit($upload_dir['path']) . $fileName;
    
        $this->create_zip($tmpFile, $path, $root_dir );
        $this->send_header($fileName );
        $this->delete_tmpFile($tmpFile );

        exit;
    }

    /** 
     * create zip
     */
    public function create_zip($tmpFile, $path, $root_dir ) {
        if ( file_exists( $tmpFile ) ) {
            unlink( $tmpFile );
        }
        
        if(!class_exists('PclZip')){
            // Load class file if it's not loaded yet
            include ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }

        // Create new archive
        $archive = new PclZip($tmpFile);
        // Add entire folder to the archive
        $archive->add($path, PCLZIP_OPT_REMOVE_PATH, $root_dir);
    }

    // Send Header
    public function send_header($fileName ) {
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }

    /**
     * delete temporarry file
     */
    public function delete_tmpFile($tmpFile ) {
        // Read file content directly
        readfile($tmpFile);
        // Remove zip file
        unlink($tmpFile);
    }

    /**
     * get plugin dir
     */
    public function get_plugin_dir( $status = false ) {
        if ( ! $status ) {
            $status = ( isset( $_GET['quick_download_plugin_status'] ) ? $_GET['quick_download_plugin_status'] : 'plugin' );
        }
        switch ( $status ) {
            case 'mustuse':
                return WPMU_PLUGIN_DIR;
            case 'dropins':
                return WP_CONTENT_DIR;
            default: // 'plugin'
                return WP_PLUGIN_DIR;
        }
    }

    /**
     * get theme dir
     */
    public function get_theme_dir() {
        return WP_CONTENT_DIR . '/themes';
    }

	/**
	 * Do stuff uplon plugin activation
	 * @return void
	 */
	public function activation() {
		$installed = get_option( 'quick_download_installed' );
		if( !$installed ){
			update_option( 'quick_download_installed', time() );
		}
		update_option( 'quick_download_version', QD_VERSION );
	}
}

/**
 * Initializes the main plugin
 * @return \Quick_Download
 */
function Quick_Download() {
	return Quick_Download::init();
}

// kick-off the plugin
quick_download();
