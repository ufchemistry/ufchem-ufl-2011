<?php
/* ----------------------------------------------------------------------------------- */
/* Options Framework Theme -- leave at top of functions.php
/* ----------------------------------------------------------------------------------- */

if (!function_exists('optionsframework_init')) {

  /* Set the file path based on whether the Options Framework Theme is a parent theme or child theme */

  if (STYLESHEETPATH == TEMPLATEPATH) {
    define('OPTIONS_FRAMEWORK_URL', TEMPLATEPATH . '/admin/');
    define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/admin/');
  } else {
    define('OPTIONS_FRAMEWORK_URL', STYLESHEETPATH . '/admin/');
    define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('stylesheet_directory') . '/admin/');
  }

  require_once (OPTIONS_FRAMEWORK_URL . 'options-framework.php');
}

//Mobile detection script
include 'library/php/Mobile_Detect.php';
$detect = new Mobile_Detect();
$detect_mobile = $detect->isMobile();
$opt_responsive = of_get_option('opt_responsive');
$disabled_global_elements = of_get_option('opt_disable_global_elements');

/* ----------------------------------------------------------------------------------- */
/* Sharepoint Calendar integration
/* ----------------------------------------------------------------------------------- */
// This is currently buggy, awaiting fixes
//include_once ('library/php/sharepoint-calendar/sharepoint-calendar.php');


/* ----------------------------------------------------------------------------------- */
/* Add Tags Metabox to Pages
/* ----------------------------------------------------------------------------------- */

// Shared tag taxonomy - Posts AND Pages -- How to change label???
function ufandshands_page_tags() {
  register_taxonomy_for_object_type('post_tag', 'page');
}
add_action('init', 'ufandshands_page_tags');

function ufandshands_page_cats() {
  register_taxonomy_for_object_type('category', 'page');
}
add_action('init', 'ufandshands_page_cats');

// When displaying a tag archive, also show pages
function ufandshands_tags_archives($wp_query) {
	if ( $wp_query->get('tag') )
		$wp_query->set('post_type', 'any');
}
add_action('pre_get_posts', 'ufandshands_tags_archives');

add_action( 'init', 'ufandshands_tag_labels');

// Rename 'Post Tags' to 'Tags'
function ufandshands_tag_labels()
{
    global $wp_taxonomies;

    //  http://codex.wordpress.org/Function_Reference/register_taxonomy
    $wp_taxonomies['post_tag']->labels = (object)array(
        'name' => 'Tags',
        'singular_name' => 'Tags',
        'search_items' => 'Search Tags',
        'popular_items' => 'Popular Tags',
        'all_items' => 'All Tags',
        'parent_item' => null, // Tags aren't hierarchical
        'parent_item_colon' => null,
        'edit_item' => 'Edit Tag',
        'update_item' => 'Update Tag',
        'add_new_item' => 'Add new Tag',
        'new_item_name' => 'New Tag Name',
        'separate_items_with_commas' => 'Separate tags with commas',
        'add_or_remove_items' => 'Add or remove tags',
        'choose_from_most_used' => 'Choose from the most used tags',
        'menu_name' => 'Tags'
    );

    $wp_taxonomies['post_tag']->label = 'Tags';
}


/* ----------------------------------------------------------------------------------- */
/* Custom TinyMCE styles
/* ----------------------------------------------------------------------------------- */

add_theme_support('editor_style');
add_editor_style('library/css/editor-styles.css'); // custom css styles in the content editor


/* ----------------------------------------------------------------------------------- */
/* Custom TinyMCE buttons
/* ----------------------------------------------------------------------------------- */
include_once('library/php/tinymce-custombuttons/functions-custombuttons.php');

// set the "kitchen sink" visible as default
function unhide_kitchensink( $args ) {
	$args['wordpress_adv_hidden'] = false;
	return $args;
}
add_filter( 'tiny_mce_before_init', 'unhide_kitchensink' );




