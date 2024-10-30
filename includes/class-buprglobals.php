<?php
/**
 * Class to define all the global variables related to the plugin.
 *
 * @since    1.0.9
 * @package  BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BUPRGlobals' ) ) {

	/**
	 * Class to add global variables for this plugin.
	 *
	 * @since    1.0.9
	 * @access   public
	 */
	class BUPRGlobals {

		/**
		 * Constructor to hook into init action.
		 *
		 * @since    1.0.9
		 * @access   public
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'bupr_review_globals' ), 10 );
		}

		/**
		 * Define all global variables related to this plugin.
		 *
		 * @since    1.0.9
		 * @access   public
		 */
		public function bupr_review_globals() {
			global $bupr, $wp_roles;

			// Check if $bupr has already been set to avoid reinitializing it.
			if ( isset( $bupr ) && is_array( $bupr ) ) {
				return; // Exit the function to prevent overwriting global variables.
			}

			// Ensure $wp_roles is set correctly.
			if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) || ! isset( $wp_roles->roles ) ) {
				$wp_roles = wp_roles();
			}

			// Get all available roles for pre-population.
			$available_roles = array_keys( $wp_roles->roles );

			// Get settings from options.
			$bupr_general_settings  = get_option( 'bupr_admin_general_options', array() );
			$bupr_criteria_settings = get_option( 'bupr_admin_settings', array() );
			$bupr_display_settings  = get_option( 'bupr_admin_display_options', array() );

			/**
			 * Global variables for general settings.
			 */
			$auto_approve_reviews    = $bupr_general_settings['bupr_auto_approve_reviews'] ?? 'yes';  // Default to 'yes'
			$reviews_per_page        = $bupr_general_settings['profile_reviews_per_page'] ?? 5;        // Default to 5
			$dir_view_ratings        = $bupr_general_settings['bupr_member_dir_reviews'] ?? 'yes';     // Default to 'yes'
			$dir_view_review_btn     = $bupr_general_settings['bupr_member_dir_add_reviews'] ?? 'yes'; // Default to 'yes'
			$allow_email             = $bupr_general_settings['bupr_allow_email'] ?? 'yes';            // Default to 'yes'
			$allow_notification      = $bupr_general_settings['bupr_allow_notification'] ?? 'yes';     // Default to 'yes'
			$allow_update            = $bupr_general_settings['bupr_allow_update'] ?? 'yes';           // Default to 'yes'
			$exclude_given_members   = $bupr_general_settings['bupr_exc_member'] ?? $available_roles;   // Prepopulate with all available roles
			$add_taken_members       = $bupr_general_settings['bupr_add_member'] ?? $available_roles;   // Prepopulate with all available roles
			$multi_reviews           = $bupr_general_settings['bupr_multi_reviews'] ?? 'yes';           // Default to 'yes'
			$hide_button             = $bupr_general_settings['bupr_hide_review_button'] ?? 'no';       // Default to 'no'
			$anonymous_reviews       = $bupr_general_settings['bupr_enable_anonymous_reviews'] ?? 'no'; // Default to 'no'
			$review_activity         = $bupr_general_settings['bupr_enable_review_activity'] ?? 'no';  // Default to 'yes'
			$review_email_subject    = $bupr_general_settings['review_email_subject'] ?? esc_html__('You have received a new review!', 'bp-member-reviews');
			$review_email_message    = $bupr_general_settings['review_email_message'] ?? esc_html__('A new review has been submitted on your profile. Log in to see the details.', 'bp-member-reviews');

			/**
			 * Global variables for display settings.
			 */
			$review_label        = $bupr_display_settings['bupr_review_title'] ?? esc_html__( 'Review', 'bp-member-reviews' );
			$review_label_plural = $bupr_display_settings['bupr_review_title_plural'] ?? esc_html__( 'Reviews', 'bp-member-reviews' );
			$rating_color        = $bupr_display_settings['bupr_star_color'] ?? '#FFC400'; // Keep yellow color for stars

			/**
			 * Global variables for criteria settings.
			 */
			$bupr_multi_criteria_allowed = ( isset( $bupr_criteria_settings['profile_multi_rating_allowed'] ) && ! empty( $bupr_criteria_settings['profile_multi_rating_allowed'] ) ) ? '1' : '0'; // Enable multi-criteria by default
			$active_rating_fields        = $bupr_criteria_settings['profile_rating_fields'] ?? array();   // Can be configured by admin

			// Define global variables for use throughout the plugin.
			$bupr = array(
				'anonymous_reviews'      => $anonymous_reviews,
				'auto_approve_reviews'   => $auto_approve_reviews,
				'dir_view_ratings'       => $dir_view_ratings,
				'dir_view_review_btn'    => $dir_view_review_btn,
				'multi_reviews'          => $multi_reviews,
				'hide_review_button'     => $hide_button,
				'reviews_per_page'       => $reviews_per_page,
				'allow_email'            => $allow_email,
				'allow_notification'     => $allow_notification,
				'allow_update'           => $allow_update,
				'exclude_given_members'  => $exclude_given_members,
				'add_taken_members'      => $add_taken_members,
				'rating_color'           => $rating_color,
				'review_label'           => $review_label,
				'review_label_plural'    => $review_label_plural,
				'multi_criteria_allowed' => $bupr_multi_criteria_allowed,
				'active_rating_fields'   => $active_rating_fields,
				'review_activity'        => $review_activity,
			);
		}
	}

	// Initialize the class.
	new BUPRGlobals();
}
