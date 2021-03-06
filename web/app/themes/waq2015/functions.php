<?php
require_once('inc/seo.php');
require_once('inc/hashbang.php');
require_once('inc/acf.php');
require_once('inc/schedule-frontend.php');
require_once('inc/schedule-backend.php');
require_once('inc/schedule-favorites.php');
require_once('inc/socialfeed.php');

if( function_exists('acf_add_options_page') ) {
    acf_add_options_page();
}



/*------------------------------------*\
    HELPERS
\*------------------------------------*/


function has($v){
    return (isset($v)&&!empty($v));
}

function is_login_page() {
    return $GLOBALS['pagenow'] == 'wp-login.php';
}

function is_banged(){
    return isset($_COOKIE['big-screen']) && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['WP_HOME']) !== false;
}

function remove_hashbang($url){
    return str_replace('#!/', '', $url);
};

function is_ajax(){
   global $wp_query;
   return isset($wp_query->query_vars['ajax']);
}

function get_header_once(){
    global $post, $header_rendered, $main_ID;
    if(!has($header_rendered)){
      get_header();
      $header_rendered = true;
      $main_ID = $post->ID;
    }
}
function get_footer_once(){
    global $post, $header_rendered, $main_ID;
    if(has($main_ID) && $main_ID==$post->ID){
      wp_reset_query();
      get_footer();
    }
}

function include_page_part($ID){
    global $post;
    query_posts(array(
        'post_type' => 'page',
        'p'         => $ID
    ));
    $template = str_replace('.php','',get_page_template_slug($ID));
    if(!has($template)) $template = 'page';
    get_template_part($template);
}

function get_ID_from_slug($slug){
    global $wpdb;
    return $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '$slug'");
}

function adjacent_post($nextprev = 'next', $meta_key=null, $meta_value=null){
    global $post;
    global $wpdb;
    global $wp_query;
    $vars = $wp_query->query_vars;
    $orderby = isset($vars['orderby']) ? $vars['orderby'] : 'post_date';
    $current = $post->$orderby;
    if($nextprev == 'next' ) {
            $sign = '>';
            $order= 'ASC';
    }
    elseif($nextprev == 'prev' ) {
            $sign = '<';
            $order= 'DESC';
    }
    $querystr = "
    SELECT $wpdb->posts.ID
    FROM $wpdb->posts, $wpdb->postmeta
    WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
    AND $wpdb->posts.post_type = '".$post->post_type."'
    ";
    if(isset($meta_key)) $querystr .= "AND $wpdb->postmeta.meta_key = '".$meta_key."'
    ";
    if(isset($meta_value)) $querystr .= "AND $wpdb->postmeta.meta_value = '".$meta_value."'
    ";
    $querystr .= "AND $wpdb->posts.post_status = 'publish'
    AND $wpdb->posts.".$orderby." ".$sign." '".$current."'
    ORDER BY $wpdb->posts.".$orderby." ".$order."
    LIMIT 1";
    $postData = $wpdb->get_results($querystr, OBJECT);
    if(empty($postData)) return false;
    else return $postData[0];
}


function hashbang_page_link($url, $id, $leavename){
    if( is_banged() && get_field('bang', $id)){
        $site_url = $_ENV['WP_HOME'];
        $pos = strpos($url, $site_url) + strlen($site_url);
        $url = substr_replace($url, '/#!', $pos, 0);
    }
    return $url;
}


/*------------------------------------*\
    TINY MCE
\*------------------------------------*/
function tiny_stylesheet() {
    add_editor_style( 'assets/css/tinymce.css' );
}
function enable_style_select( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}
function custom_tiny_styles( $init_array ) {
    // Define the style_formats array
    $style_formats = array(
        // Each array child is a format with it's own settings
        array(
            'title' => 'Main Title',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'main title'
        ),
        array(
            'title' => 'Big Title',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'huge title'
        ),
        array(
            'title' => 'Title',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'title'
        ),
        array(
            'title' => 'Sub Title',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'sub title'
        ),
        array(
            'title' => 'Small Title',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'small title'
        ),
        array(
            'title' => 'Note',
            'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table',
            'classes' => 'note'
        )
    );
    // Insert the array, JSON ENCODED, into 'style_formats'
    $init_array['style_formats'] = json_encode( $style_formats );
    return $init_array;

}

