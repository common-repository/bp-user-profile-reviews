<?php
/**
 * BuddyPress Member Review admin function class file.
 *
 * @package BuddyPress_Member_Reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Add admin page for importing Review(s).
if ( ! class_exists( 'BUPR_Admin' ) ) {
	/**
	 * The admin-facing functionality of the plugin.
	 *
	 * @package    BuddyPress_Member_Reviews
	 * @author     wbcomdesigns <admin@wbcomdesigns.com>
	 */
	class BUPR_Admin {


		/**
		 * plugin_settings_tabs
		 *
		 * @since    1.0.0
		 * @access    public
		 * @var mixed   $plugin_settings_tabs
		 */
		public $plugin_settings_tabs;

		/**
		 * Constructor.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function __construct() {
		
			add_action( 'admin_menu', array( $this, 'bupr_add_submenu_page_admin_settings' ) );
			add_action( 'admin_menu', array( $this, 'bupr_get_review_count' ) );
			add_action( 'admin_init', array( $this, 'bupr_plugin_settings' ) );
			add_action( 'in_admin_header', array( $this, 'wbcom_hide_all_admin_notices_from_setting_page' ) );
			add_filter( 'pre_update_option_bupr_admin_settings', array( $this, 'bupr_update_admin_settings' ), 10, 2 );
			add_action( 'transition_post_status', array( $this, 'bupr_generate_activity_review_status_change' ), 10, 3 );
			/* Register custom post type review */
			$bupr_post_types = get_post_types();
			if ( ! in_array( 'review', $bupr_post_types, true ) ) {
				add_action( 'init', array( $this, 'bupr_review_cpt' ) );
				add_action( 'init', array( $this, 'bupr_review_taxonomy_cpt' ) );
			}

		}

		/**
		 * Hide all notices from the setting page.
		 *
		 * @return void
		 */
		public function wbcom_hide_all_admin_notices_from_setting_page() {
			$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'bp-member-review-settings' );
			$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

			if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
				remove_all_actions( 'admin_notices' );
				remove_all_actions( 'all_admin_notices' );
			}

		}

		/**
		 * Actions performed on loading admin_menu.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_update_admin_settings( $new_value, $old_value ) {
			if ( !empty( $new_value ) && array_key_exists( 'rating_field_name', $new_value ) ) {
				$rating_fields  = isset( $new_value['rating_field_name'] ) ? $new_value['rating_field_name'] : '';
				$rating_display = isset( $new_value['rating_field_name_display'] ) ? $new_value['rating_field_name_display'] : array();
				$criteria_arr   = array();
				if ( ! empty( $rating_fields ) && isset( $rating_fields ) ) {
					foreach ( $rating_fields as $key => $fname ) {
						// Check if $fname is not empty before adding it to the array
						if ( ! empty( trim( $fname ) ) ) {
							if ( array_key_exists( $key, $rating_display ) ) {
								$criteria_arr[ $fname ] = 'yes';
							} else {
								$criteria_arr[ $fname ] = 'no';
							}
						}
					}
					unset( $new_value['rating_field_name'] );
					unset( $new_value['rating_field_name_display'] );
					$new_value['profile_rating_fields'] = $criteria_arr;
				}
			}
			return $new_value;
		}		

		/**
		 * Actions performed on loading admin_menu.
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_add_submenu_page_admin_settings() {
			if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
				add_menu_page( esc_html__( 'WB Plugins', 'bp-member-reviews' ), esc_html__( 'WB Plugins', 'bp-member-reviews' ), 'manage_options', 'wbcomplugins', array( $this, 'bupr_admin_options_page' ), 'dashicons-lightbulb', 59 );
				add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'bp-member-reviews' ), esc_html__( 'General', 'bp-member-reviews' ), 'manage_options', 'wbcomplugins' );
			}
			add_submenu_page( 'wbcomplugins', esc_html__( 'Admin Settings For Reviews', 'bp-member-reviews' ), esc_html__( 'Member Reviews', 'bp-member-reviews' ), 'manage_options', 'bp-member-review-settings', array( $this, 'bupr_admin_options_page' ) );
		}

		/**
		 * Actions performed on loading plugin settings
		 *
		 * @since    1.0.9
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_plugin_settings() {
			$this->plugin_settings_tabs['bupr-welcome'] = esc_html__( 'Welcome', 'bp-member-reviews' );
			add_settings_section( 'bupr-welcome', ' ', array( $this, 'bupr_admin_welcome_content' ), 'bupr-welcome' );

			$this->plugin_settings_tabs['bupr-general'] = esc_html__( 'General', 'bp-member-reviews' );
			register_setting( 'bupr_admin_general_options', 'bupr_admin_general_options' );
			add_settings_section( 'bupr-general', ' ', array( $this, 'bupr_admin_general_content' ), 'bupr-general' );
			$this->plugin_settings_tabs['bupr-criteria'] = esc_html__( 'Criteria', 'bp-member-reviews' );
			register_setting( 'bupr_admin_settings', 'bupr_admin_settings' );
			add_settings_section( 'bupr-criteria', ' ', array( $this, 'bupr_admin_criteria_content' ), 'bupr-criteria' );
			$this->plugin_settings_tabs['bupr-shortcode'] = esc_html__( 'Shortcode', 'bp-member-reviews' );
			add_settings_section( 'bupr-shortcode', ' ', array( $this, 'bupr_admin_shortcode_content' ), 'bupr-shortcode' );
			$this->plugin_settings_tabs['bupr-display'] = esc_html__( 'Display', 'bp-member-reviews' );
			register_setting( 'bupr_admin_display_options', 'bupr_admin_display_options' );
			add_settings_section( 'bupr-display', ' ', array( $this, 'bupr_admin_display_content' ), 'bupr-display' );
		}

		public function bupr_admin_welcome_content() {
			include 'tab-templates/bupr-welcome-page.php';
		}
		public function bupr_admin_general_content() {
			include 'tab-templates/bupr-setting-general-tab.php';
		}

		public function bupr_admin_criteria_content() {
			include 'tab-templates/bupr-setting-criteria-tab.php';
		}

		public function bupr_admin_shortcode_content() {
			include 'tab-templates/bupr-setting-shortcode-tab.php';
		}

		public function bupr_admin_display_content() {
			include 'tab-templates/bupr-setting-display-tab.php';
		}

		/**
		 * Actions performed to create a submenu page content.
		 *
		 * @since    1.0.0
		 * @access public
		 */
		public function bupr_admin_options_page() {
			global $allowedposttags;
			$tab = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : 'bupr-welcome';
			?>
			<div class="wrap">
				<div class="wbcom-bb-plugins-offer-wrapper">
					<div id="wb_admin_logo">
						<a href="https://wbcomdesigns.com/downloads/buddypress-community-bundle/?utm_source=pluginoffernotice&utm_medium=community_banner" target="_blank">
							<img src="<?php echo esc_url( BUPR_PLUGIN_URL ) . 'admin/wbcom/assets/imgs/wbcom-offer-notice.png'; ?>">
						</a>
					</div>
				</div>
				<div class="wbcom-wrap">
					<div class="blpro-header">
						<div class="wbcom_admin_header-wrapper">
							<div id="wb_admin_plugin_name">
								<?php esc_html_e( 'BuddyPress Member Reviews', 'bp-member-reviews' ); ?>
								<span>
								<?php
								/* translators: %s: */
								printf( esc_html__( 'Version %s', 'bp-member-reviews' ), esc_attr( BUPR_PLUGIN_VERSION ) );
								?>
								</span>
							</div>
							<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
						</div>
					</div>
					<div class="wbcom-admin-settings-page">
						<?php
						settings_errors();
						$this->bupr_plugin_settings_tabs();
						settings_fields( $tab );
						do_settings_sections( $tab );
						?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Actions performed to create tabs on the sub menu page.
		 *
		 * @since    1.0.0
		 * @access public
		 */
		public function bupr_plugin_settings_tabs() {
			$current_tab = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : 'bupr-welcome';
			// xprofile setup tab.
			echo '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab === $tab_key ? 'nav-tab-active' : '';
				echo '<li class="' . esc_attr( $tab_key ) . '"><a class="nav-tab ' . esc_attr( $active ) . '" id="' . esc_attr( $tab_key ) . '-tab" href="?page=bp-member-review-settings' . '&tab=' . esc_attr( $tab_key ) . '">' . esc_attr( $tab_caption ) . '</a></li>';
			}
			echo '</div></ul></div>';
		}

		/**
		 * Actions performed to create Review cpt
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_review_cpt() {
			$labels = array(
				'name'               => esc_html__( 'Reviews', 'bp-member-reviews' ),
				'singular_name'      => esc_html__( 'Review', 'bp-member-reviews' ),
				'menu_name'          => esc_html__( 'Reviews', 'bp-member-reviews' ),
				'name_admin_bar'     => esc_html__( 'Reviews', 'bp-member-reviews' ),
				'add_new'            => esc_html__( 'Add New Review', 'bp-member-reviews' ),
				'add_new_item'       => esc_html__( 'Add New Review', 'bp-member-reviews' ),
				'new_item'           => esc_html__( 'New Review', 'bp-member-reviews' ),
				'view_item'          => esc_html__( 'View Reviews', 'bp-member-reviews' ),
				'all_items'          => esc_html__( 'All Reviews', 'bp-member-reviews' ),
				'search_items'       => esc_html__( 'Search Reviews', 'bp-member-reviews' ),
				'parent_item_colon'  => esc_html__( 'Parent Review:', 'bp-member-reviews' ),
				'not_found'          => esc_html__( 'No Review Found', 'bp-member-reviews' ),
				'not_found_in_trash' => esc_html__( 'No Review Found In Trash', 'bp-member-reviews' ),
			);
			$args   = array(
				'labels'             => $labels,
				'public'             => true,
				'menu_icon'          => 'dashicons-testimonial',
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array(
					'slug'       => 'review',
					'with_front' => false,
				),
				'capability_type'    => 'post',
				'capabilities'       => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'       => true,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor', 'author' ),
			);
			register_post_type( 'review', $args );

		}

		/**
		 * [bupr_get_review_count description] Function count
		 *
		 * @return [type] [Count on Review menu item ]
		 */
		public function bupr_get_review_count() {
			global $bupr, $menu;
			if ( 'yes' !== $bupr['auto_approve_reviews'] ) {

				foreach ( $menu as $each_menu ) {
					if ( $each_menu[2] == 'edit.php?post_type=review' ) {
						$count = wp_count_posts( 'review' );
						if ( $count ) {
							$count = $count->draft;

							$key = $this->bupr_recursive_array_search( 'edit.php?post_type=review', $menu );

							if ( ! $key ) {
								return;
							}

							$menu[ $key ][0] .= sprintf(
								'<span class="awaiting-mod update-plugins count-%1$s"><span class="plugin-count">%1$s</span></span>',
								$count
							);
						}
					}
				}
			}
		}

		/**
		 * [bupr_recursive_array_search description]
		 *
		 * @param  [sting] $needle
		 * @param  [array] $haystack
		 * @return [number]  [Return array key.]
		 */
		public function bupr_recursive_array_search( $needle, $haystack ) {
			foreach ( $haystack as $key => $value ) {
				$current_key = $key;
				if (
					$needle === $value
					or (
				is_array( $value )
				&& $this->bupr_recursive_array_search( $needle, $value ) !== false
					)
				) {
					return $current_key;
				}
			}
			return false;
		}


		/**
		 * Actions performed to create Review cpt taxonomy
		 *
		 * @since    1.0.0
		 * @access   public
		 * @author   Wbcom Designs
		 */
		public function bupr_review_taxonomy_cpt() {
			$category_labels = array(
				'name'              => esc_html_x( 'Reviews Category', 'taxonomy general name', 'bp-member-reviews' ),
				'singular_name'     => esc_html_x( 'Review Category', 'taxonomy singular name', 'bp-member-reviews' ),
				'search_items'      => esc_html__( 'Search Categories', 'bp-member-reviews' ),
				'all_items'         => esc_html__( 'All Categories', 'bp-member-reviews' ),
				'parent_item'       => esc_html__( 'Parent Category', 'bp-member-reviews' ),
				'parent_item_colon' => esc_html__( 'Parent Category:', 'bp-member-reviews' ),
				'edit_item'         => esc_html__( 'Edit Category', 'bp-member-reviews' ),
				'update_item'       => esc_html__( 'Update Category', 'bp-member-reviews' ),
				'add_new_item'      => esc_html__( 'Add Category', 'bp-member-reviews' ),
				'new_item_name'     => esc_html__( 'New Category Name', 'bp-member-reviews' ),
				'menu_name'         => esc_html__( 'Category', 'bp-member-reviews' ),
			);
			$category_args   = array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => false,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'review_category' ),
			);
			register_taxonomy( 'review_category', array( 'review' ), $category_args );
		}




		public function bupr_generate_activity_review_status_change( $new_status, $old_status, $post ) {
			global $bupr;
			$bupr_member_id     = get_post_meta( $post->ID, 'linked_bp_member', true );
			if( '' !== $bupr_member_id){
				if ( ( 'publish' === $new_status && 'draft' === $old_status ) && 'review' === $post->post_type ) {
					
					do_action( 'bupr_member_review_after_review_insert', $post->ID, $bupr_member_id );
					do_action( 'bupr_sent_review_notification', $bupr_member_id, $post->ID );
	
					$bupr_admin_general  = get_option( 'bupr_admin_general_options' );
					$site_name           = get_bloginfo( 'name' );
					$site_admins         = get_users( array( 'role' => 'administrator' ) );
					$anonymous_review 	 = get_post_meta( $post->ID, 'bupr_anonymous_review_post', true );
					$bupr_sender_data    = get_userdata($post->post_author);
					$bupr_sender_email   = $bupr_sender_data->data->user_email;
					$bupr_reciever_data  = get_userdata($bupr_member_id);
					$bupr_reciever_email = $bupr_reciever_data->data->user_email;
					$bupr_reciever_name  = $bupr_reciever_data->data->user_nicename;
					$bupr_reciever_login = $bupr_reciever_data->data->user_login;
					if( isset( $anonymous_review ) && $anonymous_review == 'yes' ) {
						$user_name      = "An anonymous user";
					} else {
						$user_name      = $bupr_sender_data->display_name;
					}
					$review_subject = $bupr_reciever_name . " received a " . bupr_profile_review_tab_singular_slug();
					
					// Check BuddyPress version and use the appropriate function
					// Check if the bp_members_get_user_url() function exists (introduced in BuddyPress 12.0.0).
					if ( function_exists( 'bp_members_get_user_url' ) ) {
						// Use bp_members_get_user_url() if it exists (for BuddyPress v12.0.0 and above).
						$bupr_review_url = bp_members_get_user_url( $bupr_member_id ) . strtolower( $bupr['review_label_plural'] ) . '/view/' .  $post->ID ;
					} else {
						// Fall back to bp_core_get_user_domain() for older versions of BuddyPress.
						$bupr_review_url = bp_core_get_user_domain( $bupr_member_id ) . strtolower( $bupr['review_label_plural'] ) . '/view/' .  $post->ID ;
					}
					$bupr_to = $bupr_reciever_email;
					// Define a default value for $bupr_subject with translation support
					$bupr_subject = __('New Review on Your Profile at [site-name]', 'bp-member-reviews');
					$review_link = '<a href="' . $bupr_review_url . '">' . $review_subject . '</a>';
					// Check if a custom subject line is set in the settings and update $bupr_subject accordingly
					if (isset($bupr_admin_general['review_email_subject']) && !empty($bupr_admin_general['review_email_subject'])) {
						$bupr_subject = $bupr_admin_general['review_email_subject'];
					}
	
					// Replace '[site-name]' placeholder in the subject with the actual site name
					$bupr_subject = str_replace('[site-name]', $site_name, $bupr_subject);
	
					$message = 'Hello [user-name], <br><br>We are pleased to inform you that [reviewer-name] has recently reviewed your profile.<br><br>To view the review, simply click on the link below:<br>[review-link]<br><br>Best regards,<br>The [site-name] Team';
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
			
		}
		
	}
	new BUPR_Admin();
}