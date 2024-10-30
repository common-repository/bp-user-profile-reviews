<?php
/**
 * Class to add reviews shortcode.
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 * @package  BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BUPR_Shortcodes' ) ) {

	/**
	 * Class to serve AJAX Calls.
	 *
	 * @author   Wbcom Designs
	 * @since    1.0.0
	 */
	class BUPR_Shortcodes {

		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function __construct() {
			add_shortcode( 'bupr_display_top_members', array( $this, 'bupr_display_top_members_display' ) );
			add_action( 'bupr_member_review_form', array( $this, 'bupr_display_review_form' ) );
		}

		/**
		 * Display top members on front-end using optimized SQL queries for ratings and reviews.
		 * Added filters for future extensibility.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_display_top_members_display( $attrs ) {

			global $wpdb;

			// Extract shortcode attributes
			$atts = shortcode_atts(
				apply_filters( 'bupr_display_top_members_shortcode_atts', array(
					'title'        => '',
					'total_member' => 5,
					'type'         => 'top rated',
					'avatar'       => 'show',
				), $attrs ),
				$attrs
			);

			$bupr_title     = apply_filters( 'bupr_shortcode_title', $atts['title'] );
			$member_limit   = apply_filters( 'bupr_shortcode_member_limit', $atts['total_member'] );
			$top_member     = apply_filters( 'bupr_shortcode_type', $atts['type'] );
			$avatar_display = apply_filters( 'bupr_shortcode_avatar', $atts['avatar'] );

			$output = '';

			// Determine the meta key based on the sorting type (top rated or most reviewed)
			$meta_key = ( 'top rated' === $top_member ) ? 'bupr_aggregate_rating' : 'bupr_review_count';
			$meta_key = apply_filters( 'bupr_shortcode_meta_key', $meta_key, $top_member );

			// Query to fetch users based on their aggregate rating or review count
			
			$bupr_user_data = $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id, meta_value 
				FROM {$wpdb->usermeta} 
				WHERE meta_key = %s
				ORDER BY CAST(meta_value AS DECIMAL(10,2)) DESC 
				LIMIT %d", 
				$meta_key, 
				$member_limit
			) );
			$top_members = apply_filters( 'bupr_shortcode_query_results', $bupr_user_data, $meta_key );

			$output .= '<div class="bupr-shortcode-top-members-contents bupr_members_review_setting">';
			$output .= '<h2>' . esc_html( $bupr_title ) . '</h2>';
			$output .= '<ul class="bupr-member-main">';

			// Display each top member
			if ( ! empty( $top_members ) ) {
				foreach ( $top_members as $member ) {
					$user_id = apply_filters( 'bupr_shortcode_member_id', $member->user_id );

					// Get user's aggregate rating and review count from meta
					$aggregate_rating = get_user_meta( $user_id, 'bupr_aggregate_rating', true );
					$review_count     = get_user_meta( $user_id, 'bupr_review_count', true );

					$aggregate_rating = apply_filters( 'bupr_shortcode_aggregate_rating', ( ! empty( $aggregate_rating ) ? round( $aggregate_rating, 2 ) : 0 ), $user_id );
					$review_count     = apply_filters( 'bupr_shortcode_review_count', ( ! empty( $review_count ) ? $review_count : 0 ), $user_id );

					// Display avatar and user link
					$output .= '<li class="bupr-members">';
					if ( 'show' === $avatar_display ) {
						$output .= apply_filters( 'bupr_shortcode_avatar_display', '<div class="bupr-img-widget">' . get_avatar( $user_id, 50 ) . '</div>', $user_id );
					}
					$output .= '<div class="bupr-content-widget">';
					// Get user profile link based on available BuddyPress function
					if ( function_exists( 'bp_members_get_user_slug' ) ) {
						// Use the BuddyPress v12.0.0+ function if it exists
						$members_profile = bp_members_get_user_slug( $user_id );
					} else {
						// Fallback to the older BuddyPress function
						$members_profile = bp_core_get_userlink( $user_id );
					}
					$output         .= '<div class="bupr-member-title">' . wp_kses_post( $members_profile ) . '</div>';

					// Display star rating (5-star format) and review count
					$stars_on   = floor( $aggregate_rating );
					$stars_half = ( $aggregate_rating - $stars_on >= 0.5 ) ? 1 : 0;
					$stars_off  = 5 - ( $stars_on + $stars_half );

					$output .= '<div class="bupr-member-rating">';
					for ( $i = 1; $i <= $stars_on; $i++ ) {
						$output .= apply_filters( 'bupr_shortcode_star_full', '<span class="fas fa-star bupr-star-rate"></span>', $user_id, $i );
					}
					if ( $stars_half ) {
						$output .= apply_filters( 'bupr_shortcode_star_half', '<span class="fas fa-star-half-alt bupr-star-rate"></span>', $user_id );
					}
					for ( $i = 1; $i <= $stars_off; $i++ ) {
						$output .= apply_filters( 'bupr_shortcode_star_empty', '<span class="far fa-star bupr-star-rate"></span>', $user_id );
					}
					$output .= '</div>';

					$aggregate_rating = round( $aggregate_rating, 2 );
					$output         .= '<span class="bupr-meta">';
					/* translators: %1$s: rating; %2$s: review count */
					$output         .= apply_filters( 'bupr_shortcode_rating_text', sprintf( esc_html__( 'Rating: %1$s/5 (%2$s reviews)', 'bp-member-reviews' ), esc_html( $aggregate_rating ), esc_html( $review_count ) ), $user_id, $aggregate_rating, $review_count );
					$output         .= '</span>';

					$output .= '</div></li>';
				}
			} else {
				$output .= apply_filters( 'bupr_shortcode_no_members_text', '<p>' . esc_html__( 'No members have been reviewed yet.', 'bp-member-reviews' ) . '</p>' );
			}

			$output .= '</ul>';
			$output .= '</div>';

			return apply_filters( 'bupr_shortcode_output', $output );
		}


		/**
		 * Sort member list according to max review.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_get_sort_max_review( $bupr_rating1, $bupr_rating2 ) {
			return strcmp( $bupr_rating2['max_review'], $bupr_rating1['max_review'] );
		}

		/**
		 * Sort member list according to max star.
		 *
		 * @since    1.0.9
		 * @author   Wbcom Designs
		 */
		public function bupr_get_sort_max_stars( $bupr_rating1, $bupr_rating2 ) {
			return strcmp( $bupr_rating2['avg_rating'], $bupr_rating1['avg_rating'] );
		}
		/**
		 * Display add review form on front-end.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function bupr_display_review_form() {
			global $bp;
			global $bupr;

			$login_user           = get_current_user_id();
			$bupr_spinner_src     = includes_url() . 'images/spinner.gif';
			$auto_approve_reviews = $bupr['auto_approve_reviews'];
			$review_label         = $bupr['review_label'];

			$bupr_review_succes = false;
			$bupr_flag          = false;
			$bupr_member = array();

			// Setting default query arguments
			$args = array(
				'exclude' => array( get_current_user_id() ), // Exclude current user directly
				'fields'  => array( 'ID', 'display_name' ),  // Only get ID and display name
				'number'  => 100, // Optional: Limit the number of users retrieved at a time
			);
			
			// Apply a filter to the query arguments
			$args = apply_filters( 'bupr_member_query_args', $args );
			
			$user_query = new WP_User_Query( $args );
			
			if ( ! empty( $user_query->get_results() ) ) {
				foreach ( $user_query->get_results() as $user ) {
					$user_info = get_userdata( $user->ID );
					$bupr_member[] = array(
						'member_id'    => $user->ID,
						'member_name'  => $user->display_name,
						'member_roles' => $user_info->roles, // Fetch roles separately
					);
				}
			}
			
			// Apply a filter to allow modification of the member array
			$bupr_member = apply_filters( 'bupr_member_list', $bupr_member );

			$member_args = array(
				'post_type'      => 'review',
				'posts_per_page' => -1,
				'post_status'    => array(
					'draft',
					'publish',
				),
				'author'         => $login_user,
				'category'       => 'bp-member',
				'meta_query'     => array(
					array(
						'key'     => 'linked_bp_member',
						'value'   => bp_displayed_user_id(),
						'compare' => '=',
					),
				),
			);

			$reviews_args = new WP_Query( $member_args );
			if ( ! bp_is_members_component() && ! bp_is_user() ) {
				$bp_template_option = bp_get_option( '_bp_theme_package_id' );
			}

			if ( 0 === bp_displayed_user_id() ) {
				$this->bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member );
			} else {
				if ( 'no' === $bupr['multi_reviews'] ) {
					$user_post_count = $reviews_args->post_count;
				} else {
					$user_post_count = 0;
				}
				if ( 0 === $user_post_count ) {
					$this->bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member );
				} else {
					$bp_template_option = bp_get_option( '_bp_theme_package_id' );
					if ( 'nouveau' === $bp_template_option ) {
						?>
							<div id="message" class="success bp-feedback bp-messages bp-template-notice">
								<span class="bp-icon" aria-hidden="true"></span>
					<?php } else { ?>
								<div id="message" class="info">
										<?php } ?>
								<?php
								if ( 'publish' === $reviews_args->posts[0]->post_status ) {
									/* translators: %s: */
									$message = sprintf( __( 'You already posted a %1$s for this member.', 'bp-member-reviews' ), $review_label );
								} else {
									if ( 'yes' === $auto_approve_reviews ) {
										/* translators: %s: */
										$message = sprintf( esc_html__( 'Thank you for taking time to write this wonderful %1$s.', 'bp-member-reviews' ), $review_label );
									} else {
										/* translators: %s: */
										$message = sprintf( esc_html__( 'Thank you for taking time to write this wonderful %1$s. Your %1$s will display after moderator\'s approval.', 'bp-member-reviews' ), $review_label );
									}
								}
								?>
										<p><?php echo esc_html( $message ); ?> </p>
								</div> <!-- Close div for success/info message -->
							<?php
				}
			}
		}

		/**
		 * Check criteria
		 *
		 * @return bool
		 */
		public function bupr_criteria_is_enable() {
			global $bupr;
			$show_rating = false;
			foreach ( $bupr['active_rating_fields'] as $bupr_rating_fields ) {
				if ( $bupr['multi_criteria_allowed'] ) {
					$show_rating = true;
					return $show_rating;
				}
			}
			return $show_rating;
		}

		/**
		 * Bupr review form.
		 *
		 * @since    1.0.0
		 * @param    string $login_user             Login  User.
		 * @param    string $bupr_spinner_src       Spinner  User.
		 * @param    string $bupr_review_succes     Review Success.
		 * @param    int    $bupr_flag              Flag.
		 * @param    array  $bupr_member            Member array.
		 * @author   Wbcom Designs
		 */
		public function bupr_review_form( $login_user, $bupr_spinner_src, $bupr_review_succes, $bupr_flag, $bupr_member ) {
			global $bupr;
			$flag = false;
			if ( 'yes' === $bupr['anonymous_reviews'] ) {
				$flag = true;
			}
			$bp_template_option = bp_get_option( '_bp_theme_package_id' );
			if ( 'nouveau' === $bp_template_option ) {
				?>
				<div id="message" class="success add_review_msg success_review_msg bp-feedback bp-messages bp-template-notice">
					<span class="bp-icon" aria-hidden="true"></span>
				<?php } else { ?>
					<div id="message" class="success add_review_msg success_review_msg">
						<?php } ?>
						<p></p>
					</div>
					<?php
					if ( is_user_logged_in() ) {
						$user  = wp_get_current_user();
						$roles = ( isset( $user->roles ) && !empty( $user->roles ) ) ? $user->roles : array();
						if ( ! array_intersect( $roles, $bupr['exclude_given_members'] ) ) {
						?>
						<div class="bupr_not_give_review">
								<p><?php echo esc_html( 'Sorry, You have not been given permission to review the user !!!' ); ?></p>
						</div>
							<?php
						} else {
							?>
					<form action="" method="POST" id="bupr_review_form_public">
						<input type="hidden" value="<?php echo esc_attr( $bupr['rating_color'] ); ?>" class="bupr-display-rating-color">
						<input type="hidden" id="reviews_pluginurl" value="<?php echo esc_url( BUPR_PLUGIN_URL ); ?>">
						<div class="bp-member-add-form">
							<p>
							<?php
							/* translators: %s: */
							echo sprintf( esc_html__( 'Fill in details to submit %s', 'bp-member-reviews' ), esc_html( strtolower( $bupr['review_label'] ) ) );
							?>
							</p>
							<?php if ( 0 === bp_displayed_user_id() ) { ?>
								<p>
										<select name="bupr_member_id" id="bupr_member_review_id">
											<option value=""><?php esc_html_e( '--Select--', 'bp-member-reviews' ); ?></option>
								<?php
								// if ( ! empty( $bupr_member ) ) {
								foreach ( $bupr_member as $user ) {
									if ( is_array( $bupr['add_taken_members'] ) && ! empty( $bupr['add_taken_members'] ) && isset( $user['member_roles'][0] ) && in_array( $user['member_roles'][0], $bupr['add_taken_members'] ) ) {
										echo '<option value="' . esc_attr( $user['member_id'] ) . '">' . esc_attr( $user['member_name'] ) . '</option>';
									}
									// }
								}
								?>
										</select><br/>
										<span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>
									</p>
							<?php } ?>
								

							<?php
							$bupr_display_rating = $this->bupr_criteria_is_enable();
							if ( true == $bupr_display_rating ) {
								$field_counter = 1;
								$flage         = true;
								if ( $bupr['multi_criteria_allowed'] ) {
									foreach ( $bupr['active_rating_fields'] as $bupr_rating_fields => $bupr_criteria_setting ) :
										?>
							<div class="multi-review">
								<div class="bupr-col-4 bupr-criteria-label">
											<?php echo esc_html( $bupr_rating_fields ); ?>
								</div>
								<div class="bupr-col-4 bupr-criteria-content" id="member_review<?php echo esc_attr( $field_counter ); ?>">
									<input type="hidden" id="<?php echo 'clicked' . esc_attr( $field_counter ); ?>" value="<?php echo 'not_clicked'; ?>">
									<input type="hidden" name="member_rated_stars[]" id="member_rated_stars" class="member_rated_stars bupr-star-member-rating" id="<?php echo 'member_rated_stars' . esc_attr( $field_counter ); ?>" value="0">
											<?php	for ( $i = 1; $i <= 5; $i++ ) { ?>
										<span class="far member_stars <?php echo esc_attr( $i ); ?> fa-star bupr-stars bupr-star-rate <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $field_counter ) . esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
									<?php } ?>
								</div>
								<div class="bupr-col-12 bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></div>
							</div>
											<?php
											$field_counter++;
									endforeach;
								}
								?>
				<input type="hidden" id="member_rating_field_counter" value="<?php echo esc_attr( --$field_counter ); ?>">
								<?php
							} else {
								$field_counter = 1;
								?>
								<div class="multi-review">
										<div class="bupr-col-4 bupr-criteria-label">
											<label><?php esc_html_e( 'Rating : ', 'bp-member-reviews' ); ?><small class="rating">*</small></label>
										</div>
										<div class="bupr-col-4 bupr-criteria-content" id="member_review<?php echo esc_attr( $field_counter ); ?>">
											<input type="hidden" id="<?php echo 'clicked' . esc_attr( $field_counter ); ?>" value="<?php echo 'not_clicked'; ?>">
											<input type="hidden" name="member_rated_stars[]" id="member_rated_stars" class="member_rated_stars bupr-star-member-rating" id="<?php echo 'member_rated_stars' . esc_attr( $field_counter ); ?>" value="0">
													<?php	for ( $i = 1; $i <= 5; $i++ ) { ?>
												<span class="far member_stars <?php echo esc_attr( $i ); ?> fa-star bupr-stars bupr-star-rate <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $field_counter ) . esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
											<?php } ?>
										</div>
										<div class="bupr-col-12 bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></div>
									</div>
									<input type="hidden" id="member_rating_field_counter" value="1">
								<?php } ?>
								<input type="hidden" id="bupr_member_review_id" value="<?php echo esc_attr( bp_displayed_user_id() ); ?>">
								<p class="bupr-hide-subject">
									<input name="review-subject" id="review_subject" type="text" placeholder="<?php esc_html_e( 'Review Subject', 'bp-member-reviews' ); ?>" ><br/><span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>
								</p>
								<textarea name="review-desc" id="review_desc" placeholder="<?php 
								/* translators: %s: review label;  */
								echo sprintf( esc_html__( 'Enter your %s', 'bp-member-reviews' ), esc_html( strtolower( $bupr['review_label'] ) ) ); ?>" rows="4" cols="50"></textarea><br/><span class="bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></span>
							<?php if ( $flag ) { ?>
									<p>
										<label for="bupr_anonymous_review"><input style="width:auto !important" type="checkbox" id="bupr_anonymous_review" value="value"><?php 
										/* translators: %s: review label;  */
										echo sprintf( esc_html__( 'Send %s anonymously.', 'bp-member-reviews' ), esc_html( strtolower( $bupr['review_label'] ) ) ); ?></label>
									</p>
							<?php } ?>
								<p>
							<?php wp_nonce_field( 'save-bp-member-review', 'security-nonce' ); ?>

									<button type="button" class="btn btn-default" id="bupr_save_review" name="submit-review">
									<?php
									/* translators: %s: */
									echo sprintf( esc_html__( 'Submit %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) );
									?>
									</button>
									<input type="hidden" value="<?php echo esc_attr( $login_user ); ?>" id="bupr_current_user_id" />
									<img src="<?php echo esc_url( $bupr_spinner_src ); ?>" class="bupr-save-reivew-spinner" />
								</p>
							</div>
						</form>
							<?php
						}
					}
		}
	}

	new BUPR_Shortcodes();
}