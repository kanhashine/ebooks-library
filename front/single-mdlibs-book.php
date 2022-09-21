<?php
/**
 * The template for displaying all single posts
 */

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post(); 
    $get_book_price = get_post_meta($post->ID, '_libs_bookprice', true);
    
    ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <header class="entry-header alignwide">
            <?php the_title( '<h1 class="mdlibs-bookname entry-title">', '</h1>' ); ?>
        </header><!-- .entry-header -->

        <div class="entry-content">
            <div class="mdlibs-description"><?php the_content(); ?></div>
            <div class="mdlibs-author">
                <?php echo MD_Library_Book_Search::mdlibsGetTerms($post->ID, 'mdauthor'); ?>
            </div>
            <div class="mdlibs-publisher">
            <?php echo MD_Library_Book_Search::mdlibsGetTerms($post->ID, 'publisher'); ?>
            </div>
            <div class="mdlibs-price"><strong><?php _e( 'Price:', 'ebooks-library' ); ?> </strong>$<?php echo $get_book_price; ?></div>
            <div class="mdlibs-rating"><strong><?php _e( 'Rating:', 'ebooks-library' ); ?></strong><?php echo MD_Library_Book_Search::mdlibsGetRating($post->ID); ?></div>
        </div><!-- .entry-content -->        

    </article>
    
    <?php
	// If comments are open or there is at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}	
endwhile; // End of the loop.

get_footer();