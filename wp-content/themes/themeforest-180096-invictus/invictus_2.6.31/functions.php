<?php
/*-----------------------------------------------------------------------------------*/
/*	Main Configuration Settings
/*-----------------------------------------------------------------------------------*/

// Some Global Variables
$themename     = "Invictus";
$shortname 	   = "invictus";
$fw_path  	   = TEMPLATEPATH."/doitmax_fw/"; // root to framework directory
$fw_dir  	   = get_template_directory_uri()."/doitmax_fw/";

// Define some theme constants
define('MAX_THEMENAME', "Invictus" );
define('MAX_SHORTNAME', "invictus" );
define('MAX_VERSION', "2.6.3" );

// Define path
define('MAX_FW_PATH', TEMPLATEPATH."/doitmax_fw/"); // root to framework directory
define('MAX_FW_DIR', get_template_directory_uri()."/doitmax_fw/" );
define('MAX_OPTIONS_PATH', get_template_directory_uri()."/doitmax_fw/options/"); // Define Options include path

require(MAX_FW_PATH.'/update-notifier.php');

/* @v 2.0 */
// Define global constants
define('MAX_OPTIONS_PAGE', 'max_options');
define('GALLERY_TAXONOMY', 'gallery-categories');
define('POST_TYPE_GALLERY', 'gallery'); // name the custom post type for galleries
define('PER_PAGE_DEFAULT', get_option(MAX_SHORTNAME.'_general_posts_per_page'));
/* @end 2.0 */

/* @v 2.1 */
define('MAX_CONTENT_WIDTH', 660); // set the width of the maximum media width on content pages
define('MAX_FULL_WIDTH', 923); // set the width of the maximum media width on content pages
define('MAX_DOCUMENTATION_LINK', "http://help.doitmax.de/invictus/"); // set the link to the online documentation
define('MAX_LINK_SUPPORT', "http://support.doitmax.de/"); // set the link to the support forum

if ( get_magic_quotes_gpc() ) {
    $_POST      = array_map( 'stripslashes_deep', $_POST );
    $_GET       = array_map( 'stripslashes_deep', $_GET );
    $_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
}
/* @end 2.1 */

/*-----------------------------------------------------------------------------------*/
/*	Including framework files
/*-----------------------------------------------------------------------------------*/

// get some framework files
$include_files = array(
            'max_custom_post_type',
            'max_lib',
            'max_custom_post_meta',
            'max_custom_page_meta',
            'max_images',
            'max_header',
            'max_shortcodes',
            'max_posts',
            'max_attachments',
            'max_wp_menu',
            'max_widgets',
            'max_theme_functions',
            'max_wp_admin',
            'max_mass_upload',
            'mobiledetect'
					);

// Include the files
$count_files   = count($include_files);

for ($x = 0; $x < $count_files; $x++) {
	include($fw_path.$include_files[$x].".php");
}


/*-----------------------------------------------------------------------------------*/
/*  Add default posts and comments RSS feed links to head
/*-----------------------------------------------------------------------------------*/

add_theme_support( 'automatic-feed-links' );

/*-----------------------------------------------------------------------------------*/
/*  Add support for Gallery Post Formats
/*-----------------------------------------------------------------------------------*/

// add_theme_support( 'post-formats', array( 'gallery' ) );

/*-----------------------------------------------------------------------------------*/
/*  Allow to execute Shortcodes in the excerpt
/*-----------------------------------------------------------------------------------*/
add_filter( 'the_excerpt', 'shortcode_unautop');
add_filter( 'the_excerpt', 'do_shortcode');
remove_filter('the_excerpt', 'wpautop');

/*-----------------------------------------------------------------------------------*/
/*  Add Excerpt Support to pages
/*-----------------------------------------------------------------------------------*/
add_post_type_support('page', array('excerpt'));
add_filter('excerpt_length', create_function('$a', 'return 50;'));

/*-----------------------------------------------------------------------------------*/
/*	WP2.9+ Thumbnails Settings
/*-----------------------------------------------------------------------------------*/

if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 56, 56, true ); // Normal thumbnails
	add_image_size( 'full', 2000, '', true ); // Full thumbnails
	add_image_size( 'large', 940, '', true ); // Large thumbnails
	add_image_size( 'tablet', 1024, '', true ); // Tablet fullsize BG
	add_image_size( 'mobile', 400, '', true ); // Mobile fullsize BG
	add_image_size( 'medium', 200, '', true ); // Medium thumbnails
	add_image_size( 'small', 125, '', true ); // Small thumbnails
	add_image_size( 'slides-slider', 940, '', true); // Full content width without crop
	add_image_size( 'nivo-slider', 940, 440, true); // Full content width with crop
	add_image_size( 'slider-preview', 250, 150, true); // Preview Image of backend slider images
}


add_action('init','max_options');

