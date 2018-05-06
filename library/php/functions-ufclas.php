<?php

/**
 * Display social network links only if they are set in the theme options, ignores the parent organization
 */

function ufclas_get_site_socialnetworks() {
	$social_networks = array(
		'facebook' => 'Facebook',
		'twitter' => 'Twitter',
		'youtube' => 'YouTube',
		'siteblog' => 'Blog',
	);
	
	foreach( $social_networks as $name => $title ){
		$link = of_get_option("opt_{$name}_url");
		if( !empty($link) ){
			echo "<li><a href=\"{$link}\" class=\"{$name} ir\" title=\"{$title}\">{$title}</a></li>";
		}
	}
}

/**
 * Change the Read More Text from the default
 */
function ufclas_excerpt_more( $more ){
	global $post;
	$custom_meta = get_post_custom($post->ID);
	$custom_button_text = ( isset($custom_meta['custom_meta_featured_content_button_text']) )? $custom_meta['custom_meta_featured_content_button_text'][0]:'';
	$label = ( empty($custom_button_text) )? "Read&nbsp;More":$custom_button_text;
	return '&hellip; <a href="'. get_permalink($post->ID) . '" title="'. get_the_title($post->ID) . '" class="read-more">' . $label . '</a>';   
}

add_filter('excerpt_more', 'ufclas_excerpt_more');
add_filter('the_content_more_link', 'ufclas_excerpt_more');

/**
 * Show either the_content or the_excerpt based on whether post contains the <!--more--> tag
 */
function ufclas_teaser_excerpt( $excerpt ){
	global $post;
	$has_teaser = (strpos($post->post_content, '<!--more') !== false);
	if ($has_teaser){
		// Remove extra formatting from the content
		return strip_tags( get_the_content(), '<a><br><br/>' );
	}
	else {
		return $excerpt;	
	}
}

add_filter( 'get_the_excerpt', 'ufclas_teaser_excerpt');

/**
 * The Events Calendar functions 
 */
function ufclas_events_widget_before_title(){
	global $post;
	
	// Change format of the start date for styling as an icon
	$tribe_post_month = tribe_get_start_date( $post, false, 'M' );
	$tribe_post_day = tribe_get_start_date( $post, false, 'j' );
	?>
    
    <div class="event-date">
    	<span class="event-month"><?php echo $tribe_post_month; ?></span>
        <span class="event-day"><?php echo $tribe_post_day; ?></span>
   	</div>
    
    <?php 
}
add_action( 'tribe_events_list_widget_before_the_event_title', 'ufclas_events_widget_before_title' );

/**
 * Show additional sizes in the insert image dialog
 */
function ufclas_show_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
		'page_header' => __( 'Page Header' ),
    ) );
}
add_filter( 'image_size_names_choose', 'ufclas_show_custom_sizes' );

/**
 * Adds Widgets in Homepage Feature Area
 **/
function ufclas_responsive_featured_widget_area() {
		
		$ufclas_responsive_home_featured_widgets = of_get_option("opt_home_featured_widgets");
		
		if($ufclas_responsive_home_featured_widgets){
			echo '<div id="featured-widget-right">';
		
			get_sidebar('home_featured_right');
		
			echo "</div><!-- end #featured-widget-right -->";
		}
}

// Homepage Featured Widget
function ufclas_responsive_widgets_init() {
    
	$ufclas_responsive_home_featured_widgets = of_get_option("opt_home_featured_widgets");
	
	if ($ufclas_responsive_home_featured_widgets) {
		register_sidebar (array(
			'name' => 'Home Featured Right',
			'id' => 'home_featured_right',
			'description' => 'Widgets in this area will be shown in the Header Featured area.',
			'before_widget' => '<div class="widget home_widget">',
			'after_widget' => '</div>',
			'before_title' => '<h3>',
			'after_title' => '</h3>',
		));
	}
}
add_action( 'widgets_init', 'ufclas_responsive_widgets_init' );

