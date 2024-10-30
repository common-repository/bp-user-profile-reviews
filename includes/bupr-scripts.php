<?php
/**
 * Class to add custom scripts and styles.
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 * @package BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BUPRScriptsStyles' ) ) {
	/**
	 * Class to add custom scripts and styles.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @author   Wbcom Designs
	 */
	class BUPRScriptsStyles {

		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function __construct() {

			// Add Scripts only on reviews tab.
			add_action( 'wp_enqueue_scripts', array( $this, 'bupr_custom_variables' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'bupr_styles_method' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'bupr_admin_custom_variables' ) );
		}

		/**
		 * Actions performed for enqueuing scripts and styles for site front
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_custom_variables() {
			global $bupr;
			wp_enqueue_style( 'bupr-reviews-css', BUPR_PLUGIN_URL . 'assets/css/bupr-reviews.css', array(), BUPR_PLUGIN_VERSION, false);
			wp_enqueue_style( 'bupr-font-awesome', 'https://use.fontawesome.com/releases/v5.4.2/css/all.css', array(), BUPR_PLUGIN_VERSION, false );
			wp_enqueue_style( 'bupr-front-css', BUPR_PLUGIN_URL . 'assets/css/bupr-front.css', array(), BUPR_PLUGIN_VERSION, false );
			wp_enqueue_script( 'bupr-front-js', BUPR_PLUGIN_URL . 'assets/js/bupr-front.js', array( 'jquery' ), BUPR_PLUGIN_VERSION, true );
			$title_review   = bupr_profile_review_tab_singular_slug();
			$cur_name       = bp_get_displayed_user_fullname();
			$reviews_titles = array(
				'cur_username' => $cur_name,
				'review_title' => $title_review,
				'review_nonce' => wp_create_nonce('review-nonce'),
			);
			wp_localize_script( 'bupr-front-js', 'mail_title', $reviews_titles );

		}

		/**
		 * Actions performed for enqueuing styles for site front
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_styles_method() {
			global $bupr;
			$bupr_star_color = $bupr['rating_color'];
			$custom_css = ".bupr-star-rate {
			        			color: {$bupr_star_color} !important;
			        		}
				";
			wp_add_inline_style( 'bupr-front-css', $custom_css );
		}

		/**
		 * Actions performed for enqueuing scripts and styles for admin page
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_admin_custom_variables() {
			
			if ( ! wp_script_is( 'wbcom-admin-setting-js', 'enqueued' ) ) {
				wp_enqueue_script( 'wbcom-admin-setting-js', BUPR_PLUGIN_URL . 'admin/wbcom/assets/js/wbcom-admin-setting.js', array( 'jquery' ) , BUPR_PLUGIN_VERSION, false);
			}
			if ( function_exists( 'get_current_screen' ) ) {
				$screen = get_current_screen();
				if ( ( isset( $_GET['page'] ) && 'bp-member-review-settings' === $_GET['page'] ) || ( isset( $_GET['post_type'] ) && 'review' === $_GET['post_type'] ) ) { // phpcs:ignore
					wp_enqueue_script( 'jquery' );
					wp_enqueue_script( 'jquery-ui-sortable' );
					if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
						wp_enqueue_style( 'font-awesome', '//use.fontawesome.com/releases/v5.5.0/css/all.css', array(), BUPR_PLUGIN_VERSION, false );
					}
					if ( ! wp_style_is( 'wbcom-select2-css', 'enqueued' ) ) {
						wp_enqueue_style( 'wbcom-select2-css', BUPR_PLUGIN_URL . 'admin/assets/css/select2.min.css', array(), BUPR_PLUGIN_VERSION, false );
					}
					if ( ! wp_script_is( 'wbcom-select2-js', 'enqueued' ) ) {
						wp_enqueue_script( 'wbcom-select2-js', BUPR_PLUGIN_URL . 'admin/assets/js/select2.min.js', array( 'jquery' ), BUPR_PLUGIN_VERSION, false );
					}
					wp_enqueue_script( 'bupr-js-admin', BUPR_PLUGIN_URL . 'admin/assets/js/bupr-admin.js', array( 'jquery' ), BUPR_PLUGIN_VERSION, false );

					wp_localize_script(
						'bupr-js-admin',
						'bupr_admin_ajax_object',
						array(
							'ajaxurl'     		  => admin_url( 'admin-ajax.php' ),
							'success_msz' 		  => esc_html__( 'Already Installed & Activated', 'bp-member-reviews' ),
							'error_msz'   		  => esc_html__( 'There was a problem performing the action.', 'bp-member-reviews' ),
							'number_validation'   => esc_html__( '* Number input not allowed *', 'bp-member-reviews' ),
							'nonce'           	  => wp_create_nonce( 'bupr_member_review_ajax' ),
						)
					);
					wp_enqueue_style( 'bupr-css-admin', BUPR_PLUGIN_URL . 'admin/assets/css/bupr-admin.css', array(), BUPR_PLUGIN_VERSION, false  );
					if ( ! wp_script_is( 'wp-color-picker', 'enqueued' ) ) {
						wp_enqueue_style( 'wp-color-picker' );
					}
					/* add wp color picker */
					wp_enqueue_script( 'bupr-color-picker', BUPR_PLUGIN_URL . 'admin/assets/js/bupr-color-picker.js', array( 'wp-color-picker' ), BUPR_PLUGIN_VERSION, false );
				}
			}
		}
	}
	new BUPRScriptsStyles();
}