/*------------------------------------*\
	MENUS
\*------------------------------------*/

add_theme_support('menus');

function register_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'main' => 'Menu principal',
        'secondary' => 'Menu secondaire'
    ));
}

/*------------------------------------*\
    TAILLES D'IMAGE
\*------------------------------------*/

/* -------------------------------------------------------------------------------------------- Tailles d'Images et Crops ----------- */
// AJOUT DE TAILLES D'IMAGES

// CROP
add_image_size('wide', 900, 450, true);
add_image_size('wide retina', 1800, 900, true);
add_image_size('blog-thumb', 450, 450, false); //200 pixels wide (and unlimited height)


// RESIZE
// add_image_size('huge', 3600, 3600, false);

/*------------------------------------*\
	HEAD
\*------------------------------------*/

function header_scripts()
{
    if (!is_admin() && !is_login_page()) {

        wp_register_script('modernizr', get_template_directory_uri() . '/assets/js/modernizr.custom.js', array(), null);
        wp_enqueue_script('modernizr');

        wp_register_script('googlemap', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array(), null);
        wp_enqueue_script('googlemap');

        wp_deregister_script('jquery'); // Deregister WordPress jQuery
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js', array(), null); // fallback
        wp_enqueue_script('jquery'); // Enqueue it!

        wp_register_script('easing', get_template_directory_uri() . '/assets/js/jquery.bez.js', array(), null);
        wp_enqueue_script('easing');

        wp_register_script('cookies', get_template_directory_uri() . '/assets/js/jquery.cookie.js', array(), null);
        wp_enqueue_script('cookies');

        wp_register_script('tabs', get_template_directory_uri() . '/assets/js/tabs.js', array(), null);
        wp_enqueue_script('tabs');

        wp_register_script('raf', get_template_directory_uri() . '/assets/js/raf.min.js', array(), null);
        wp_enqueue_script('raf');

        wp_register_script('scrollEvents', get_template_directory_uri() . '/assets/js/scrollEvents.min.js', array(), null);
        wp_enqueue_script('scrollEvents');

        wp_register_script('sticky', get_template_directory_uri() . '/assets/js/sticky.min.js', array(), null);
        wp_enqueue_script('sticky');

        wp_register_script('breakpoints', get_template_directory_uri() . '/assets/js/breakpoints.min.js', array(), null);
        wp_enqueue_script('breakpoints');


        wp_register_script('map', get_template_directory_uri() . '/assets/js/map.js', array(), null);
        wp_enqueue_script('map');


        wp_register_script('custom', get_template_directory_uri() . '/assets/js/main.js', array(), null);
        wp_enqueue_script('custom');

    }
    /* ---------------------------------------------------------------------------------------------- Admin JavaScript  ---------- */
    // SCRIPT ADMIN
    //
    // else{
    //     wp_register_script('admin', '/assets/js/admin.js');
    //     wp_enqueue_script('admin');
    // }

}


function header_styles()
{
    wp_register_style('main', get_template_directory_uri() . '/assets/css/application.css');
    wp_enqueue_style('main'); // Enqueue it!
}


/* ---------------------------- */
// CSS POUR L'ADMIN

function admin_style() {
    wp_register_style('admin', get_template_directory_uri() . '/assets/css/admin.css');
    wp_enqueue_style('admin');
}

/*------------------------------------*\
    LOGIN/REGISTER FORM
\*------------------------------------*/

function fb_login_form(){
    ob_start();
    do_action( 'wordpress_social_login' );
    $loginForm = ob_get_contents();
    ob_end_clean();

    $loginForm = preg_replace('/<a(.*?)>(.*?)<\/a>/is', "<a$1 tab-index=\"2\"><span>$2</span></a>", $loginForm);

    return $loginForm;
}

