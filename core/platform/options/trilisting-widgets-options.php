<?php
/**
 * ReduxFramework
 * For full documentation, please visit: http://docs.reduxframework.com/
 */

if ( ! class_exists( 'Redux' ) ) {
	return;
}

// This is your option name where all the Redux data is stored.
$opt_name = TRILISTING\Trilisting_Info::OPTION_NAME;

// This line is only for altering the demo. Can be easily removed.
$opt_name = apply_filters( 'trilisting_widgets_redux_option/opt_name', $opt_name );

// init extensions
Redux::setExtensions( $opt_name, plugin_dir_path( dirname( __FILE__ ) ) . 'options/extensions' );

/**
 * ---> SET ARGUMENTS
 * All the possible arguments for Redux.
 * For full documentation on arguments, please refer to:
 * https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'options/trilisting-widgets-helper-function.php';

$theme = wp_get_theme(); // For use with some settings. Not necessary.

$args = array(
	// TYPICAL -> Change these values as you need/desire
	'opt_name'              => $opt_name,
	// This is where your data is stored in the database and also becomes your global variable name.
	'display_name'          => $theme->get( 'Name' ),
	// Name that appears at the top of your panel
	'display_version'       => $theme->get( 'Version' ),
	// Version that appears at the top of your panel
	'menu_type'             => 'submenu', //'menu',
	//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
	'page_parent'           => 'trilisting',
	'page_title'            => esc_html__( 'triListing Options', 'trilisting' ),
	'menu_title'            => esc_html__( 'Options', 'trilisting' ),
	'page_permissions'      => 'edit_theme_options',
	// Permissions needed to access the options panel.
	'page_slug'             => 'trilisting_options',
	// Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
	'allow_sub_menu'        => false,
	// Show the sections below the admin menu item or not
	'templates_path'        => plugin_dir_path( dirname( __FILE__ ) ) . 'options/templates/panel/',
	// Declare panel templates

	// You will need to generate a Google API key to use this feature.
	// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
	'google_api_key'        => '',
	// Set it you want google fonts to update weekly. A google_api_key value is required.
	'google_update_weekly'  => true,
	// Must be defined to add google fonts to the typography widget
	'async_typography'      => false,
	// Use a asynchronous font on the front end or font string
	//'disable_google_fonts_link' => true, // Disable this in case you want to create your own google fonts loader
	'admin_bar'             => false,
	// Show the panel pages on the admin bar
	'admin_bar_icon'        => 'dashicons-portfolio',
	// Choose an icon for the admin bar menu
	'admin_bar_priority'    => 50,
	// Choose an priority for the admin bar menu
	'global_variable'       => '',
	// Set a different name for your global variable other than the opt_name
	'dev_mode'              => false,
	// Show the time the page took to load, etc
	'update_notice'	        => true,
	// If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
	'customizer'            => true,
	// Enable basic customizer support
	//'open_expanded'		 => true, // Allow you to start the panel in an expanded way initially.
	//'disable_save_warn' => true, // Disable the save warning when a user changes a field

	// OPTIONAL -> Give you extra features
	'page_priority'	        => null,
	// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
	//'page_parent'			=> 'themes.php',
	// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	'menu_icon'             => '',
	// Specify a custom URL to an icon
	'last_tab'              => 0,
	// Force your panel to always open to a specific tab (by id)
	'page_icon'	            => 'icon-themes',
	// Icon displayed in the admin panel next to your menu_title

	'save_defaults'         => true,
	// On load save the defaults to DB before user clicks save or not
	'default_show'          => false,
	// If true, shows the default value next to each field that is not the default value.
	'default_mark'          => '',
	// What to print by the field's title if the value shown is default. Suggested: *
	'show_import_export'    => true,
	// Shows the Import/Export panel when not used as a field.

	// CAREFUL -> These options are for advanced use only
	'transient_time'        => 60 * MINUTE_IN_SECONDS,
	'output'                => true,
	// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
	'output_tag'            => true,
	// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
	// 'footer_credit' => '', // Disable the footer credit of Redux. Please leave if you can help it.

	// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
	'database'              => '',
	// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
	'use_cdn'               => true,
	// If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.
	'disable_tracking'      => true,
);

Redux::setArgs( $opt_name, $args );
/*
 * ---> END ARGUMENTS
 */