/* ----------------------------------------------------------------------------------- */
/* Add Lightbox rel attribute for Galleries
/* ----------------------------------------------------------------------------------- */

function ufandshands_lightbox_rel ($content) {
  global $post;
	$pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";
    $replacement = '<a$1href=$2$3.$4$5 rel="prettyPhoto['.$post->ID.']"$6>$7</a>';
    $rel_content = preg_replace($pattern, $replacement, $content, -1, $count);
    if ($count > 1) {
      $content = $rel_content;
    }
    return $content;
}
add_filter('the_content', 'ufandshands_lightbox_rel', 12);
add_filter('get_comment_text', 'ufandshands_lightbox_rel');


/* ----------------------------------------------------------------------------------- */
/* Misc. Header and Footer Items -- helps keep the template header and footer cleaner
/* ----------------------------------------------------------------------------------- */

// Enables post and comment RSS feed links to head
add_theme_support('automatic-feed-links');

function ufandshands_header_adder() {
  global $detect_mobile;
  global $post;
  
  $bloginfo_url = get_stylesheet_directory_uri();
  $bloginfo_name = get_bloginfo('name');
  $parent_org = of_get_option('opt_parent_colleges_institutes');

  // Site <title> logic
  echo "<title>";
  if (!is_front_page()) {
    echo wp_title('&raquo;', false, 'right') . " " . $bloginfo_name;
  } else { //if we are on the home page, only show the name of the site
    echo $bloginfo_name;
  }
  if ( ($bloginfo_name != $parent_org) && ($parent_org == 'University of Florida')) {
    echo " &raquo; " . $parent_org;
  } elseif ( ($parent_org != 'None') && ($bloginfo_name != 'University of Florida') ) {
    echo " &raquo; " . $parent_org . " &raquo; University of Florida";
  }
  echo "</title>\n";

  echo "<link rel='apple-touch-icon' href='" . $bloginfo_url . "/apple-touch-icon.png'>\n";
  
  //custom fav icon based on the parent organization
  //default favicon.ico is the '&'
  switch ($parent_org) {
    case "UF Academic Health Center":
      echo "<link rel='shortcut icon' href='" . $bloginfo_url . "/favicon-ahc.ico' />\n";
      break;
    case "Shands HealthCare":
      echo "<link rel='shortcut icon' href='" . $bloginfo_url . "/favicon-shands.ico' />\n";
      break;
    default:
      echo "<link rel='shortcut icon' href='" . $bloginfo_url . "/favicon.ico' />\n";
  }

  // meta description - display post/page excerpt for SEO 
  if (is_page() || is_single()) {
    $current_post = get_post($post->ID);
    $meta_excerpt = $current_post->post_excerpt;
    if (!empty($meta_excerpt)) {
      echo '<meta name="description" content="' . htmlentities( $meta_excerpt ) . '" />'."\n";
    }
  }
  
	// Facebook Insights fb:admins code allows you to enable this site to be analyzed by Facebook Insights
	// http://www.virante.com/blog/2011/02/03/how-to-track-shares-from-facebook-pages/
	$facebookinsights = of_get_option('opt_facebook_insights');
	if ($facebookinsights) {
	echo "<meta property=\"fb:admins\" content=\"".$facebookinsights."\" />";  
	}
	
	// For mobile, set a cookie so that pages from mobile browsers will not be cached
	if ( $detect_mobile && of_get_option('opt_responsive') && !isset($_COOKIE["UFLmobileFull"])) {
		setcookie("UFLmobileMobile", "enabled", time()+2592000, "/");
	}
}
add_action('wp_head', 'ufandshands_header_adder');

/* ----------------------------------------------------------------------------------- */
/* 	Small Misc. Unrelated Directives
/* ----------------------------------------------------------------------------------- */