function user_login_form($errors){
    $loginForm = wp_login_form( array(
      'echo' => false,
      'id_submit' => 'submit-login',
      'redirect'       => get_permalink(get_ID_from_slug('mon-horaire')),
      'label_username' => __( 'Nom d\'utilisateur', 'waq' ),
          'label_password' => __( 'Mot de passe', 'waq' ),
          'label_remember' => __( 'Rester connecté', 'waq' ),
          'label_log_in'   => __( 'Connexion','waq' ),
          'value_username' =>isset($_GET['user']) ? urldecode($_GET['user']) : NULL
    ));
    $loginForm = preg_replace('/<p(.*?)>(.*?)<\/p>/is', "<div class=\"field\"><p$1>$2</p></div>", $loginForm);

    if(has($errors)){

        if(in_array('empty_password', $errors)){
            $pos = strrpos($loginForm, '<input type="password"');
            $loginForm = substr($loginForm, 0, $pos).
                '<p class="error message note">'.__( 'Un mot de passe est requis', 'waq' ).'</p>'.
                substr($loginForm, $pos);
        }
        if(in_array('failed', $errors)){
            $pos = strrpos($loginForm, '<input type="password"');
            $loginForm = substr($loginForm, 0, $pos).
                '<p class="error message note">'.__( 'Le mot de passe est erroné', 'waq' ).'</p>'.
                substr($loginForm, $pos);
        }

    }

    return $loginForm;
}


// http://www.danielauener.com/build-fully-customized-wordpress-login-annoying-redirects/
function login_fail( $username ) {
    $referrer = $_SERVER['HTTP_REFERER'];
    if (!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') ) {
        wp_redirect( strtok($referrer, '?').'?login=failed&user='.$username );
        exit;
    }
}
function redirect_login($redirect_to, $url, $user) {
    if(empty($_SERVER['HTTP_REFERER'])) return;
    $referrer = $_SERVER['HTTP_REFERER'];
    $errors_keys = [];
    if(!empty($_GET['login'])) $previous_try = $_GET['login'];
    if(isset($user->errors)){
        foreach($user->errors as $error=>$message)
            $errors_keys[] = $error;
    }
    if(count($errors_keys)>0){
        wp_redirect(    $_SERVER['PHP_SELF'].
                        '?login='.implode('+', $errors_keys).
                        (has($_POST['log'])?'&user='.urlencode($_POST['log']):'')
                    );
        exit;
    }
    else if(isset($previous_try)){
        wp_redirect(    '/connexion'.
                        '?login='.$previous_try.
                        (has($_GET['user'])?'&user='.$_GET['user']:'')
                    );
        exit;
    }
    else{
        wp_redirect('/mon-horaire');
        exit;
    }
}
function authenticate_user($user, $username, $password ) {
    if(empty($_SERVER['HTTP_REFERER']) || empty($_POST['redirect_to'])) return;
    $referrer = $_SERVER['HTTP_REFERER'];
    if(!isset($_POST['log'])){
        wp_redirect( strtok($referrer, '?').'?success' );
        exit;
    }
    return $user;
}


function registration_form_errors($errors, $user_login, $user_email) {
    if(empty($_SERVER['HTTP_REFERER'])) return;
    $referrer = $_SERVER['HTTP_REFERER'];
    $errors_keys = [];

    if(!has($_POST['user_password']))
        $errors->add( 'password_missing', __('Vous devez entrer un mot de passe', 'waq') );
    if(!has($_POST['user_password_repeat']))
        $errors->add( 'password_repeat_missing', __('Répétez le mot de passe ici', 'waq') );
    if(has($_POST['user_password']) && has($_POST['user_password_repeat']))
        if($_POST['user_password'] != $_POST['user_password_repeat'])
            $errors->add( 'passwords_not_matched', __('Les mots de passe entrés ne sont pas identiques.', 'waq') );

    foreach($errors->errors as $error=>$message)
        $errors_keys[] = $error;
    if(count($errors_keys)>0){
        wp_redirect(    strtok($referrer, '?').
                        '?registration='.implode('+', $errors_keys).
                        (has($user_login)?'&user='.urlencode($user_login):'').
                        (has($user_email)?'&email='.urlencode($user_email):'')
                    );
        exit;
    }
    return $errors;
}
function register_user( $user_id ) {

    if(isset($_POST['user_name']) && has($_POST['user_name']) ){
        update_user_meta($user_id, 'first_name', $_POST['user_name']);
        update_user_meta($user_id, 'display_name', $_POST['user_name']);
    }
    if(isset($_POST['user_password']) && has($_POST['user_password']) )
        wp_set_password( $_POST['user_password'], $user_id );
}