//generate options
$post_types_opt   = trilisting_enable_post_types();
$acf_group_fields = trilisting_get_group_post_fields();
$post_types_opt[] = 'post';

// -> START post options
$list_post_types = $list_maps_meta = $general_options = [];
$widget_blog_1 = $widget_standard_1 = $widget_thumbnail_1 = [];

$list_post_types[] = array(
	'id'       => 'allow_publish_posts',
	'type'     => 'switch',
	'title'    => esc_html__( 'Allow registered users to publish posts without moderation', 'trilisting' ),
	'default'  => false,
);

$list_post_types[] = array(
	'id'       => 'enable_publish_submitted_listing',
	'type'     => 'switch',
	'title'    => esc_html__( 'Allow users to edit own published listing', 'trilisting' ),
	'default'  => true,
);

$list_post_types[] = array(
	'id'       => 'enable_edited_listing',
	'type'     => 'switch',
	'title'    => esc_html__( 'Allow users to edit own pending listing', 'trilisting' ),
	'default'  => true,
);

$list_post_types[] = array(
	'id'       => 'enable_saved_listing',
	'type'     => 'switch',
	'title'    => esc_html__( 'Saving Listing', 'trilisting' ),
	'subtitle' => esc_html__( 'Allows users to save listing.', 'trilisting' ),
	'default'  => true,
);

$list_post_types[] = array(
	'id'       => 'enable_gallery_listing',
	'type'     => 'switch',
	'title'    => esc_html__( 'Enable Gallery', 'trilisting' ),
	'subtitle' => esc_html__( 'Add gallery in submit form.', 'trilisting' ),
	'desc'     => esc_html__( 'To display the gallery in listing, ', 'trilisting' ) . '<a href="https://trilisting.com/trilisting-plugin-documentation/#trilisting-docs-title-b9ad1a4" target="_blank">' . esc_html__( 'follow instructions.', 'trilisting' ) . '</a>',
	'default'  => true,
);

// Maps
$list_maps_meta[] = array(
	'id'       => 'google_maps_api_key',
	'type'     => 'text',
	'title'    => esc_html__( 'Google Maps API Key', 'trilisting' ),
	'desc'     => __( 'This is a requirement to use Google Maps, you can get key ', 'trilisting' ) . '<a target="_blank" href="https://console.developers.google.com/flows/enableapi?apiid=static_maps_backend,street_view_image_backend,maps_embed_backend,places_backend,geocoding_backend,directions_backend,distance_matrix_backend,geolocation,elevation_backend,timezone_backend,maps_backend&keyType=CLIENT_SIDE&reusekey=true">' . esc_html__( 'here', 'trilisting' ) . '</a>',
);

$list_maps_meta[] = array(
	'id'            => 'maps_marker_section_begin',
	'type'          => 'section',
	'title'         => esc_html__( 'Map Markers', 'trilisting' ),
	'indent'        => true,
);

$list_maps_meta[] = array(
	'id'            => 'maps_default_presets_markers',
	'type'          => 'image_select',
	'title'         => esc_html__( 'Default Marker', 'trilisting' ),
	'options'       => array(
		'1' => array(
			'alt' => 'default-1',
			'img' => TRILISTING_ASSETS_URL . 'img/markers/marker-default-1.png',
		),
		'2' => array(
			'alt' => 'default-2',
			'img' => TRILISTING_ASSETS_URL . 'img/markers/marker-default-2.png',
		),
	),
	'default'       => '1',
);

$list_maps_meta[] = array(
	'id'       => 'custom_default_marker_image',
	'type'     => 'media',
	'title'    => esc_html__( 'Custom Default Marker', 'trilisting-places' ),
);

$list_maps_meta[] = array(
	'id'            => 'maps_sticky_presets_markers',
	'type'          => 'image_select',
	'title'         => esc_html__( 'Sticky Marker', 'trilisting' ),
	'options'       => array(
		'1' => array(
			'alt' => 'featured-1',
			'img' => TRILISTING_ASSETS_URL . 'img//markers/marker-featured-1.png',
		),
		'2' => array(
			'alt' => 'featured-2',
			'img' => TRILISTING_ASSETS_URL . 'img//markers/marker-featured-2.png',
		),
	),
	'default'       => '1',
);

$list_maps_meta[] = array(
	'id'       => 'custom_sticky_marker_image',
	'type'     => 'media',
	'title'    => esc_html__( 'Custom Sticky Marker', 'trilisting-places' ),
);

