<?php
add_action( 'widgets_init', 'bupr_members_review_widget' );

function bupr_members_review_widget() {
    register_widget( 'bupr_members_review_setting' );
}

class bupr_members_review_setting extends WP_Widget {

    /** Constructor */
    function __construct() {
        $widget_ops  = array(
            'classname'   => 'bupr_members_review_setting',
            'description' => esc_html__( 'Display members list according to members reviews.', 'bp-member-reviews' ),
        );
        $control_ops = array(
            'width'   => 280,
            'height'  => 350,
            'id_base' => 'bupr_members_review_setting',
        );
        parent::__construct( 'bupr_members_review_setting', esc_html__( 'BP Member Review Widget', 'bp-member-reviews' ), $widget_ops, $control_ops );
    }

    /** Display widget */
    function widget( $args, $instance ) {
        extract( $args );

        global $wpdb;

        // Widget instance settings
        $bupr_title   = isset( $instance['bupr_title'] ) ? apply_filters( 'widget_title', $instance['bupr_title'] ) : esc_html__( 'Top Rated Members', 'bp-member-reviews' );
        $memberLimit  = isset( $instance['bupr_member'] ) ? (int) $instance['bupr_member'] : 20; // Limit to 20 members by default
        $topMember    = isset( $instance['top_member'] ) ? $instance['top_member'] : 'top-rated';
        $avatar       = isset( $instance['avatar'] ) ? $instance['avatar'] : 'Show';

        // Build the SQL query based on the sorting method
        if ( $topMember === 'top-rated' ) {
            // Query for top-rated members (by aggregate rating)
            $results = $wpdb->get_results( $wpdb->prepare(
                "
                SELECT um1.user_id, um1.meta_value AS aggregate_rating, um2.meta_value AS review_count
                FROM $wpdb->usermeta AS um1
                JOIN $wpdb->usermeta AS um2 ON um1.user_id = um2.user_id
                WHERE um1.meta_key = 'bupr_aggregate_rating'
                AND um2.meta_key = 'bupr_review_count'
                ORDER BY CAST(um1.meta_value AS DECIMAL(10,2)) DESC
                LIMIT %d
                ", $memberLimit)
            );
        } elseif ( $topMember === 'top-viewed' ) {
            // Query for most-reviewed members (by review count)
            $results = $wpdb->get_results( $wpdb->prepare(
                "
                SELECT um1.user_id, um1.meta_value AS aggregate_rating, um2.meta_value AS review_count
                FROM $wpdb->usermeta AS um1
                JOIN $wpdb->usermeta AS um2 ON um1.user_id = um2.user_id
                WHERE um1.meta_key = 'bupr_aggregate_rating'
                AND um2.meta_key = 'bupr_review_count'
                ORDER BY CAST(um2.meta_value AS UNSIGNED) DESC
                LIMIT %d
                ", $memberLimit)
            );
        }

        // Check if any users were found
        if ( ! empty( $results ) ) {
            echo $before_widget; // phpcs:ignore.
            echo $before_title . esc_html( $bupr_title ) . $after_title; // phpcs:ignore.
            echo '<ul class="bupr-member-main">';

            // Loop through the results and display them
            foreach ( $results as $user ) {
                $user_id = $user->user_id;
                $aggregate_rating = round( floatval( $user->aggregate_rating ), 2 );
                $review_count = intval( $user->review_count );
                
                // Display user avatar and rating
                echo '<li class="bupr-members">';
                if ( $avatar === 'Show' ) {
                    echo '<div class="bupr-img-widget">' . get_avatar( $user_id, 50 ) . '</div>';
                }
                echo '<div class="bupr-content-widget">';
                echo '<div class="bupr-member-title">' . bp_core_get_userlink( $user_id ) . '</div>'; // phpcs:ignore.
                echo '<div class="bupr-member-rating">';

                // Star rating visualization (Fixed 5 stars, consistent display)
                $stars_on = floor( $aggregate_rating );
                $stars_half = ( $aggregate_rating - $stars_on >= 0.5 ) ? 1 : 0;
                $stars_off = 5 - ( $stars_on + $stars_half );

                // Display stars
                for ( $i = 0; $i < $stars_on; $i++ ) {
                    echo '<span class="fas fa-star bupr-star-rate"></span>';
                }
                if ( $stars_half ) {
                    echo '<span class="fas fa-star-half-alt bupr-star-rate"></span>';
                }
                for ( $i = 0; $i < $stars_off; $i++ ) {
                    echo '<span class="far fa-star bupr-star-rate"></span>';
                }

                // Display review count and consistent rating format (e.g., 5/5 (1 review))
                echo '</div>';
                /* translators: %1$s: aggragate rating; %2$s: Review count; %3$s: Reviews. */
                echo '<span class="bupr-meta">' . sprintf( esc_html__( '%1$s/5 (%2$s %3$s)', 'bp-member-reviews' ), esc_html( $aggregate_rating ), esc_html( $review_count ), esc_html__( 'reviews', 'bp-member-reviews' ) ) . '</span>';
                echo '</div></li>';
            }

            echo '</ul>';
            echo $after_widget; // phpcs:ignore.
        } else {
            // No users found
            echo '<p>' . esc_html__( 'No members have been reviewed yet.', 'bp-member-reviews' ) . '</p>';
        }
    }

    /** Update widget */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['bupr_title'] = strip_tags( $new_instance['bupr_title'] );
        $instance['bupr_member'] = intval( $new_instance['bupr_member'] );
        $instance['top_member'] = strip_tags( $new_instance['top_member'] );
        $instance['avatar'] = strip_tags( $new_instance['avatar'] );
        return $instance;
    }

    /** Widget form */
    function form( $instance ) {
        $defaults = array(
            'bupr_title'  => esc_html__( 'Top Members', 'bp-member-reviews' ),
            'bupr_member' => 20,
            'top_member'  => 'top-rated',
            'avatar'      => 'Show',
        );
        $instance   = wp_parse_args( (array) $instance, $defaults );
        $title      = esc_attr( $instance['bupr_title'] );
        $member     = esc_attr( $instance['bupr_member'] );
        $topmembers = esc_attr( $instance['top_member'] );
        $avatar     = esc_attr( $instance['avatar'] );
        ?>
        <div class="bupr-widget-class">
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'bupr_title' ) ); ?>"><?php esc_html_e( 'Enter Title', 'bp-member-reviews' ); ?>:</label>
                <input class="regular_text" id="<?php echo esc_attr( $this->get_field_id( 'bupr_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bupr_title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'bupr_member' ) ); ?>"><?php esc_html_e( 'Display Members', 'bp-member-reviews' ); ?>:</label>
                <input class="regular_text" id="<?php echo esc_attr( $this->get_field_id( 'bupr_member' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'bupr_member' ) ); ?>" type="number" value="<?php echo esc_attr( $member ); ?>" />
            </p>