add_theme_support('post-thumbnails'); // Enable 'Featured Image' box for this theme
add_filter('wp_feed_cache_transient_lifetime', create_function('$a', 'return 1800;')); // Change cache times (mostly used by RSS)
add_editor_style(); // Use custom CSS in the content editor -- FOLLOWUP WITH ACTUAL STYLES
add_filter('widget_text', 'do_shortcode'); // Enable shortcodes in widgets

remove_action('wp_head', 'wlwmanifest_link'); // Removes Windows Live Writer Link
remove_action('wp_head', 'wp_generator'); // Removes WP version from head
//remove_action( 'wp_head', 'rsd_link' ); // Removes the link to the Really Simple Discovery service endpoint, EditURI link
remove_action( 'wp_head', 'index_rel_link' ); // index link




/* ----------------------------------------------------------------------------------- */
/* 	WIDGET INCLUDES -- All functions related to Widgets should be here
/* ----------------------------------------------------------------------------------- */

include('library/php/include-widgets.php');


/* ----------------------------------------------------------------------------------- */
/* 	Change the 'from name' in emails to match blog name
/* ----------------------------------------------------------------------------------- */

add_filter('wp_mail_from_name', 'new_mail_from_name');

function new_mail_from_name($old) {
  $blog_name = get_bloginfo('name');
  return $blog_name;
}


/* ----------------------------------------------------------------------------------- */
/* 	Removing some boxes from the write screens; less clutter
/* ----------------------------------------------------------------------------------- */

function remove_post_custom_fields() {
  remove_meta_box('postcustom', 'page', 'normal');
  remove_meta_box('authordiv', 'page', 'normal');
  remove_meta_box('commentstatusdiv', 'page', 'normal');
  remove_meta_box('commentsdiv', 'page', 'normal');
  remove_meta_box( 'postimagediv', 'page', 'normal' );
  // DOES NOT LET YOU RENAME PAGE SLUGS -- BUG   remove_meta_box( 'slugdiv' , 'page' , 'normal' ); 
  remove_meta_box('postcustom', 'post', 'normal');
  remove_meta_box('trackbacksdiv', 'post', 'normal');
}
add_action('admin_menu', 'remove_post_custom_fields');


function remove_featured_image_field() {
    remove_meta_box( 'postimagediv','page','side' );
}
//add_action('do_meta_boxes', 'remove_featured_image_field');

/* ----------------------------------------------------------------------------------- */
/* 	Gravity Forms custom code section
/* ----------------------------------------------------------------------------------- */


/* ----------------------------------------------------------------------------------- */
/* 	Search Text
/* ----------------------------------------------------------------------------------- */

function ufandshands_search_text() {
  $blog_name = get_bloginfo('name');
  $blog_name_length = strlen($blog_name);

  if ($blog_name_length > 20) {
    $blog_name = 'Search UF Web';
  } else {
    $blog_name = 'Search ' . $blog_name;
  }

  echo $blog_name;
}


/* ----------------------------------------------------------------------------------- */
/* 	Members Only
/* ----------------------------------------------------------------------------------- */

function ufandshands_members_only() {
  $ip = $_SERVER['REMOTE_ADDR'];
  if ((preg_match("/(159\.178\.[0-9]{1,3}\.[0-9]{1,3})/", $ip) > 0 || preg_match("/(128\.227\.[0-9]{1,3}\.[0-9]{1,3})/", $ip) > 0 || preg_match("/(10\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/", $ip) > 0) || is_user_logged_in()) {
    return true;
  } else {
    return false;
  }
}


/* ----------------------------------------------------------------------------------- */
/* 	Paginator
/* ----------------------------------------------------------------------------------- */

