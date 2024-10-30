<?php
/**
 * Plugin review class.
 * Prompts users to give a review of the plugin on WordPress.org after a period of usage.
 *
 * Heavily based on code by Rhys Wynne
 * https://winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/
 *
 * @package Bp_Job_Manager
 */

if ( ! class_exists( 'Bupr_Admin_Feedback' ) ) :

	/**
	 * The feedback.
	 */
	class Bupr_Admin_Feedback {

		/**
		 * Slug.
		 *
		 * @var string $slug
		 */
		private $slug;

		/**
		 * Name.
		 *
		 * @var string $name
		 */
		private $name;

		/**
		 * Time limit.
		 *
		 * @var int $time_limit
		 */
		private $time_limit;

		/**
		 * No Bug Option.
		 *
		 * @var string $nobug_option
		 */
		public $nobug_option;

		/**
		 * Activation Date Option.
		 *
		 * @var string $date_option
		 */
		public $date_option;

		/**
		 * Class constructor.
		 *
		 * @param array $args Arguments for plugin feedback.
		 */
		public function __construct( $args ) {
			$this->slug = $args['slug'];
			$this->name = $args['name'];

			$this->date_option  = $this->slug . '_activation_date';
			$this->nobug_option = $this->slug . '_no_bug';

			// Allow a custom time limit, default to one week.
			$this->time_limit = isset( $args['time_limit'] ) ? (int) $args['time_limit'] : WEEK_IN_SECONDS;

			// Add actions.
			add_action( 'admin_init', array( $this, 'check_installation_date' ) );
			add_action( 'admin_init', array( $this, 'set_no_bug' ), 5 );
		}

		/**
		 * Convert seconds to human-readable format.
		 *
		 * @param int $seconds Number of seconds to convert.
		 * @return string Human-readable time difference.
		 */
		public function seconds_to_words( $seconds ) {
			// Years.
			$years = intval( ( $seconds ) / YEAR_IN_SECONDS );
			if ( $years > 1 ) {
				 /* translators: %s: years. */
				return sprintf( __( '%s years', 'bp-member-reviews' ), $years );
			} elseif ( $years > 0 ) {
				return __( 'a year', 'bp-member-reviews' );
			}

			// Weeks.
			$weeks = intval( ( $seconds ) / WEEK_IN_SECONDS );
			if ( $weeks > 1 ) {
				 /* translators: %s: weeks. */
				return sprintf( __( '%s weeks', 'bp-member-reviews' ), $weeks );
			} elseif ( $weeks > 0 ) {
				return __( 'a week', 'bp-member-reviews' );
			}

			// Days.
			$days = intval( ( $seconds ) / DAY_IN_SECONDS );
			if ( $days > 1 ) {
				 /* translators: %s: days. */
				return sprintf( __( '%s days', 'bp-member-reviews' ), $days );
			} elseif ( $days > 0 ) {
				return __( 'a day', 'bp-member-reviews' );
			}

			// Hours.
			$hours = intval( ( $seconds ) / HOUR_IN_SECONDS );
			if ( $hours > 1 ) {
				 /* translators: %s: hours. */
				return sprintf( __( '%s hours', 'bp-member-reviews' ), $hours );
			} elseif ( $hours > 0 ) {
				return __( 'an hour', 'bp-member-reviews' );
			}

			// Minutes.
			$minutes = intval( ( $seconds ) / MINUTE_IN_SECONDS );
			if ( $minutes > 1 ) {
				 /* translators: %s: minutes. */
				return sprintf( __( '%s minutes', 'bp-member-reviews' ), $minutes );
			} elseif ( $minutes > 0 ) {
				return __( 'a minute', 'bp-member-reviews' );
			}

			return __( 'a moment', 'bp-member-reviews' );
		}

		/**
		 * Check installation date and trigger review notice.
		 */
		public function check_installation_date() {
			if ( ! get_site_option( $this->nobug_option ) ) {
				// Set the activation date if it doesn't exist.
				if ( ! get_site_option( $this->date_option ) ) {
					add_site_option( $this->date_option, time() );
				}

				// Check if the time limit has passed.
				$install_date = get_site_option( $this->date_option );
				if ( ( time() - $install_date ) > $this->time_limit ) {
					add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
				}
			}
		}

		/**
		 * Display admin notice to request a review.
		 */
		public function display_admin_notice() {
			$screen = get_current_screen();

			if ( isset( $screen->base ) && 'plugins' === $screen->base ) {
				$no_bug_url = wp_nonce_url( admin_url( '?' . $this->nobug_option . '=true' ), 'bp-member-reviews-feedback-nonce' );
				$time       = $this->seconds_to_words( time() - get_site_option( $this->date_option ) );
				?>
				<div class="notice updated bp-member-reviews-notice">
					<div class="bp-member-reviews-notice-inner">
						<div class="bp-member-reviews-notice-icon">
							<img src="<?php echo esc_url( BUPR_PLUGIN_URL . '/admin/wbcom/assets/imgs/bp_member_review.png' ); ?>" alt="<?php esc_attr_e( 'BuddyPress Member Reviews', 'bp-member-reviews' ); ?>" />
						</div>
						<div class="bp-member-reviews-notice-content">
							<h3><?php esc_html_e( 'Are you enjoying BuddyPress Member Reviews?', 'bp-member-reviews' ); ?></h3>
							<p><?php 
							 /* translators: %s: Plugin name. */
							 printf( esc_html__( 'We hope you\'re enjoying %1$s! Could you please do us a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'bp-member-reviews' ), esc_html( $this->name ) ); ?></p>
						</div>
						<div class="bp-member-reviews-install-now">
							<a href="https://wordpress.org/support/plugin/bp-user-profile-reviews/#new-post" class="button button-primary bp-member-reviews-install-button" target="_blank"><?php esc_html_e( 'Leave a Review', 'bp-member-reviews' ); ?></a>
							<a href="<?php echo esc_url( $no_bug_url ); ?>" class="no-thanks"><?php esc_html_e( 'No thanks / I already have', 'bp-member-reviews' ); ?></a>
						</div>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Set the option to no longer show the review notice.
		 */
		public function set_no_bug() {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bp-member-reviews-feedback-nonce' ) && isset( $_GET[ $this->nobug_option ] ) && current_user_can( 'manage_options' ) ) {
				add_site_option( $this->nobug_option, true );
			}
		}
	}
endif;

/**
 * Instantiate the Bupr_Admin_Feedback class.
 */
new Bupr_Admin_Feedback(
	array(
		'slug'       => 'bp_member_review',
		'name'       => __( 'BuddyPress Member Reviews', 'bp-member-reviews' ),
		'time_limit' => WEEK_IN_SECONDS, // You can set custom time here.
	)
);
