<?php
/**
 * Plugin Name:	        eBooks Library
 * Plugin URI:	        https://wordpress.org/plugins/ebooks-library
 * Description:	        eBooks library plugin allow to add multiple books from backend and help to users/visitors to search books in library. The reader can search books by author, publisher, price and ratings. They can also search thier favoure book by book name.
 * Version:		        1.0
 * Author:		        Kanhaiya
 * Author URI:	        #
 * Requires at least:   5.2
 * Requires PHP:        7.2
 * Tested up to:        6.0.1
 * Text Domain:         ebooks-library
 * License:		        GPL-2.0+
 * License URI:	        http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists('MD_Library_Book_Search') ) {
    class MD_Library_Book_Search {       

        public function __construct()   
        {
            define( 'MDLIBS_PATH', plugin_dir_path( __FILE__ ) );
            define( 'MDLIBS_ASSETS', plugins_url('/assets/', __FILE__) );
            define( 'MDLIBS_IMAGES', plugins_url('/inc/images/', __FILE__) );
            define( 'MDLIBS_SLUG', plugin_basename( __FILE__ ) );
            define( 'MDLIBS_PRFX', 'mdlibs_' );
            define( 'MDLIBS_VERSION', '1.0' );
            define( 'MDLIBS_FRONT_PATH', plugins_url('/front/', __FILE__) );
            add_action( 'init', array($this, 'mdlibs_booksearch_post_type') );
            add_action( 'init', array($this, 'mdlibs_taxonomy_for_books') );

            // Add meta box
            add_action('add_meta_boxes_mdlibs-book', array($this, 'mdlibs_book_property_metaboxes'));

            // rewrite_rules upon plugin activation
            register_activation_hook( __FILE__, array($this, 'mdlibs_booksearch_rewrite_flush') );

            // add admin js
            add_action('admin_enqueue_scripts', array($this, 'mdlibs_book_admin_script'));

            // Save meta box content
            add_action( 'save_post', array($this, 'mdlibs_book_save_metabox') );

            // Add menu to CPT Book
            add_action('admin_menu', array($this, 'addMenuToCPTbook'));

            // add scripts for shortcode
            add_action('wp_enqueue_scripts', array($this, 'mdlibs_book_shortcode_script'));

            //Shortcode
            add_action('init', array($this, 'mdlibs_book_library_filter'));

            // Ajax search book action
            add_action("wp_ajax_mdlibs_ajax_filter_callback", array($this, "mdlibs_ajax_filter_callback"));
            add_action("wp_ajax_nopriv_mdlibs_ajax_filter_callback", array($this, "mdlibs_ajax_filter_callback"));

            //Single template
            add_filter( 'single_template', array($this, 'mdlibs_get_cpt_template' ));
        }

        //Get Rating
        public static function mdlibsGetRating($post_id){
            $_libs_bratings = get_post_meta($post_id, '_libs_bookratings', true);
            
            $ratingstar = '';
            for($i = 1; $i<=5; $i++){
                if(!empty($_libs_bratings) && ($i <= $_libs_bratings)) {
                    $ratingstar .= '<span class="fa fa-star checked"></span>';
                }
                else{
                    $ratingstar .= '<span class="fa fa-star"></span>';
                }               
            }    
            return $ratingstar; 
        }

        public function mdlibs_booksearch_post_type()
        {
            $labels = array(
                'name'               => __( 'Books', 'ebooks-library' ),
                'singular_name'      => __( 'Book', 'ebooks-library' ),
                'menu_name'          => __( 'eBooks', 'ebooks-library' ),
                'parent_item_colon'  => __( 'Parent Book', 'ebooks-library' ),
                'all_items'          => __( 'All Books', 'ebooks-library' ),
                'view_item'          => __( 'View Book', 'ebooks-library' ),
                'add_new_item'       => __( 'Add New Book', 'ebooks-library' ),
                'add_new'            => __( 'Add New', 'ebooks-library' ),
                'edit_item'          => __( 'Edit Book', 'ebooks-library' ),
                'update_item'        => __( 'Update Book', 'ebooks-library' ),
                'search_items'       => __( 'Search Book', 'ebooks-library' ),
                'not_found'          => __( 'Not Found', 'ebooks-library' ),
                'not_found_in_trash' => __( 'Not found in Trash', 'ebooks-library' ),
            );
            $args = array(
                'label'               => __( 'ebooks', 'ebooks-library' ),
                'description'         => __( 'eBooks is a online showcase library plugin', 'ebooks-library' ),
                'labels'              => $labels,
                'supports'            => array(
                                            'title',
                                            'editor',
                                            'thumbnail',
                                        ),
                'public'              => true,
                'hierarchical'        => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'has_archive'         => true,
                'has_category'        => true,
                'can_export'          => true,
                'exclude_from_search' => false,
                'yarpp_support'       => true,
                'publicly_queryable'  => true,
                'capability_type'     => 'post',
                'menu_icon'           => 'dashicons-book',
                'query_var'           => true,
                'rewrite'             => array(
                'slug' => 'mdlibs-book',
                'register_meta_box_cb' => 'mdlibs_book_property_metaboxes',
            ),
            );
            register_post_type( 'mdlibs-book', $args );
        }

        public function mdlibs_taxonomy_for_books()
        {
            $author_labels = array(
                'name'              => __( 'Author', 'ebooks-library' ),
                'singular_name'     => __( 'Author', 'ebooks-library' ),
                'search_items'      => __( 'Search Authors', 'ebooks-library' ),
                'all_items'         => __( 'All Authors', 'ebooks-library' ),
                'parent_item'       => __( 'Parent Author', 'ebooks-library' ),
                'parent_item_colon' => __( 'Parent Author:', 'ebooks-library' ),
                'edit_item'         => __( 'Edit Author', 'ebooks-library' ),
                'update_item'       => __( 'Update Author', 'ebooks-library' ),
                'add_new_item'      => __( 'Add New Author', 'ebooks-library' ),
                'new_item_name'     => __( 'New Author Name', 'ebooks-library' ),
                'menu_name'         => __( 'Authors', 'ebooks-library' ),
            );

            $auth_args = array(
                'hierarchical'      => false,
                'labels'            => $author_labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'sort'              => true,
                'rewrite'           => array(
                'slug' => 'mdauthor',
                ),
            );

            // Register Taxonomy for Book Author
            register_taxonomy( 'mdauthor', 
                array( 'mdlibs-book' ), 
                $auth_args
            );
                        
            $pub_labels = array(
                'name'              => __( 'Publisher', 'ebooks-library' ),
                'singular_name'     => __( 'Publisher', 'ebooks-library' ),
                'search_items'      => __( 'Search Publishers', 'ebooks-library' ),
                'all_items'         => __( 'All Publisher', 'ebooks-library' ),
                'parent_item'       => __( 'Parent Publisher', 'ebooks-library' ),
                'parent_item_colon' => __( 'Parent Publisher:', 'ebooks-library' ),
                'edit_item'         => __( 'Edit Publisher', 'ebooks-library' ),
                'update_item'       => __( 'Update Publisher', 'ebooks-library' ),
                'add_new_item'      => __( 'Add New Publisher', 'ebooks-library' ),
                'new_item_name'     => __( 'New Publisher Name', 'ebooks-library' ),
                'menu_name'         => __( 'Publishers', 'ebooks-library' ),
            );

            $pub_args = array(
                'hierarchical'      => false,
                'labels'            => $pub_labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'sort'              => true,
                'rewrite'           => array(
                'slug' => 'publisher',
                ),
            );

            // Register Taxonomy for Book Publisher
            register_taxonomy( 'publisher', 
                array( 'mdlibs-book' ), 
                $pub_args 
            );
            
        }

        // To get permalinks to work when you activate the plugin
        public function mdlibs_booksearch_rewrite_flush()
        {
            $this->mdlibs_booksearch_post_type();
            flush_rewrite_rules();
        }

        // Add addtional information to book post type by metabox
        public function mdlibs_book_property_metaboxes($post)
        {
            add_meta_box('book-price', __( 'Book Information', 'ebooks-library' ), array($this, 'mdlibs_book_price_callback'), 'mdlibs-book', 'normal', 'default');
        }

        // Price call back
        public function mdlibs_book_price_callback()
        {
            global $post;

            //Create nonce to verify data

            wp_nonce_field( 'mdlibsnonce', 'libs_book_meta_noncename' );
            $libs_bookprice = get_post_meta($post->ID, '_libs_bookprice', true);
            $libs_bookratings = get_post_meta($post->ID, '_libs_bookratings', true);

            // Price Field
            echo '<p class="libs_bookprice"><label for="price"><strong>'.__( 'Price:', 'ebooks-library' ). '</strong> </label><input type = "range" name="_libs_bookprice" value="' .$libs_bookprice . '" class = "libs-bookprice" id="libs-bookprice" min="1" max="3000"> <span id="book_price_val"></span></p>';

            // Rating Field
            echo '<p class="libs_bookratings"><label for="rating"><strong>'.__( 'Rating:', 'ebooks-library' ). '</strong> </label><input type = "number" name="_libs_bookratings" value="' .$libs_bookratings . '" class = "libs-bookrating" id="libs-bookratings" min="1" max="5"></p>';
            
        }

        // Add dashboard menu in CPT book
        public function addMenuToCPTbook()
        {
            add_submenu_page(
                'edit.php?post_type=mdlibs-book',
                __( 'Dashboard Settings', 'ebooks-library' ),
                __( 'Dashboard', 'ebooks-library' ),
                'manage_options',
                'mdlibsdesh',
                array($this, 'mdlibsDeshboard')
            );
        } 

        public function mdlibsDeshboard()
        { 
           _e("<h3 class='mdlibs-shortcode-ds'>Use this shortcode to display book search filter</h3>");
           echo "<code>[mdlibsfilter]</code>";
        
        }

        // Save custom metabox values
        public function mdlibs_book_save_metabox($post_id )
        {
            // Check if nonce is set
            if ( ! isset( $_POST['libs_book_meta_noncename'] ) || !wp_verify_nonce($_POST['libs_book_meta_noncename'], 'mdlibsnonce' ) ) {
                return $post_id;
            }

            // Check that the logged in user has permission to edit this post
            if ( ! current_user_can( 'edit_post' ) ) {
                return $post_id;
            }

            if(filter_var($_POST['_libs_bookprice'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1,"max_range" => 3000)))){
                $get_book_price = (int)$_POST['_libs_bookprice'];
            }
            else {
                $get_book_price = 1;
            }
                
            if(filter_var($_POST['_libs_bookratings'], FILTER_VALIDATE_INT, array("options" => array("min_range" => 1,"max_range" => 5)))){
                $book_ratings = (int)$_POST['_libs_bookratings'];
            }
            else {
                $book_ratings = '';
            }

            update_post_meta( $post_id, '_libs_bookprice', $get_book_price );
            update_post_meta( $post_id, '_libs_bookratings', $book_ratings );

        }
        // Admin Custom Script

        public function mdlibs_book_admin_script()
        {
            wp_enqueue_script(
                'mdlibs_custom_adminscript',
                MDLIBS_ASSETS . 'js/mdlibs-book-search-admin-script.js',
                array( 'jquery' ),
                '1.0',
                TRUE
            );
        }

        // Shorcode scripts

        public function mdlibs_book_shortcode_script()
        {
            
            wp_register_script(
                'mdlibs_rangeslider_script', MDLIBS_ASSETS . 'js/mdlibs-jquerymobile.js',
                array( 'jquery' ),
                '1.0',
                TRUE
            );

            wp_register_script(
                'mdlibs_searchfilter', MDLIBS_ASSETS . 'js/mdlibs-book-search-cscript.js',
                array( 'jquery' ),
                '1.0',
                TRUE
            );

            wp_register_style(
                'mdlibs_rangeslider_style', MDLIBS_ASSETS . 'css/mdlibs-jquerymobile.css'
            );

            wp_enqueue_style(
                'mdlibs_fontawsom_style', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'
            );
            
            wp_enqueue_style(
                'mdlibs_css_style', MDLIBS_ASSETS . 'css/library-book-search-csstyle.css'
            );

        }

        
        // Book Search Callback
        public function mdlibs_ajax_filter_callback()
        {            
            if ( !wp_verify_nonce( $_POST['mdlibsnonce'], "mdlibs_nonce")) {
                exit("No naughty business please");
            } 
            $bookname = sanitize_text_field($_POST['bookname']);
            $bookauthor = sanitize_text_field($_POST['bookauthor']);
            $bookpublisher = sanitize_text_field($_POST['bookpublisher']);
            $bookrating = intval($_POST['bookrating']);
            $bookrange1 = intval($_POST['bookrange1']);
            $bookrange2 = intval($_POST['bookrange2']);

            $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
            $args = array(
                'post_type' => 'mdlibs-book',
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'desc',
                'posts_per_page' => '21',
                'paged' => $paged,

            );
            // Search book by title
            if(!empty($bookname)){
                $args['s'] = $bookname;
            }
            // Tax Query Begin
            if(!empty($bookauthor) && !empty($bookpublisher)){
                $tax_query = array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => 'mdauthor',
                        'field'    => 'term_id',
                        'terms'    => $bookauthor,
                    ),
                    array(
                        'taxonomy' => 'publisher',
                        'field'    => 'term_id',
                        'terms'    => $bookpublisher,
                    ),
                );
                $args['tax_query'] = $tax_query;
            }
            elseif(!empty($bookauthor)){
                $tax_query = array(
                    'taxonomy' => 'mdauthor',
                    'field'    => 'term_id',
                    'terms'    => $bookauthor,
                );
                $args['tax_query'] = $tax_query;
            }
            elseif(!empty($bookpublisher)){
                $tax_query = array(
                    'taxonomy' => 'publisher',
                    'field'    => 'term_id',
                    'terms'    => $bookpublisher,
                );
                $args['tax_query'] = $tax_query;
            }
            // Tax Query End

            // Meta Query Begin
            if(!empty($bookrating) && !empty($bookrange2)){
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key' => '_libs_bookratings',
                        'value' => $bookrating,
                        'type'    => 'numeric',
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => '_libs_bookprice',
                        'value' => array($bookrange1, $bookrange2),
                        'type'    => 'numeric',
                        'compare' => 'BETWEEN',
                    ),
                );
                $args['meta_query'] = $meta_query;
            }
            elseif(!empty($bookrating)){
                $meta_query = array(
                    'key' => '_libs_bookratings',
                    'value' => $bookrating,
                    'type'    => 'numeric',
                    'compare' => 'LIKE',
                );
                $args['meta_query'] = $meta_query;
            }
            elseif(!empty($bookrange2)){
                $meta_query = array(
                    'key' => '_libs_bookprice',
                    'value' => array($bookrange1, $bookrange2),
                    'type'    => 'numeric',
                    'compare' => 'BETWEEN',
                );
                $args['meta_query'] = $meta_query;
            }
            // Meta Query End

            $query = new WP_Query( $args );
            $json = array();
            if($query->have_posts()) : 
                $html = '<div class="mdlibs-list-books">
                <table>
                    <tr>
                        <th>'.__( 'No', 'ebooks-library' ).'</th>
                        <th>'.__( 'Book Name', 'ebooks-library' ).'</th>
                        <th>'.__( 'Price', 'ebooks-library' ).'</th>
                        <th>'.__( 'Author', 'ebooks-library' ).'</th>
                        <th>'.__( 'Publisher', 'ebooks-library' ).'</th>
                        <th>'.__( 'Rating', 'ebooks-library' ).'</th>
                    </tr>';
                    
                    
                $counter = 0;
                while($query->have_posts()) :
                   $counter++;
                   $query->the_post();
                   $pid = get_the_ID();
                   $_libs_book_price = get_post_meta($pid, '_libs_bookprice', true);
                   $_libs_bratings = get_post_meta($pid, '_libs_bookratings', true);

                   $author_term_obj_list = get_the_terms( $pid, 'mdauthor' );
                   $author_terms = join(', ', wp_list_pluck($author_term_obj_list, 'name'));

                   $publisher_term_obj_list = get_the_terms( $pid, 'publisher' );
                   $publisher_terms = join(', ', wp_list_pluck($publisher_term_obj_list, 'name'));
                    
                    $html .= '<tr>
                        <td>'.$counter.'</td>
                        <td><a href="'.get_the_permalink().'" target="_blank">'.__( get_the_title(), 'ebooks-library' ).'</a></td>
                        <td>$'.__( $_libs_book_price, 'ebooks-library' ).'</td>
                        <td>'.__( $author_terms, 'ebooks-library' ).'</td>
                        <td>'.__( $publisher_terms, 'ebooks-library' ).'</td>
                        <td>'.self::mdlibsGetRating($pid).'</td>
                    </tr>';
           
                
                endwhile;
                $html .= '</table>           
                </div>';
                else:            
                $html .= '<p class="mdlibs-oops">'.__( "Oops, there are no posts.", 'ebooks-library' ).'</p>';  
            endif;
            $json = $html;
            wp_send_json_success( $json );
        }
        

        // Shortcode callback
        public function mdlibs_book_library_filter()
        {
            add_shortcode('mdlibsfilter', function(){
                // get publishers
                $publisher_terms = get_terms( array(
                    'taxonomy' => 'publisher',
                    'hide_empty' => false,
                ) );

                // Get Authors
                $author_terms = get_terms( array(
                    'taxonomy' => 'mdauthor',
                    'hide_empty' => false,
                ) );

                ob_start();
                wp_enqueue_style('mdlibs_rangeslider_style');
                wp_enqueue_script('mdlibs_rangeslider_script');
                wp_enqueue_script('mdlibs_searchfilter');
                wp_localize_script( 'mdlibs_searchfilter', 'mdlibsAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))); 
                $mdlibs_nonce = wp_create_nonce("mdlibs_nonce");
                ?>
                <div class="mdlibsmain">                    
                    <div class="mdlibsInnerdiv">
                        <div class="mdlibsRow">
                            <div class="form-group mdlibsInnerLeft">
                                <label for="Book Name"><?php _e( 'Book Name:', 'ebooks-library' ); ?> </label> 
                                <input id="mdlibsBookName" type="text" value="" />
                            </div>
                            <div class="form-group mdlibsInnerRight">
                                <label for="Author"><?php _e( 'Author:', 'ebooks-library' ); ?> </label>                                
                                <select id="mdlibsAuthor" name="mdlibsAuthor">
                                    <option value=""><?php _e( 'Select Author', 'ebooks-library' ); ?></option>
                                    <?php 
                                    foreach ($author_terms as $authorval){ ?>                                     
                                        <option value="<?php echo esc_html($authorval->term_id); ?>"><?php esc_html_e( $authorval->name, 'ebooks-library' ); ?></option>
                                    <?php }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mdlibsRow">
                            <div class="form-group mdlibsInnerLeft">
                                <label for="Publisher"><?php _e( 'Publisher:', 'ebooks-library' ); ?> </label>
                                <select id="mdlibsPublisher" name="mdlibsPublisher">
                                    <option value=""><?php _e( 'Select Publisher', 'ebooks-library' ); ?></option>
                                    <?php 
                                    foreach ($publisher_terms as $publisherval){ ?>                                     
                                        <option value="<?php echo esc_html($publisherval->term_id); ?>"><?php esc_html_e( $publisherval->name, 'ebooks-library' ); ?></option>
                                    <?php }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group mdlibsInnerRight">
                                <label for="Rating"><?php _e( 'Rating:', 'ebooks-library' ); ?> <em><?php _e( '(value 1 to 5)', 'ebooks-library' ); ?></em></label>
                                <select id="mdlibsrating" name="mdlibsrating">
                                    <option value=""><?php _e( 'Select Rating', 'ebooks-library' ); ?></option>
                                    <option value="1"><span class="mdlibssingle">*</span></option>
                                    <option value="2"><span class="mdlibsdouble">**</span></option>
                                    <option value="3"><span class="mdlibstripple">***</span></option>
                                    <option value="4"><span class="mdlibsfourth">****</span></option>
                                    <option value="5"><span class="mdlibsfive">*****</span></option>
                                </select>                                
                            </div>
                        </div>
                        

                        <div class="mdlibsRow1 mdlibPriceFilter">
                            <div class="mdlibsInnerFull">
                                <div data-role="rangeslider">
                                    <label for="range-1a"><?php _e( 'Price:', 'ebooks-library' ); ?></label>
                                   
                                    <div class="sliders_control">
                                        <input id="fromSlider" type="range" name="mdlibs-range-1a" id="mdlibs-range-1a" value="1" min="1" max="3000"/>
                                        <input id="toSlider" type="range" name="mdlibs-range-1a" id="mdlibs-range-1b" value="3000" min="1" max="3000"/>        
                                    </div>
                                    <div class="mdlibs-showinput">
                                        <input class="form_control_container__time__input" type="number" id="fromInput" value="1" min="1" max="3000"/>
                                        <input class="form_control_container__time__input" type="number" id="toInput" value="3000" min="1" max="3000"/>
                                    </div>


                                    <!-- <input type="range" name="mdlibs-range-1a" id="mdlibs-range-1a" min="0" max="3000" value="0" data-popup-enabled="true" data-show-value="true">
                                    <input type="range" name="mdlibs-range-1a" id="mdlibs-range-1b" min="0" max="3000" value="1000" data-popup-enabled="true" data-show-value="true"> -->
                                </div>
                            </div>
                        </div>

                        <div class="mdlibsRow mdlibs_submitbtn">
                            <div class="mdlibsInnerCenter">
                                <input type="hidden" id="mdlibs_nonce" value="<?php echo $mdlibs_nonce; ?>" />
                                
                                <!-- Image loader -->
                                <img id='mdlibsloader' src='<?php echo MDLIBS_IMAGES."loadingAjax.gif"; ?>' width='26px' height='26px' style="display:none;">
                                <!-- Image loader -->
                                <input id="mdlibsearch" type="button" value="Search" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="mdlibsOuterdiv" id="mdlibSearchResult"> </div>
                </div>
                <?php
                return ob_get_clean();
            });
        }

        // Single custom post type details page
        public function mdlibs_get_cpt_template($single_template) {
            global $post;            
            if ($post->post_type == 'mdlibs-book') {
                 $single_template = dirname(__FILE__) . '\front\single-mdlibs-book.php';
            }
            return $single_template;
       }

       //Get term links
       public static function mdlibsGetTerms($post_id, $taxonomy)
       {
       
        $term_obj_list = get_the_terms( $post_id, $taxonomy );
        if ( $term_obj_list && ! is_wp_error( $term_obj_list ) ) : 

            $term_links = array();
            
            foreach ( $term_obj_list as $term ) {
                $term_links[] = '<a href="' . esc_attr( get_term_link( $term->slug, $taxonomy ) ) . '">' . __( $term->name ) . '</a>';
            }
                                    
            $all_terms = join( ', ', $term_links );
            if($taxonomy == 'mdauthor'){
                $taxo_label = __( 'Author', 'ebooks-library' );
            }
            else{
                $taxo_label = __( 'Publisher', 'ebooks-library' );
            }
    
            return '<strong>'.$taxo_label.': </strong><span class="terms-' . esc_attr( $term->slug ) . '">' . __( $all_terms ) . '</span>';
    
        endif;  
       }
       

    }
    new MD_Library_Book_Search();

}