function disable_new_user_mail(){
    if (!function_exists('wp_new_user_notification')) {
        function wp_new_user_notification() {}
    }
}
/*------------------------------------*\
     OPTIONS EN VRAC
\*------------------------------------*/


// Remove the <div> surrounding the dynamic navigation to cleanup markup
function remove_nav_wrapper($args = '')
{
    $args['container'] = false;
    return $args;
}
function css_class_filter($classes, $item)
{
    if(is_array($classes)){
        $klasses = array();
        $id = get_post_meta($item->ID, '_menu_item_object_id', true);
        $post = get_post($id);
        $slug = $post->post_name;
        array_push($klasses, $slug);
        if(in_array('current-menu-item', $classes)){ array_push($klasses, 'active'); }
        else { array_push($klasses, ''); }

        return $klasses;
    }
    else{
        return $klasses;
    }
}
// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function my_css_attributes_filter($var)
{
    // print_r($var);
    $klasses = array($var[0]);
    if(is_array($var)){
        if(in_array('current-menu-item', $var)){ array_push($klasses, 'active'); }
    }
    return is_array($var) ? $klasses : '';
}

function my_body_class_filter($var)
{
    $klasses = is_admin_bar_showing() ? array('admin-bar') : array();
    return is_array($var) ? $klasses : '';
}

// Remove invalid rel attribute values in the categorylist
function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}


/* ------------------------------------------------------------------------------------------------- body.slug ----------------- */
// AJOUTER LA CLASSE «SLUG» AU BODY

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }
    return $classes;
}


// Remove wp_head() injected Recent Comment styles
function my_remove_recent_comments_style()
{
    global $wp_widget_factory;
    remove_action('wp_head', array(
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style'
    ));
}

// Pagination for paged posts, Page 1, Page 2, Page 3, with Next and Previous Links, No plugin
function blankwp_pagination()
{
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages
    ));
}

// Custom Excerpts
function blankwp_index($length) // Create 20 Word Callback for Index page Excerpts, call using blankwp_excerpt('blankwp_index');
{
    return 45;
}
//Fonction pour remplacer le [...] du excerpt par un texte plus intuitif
function new_excerpt_more($output) {
    return '... <a href="'. get_permalink() . '" class="link-status" title="'. the_title('', '', false).'">Lire la suite</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');
// Remove Admin bar
function remove_admin_bar()
{
    return false;
}


// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function remove_thumbnail_dimensions( $html )
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}
/* --------------------------------------------------------------------------------------------------- Remove Menu Items ----- */
// Remove menu items
function remove_menus() {
    global $menu;
    $restricted = array(__('Links'), __('Comments'));
    end ($menu);
    while (prev($menu)){
        $value = explode(' ',$menu[key($menu)][0]);
        if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
    }
}

/* --------------------------------------------------------------------------------------------------- Remove Comments Support -- */
//
// Remove comment so you don't get spammed
//
function remove_comment_support() {
    remove_post_type_support( 'post', 'comments' );
    remove_post_type_support( 'page', 'comments' );
    remove_post_type_support( 'attachment', 'comments' );
}


/* -------------------------------------------------------------------------------------------------- Custom Post Types ------- */


/*------------------------------------*\
    THEME
\*------------------------------------*/

function enable_more_buttons($buttons) {
    $buttons[] = 'hr';
    $buttons[] = 'sub';
    $buttons[] = 'sup';
    return $buttons;
}

// --------------------------------------------
//
// REWRITE RULES
//
//