$list_maps_meta[] = array(
	'id'       => 'maps_marker_section_end',
	'type'     => 'section',
	'indent'   => false,
);

// Blog
$widget_blog_1[] = array(
	'id'            => 'widget_blog_1_title_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Title Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 80,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 80,
	'display_value' => 'text',
);

$widget_blog_1[] = array(
	'id'            => 'widget_blog_1_excerpt_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Excerpt Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 115,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 250,
	'display_value' => 'text',
);

// Standard
$widget_standard_1[] = array(
	'id'            => 'widget_standard_1_title_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Title Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 74,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 100,
	'display_value' => 'text',
);

$widget_standard_1[] = array(
	'id'            => 'widget_standard_1_excerpt_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Excerpt Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 190,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 520,
	'display_value' => 'text',
);

// Thumbnail
$widget_thumbnail_1[] = array(
	'id'            => 'widget_thumbnail_1_title_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Title Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 75,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 80,
	'display_value' => 'text',
);

// Map
$widget_map_1[] = array(
	'id'            => 'widget_maps_1_title_length',
	'type'          => 'slider',
	'title'         => esc_html__( 'Title Length', 'trilisting' ),
	'subtitle'      => esc_html__( 'Limit number of characters to display.', 'trilisting' ),
	'default'       => 80,
	'mix'           => 1,
	'step'          => 1,
	'max'           => 80,
	'display_value' => 'text',
);

// General
$general_options[] = array(
	'id'       => 'hidden_wpadminbar',
	'type'     => 'switch',
	'title'    => esc_html__( 'Hide Admin Panel', 'trilisting' ),
	'subtitle' => esc_html__( 'Hide WordPress admin panel for your users while they are logged in.', 'trilisting' ),
	'on'       => esc_html__( 'Yes', 'trilisting' ),
	'off'      => esc_html__( 'No', 'trilisting' ),
	'default'  => true,
);

$general_options[] = array(
	'id'       => 'post_type_rating_comments',
	'type'     => 'select',
	'multi'    => true,
	'title'    => esc_html__( 'Enable Reviews', 'trilisting' ),
	'subtitle' => esc_html__( 'Enable reviews for selected post types.', 'trilisting' ),
	'data'     => 'post_types',
	'default'  => array( 'trilisting_places' ),
);

$general_options[] = array(
	'id'       => 'dashboard_page_theme',
	'type'     => 'select',
	'title'    => esc_html__( 'Dashboard Page', 'trilisting' ),
	'data'     => 'pages',
);

$general_options[] = array(
	'id'       => 'submit_listing_page_theme',
	'type'     => 'select',
	'title'    => esc_html__( 'Submit Listing Page', 'trilisting' ),
	'data'     => 'pages',
);

if ( isset( $post_types_opt ) && ! empty( $post_types_opt ) ) {

	foreach ( $post_types_opt as $post_type ) {
		$post_type_name = str_replace( '_', ' ', $post_type );

		if ( 'post' !== $post_type ) {
			$list_post_types[] = array(
				'id'       => $post_type . '_meta',
				'type'     => 'checkbox',
				'title'    => esc_attr( ucwords( $post_type_name ) ) . esc_html__( ' Meta Data', 'trilisting' ),
				'subtitle' => esc_html__( 'What information should be hidden.', 'trilisting' ),
				'options'  => TRILISTING\trilisting_get_post_options( $acf_group_fields, false, $post_type ),
				'default'  => TRILISTING\trilisting_get_post_options( $acf_group_fields, true, $post_type ),
			);

			$widget_map_1[] = array(
				'id'       => 'widget_maps_1_meta_' . $post_type,
				'type'     => 'checkbox',
				'title'    => esc_attr( ucwords( $post_type_name ) ) . esc_html__( ' Meta Data', 'trilisting' ),
				'subtitle' => esc_html__( 'What information should be displayed in listing on map.', 'trilisting' ),
				'options'  => TRILISTING\trilisting_get_widget_meta_opts( $acf_group_fields, [], $post_type ),
			);

			$general_options[] = array(
				'id'       => $post_type . 'search_page_theme',
				'type'     => 'select',
				'title'    => esc_html__( 'Search Page - ', 'trilisting' ) . $post_type,
				'data'     => 'pages',
			);
		}

		$widget_blog_1[] = array(
			'id'       => 'widget_blog_1_meta_' . $post_type,
			'type'     => 'checkbox',
			'title'    => esc_attr( ucwords( $post_type_name ) ) . esc_html__( ' Meta Data', 'trilisting' ),
			'subtitle' => esc_html__( 'What information should be displayed in widget.', 'trilisting' ),
			'options'  => TRILISTING\trilisting_get_widget_meta_opts( $acf_group_fields, [], $post_type ),
		);

		$widget_standard_1[] = array(
			'id'       => 'widget_standard_1_meta_' . $post_type,
			'type'     => 'checkbox',
			'title'    => esc_attr( ucwords( $post_type_name ) ) . esc_html__( ' Meta Data', 'trilisting' ),
			'subtitle' => esc_html__( 'What information should be displayed in widget.', 'trilisting' ),
			'options'  => TRILISTING\trilisting_get_widget_meta_opts( $acf_group_fields, [], $post_type ),
		);

		$widget_thumbnail_1[] = array(
			'id'       => 'widget_thumbnail_1_meta_' . $post_type,
			'type'     => 'checkbox',
			'title'    => esc_attr( ucwords( $post_type_name ) ) . esc_html__( ' Meta Data', 'trilisting' ),
			'subtitle' => esc_html__( 'What information should be displayed in widget.', 'trilisting' ),
			'options'  => TRILISTING\trilisting_get_widget_meta_opts( $acf_group_fields, [], $post_type ),
		);
	} // End foreach
} // End if