/**
 * Add styles and scripts using wp_enqueue_scripts instead of wp_head
 * @since 0.8.4
 */
function ufclas_responsive_styles_scripts(){
	global $detect_mobile;
	$opt_responsive = of_get_option('opt_responsive');
	$actionitem_alternate = of_get_option('opt_actionitem_altcolor');
	$collapse_sidebar_nav = of_get_option('opt_collapse_sidebar_nav');
	$ufl_menu = of_get_option('opt_ufl_menu');
	$mega_menu = of_get_option('opt_mega_menu');
	$story_stacker = of_get_option('opt_story_stacker');
	$opt_number_posts = of_get_option('opt_number_of_posts_to_show');
	$custom_js = of_get_option('opt_custom_js');
	
	/**
	 * Add Stylesheets
	 */

	wp_enqueue_style( 'ufl-responsive-print', get_template_directory_uri() . '/library/css/print.min.css', array(), NULL, 'print' );
	wp_enqueue_style( 'ufl-responsive', get_template_directory_uri() . '/style.min.css', array(), '0.8.5', 'all' );
	
	if ( $collapse_sidebar_nav ) {
		$custom_css  = '#sidebar-nav .children,#sidebar-nav ul ul{display:none;list-style-type:none}#sidebar-nav .current_page_ancestor .children,#sidebar-nav .current_page_item .children,#sidebar-nav .current_page_parent .children,#sidebar-nav .current_page_parent ul{display:block}';
		wp_add_inline_style('ufl-responsive', $custom_css);
  	}
	
	// Menu styles
	if ( function_exists('wpmega_init') ) {
		wp_enqueue_style( 'ufl-responsive-uber-menu', get_template_directory_uri() . '/library/css/uber-menu.min.css', array(), NULL, 'all' );
	} elseif ( $ufl_menu ){
		wp_enqueue_style( 'ufl-responsive-ufl-menu', get_template_directory_uri() . '/library/css/ufl-menu.min.css', array(), NULL, 'all' );
	} elseif ( $mega_menu ){
		wp_enqueue_style( 'ufl-responsive-mega-menu', get_template_directory_uri() . '/library/css/mega-menu.min.css', array(), NULL, 'all' );
	} else {
		wp_enqueue_style( 'ufl-responsive-navigation', get_template_directory_uri() . '/library/css/navigation.min.css', array(), NULL, 'all' );
	}
	
	// PrettyPhoto
	if ( !$detect_mobile ){
		wp_enqueue_style( 'ufl-responsive-prettyPhoto', get_template_directory_uri() . '/library/css/prettyPhoto.css', array(), NULL, 'screen' );
  	}  

	// Alternate color for Call to Action item used for warnings and emergency alerts.
	if ($actionitem_alternate){
		$custom_css  = '#header-actionitem{bottom:0;position:absolute;right:25px;border:1px solid #b50101;border-bottom:none;border-top:none;-moz-box-shadow:inset 0 1px 0 0 #f80101;-webkit-box-shadow:inset 0 1px 0 0 #f80101;box-shadow:inset 0 1px 0 0 #f80101;color:#fff;font-family:helvetica,arial,sans-serif;font-size:14px;font-weight:700;padding:7px 15px 5px;text-decoration:none;text-align:center;text-shadow:0 1px 1px #f80101;background:transparent url(' . get_template_directory_uri() . '/images/bg-custom-button-red.png) 0 0 repeat-x;width:auto}';
		wp_add_inline_style('ufl-responsive', $custom_css);
	}
	
	// Responsive
	if ($opt_responsive && $detect_mobile && !isset($_COOKIE["UFLmobileFull"])){
		wp_enqueue_style('ufl-responsive-mobile', get_template_directory_uri() . '/library/css/responsive.min.css');
	}
	
	/**
	 * Add Scripts
	 */
	 
	// Header Scripts
	wp_deregister_script('jquery'); //remove the built-in one first.
	wp_enqueue_script('jquery', "https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js", array(), NULL, false);
	wp_enqueue_script('modernizr', get_template_directory_uri() . '/library/js/modernizr-1.7.min.js', array('jquery'), NULL, false);
	
	// Footer Scripts
	wp_enqueue_script('cycle', get_template_directory_uri() . '/library/js/jquery.cycle.min.js', array('jquery'), NULL, true);
	wp_enqueue_script('hoverintent', get_template_directory_uri() . '/library/js/jquery.hoverIntent.minified.js', array('jquery'), NULL, true);
  	wp_enqueue_script('common-script', get_template_directory_uri() . '/library/js/script.min.js', array(), NULL, true);
	
	// institutional-nav.js, small file minified and added inline to reduce requests
	$inline_js = "/* institutional-nav.js */ \n";
	$inline_js .= '$(document).ready(function(){function t(){$(this).find(".sub-mega").stop().fadeTo("fast",1).show()}function i(){$(this).find(".sub-mega").stop().fadeTo("fast",0,function(){$(this).hide()})}var n={sensitivity:1,interval:50,over:t,timeout:500,out:i};$("#institutional-nav .sub-mega").css({opacity:"0"}),$("#institutional-nav li").hoverIntent(n)});';
	wp_add_inline_script('common-script', $inline_js);
	
	
	if ($opt_responsive){
        wp_enqueue_script('responsive-script', get_template_directory_uri() . '/library/js/responsive.min.js', array('common-script'), NULL, true);
    }
	
	// load menu scripts
	if ( $mega_menu ){
    	wp_enqueue_script('megamenu', get_template_directory_uri() . '/library/js/mega-menu.min.js', array('jquery', 'hoverintent'), NULL, true);
  	}
	elseif ( !$ufl_menu && !$opt_responsive ) {
		wp_enqueue_script('defaultmenu', get_template_directory_uri() . '/library/js/default-menu.min.js', array('jquery', 'hoverintent'), NULL, true);
	}
	else {
		wp_enqueue_script('responsivemenu', get_template_directory_uri() . '/library/js/responsive-menu.min.js', array(), NULL, true);
	}
	
	if ( !$detect_mobile ){
		wp_enqueue_script('pretty-photo', get_template_directory_uri() . '/library/js/jquery.prettyPhoto.js', array(), NULL, true);
		
		// Initialize prettyPhoto
		$inline_js = "/* prettyPhoto */ \n";
		$inline_js .= 'jQuery(function($){ $("a[rel^=\'prettyPhoto\']").prettyPhoto(); });';
		wp_add_inline_script('pretty-photo', $inline_js);
		
		// autoclear.js, small file minified and added inline to reduce requests
		$inline_js = "/* autoclear.js */ \n";
		$inline_js .= 'function init(){for(var t=jQuery,e=t("input#header-search-field"),i=0;i<e.length;i++)"text"==e[i].type&&(e[i].setAttribute("rel",e[i].defaultValue),e[i].onfocus=function(){return this.value!=this.getAttribute("rel")?!1:void(this.value="")},e[i].onblur=function(){return""!=this.value?!1:void(this.value=this.getAttribute("rel"))},e[i].ondblclick=function(){this.value=this.getAttribute("rel")})}document.childNodes&&(window.onload=init);';
		wp_add_inline_script('common-script', $inline_js);
	}
	else {
		// a conditional statement to add flexible slider if responsive theme is active		
		wp_enqueue_style('flexslider', get_template_directory_uri() .'/library/css/flexslider.css', array(), NULL, 'screen');
		wp_enqueue_script('flexslider', get_template_directory_uri().'/library/js/jquery.flexslider-min.js', array('jquery'), NULL, true);
		wp_enqueue_script('autoclear_reponsive', get_template_directory_uri() . '/library/js/autoclear-responsive.min.js', false, NULL, true);
	}
	
	if (!$detect_mobile || isset($_COOKIE["UFLmobileFull"])){
		// load story-stacker script
		if($story_stacker) {
			wp_enqueue_script('storystacker', get_template_directory_uri() . '/library/js/story-stacker.min.js', array('jquery'), NULL, true);
		}
	  
	  	// load slider script
		if(($opt_number_posts > '1') && !($story_stacker)) {
			wp_enqueue_script('featureslider', get_template_directory_uri() . '/library/js/feature-slider.min.js', array('jquery'), NULL, true);
		}
	}
	
	if ( !empty($custom_js) ){
		wp_add_inline_script('common-script', "/* custom js */ \n" . $custom_js);
	}
	
	if (is_singular('post')){
		wp_enqueue_script('comment-reply'); // loads the javascript required for threaded comments 
		wp_enqueue_script('plusone', "https://apis.google.com/js/plusone.js");
		wp_enqueue_script('twitter', "https://platform.twitter.com/widgets.js");
	}

	// Remove plugin styles
	if ( is_home() || is_front_page() ){
		wp_dequeue_style('jquery-issuem-flexslider');
		wp_dequeue_style('tablepress-default');
		wp_dequeue_script('jquery-issuem-flexslider');
	}
}
add_action('wp_enqueue_scripts', 'ufclas_responsive_styles_scripts');