function ufandshands_pagination($pages = '', $range = 4) {
  $showitems = ($range * 2) + 1;

  global $paged;
  if (empty($paged))
    $paged = 1;

  if ($pages == '') {
    global $wp_query;
    $pages = $wp_query->max_num_pages;
    if (!$pages) {
      $pages = 1;
    }
  }

  if (1 != $pages) {
    echo "<div class=\"pagination\"><span class=\"page-of\">Page " . $paged . " of " . $pages . "</span>";
    if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
      echo "<a href='" . get_pagenum_link(1) . "'>&laquo; First</a>";
    if ($paged > 1 && $showitems < $pages)
      echo "<a href='" . get_pagenum_link($paged - 1) . "'>&lsaquo; Previous</a>";

    for ($i = 1; $i <= $pages; $i++) {
      if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
        echo ($paged == $i) ? "<span class=\"current\">" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class=\"inactive\">" . $i . "</a>";
      }
    }

    if ($paged < $pages && $showitems < $pages)
      echo "<a href=\"" . get_pagenum_link($paged + 1) . "\">Next &rsaquo;</a>";
    if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages)
      echo "<a href='" . get_pagenum_link($pages) . "'>Last &raquo;</a>";
    echo "</div>\n";
  }
}


/*-----------------------------------------------------------------------------------*/
/*	Individual Comment Styling
/*-----------------------------------------------------------------------------------*/

function ufandshands_comment($comment, $args, $depth) {

    $is_by_author = false;

    if($comment->comment_author_email == get_the_author_meta('email')) {
        $is_by_author = true;
    }

    $GLOBALS['comment'] = $comment; ?>
   <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
     
     <div id="comment-<?php comment_ID(); ?>">
      <div class="line"></div>
      <?php echo get_avatar($comment, $size='40'); ?>
      <div class="comment-author vcard">
         <?php printf(__('<cite class="fn">%s</cite>'), get_comment_author_link()); ?>
         <?php if($is_by_author) { ?><span class="author-tag"><?php echo 'Author'; ?></span><?php } ?>
      </div>

      <div class="comment-meta commentmetadata">
        <a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a>
        <?php edit_comment_link(__('(Edit)'),'  ','') ?><?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
      </div>
      
      <?php if ($comment->comment_approved == '0') : ?>
         <em class="moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
         <br />
      <?php endif; ?>
	  
      <div class="comment-body">
      <?php comment_text() ?>
	  </div>
      
     </div>

<?php
}


/*-----------------------------------------------------------------------------------*/
/*	Separated Pings Styling
/*-----------------------------------------------------------------------------------*/

function tz_list_pings($comment, $args, $depth) {
       $GLOBALS['comment'] = $comment; ?>
<li id="comment-<?php comment_ID(); ?>"><?php comment_author_link(); ?>
<?php }


/* ----------------------------------------------------------------------------------- */
/* 	Thumbnail Generator
/* ----------------------------------------------------------------------------------- */

function ufandshands_post_thumbnail($preset, $alignment, $thumb_w, $thumb_h) {

  global $post;
  
  // 1. Check for featured image

  if (has_post_thumbnail()) {
    echo "<a href=\"" . get_permalink() . "\">";
    the_post_thumbnail($preset, array('class' => $alignment));
    echo "</a>";
    return true;

    // 2. Check for attached image or body content image
  } else {
    $img = '';
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'numberposts' => -1,
        'order' => 'ASC',
        'post_status' => null,
        'post_parent' => $post->ID
    );
    $attachments = get_posts($args);
    if ($attachments) {
      foreach ($attachments as $attachment) {
        $img = wp_get_attachment_image_src($attachment->ID, 'thumbnail');
        break;
      }
    } else {
      $pattern = '/src=[\'"]?([^\'" >]+)[\'" >]/';
      preg_match($pattern, $post->post_content, $img_matches);
	  $edited_image_reg = false;
	  if( !empty( $img_matches )){
		  $trimmed_img_matches = trim($img_matches[0], "src=");
		  $image_file_extension = end(explode(".", $trimmed_img_matches));
		  $chopend_img_matches = substr($trimmed_img_matches, 0, -12);
	
		  // Only works on RE-SIZED images
		  $edited_image_reg_pattern = '/[0-9][0-9][0-9]x[0-9][0-9][0-9]/';
		  if ($c = preg_match_all($edited_image_reg_pattern, $trimmed_img_matches, $matches)) {
			$edited_image_reg = true;
		  }
	  }
    }

    // Display Thumbnail
    if (!empty($img)) {
      ?>
      <a href="<?php the_permalink() ?>"><img src="<?php echo $img[0]; ?>" class="<?php echo $alignment; ?>" alt="<?php the_title(); ?>" /></a>
      <?php
      return true;
    } elseif ($edited_image_reg) {

      if (strlen($img_matches[0]) > 7) {

        // width of the thumbnails
        $thumbwidth = $thumb_w;

        //  height of the thumbnails
        $thumbheight = $thumb_h;
        ?>
        <a href="<?php the_permalink() ?>"><img class="<?php echo $alignment ?>" src=<?php echo $chopend_img_matches . $thumbwidth . "x" . $thumbheight . "." . $image_file_extension; ?>" alt="<?php the_title(); ?>" /></a>
      <?php
        return true;
      }
    } else {
        return false;
    }
  }
}


