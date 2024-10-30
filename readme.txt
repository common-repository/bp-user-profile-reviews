=== Wbcom Designs - BuddyPress Member Reviews ===
Contributors: wbcomdesigns, vapvarun
Donate link: https://wbcomdesigns.com/donate/
Tags: buddypress, members
Requires at least: 4.0
Tested up to: 6.6.2
Stable tag: 3.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The BuddyPress Member Reviews plugin enhances the Buddypress community by empowering registered users to post reviews on other members' profiles. This feature is exclusive to registered members, preventing users from reviewing their profiles to ensure unbiased feedback. The plugin provides guests a view-only mode, where they can read reviews but not contribute.

This plugin offers a customizable review form that supports multiple criteria, each rated up to 5 points. Administrators can add or modify these criteria and adjust their sequence to suit the community's needs. The review form seamlessly integrates into a member's profile page for easy access and can also be displayed on other pages using a shortcode, providing versatility in placement.

The BuddyPress Member Reviews plugin introduces a widget to showcase top-rated members. This encourages quality contributions within the community and helps foster a competitive and engaging environment.

We've designed this plugin with reliability, scalability, security, and flexibility, ensuring a user-friendly experience that can grow and adapt to your community. Your feedback is invaluable to us, and we welcome any suggestions to enhance this plugin further.

