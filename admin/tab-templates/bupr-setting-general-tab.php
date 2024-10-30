<?php
/**
 * BuddyPress Member Review general tab.
 *
 * @package BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* admin setting on dashboard */
global $bupr;

$message = 'Hello [user-name],

We hope this message finds you well.

We are pleased to inform you that [reviewer-name] has recently submitted a review on your profile. To view the full review and respond, please click on the link below:

[review-link]

We appreciate your engagement in our community and value your contributions.

Best regards,  
The [site-name] Team';

$approve_message = 'Hello [site-admin],

Greetings!

You have received a new review on [site-name] that is pending approval. To review and approve the submission, please click on the link below:

[review-aproval-link]

Thank you for helping us maintain a vibrant and engaged community.

Best regards,  
The [site-name] Team';

$review_email_subject = ( ! empty( $bupr_admin_general['review_email_subject'] ) ) ? $bupr_admin_general['review_email_subject'] : 'You Have Received a New Review on [site-name]';
$review_email_message = ( ! empty( $bupr_admin_general['review_email_message'] ) ) ? $bupr_admin_general['review_email_message'] : $message;
$review_approve_email_subject = ( ! empty( $bupr_admin_general['review_approve_email_subject'] ) ) ? $bupr_admin_general['review_approve_email_subject'] : 'A New Review Requires Your Approval on [site-name]';
$review_approve_email_message = ( ! empty( $bupr_admin_general['review_approve_email_message'] ) ) ? $bupr_admin_general['review_approve_email_message'] : $approve_message;

// get all user for exclude for review.
$user_roles = array_reverse( get_editable_roles() );
$bupr_admin_general   = get_option( 'bupr_admin_general_options' );