/* ----------------------------------------------------------------------------------- */
/* 	ShortCodes: includes our custom short code library
/* ----------------------------------------------------------------------------------- */

include('library/php/shortcodes.php');


/* ----------------------------------------------------------------------------------- */
/* 	Custom Thumbnail Sizes
/* ----------------------------------------------------------------------------------- */

if (function_exists('add_image_size')) {
  add_image_size('full-width-thumb', 930, 325, array('center', 'top'));
  add_image_size('half-width-thumb', 450, 305, array('center', 'top'));
  add_image_size('slider-scrubber-thumb', 130, 100, true);
  add_image_size('page_header', 680, 220, array('center', 'top'));
  add_image_size('stacker-thumb', 630, 298, array('center', 'top'));
  add_image_size('stacker-thumb-small', 67, 67, true); 
  add_image_size('ufl_menu_thumb', 203, 96, true);
  add_image_size('ufl_post_thumb', 600, 210, false);
}



/* ----------------------------------------------------------------------------------- */
/* 	Custom Write Panels Meta Boxes
/* ----------------------------------------------------------------------------------- */

include_once ("library/php/functions-metabox.php");


/*-------------------------------------------------------------------------------------*/
/*	Change Default Excerpt Length
/*-------------------------------------------------------------------------------------*/

function ufandshands_excerpt_length($length) {
  return 30;
}
add_filter('excerpt_length', 'ufandshands_excerpt_length');


/* ----------------------------------------------------------------------------------- */
/* Excerpts for SEO
/* ----------------------------------------------------------------------------------- */

add_action('init', 'ufandshands_add_excerpts_to_pages');

function ufandshands_add_excerpts_to_pages() {
  add_post_type_support('page', 'excerpt');
}


/* ----------------------------------------------------------------------------------- */
/* Custom Menu Registration
/* ----------------------------------------------------------------------------------- */

add_action('init', 'ufandshands_register_menus');

function ufandshands_register_menus() {
  register_nav_menus(
          array(
              'header_links' => 'Header Links',
              'rolebased_nav' => 'Role-Based Navigation',
              'main_menu' => 'Main Menu Override'
          )
  );
  
  if (function_exists('wpmega_init')) { //uber menu, do not enable
    /*register_nav_menus(
          array(
              'main_menu' => 'Main Menu'
          )
	);*/
  }
}


/* ----------------------------------------------------------------------------------- */
/* Formerlly: Social Media URLs
/* Now: Stores meta data about the institutional groups that we may need to access at a later date.
/* ----------------------------------------------------------------------------------- */

$college_inst_data = array(
    "University of Florida" => array(
        "facebook" => "https://www.facebook.com/uflorida",
        "twitter" => "https://twitter.com/@uf",
        "youtube" => "https://www.youtube.com/user/universityofflorida/"
	)
);

