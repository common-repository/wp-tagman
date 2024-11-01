<?php
/**
 * WP TagMan
 *
 * Plugin Name: WP TagMan
 * Plugin URI:        https://adamainsworth.co.uk/plugins/
 * Description: 	  Allows you to place Google Tag Manager into your Wordpress Site
 * Version:           1.0.0
 * Author:            Adam Ainsworth
 * Author URI:        https://adamainsworth.co.uk/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-tagman
 * Domain Path:       /languages
 * Requires at least: 4.0.4
 * Tested up to:      5.8.1 
 */

 // redirect if some comes directly
if ( ! defined( 'WPINC' ) && ! defined( 'ABSPATH' ) ) {
	header('Location: /'); die;
}

// check that we're not defined somewhere else
if ( ! class_exists( 'WP_TagMan' ) ) {
	class WP_TagMan {
		private function __construct() {}

		public static function activate() {
	        if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			add_option("wp_tagman_gtm_code", 'Default', '', 'yes');
		}

		public static function deactivate() {
	        if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			delete_option('wp_tagman_gtm_code');
		}

		public static function uninstall() {
	        if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			if ( __FILE__ !== WP_UNINSTALL_PLUGIN ) {
				return;
			}
			 
			$option_name = 'wp_tagman_options';
			delete_option($option_name);
			delete_site_option($option_name);
		}

		public static function init() {
			add_action( 'wp_footer', [__CLASS__, 'main'] );
			add_action( 'admin_menu', [__CLASS__, 'add_admin_menu'] );
			add_action( 'admin_init', [__CLASS__, 'options_init'] );
			add_filter( 'plugin_action_links', [ __CLASS__, 'add_links' ], 10, 2 );
		}

		public static function main() {
			$options = get_option( 'wp_tagman_options' );
			$gtm_code = $options['gtm_code'];

			// don't fire if user logged in
			// also, forget about it if there's no GTM
			if( $gtm_code && !is_user_logged_in() ) : ?>
				<!-- Google Tag Manager -->
					<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo( $gtm_code ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
					<script id="wp-tagman-script">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo( $gtm_code ); ?>');</script>
					<script>jQuery('body').prepend( jQuery('#wp-tagman-script') );</script>
				<!-- End Google Tag Manager -->
			<?php endif;
		}
						
		// add links to section on plugins page
		public static function add_links( $links, $file ) {
			if ( $file === 'wp-tagman/wp-tagman.php' && current_user_can( 'manage_options' ) ) {	
				
				$links = (array) $links;
				$links[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=wp_tagman' ), __( 'Settings', 'wp-tagman' ) );
			}

			return $links;
		}
		
		// add the item to the admin menu
		public static function add_admin_menu() { 
			add_options_page(
				'WP TagMan',
				'WP TagMan',
				'manage_options',
				'wp_tagman',
				[__CLASS__, 'options_page_render']
			);
		}
		
		// set up options and settings fields
		public static function options_init() { 
			register_setting( 'wp_tagman_options', 'wp_tagman_options' );
		
			add_settings_section(
				'wp_tagman_options_section', 
				__( '', 'wp-tagman' ), 
				[__CLASS__, 'settings_render'], 
				'wp_tagman_options'
			);
		
			add_settings_field( 
				'gtm_code', 
				__( 'GTM code', 'wp-tagman' ), 
				[__CLASS__, 'gtm_code_render'], 
				'wp_tagman_options', 
				'wp_tagman_options_section' 
			);
		}
		
		// render options page
		public static function options_page_render() { 
			?>
				<form action='options.php' method='post'>		
					<h2>WP TagMan</h2>

					<?php
						settings_fields( 'wp_tagman_options' );
						do_settings_sections( 'wp_tagman_options' );
						submit_button();
					?>
				</form>
			<?php
		}
		
		// render settings section
		public static function settings_render() { 
			?><p><strong><?php echo __( 'You must enter the code you were given by GTM to enable the plugin to function properly.', 'wp-tagman' ); ?></strong></p><?php
		}
		
		// render settings fields
		public static function gtm_code_render() {
			$options = get_option( 'wp_tagman_options' );
			?>
			<input name="wp_tagman_options[gtm_code]" type="text" value="<?php echo( $options['gtm_code'] ); ?>" class="regular-text code" />
			<?php
		}		
	}

	register_activation_hook( __FILE__, [ 'WP_TagMan', 'activate' ] );
	register_deactivation_hook( __FILE__, [ 'WP_TagMan', 'deactivate' ] );
	register_uninstall_hook( __FILE__, [ 'WP_TagMan', 'uninstall' ] );
	add_action( 'plugins_loaded', [ 'WP_TagMan', 'init' ] );
}
