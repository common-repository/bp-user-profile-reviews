<?php
/**
 * Plugin Name: Wbcom Designs - BuddyPress Member Reviews
 * Plugin URI: https://wbcomdesigns.com/downloads/buddypress-user-profile-reviews/
 * Description: Enhances the BuddyPress community by allowing registered users to post reviews on other members' profiles. This feature is exclusive to registered members, ensuring unbiased feedback by preventing users from reviewing their own profiles.
 * Version: 3.3.1
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com
 * License: GPLv2+
 * Text Domain: bp-member-reviews
 * Domain Path: /languages
 *
 * @package BuddyPress_Member_Reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin version and path constants.
 */
define( 'BUPR_PLUGIN_VERSION', '3.3.1' );
define( 'BUPR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BUPR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin text-domain for translations.
 */
if ( ! function_exists( 'bupr_load_textdomain' ) ) {
	add_action( 'init', 'bupr_load_textdomain' );
	function bupr_load_textdomain() {
		$domain = 'bp-member-reviews';
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, BUPR_PLUGIN_PATH . 'languages/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
}

/**
 * Include required plugin files after BuddyPress is loaded.
 */
if ( ! function_exists( 'bupr_plugins_files' ) ) {
	add_action( 'plugins_loaded', 'bupr_plugins_files' );
	function bupr_plugins_files() {	
		if ( class_exists( 'BuddyPress' )  && _is_plugin_active()) {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bupr_admin_page_link' );
			
			// List of required files to include.
			$include_files = array(
				'includes/class-buprglobals.php',
				'admin/wbcom/wbcom-admin-settings.php',
				'includes/bupr-scripts.php',
				'admin/bupr-admin.php',
				'admin/class-bupr-admin-feedback.php',
				'includes/bupr-filters.php',
				'includes/bupr-shortcodes.php',
				'includes/widgets/display-review.php',
				'includes/widgets/member-rating.php',
				'includes/bupr-ajax.php',
				'includes/bupr-notification.php',
				'includes/bupr-general-functions.php',
			);

			foreach ( $include_files as $file ) {
				include_once BUPR_PLUGIN_PATH . $file;
			}
		}
	}
}

/**
 * check whether the plugin is active or not.
 *
 * @return boolean
 */
function _is_plugin_active(){
	if ( is_multisite() ) {
		$active_plugins = get_site_option('active_sitewide_plugins');
		if(empty($active_plugins)){
			$active_plugins = get_option('active_plugins');
			if(in_array( plugin_basename( __FILE__ ) , $active_plugins )){
				return true;
			}
		}
		if(array_key_exists( plugin_basename( __FILE__ ) , $active_plugins )){
			return true;
		}
	} else {
		$active_plugins = get_option('active_plugins');
		if(in_array( plugin_basename( __FILE__ ) , $active_plugins )){
			return true;
		}
	}
	return false;
}

/**
 * Add settings link to plugin action links.
 *
 * @param array $links The plugin setting links array.
 *
 * @return array
 */
function bupr_admin_page_link( $links ) {
	$settings_link = array(
		'<a href="' . admin_url( 'admin.php?page=bp-member-review-settings' ) . '">' . esc_html__( 'Settings', 'bp-member-reviews' ) . '</a>',
	);
	return array_merge( $links, $settings_link );
}

/**
 * Deactivate the plugin if BuddyPress is not active.
 */

 function bupr_requires_buddypress() {
		$network_active_plugins = get_site_option( 'active_sitewide_plugins' );
		if(empty($network_active_plugins)){
			$network_active_plugins = get_option('active_plugins');
			$network_active_plugins = array_flip($network_active_plugins);
		}
        if ( ! class_exists( 'BuddyPress' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            add_action( 'admin_notices', 'bupr_required_plugin_admin_notice' );
        }
		elseif( is_multisite() ){
			if ( !is_network_admin() ) {
				global $current_blog;
				$current_blog_id = (int) $current_blog->blog_id;
				switch_to_blog( $current_blog_id );
				if (!array_key_exists( plugin_basename( __FILE__ ) , $network_active_plugins )) {
					deactivate_plugins( plugin_basename( __FILE__ ) );	
				} 	
				restore_current_blog();
			}else{
				if(! array_key_exists( 'buddypress/bp-loader.php' , $network_active_plugins ) ){
					deactivate_plugins( plugin_basename( __FILE__ ) );	
					add_action( 'admin_notices', 'bupr_required_plugin_admin_notice' );
				}
			}
		}
}
add_action( 'admin_init', 'bupr_requires_buddypress' );


/**
 * Deactivate the plugin if BuddyPress is not active on multisite.
 */
function modify_sitewide_plugins($value) {
	if(is_multisite()){
		global $current_blog;
		$current_blog_id = (int) $current_blog->blog_id;
		$dependent_plugin_basename = 'buddypress/bp-loader.php';
		if ( !is_network_admin() ) {
				if( ! class_exists( 'BuddyPress' ) ){
					unset($value[plugin_basename( __FILE__ )]);
					show_admin_notice('bupr_required_plugin_admin_notice');
				}else{
					if(defined( 'BP_ROOT_BLOG' ) && ( BP_ROOT_BLOG != $current_blog_id )){
						unset($value[plugin_basename( __FILE__ )]);
						show_admin_notice('bupr_show_buddypress_root_blog_notice');
					}elseif(!defined( 'BP_ROOT_BLOG' ) &&  !is_main_site($current_blog_id) ){
						unset($value[plugin_basename( __FILE__ )]);
						show_admin_notice('bupr_show_buddypress_root_blog_notice');
					}
				}
		}else{
			if( ! class_exists( 'BuddyPress' ) ){
				unset($value[plugin_basename( __FILE__ )]);
				show_admin_notice('bupr_required_plugin_admin_notice');
			}
		}
	}
    return $value;
}

add_filter('site_option_active_sitewide_plugins', 'modify_sitewide_plugins');

/**
 * Show admin notice for plugin when active action is performed.
 */
function show_admin_notice($admin_notice){
	if( isset($_GET['activate']) ) {
		add_action( 'admin_notices', $admin_notice);
		unset($_GET['activate']);
	}
}

/**
 * Admin notice to indicate BuddyPress is required.
 */
function bupr_required_plugin_admin_notice() {
	$plugin_name = esc_html__( 'BuddyPress Member Reviews', 'bp-member-reviews' );
	$bp_plugin   = esc_html__( 'BuddyPress', 'bp-member-reviews' );
	echo '<div class="error"><p>';
	printf(
		 /* translators: %1$s: BuddyPress Member Reviews; %2$s: Buddypress. */
		esc_html__( '%1$s requires %2$s to be installed and active.', 'bp-member-reviews' ),
		'<strong>' . esc_html( $plugin_name ) . '</strong>',
		'<strong>' . esc_html( $bp_plugin ) . '</strong>'
	);
	echo '</p></div>';
}

/**
 * Show admin notice to inform the user that To-Do List can only be activated on the BuddyPress root blog.
 */
function bupr_show_buddypress_root_blog_notice() {
    echo '<div class="notice notice-error error is-dismissible">';
    echo '<p>' . esc_html__( 'Wbcom Designs BP Member Review can only be activated on the BuddyPress root blog.', 'bp-member-reviews' ) . '</p>';
    echo '</div>';
}

/**
 * Redirect to plugin settings page after activation.
 */
function bupr_activation_redirect_settings( $plugin ) {
	if ( $plugin === plugin_basename( __FILE__ ) && class_exists( 'BuddyPress' ) && ! is_multisite() ) {
		wp_redirect( admin_url( 'admin.php?page=bp-member-review-settings' ) );
		exit;
	}
}
add_action( 'activated_plugin', 'bupr_activation_redirect_settings' );

/**
 * Perform plugin activation redirect.
 */
function bp_member_review_do_activation_redirect() {
	if ( get_transient( '_bp_member_review_is_new_install' ) && ! is_multisite() ) {
		delete_transient( '_bp_member_review_is_new_install' );
		wp_safe_redirect( admin_url( 'admin.php?page=bp-member-review-settings' ) );
	}
}
add_action( 'admin_init', 'bp_member_review_do_activation_redirect' );

/**
 * Translate site URL using WPML for frontend use.
 *
 * @param string $url The URL to translate.
 *
 * @return string Translated URL.
 */
function bupr_site_url( $url ) {
	if ( ! is_admin() && false === strpos( $url, 'wp-admin' ) ) {
		return untrailingslashit( apply_filters( 'wpml_home_url', $url ) );
	}
	return $url;
}

/**
 * Unschedule the cron event on plugin deactivation.
 */
function bupr_unschedule_review_recalculation() {
    $timestamp = wp_next_scheduled( 'bupr_cron_recalculate_user_reviews_batch' );
    if ( $timestamp ) {
        wp_unschedule_event( $timestamp, 'bupr_cron_recalculate_user_reviews_batch' );
    }
    delete_option( 'bupr_current_batch' ); // Clean up the stored batch number
}
register_deactivation_hook( __FILE__, 'bupr_unschedule_review_recalculation' );

