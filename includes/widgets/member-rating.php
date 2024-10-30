<?php
add_action( 'widgets_init', 'bupr_member_rating_widget' );

function bupr_member_rating_widget() {
    register_widget( 'bupr_single_member_rating_widget' );
}

class bupr_single_member_rating_widget extends WP_Widget {

    /** Constructor to set up widget options */
    function __construct() {
        $widget_ops  = array(
            'classname'   => 'bupr_single_member_rating_widget buddypress',
            'description' => esc_html__( 'Display ratings for the displayed member.', 'bp-member-reviews' ),
        );
        $control_ops = array(
            'width'   => 280,
            'height'  => 350,
            'id_base' => 'bupr_single_member_rating_widget',
        );
        parent::__construct( 'bupr_single_member_rating_widget', esc_html__( 'BP Displayed Member Rating Widget', 'bp-member-reviews' ), $widget_ops, $control_ops );
    }

    function widget( $args, $instance ) {
        extract( $args );

        // Check if it's a user profile being viewed
        if ( ! bp_is_user() ) {
            return;
        }

        $user_id         = bp_displayed_user_id(); // Get the displayed user ID
        /* translators: %s: Review label; */
        $bupr_title      = isset( $instance['bupr_title'] ) ? apply_filters( 'widget_title', $instance['bupr_title'] ) : sprintf( __( "%s's Ratings", 'bp-member-reviews' ), bp_get_displayed_user_fullname() );
        $rating_limit    = isset( $instance['rating_limit'] ) ? $instance['rating_limit'] : 5;
        $rating_default  = isset( $instance['rating_default'] ) ? $instance['rating_default'] : 'latest';
        
        // Get the reviews for the displayed user
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
        $final_review_arr = array();
        $final_review_obj = array();
        $bupr_reviews_count = count( $reviews );

        // Process each review to get the star rating
        if ( $bupr_reviews_count != 0 ) {
            foreach ( $reviews as $review ) {
                $review_ratings = get_post_meta( $review->ID, 'profile_star_rating', true );
                if ( is_array( $review_ratings ) && ! empty( $review_ratings ) ) {
                    $total_rating = array_sum( $review_ratings );
                    $rating_count = count( $review_ratings );
                    $final_review_arr[ $review->ID ] = round( (int) $total_rating / $rating_count, 2 ); // Ensure rating precision
                    $final_review_obj[ $review->ID ] = $review;
                }
            }
        }

        echo $before_widget; // phpcs:ignore.
        echo $before_title . esc_html( $bupr_title ) . $after_title; // phpcs:ignore.
        // Sorting Logic for Reviews (highest, lowest, latest)
        if ( ! empty( $final_review_arr ) ) {
            if ( 'highest' === $rating_default ) {
                arsort( $final_review_arr );
            } elseif ( 'lowest' === $rating_default ) {
                asort( $final_review_arr );
            }

            // Display the sorted reviews
            echo '<ul class="item-list" id="bp-member-rating">';
            $bupr_user_count = 0;
            foreach ( $final_review_arr as $buprKey => $buprValue ) {
                if ( $bupr_user_count == $rating_limit ) {
                    break;
                } else {
                    $author_id = $final_review_obj[ $buprKey ]->post_author;
                    $members_profile = bp_core_get_userlink( $author_id );
                    $user_anonymous_review = get_post_meta( $buprKey, 'bupr_anonymous_review_post', true );
                    
                    echo '<li class="vcard"><div class="item-avatar">';
                    echo get_avatar( $author_id, 50 );
                    echo '</div><div class="item"><div class="item-title">';
                    echo ( $user_anonymous_review != 'yes' ) ? wp_kses_post( $members_profile ) : esc_html__( 'Anonymous', 'bp-member-reviews' );
                    echo '</div><div class="item-meta">';

                    // Display the star ratings (fixed 5 stars with actual rating filled)
                    $bupr_avg_rating = $buprValue;
                    $stars_on        = floor( $bupr_avg_rating );
                    $stars_half      = ( $bupr_avg_rating - $stars_on >= 0.5 ) ? 1 : 0;
                    $stars_off       = 5 - ( $stars_on + $stars_half );

                    // Filled stars
                    for ( $i = 1; $i <= $stars_on; $i++ ) {
                        echo '<span class="fas fa-star bupr-star-rate"></span>';
                    }
                    // Half star
                    if ( $stars_half ) {
                        echo '<span class="fas fa-star-half-alt bupr-star-rate"></span>';
                    }
                    // Empty stars
                    for ( $i = 1; $i <= $stars_off; $i++ ) {
                        echo '<span class="far fa-star bupr-star-rate"></span>';
                    }

                    // Display review count and rating (X/5)
                    echo '</div><span class="bupr-meta">';
                    /* translators: %1$s: average rating; */
                    echo sprintf( esc_html__( '%1$s/5', 'bp-member-reviews' ), esc_html( $bupr_avg_rating ) );
                    echo '</span></div></li>';

                    $bupr_user_count++;
                }
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__( 'No member has been rated yet.', 'bp-member-reviews' ) . '</p>';
        }

        echo $after_widget; // phpcs:ignore.
    }

    // Function for updating widget options
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['bupr_title'] = strip_tags( $new_instance['bupr_title'] );
        $instance['rating_limit'] = $new_instance['rating_limit'];
        $instance['rating_default'] = $new_instance['rating_default'];
        return $instance;
    }

    // Widget form options
    function form( $instance ) {
        $defaults = array(
            'bupr_title'     => esc_html__( 'Member Ratings', 'bp-member-reviews' ),
            'rating_limit'   => 5,
            'rating_default' => 'latest',
        );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = esc_attr( $instance['bupr_title'] );
        $rating_limit = esc_attr( $instance['rating_limit'] );
        $rating_default = esc_attr( $instance['rating_default'] );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'bupr_title' ) ); ?>"><?php esc_html_e( 'Enter Title:', 'bp-member-reviews' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'bupr_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bupr_title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'rating_limit' ) ); ?>"><?php esc_html_e( 'Number of Ratings to Show:', 'bp-member-reviews' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'rating_limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rating_limit' ) ); ?>" type="number" value="<?php echo esc_attr( $rating_limit ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'rating_default' ) ); ?>"><?php esc_html_e( 'Default Sorting:', 'bp-member-reviews' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'rating_default' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rating_default' ) ); ?>" class="widefat">
                <option value="latest" <?php selected( $rating_default, 'latest' ); ?>><?php esc_html_e( 'Latest', 'bp-member-reviews' ); ?></option>
                <option value="highest" <?php selected( $rating_default, 'highest' ); ?>><?php esc_html_e( 'Highest', 'bp-member-reviews' ); ?></option>
                <option value="lowest"  <?php selected( $rating_default, 'lowest' ); ?>><?php esc_html_e( 'Lowest', 'bp-member-reviews' ); ?></option>
            </select>
        </p>
        <?php
    }
}