function ufandshands_get_socialnetwork_url($type) {

  global $college_inst_data;

  $parent_org = of_get_option("opt_parent_colleges_institutes");
  $parent_org_socialnetwork = $college_inst_data[$parent_org][$type];

  $socialnetwork_type = of_get_option("opt_" . $type . "_url");

  if (!empty($socialnetwork_type)) {
    $output = $socialnetwork_type;
  } elseif (!empty($parent_org_socialnetwork)) {
    $output = $parent_org_socialnetwork;
  } elseif ($type == "facebook") {
    $output = $college_inst_data["University of Florida"][$type];
  } elseif ($type == "twitter") {
    $output = $college_inst_data["University of Florida"][$type];
  } elseif ($type == "youtube") {
    $output = $college_inst_data["University of Florida"][$type];
  }

  echo $output;
}


/* ----------------------------------------------------------------------------------- */
/* Custom Sidebar Navigation
/* ----------------------------------------------------------------------------------- */

function ufandshands_sidebar_navigation($post) {
  $sidebar_nav_walker = new ufandshands_sidebar_nav_walker;
  $post_ancestors = get_post_ancestors($post);

  if ( count($post_ancestors) ) {
    $top_page = array_pop($post_ancestors);
    
    $children = wp_list_pages(array(
        'walker' => $sidebar_nav_walker,
        'title_li' => '',
        'child_of' => $top_page,
        'echo' => false
            ));
  } elseif (is_page()) {
    $children = wp_list_pages(array(
        'walker' => $sidebar_nav_walker,
        'title_li' => '',
        'child_of' => $post->ID,
        'echo' => false,
        'depth' => 3,
            ));
    $sect_title = the_title('', '', false);
  }
  else {
	 $children = wp_list_pages(array(
      'walker' => $sidebar_nav_walker,
      'title_li' => '',
      'child_of' => $post->ID,
      'echo' => false
          )); 
  }
  
  if ($children || is_active_sidebar('page_sidebar')) {
      return $children;
  }
}


/* ----------------------------------------------------------------------------------- */
/* Custom Breadcrumb Function
/* ----------------------------------------------------------------------------------- */

function ufandshands_breadcrumbs() {
  global $post;
  if (is_page() && !is_front_page()) {
    $breadcrumb = "<nav id='breadcrumb'><div class='container'>";
    $breadcrumb .= '<a href="' . get_bloginfo('url') . '">Home</a> ';
    $post_ancestors = get_post_ancestors($post);
    if ($post_ancestors) {
      $post_ancestors = array_reverse($post_ancestors);
      foreach ($post_ancestors as $crumb)
        $breadcrumb .= ' <a href="' . get_permalink($crumb) . '">' . get_the_title($crumb) . '</a> ';
    }
    $breadcrumb .= '<strong>' . get_the_title() . '</strong>';
    $breadcrumb .= "</div></nav>";

    echo $breadcrumb;
  }
}

/* ----------------------------------------------------------------------------------- */
/* Imports Verbose Walker Classes for Navigation (Primary, Sidebar, Role-Based)
/* ----------------------------------------------------------------------------------- */

include('library/php/walkers.php');


/* ----------------------------------------------------------------------------------- */
/* <h1>TITLE</h1> functions
/* ----------------------------------------------------------------------------------- */

function ufandshands_content_title() {
	global $post;
		$custom_meta = get_post_custom($post->ID);
    
   if(is_page($post->ID)) {
		$custom_subtitle = ( isset($custom_meta['custom_meta_page_subtitle']) )? $custom_meta['custom_meta_page_subtitle'][0]:null;
    	$custom_title_override = ( isset($custom_meta['custom_meta_page_title_override']) )? $custom_meta['custom_meta_page_title_override'][0]:null;
   } elseif(is_single($post->ID)) {
    	$custom_subtitle = ( isset($custom_meta['custom_meta_post_subtitle']) )? $custom_meta['custom_meta_post_subtitle'][0]:null;
   } else {
     return;
   }
		echo "<h1>";
      if (isset($custom_title_override)) {
        echo $custom_title_override;
      } else {
        echo get_the_title();
      }
      if(isset($custom_subtitle)) :
        echo "<span class='medium-blue'>: ";
          echo $custom_subtitle;
        echo "</span>";
      endif;
			
		echo "</h1>";

}


