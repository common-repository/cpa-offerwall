<?php 

/*

Plugin Name: CPA OFFERWALL
Plugin URI:
Description: This plugin retrieves cpagrip json offers  and displays it in template as offerwall
Version: 1.0.2
Author: ZackSnyder
Author URI: https://profiles.wordpress.org/zacksnyder/
License: GPLv2 or later

*/


if(!defined('CPA_OW_PATH')){
	define('CPA_OW_PATH', plugin_dir_path(__FILE__) );
}
if(!defined('CPA_OW_URL')){
	define('CPA_OW_URL', plugin_dir_url(__FILE__) );
}

if ( ! function_exists( 'cpa_freemius' ) ) {
    // Create a helper function for easy SDK access.
    function cpa_freemius() {
        global $cpa_freemius;

        if ( ! isset( $cpa_freemius ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $cpa_freemius = fs_dynamic_init( array(
                'id'                  => '10408',
                'slug'                => 'cpa-offerwall',
                'type'                => 'plugin',
                'public_key'          => 'pk_cf2ae81a4872960633641639a686c',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'cpa-ow-settings',
                ),
            ) );
        }

        return $cpa_freemius;
    }

    // Init Freemius.
    cpa_freemius();
    // Signal that SDK was initiated.
    do_action( 'cpa_freemius_loaded' );
}






class CPA_OW{

	public function __construct()
	{
		$this->hooks();
		$this->init();
	}

	private function hooks(){

		add_action('admin_menu', array($this, 'cpa_ow_admin_menu_option'));

		add_action( 'wp_enqueue_scripts', array($this, 'cp_ow_theme_dequeue'), 99);

		add_action( 'wp_enqueue_scripts', array($this, 'cp_ow_enqueue'), 99 );

		add_action( 'admin_enqueue_scripts', array($this, 'enqueuing_admin_scripts'), 9999);


		add_filter('theme_page_templates', array($this, 'cpa_template_register'), 10,3);

		add_filter('template_include', array($this, 'cpa_template_select'), 99);
		
	}
	
	public function cpa_ow_admin_menu_option(){
		add_menu_page(
			'CPA OFFERWALL',
			'CPA OFFERWALL', 
			'manage_options', 
			'cpa-ow-settings', 
			array($this, 'cpa_ow_settings'), 'dashicons-money-alt', 22);
		add_submenu_page('cpa-ow-settings','CPA OW Settings', 
			'CPA OW Settings', 'administrator', 'cpa-ow-settings', array($this, 'cpa_ow_settings'));

	}

	private function init(){
		//settings conf file
		include CPA_OW_PATH . "settings/settings.php";
	}

	public function cpa_ow_settings(){
		//settings View File
		include CPA_OW_PATH . "settings/view.php";
	}
	
	public function cp_ow_theme_dequeue() {

		global $post;

		$page_temp_slug = get_page_template_slug($post->ID);

		$templates = $this->cpa_template_array();

		if(isset($templates[$page_temp_slug])){
		//remove theme stylesheet
			$wp_scripts = wp_scripts();
			$wp_styles  = wp_styles();
			$themes_uri = get_theme_root_uri();

			foreach ( $wp_scripts->registered as $wp_script ) {
				if ( strpos( $wp_script->src, $themes_uri ) !== false ) {
					wp_deregister_script( $wp_script->handle );
				}
			}

			foreach ( $wp_styles->registered as $wp_style ) {
				if ( strpos( $wp_style->src, $themes_uri ) !== false ) {
					wp_deregister_style( $wp_style->handle );
				}
			}

		}
	}

	public function cp_ow_enqueue() {

		global $post;

		$page_temp_slug = get_page_template_slug($post->ID);

		$templates = $this->cpa_template_array();

		if(isset($templates[$page_temp_slug])){
			// stylesheets and fonts
			wp_enqueue_style('bootstrap-styles', CPA_OW_URL.'templates/default-cpa-template/lib/css/bootstrap.min.css');
			wp_enqueue_style('cpa-ow-font-styles', CPA_OW_URL.'templates/default-cpa-template/lib/fonts/cpa-ow-fonts.css');
			wp_enqueue_style('cpa-ow-styles', CPA_OW_URL.'templates/default-cpa-template/lib/css/style.css');

    	// bootstrap script and custom js script
			wp_enqueue_script( 'bootstrap-script', CPA_OW_URL.'templates/default-cpa-template/lib/js/bootstrap.bundle.min.js');
			wp_enqueue_script( 'cpa-ow-script', CPA_OW_URL.'templates/default-cpa-template/lib/js/scripts.js');
		} 

		

		$script_params = array('cpa_ow_json_url' => get_option('cpa_ow_json_url'));

		wp_localize_script('cpa-ow-script', 'scriptParams', $script_params);

	}

	public function enqueuing_admin_scripts(){

		wp_enqueue_style('admin-cpa-ow-styles', CPA_OW_URL.'res/css/admin-style.css');
		wp_enqueue_style('admin-fontawesome-5', CPA_OW_URL.'res/fonts/all.min.css');

	}

	public function cpa_template_array(){

		$temps = [];
		$temps['default-cpa-template.php'] = "Default CPA Template";

		return $temps;
	}

	public function cpa_template_register($page_templates,$theme,$post){

		$templates = $this->cpa_template_array();

		foreach ($templates as $tk => $tv) {
			$page_templates[$tk] = $tv;
		}

		return $page_templates;

	}

	public function cpa_template_select($template){
		global $post,$query,$wpdb;

		$page_temp_slug = get_page_template_slug($post->ID);

		$templates = $this->cpa_template_array();

		if(isset($templates[$page_temp_slug])){
			$template = CPA_OW_PATH.'templates/default-cpa-template/'.$page_temp_slug;
		} 


		return $template;

	}

}


$CPA_OW = new CPA_OW();


?>