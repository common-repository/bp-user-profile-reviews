<?php
/**
 * BuddyPress Member Review Shortcode Tab.
 *
 * @package BuddyPress_Member_Reviews
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wbcom-tab-content">
	<div class="wbcom-wrapper-admin">
		
	<div class="bupr-tab-header">
			<div class="wbcom-admin-title-section">
				<h3>
					<?php esc_html_e( 'Member Review Shortcode', 'bp-member-reviews' ); ?>
				</h3>
				<input type="hidden" class="bupr-tab-active" value="shortcode"/>
			</div>
		</div>

		<!-- Top Rated Members Shortcode Block -->
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'Top Rated Members Shortcode', 'bp-member-reviews' ); ?></label>
				<p class="description">
					<?php esc_html_e( 'Use this shortcode to display top-rated BuddyPress members.', 'bp-member-reviews' ); ?>							
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<code><?php echo esc_html( '[bupr_display_top_members]' ); ?></code>
			</div>

			<!-- Available Parameters for Top Rated Members Shortcode -->
			<div class="wbcom-settings-section-options-heading">
				<h4><?php esc_html_e( 'Available Parameters', 'bp-member-reviews' ); ?></h4>
			</div>

			<!-- Title Parameter -->
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'title', 'bp-member-reviews' ); ?></label>
				<p class="description">
					<?php esc_html_e( 'Add a title before the top-rated/reviewed members listing.', 'bp-member-reviews' ); ?>							
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<code><?php echo esc_html( "[bupr_display_top_members title='Top Rated Members']" ); ?></code>
			</div>

			<!-- Total Members Parameter -->
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'total_member', 'bp-member-reviews' ); ?></label>
				<p class="description">
					<?php esc_html_e( 'Limit the number of members displayed in the top-rated/reviewed members list.', 'bp-member-reviews' ); ?>							
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<code><?php echo esc_html( "[bupr_display_top_members total_member=5]" ); ?></code>
			</div>

			<!-- Type Parameter -->
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'type', 'bp-member-reviews' ); ?></label>
				<p class="description">
					<?php esc_html_e( 'Display members based on either the maximum number of reviews or the highest ratings.', 'bp-member-reviews' ); ?>							
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<code><?php echo esc_html( "[bupr_display_top_members type='top rated']" ); ?></code>
				<br/>
				<code><?php echo esc_html( "[bupr_display_top_members type='top reviewed']" ); ?></code>
			</div>

			<!-- Avatar Parameter -->
			<div class="wbcom-settings-section-options-heading">
				<label><?php esc_html_e( 'avatar', 'bp-member-reviews' ); ?></label>
				<p class="description">
					<?php esc_html_e( 'Hide or show the members’ avatars.', 'bp-member-reviews' ); ?>							
				</p>
			</div>
			<div class="wbcom-settings-section-options">
				<code><?php echo esc_html( "[bupr_display_top_members avatar='hide']" ); ?></code>
			</div>
		</div>

	</div>
</div>