/* ----------------------------------------------------------------------------------- */
/* Title (<title></title> Functions
/* ----------------------------------------------------------------------------------- */

function ufandshands_site_title_size() {
    $site_title_size = of_get_option("opt_title_size");
	$site_title_padding = of_get_option("opt_title_pad");
	$site_tagline_size = of_get_option("opt_tagline_size");
	
	if (!empty($site_title_size) || !empty($site_title_padding) || !empty($site_tagline_size)) {
		$site_title_embedded_css = "<style type='text/css'>";
		if (!empty($site_title_size)) {
		  $site_title_embedded_css .= "#header-title h1#header-title-text, #header-title h2#header-title-text { font-size: " . $site_title_size . "em !important; }";
		}
		if (!empty($site_title_padding)) {
		  //DISABLED - padding on wrong side of the street $site_title_embedded_css .= "header #header-title h1#header-title-text, #header-title h2#header-title-text { padding-bottom: " . $site_title_padding . "px !important; }";
		  $site_title_embedded_css .= "header #header-title h2#header-title-tagline, #header-title h3#header-title-tagline { padding-top: " . $site_title_padding . "px !important; }";
		}
		if (!empty($site_tagline_size)) {
		  $site_title_embedded_css .= "header #header-title h2#header-title-tagline, #header-title h3#header-title-tagline { font-size: " . $site_tagline_size . "em !important; }";
		}
		$site_title_embedded_css .= "</style>";
	
		echo $site_title_embedded_css;
	}
}
add_action('wp_head', 'ufandshands_site_title_size');

// Alternate Logo
// Logic has to run outside of primary title function because function gets called AFTER wp_head is processsed
function ufandshands_alternate_logo() {
    $ufandshands_alternate_logo = of_get_option("opt_alternative_site_logo");
	
	if (!empty($ufandshands_alternate_logo)) {
	
		$alternative_site_logo_height = of_get_option("opt_alternative_site_logo_height");
		$alternative_site_logo_width = of_get_option("opt_alternative_site_logo_width");
		$alternate_logo_css = "<style type='text/css'>";
		$alternate_logo_css .= "header #header-title #header-parent-organization-logo.none, header #header-title #header-parent-organization-logo.alt {
									display: block !important;
									float: left;
									background-color: transparent;
									background-image: url(" . $ufandshands_alternate_logo . ");
									background-repeat: no-repeat;
									background-attachment: scroll;
									height: " . $alternative_site_logo_height . "px;
									width: " . $alternative_site_logo_width . "px;
									margin-right: 10px;	}";
		$alternate_logo_css .= "</style>";
	
		echo $alternate_logo_css;
  }
}
add_action('wp_head', 'ufandshands_alternate_logo');