/**
 * Inline scripts and head content
 * @since 0.8.4
 */
function ufclas_responsive_inline_head(){
	global $detect_mobile, $opt_responsive;
	$custom_css = of_get_option('opt_custom_css');
  	$custom_responsive_css = of_get_option('opt_responsive_css');
	
	if ($opt_responsive && $detect_mobile && !isset($_COOKIE["UFLmobileFull"])) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";	
	}
	
	//Custom CSS
	if(!empty($custom_css)) {
		echo '<style type="text/css">' . $custom_css . '</style>'."\n";
	}
	
	//Custom Responsive CSS
	if (!empty($custom_responsive_css) && $detect_mobile) {
		if (!isset($_COOKIE["UFLmobileFull"])){
			echo '<style type="text/css">' . $custom_responsive_css . '</style>' . "\n";
		}
	}
	
	if (current_user_can( 'manage_options' )) {	
		echo '<style type="text/css">#institutional-nav-min {top:83px;} #responsive-header-search-wrap{top:118px;} #responsive-menu-toggle{top:89px;}</style>';
	}
}
add_action('wp_head', 'ufclas_responsive_inline_head');

/**
 * Template tag used to hook content after the <body> tag
 *
 * @since 0.8.9
 */
function ufclas_responsive_body_top(){
	do_action('ufclas_responsive_body_top');
}

/**
 * Template tag used to hook content after the <head> tag
 *
 * @since 0.8.9
 */
function ufclas_responsive_head_top(){
	do_action('ufclas_responsive_head_top');
}

/**
 * Displays Facebook Like/Share button on posts
 *
 * @since 0.8.9
 */
function ufclas_responsive_facebook_like(){
	if ( is_singular('post') ){
		include get_template_directory() . '/library/php/facebook-like-button.php';
	}
}
add_action('ufclas_responsive_body_top', 'ufclas_responsive_facebook_like');

/**
 * Displays Facebook Open graph meta
 *
 * @since 0.8.9
 */
function ufclas_responsive_facebook_meta(){
	if ( is_singular('post') || is_page() ): ?>
		<meta property="og:url"           content="<?php the_permalink(); ?>" />
		<meta property="og:type"          content="website" />
		<meta property="og:title"         content="<?php the_title(); ?>" />
		<meta property="og:description"   content="<?php the_excerpt(); ?>" />
		<meta property="og:image"         content="<?php the_post_thumbnail_url('medium'); ?>" />
	<?php
	endif;
}
add_action('ufclas_responsive_head_top', 'ufclas_responsive_facebook_meta');