            <p>
                <span>
                    <input class="regular_text" id="<?php echo esc_attr( $this->get_field_id( 'top_rated' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_member' ) ); ?>" value="top-rated" type="radio" <?php checked( $topmembers, 'top-rated' ); ?> />
                    <label for="<?php echo esc_attr( $this->get_field_id( 'Top rated' ) ); ?>"><?php esc_html_e( 'Top Rated ', 'bp-member-reviews' ); ?>
                    </label>
                </span>
                <span>
                    <input class="regular_text" id="<?php echo esc_attr( $this->get_field_id( 'top_viewed' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'top_member' ) ); ?>" value="top-viewed" type="radio" <?php checked( $topmembers, 'top-viewed' ); ?> />
                    <label for="<?php echo esc_attr( $this->get_field_id( 'Top Viewed' ) ); ?>"><?php esc_html_e( 'Most Reviewed', 'bp-member-reviews' ); ?>
                    </label>
                </span>
            </p>

            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'avatar' ) ); ?>"><?php esc_html_e( 'Display Avatar ', 'bp-member-reviews' ); ?>
                </label>
                <?php
                $bupr_options = array( 'Show', 'Hide' );
                ?>
                <select id="<?php echo esc_attr( $this->get_field_id( 'avatar' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'avatar' ) ); ?>">
                    <?php
                    foreach ( $bupr_options as $bupr_option ) {
                        ?>
                        <option value="<?php echo esc_attr( $bupr_option ); ?>" <?php selected( $avatar, $bupr_option ); ?>><?php echo esc_html( $bupr_option ); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </p>
        </div>
        <?php
    }
}
