<?php

/**
 * Class to serve filter Calls.
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 * @package BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BUPR_Custom_Hooks' ) ) {

	/**
	 * Class to add custom hooks for this plugin
	 *
	 * @since    1.0.0
	 * @author   Wbcom Designs
	 */
	class BUPR_Custom_Hooks {


		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function __construct() {
			add_action( 'wp', array( $this, 'bupr_member_profile_reviews_tab' ), 11 );
			if ( function_exists( 'buddypress' ) && buddypress()->buddyboss ) {
				add_action( 'bp_before_member_in_header_meta', array( $this, 'bupr_member_average_rating' ) );
			} else {
				add_action( 'bp_before_member_header_meta', array( $this, 'bupr_member_average_rating' ) );
			}
			add_action( 'youzify_after_profile_header_user_meta', array( $this, 'bupr_member_average_rating' ) );
			add_action( 'bp_setup_admin_bar', array( $this, 'bupr_setup_admin_bar' ), 10 );

			add_action( 'init', array( $this, 'bupr_add_bp_member_reviews_taxonomy_term' ) );
			add_filter( 'post_row_actions', array( $this, 'bupr_bp_member_reviews_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-review', array( $this, 'bupr_remove_edit_bulk_actions' ), 10, 1 );
			// add_action('bp_screens', array($this, 'bupr_view_review_tab_function_to_show_screen'));
			add_action( 'bp_member_header_actions', array( $this, 'bupr_add_review_button_on_member_header' ) );
			add_action( 'youzify_after_profile_header_user_meta', array( $this, 'bupr_add_review_button_on_member_header' ) );
			add_action( 'bp_activity_after_save', array( $this, 'bupr_add_activity_meta' ) );
			add_filter( 'bp_get_activity_action', array( $this, 'bupr_hide_username_in_activity' ), 10, 2 );
			add_filter( 'bp_get_activity_user_link', array( $this, 'bupr_change_user_link' ) );
			add_filter( 'bp_get_activity_avatar', array( $this, 'bupr_change_avatar_image' ));

			/*
			 * Add review link at member's directory if option admin setting is enabled.
			 */

			// Check if BuddyBoss is active first.
			if ( function_exists( 'buddypress' ) && buddypress()->buddyboss ) {
				// Use BuddyBoss specific action.
				add_action( 'bp_member_members_list_item', array( $this, 'bupr_rating_directory' ), 50 );
			} else {
				// Check the theme package ID to distinguish between BuddyPress Legacy and Nouveau.
				$theme_package = bp_get_option( '_bp_theme_package_id' );

				if ( 'nouveau' === $theme_package ) {
					// If BuddyPress Nouveau is active, use the 'bp_directory_members_item_meta' hook.
					add_action( 'bp_directory_members_item_meta', array( $this, 'bupr_rating_directory' ), 50 );
				} else {
					// If BuddyPress Legacy is active, use the 'bp_directory_members_item' hook.
					add_action( 'bp_directory_members_item', array( $this, 'bupr_rating_directory' ), 50 );
				}
			}
			
			add_action( 'init', array( $this, 'bupr_set_default_rating_criteria' ) );
			add_action( 'bupr_after_member_review_list', array( $this, 'bupr_edit_review_form_modal' ) );

			if ( in_array( 'bp-rewrites/class-bp-rewrites.php', get_option( 'active_plugins' ) ) ) {
				add_filter( 'bp_nouveau_get_nav_link', array( $this, 'bupr_nouveau_link_fix' ), 10, 2 );
			}

			add_action( 'bupr_member_review_after_review_insert', array( $this, 'bupr_create_review_activity' ), 10, 2 );
			
			add_action( 'bp_get_activity_content_body', array( $this, 'bupr_added_activity_star_rating' ), 10, 2 );
		}

		/**
		 * @bupr_nouveau_link_fix() nav item links.
		 *
		 * @param string $link     The URL for the nav item.
		 * @param object $nav_item The nav item object.
		 * @return string The URL for the nav item.
		 */
		public function bupr_nouveau_link_fix( $link, $nav_item ) {
			$bp_nouveau = bp_nouveau();
			$nav_item   = $bp_nouveau->current_nav_item;

			$link = '#';
			if ( ! empty( $nav_item->link ) ) {
				$link = $nav_item->link;
			}

			if ( 'personal' === $bp_nouveau->displayed_nav && ! empty( $nav_item->primary ) ) {
				if ( bp_loggedin_user_domain() ) {
					$link = str_replace( bp_loggedin_user_domain(), bp_displayed_user_domain(), $link );
				} else {
					$link = trailingslashit( bp_displayed_user_domain() . $link );
				}
			}
			return $link;
		}

		/**
		 * Get displayed user role.
		 *
		 * @since    2.3.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_get_current_user_roles( $user_id ) {
			if ( is_user_logged_in() ) {
				$user  = get_userdata( $user_id );
				$roles = array();
				if ( is_object( $user ) ) {
					$roles = $user->roles;
				}
				return $roles; // This returns an array.
			}
		}

		/**
		 * To add default criteria review settings.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_set_default_rating_criteria() {
			$bupr_admin_settings = get_option( 'bupr_admin_settings', true );
			if ( empty( $bupr_admin_settings ) || ! is_array( $bupr_admin_settings ) ) {
				$default_admin_criteria = array(
					'profile_multi_rating_allowed' => '1',
					'profile_rating_fields'        => array(
						esc_html__( 'Member Response', 'bp-member-reviews' ) => 'yes',
						esc_html__( 'Member Skills', 'bp-member-reviews' ) => 'yes',
					),
				);
				update_option( 'bupr_admin_settings', $default_admin_criteria );
			}
		}

		/**
		 * Display average rating in the BuddyPress directory.
		 *
		 * @since 1.0.0
		 */
		public function bupr_rating_directory() {
			global $members_template, $bupr;
			// Check if we are on the members directory and if the setting is enabled
			if ( ! bp_is_members_directory() || 'yes' !== $bupr['dir_view_ratings'] ) {
				return;
			}

			$bupr_type       = 'integer';
			$bupr_avg_rating = 0;

			// Gather all the member's ratings
			$bupr_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => $members_template->member->id,
						'compare' => '=',
					),
				),
			);

			$reviews                 = get_posts( $bupr_args );
			$bupr_total_rating       = 0;
			$rate_counter            = 0;
			$bupr_reviews_count      = count( $reviews );
			$bupr_total_review_count = 0;

			if ( 0 !== $bupr_reviews_count ) {
				foreach ( $reviews as $review ) {
					$rate                = 0;
					$reviews_field_count = 0;
					$review_ratings      = get_post_meta( $review->ID, 'profile_star_rating', false );
					if ( ! empty( $review_ratings[0] ) ) {
						if ( ! empty( $bupr['active_rating_fields'] ) ) {
							foreach ( $review_ratings[0] as $field => $value ) {
								$rate += $value;
								$reviews_field_count++;
							}

							if ( 0 !== $reviews_field_count ) {
								$bupr_total_rating += (int) $rate / $reviews_field_count;
								$bupr_total_review_count++;
								$rate_counter++;
							}
						}
					}
				}

				if ( 0 !== $bupr_total_review_count ) {
					$bupr_avg_rating = $bupr_total_rating / $bupr_total_review_count;
					$bupr_type       = gettype( $bupr_avg_rating );
				}

				$bupr_stars_on   = '';
				$stars_off       = '';
				$stars_half      = '';
				$remaining       = $bupr_avg_rating - (int) $bupr_avg_rating;

				if ( $remaining > 0 ) {
					$stars_on        = intval( $bupr_avg_rating );
					$stars_half      = 1;
					$stars_off       = 5 - ( $stars_on + $stars_half );
				} else {
					$stars_on   = $bupr_avg_rating;
					$stars_off  = 5 - $bupr_avg_rating;
					$stars_half = 0;
				}

				$bupr_avg_rating = round( $bupr_avg_rating, 2 );
				
				if ( $bupr_avg_rating > 0 ) {
					?>
					<!-- Add Parent Container to Keep Alignment Center -->
					<div class="bupr-directory-review-parent-container">
						<div class="bupr-directory-review-wrapper">
							<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
								<span itemprop="ratingValue" content="<?php echo esc_attr( $bupr_avg_rating ); ?>"></span>
								<span itemprop="bestRating" content="5"></span>
								<span itemprop="ratingCount" content="<?php echo esc_attr( $rate_counter ); ?>"></span>
								<span itemprop="reviewCount" content="<?php echo esc_attr( $bupr_reviews_count ); ?>"></span>
								<span itemprop="itemReviewed" content="Person"></span>
								<span itemprop="name" content="
								<?php
									if ( function_exists( 'bp_get_version' ) && version_compare( bp_get_version(), '12.0.0', '>=' ) ) {
										echo esc_attr( bp_members_get_user_slug( $members_template->member->id ) );
									} else {
										echo esc_attr( bp_core_get_username( $members_template->member->id ) );
									}
								?>
								"></span>
								<span itemprop="url" content="<?php echo esc_url( bp_core_get_userlink( $members_template->member->id, false, true ) ); ?>"></span>

								<div class="bupr-directory-review-stars bupr-directory-tooltip" data-tooltip="Average rating: <?php echo esc_attr( $bupr_avg_rating ); ?> based on <?php echo esc_attr( $rate_counter ); ?> reviews">
									<?php
									// Display the stars
									for ( $i = 1; $i <= $stars_on; $i++ ) {
										echo '<span class="fas fa-star bupr-star-rate"></span>';
									}
									for ( $i = 1; $i <= $stars_half; $i++ ) {
										echo '<span class="fas fa-star-half-alt bupr-star-rate"></span>';
									}
									for ( $i = 1; $i <= $stars_off; $i++ ) {
										echo '<span class="far fa-star bupr-star-rate"></span>';
									}
									?>
									<span class="bupr-directory-rating-text">
										<?php echo esc_html( $bupr_avg_rating ) . '/5 (' . esc_html( $bupr_reviews_count ) . ' reviews)'; ?>
									</span>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}




		/**
		 * Actions performed to add a review button on member header.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_add_review_button_on_member_header() {
			global $bupr;
			if ( ! empty( $bupr['hide_review_button'] ) && 'yes' === $bupr['hide_review_button'] ) {
				if ( is_user_logged_in() ) {
					if ( bp_displayed_user_id() === bp_loggedin_user_id() ) {
						$this->bupr_members_right_to_review();
					} else {
						$this->bupr_members_right_to_take_review();
					}
				}
			}
		}

		/**
		 * Map members who can give review by member role.
		 */
		public function bupr_members_right_to_review() {
			global $bp, $bupr;
			$review_div = 'form';
			$user_id    = bp_loggedin_user_id();
			$user_role  = ( !empty( $this->bupr_get_current_user_roles( $user_id ) ) ) ? $this->bupr_get_current_user_roles( $user_id )[0] : '';
			// If user role is excluded from giving reviews, exit early
			if ( in_array( $user_role, $bupr['exclude_given_members'], true ) ) {
				return;
			}

			// Check if the displayed user is not the logged-in user
			if ( bp_displayed_user_id() !== bp_loggedin_user_id() ) {

				// If exclude_given_members is not empty, process logic
				if ( ! empty( $bupr['exclude_given_members'] ) ) {

					// Check if the logged-in user is not excluded from giving reviews
					if ( ! in_array( $user_role[0], $bupr['exclude_given_members'], true ) ) {

						// Construct the review URL
						$review_url         = bp_core_get_userlink( $user_id, false, true ) . bupr_profile_review_tab_plural_slug() . '/add-' . bupr_profile_review_tab_singular_slug();
						$bp_template_option = bp_get_option( '_bp_theme_package_id' );

						// BuddyPress Nouveau template
						if ( 'nouveau' === $bp_template_option ) {
							?>
							<li id="bupr-add-review-btn" class="generic-button">
								<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review"> <!-- Standard button class -->
									<?php
									/* translators: %s: Review label; */
									echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
								</a>
							</li>
							<?php
						} else { // BuddyPress legacy or other template
							?>
							<div id="bupr-add-review-btn" class="generic-button">
								<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review"> <!-- Standard button class -->
									<?php
									/* translators: %s: review label. */
									echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
								</a>
							</div>
							<?php
						}
					}

				} else { // If exclude_given_members is empty, allow review submission for all members

					// Construct the review URL
					$review_url         = bp_core_get_userlink( $user_id, false, true ) . bupr_profile_review_tab_plural_slug() . '/add-' . bupr_profile_review_tab_singular_slug();
					$bp_template_option = bp_get_option( '_bp_theme_package_id' );

					// BuddyPress Nouveau template
					if ( 'nouveau' === $bp_template_option ) {
						?>
						<li id="bupr-add-review-btn" class="generic-button">
							<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review"> <!-- Standard button class -->
								<?php
								/* translators: %s: review label. */
								echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
							</a>
						</li>
						<?php
					} else { // BuddyPress legacy or other template
						?>
						<div id="bupr-add-review-btn" class="generic-button">
							<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review"> <!-- Standard button class -->
								<?php
								/* translators: %s: review label. */
								echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
							</a>
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Members who can only take reviews
		 */
		public function bupr_members_right_to_take_review() {
			global $bp, $bupr;
			$review_div = 'form';
			$user_id    = bp_loggedin_user_id();
			$user_role  = ( !empty( $this->bupr_get_current_user_roles( $user_id ) ) ) ? $this->bupr_get_current_user_roles( $user_id )[0] : '';
			// Exit early if the user's role is excluded from taking reviews
			if ( ! in_array( $user_role, $bupr['exclude_given_members'], true ) ) {
				return;
			}

			// Check if the displayed user is not the logged-in user
			if ( bp_displayed_user_id() !== bp_loggedin_user_id() ) {

				// If add_taken_members is set, process the review logic
				if ( ! empty( $bupr['add_taken_members'] ) ) {
					$user_id   = bp_displayed_user_id();
					$user_role = $this->bupr_get_current_user_roles( $user_id );
					// Check if the displayed user has the role to take reviews
					if ( in_array( $user_role[0], $bupr['add_taken_members'], true ) ) {

						// Construct the review URL
						$review_url         = bp_core_get_userlink( $user_id, false, true ) . bupr_profile_review_tab_plural_slug() . '/add-' . bupr_profile_review_tab_singular_slug();
						$bp_template_option = bp_get_option( '_bp_theme_package_id' );

						// BuddyPress Nouveau template
						if ( 'nouveau' === $bp_template_option ) {
							?>
							<li id="bupr-add-review-btn" class="generic-button">
								<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review" show="<?php echo esc_attr( $review_div ); ?>">
									<?php
									/* translators: %s: review label. */
									echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
								</a>
							</li>
							<?php
						} else { // BuddyPress legacy or other template
							?>
							<div id="bupr-add-review-btn" class="generic-button">
								<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review" show="<?php echo esc_attr( $review_div ); ?>">
									<?php
									/* translators: %s: review label. */
									echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
								</a>
							</div>
							<?php
						}
					}
				} else { // If add_taken_members is empty, allow reviews for all

					// Construct the review URL
					$review_url         = bp_core_get_userlink( $user_id, false, true ) . bupr_profile_review_tab_plural_slug() . '/add-' . bupr_profile_review_tab_singular_slug();
					$bp_template_option = bp_get_option( '_bp_theme_package_id' );

					// BuddyPress Nouveau template
					if ( 'nouveau' === $bp_template_option ) {
						?>
						<li id="bupr-add-review-btn" class="generic-button">
							<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review" show="<?php echo esc_attr( $review_div ); ?>">
								<?php
								/* translators: %s: review label. */
								echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
							</a>
						</li>
						<?php
					} else { // BuddyPress legacy or other template
						?>
						<div id="bupr-add-review-btn" class="generic-button">
							<a href="<?php echo esc_url( $review_url ); ?>" class="button add-review" show="<?php echo esc_attr( $review_div ); ?>">
								<?php
								/* translators: %s: review label. */
								echo sprintf( esc_html__( 'Add %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ); ?>
							</a>
						</div>
						<?php
					}
				}
			}
		}

		/**
		 * Setup Reviews link in admin bar.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @param    array $wp_admin_nav Member Review add menu array.
		 * @author   Wbcom Designs
		 */
		public function bupr_setup_admin_bar( $wp_admin_nav = array() ) {
			global $wp_admin_bar;
			global $bupr;
			$bupr_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => get_current_user_id(),
						'compare' => '=',
					),
				),
			);

			$reviews       = get_posts( $bupr_args );
			$reviews_count = count( $reviews );

			$profile_menu_slug = isset( $bupr['review_label_plural'] ) ? sanitize_title( $bupr['review_label_plural'] ) : esc_html( 'reviews' );

			$base_url = bp_loggedin_user_domain() . $profile_menu_slug;
			if ( is_user_logged_in() ) {
				$wp_admin_bar->add_menu(
					array(
						'parent' => 'my-account-buddypress',
						'id'     => 'my-account-' . $profile_menu_slug,
						'title'  => $bupr['review_label_plural'] . ' <span class="count">' . $reviews_count . '</span>',
						'href'   => trailingslashit( $base_url ),
					)
				);
			}
		}

		/**
		 * Display average rating on a BuddyPress member's profile.
		 *
		 * @since 1.0.0
		 */
		public function bupr_member_average_rating() {
			
			global $bupr;

			$bupr_type       = 'integer';
			$bupr_avg_rating = 0;

			// Gather all the member's reviews
			$bupr_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => bp_displayed_user_id(),
						'compare' => '=',
					),
				),
			);

			$reviews                 = get_posts( $bupr_args );
			$bupr_total_rating       = 0;
			$rate_counter            = 0;
			$bupr_reviews_count      = count( $reviews );
			$bupr_total_review_count = 0;
			if ( 0 !== $bupr_reviews_count ) {
				foreach ( $reviews as $review ) {
					$rate                = 0;
					$reviews_field_count = 0;
					$review_ratings      = get_post_meta( $review->ID, 'profile_star_rating', false );
					
					if ( ! empty( $review_ratings[0] ) ) {
						if ( ! empty( $bupr['active_rating_fields'] ) ) {
							
							foreach ( $review_ratings[0] as $field => $value ) {
								$rate += $value;
								$reviews_field_count++;
								
							}

							if ( 0 !== $reviews_field_count ) {
								$bupr_total_rating += (int) $rate / $reviews_field_count;
								$bupr_total_review_count++;
								$rate_counter++;
							}
						}
					}
				}
				
				if ( 0 !== $bupr_total_review_count ) {
					$bupr_avg_rating = $bupr_total_rating / $bupr_total_review_count;
					$bupr_type       = gettype( $bupr_avg_rating );
				}

				$bupr_stars_on   = '';
				$stars_off       = '';
				$stars_half      = '';
				$remaining       = $bupr_avg_rating - (int) $bupr_avg_rating;

				if ( $remaining > 0 ) {
					$stars_on        = intval( $bupr_avg_rating );
					$stars_half      = 1;
					$stars_off       = 5 - ( $stars_on + $stars_half );
				} else {
					$stars_on   = $bupr_avg_rating;
					$stars_off  = 5 - $bupr_avg_rating;
					$stars_half = 0;
				}
				$bupr_avg_rating = round( $bupr_avg_rating, 2 );
				if ( $bupr_avg_rating > 0 ) {
					?>
					<!-- Left-aligned container for member profile reviews -->
					<div class="bupr-member-review-wrapper">
						<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
							<span itemprop="ratingValue" content="<?php echo esc_attr( $bupr_avg_rating ); ?>"></span>
							<span itemprop="bestRating" content="5"></span>
							<span itemprop="ratingCount" content="<?php echo esc_attr( $rate_counter ); ?>"></span>
							<span itemprop="reviewCount" content="<?php echo esc_attr( $bupr_reviews_count ); ?>"></span>
							<span itemprop="itemReviewed" content="Person"></span>
							<span itemprop="name" content="
							<?php
								if ( function_exists( 'bp_get_version' ) && version_compare( bp_get_version(), '12.0.0', '>=' ) ) {
									echo esc_attr( bp_members_get_user_slug( bp_displayed_user_id() ) );
								} else {
									echo esc_attr( bp_core_get_username( bp_displayed_user_id() ) );
								}
							?>
							"></span>
							<span itemprop="url" content="<?php echo esc_url( bp_core_get_userlink( bp_displayed_user_id(), false, true ) ); ?>"></span>

							<div class="bupr-member-review-stars bupr-member-tooltip" data-tooltip="Average rating: <?php echo esc_attr( $bupr_avg_rating ); ?> based on <?php echo esc_attr( $rate_counter ); ?> reviews">
								<?php
								// Display the stars
								for ( $i = 1; $i <= $stars_on; $i++ ) {
									echo '<span class="fas fa-star bupr-star-rate"></span>';
								}
								for ( $i = 1; $i <= $stars_half; $i++ ) {
									echo '<span class="fas fa-star-half-alt bupr-star-rate"></span>';
								}
								for ( $i = 1; $i <= $stars_off; $i++ ) {
									echo '<span class="far fa-star bupr-star-rate"></span>';
								}
								?>
								<span class="bupr-member-rating-text">
									<?php echo esc_html( $bupr_avg_rating ) . '/5 (' . esc_html( $bupr_reviews_count ) . ' reviews)'; ?>
								</span>
							</div>
						</div>
					</div>
					<?php
				}
			}
		}



				/**
				 * Actions performed to remove edit from bulk options
				 *
				 * @since    1.0.0
				 * @access   public
				 * @param    array $actions Actions array.
				 * @author   Wbcom Designs
				 */
		public function bupr_remove_edit_bulk_actions( $actions ) {
			unset( $actions['edit'] );
			return $actions;
		}

				/**
				 * Actions performed to hide row actions
				 *
				 * @since    1.0.0
				 * @access   public
				 * @param    array $actions Actions array.
				 * @param    array $post    Posts array.
				 * @author   Wbcom Designs
				 */
		public function bupr_bp_member_reviews_row_actions( $actions, $post ) {
			global $bp;
			global $bupr;
			if ( 'review' === $post->post_type ) {
				unset( $actions['edit'] );
				unset( $actions['view'] );
				unset( $actions['inline hide-if-no-js'] );
				$review_term = isset( wp_get_object_terms( $post->ID, 'review_category' )[0]->name ) ? wp_get_object_terms( $post->ID, 'review_category' )[0]->name : '';
				if ( 'BP Member' === $review_term ) {
					// Add a link to view the review.
					$review_title     = $post->post_title;
					$linked_bp_member = get_post_meta( $post->ID, 'linked_bp_member', true );

					$review_url = bp_core_get_userlink( $linked_bp_member, false, true ) . strtolower( $bupr['review_label_plural'] ) . '/view/' . $post->ID;
					/* translators: %s: */
					$actions['view_review'] = '<a href="' . $review_url . '" title="' . $review_title . '">' . sprintf( esc_html__( 'View %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ) . '</a>';

					// Add Approve Link for draft reviews.
					if ( 'draft' === $post->post_status ) {
						$actions['approve_review'] = '<a href="javascript:void(0);" title="' . $review_title . '" class="bupr-approve-review" data-rid="' . $post->ID . '">' . esc_html__( 'Approve', 'bp-member-reviews' ) . '</a>';
					}
				}
			}
			return $actions;
		}

				/**
				 * Action performed to add taxonomy term for group reviews
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_add_bp_member_reviews_taxonomy_term() {
			$termexists = term_exists( 'BP Member', 'review_category' );
			if ( 0 === $termexists || null === $termexists ) {
				wp_insert_term( 'BP Member', 'review_category' );
			}
		}

				/**
				 * Action performed to add a tab for member profile reviews
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_member_profile_reviews_tab() {
			global $bp;
			global $bupr;
			$bp_pages = bp_core_get_directory_pages();
			add_filter( 'site_url', 'bupr_site_url', 99 );
			$member_slug = ( isset( $bp_pages->members ) && isset( $bp_pages->members->slug ) ) ? $bp_pages->members->slug : 'members';

			/* count member's review */
			$bupr_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => bp_displayed_user_id(),
						'compare' => '=',
					),
				),
			);

			$bupr_reviews = new WP_Query( $bupr_args );
			if ( ! empty( $bupr_reviews->posts ) ) {
				$bupr_reviews = count( $bupr_reviews->posts );
				if ( ! empty( $bupr_reviews ) ) {
					$bupr_notification = '<span class="count">' . $bupr_reviews . '</span>';
				} else {
					$bupr_notification = '<span class="count">' . 0 . '</span>';
				}
			} else {
				$bupr_notification = '<span class="count">' . 0 . '</span>';
			}

			$name = bp_get_displayed_user_username();

			$tab_args = array(
				'name'                    => bupr_profile_review_tab_name() . 's' . $bupr_notification,
				'slug'                    => bupr_profile_review_tab_plural_slug(),
				'screen_function'         => array( $this, 'bupr_reviews_tab_function_to_show_screen' ),
				'position'                => 75,
				'default_subnav_slug'     => 'view',
				'show_for_displayed_user' => true,
			);
			bp_core_new_nav_item( $tab_args );

			$parent_slug = bupr_profile_review_tab_plural_slug();

			// Add subnav to view a review.
			bp_core_new_subnav_item(
				array(
					'name'            => bupr_profile_review_tab_name(),
					'slug'            => 'view',
					'parent_url'      => $bp->loggedin_user->domain . $parent_slug . '/',
					'parent_slug'     => $parent_slug,
					'screen_function' => array( $this, 'bupr_view_review_tab_function_to_show_screen' ),
					'position'        => 100,
					'link'            => site_url() . "/$member_slug/$name/$parent_slug/",
				)
			);

			// Add subnav to add a review.
			if ( bp_displayed_user_id() === bp_loggedin_user_id() ) {
				if ( ! empty( $bupr['exclude_given_members'] ) ) {
					$user_role = $this->bupr_get_current_user_roles( bp_loggedin_user_id() );
					if ( ! empty( $user_role ) && in_array( $user_role[0], $bupr['exclude_given_members'], true ) && ! bp_loggedin_user_id() ) {
						bp_core_new_subnav_item(
							array(
								/* translators: Review Label */
								'name'            => sprintf( esc_html__( 'Add %1$s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ),
								'slug'            => 'add-' . bupr_profile_review_tab_singular_slug(),
								'parent_url'      => $bp->loggedin_user->domain . $parent_slug . '/',
								'parent_slug'     => $parent_slug,
								'screen_function' => array( $this, 'bupr_reviews_form_tab_function_to_show_screen' ),
								'position'        => 200,
								'link'            => site_url() . "/$member_slug/$name/$parent_slug/" . 'add-' . bupr_profile_review_tab_singular_slug(),
							)
						);
					}
				}
			} else {
				$user_role = $this->bupr_get_current_user_roles( bp_loggedin_user_id() );
				if ( ! empty( $user_role ) && ! array_intersect( $user_role, $bupr['exclude_given_members'] ) ) {
					return ; 
				}

				if ( ! empty( $bupr['add_taken_members'] ) && ! empty( $user_role ) ) {
					$user_role = $this->bupr_get_current_user_roles( bp_displayed_user_id() );
					$user_role = ! empty( $user_role ) ? $user_role : array();

					if ( array_intersect( $user_role, $bupr['add_taken_members']) ) {
						bp_core_new_subnav_item(
							array(
								/* translators: %s: */
								'name'            => sprintf( esc_html__( 'Add %1$s', 'bp-member-reviews' ), esc_html( bupr_profile_review_singular_tab_name() ) ),
								'slug'            => 'add-' . bupr_profile_review_tab_singular_slug(),
								'parent_url'      => $bp->loggedin_user->domain . $parent_slug . '/',
								'parent_slug'     => $parent_slug,
								'screen_function' => array( $this, 'bupr_reviews_form_tab_function_to_show_screen' ),
								'position'        => 200,
								'link'            => site_url() . "/$member_slug/$name/$parent_slug/" . 'add-' . bupr_profile_review_tab_singular_slug(),
							)
						);
					}
				} else {

					bp_core_new_subnav_item(
						array(
							/* translators: %s: */
							'name'            => sprintf( esc_html__( 'Add %1$s', 'bp-member-reviews' ), esc_html( bupr_profile_review_singular_tab_name() ) ),
							'slug'            => 'add-' . bupr_profile_review_tab_singular_slug(),
							'parent_url'      => $bp->loggedin_user->domain . $parent_slug . '/',
							'parent_slug'     => $parent_slug,
							'screen_function' => array( $this, 'bupr_reviews_form_tab_function_to_show_screen' ),
							'position'        => 200,
							'link'            => site_url() . "/$member_slug/$name/$parent_slug/" . 'add-' . bupr_profile_review_tab_singular_slug(),
						)
					);
				}
			}
			remove_filter( 'site_url', 'bupr_site_url', 99 );
		}

				/**
				 * Action performed to show screen of reviews listing tab.
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_reviews_tab_function_to_show_screen() {
			add_action( 'bp_template_content', array( $this, 'bupr_reviews_tab_function_to_show_content' ) );
			bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
		}

				/**
				 * Action performed to show screen of reviews form tab.
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_reviews_form_tab_function_to_show_screen() {
			add_action( 'bp_template_content', array( $this, 'bupr_reviews_form_to_show_content' ) );
			bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
		}

				/**
				 * Actions performed to show the content of reviews list tab
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_reviews_tab_function_to_show_content() {
			bupr_get_template( 'bupr-reviews-tab-template.php' );
		}

		/**
		 * Action performed to show the content of add review tab
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_reviews_form_to_show_content() {
			global $bupr;
			?>
			<div class="bupr-bp-member-review-no-popup-add-block">
			<?php
			if ( is_user_logged_in() ) {
				do_action( 'bupr_member_review_form' );
			} else {
				$bp_template_option = bp_get_option( '_bp_theme_package_id' );
				if ( 'nouveau' === $bp_template_option ) {
					?>
						<div id="message" class="info bp-feedback bp-messages bp-template-notice">
							<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>
							<div id="message" class="info">
							<?php } ?>
							<p><?php 
							printf(
						/* translators: %1$s: Review user link; %2$s: review label; %3$s: reviewed user link. */
						'You must <a href="%1$s">login</a> to add a %2$s.'
						,esc_url( wp_login_url( get_permalink() ) ),
						esc_html( strtolower( $bupr['review_label'] ) ),
						); ?></p></div>
						<?php 
					} ?>
					</div>
			<?php
		}

				/**
				 * Action performed to show screen of single review view tab.
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_view_review_tab_function_to_show_screen() {
			add_action( 'bp_template_content', array( $this, 'bupr_view_review_tab_function_to_show_content' ) );
			bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
		}

				/**
				 * Action performed to show the content of reviews list tab.
				 *
				 * @since    1.0.0
				 * @access   public
				 * @author   Wbcom Designs
				 */
		public function bupr_view_review_tab_function_to_show_content() {
			bupr_get_template( 'bupr-single-review-template.php' );
		}

		public function bupr_edit_review_form_modal() {
			if ( is_user_logged_in() ) {
				bupr_get_template( 'bupr-edit-review-form.php' );
			}
		}

		/**
		 * Create activity on new review creation.
		 *
		 * @since 2.8.1
		 * @access public
		 * @author Wbcom Designs
		 *
		 * @param int $review_id        The ID of the review post.
		 * @param int $reviewed_user_id The ID of the user being reviewed.
		 */
		public function bupr_create_review_activity( $review_id, $reviewed_user_id ) {
			global $bupr, $bp;

			if(isset($bupr['review_activity']) && 'yes' !== $bupr['review_activity'] ){
				return;
			}
			
			// Apply filter to allow disabling activity creation.
			$allow_activity = apply_filters( 'bupr_allow_activity_posting', true, $review_id, $reviewed_user_id );

			// If the filter returns false, do not proceed with activity creation.
			if ( false === $allow_activity ) {
				return;
			}

			// Check if the review activity should be created (based on settings).
				// Get the review post data.
				$review = get_post( $review_id );
				if ( ! empty( $review ) ) {
					// Get the reviewer's (author's) and reviewed user's profile URLs.
					$reviewer_user_id   = $review->post_author;
					$reviewer_user_link = bp_core_get_userlink( $reviewer_user_id );
					$reviewed_user_link = bp_core_get_userlink( $reviewed_user_id );

					// Get the review content.
					$review_content = $review->post_content;

					// Construct the action text (reviewer to reviewed user).
					$action = sprintf(
						apply_filters( 'bupr_member_review_activity_action',
						/* translators: %1$s: Review user link; %2$s: review label; %3$s: reviewed user link. */
						__( '%1$s posted a new %2$s to %3$s', 'bp-member-reviews' )
						),
						$reviewer_user_link,
						strtolower( esc_html( $bupr['review_label'] ) ),
						$reviewed_user_link
					);

					// Ensure the content is safe.
					$escaped_content = wp_kses_post( $review_content );

					if(function_exists('bp_activity_add')){
						// Add activity to BuddyPress.
						bp_activity_add( array(
							'action'        => $action,
							'content'       => $escaped_content, // No links or ratings, just review content.
							'component'     => $bp->members->id,
							'type'          => defined( 'YOUZIFY_VERSION' ) ? 'activity_status' : 'member_review',
							'user_id'       => $reviewer_user_id,
							'item_id'       => $review_id,
							'secondary_item_id' => $reviewed_user_id,
							'hide_sitewide' => false,
							'is_spam'       => false,
							'privacy'       => 'public',
						) );
					}else{
						return;
					}
					
				}
			}

	/**
	 * Added member star rating in activity.
	 *
	 * @param  string $activity_content Activity Content.
	 * @param  object $activity Activity Object.
	 */
	public function bupr_added_activity_star_rating( $activity_content, $activity ) {
		$post_id              = $activity->item_id;
		$review_rating_fields = get_option( 'bupr_admin_settings', true );
		$review_ratings       = get_post_meta( $post_id, 'profile_star_rating', false );		
		$review_start         = '';		
		if ( ! empty( $review_rating_fields['profile_rating_fields'] ) && ! empty( $review_ratings[0] ) ) {			
			$review_start .= '<div class="bupr-multi-review">';
			foreach ( $review_rating_fields['profile_rating_fields'] as $review_field => $review_field_val ) {				
				if ( array_key_exists( $review_field, $review_ratings[0] ) ) {
					$review_start .= '<div class="multi-review">';
					$review_start .= '<div class="bupr-col-6">' . esc_html( $review_field ) . '</div>';
					$review_start .= '<div class="bupr-col-6">';
					/*** Ratings */
					$stars_on  = $review_ratings[0][ $review_field ];
					$stars_off = 5 - $stars_on;
					for ( $i = 1; $i <= $stars_on; $i++ ) {
						$review_start .= '<span class="fas fa-star stars bupr-star-rate"></span>';

					}
					for ( $i = 1; $i <= $stars_off; $i++ ) {
						$review_start .= '<span class="far fa-star stars bupr-star-rate"></span>';
					}
					$review_start .= '</div>';
					$review_start .= '</div>';
				}
			}
			$review_start .= '</div>';
		}
		return $activity_content . $review_start;
	}

		/**
		* Saved the user name as anonymous if meta in review.
		 *
		* @since    3.2.2
		* @access   public
		* @author   Wbcom Designs
		 */
		public function bupr_add_activity_meta($activity){
			$is_anonymous = get_post_meta($activity->item_id, 'bupr_anonymous_review_post',true);
			$anonymous_user = ( $is_anonymous == 'yes' ) ? esc_html__( 'An anonymous user', 'bp-member-reviews' ) : '';
			if ( isset( $activity->id ) && !empty( $anonymous_user ) ) {
				bp_activity_add_meta( $activity->id, 'bupr_user_string', $anonymous_user );
			}
		}

		/**
		* Hide the user name if anonymous text available.
		 *
		* @since    3.2.2
		* @access   public
		* @author   Wbcom Designs
		 */
		public function bupr_hide_username_in_activity( $action, $activity ) {
			$bupr_user_string = bp_activity_get_meta( $activity->id, 'bupr_user_string' );
			$user_id = $activity->user_id; 
			if( ! empty( $bupr_user_string ) ){
				$action = str_replace( bp_core_get_userlink( $user_id ), $bupr_user_string , $action );
			}
			return apply_filters( 'bupr_hide_username_in_activity', $action );
		}

		/**
		* Change the user url if the activity is of anonymous user.
		 *
		* @since    3.2.2
		* @access   public
		* @author   Wbcom Designs
		 */
		public function bupr_change_user_link( $user_link_activity ) {
			global $activities_template;
			$activity = $activities_template->activity;
			$is_anonymous = get_post_meta($activity->item_id, 'bupr_anonymous_review_post',true);
			if( 'yes' === $is_anonymous ){
			$user_link_activity = '#';
			}
			return apply_filters( 'bupr_change_user_link', $user_link_activity );
		}

		/**
		* Change the user gravatar if the activity is of anonymous user.
		 *
		* @since    3.2.2
		* @access   public
		* @author   Wbcom Designs
		 */
		public function bupr_change_avatar_image( $gravatar_image ) {
			global $activities_template;
			$activity = $activities_template->activity;
			$is_anonymous = get_post_meta($activity->item_id, 'bupr_anonymous_review_post',true);
			if( 'yes' === $is_anonymous ){
				$size = apply_filters( 'bupr_default_avatar_size', 150 );
				$default_avatar = apply_filters( 'bupr_change_default_avatar', 'mystery' );  // Could also use identicon, monsterid, retro, etc.
				$gravatar_url = 'https://www.gravatar.com/avatar/?s=' . $size . '&d=' . $default_avatar;
			
			$gravatar_image =  '<img src="' . esc_url( $gravatar_url ) . '" class="avatar photo" alt="Custom Default Gravatar" />';
			}
			return apply_filters( 'bupr_change_avatar_image', $gravatar_image );
		}
	}
			new BUPR_Custom_Hooks();
}