// Title and tagline generation function
function ufandshands_site_title() {

  // Adds emphasis to certain words
  function ufandshands_emphasis_adder($emphasis_text) {
    $emphasis_pattern = array("/ of /", "/ for /", "/ in /", "/ the /", "/ to /", "/ by /");
    $emphasis_replacement = array(" <em>of</em> ", " <em>for</em> ", " <em>in</em> ", " <em>the</em> ", " <em>to</em> ", " <em>by</em> ");
    $emphasis_text = preg_replace($emphasis_pattern, $emphasis_replacement, $emphasis_text);

    return $emphasis_text;
  }

  $site_description = ufandshands_emphasis_adder(get_bloginfo('description'));
  $site_title = ufandshands_emphasis_adder(get_bloginfo('title'));
  $ufandshands_alternate_logo = of_get_option("opt_alternative_site_logo");

  // Begin to build $title string
  $title = "<div id='header-title' class='alpha omega span-15'><a href='" . get_bloginfo('url') . "' title='" . get_bloginfo('name') . "'>";

  // Build logo of parent organization
  $parent_org = of_get_option("opt_parent_colleges_institutes");
  if ($parent_org == "University of Florida" && empty($ufandshands_alternate_logo)) {
    $parent_org_logo = "uf";
    $header_title_text_right_class_size = "";
  } elseif ($parent_org == "University of Florida" && !empty($ufandshands_alternate_logo)) {
    $parent_org_logo = "alt";
    $header_title_text_right_class_size = " ";
  } elseif ($parent_org == "None") {
    $parent_org_logo = "none";
    $header_title_text_right_class_size = " ";
  } else {
    $parent_org_logo = "uf";
    $parent_org = "University of Florida";
    $header_title_text_right_class_size = "";
  }

  $title .= "<h3 id='header-parent-organization-logo' class='ir " . $parent_org_logo . "'>" . $parent_org . "</h3>"; // logos
  $title .= "<div id='header-title-text-right' class='alpha omega " . $header_title_text_right_class_size . " " . $parent_org_logo . "'>"; // logos

  if (get_bloginfo('title')=="University of Florida") { // UF custom title begin
  
		$title .= "<h1 id='uf-title' class='ir' >University of Florida</h1>";
  
  } else {
  // If we are on the front page, make the site a <h1>, otherwise make it a <h2> (...and description <h2>-><h3>)	
	  if (is_front_page()) {
		$title .= "<h1 id='header-title-text' class='palatino'>" . $site_title . "</h1>";
		if (!empty($site_description)) {
		  $title .= "<h2 id='header-title-tagline' class='palatino'>" . $site_description . "</h2>";
		}
	  } else {
		$title .= "<h2 id='header-title-text' class='palatino not-front'>" . $site_title . "</h2>";
		if (!empty($site_description)) {
		  $title .= "<h3 id='header-title-tagline' class='palatino not-front'>" . $site_description . "</h3>";
		}
	  }
  }// end UF custom title 

  // Close our tags
  $title .= "</div></a></div>";

  // Display title
  echo $title;
}

// I'm guessing they did this as a plugin because I can't find any other usage of the theme option
function ufl_google_analytics() {
  $analytics_acct = of_get_option( 'opt_analytics_acct' );

  if ( !empty( $analytics_acct ) ) {
    include get_template_directory() . '/library/php/google-analytics.php';
  }
}

add_action( 'ufclas_responsive_body_top', 'ufl_google_analytics' );

/* ufl_site_last_updated
*
* This will check the modified_date of all published posts and give you the most recent date.
* This replaces the get_blog_info MU-only function so that the theme can be used on non-MU installs.
*
*/
function ufl_site_last_updated($d = '') {
	$recent = new WP_Query("showposts=1&orderby=modified&post_status=publish&post_type=any");
	if ( $recent->have_posts() ) {
		while ( $recent->have_posts() ) {
			$recent->the_post();
			$last_update = get_the_modified_date($d);
		}
		echo $last_update;
	}
}


function ufl_check_email_address($email) {
    // Uses very basic built-in email validation
	if( false === filter_var($email, FILTER_VALIDATE_EMAIL) ){
		return false;
	}
	else{
		return true;	
	}
}

// WordPress decided to remove the oEmbed width from settings so we have to change it here now.
// This is the default (single post size), overwritten where needed. 
if ( ! isset( $content_width ) ) {$content_width = 621;}

// Disables all core updates (Removed to manage updates in wp-config or plugin file instead) 
// define( 'WP_AUTO_UPDATE_CORE', false ); 

// Include theme info and update notification related functions. (Removed because file isn't working)
// include_once( 'library/php/update-notifier.php' );

// Include Shibboleth related functions.
include_once( 'library/php/functions-shibboleth.php' );

// Include site specific functions. This file is normally blank unless overlaid.
include_once ( 'overlay-functions.php' );

// Include UF CLAS functions
include_once( 'library/php/functions-ufclas.php' );

?>