[vimeo https://vimeo.com/556813504]

= Links =

- [Plugin URL](https://wbcomdesigns.com/downloads/buddypress-member-reviews/) - Learn more about the BuddyPress Member Reviews plugin.

=== THEME - WORDPRESS THEME WITH OUTSTANDING BUDDYPRESS SUPPORT ===

- [FREE BuddyPress Theme: BuddyX](https://wordpress.org/themes/buddyx/) - A clean and customizable theme to enhance your BuddyPress community experience.

#### OUR ADD-ON PLUGINS TO ENHANCE YOUR BUDDYPRESS OR BUDDYBOSS PLATFORM COMMUNITY

Enhance your community with these free add-ons by Wbcom Designs:

- **[BuddyPress Member Reviews](https://wordpress.org/plugins/bp-user-profile-reviews/)** - Enable member-to-member ratings and feedback.
- **[BuddyPress Group Reviews](https://wordpress.org/plugins/review-buddypress-groups/)** - Allow group ratings and feedback.
- **[BuddyPress Activity Social Share](https://wordpress.org/plugins/bp-activity-social-share/)** - Facilitate sharing of activities on social networks.
- **[Private Community with BP Lock](https://wordpress.org/plugins/lock-my-bp/)** - Make your community private, with selective public access.
- **[BuddyPress Job Manager](https://wordpress.org/plugins/bp-job-manager/)** - Integrate job listings into your community.
- **[Check-ins for BuddyPress Activity](https://wordpress.org/plugins/bp-check-in/)** - Allow members to share their location.
- **[BuddyPress Favorite Notification](https://wordpress.org/plugins/bp-favorite-notification/)** - Notify members of likes and favorites on their activities.
- **[Shortcodes & Elementor Widgets For BuddyPress](https://wordpress.org/plugins/shortcodes-for-buddypress/)** - Add activity and directory listings with shortcodes and widgets.

Discover premium add-ons to customize your community further:

- **[BuddyPress Hashtags](https://wbcomdesigns.com/downloads/buddypress-hashtags/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Polls](https://wbcomdesigns.com/downloads/buddypress-polls/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Quotes](https://wbcomdesigns.com/downloads/buddypress-quotes/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Status & Reaction](https://wbcomdesigns.com/downloads/buddypress-status/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Profanity](https://wbcomdesigns.com/downloads/buddypress-profanity/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Sticky Post](https://wbcomdesigns.com/downloads/buddypress-sticky-post/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[BuddyPress Auto Friends](https://wbcomdesigns.com/downloads/buddypress-auto-friends/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**
- **[Shortcodes & Elementor Widgets For BuddyPress Pro](https://wordpress.org/plugins/shortcodes-for-buddypress/?utm_source=wp.org&utm_medium=plugins&utm_campaign=wp.org)**

= EXPERT SUPPORT =

Need help? Contact us at [Wbcom Designs Support](https://wbcomdesigns.com/support/).

== Installation ==

1. Upload the "buddypress-member-review" folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= Where do I get support? =

Please visit [Wbcom Designs](https://wbcomdesigns.com/contact/) for support. We're here to help.

= How can we submit a review to a member profile? =

Navigate to the "/members" section and select a profile. Click on the "Reviews" menu to add a review.

= How can we add more rating criteria? =

Go to "Dashboard->Review->BP member review setting page", find the Criteria tab, add your criteria, and save.

= How can I delete or edit Criteria? =

Under the Criteria tab in the plugin settings, you'll find options to delete, enable/disable, or rearrange criteria.

= What is the Top Members widget, and how do I use it? =

This widget displays top-rated members. Find it in the widgets section after activating the plugin.

== Screenshots ==

1. Member Reviews at member directory.(png|jpg|jpeg|gif).
2. Review display at member profile.(png|jpg|jpeg|gif).
3. Review submission at member profile.(png|jpg|jpeg|gif).
4. Plugin settings- General Options.(png|jpg|jpeg|gif).
5. Plugin settings- Review Criteria.(png|jpg|jpeg|gif).
6. Plugin Option to shortcode.(png|jpg|jpeg|gif).
7. Plugin Option to display.(png|jpg|jpeg|gif).


== Changelog ==
= 3.3.1 =
* Fixed: Multi-user role issue for accepting and writing reviews.
* Managed: Header action button for BuddyBoss theme.
* Fixed: Activity actions with Youzify.
* Managed: UI issues with Youzify.
* Fixed: No email approval mail sent to admin with BuddyBoss and Youzify.
* Fixed: Background color issues specific to themes.
* Managed: Activity review UI and removed extra `bupr-front.css` file.
* Added: Youzify support.
* Added: Member star rating in activity content.
* Removed: `add_review_member_form` shortcode-related functionality.
* Removed: Unused HTML related to `bupr_review_filter`.
* Removed: Unused functions in the plugin.
* Fixed: Review submission issue when BuddyPress components are disabled.
* Fixed: PHP warnings while approving group reviews.
* Resolved: Issue with reviews not displaying in specific conditions on the member's directory.
* Resolved: Issue with average ratings on the member’s profile when the 'allow multiple criteria' option is disabled.
* Fixed: Single rating edit and save issues.
* Fixed: Fatal error when disabling multiple criteria or removing all fields.

= 3.3.0 =
* Fixes: Resolved avatar and user URL link issue for anonymous reviews.
* Fixes: Corrected shortcode form issue in BuddyBoss.
* Fixes: Adjusted pagination for reviews and notifications.
* Fixes: Addressed review approval notification issue in admin.
* Fixes: Properly reset review form after submission.
* Fixes: Corrected issue where reviews could be submitted even in draft status.
* Fixes: Resolved issue with user name displaying incorrectly in review titles.
* Fixes: Deprecated warning removed and PHP notices addressed.
* Improvements: Managed responsive UI for the "Add Review" button on iPad and other mobile views.
* Improvements: Managed notification display for review count in the WordPress admin bar.
* Improvements: Added multisite compatibility with proper plugin activation/deactivation checks.
* Improvements: Enhanced review tab UI for mobile and tablet views.
* Improvements: Security enhancements, including field validation and review form handling.
* Improvements: Refined review calculation for individual users.
* Improvements: Ensured consistent behavior for review activity display settings.
* Improvements: Code refactoring for better performance and security.
* Updates: Youzify integration added for displaying reviews on user profile headers.
* Updates: Included settings and functionality for review activity options.
* Updates: Standardized button classes for consistent UI design across BuddyPress, BuddyBoss, and Youzify.
* Updates: Managed tooltip alignment and button styling for different themes (BuddyPress Legacy, BuddyPress Nouveau, and BuddyBoss).
* Updates: Refined template loading for better GamiPress integration with additional security measures.
* Updates: PHPCS fixes to maintain code quality and coding standards.

= 3.2.0 =
* Fix: Resolved an issue where text statuses were not being saved (#171).
* Fix: Corrected the mobile UI timeline reaction issue for a smoother user experience (#170).
* Fix: Addressed the reaction reload issue to ensure consistent functionality.
* Enhancement: Added an undo reaction option to improve user interaction.
* Fix: Fixed the status icon issue for better visual representation (#167).

= 3.1.1 =
* Fix: (#229)Fixed email subject not save
* Fix: (#230)Fixed user name is not display on dropdown on shortcode
* Fix: Compatibility check with WordPress 6.5.0

= 3.1.0 =
* Fix: (#227)Fixed add-review-button-on-member-header
* Fix: Fixed notification issue
* Fix: (#228) Fixed issue-with-group-review

= 3.0.0 =
* Fix: BuddyPress v12 Fixes
* Fix: Updated screenshots

= 2.9.3 =
* Fix: PHPCS Fixes

= 2.9.2 =
* Fix: (#220) Issue with php 8.2

= 2.9.1 =
* Fix: Plural review string. 
* Fix: Added hook to render review form.
* Fix: (#209)Review date issue
* Fix: (#205)Rating issue in member directory with BB
* Fix: (#204)Undefined variable warning

= 2.9.0 =
* Enhancement: #199 - Gamipress Integration
* Fix: (#202) Fixed anonymous review notification issue
* Fix: (#193) Fixed review count not visible on the member profile tab
* Fix: Added missing preview image

= 2.8.3 =
* Fix: changes for translation issue
* Fix: (#189) fixed image does not show properly in buddy boss
* Fix: (#187) fixed admin notice issue

= 2.8.1 =
* Fix: Updated Admin Wrapper

= 2.8.0 =
* Fix: Fixed string translations
* Fix: BuddyBoss Support Managed
* Fix: (#176) Fixed notification link issue when bp rewrites plugin activated
* Fix: (#175) Fixed edit button is not displaying after filtering the review

= 2.7.0 =
* Fix: Fixed phpcs error
* Fix: Removed install plugin button from wrapper
* Fix: Email string changed with small latters
* Fix: Added hookable position after review submit
* Fix: (#174) Fixed review tab redirection issue to login user profile

= 2.6.1 =
* Fix: Fixed member header add review button redirects to 404 page

= 2.6.0 =
* Fix: (#170) added common space between rating stars
* Fix: (#160 )Fixed shortcode attribute is not working
* Fix: (#155) Fixed member avatar changed on rating filter of widget
* Fix: (#168) Update review content UI
* Fix: Added admin option to control update review functionality
* Fix: (#167) Fixed any one can update review
* Fix: (#166) Fixed edit functionality not working if starts are not change
* Fix: (#164) Managed edit review pop UI with kleo
* Fix: (#157) Fixed email string translation not working
* Fix: (#120) Fixed member directory review not showing with buddyboss
* Fix: (#154) Update success message color
* Fix: (#145) Fixed replace plural string with singular tab name
* Fix: (#155) Fixed user avatar not showing
* Fix: (#161) Fixed remove review cpt featurde image support
* Fix: (#139) Managed edit review model UI
* Fix: (#163) Managed UI with olympus theme
* Fix: (#162) Update custom input slider UI
* Fix: (#101) Fixed review notification url go to 404 page
* Fix: Remove Siteurl filter from plugin file and set in function file
* Fix: Update Member criteria field name with WPML Plugin
* Fix: Fixed admin-ajax.php url issue
* Fix: Fixed #158 - Add review(while translating) redirect to 404 page

= 2.5.0 =
* Fix: Update plugin backend UI
* Fix: (#146) Fixed member can review themselfs
* Fix: Update tab label and slug
* Fix: (#143) Fixed errors and warnings
* Fix: (#141) Fixed plural string with singular
* Fix: Review form issue fixed
* Fix: Fixed PHPCS issues

= 2.4.1 =
* Fix: New reviews tab should follow the same slug as parent.
* Fix: BP nav bar link will also use the dynamic review slug from options.

= 2.4.0 =
* Fix: ( #123) Allow to change review url when label is changed
* Fix: (#122) Fixed escaping functions
* Fix: (#110) Updated Language file and changed the strings
* Fix: (#103) Fixed hide review button in backend settings

= 2.3.0 =
* Fix: (#76) Fixed php notices and warnings
* Fix: (#74) Fixed review action is showing in notification
* Fix: #72)Changed review date format
* Fix: (#73) Fixed blank bbpress notifications

= 2.2.0 =
* Fix: Remove redclare function.
* Fix: (#64) updated message string on bad review.
* Fix: (#57) Fix admin does not get notification to approve the review.
* Fix: (#63) Fix filter tabs are not working.
* Fix: (#62) Fix Anonymous Review

= 2.1.0 =
* Fix - (#59) Update style
* Fix - (#53) Member ratings and Top Members ratings UI managed
* Fix - Added rating widget.
* Fix - (#52)-Date (when the review is posted) issue.

= 2.0.1 =
* Fix - Compatibility with BuddyPress 4.3.0. #48
* Fix - Fixed file not found error.

= 2.0.0 =
* Fix - Compatibility with BuddyPress 4.1.0. #43
* Fix - BuddyPress notification issue.
* Fix - Fixed Translation string issue.
* Enhancement - Improve Backend UI where you can manage all wbcom plugin's settings at one place.
* Enhancement - Added plural label for Review.
* Enhancement - Added shortcode for display top reviews on page.
* Enhancement - Added French translation files – credits to Jean Pierre Michaud

= 1.0.8 =
* Fix - Undefined variable issue in js code.

= 1.0.7 =
* Enhancement - Added admin setting to enable/disable review listing at member directory.
* Enhancement - Added admin setting to enable/disable View Review link at member directory.
* Enhancement - Added option for sending reviews anonymously with admin enable/disable setting.
* Fix - Ajax response issue while saving admin settings under General setting tab.

= 1.0.6 =
* Fix - Admin Styles css and js files are not applied

= 1.0.5 =
* Enhancement - Code Quality Improvment with WPCS
* Fix - 404 errors when BP member page slug is different

= 1.0.4 =
* Enhancement - Add support for member directory
* Enhancement - Added Google Rating Snippet meta tags
* Enhancement - Simplified rating view on single member profile
* Fix - Removed JS tab for add review and added real sub nav to add reviews

= 1.0.3 =
* Fix - Review header cleanup

= 1.0.2 =
* Fix - Added validation for shortcode review form and member review form for both condition with & without criteria fields.
* Fix - Remove number on hover of bar rating in member's header section & in plugin widget.
* Fix - UI improvement in plugin admin settings.
* Enhancement - Added functionality of multiple review.

= 1.0.1 =
* Layout Improvement
* Added Auto approve reviews options
* Added Email Notification options
* Added BuddyPress Notifcation options
* Added Exclude Members for review options
* Removed option to rate yourself

== Upgrade Notice ==
= 1.0.0 =
This version is the initial version of the plugin with basic review adding functionality.