// -> START listing options
Redux::setSection( $opt_name, array(
	'title'   => esc_html__( 'triListing Options', 'trilisting' ),
	'id'      => 'trilisting_options_wrap_section',
	'heading' => false,
	'icon'    => 'fa fa-cogs',
));

// -> START general options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'General', 'trilisting' ),
	'id'         => 'general_options',
	'icon'       => 'fa fa-cog',
	'subsection' => true,
	'heading'    => false,
	'fields'     => $general_options,
) );

// -> START Archives options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Layout', 'trilisting' ),
	'id'         => 'archive_options',
	'icon'       => 'fa fa-columns',
	'subsection' => true,
	'heading'    => false,
	'fields'     => array(
		//Archive/Search pages
		array(
			'id'       => 'archive_search_section_begin',
			'type'     => 'section',
			'title'    => esc_html__( 'Archive/Search Pages', 'trilisting' ),
			'indent'   => true,
		),
		array(
			'id'       => 'layouts_search_result_tmpl',
			'type'     => 'button_set',
			'title'    => esc_html__( 'Listing View', 'trilisting' ),
			'subtitle' => esc_html__( 'Choose how to display listings on archive/search page.', 'trilisting' ),
			'options'  => TRILISTING\trilisting_get_widgets(),
			'default'  => 'widget_blog_1',
		),
		array(
			'id'       => 'pagination_type',
			'type'     => 'button_set',
			'title'    => esc_html__( 'Pagination', 'trilisting' ),
			'subtitle' => esc_html__( 'Choose how to display your pagination.', 'trilisting' ),
			'options'  => TRILISTING\trilisting_get_pagination_type_widgets(),
			'default'  => 'numeric',
		),
		array(
			'id'            => 'layouts_search_result_count_posts',
			'type'          => 'slider',
			'title'         => esc_html__( 'Number of Listings', 'trilisting' ),
			'default'       => 12,
			'mix'           => 1,
			'step'          => 1,
			'min'           => 1,
			'max'           => 30,
			'display_value' => 'text',
		),
		array(
			'id'        => 'layouts_search_result_columns',
			'type'      => 'select',
			'title'     => esc_html__( 'Columns', 'trilisting' ),
			'options'   => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
			'default'   => '1',
		),
		array(
			'id'      => 'enable_archive_search_maps',
			'type'    => 'switch',
			'title'   => esc_html__( 'Show Map on the Page', 'trilisting' ),
			'on'      => esc_html__(  'Yes', 'trilisting' ),
			'off'     => esc_html__(  'No', 'trilisting' ),
			'default' => true,
		),
		array(
			'id'       => 'archive_search_section_end',
			'type'     => 'section',
			'indent'   => false,
		),
		//Save
		array(
			'id'       => 'saved_section_begin',
			'type'     => 'section',
			'title'    => esc_html__( 'Saved Listings Page', 'trilisting' ),
			'indent'   => true,
		),
		array(
			'id'        => 'layouts_save_result_tmpl',
			'type'      => 'button_set',
			'title'     => esc_html__( 'Listing View', 'trilisting' ),
			'subtitle'  => esc_html__( 'Choose how to display listings on save page.', 'trilisting' ),
			'options'   => TRILISTING\trilisting_get_widgets(),
			'default'   => 'widget_blog_1',
		),
		array(
			'id'        => 'layouts_save_result_tmpl_columns',
			'type'      => 'select',
			'title'     => esc_html__( 'Blog Columns', 'trilisting' ),
			'options'   => array(
				'1' => '1',
				'2' => '2',
				'3' => '3',
			),
			'default'   => '2',
			'required'  => array( 'layouts_save_result_tmpl', 'equals', 'widget_standard_1' ),
		),
		array(
			'id'            => 'layouts_save_result_count_posts',
			'type'          => 'slider',
			'title'         => esc_html__( 'Number of Listings', 'trilisting' ),
			'default'       => 12,
			'mix'           => 1,
			'step'          => 1,
			'min'           => 1,
			'max'           => 30,
			'display_value' => 'text',
		),
		array(
			'id'       => 'saved_section_end',
			'type'     => 'section',
			'indent'   => false,
		),
	),
) );

