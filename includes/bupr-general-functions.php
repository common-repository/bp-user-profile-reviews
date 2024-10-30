<?php
/**
 * Locate template.
 *
 * Locate the called template.
 * Search Order:
 * 1. /themes/theme/buddypress-member-review/$template_name
 * 2. /themes/theme/$template_name
 * 3. /plugins/buddypress-member-review/templates/$template_name.
 *
 * @since 1.0.0
 *
 * @param   string $template_name          Template to load.
 * @param   string $template_path          Path to templates.
 * @param   string $default_path           Default path to template files.
 * @return  string                          Path to the template file.
 */
function bupr_locate_template( $template_name, $template_path = '', $default_path = '' ) {

    if ( ! $template_path ) :
        $template_path = 'buddypress-member-review/';
    endif;

    // Set default plugin templates path.
    if ( ! $default_path ) :
        $default_path = BUPR_PLUGIN_PATH . 'includes/templates/';
    endif;

    // Search template file in theme folder.
    $template = locate_template(
        array(
            $template_path . $template_name,
            $template_name,
        )
    );

    // Get plugin template file if not found in theme.
    if ( ! $template ) :
        $template = $default_path . $template_name;
    endif;

    return apply_filters( 'bupr_locate_template', $template, $template_name, $template_path, $default_path );
}

/**
 * Get template.
 *
 * Search for the template and include the file.
 *
 * @since 1.0.0
 *
 * @param string $template_name          Template to load.
 * @param array  $args                   Arguments to pass to the template file.
 * @param string $template_path          Path to templates.
 * @param string $default_path           Default path to template files.
 */
function bupr_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( is_array( $args ) && ! empty( $args ) ) :
        extract( $args ); // Extract the args into variables.
    endif;

    $template_file = bupr_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $template_file ) ) :
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $template_file ) ), '1.0.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return;
    endif;

    include $template_file;
}

/**
 * Get the singular review tab name on a member's profile.
 *
 * @return string Singular tab name.
 */
function bupr_profile_review_singular_tab_name() {
    global $bupr;

    $tab_name = isset( $bupr['review_label'] ) ? esc_html( $bupr['review_label'] ) : __( 'Review', 'bp-member-reviews' );

    return apply_filters( 'bupr_profile_review_singular_tab_name', $tab_name );
}

/**
 * Get the plural review tab name on a member's profile.
 *
 * @return string Plural tab name.
 */
function bupr_profile_review_tab_name() {
    global $bupr;

    $tab_name = isset( $bupr['review_label_plural'] ) ? esc_html( $bupr['review_label_plural'] ) : __( 'Reviews', 'bp-member-reviews' );

    return apply_filters( 'bupr_review_tab_name', $tab_name );
}

/**
 * Get the slug for the plural review tab on a member's profile.
 *
 * @return string Slug for plural tab.
 */
function bupr_profile_review_tab_plural_slug() {
    global $bupr;

    $tab_slug = isset( $bupr['review_label_plural'] ) ? sanitize_title( $bupr['review_label_plural'] ) : 'reviews';

    return apply_filters( 'bupr_review_tab_plural_slug', esc_html( $tab_slug ) );
}

/**
 * Get the slug for the singular review tab on a member's profile.
 *
 * @return string Slug for singular tab.
 */
function bupr_profile_review_tab_singular_slug() {
    global $bupr;

    $tab_slug = isset( $bupr['review_label'] ) ? sanitize_title( $bupr['review_label'] ) : 'review';

    return apply_filters( 'bupr_review_tab_singular_slug', esc_html( $tab_slug ) );
}

/**
 * Register BuddyPress Member Review triggers for GamiPress integration.
 *
 * @param array $triggers GamiPress triggers.
 * @return array Modified triggers.
 */
function buddypress_member_review_bp_activity_triggers( $triggers ) {
    $triggers[ __( 'BuddyPress Member Review', 'bp-member-reviews' ) ] = array(
        'gamipress_bp_member_review' => __( 'Give Member Review', 'bp-member-reviews' ),
    );

    return $triggers;
}
add_filter( 'gamipress_activity_triggers', 'buddypress_member_review_bp_activity_triggers' );

/**
 * Get the user ID for the review trigger in GamiPress.
 *
 * @param int    $user_id The current user ID.
 * @param string $trigger The trigger identifier.
 * @param array  $args    Trigger arguments.
 * @return int Modified user ID.
 */
function buddypress_member_review_trigger_get_user_id( $user_id, $trigger, $args ) {
    if ( 'gamipress_bp_member_review' === $trigger ) {
        $user_id = $args[0];
    }

    return $user_id;
}
add_filter( 'gamipress_trigger_get_user_id', 'buddypress_member_review_trigger_get_user_id', 10, 3 );


/**
 * Handle review submission and update user meta with review data.
 *
 * This function is triggered after a review is submitted or updated.
 *
 * @param int $review_id       ID of the submitted review.
 * @param int $reviewed_user_id ID of the user who received the review.
 */
add_action( 'bupr_member_review_after_review_insert', 'bupr_handle_review_submission', 10, 2 );

