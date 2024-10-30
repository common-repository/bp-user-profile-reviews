<?php
/**
 * Class to serve AJAX Calls
 *
 * @since    1.0.0
 * @author   Wbcom Designs
 * @package BuddyPress_Member_Reviews
 */

defined( 'ABSPATH' ) || exit;

/**
* Class to serve AJAX Calls
*
* @since    1.0.0
* @author   Wbcom Designs
*/
if ( ! class_exists( 'BUPR_AJAX' ) ) {
	/**
	 * The ajax functionality of the plugin.
	 *
	 * @package    BuddyPress_Member_Reviews
	 * @author     wbcomdesigns <admin@wbcomdesigns.com>
	 */
	class BUPR_AJAX {

		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function __construct() {

			/* add action for approving reviews */
			add_action( 'wp_ajax_bupr_approve_review', array( $this, 'bupr_approve_review' ) );
			add_action( 'wp_ajax_nopriv_bupr_approve_review', array( $this, 'bupr_approve_review' ) );

			add_action( 'wp_ajax_allow_bupr_member_review_update', array( $this, 'wp_allow_bupr_my_member' ) );
			add_action( 'wp_ajax_nopriv_allow_bupr_member_review_update', array( $this, 'wp_allow_bupr_my_member' ) );

			/*** Filter post_date_gmt for prevent update post date on update_post_data */
			add_filter( 'wp_insert_post_data', array( $this, 'bupr_filter_review_post' ), 10, 1 );
			
			add_action( 'wp_ajax_bupr_edit_review', array( $this, 'bupr_edit_review' ) );
			add_action( 'wp_ajax_bupr_update_review', array( $this, 'bupr_update_review' ) );
		}

		/**
		 * Actions performed on inserting post data.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @param    array $data Post data array.
		 * @author   Wbcom Designs
		 */
		public function bupr_filter_review_post( $data ) {
			if ( $data['post_type'] === 'review' ) {
				$post_date             = $data['post_date'];
				$post_date_gmt         = get_gmt_from_date( $post_date );
				$data['post_date_gmt'] = $post_date_gmt;
			}
			return $data;
		}

		/**
		 * Actions performed to approve review at admin end.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_approve_review() {
			if(check_ajax_referer('bupr_member_review_ajax','nonce')){
				if ( isset( $_POST['action'] ) && $_POST['action'] === 'bupr_approve_review') {
					$rid  =  isset( $_POST['review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) : '';
					$args = array(
						'ID'          => $rid,
						'post_status' => 'publish',
					);
					wp_update_post( $args );
					$author_id = get_post_field( 'post_author', $rid );
	
					do_action( 'gamipress_bp_member_review', $author_id );
	
					wp_send_json_success('review-approved-successfully');
				}
			}
		}

		/**
		 * Add review to member's profile.
		 *
		 * @since    1.0.0
		 * @author   Wbcom Designs
		 */
		public function wp_allow_bupr_my_member() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'review-nonce' ) ) {
				return false;
			}
			global $bupr;
			
			if ( filter_input( INPUT_POST, 'action' ) && filter_input( INPUT_POST, 'action' ) === 'allow_bupr_member_review_update' ) {
				$bupr_rating_criteria = array();
				if ( ! empty( $bupr['active_rating_fields'] ) ) {
					foreach ( $bupr['active_rating_fields'] as $bupr_keys => $bupr_fields ) {
							$bupr_rating_criteria[] = $bupr_keys;
					}
				}
				$bupr_reviews_status = 'draft';
				if ( 'yes' === $bupr['auto_approve_reviews'] ) {
					$bupr_reviews_status = 'publish';
				}

				$bupr_multi_reviews         = $bupr['multi_reviews'];
				$bupr_current_user          = filter_input( INPUT_POST, 'bupr_current_user' );
				$current_user         		= wp_get_current_user();				
				// $review_subject             = filter_input( INPUT_POST, 'bupr_review_title' );
				$review_desc                = filter_input( INPUT_POST, 'bupr_review_desc' );
				$bupr_member_id             = filter_input( INPUT_POST, 'bupr_member_id' );
				$review_count               = filter_input( INPUT_POST, 'bupr_field_counter' );
				$anonymous_review           = filter_input( INPUT_POST, 'bupr_anonymous_review' );
				$profile_rated_field_values = isset( $_POST['bupr_review_rating'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bupr_review_rating'] ) ) : '';
				$bupr_admin_general         = get_option( 'bupr_admin_general_options' );
				$site_name                  = get_bloginfo( 'name' );
				$site_admins                = get_users( array( 'role' => 'administrator' ) );
				if ( ! empty( $site_admins ) ) {
					$site_admin = $site_admins[0]->display_name;
				}
				$bupr_reviewed_user = get_user_by( 'id', $bupr_member_id );
				if( isset( $anonymous_review ) && $anonymous_review == 'yes' ) {
					$user_name      = "An anonymous user";
				} else {
					$user_name      = $current_user->display_name;
				}
				$bupr_reviewed_user_name = $bupr_reviewed_user->display_name;

				$review_subject = $bupr_reviewed_user_name. " received a " . bupr_profile_review_tab_singular_slug();

				$bupr_count = 0;

				$bupr_member_star = array();
				$member_args      = array(
					'post_type'      => 'review',
					'posts_per_page' => -1,
					'post_status'    => array(
						'draft',
						'publish',
					),
					'author'         => $bupr_current_user,
					'category'       => 'bp-member',
					'meta_query'     => array(
						array(
							'key'     => 'linked_bp_member',
							'value'   => $bupr_member_id,
							'compare' => '=',
						),
					),
				);
				$reviews_args     = new WP_Query( $member_args );

				if ( 'no' === $bupr['multi_reviews'] ) {
					$user_post_count = $reviews_args->post_count;
				} else {
					$user_post_count = 0;
				}

				if ( $user_post_count === 0 ) {
					if ( ! empty( $profile_rated_field_values ) ) {
						foreach ( $profile_rated_field_values as $bupr_stars_rate ) {
							if ( $bupr_count === $review_count ) {
								break;
							} else {
								$bupr_member_star[] = $bupr_stars_rate;
							}
							$bupr_count++;
						}
					}

					if ( ! empty( $bupr_member_id ) && $bupr_member_id !== 0 ) {
						$bupr_rated_stars = array();
						// print_r($bupr_rating_criteria);
						if ( $bupr['multi_criteria_allowed'] && ( count( $bupr_rating_criteria ) === count( $bupr_member_star ) ) )  {
							$bupr_rated_stars = array_combine( $bupr_rating_criteria, $bupr_member_star );
						} else {
							$bupr_rated_stars = $bupr_member_star;
						}

						$add_review_args = array(
							'post_type'    => 'review',
							'post_title'   => $review_subject,
							'post_content' => $review_desc,
							'post_status'  => $bupr_reviews_status,
						);

						$review_id = wp_insert_post( $add_review_args );

						if ( $bupr_reviews_status == 'publish' ) {
							do_action( 'gamipress_bp_member_review', $bupr_current_user );
						}

						//do_action( 'bupr_member_review_after_review_insert', $review_id, $bupr_member_id );

						if ( $review_id ) {

							wp_set_object_terms( $review_id, 'BP Member', 'review_category' );
							update_post_meta( $review_id, 'linked_bp_member', $bupr_member_id );
							if ( 'yes' === $bupr['anonymous_reviews'] ) {
								update_post_meta( $review_id, 'bupr_anonymous_review_post', $anonymous_review );
							}
							if ( ! empty( $bupr_rated_stars ) ) :
								update_post_meta( $review_id, 'profile_star_rating', $bupr_rated_stars );
								
								 // Recalculate reviews for the reviewed user after meta data is updated
								 if ( $bupr_reviews_status == 'publish' ) {
									do_action( 'bupr_member_review_after_review_insert', $review_id, $bupr_member_id );
								}
							endif;

							if (!empty($bupr_current_user) && !empty($bupr_member_id)) {
								$bupr_sender_data    = get_userdata($bupr_current_user);
								$bupr_sender_email   = $bupr_sender_data->data->user_email;
								$bupr_reciever_data  = get_userdata($bupr_member_id);
								$bupr_reciever_email = $bupr_reciever_data->data->user_email;
								$bupr_reciever_name  = $bupr_reciever_data->data->user_nicename;
								$bupr_reciever_login = $bupr_reciever_data->data->user_login;

								// Check BuddyPress version and use the appropriate function
								// Check if the bp_members_get_user_url() function exists (introduced in BuddyPress 12.0.0).
								if ( function_exists( 'bp_members_get_user_url' ) ) {
									// Use bp_members_get_user_url() if it exists (for BuddyPress v12.0.0 and above).
									$bupr_review_url = bp_members_get_user_url( $bupr_member_id ) . strtolower( $bupr['review_label_plural'] ) . '/view/' . $review_id;
								} else {
									// Fall back to bp_core_get_user_domain() for older versions of BuddyPress.
									$bupr_review_url = bp_core_get_user_domain( $bupr_member_id ) . strtolower( $bupr['review_label_plural'] ) . '/view/' . $review_id;
								}

							}


							/* send notification to member if  notification is enable */
							if ( ( 'yes' === $bupr['allow_notification'] ) && $bupr_reviews_status == 'publish') {
								do_action( 'bupr_sent_review_notification', $bupr_member_id, $review_id );
							}

							$review_link = '<a href="' . $bupr_review_url . '">' . $review_subject . '</a>';
							/* send email to member if email notification is enable */
							if ( 'yes' === $bupr['auto_approve_reviews'] ) {
								if ( 'yes' === $bupr['allow_email'] ) {
									$bupr_to = $bupr_reciever_email;
									// Define a default value for $bupr_subject with translation support
									$bupr_subject = __('New Review on Your Profile at [site-name]', 'bp-member-reviews');

									// Check if a custom subject line is set in the settings and update $bupr_subject accordingly
									if (isset($bupr_admin_general['review_email_subject']) && !empty($bupr_admin_general['review_email_subject'])) {
										$bupr_subject = $bupr_admin_general['review_email_subject'];
									}

									// Replace '[site-name]' placeholder in the subject with the actual site name
									$bupr_subject = str_replace('[site-name]', $site_name, $bupr_subject);

									$message = 'Hello [user-name], <br><br>
									We are pleased to inform you that [reviewer-name] has recently reviewed your profile.<br><br>
									To view the review, simply click on the link below:<br>
									[review-link]<br><br>
									Best regards,<br>
									The [site-name] Team';
									/* translators: %s is replaced with the user name %2$s is replaced with the review singular lable of translations */
									if ( isset( $bupr_admin_general['review_email_message'] ) && ! empty( $bupr_admin_general['review_email_message'] ) ) {
										$bupr_message = str_replace(
											array( '[user-name]', '[reviewer-name]', '[review-link]', '[site-name]' ),
											array( $bupr_reciever_name, $user_name, $review_link, $site_name ),
											$bupr_admin_general['review_email_message']
										);
									}else{
										$bupr_message = str_replace(
											array( '[user-name]', '[reviewer-name]', '[review-link]', '[site-name]' ),
											array( $bupr_reciever_name, $user_name, $review_link, $site_name ),
											$message
										);
									}
									$bupr_header = array( 'Content-Type: text/html; charset=UTF-8' );
									wp_mail( $bupr_to, $bupr_subject, nl2br( $bupr_message ), $bupr_header );
								}
							}

							if ( 'no' === $bupr['auto_approve_reviews'] ) {
								if ( 'yes' === $bupr['allow_email'] ) {
									$bupr_to = get_option('admin_email');
									$bupr_admin_general  = get_option( 'bupr_admin_general_options' );									
									$bupr_subject     = ( isset( $bupr_admin_general['review_approve_email_subject'] ) ) ? $bupr_admin_general['review_approve_email_subject'] : '';
									$bupr_subject     = str_replace('[site-name]', $site_name, $bupr_subject);
									$bupr_review_url  = add_query_arg(
														array(
															'post_type' => 'review'
														),
														admin_url( 'edit.php' )
													);
									$approve_review_link = '<a href="' . $bupr_review_url . '">' . $review_subject . '</a>';									
									$bupr_approve_message = ( isset( $bupr_admin_general['review_approve_email_message'] ) ) ? $bupr_admin_general['review_approve_email_message'] : '';									
									$bupr_message = str_replace(
										array( '[review-aproval-link]', '[site-admin]', '[site-name]' ),
										array( $approve_review_link, $site_admin, $site_name ),
										$bupr_approve_message
									);
									$bupr_header = array( 'Content-Type: text/html; charset=UTF-8' );									
									wp_mail( $bupr_to, $bupr_subject, nl2br( $bupr_message ), $bupr_header );
								}
								/* translators: %s: */
								wp_send_json_success(sprintf( esc_html__( 'Thank you for sharing your %s! After admin approval, it will be displayed on members\' profiles.', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ));
								
							} else {
								/* translators: %s: */
								wp_send_json_success( sprintf( esc_html__( 'Thank you for sharing your thoughts in this %s!', 'bp-member-reviews' ), esc_html( strtolower( $bupr['review_label'] ) ) ) );
							}
						} else {
							wp_send_json_error( '<p class="bupr-error">' . esc_html__( 'Please try again!', 'bp-member-reviews' ) . '</p>' );
						}						
						
					} else {
						wp_send_json_error( '<p class="bupr-error">' . esc_html__( 'Please select a member.', 'bp-member-reviews' ) . '</p>' );
					}
				} else {
					/* translators: %s: */
					wp_send_json_success( sprintf( esc_html__( 'You already posted a %1$s for this member.', 'bp-member-reviews' ), esc_html( strtolower( $bupr['review_label'] ) ) ) );
				}
				die;
			}
		}

		public function bupr_edit_review() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'review-nonce' ) ) {
				return false;
			}
			if ( isset( $_POST['action'] ) && 'bupr_edit_review' === $_POST['action'] ) {
				global $bupr;

				$review_id             = ( isset( $_POST['review'] ) ) ?  sanitize_text_field( wp_unslash( $_POST['review'] ) ) : '';
				$review                = get_post( $review_id );
				$member_review_ratings = get_post_meta( $review_id, 'profile_star_rating', false );
				$return_review         = array();
				$review_output         = '';
				$field_counter         = 1;

				if ( ! empty( $bupr['active_rating_fields'] ) ) {
					$member_review_rating_fields = $bupr['active_rating_fields'];
				}

				$bupr_rating_criteria = array();
				if ( ! empty( $member_review_rating_fields ) ) {
					foreach ( $member_review_rating_fields as $bupr_keys => $bupr_fields ) {
							$bupr_rating_criteria[] = $bupr_keys;
					}
				}

				$review_output .= '<div id="bupr-edit-review-field-wrapper" data-review="' . esc_attr( $review_id ) . '">';
				$review_output .= '<textarea name="bupr-review-description" id="review_desc" rows="4" cols="50">' . $review->post_content . '</textarea>';
				if ( ! empty( $member_review_rating_fields ) && ! empty( $member_review_ratings[0] ) ) {
					foreach ( $member_review_ratings[0] as $field => $bupr_value ) {
						if ( in_array( $field, $bupr_rating_criteria, true ) ) {
							$review_output .= '<div class="multi-review"><div class="bupr-col-4 bupr-criteria-label">' . esc_attr( $field ) . '</div>';
							$review_output .= '<div id="member-review-' . $field_counter . '" class="bupr-col-4 bupr-criteria-content">';
							$review_output .= '<input type="hidden" id="clicked' . esc_attr( $field_counter ) . '" value="not_clicked">';
							$review_output .= '<input type="hidden" name="member_rated_stars[]" class="member_rated_stars bupr-star-member-rating" id="member_rated_stars' . esc_attr( $field_counter ) . '" data-critaria="' . esc_attr( $field ) . '" value="0" >';
							/*** Star rating Ratings */
							$stars_on  = $bupr_value;
							$stars_off = 5 - $stars_on;
							$count     = 0;
							for ( $i = 1; $i <= $stars_on; $i++ ) {
								$review_output .= '<span id="' . esc_attr( $field_counter . $i ) . '" class="fas fa-star bupr-star-rate member-edit-stars bupr-star ' . esc_attr( $i ) . '" data-attr="' . esc_attr( $i ) . '"></span>';
								$count++;
							}

							for ( $i = 1; $i <= 5; $i++ ) {
								if ( $i > $count ) {
									$review_output .= '<span id="' . esc_attr( $field_counter . $i ) . '" class="far fa-star stars bupr-star-rate member-edit-stars bupr-star ' . esc_attr( $i ) . '" data-attr="' . esc_attr( $i ) . '"></span>';
								}
							}
							/*star rating end */
							$review_output .= '</div></div>';
						}else {
							$stars_on  = $bupr_value;
							$field_counter = 1;
							$count     = 0;
							ob_start();
							?>
							<div class="multi-review">
									<div class="bupr-col-4 bupr-criteria-label">
										<label><?php esc_html_e( 'Rating : ', 'bp-member-reviews' ); ?><small class="rating">*</small></label>
									</div>
									<div class="bupr-col-4 bupr-criteria-content" id="member_review<?php echo esc_attr( $field_counter ); ?>">
										<input type="hidden" id="<?php echo 'clicked' . esc_attr( $field_counter ); ?>" value="<?php echo 'not_clicked'; ?>">
										<input type="hidden" name="member_rated_stars[]" id="member_rated_stars" class="member_rated_stars bupr-star-member-rating" id="<?php echo 'member_rated_stars' . esc_attr( $field_counter ); ?>" data-critaria="<?php echo esc_attr( $field ); ?>" value="0"  value="0">
												<?php	
												for( $i = 1; $i <= $stars_on; $i++ ) {
													echo '<span id="' . esc_attr( $field_counter . $i ) . '" class="fas fa-star bupr-star-rate member-edit-stars bupr-star ' . esc_attr( $i ) . '" data-attr="' . esc_attr( $i ) . '"></span>';
													$count++;
												}
												for( $i = 1; $i <= 5; $i++ ) { 
													if ( $i > $count ) { ?>
											<span class="far member_stars <?php echo esc_attr( $i ); ?> fa-star bupr-stars bupr-star-rate member-edit-stars <?php echo esc_attr( $i ); ?>" id="<?php echo esc_attr( $field_counter ) . esc_attr( $i ); ?>" data-attr="<?php echo esc_attr( $i ); ?>" ></span>
										<?php }
									} ?>
									</div>
									<div class="bupr-col-12 bupr-error-fields">*<?php esc_html_e( 'This field is required.', 'bp-member-reviews' ); ?></div>
								</div>
								<input type="hidden" id="member_rating_field_counter" value="1">
							<?php 
							$review_output .= ob_get_contents();
							ob_end_clean();
						} 
						$field_counter++;
					}
				}
					
				/* translators: %s: */
				$review_output .= '<button type="button" class="btn btn-default" id="bupr_upodate_review" name="update-review">' . sprintf( esc_html__( 'Update %s', 'bp-member-reviews' ), esc_html( $bupr['review_label'] ) ) . '</button>';
				$review_output .= '</div>';

				if ( ! empty( $review ) ) {
					$return_review = array(
						'review' => $review_output,
					);
					wp_send_json_success( $return_review );
				}
			}
		}

		public function bupr_update_review() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'review-nonce' ) ) {
				return false;
			}
			if ( isset( $_POST['action'] ) && 'bupr_update_review' === $_POST['action'] ) {
				global $bupr;

				$review_id       = isset( $_POST['review_id'] ) ? sanitize_text_field( wp_unslash( $_POST['review_id'] ) ) : 0;
				$review_content  = isset( $_POST['bupr_review_desc'] ) ? sanitize_text_field( wp_unslash( $_POST['bupr_review_desc'] ) ) : '';
				$critaria_rating = isset( $_POST['bupr_review_rating'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['bupr_review_rating'] ) ) : '';
				$old_ratings     = get_post_meta( $review_id, 'profile_star_rating', true );

				$review_args = array(
					'ID'           => esc_sql( $review_id ),
					'post_content' => wp_kses_post( $review_content ),
					'post_status'  => 'publish',
				);

				$update_review = wp_update_post( $review_args, true );

				if ( ! empty( $critaria_rating ) ) {
					foreach ( $critaria_rating as $critaria => $rating ) {
						if ( array_key_exists( $critaria, $old_ratings ) && '0' !== $rating ) {
							$old_ratings[ $critaria ] = $rating;
						}
					}

					update_post_meta( $review_id, 'profile_star_rating', $old_ratings );
				}

				if ( ! is_wp_error( $update_review ) ) {
					wp_send_json_success();
				} else {
					wp_send_json_error();
				}
			}

		}
	}
	new BUPR_AJAX();
}