function themes_dir_add_rewrites() {

  global $wp_rewrite;
  $theme_name = get_template();
  $new_non_wp_rules = array(
    '(.css)'       => 'app/themes/' . $theme_name . '/assets/$1',
    'css/(.*)'       => 'app/themes/' . $theme_name . '/assets/css/$1',
    'js/(.*)'        => 'app/themes/' . $theme_name . '/assets/js/$1',
    'img/(.*)'    => 'app/themes/' . $theme_name . '/assets/img/$1',
    'fonts/(.*)'       => 'app/themes/' . $theme_name . '/assets/fonts/$1',
    'svg/(.*)'       => 'app/themes/' . $theme_name . '/assets/svg/$1'
  );
  $wp_rewrite->non_wp_rules += $new_non_wp_rules;
}

function rewrite_author($rules){
  foreach($rules as $rule=>$value){
    unset($rules[$rule]);
    $rules[str_replace('author', 'horaire', $rule)] = $value;
  }
  return $rules;
}

/* -------------------------------------------------------------------------------------------------- Variable après le slug ------- */
//
// AJOUTER UN VARIABLE À UN "/" APRÈS LE SLUG
//

function add_endpoint()
{
    add_rewrite_endpoint('filtre', EP_PAGES );
    add_rewrite_endpoint('update', EP_PAGES );
    add_rewrite_endpoint('export', EP_PAGES );
    add_rewrite_endpoint('categorie', EP_PAGES );
    add_rewrite_endpoint('ajax', EP_PAGES );
}



/*------------------------------------*\
    Actions + Filters + ShortCodes
\*------------------------------------*/

//
//  Add Actions
//

add_action('wp_enqueue_scripts', 'header_styles');
add_action('admin_enqueue_scripts', 'admin_style');   // Css pour l'admin
add_action('init', 'header_scripts');
add_action('init', 'register_menu');
add_action('init', 'remove_comment_support');
add_action('init', 'tiny_stylesheet');
add_action('init', 'add_endpoint');   // Ajouter une variables domain.com/slug/var  (voir aussi add_filter)
add_action('generate_rewrite_rules', 'themes_dir_add_rewrites'); // Rewrite des URLs
add_action('widgets_init', 'my_remove_recent_comments_style'); // Remove inline Recent Comment Styles from wp_head()
add_action('admin_menu', 'remove_menus'); // Enlever des éléments dans le menu Admin
add_action('wp_login_failed', 'login_fail');
add_action('user_register', 'register_user', 1, 1);
add_action('login_redirect', 'redirect_login', 10, 3);
add_action('registered_post_type', 'disable_new_user_mail');
//
//  Remove Actions
//
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'index_rel_link'); // Index link
remove_action('wp_head', 'parent_post_rel_link', 10, 0); // Prev link
remove_action('wp_head', 'start_post_rel_link', 10, 0); // Start link
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // Display relational links for the posts adjacent to the current post.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

//
//
// Add Filters


add_filter('body_class', 'add_slug_to_body_class'); // Add slug to body class (Starkers build)
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to post images
add_filter('wp_nav_menu_args', 'remove_nav_wrapper'); // Remove surrounding <div> from WP Navigation
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('the_excerpt', 'shortcode_unautop'); // Remove auto <p> tags in Excerpt (Manual Excerpts only)
add_filter( 'excerpt_length', 'blankwp_index', 999 );
add_filter('nav_menu_css_class', 'css_class_filter', 10, 2); // Remove Navigation <li> injected classes (Commented out by default)
add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID (Commented out by default)
add_filter('page_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's (Commented out by default)
add_filter('body_class', 'my_body_class_filter', 10, 2); // Remove <body> injected classes (Commented out by default)
add_filter('show_admin_bar', 'remove_admin_bar'); // Remove Admin bar
add_filter("mce_buttons", "enable_more_buttons"); // Ajouter des boutons custom au WYSIWYG
add_filter( 'tiny_mce_before_init', 'custom_tiny_styles');
add_filter('mce_buttons_2', 'enable_style_select');
add_filter('authenticate', 'authenticate_user', 1, 3);
add_filter('registration_errors', 'registration_form_errors', 20, 3);
add_filter('author_rewrite_rules', 'rewrite_author' );
add_filter('page_link', 'hashbang_page_link', 10, 3 );
?>