?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e( 'General Settings', 'bp-member-reviews' ); ?></h3>
		</div>
		<div class="wbcom-admin-option-wrap wbcom-admin-option-wrap-view">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'bupr_admin_general_options' );
				do_settings_sections( 'bupr_admin_general_options' );
				?>
				<div class="form-table">
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr-multi-review">
								<?php esc_html_e( 'Multiple Reviews', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Allow users to submit multiple reviews to the same user.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to default the value to 'no' if unchecked -->
								<input type="hidden" name="bupr_admin_general_options[bupr_multi_reviews]" value="no">
								<input name="bupr_admin_general_options[bupr_multi_reviews]" type="checkbox" id="bupr-multi-review" <?php checked( esc_attr( $bupr['multi_reviews'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr-hide-review-button">
								<?php esc_html_e( 'Show Review Button', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Display the "Add Review" button on member profile headers.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<input name="bupr_admin_general_options[bupr_hide_review_button]" type="checkbox" id="bupr-hide-review-button" <?php checked( esc_attr( $bupr['hide_review_button'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_review_auto_approval">
								<?php esc_html_e( 'Auto approve reviews', 'bp-member-reviews' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Automatically approve reviews without requiring manual approval.', 'bp-member-reviews' ); ?>
							</p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to handle unchecked state -->
								<input type="hidden" name="bupr_admin_general_options[bupr_auto_approve_reviews]" value="no">
								<input type="checkbox" id="bupr_review_auto_approval" name="bupr_admin_general_options[bupr_auto_approve_reviews]" <?php checked( esc_attr( $bupr['auto_approve_reviews'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>

					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_member_dir_reviews">
								<?php esc_html_e( 'Show ratings in member directory', 'bp-member-reviews' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Display member ratings on the member directory page.', 'bp-member-reviews' ); ?>
							</p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to handle unchecked state -->
								<input type="hidden" name="bupr_admin_general_options[bupr_member_dir_reviews]" value="no">
								<input type="checkbox" id="bupr_member_dir_reviews" name="bupr_admin_general_options[bupr_member_dir_reviews]" <?php checked( esc_attr( $bupr['dir_view_ratings'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>

					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_allow_email">
								<?php esc_html_e( 'Emails', 'bp-member-reviews' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Send an email notification to members when someone reviews their profile.', 'bp-member-reviews' ); ?>
							</p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to handle unchecked state -->
								<input type="hidden" name="bupr_admin_general_options[bupr_allow_email]" value="no">
								<input type="checkbox" id="bupr_allow_email" name="bupr_admin_general_options[bupr_allow_email]" <?php checked( esc_attr( $bupr['allow_email'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>

					<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
						<div class="wbcom-settings-section-options-heading">
							<label>
								<?php esc_html_e( 'Email Subject', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Enter the subject line for review notification emails.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<input id="review_deny_email_subject" class="large-text" name="bupr_admin_general_options[review_email_subject]" value="<?php echo esc_attr( $review_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
						</div>
					</div>
					<div class="bgr-row wbcom-settings-section-wrap review-deny-email-section">
						<div class="wbcom-settings-section-options-heading">
							<label>
								<?php esc_html_e( 'Email Message', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Please add review email message.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<?php
							wp_editor(
								$review_email_message,
								'review-email-message',
								array(
									'media_buttons' => false,
									'textarea_name' => 'bupr_admin_general_options[review_email_message]',
								)
							);
							?>
						</div>					
					</div>
					<?php			
					if ( ! empty( $bupr_admin_general ) ) {
						if ( isset($bupr_admin_general['bupr_auto_approve_reviews']) && 'no' === $bupr_admin_general['bupr_auto_approve_reviews'] ) {
					?>
					<!-- admin email section -->
					 <div class="bgr-row wbcom-settings-section-wrap review-approve-email-section">
							<div class="wbcom-settings-section-options-heading">
								<label>
									<?php esc_html_e( 'Review Approve Email Subject', 'bp-member-reviews' ); ?>
								</label>
								<p class="description"><?php esc_html_e( 'Enter the subject line for review approval notification emails.', 'bp-member-reviews' ); ?></p>
							</div>
							<div class="wbcom-settings-section-options">
								<input id="review_approve_email_subject" class="large-text" name="bupr_admin_general_options[review_approve_email_subject]" value="<?php echo esc_attr( $review_approve_email_subject ); ?>" type="text" placeholder="Please enter review email subject.">
							</div>
						</div>
						<div class="bgr-row wbcom-settings-section-wrap review-approve-email-section">
					<div class="wbcom-settings-section-options-heading">
						<label>
							<?php esc_html_e( 'Review Approve Email Message', 'bp-member-reviews' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Enter the message content for review approval notification emails.', 'bp-member-reviews' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php						
						wp_editor(
							$review_approve_email_message,
							'review-approve-email-message',
							array(
								'media_buttons' => false,
								'textarea_name' => 'bupr_admin_general_options[review_approve_email_message]',
							)
						);
						?>
					</div>
					<?php } } ?>
					<!-- admin email section end -->
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_review_notification">
								<?php esc_html_e( 'BuddyPress Notifications', 'bp-member-reviews' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Notify members through BuddyPress when they receive a new review.', 'bp-member-reviews' ); ?>
							</p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to handle unchecked state -->
								<input type="hidden" name="bupr_admin_general_options[bupr_allow_notification]" value="no">
								<input type="checkbox" id="bupr_review_notification" name="bupr_admin_general_options[bupr_allow_notification]" <?php checked( esc_attr( $bupr['allow_notification'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>

					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_review_update">
								<?php esc_html_e( 'Update Review', 'bp-member-reviews' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Allow members to update or modify their reviews.', 'bp-member-reviews' ); ?>
							</p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<!-- Hidden field to handle unchecked state -->
								<input type="hidden" name="bupr_admin_general_options[bupr_allow_update]" value="no">
								<input type="checkbox" id="bupr_review_update" name="bupr_admin_general_options[bupr_allow_update]" <?php checked( esc_attr( $bupr['allow_update'] ), 'yes' ); ?> value="yes">
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>

					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="profile_reviews_per_page">
								<?php esc_html_e( 'Reviews pages show at most', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Limit the number of reviews displayed per page on the Member Reviews page.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<input id="profile_reviews_per_page" class="small-text" name="bupr_admin_general_options[profile_reviews_per_page]" step="1" min="1" value="<?php echo esc_attr( $bupr['reviews_per_page'] ); ?>" type="number">
							<?php esc_html_e( 'Reviews', 'bp-member-reviews' ); ?>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_exc_member">
								<?php esc_html_e( 'Select member roles to write reviews', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Select user roles that are allowed to submit reviews.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<select name="bupr_admin_general_options[bupr_exc_member][]" id="bupr_exc_member" class="bupr_excluding_member" multiple>
								<?php
								foreach ( $user_roles as $role => $details ) {
									$name = translate_user_role( $details['name'] );
									if ( ! empty( $bupr['exclude_given_members'] ) ) {
										if ( in_array( $role, $bupr['exclude_given_members'] ) ) {
											?>
												<option value="<?php echo esc_attr( $role ); ?>" <?php echo 'selected = "selected"'; ?>><?php echo esc_html( $name ); ?></option>
											<?php
										} else {
											?>
												<option value='<?php echo esc_attr( $role ); ?>'><?php echo esc_html( $name ); ?></option>
											<?php
										}
									} else {
										?>
											<option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_exc_member">
								<?php esc_html_e( 'User roles to accept reviews', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Select user roles that are eligible to receive reviews.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<select name="bupr_admin_general_options[bupr_add_member][]" id="bupr_add_member" class="bupr_adding_member" multiple>
								<?php
								foreach ( $user_roles as $role => $details ) {
									$name = translate_user_role( $details['name'] );
									if ( ! empty( $bupr['add_taken_members'] ) ) {
										if ( in_array( $role, $bupr['add_taken_members'] ) ) {
											?>
												<option value="<?php echo esc_attr( $role ); ?>" <?php echo 'selected = "selected"'; ?>><?php echo esc_html( $name ); ?></option>
											<?php
										} else {
											?>
												<option value='<?php echo esc_attr( $role ); ?>'><?php echo esc_html( $name ); ?></option>
											<?php
										}
									} else {
										?>
											<option value="<?php echo esc_attr( $role ); ?>"><?php echo esc_html( $name ); ?></option>
										<?php
									}
								}


								?>
							</select>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_enable_anonymous_reviews">
								<?php esc_html_e( 'Enable anonymous reviews', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Allow users to submit reviews anonymously.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<input type="checkbox" id="bupr_enable_anonymous_reviews" value="yes" name="bupr_admin_general_options[bupr_enable_anonymous_reviews]" <?php checked( esc_attr( $bupr['anonymous_reviews'] ), 'yes' ); ?>>
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>
					<div class="wbcom-settings-section-wrap">
						<div class="wbcom-settings-section-options-heading">
							<label for="bupr_enable_review_activity">
								<?php esc_html_e( 'Enable review activity', 'bp-member-reviews' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'Allow users to generate review activity.', 'bp-member-reviews' ); ?></p>
						</div>
						<div class="wbcom-settings-section-options">
							<label class="bupr-switch">
								<input type="checkbox" id="bupr_enable_review_activity" value="yes" name="bupr_admin_general_options[bupr_enable_review_activity]" <?php checked( esc_attr( $bupr['review_activity'] ), 'yes' ); ?>>
								<div class="bupr-slider bupr-round"></div>
							</label>
						</div>
					</div>
				</div>
				<?php submit_button(); ?>
			</form>
		</div>
	</div>
</div>