// -> START Maps options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Map', 'trilisting' ),
	'icon'       => 'fa fa-globe-americas',
	'id'         => 'maps_section',
	'subsection' => true,
	'heading'    => false,
	'fields'     => $list_maps_meta,
) );

// -> START Listing options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Single Listing', 'trilisting' ),
	'icon'       => 'fa fa-map-marker-alt',
	'id'         => 'single_post_types_options',
	'subsection' => true,
	'heading'    => false,
	'fields'     => $list_post_types,
));

// -> START ReCaptcha options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'ReCaptcha', 'trilisting' ),
	'id'         => 'recaptcha_options',
	'icon'       => 'fa fa-ban',
	'subsection' => true,
	'heading'    => false,
	'fields'     => array(
		array(
			'id'      => 'enable_login_recaptcha',
			'type'    => 'switch',
			'title'   => esc_html__( 'ReCAPTCHA v2 on the Log in Page', 'trilisting' ),
			'default' => false,
		),
		array(
			'id'      => 'enable_recaptcha',
			'type'    => 'switch',
			'title'   => esc_html__( 'ReCAPTCHA v2 on the Registration Page', 'trilisting' ),
			'default' => false,
		),
		array(
			'id'      => 'enable_recaptcha_reset_password',
			'type'    => 'switch',
			'title'   => esc_html__( 'ReCAPTCHA v2 on the Reset Password Page', 'trilisting' ),
			'default' => false,
		),
		array(
			'id'      => 'recaptcha_site_key',
			'type'    => 'text',
			'title'   => esc_html__( 'Site Key', 'trilisting' ),
			'desc'    => __( 'Required - Enter ReCaptcha Site Key That You get After Site Registration ', 'trilisting' ) . '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">' . esc_html__( 'here', 'trilisting' ) . '</a>',
		),
		array(
			'id'      => 'recaptcha_secret_key',
			'type'    => 'text',
			'title'   => esc_html__( 'Secret Key', 'trilisting' ),
			'desc'    => __( 'Required - Enter ReCaptcha Secret Key That You get After Site Registration ', 'trilisting' ) . '<a href="https://www.google.com/recaptcha/admin#list" target="_blank">' . esc_html__( 'here', 'trilisting' ) . '</a>',
		),
	),
));

