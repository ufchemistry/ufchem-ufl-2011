<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
	<?php ufclas_responsive_head_top(); // Custom hook to include content after the head tag ?>
<?php
global $detect_mobile;
global $opt_responsive;
?>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	
	<?php ufclas_responsive_body_top(); // Custom hook to include content after the body tag ?>
	
	<div id="full-modal"></div>
		<ul class="screen-reader-text">
			<li><a href="http://assistive.usablenet.com/tt/<?php if ( empty($post->ID) ) { echo esc_url(home_url()); } else { echo esc_url(get_permalink( $post->ID )); } ?>" accesskey="t" title="Text-only version of this website">Text-only version of this website</a></li>
			<li><a href="#content" accesskey="s" title="Skip navigation">Skip navigation</a></li>
			<li><a href="<?php bloginfo( 'url' ); ?>" accesskey="1" title="Home page">Home page</a></li>
			<li><a href="<?php bloginfo( 'url' ); ?>/#secondary" accesskey="2" title="what's new">What's new</a></li>
			<li><a href="#search" accesskey="4" title="Search">Search</a></li>
			<?php $webmaster_email = of_get_option("opt_webmaster_email"); if( !empty($webmaster_email) && ufl_check_email_address($webmaster_email) ) { echo "<li><a id=\"contact-webmaster-srt\" accesskey=\"6\" href=\"mailto:".$webmaster_email."\">Contact Webmaster</a></li> "; } else { echo "<li><a id=\"contact-webmaster-srt\" accesskey=\"6\" href=\"".$webmaster_email."\">Contact Webmaster</a></li> "; } ?>
			<li><a href="#footer-links" accesskey="8" title="Website policies">Website policies</a></li>
			<li><a href="http://www.ufl.edu/disability/" accesskey="0" title="Disability services">Disability services</a></li>
		</ul>
	
  <?php
	$disabled_global_elements = of_get_option('opt_disable_global_elements');
	if (!$disabled_global_elements):	
		if ($opt_responsive && $detect_mobile) {
			if (isset($_COOKIE["UFLmobileFull"])){
				ufclas_responsive_top_nav();
			}
		}else{
			ufclas_responsive_top_nav();
		}
	endif;
   ?>

	<header role="banner">
	  <div class="container">
	  
		<!-- begin website title logic -->
			<?php ufandshands_site_title(); ?>
		<!-- end website title logic -->
		
        <?php if (!$disabled_global_elements): ?>
		<ul id="header-social">
			<?php ufclas_get_site_socialnetworks(); ?>
		</ul>
        <?php endif; ?>
		<div id="header-search-wrap">
			<?php if (has_nav_menu('header_links')) { //detects if the header_links menu is being used ?>
			  <nav id="utility-links" class="span-7half" role="navigation">
				<ul><?php wp_nav_menu( array('theme_location' => 'header_links', 'container' => false )); ?></ul>
			  </nav>
			<?php } ?>
		  <div id="searchform-wrap">
		  	<?php $opt_search_box_text = of_get_option("opt_search_box_text"); ?>
			<?php $opt_use_wp_search = of_get_option("opt_use_wp_search"); if(!empty($opt_use_wp_search)) { ?>
			<form method="get" id="searchform" action="<?php echo home_url('/'); ?>" role="search">
				<input type="text" value="<?php if (!empty($opt_search_box_text)) { echo $opt_search_box_text; } else { echo 'Search This Site'; }?>" id="header-search-field" name="s" />
				<input type="image" src="<?php bloginfo('template_url'); ?>/images/header-search-btn-orange.jpg" id="header-search-btn"  alt="Search Button" name="sa" />
			</form>
			<?php } else { ?>
			<form method="get" id="searchform" action="<?php echo is_ssl()? 'https':'http'; ?>://search.ufl.edu/search" role="search">
				<input type="hidden" name="source" id="source" value="web">
				<?php $opt_site_specific_search = of_get_option("opt_site_specific_search"); if(!empty($opt_site_specific_search)) { ?>
				<input type="hidden" name="site" id="site" value="<?php $parsed_url = parse_url( home_url() ); echo $parsed_url['host']; if ( isset($parsed_url['path']) ) { echo $parsed_url['path']; } ?>">
				<?php } ?>
				<input type="text" value="<?php if (!empty($opt_search_box_text)) { echo $opt_search_box_text; } else { echo 'Search UF Web'; } ?>" id="header-search-field" name="query" />
				<input type="image" src="<?php bloginfo('template_url'); ?>/images/header-search-btn-orange.jpg" id="header-search-btn"  alt="Search Button" name="sa" />
			</form>
			<?php } ?>
		  </div>
		</div><!-- end header-search-wrap -->
		
		<?php // orange header action item box
		if (!$detect_mobile || isset($_cookie["UFLmobileFull"])){	
			$actionitem_text = of_get_option('opt_actionitem_text');
			$actionitem_url = of_get_option('opt_actionitem_url');
		
			if (!empty($actionitem_text)) {
				echo "<a id='header-actionitem' href='".$actionitem_url."'>".$actionitem_text."</a>";
			}
		}
		?>
		
	  </div><!-- end header .container -->
	</header>
	<?php 
        if ($opt_responsive) {
			if ($detect_mobile && !isset($_COOKIE["UFLmobileFull"])){
	        	include 'library/php/responsive-menu.php'; //responsive menu logic 
			}
         }
    ?>
    
    <?php include('library/php/menu.php'); //menu logic ?>