function bupr_handle_review_submission( $review_id, $reviewed_user_id ) {
    // Recalculate the review data for the reviewed user
    bupr_recalculate_user_reviews_for_user( $reviewed_user_id );
}

/**
 * Recalculate and update review data for a specific user using SQL.
 *
 * This function recalculates the total number of reviews and the aggregate rating for a specific user.
 *
 * @param int $user_id The ID of the user whose reviews need recalculation.
 */
function bupr_recalculate_user_reviews_for_user( $user_id ) {
    global $wpdb;

    // Fetch all reviews for this user using raw SQL

    $reviews = $wpdb->get_results( $wpdb->prepare(
        "
        SELECT p.ID 
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'review'
        AND p.post_status = 'publish'
        AND pm.meta_key = 'linked_bp_member'
        AND pm.meta_value = %d
        ",
        $user_id
    ) );

    // Initialize variables to calculate aggregate rating
    $total_rating = 0;
    $review_count = count( $reviews );

    if ( $review_count > 0 ) {
        foreach ( $reviews as $review ) {
            // Fetch review ratings from post meta
            $review_ratings = get_post_meta( $review->ID, 'profile_star_rating', true );

            // If the ratings are serialized, unserialize and calculate the average
            if ( is_array( $review_ratings ) && ! empty( $review_ratings ) ) {
                $total_rating += array_sum( $review_ratings ) / count( $review_ratings );
            } else {
                $total_rating += (int) $review_ratings;
            }
        }

        // Calculate the aggregate rating
        $aggregate_rating = $total_rating / $review_count;

        // Update user meta with the calculated review count and aggregate rating
        update_user_meta( $user_id, 'bupr_review_count', $review_count );
        update_user_meta( $user_id, 'bupr_aggregate_rating', $aggregate_rating );

    } else {
        // If no reviews are found, reset the user's review count and aggregate rating
        update_user_meta( $user_id, 'bupr_review_count', 0 );
        update_user_meta( $user_id, 'bupr_aggregate_rating', 0 );

    }
}



/**
 * Recalculate and update review data for a batch of users.
 *
 * This function processes a limited number of users at a time to avoid memory and timeout issues.
 * It uses pagination to process the users in batches.
 *
 * @param int $batch_size The number of users to process in each batch.
 * @param int $paged      The current batch number (for pagination).
 */
function bupr_recalculate_user_reviews_batch( $batch_size = 50, $paged = 1 ) {
    // Set up user query arguments for pagination
    $args = array(
        'number' => $batch_size,
        'paged'  => $paged,
    );

    $users = get_users( $args );

    foreach ( $users as $user ) {
        $user_id = $user->ID;

        // Fetch all reviews for this user
        $bupr_args = array(
            'post_type'   => 'review',
            'post_status' => 'publish',
            'meta_query'  => array(
                array(
                    'key'     => 'linked_bp_member',
                    'value'   => $user_id,
                    'compare' => '=',
                ),
            ),
        );
        $reviews = get_posts( $bupr_args );

        // Initialize variables to calculate aggregate rating
        $total_rating = 0;
        $review_count = count( $reviews );

        if ( $review_count > 0 ) {
            foreach ( $reviews as $review ) {
                $review_ratings = get_post_meta( $review->ID, 'profile_star_rating', true );

                if ( is_array( $review_ratings ) && ! empty( $review_ratings ) ) {
                    $total_rating += array_sum( $review_ratings ) / count( $review_ratings );
                } else {
                    $total_rating += (int) $review_ratings;
                }
            }

            // Calculate the aggregate rating
            $aggregate_rating = $total_rating / $review_count;

            // Update user meta
            update_user_meta( $user_id, 'bupr_review_count', $review_count );
            update_user_meta( $user_id, 'bupr_aggregate_rating', $aggregate_rating );
        }
    }

    return count( $users ); // Return the number of users processed in this batch
}

/**
 * Schedule a cron job for recalculating user reviews in batches.
 */
function bupr_schedule_review_recalculation() {
    if ( ! wp_next_scheduled( 'bupr_cron_recalculate_user_reviews_batch' ) ) {
        wp_schedule_event( time(), 'hourly', 'bupr_cron_recalculate_user_reviews_batch' );
    }
}
add_action( 'wp', 'bupr_schedule_review_recalculation' );

/**
 * Perform batch recalculation via cron job.
 */
function bupr_cron_recalculate_user_reviews_batch() {
    $batch_size = 50;
    $paged = (int) get_option( 'bupr_current_batch', 1 ); // Get current batch from the database

    $users_processed = bupr_recalculate_user_reviews_batch( $batch_size, $paged );

    if ( $users_processed < $batch_size ) {
        // If fewer users were processed than the batch size, reset the batch counter
        update_option( 'bupr_current_batch', 1 );
    } else {
        // Otherwise, move to the next batch
        update_option( 'bupr_current_batch', $paged + 1 );
    }
}

/**
 * Update the activity action with youzify.
 *
 * @param  mixed $action
 * @param  object $activity
 * @return mixed $action
 */
function bupr_youzify_activity_action_wall_posts( $action, $activity ){	
	$action = $activity->action;
	return $action;
}
add_filter( 'youzify_activity_new_post_action', 'bupr_youzify_activity_action_wall_posts',10, 2 );