// -> START User options
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'User', 'trilisting' ),
	'id'         => 'user_options',
	'icon'       => 'fa fa-user',
	'subsection' => true,
	'heading'    => false,
	'fields'     => array(
		array(
			'id'       => 'login_redirect_page',
			'type'     => 'select',
			'title'    => esc_html__( 'Redirect to this Page After log in', 'trilisting' ),
			'data'     => 'pages',
		),
		array(
			'id'       => 'register_redirect_page',
			'type'     => 'select',
			'title'    => esc_html__( 'Redirect to this Page After Registration', 'trilisting' ),
			'data'     => 'pages',
		),
		array(
			'id'       => 'privacy_policy_section_begin',
			'type'     => 'section',
			'title'    => esc_html__( 'Privacy Policy', 'trilisting' ),
			'indent'   => true,
		),
		array(
			'id'       => 'enable_privacy_policy',
			'type'     => 'switch',
			'title'    => esc_html__( 'Enable on Registration Form', 'trilisting' ),
			'default'  => false,
		),
		array(
			'id'       => 'enable_privacy_policy_comments_form',
			'type'     => 'switch',
			'title'    => esc_html__( 'Enable on Comment Form', 'trilisting' ),
			'default'  => false,
		),
		array(
			'id'       => 'page_privacy_policy',
			'type'     => 'select',
			'title'    => esc_html__( 'Select Privacy Policy Page', 'trilisting' ),
			'data'     => 'pages',
		),
		array(
			'id'       => 'privacy_policy_comments_form_text',
			'type'     => 'editor',
			'args'     => array(
				'media_buttons' => false,
			),
			'title'    => esc_html__( 'Privacy Policy Agreement Confirmation Text', 'trilisting' ),
			'default'  => esc_html__( 'I consent to collecting and storing the data I submit in this form', 'trilisting' ),
			'required' => array( 'enable_privacy_policy_comments_form', 'equals', '1' ),
		),
		array(
			'id'       => 'privacy_policy_section_end',
			'type'     => 'section',
			'indent'   => false,
		),
		array(
			'id'       => 'terms_and_conditions_section_begin',
			'type'     => 'section',
			'title'    => esc_html__( 'Terms and Conditions', 'trilisting' ),
			'indent'   => true,
		),
		array(
			'id'       => 'enable_terms_and_conditions',
			'type'     => 'switch',
			'title'    => esc_html__( 'Enable Terms and Conditions for Registration Form', 'trilisting' ),
			'default'  => false,
		),
		array(
			'id'       => 'page_terms_and_conditions',
			'type'     => 'select',
			'title'    => esc_html__( 'Select Terms and Conditions Page', 'trilisting' ),
			'data'     => 'pages',
			'required' => array( 'enable_terms_and_conditions', 'equals', '1' ),
		),
		array(
			'id'       => 'terms_and_conditions_section_end',
			'type'     => 'section',
			'indent'   => false,
		),
	),
));

// -> START Default thumbmail
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Default Listing Image', 'trilisting' ),
	'desc'       => esc_html__( 'Default listing image is used when user forget to set listing image for the post.', 'trilisting' ),
	'id'         => 'def_thumbnail_opt',
	'subsection' => true,
	'icon'       => 'fa fa-image',
	'fields'     => array(
		array(
			'title'    => esc_html__( 'Image', 'trilisting' ),
			'subtitle' => esc_html__( 'Select from media library or upload new image.', 'trilisting' ),
			'id'       => 'def_thumb_img',
			'type'     => 'media',
		),
	),
) );

// -> START triListing Widgets options
Redux::setSection( $opt_name, array(
	'title'   => esc_html__( 'triListing Widgets', 'trilisting' ),
	'id'      => 'main_widgets_opt',
	'icon'    => 'fa fa-list',
	'heading' => false,
	'fields'  => [],
) );

// setSection
Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Blog 1', 'trilisting' ),
	'subsection' => true,
	'heading'    => false,
	'fields'     => $widget_blog_1,
) );

Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Standard 1', 'trilisting' ),
	'subsection' => true,
	'heading'    => false,
	'fields'     => $widget_standard_1,
) );

Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Thumbnail 1', 'trilisting' ),
	'subsection' => true,
	'heading'    => false,
	'fields'     => $widget_thumbnail_1,
) );

Redux::setSection( $opt_name, array(
	'title'      => esc_html__( 'Map', 'trilisting' ),
	'subsection' => true,
	'heading'    => false,
	'fields'     => $widget_map_1,
) );

if ( ! function_exists( 'trilisting_widgets_admin_icon_font' ) ) :
	/**
	 * Include font-awesome for redux admin
	 */
	function trilisting_widgets_admin_icon_font() {
		wp_register_style(
			'font-awesome',
			TRILISTING_ASSETS_URL . 'libs/font-awesome5/css/all.min.css',
			[],
			time(),
			'all'
		);
		wp_enqueue_style( 'font-awesome' );
	}
endif;
add_action( 'redux/page/' . $opt_name . '/enqueue', 'trilisting_widgets_admin_icon_font' );