if (!function_exists('max_options')) {

	function max_options(){

		global $themename, $social_array, $order_array, $cropping_array, $nivo_effect_array, $slider_array, $easing_transitions_array;

		/*-----------------------------------------------------------------------------------*/
		/*	Store options from database in array for use in theme
		/*-----------------------------------------------------------------------------------*/
		global $max_options;

		$max_options = get_option('max_options');

		/*-----------------------------------------------------------------------------------*/
		/*	Catch the Wordpress Categories
		/*-----------------------------------------------------------------------------------*/
		global $wp_cats;

		$wp_cats = array();
		$max_categories = get_categories('hide_empty=0');
		foreach ($max_categories as $category_list ) {
			 $wp_cats[$category_list->cat_ID] = $category_list->cat_name;
		}

		/*-----------------------------------------------------------------------------------*/
		/*	Catch the Wordpress Pages
		/*-----------------------------------------------------------------------------------*/
		global $wp_pages;

		$max_pages = get_pages('sort_column=post_parent,menu_order');
		$wp_pages = array();
		foreach ($max_pages as $page_list) {
			$wp_pages[$page_list->ID] = $page_list->post_name;
		}
		$max_pages_temp = array_unshift($wp_pages, "Select a page:");

		/*-----------------------------------------------------------------------------------*/
		/*	Catch the Taxonomy Cats for Galleries
		/*-----------------------------------------------------------------------------------*/
		global $wp_gal_cats;

		$gallery_cats = get_terms(GALLERY_TAXONOMY, 'orderby=name&hide_empty=0&hierarchical=1');
		$wp_gal_cats = array();
		foreach ($gallery_cats as $term_list ) {
			 $wp_gal_cats[$term_list->term_id] = $term_list->name;
		}


		/*-----------------------------------------------------------------------------------*/
		/*	Make theme available for translation
		/*-----------------------------------------------------------------------------------*/

		load_theme_textdomain( 'invictus', TEMPLATEPATH . '/languages' );

		$locale = get_locale();

		$locale_file = TEMPLATEPATH . "/languages/$locale.php";
		if ( is_readable( $locale_file ) )
			require_once( $locale_file );


		/*-----------------------------------------------------------------------------------*/
		/*	 Creates the admin menu
		/*-----------------------------------------------------------------------------------*/
		// Option presets
		$slider_array		  		= array('none'=>"No Slider", 'slider-nivo' => "Nivo Slider", 'slider-slides' => 'Slides Slider', 'slider-kwicks' => "Accordion Slider");
		$social_array = array('500px'=>'500px',
  											'addthis'            => "AddThis",
  											'audioboo'           => "Audioboo",
  											'bebo'               => "bebo",
  											'behance'            => "Behance",
  											'blogger'            => "Blogger",
  											'creativecommons'    => "CreativeCommons",
  											'dailybooth'         => "DailyBooth",
  											'delicious'          => "Delicious",
  											'designfloat'        => "DesignFloat",
  											'digg'               => "Digg",
  											'dopplr'             => "Dopplr",
  											'dribbble'           => "Dribbble",
  											'email'              => "Email",
  											'ember'              => "Ember",
  											'etsy'               => "Etsy",
  											'evernote'           => "Evernote",
  											'facebook'           => "Facebook",
  											'flickr'             => "Flickr",
  											'formspring'         => "Formspring",
  											'forrst'             => "Forrst",
  											'foursquare'         => "Foursquare",
  											'friendfeed'         => "FriendFeed",
  											'github'             => "GitHub",
  											'google'             => "Google",
  											'googleplus'         => 'Google+',
  											'grooveshark'        => 'Grooveshark',
  											'hyves'              => 'Hyves',
  											'icq'                => "ICQ",
  											'imdb'               => "IMDB",
  											'instagram'          => 'Instagram',
  											'lastfm'             => "Lastfm",
  											'linkedin'           => "LinkedIn",
  											'livejournal'        => 'LiveJournal',
  											'lockerz'            => 'Lockerz',
  											'megavideo'          => 'Megavideo',
  											'msn'                => "MSN",
  											'myspace'            => "MySpace",
  											'path'               => 'Path',
  											'paypal'             => 'PayPal',
  											'piano'              => 'Piano',
  											'picasa'             => 'Picasa',
  											'pinterest'          => "Pinterest",
  											'playstation'        => 'Playstation',
  											'posterous'          => 'Posterous',
  											'reddit'             => "Reddit",
  											'rss'                => "RSS",
  											'sharethis'          => 'ShareThis',
  											'skype'              => "Skype",
  											'socialvibe'         => "SocialVibe",
  											'soundcloud'         => "SoundCloud",
  											'spotify'            => "Spotify",
  											'stumbleupon'        => "Stumbleupon",
  											'technorati'         => "Technorati",
  											'themeforest'        => "ThemeForest",
  											'tumblr'             => "Tumblr",
  											'twitpic'            => "TwitPic",
  											'twitter'            => "Twitter",
  											'typepad'            => "Typepad",
  											'viddler'            => "Viddler",
  											'vimeo'              => "Vimeo",
  											'virb'               => "Virb",
  											'vkontakt'           => "VKontakt",
  											'xing'               => "Xing",
  											'windows'            => "Windows",
  											'wordpress'          => "WordPress",
  											'xing'               => "Xing",
  											'yahoo'              => "Yahoo",
  											'youtube'            => "YouTube",
  											'zerply'             => "Zerply");
		$theme_array                  = array("black"=>"Black Theme","white"=>"White Theme");
		$order_array                  = array("rand"=>"Random","id"=>"Post-ID","date"=>"Post Date","title"=>"Post Title","modified"=>"Last modified", 'menu_order'=>'Menu Order');
		$pretty_speed_array           = array('fast'=>'Fast',"normal"=>'Normal',"slow"=>'Slow');
		$pretty_theme_array           = array('dark_square'=>"Dark Square",'light_square'=>"Light Square",'dark_rounded'=>"Dark Rounded",'light_rounded'=>"Light Rounded",'facebook'=>"Facebook");
		$fullsize_speed_array         = array('slow'=>'Slow','normal'=>'Normal','fast'=>'Fast');
		$fullsize_transition_array    = array(0=>"None",1=>"Fade",2=>"Slide Down",3=>"Slide Left",4=>"Slide Top",5=>"Slide Right",6=>"Blind horizontal",7=>"Blind Vertiacl",90=>"Slide Right/Left",91=>"Slide Top/Down");
		$fullsize_overlay_array       = array("dotted"=> "Dots", "squared" => "Squares", "scanlines" => "Scanlines", "carbon" => "Carbon", "triangles" => "Triangles" );
		$cropping_array               = array( 'c' => 'Position in the Center (default)', 't' => 'Align top', 'tr' => 'Align top right', 'tl' => 'Align top left', 'b' => 'Align bottom', 'br' => 'Align bottom right', 'bl' => 'Align bottom left', 'l' => 'Align left', 'r' => 'Align right' );
		$nivo_effect_array            = array("random"=>"Random","sliceDown"=>"Slice Down","sliceDownLeft"=>"Slice Down Left","sliceUp"=>"Slice Up","sliceUpLeft"=>"Slice Up Left","sliceUpDown"=>"Slice Up Down","sliceUpDownLeft"=>"Slice Up Down Left","fold"=>"Fold","fade"=>"Fade","slideInRight"=>"Slide in Right","slideInLeft"=>"Slide in Left", "boxRandom" => "Box Random", "boxRain" => "Box Rain", "boxRainReverse" => "Box Rain Reverse", "boxRainGrow" => "Box Rain Grow", "boxRainGrowReverse" => "Box Rain Grow Reverse");
		$easing_transitions_array     = array("linear"=>"Linear","easeInSine"=>"easeInSine","easeOutSine"=>"easeOutSine","easeInQuad"=>"easeInQuad","easeOutQuad"=>"easeOutQuad","easeInCubic"=>"easeInCubic","easeOutCubic"=>"easeOutCubic","easeInQuart"=>"easeInQuart","easeOutQuart"=>"easeOutQuart","easeInExpo"=>"easeInExpo","easeOutExpo"=>"easeOutExpo","easeInCirc"=>"easeInCirc","easeOutCirc"=>"easeOutCirc","easeInElastic"=>"easeInElastic","easeOutElastic"=>"easeOutElastic","easeInOutElastic"=>"easeInOutElastic","easeInBack"=>"easeInBack","easeOutBack"=>"easeOutBack","easeInBounce"=>"easeInBounce","easeOutBounce"=>"easeOutBounce");


		/*-----------------------------------------------------------------------------------*/
		/*	 Backward compatibility to optionsets from older theme versions
		/*-----------------------------------------------------------------------------------*/
		if( !get_option(MAX_SHORTNAME.'_font_backward_check') || get_option(MAX_SHORTNAME.'_font_backward_check') == "" ){

			$old_headline_font = get_option(MAX_SHORTNAME.'_color_main_headlines');
			$old_h1_font_size = get_option(MAX_SHORTNAME.'_font_size_h1') != "" ? get_option(MAX_SHORTNAME.'_font_size_h1') : 42;
			$old_h2_font_size = get_option(MAX_SHORTNAME.'_font_size_h2') != "" ? get_option(MAX_SHORTNAME.'_font_size_h2') : 36;
			$old_h3_font_size = get_option(MAX_SHORTNAME.'_font_size_h3') != "" ? get_option(MAX_SHORTNAME.'_font_size_h3') : 30;
			$old_h4_font_size = get_option(MAX_SHORTNAME.'_font_size_h4') != "" ? get_option(MAX_SHORTNAME.'_font_size_h4') : 24;
			$old_h5_font_size = get_option(MAX_SHORTNAME.'_font_size_h5') != "" ? get_option(MAX_SHORTNAME.'_font_size_h5') : 20;
			$old_h6_font_size = get_option(MAX_SHORTNAME.'_font_size_h6') != "" ? get_option(MAX_SHORTNAME.'_font_size_h6') : 16;

			$old_h1_font_color = get_option(MAX_SHORTNAME.'_color_main_h1') != "" ? get_option(MAX_SHORTNAME.'_color_main_h1') : '#EEEEEE';
			$old_h2_font_color = get_option(MAX_SHORTNAME.'_color_main_h2') != "" ? get_option(MAX_SHORTNAME.'_color_main_h2') : '#c73a3a';
			$old_h3_font_color = get_option(MAX_SHORTNAME.'_color_main_h3') != "" ? get_option(MAX_SHORTNAME.'_color_main_h3') : '#CCCCCC';
			$old_h4_font_color = get_option(MAX_SHORTNAME.'_color_main_h4') != "" ? get_option(MAX_SHORTNAME.'_color_main_h4') : '#CCCCCC';
			$old_h5_font_color = get_option(MAX_SHORTNAME.'_color_main_h5') != "" ? get_option(MAX_SHORTNAME.'_color_main_h5') : '#CCCCCC';
			$old_h6_font_color = get_option(MAX_SHORTNAME.'_color_main_h6') != "" ? get_option(MAX_SHORTNAME.'_color_main_h6') : '#CCCCCC';

			$old_body_font_family = get_option(MAX_SHORTNAME.'_color_main_font') != "" ? get_option(MAX_SHORTNAME.'_color_main_font') : 'PT Sans';
			$old_body_font_size = get_option(MAX_SHORTNAME.'_color_font_size') != "" ? get_option(MAX_SHORTNAME.'_color_font_size') : 12;

			if( $old_body_font_family && !is_array( get_option(MAX_SHORTNAME.'_font_body') ) ){
				add_option(MAX_SHORTNAME.'_font_body', array('font_family' => $old_body_font_family, 'font_size' => $old_body_font_size, 'line_height' => 20, 'font_weight' => 'normal', 'font_color' => '#BBBBBB' ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h1') ) ){
				add_option(MAX_SHORTNAME.'_font_h1', array('font_family' => $old_headline_font, 'font_size' => $old_h1_font_size, 'line_height' => 60, 'font_weight' => 300, 'font_color' => $old_h1_font_color ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h2') ) ){
				add_option(MAX_SHORTNAME.'_font_h2', array('font_family' => $old_headline_font, 'font_size' => $old_h2_font_size, 'line_height' => 50, 'font_weight' => 300, 'font_color' => $old_h2_font_color ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h3') ) ){
				add_option(MAX_SHORTNAME.'_font_h3', array('font_family' => $old_headline_font, 'font_size' => $old_h3_font_size, 'line_height' => 40, 'font_weight' => 300, 'font_color' => $old_h3_font_color ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h4') ) ){
				add_option(MAX_SHORTNAME.'_font_h4', array('font_family' => $old_headline_font, 'font_size' => $old_h4_font_size, 'line_height' => 30, 'font_weight' => 300, 'font_color' => $old_h4_font_color ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h5') ) ){
				add_option(MAX_SHORTNAME.'_font_h5', array('font_family' => $old_headline_font, 'font_size' => $old_h5_font_size, 'line_height' => 20, 'font_weight' => 300, 'font_color' => $old_h5_font_color ), '', 'yes' );
			}
			if( $old_headline_font && !is_array( get_option(MAX_SHORTNAME.'_font_h6') ) ){
				add_option(MAX_SHORTNAME.'_font_h6', array('font_family' => $old_headline_font, 'font_size' => $old_h6_font_size, 'line_height' => 15, 'font_weight' => 300, 'font_color' => $old_h6_font_color ), '', 'yes' );
			}

			// add control option for backward compatibility
			add_option(MAX_SHORTNAME.'_font_backward_check', "true");

		}

		// The Options
		$options = array();

		// Create the General Tab
		$options[] = array( "name" => __('General', MAX_SHORTNAME),
							"id"=>"general",
							"type" => "section",
							"icon" => "hammer_screwdriver.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Logo options', MAX_SHORTNAME),
							"id"=>"subhead_general_logo",
							"type" => "subhead");

			$options[] = array( "name" => __('Custom logo', MAX_SHORTNAME),
								"desc" => __('Upload your own logo to use as site logo. (Should not be larger than 235x100px)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_custom_logo",
								"std" => "",
								"type" => "upload");

			$options[] = array( "name" => __('Blank logo', MAX_SHORTNAME),
								"desc" => __('Turn on, if you want to show a blank logo without background and borders. Recommend if you have a transparent logo or a one with a colored background.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_custom_logo_blank",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Custom Favicon', MAX_SHORTNAME),
								"desc" => __('Upload a 16px x 16px Png/Gif image that will represent your website\'s favicon.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_custom_favicon",
								"std" => "",
								"type" => "upload");

			$options[] = array( "name" => __('Use custom login logo', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the custom login logo for the WP-Admin Login. (upload your custom logo to /invictus/images/wp-login-logo.png)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_custom_login_logo",
								"type" => "checkbox",
								"std" => "true");

		$options[] = array( "name" => __('Post Options', MAX_SHORTNAME),
							"id"=>"subhead_general_post",
							"type" => "subhead");

			$options[] = array( "name" => __('Show fullsize overlay', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the overlay pattern on all pages. This options has no effect at the fullsize gallery overlay.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_show_fullsize_overlay",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Posts per page', MAX_SHORTNAME),
								"desc" => __('Enter the number of post, that will be displayed on a portfolio page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_posts_per_page",
								"type" => "slider",
								"step" => "1",
								"max" => "300",
								"min" => "1",
								"std" => "24");

		$options[] = array( "name" => __('Others', MAX_SHORTNAME),
							"id"=>"subhead_general_others",
							"type" => "subhead");

			$options[] = array( "name" => __('Fade in Content after x milliseconds.', MAX_SHORTNAME),
								"desc" => __('Select the milliseconds after your content is fade in to let user take a short look on your background images. 0 value means this option is deactivated.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_fadein_content",
								"type" => "slider",
								"step" => "100",
								"max" => "20000",
								"min" => "0",
								"std" => "0");

			$options[] = array( "name" => __('Google Analytics ID', MAX_SHORTNAME),
								"desc" => __('Enter your Google Analytic ID to track your page visitors.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_google_analytics_id",
								"type" => "text",
								"std" => "");

			$options[] = array( "name" => __('Custom CSS',MAX_SHORTNAME),
								"desc" => __('Enter some CSS to your theme by adding it to this block.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_custom_css",
								"std" => "",
								"type" => "textarea",
								"rows" => 10);

			$options[] = array( "name" => __('Password protected text', MAX_SHORTNAME),
								"desc" => __('Enter the text wich is shown if a page or post is password protected.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_protected_login_text",
								"std" => "Whoops, this page is password protected. To view it please enter the password below:",
								"type" => "textarea",
								"rows" => 10);

			/* $options[] = array( "name" => __('Show custom Dashboard feed', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the custom dashboard feed to get latest news from our blog on your WP-Dashboard. (since 2.2)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_custom_dashboard",
								"type" => "checkbox",
								"std" => "true"); */

		$options[] = array( "type" => "close");

		// Create the Homepage Tab
		$options[] = array( "name" => __('Homepage', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_homepage",
							"type" => "section",
							"icon" => "house.png");
		$options[] = array( "type" => "open");


		$options[] = array( "name" => __('Welcome Teaser', MAX_SHORTNAME),
							"id"=>"subhead_general_welcome",
							"type" => "subhead");

			$options[] = array( "name" => __('Show Welcome Teaser', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show your Welcome Teaser on the Homepage', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_show_welcome_teaser",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Welcome Teaser', MAX_SHORTNAME),
								"desc" => __('Change your "Welcome Teaser on the Homepage.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_welcome_teaser",
								"type" => "textarea",
								"std" => "Hi, my name is <strong>John Doe</strong>. I AM a Professional Photographer located in the beautiful city of <strong>Berlin, Germany</strong>. Checkyout my great work in my awesome online Portfolio.",
								"rows"=> 8 );

			$options[] = array( "name" => __('Welcome Teaser Font Size', MAX_SHORTNAME),
								"desc" => __('Select the fontsize for your Welcome Teaser in px', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_teaser_font_size",
								"type" => "slider",
								"step" => "1",
								"max" => "60",
								"min" => "1",
								"std" => "26");

			$options[] = array( "name" => __('Welcome Teaser Bold Font Size', MAX_SHORTNAME),
								"desc" => __('Select the fontsize for the bold text of your Welcome Teaser in px', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_teaser_font_size_bold",
								"type" => "slider",
								"step" => "1",
								"max" => "60",
								"min" => "1",
								"std" => "34");

		$options[] = array( "name" => __('Other', MAX_SHORTNAME),
							"id"=>"subhead_general_welcome",
							"type" => "subhead");

			$options[] = array( "name" => __('Show Sidebar', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show a sidebar on your Homepage', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_show_homepage_sidebar",
								"type" => "checkbox",
								"std" => "false");

		$options[] = array( "type" => "close");

		// Create the Colors & Layout Tab
		$options[] = array( "name" => __('Colors', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_colors",
							"type" => "section",
							"icon" => "control_wheel.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Main Colors', MAX_SHORTNAME),
							"id"=>"subhead_colors_main",
							"type" => "subhead");

			$options[] = array( "name" => __('Colour Scheme', MAX_SHORTNAME),
								"desc" => __('Select the colour scheme for the theme.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_color_main",
								"type" => "select",
								"options" => $theme_array,
								"std" => "white");

			$options[] = array( "name"  => __('Main Color', MAX_SHORTNAME),
								"desc" =>  __('This is the main color for your theme (borders, current nav marker, nav pulldown background...)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_color_main_typo",
								"type" => "colorpicker",
								"std" => "#c73a3a");

			$options[] = array( "name"  => __('Link Color', MAX_SHORTNAME),
								"desc" => __('This is the link color for your theme.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_color_main_link",
								"type" => "colorpicker",
								"std" => "#c73a3a");

			$options[] = array( "name"  => __('Navigation Link Hover', MAX_SHORTNAME),
								"desc" => __('This is the hover color for your navigation links (hover and active).', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_color_nav_link_hover",
								"type" => "colorpicker",
								"std" => "#FFFFFF");

			$options[] = array( "name"  => __('Pulldown Link Hover', MAX_SHORTNAME),
								"desc" => __('This is the hover color for your pulldown links (hover and active).', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_color_pulldown_link_hover",
								"type" => "colorpicker",
								"std" => "#212121");

		$options[] = array( "type" => "close");

		// Create the Font Tab
		$options[] = array( "name" => __('Fonts', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_font",
							"type" => "section",
							"icon" => "font.png");
		$options[] = array( "type" => "open");


		$options[] = array( "name" => __('Main Font', MAX_SHORTNAME),
							"id"=>"subhead_font_main",
							"type" => "subhead");

			$options[] = array( "name" => __('Google Font API Key', MAX_SHORTNAME),
								"desc" => __('Enter your Google Font API Key to ensure updates of the google font library.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_google_fontapi_key",
								"type" => "text",
								"std" => "");

			$options[] = array( "name" => __('Use Subsets', MAX_SHORTNAME),
								"desc" => __('Choose the subsets of Google Fonts to use. ( Greek and Cyrillic )', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_subsets",
								"type" => "select",
								"options" => array('none' => 'None (Default)', 'greek' => "Greek", 'cyrillic' => "Cyrillic"),
								"std" => "none");

			$options[] = array( "name" => __('Main Body font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_body",
								"std" => array('font_family' => "PT Sans", 'font_size' => 12, 'line_height' => 20, 'font_weight' => 300, 'font_color' => '#BBBBBB'),
								"type" => "font",
								"min" => 1,
								"max" => 60);

			$options[] = array( "name" => __('H1 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h1",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 42, 'line_height' => 60, 'font_weight' => 300, 'font_color' => '#EEEEEE'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('H2 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h2",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 36, 'line_height' => 50, 'font_weight' => 300, 'font_color' => '#c73a3a'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('H3 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h3",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 30, 'line_height' => 40, 'font_weight' => 300, 'font_color' => '#CCCCCC'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('H4 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h4",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 24, 'line_height' => 30, 'font_weight' => 300, 'font_color' => '#CCCCCC'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('H5 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h5",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 18, 'line_height' => 20, 'font_weight' => 300, 'font_color' => '#CCCCCC'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('H6 font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_h6",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 16, 'line_height' => 15, 'font_weight' => 300, 'font_color' => '#CCCCCC'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('Widget headline font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_widget",
								"std" => array('font_family' => "Yanone Kaffeesatz", 'font_size' => 24, 'line_height' => 28, 'font_weight' => 300, 'font_color' => '#c73a3a'),
								"type" => "font",
								"min" => 12,
								"max" => 80);

			$options[] = array( "name" => __('Navigation font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_navigation",
								"std" => array('font_family' => "PT Sans", 'font_size' => 13, 'line_height' => 18, 'font_weight' => 100, 'font_color' => '#AAAAAA'),
								"type" => "font",
								"min" => 10,
								"max" => 80);

			$options[] = array( "name" => __('Navigation pulldown font style', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_font_navigation_pulldown",
								"std" => array('font_family' => "PT Sans", 'font_size' => 12, 'line_height' => 18, 'font_weight' => 100, 'font_color' => '#FFFFFF'),
								"type" => "font",
								"min" => 10,
								"max" => 80);

		$options[] = array( "type" => "close");

		// Create the Blog Tab
		$options[] = array( "name" => __('Blog', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_blog",
							"type" => "section",
							"icon" => "page.png");
		$options[] = array( "type" => "open");

			$options[] = array( "name" => __('Blog Category', MAX_SHORTNAME),
								"desc" => __('Choose the Cagetory of your Blog to display the Blog Posts in the correct Template file.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_blog_id",
								"type" => "select",
								"options" => $wp_cats,
								"std" => "2");

			$options[] = array( "name" => __('Show Full Blogpost in the blogroll', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the full blog post in the blogroll instead of a short excerpt.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_show_fullblog",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Do not show videos and sliders in blogroll', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the videos or sliders on blog post in the blogroll instead of the featured image.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_blog_show_compact",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Show Full Width Blog details', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the full blog post detail page without sidebar but with full page width.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_show_fullblog_details",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Show Post Author', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the post author of a post on the blog post page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_show_author",
								"type" => "checkbox",
								"std" => "true");

		$options[] = array( "type" => "close");

		// Create the Images Tab
		$options[] = array( "name" => __('Photos/Images', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_images",
							"type" => "section",
							"icon" => "images.png");
		$options[] = array( "type" => "open");


		$options[] = array( "name" => __('Do not use timthumb.php image cropping', MAX_SHORTNAME),
								"desc" => __('Activate this option to not use the timthumb dynamic image resizing script.<br><strong>Important Note:</strong> Some functions like "Cropping Direction", "Greyscale" and others are not working without timthumb!', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_no_timthumb",
								"type" => "checkbox",
								"std" => "false");


		$options[] = array( "name" => __('Photo project page settings', MAX_SHORTNAME),
							"id"=>"subhead_image_project",
							"type" => "subhead");

			$options[] = array( "name" => __('Do not crop photos featured image', MAX_SHORTNAME),
								"desc" => __('Check, if you want to use the original ratio without cropping for your featured images on a photos post page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_project_original_ratio",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Do not crop blog featured images', MAX_SHORTNAME),
								"desc" => __('Check, if you want to use the original ratio without cropping for your featured images on a blog post page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_blog_original_ratio",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Show gallery images on photo project page', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show Images from the current gallery where the photo is drawn from.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_show_gallery_images",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Number of photo page gallery images', MAX_SHORTNAME),
								"desc" => __('Set the number of gallery images to show on a photos post page. These images are drawn from the gallery the current photo is bind to.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_count_gallery_images",
								"type" => "slider",
								"step" => "1",
								"max" => "100",
								"min" => "1",
								"std" => "20");

			$options[] = array( "name" => __('Show photo author', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the author of a photo post on the photos post page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_general_show_photo_author",
								"type" => "checkbox",
								"std" => "true");

		$options[] = array( "name" => __('Other settings', MAX_SHORTNAME),
							"id"=>"subhead_image_others",
							"type" => "subhead");

			$options[] = array( "name" => __('Show image caption', MAX_SHORTNAME),
								"desc" => __('Show the image caption and a small excerpt on mouseover, always or do not show it at all.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_show_caption",
								"type" => "select",
								"options" => array("true" => "On hover", "always" => "Show always", "false" => "Don't show"),
								"std" => "true");

			$options[] = array( "name" => __('Fullsize grid image width', MAX_SHORTNAME),
								"desc" => __("Set the width of a single image in the fullsize grid template. <strong>This value is needed to show the images.</strong>", MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_fullsize_grid_width",
								"type" => "slider",
								"step" => "1",
								"max" => "600",
								"min" => "1",
								"std" => "200");

			// set value if not set before
			if( !get_option_max('image_fullsize_grid_height') && get_option_max('image_fullsize_grid_height') == "" ) {
				update_option(MAX_SHORTNAME.'_image_fullsize_grid_height', 0);
			}
			$options[] = array( "name" => __('Fullsize grid image height', MAX_SHORTNAME),
								"desc" => __("Set the height of a single image in the fullsize grid template. Set to 0 to use height proportional to it's width.", MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_fullsize_grid_height",
								"type" => "slider",
								"step" => "1",
								"max" => "600",
								"min" => "0",
								"std" => "0");

			$options[] = array( "name" => __('Fullsize scroller image heigth', MAX_SHORTNAME),
								"desc" => __('Set the height of a single image in the fullsize scroller template', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_fullsize_scroller_height",
								"type" => "slider",
								"step" => "1",
								"max" => "1000",
								"min" => "1",
								"std" => "400");

			$options[] = array( "name" => __('Show image fade', MAX_SHORTNAME),
								"desc" => __('Show the image fade and background on mouseover.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_image_show_fade",
								"type" => "checkbox",
								"std" => "true");

		$options[] = array( "type" => "close");


		// Create the Fullsize Gallery Tab
		$options[] = array( "name" => "Fullsize Gallery",
							"id"=>"header_fullsize",
							"type" => "section",
							"icon" => "fullsize_gallery.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Main Settings', MAX_SHORTNAME),
							"id"=>"subhead_fullsize_main",
							"type" => "subhead");

			$options[] = array( "name" => __('Number of images', MAX_SHORTNAME),
								"desc" => __('Enter the number of images you want to show in the fullsize gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_count",
								"type" => "slider",
								"step" => "1",
								"max" => "350",
								"min" => "1",
								"std" => "18");

			$options[] = array( "name" => __('Featured galleries', MAX_SHORTNAME),
								"desc" => __('Choose the galleries from which featured images for the fullsize gallery are drawn.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_cat",
								"type" => "multicheck",
								"options" => $wp_gal_cats,
								"std" => "false");

			$options[] = array( "name" => __('Show fullsize overlay', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the overlay pattern on your fullsize galleries.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_show_fullsize_overlay",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Preload images', MAX_SHORTNAME),
								"desc" => __('Check, if you want to preload all Fullsize Gallery images on page load. <strong>Not recommend, if you have more than 15 images attached to your fullsize gallery galleries</strong>. (Since 2.2)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_preload",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Always fit images', MAX_SHORTNAME),
								"desc" => __('Check, if you want the images never exceed browser width or height and always remain their original proportions.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_fit_always",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Show overlay text', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show a title and a small text as overlay on the fullsize gallery (remember that text might not be readable on light fullsize backgrounds).', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_title_show",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Overlay title', MAX_SHORTNAME),
								"desc" => __('Enter the text you want to show as title text on your homepage fullsize gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_title",
								"type" => "text",
								"std" => "");

			$options[] = array( "name" => __('Overlay text', MAX_SHORTNAME),
								"desc" => __('Enter the text you want to show as description text on your homepage fullsize gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_text",
								"std" => "",
								"type" => "textarea",
								"rows" => 10);

			$options[] = array( "name" => __('Image order', MAX_SHORTNAME),
								"desc" => __('Choose the order of images displayed in the Fullsize Gallery on homepage.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_order",
								"type" => "select",
								"options" => $order_array,
								"std" => "normal");

			$options[] = array( "name" => __('Image sorting direction', MAX_SHORTNAME),
								"desc" => __('Choose the sorting direction of the images in a full size gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_featured_sorting",
								"type" => "select",
								"options" => array('desc' => 'Descending', 'asc' => 'Ascending'),
								"std" => "desc");

			$options[] = array( "name" => __('Overlay pattern', MAX_SHORTNAME),
								"desc" => __('Select the type of overlay pattern for your full size gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_overlay_pattern",
								"type" => "radio",
								"options" => $fullsize_overlay_array,
								"std" => "dotted",
								"addtype" => "overlay");

			$options[] = array( "name" => __('Show password protected posts', MAX_SHORTNAME),
								"desc" => __('Do you want to show password protected posts on the fullsize gallery scroller?.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_exclude_protected",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Remove link on image title', MAX_SHORTNAME),
								"desc" => __('Do you want to remove the link on the fullsize gallery image title? This is a global setting and switching on will overwrite individual post settings.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_remove_title_link",
								"type" => "checkbox",
								"std" => "false");

		$options[] = array( "name" => __('Slider Settings', MAX_SHORTNAME),
							"id"=>"subhead_fullsize_slider",
							"type" => "subhead");

			$options[] = array( "name" => __('Show Thumbnail Scroller on Homepage', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the thumbnails on the homepage or not. By default the thumbnails are displayed.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_homepage_show_thumbnails",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Use mouse move scrolling', MAX_SHORTNAME),
								"desc" => __('Check, if you want to use the new mouse move scrolling on the thumbnails. Off will keep the old style scrolling. (since 2.0)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_mouse_scrub",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Hide thumbnails on mouseout', MAX_SHORTNAME),
								"desc" => __('Switch this option on, if you want to hide the thumbnail scroller when you leave it with the mouse cursor.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_mouse_leave",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Use key navigation', MAX_SHORTNAME),
								"desc" => __('Check, if you want to use the key navigation for your fullsize galleries (left = prev slide, right = next slide, up = show thumbnails, down = hidethumbnails). (since 2.1.7)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_key_nav",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Autoplay slideshow', MAX_SHORTNAME),
								"desc" => __('Check, if you want to autoplay the slideshow for your fullsize gallery. (since 2.0)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_autoplay_slideshow",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Show overlay slideshow title', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the overlay slideshow title beside the slideshow controls on pages with a fullsize background gallery.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_show_title",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Show overlay slideshow excerpt', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the overlay slideshow excerpt below the title on pages with a fullsize background gallery. This excerpt is only shown, when "Show overlay slideshow title" is activated.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_show_title_excerpt",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Position of thumbnail controls', MAX_SHORTNAME),
								"desc" => __('Choose where to show the thumbnail controls of your fullsize gallery scroller.', MAX_SHORTNAME),
								"id" => __(MAX_SHORTNAME.'_fullsize_controls_position'),
								"type" => "select",
								"options" => array('right' => 'Right', 'centered' => 'Centered'),
								"std" => "right");

			$options[] = array( "name" => __('Greyscale images', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show greyscaled thumbnails in your slider. Colored images will be shown on hover.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_use_greyscale",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Crop thumbnails', MAX_SHORTNAME),
								"desc" => __('Choose whether you want to show cropped thumbnails in your slider. All images are appearing in the same format.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_use_square",
								"type" => "checkbox",
								"std" => "false");

			$options[] = array( "name" => __('Thumbnail height', MAX_SHORTNAME),
								"desc" => __('Set the height of the thumbnails in the scroller. The width is set proportional.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_thumb_height",
								"type" => "slider",
								"step" => "1",
								"max" => "200",
								"min" => "1",
								"std" => "100");

			$options[] = array( "name" => __('Thumbnail quality', MAX_SHORTNAME),
								"desc" => __('Set quality value of the Thumbnails in percent.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_thumb_quality",
								"type" => "slider",
								"step" => "1",
								"max" => "100",
								"min" => "1",
								"std" => "100");

			$options[] = array( "name" => __('Slide interval', MAX_SHORTNAME),
								"desc" => __('The interval betweeen background slides in ms.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_interval",
								"type" => "slider",
								"step" => "100",
								"max" => "50000",
								"min" => "1000",
								"std" => "8000");

			$options[] = array( "name" => __('Animation speed', MAX_SHORTNAME),
								"desc" => __('The speed of the slides animation.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_speed",
								"type" => "select",
								"options" => $fullsize_speed_array,
								"std" => "normal");

			$options[] = array( "name" => __('Transition', MAX_SHORTNAME),
								"desc" => __('Type of slide transition.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_transition",
								"type" => "select",
								"options" => $fullsize_transition_array,
								"std" => 1);

			$options[] = array( "name" => __('Video Settings', MAX_SHORTNAME),
							"id"=>"subhead_fullsize_video",
							"type" => "subhead");

			// backward compatibility
			$_autoplay_videos = get_option_max('fullsize_autoplay_video');
			if( !$_autoplay_videos || $_autoplay_videos == "" ){
				update_option( MAX_SHORTNAME.'_fullsize_autoplay_video', 'true' );
			}
			$_video_show_elements = get_option_max('fullsize_video_show_elements');
			if( !$_video_show_elements || $_video_show_elements == "" ){
				update_option( MAX_SHORTNAME.'_fullsize_video_show_elements', 'true' );
			}

			$options[] = array( "name" => __('Show page elements when a video is playing.', MAX_SHORTNAME),
								"desc" => __('Check, if you want to show the page elements when a video is playing. This will not show the thumbnail slider, but the navigation and logo.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_video_show_elements",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Autoplay slideshow videos', MAX_SHORTNAME),
								"desc" => __('Check, if you want to autoplay videos on your slideshow in a fullsize gallery. (since 2.1.6)', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_autoplay_video",
								"type" => "checkbox",
								"std" => "true");

			$options[] = array( "name" => __('Force to play YouTube videos in HD', MAX_SHORTNAME),
								"desc" => __('Check, if you want to play your YouTube fullsize videos always in HD.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_fullsize_yt_hd",
								"type" => "checkbox",
								"std" => "true");


		$options[] = array( "type" => "close");

		// Create the Flickr Template Management Tab
		$options[] = array( "name" => __('Fullsize Flickr', MAX_SHORTNAME),
							"id"=>"header_fullsize_flickr",
							"type" => "section",
							"icon" => "flickr.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('General', MAX_SHORTNAME),
							"id"=>"subhead_flickr_general",
							"type" => "subhead");

		$options[] = array( "name" => __('Flickr API Key - <a href="http://bit.ly/phYq8o" target="_blank"><small>You need to get your own!</small></a>', MAX_SHORTNAME),
							"desc" => __('Your Flickr API Key. Please create your own here: <a href="http://bit.ly/phYq8o">http://bit.ly/phYq8o</a>',MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_api_key",
							"type" => "text",
							"std" => "8fbf274a2c8d12ffa1bbf00b4462f715");

		$options[] = array( "name" => __("Always fit images", MAX_SHORTNAME),
							"desc" => __('Image will never exceed browser width or height.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_always_fit",
							"type" => "checkbox",
							"std" => "false");

		$options[] = array( "name" => __("Show overlay", MAX_SHORTNAME),
							"desc" => __('Enable or disable the fullsize overlay.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_scanlines",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Slideshow', MAX_SHORTNAME),
							"id"=>"subhead_flickr_slideshow",
							"type" => "subhead");

		$options[] = array( "name" => __("Autoplay", MAX_SHORTNAME),
							"desc" => __('Check, slideshow starts playing automatically.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_autoplay",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Slideshow interval', MAX_SHORTNAME),
							"desc" => __('The interval betweeen each slides in ms.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_slideshow_interval",
							"type" => "slider",
							"step" => "10",
							"max" => "20000",
							"min" => "100",
							"std" => "5000");

		$options[] = array( "name" => __('Transition', MAX_SHORTNAME),
							"desc" => __('Type of transition for each slide', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_transition",
							"type" => "select",
							"options" => array(
								'0' => __('None', MAX_SHORTNAME),
								'1' => __('Fade', MAX_SHORTNAME),
								'2' => __('Slide Top', MAX_SHORTNAME),
								'3' => __('Slide Right', MAX_SHORTNAME),
								'4' => __('Slide Bottom', MAX_SHORTNAME),
								'5' => __('Slide Left', MAX_SHORTNAME),
							),
							"std" => "1");

		$options[] = array( "name" => __('Transition speed', MAX_SHORTNAME),
							"desc" => __('The speed of transition in ms.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_transition_speed",
							"type" => "slider",
							"step" => "10",
							"max" => "5000",
							"min" => "10",
							"std" => "750");

		$options[] = array( "name" => __('Control bar settings', MAX_SHORTNAME),
							"id"=>"subhead_flickr_controls",
							"type" => "subhead");

		$options[] = array( "name" => __("Show navigation", MAX_SHORTNAME),
							"desc" => __('Show slideshow controls or hide (play, pause, next, prev).', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_navigation",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Show thumbnails", MAX_SHORTNAME),
							"desc" => __('Show thumbnail navigation for prev and next images.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_thumbnail_navigation",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Show slide numbers", MAX_SHORTNAME),
							"desc" => __('Display actual and allover slide numbers on control bar.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_slide_counter",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Show slide captions", MAX_SHORTNAME),
							"desc" => __('Display the slide caption for each image. (Pull from "title" in slides array of flickr images).', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_flickr_slide_captions",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "type" => "close");

		// Create the PrettyPhoto Tab
		$options[] = array( "name" => __('PrettyPhoto', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_prettyPhoto",
							"type" => "section",
							"icon" => "prettyphoto.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Slideshow Interval', MAX_SHORTNAME),
							"desc" => __('Interval time of images in ms. Value "false" will disable the slideshow."', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_interval",
							"type" => "slider",
							"step" => "100",
							"max" => "50000",
							"min" => "1000",
							"std" => "8000");

		$options[] = array( "name" => __('Animation Speed', MAX_SHORTNAME),
							"desc" => __('The Speed of the popup animation to open an image.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_speed",
							"type" => "select",
							"options" => $pretty_speed_array,
							"std" => 'normal');

		$options[] = array( "name" => __('Theme', MAX_SHORTNAME),
							"desc" => __('Choose from one of the five supplied themes.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_theme",
							"type" => "select",
							"options" => $pretty_theme_array,
							"std" => "dark_square");

		$options[] = array( "name" => __('Show Lightbox Gallery', MAX_SHORTNAME),
							"desc" => __('Check, if you want to show the lightbox gallery of prettyPhoto.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_gallery_show",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Show Lightbox Title &amp; Description', MAX_SHORTNAME),
							"desc" => __('Check, if you want to show the title and description of a image open in prettyPhoto.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_title_show",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Play YouTube videos in HD', MAX_SHORTNAME),
							"desc" => __('Switch to "ON", if you want to play YouTube videos in HD by default.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_youtube_hd",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Show social tools', MAX_SHORTNAME),
							"desc" => __('Check, if you want to show the social tools of Twitter &amp; Facebook in the lightbox.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_pretty_social_tools",
							"type" => "checkbox",
							"std" => "false");

		$options[] = array( "type" => "close");


		// Create the Contact Tab
		$options[] = array( "name" => __('Contact', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_contact",
							"type" => "section",
							"icon" => "vcard.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Contact Form eMail', MAX_SHORTNAME),
							"desc" => __('Enter the eMail thats used to send a you a contact request via contact formular.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_email",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Show Sidebar Text', MAX_SHORTNAME),
							"desc" => __('Show the Info Sidebar Text on Contact Page on the right Sidebar', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_show_text",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Sidebar Info Headline', MAX_SHORTNAME),
							"desc" => __('Enter the headline of your contact information text.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_sidebar_header",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Sidebar Info Text', MAX_SHORTNAME),
							"desc" => __('Enter the text for your contact information text on the left.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_info",
							"type" => "textarea",
							"std" => "Aenean nisl orci, condimentum ultrices consequat eu, vehicula ac mauris. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean nisl orci, condimentum ultrices consequat eu, vehicula ac mauris. Ut adipiscing, leo nec. Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
							"rows"=> 6 );

		$options[] = array( "name" => __('Show Company Info', MAX_SHORTNAME),
							"desc" => __('Show company info text on the left of your contact page.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_show_info",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Company headline', MAX_SHORTNAME),
							"desc" => __('Enter the headline of your company contact information.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_info_header",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Adress Line 1', MAX_SHORTNAME),
							"desc" => __('Enter the first Adress Line.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_adress_1",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Adress Line 2', MAX_SHORTNAME),
							"desc" => __('Enter the second Adress Line.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_adress_2",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Phone', MAX_SHORTNAME),
							"desc" => __('Enter your Phone Number. Leave blank to hide.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_phone",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Fax', MAX_SHORTNAME),
							"desc" => __('Enter your Fax Number. Leave blank to hide.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_fax",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('Contact eMail', MAX_SHORTNAME),
							"desc" => __('Enter your Company E-Mail. Leave blank to hide.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_contact_info_email",
							"type" => "text",
							"std" => "");

		$options[] = array( "type" => "close");


		// Create the Footer Tab
		$options[] = array( "name" => __('Footer', MAX_SHORTNAME),
							"id"=> MAX_SHORTNAME."_header_footer",
							"type" => "section",
							"icon" => "layout_footer.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Copyright', MAX_SHORTNAME),
							"desc" => __('Enter your Copyright Text here.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_copyright",
							"type" => "select",
							"type" => "textarea",
							"std" => "copyright 2011 doitmax - Invictus. // Fullsize Background Wordpress Theme",
							"rows"=> 6 );

		$options[] = array( "name" => __("Show Social links", MAX_SHORTNAME),
							"desc" => __('Do you want to show social links in your site footer?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_social_use",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "type" => "close");


		// Create the Social Management Tab
		$options[] = array( "name" => __('Socials', MAX_SHORTNAME),
							"id"=>"header_social",
							"type" => "section",
							"icon" => "social.png");
		$options[] = array( "type" => "open");

		$options[] = array( "name" => __('Social Icons', MAX_SHORTNAME),
							"desc" => __('Choose the social icons that will be displayed in your footer.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_social_show",
							"type" => "socialinput",
							"options" => $social_array,
							"std" => array('true'));

		$options[] = array( "name" => __("Open Blank", MAX_SHORTNAME),
							"desc" => __('Do you want to show social links in a blank Browser window?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_social_show_blank",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Social Options',MAX_SHORTNAME),
							"id"=>"subhead_posts_socials",
							"type" => "subhead");

		$options[] = array( "name" => __("Activate Social Sharing", MAX_SHORTNAME),
							"desc" => __('Do you want to show social sharing buttons for each blog or photo post?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Facebook", MAX_SHORTNAME),
							"desc" => __('Show the Facebook "Like" Button?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_facebook",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Twitter", MAX_SHORTNAME),
							"desc" => __('Show the Twitter Button?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_twitter",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Google+", MAX_SHORTNAME),
							"desc" => __('Show the Google+ Button?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_google",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __("Pinterest", MAX_SHORTNAME),
							"desc" => __('Show the Pinterest Button?', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_pinterest",
							"type" => "checkbox",
							"std" => "true");

		$options[] = array( "name" => __('Social Share Caption', MAX_SHORTNAME),
							"desc" => __('Enter the text which is shown near the share buttons of your posts.',MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_text",
							"type" => "text",
							"std" => "<strong>Share my work!</strong>");

		$options[] = array( "name" => __('Facebook Options',MAX_SHORTNAME),
							"id"=>"subhead_facebook_opions",
							"type" => "subhead");

		$options[] = array( "name" => __('Facebook language', MAX_SHORTNAME),
							"desc" => __('Enter your language string for the like facebook button e.g. en_US, en_GB, de_DE, it_IT.', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_post_social_language",
							"type" => "text",
							"std" => "en_US");

		$options[] = array( "name" => __('fb:admins', MAX_SHORTNAME),
							"desc" => __('The Facebook admin id (<a href="https://developers.facebook.com/docs/insights/">More Info</a>)', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_social_fb_admins",
							"type" => "text",
							"std" => "");

		$options[] = array( "name" => __('fb:app_id', MAX_SHORTNAME),
							"desc" => __('The Facebook app_id (<a href="https://developers.facebook.com/docs/insights/">More Info</a>)', MAX_SHORTNAME),
							"id" => MAX_SHORTNAME."_social_fb_appid",
							"type" => "text",
							"std" => "");

		$options[] = array( "type" => "close");

		// Create the Pages tab
		$options[] = array( "name" => __('Page Backgrounds', MAX_SHORTNAME),
							"id"=>"page_backgrounds",
							"type" => "section",
							"icon" => "bg_image.png");
		$options[] = array( "type" => "open");

			$options[] = array( "name" => __('404 Background', MAX_SHORTNAME),
								"desc" => __('Upload your background for the 404 Page not found page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_page_background_404",
								"std" => "",
								"type" => "upload");

			$options[] = array( "name" => __('Tags Page', MAX_SHORTNAME),
								"desc" => __('Upload your background for the Tags page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_page_background_tag",
								"std" => "",
								"type" => "upload");

			$options[] = array( "name" => __('Archive', MAX_SHORTNAME),
								"desc" => __('Upload your background for the Archive page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_page_background_archive",
								"std" => "",
								"type" => "upload");

			$options[] = array( "name" => __('Search Results', MAX_SHORTNAME),
								"desc" => __('Upload your background for the Search results page.', MAX_SHORTNAME),
								"id" => MAX_SHORTNAME."_page_background_search",
								"std" => "",
								"type" => "upload");

		$options[] = array( "type" => "close");


		// update Some Options
		update_option('max_template', $options);
		update_option('max_themename', $themename);
		update_option('max_shortname', MAX_SHORTNAME);

	}

}

/*-----------------------------------------------------------------------------------*/
/*	Add some Filters to allow shortcodes in a Text Widget
/*-----------------------------------------------------------------------------------*/

add_filter('widget_text', 'shortcode_unautop');
add_filter('widget_text', 'do_shortcode');

/*-----------------------------------------------------------------------------------*/
/*	Add Custom password protect form
/*-----------------------------------------------------------------------------------*/

add_filter( 'the_password_form', 'custom_password_form' );

function custom_password_form() {
	global $post;
	$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
	$content = '<form class="protected-post-form" action="' . get_option('siteurl') . '/wp-login.php?action=postpass" method="post">
	<p>' . get_option_max('protected_login_text') . '</p>
	<label for="' . $label . '">' . __( "Your Password:", MAX_SHORTNAME ) . ' </label><input name="post_password" class="password-protect" id="' . $label . '" type="password" size="20" /><input type="submit" name="Submit" class="password-login" value="' . esc_attr__( "Login", MAX_SHORTNAME ) . '" />
	</form>
	';
	return $content;
}

/*-----------------------------------------------------------------------------------*/
/*	Add wmode transparent to embeded videos
/*-----------------------------------------------------------------------------------*/

add_filter('embed_oembed_html', 'add_video_wmode_transparent', 10, 3);

function add_video_wmode_transparent($html, $url, $attr) {
   if (strpos($html, "<embed src=" ) !== false) {
		$html = str_replace('</param><embed', '</param><param name="wmode" value="transparent"></param><embed wmode="transparent" ', $html);
		return $html;
   } else {
		return $html;
   }
}

/*-----------------------------------------------------------------------------------*/
/*	add a body class if fullwidth is activated
/*-----------------------------------------------------------------------------------*/

add_filter( 'body_class', 'max_add_fullwidth_body_class');

function max_add_fullwidth_body_class( $classes ) {
	global $meta;

	@$custom_fields = get_post_custom_values('_wp_page_template', $post_id );
	$page_template = $custom_fields[0];

	if ( ( isset($meta[MAX_SHORTNAME."_page_gallery_fullwidth"]) && $meta[MAX_SHORTNAME."_page_gallery_fullwidth"] == 'true' ) ||
		 $page_template == 'template-sidebar-fullwidth.php' )
		$classes[] = 'fullwidth-content';
	return $classes;
}


/*-----------------------------------------------------------------------------------*/
/*	function to filter posts for a custom query
/*-----------------------------------------------------------------------------------*/
add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
	if(is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
		$post_type = get_query_var('post_type');
		if($post_type) :
			$post_type = $post_type;
		else:
			$post_type = array('post','gallery'); // replace cpt to your custom post type
			$query->set('post_type', $post_type);
		endif;
	return $query;
	}
}

/*-----------------------------------------------------------------------------------*/
/*	function to add custom post types to the rss feed
/*-----------------------------------------------------------------------------------*/
function myfeed_request($qv) {
	if (isset($qv['feed']) && !isset($qv['post_type']))
		$qv['post_type'] = array('post', POST_TYPE_GALLERY);
	return $qv;
}
add_filter('request', 'myfeed_request');

/*-----------------------------------------------------------------------------------*/
/*	function to add featured images to the rss feed
/*-----------------------------------------------------------------------------------*/
function insertThumbnailRSS($content) {
	global $post;
	if ( has_post_thumbnail( $post->ID ) ){
		$content = '' . get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'alt' => get_the_title(), 'title' => get_the_title(), 'style' => 'float:right;' ) ) . '' . $content;
	}
	return $content;
}
add_filter('the_excerpt_rss', 'insertThumbnailRSS');
add_filter('the_content_feed', 'insertThumbnailRSS');

/*-----------------------------------------------------------------------------------*/
/*	function to add a category filter for the custom post type
/*-----------------------------------------------------------------------------------*/
function max_custom_post_type_filer() {

	global $typenow;

	$taxonomy = GALLERY_TAXONOMY;

	if( $typenow != "page" && $typenow != "post" ){

		$filters = array($taxonomy);

		foreach ($filters as $tax_slug) {
			$tax_obj = get_taxonomy($tax_slug);
			$tax_name = $tax_obj->labels->name;
			$terms = get_terms($tax_slug);
			echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
			echo "<option value=''>Show All $tax_name</option>";
			foreach ($terms as $term) { echo '<option value='. $term->slug, $_GET[$tax_slug] == $term->slug ? ' selected="selected"' : '','>' . $term->name .' (' . $term->count .')</option>'; }
			echo "</select>";
		}

	}

}
add_action( 'restrict_manage_posts', 'max_custom_post_type_filer' );

/*-----------------------------------------------------------------------------------*/
/*	function to add a prettyphoto data-rel to WordPress Gallery images
/*-----------------------------------------------------------------------------------*/
function max_prettyphoto_add($content) {
	$content = preg_replace("/<a/","<a data-rel=\"prettyPhoto[wpgallery]\"", $content, 1);
	return $content;
}
add_filter( 'wp_get_attachment_link', 'max_prettyphoto_add');

?>