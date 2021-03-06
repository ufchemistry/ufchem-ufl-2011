<?php include("header.php"); ?>

	<?php ufandshands_breadcrumbs(); ?>
	
	<div id="content-wrap">
	  <div id="content-shadow">
		<div id="content" class="container">
		
		  <article name ="content" id="main-content" class="span-24" role="main">
		    <div class="box">
  		  <img src="<?php bloginfo('template_url'); ?>/images/404.jpg" class="alignright" alt="404 Error - Page Not Found" />
  		  <h1>404 Error</h1>
  		  <h2 class="medium-blue">File or Page Not Found</h2>
  		  <p>Sorry, the page you are looking for doesn't appear to exist (or may have moved). Don't worry though, we will help get you to the right place.</p>
  		  <p>You may want to try using the search field above or you can try one of the links below:</p>
  		  <ul>
  		    <li><a href="<?php echo home_url(); ?>">Home</a></li>
  		    <?php wp_nav_menu( array( 'theme_location' => 'main_menu', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 2 ) ); ?>
  		  </ul>
        </div>
		  </article><!-- end #main-content -->
		  
	  </div>
	</div>
	</div>
<?php include('user-role-menu.php'); ?>
<?php include("footer.php"); ?>