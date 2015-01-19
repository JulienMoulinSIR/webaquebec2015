<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <!-- dns prefetch -->
    <link href="//www.google-analytics.com" rel="dns-prefetch">
    <!-- meta -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />

    <?php
    // SET VARIABLES
    $is404 = is_404();
    $is_home = get_page_template_slug()=="template-home.php";
    $needs_cookiebang =  !isset($_COOKIE['big-screen']);
    $needs_hashbang = get_post_type()=="page" && get_field('bang');
    $needs_bang = $needs_cookiebang || $needs_hashbang;
    $scheduleStatus = get_field('my_schedule', 'options');

    //
    //
    // SEO, META, OG TAGS
    if(!$is404) acf_seo(); // functions_seo.php
    ?>

    <!-- icons -->
    <link href="<?= get_home_url() ?>/favicon.ico" rel="shortcut icon">

    <?php
    //
    //
    // HASHBANG
    if($needs_bang): ?>
    <script type="text/javascript">
      var bang = true;
      <?php if($needs_cookiebang): ?>
      var cookiebang = true;
      <?php endif; ?>
      <?php if($needs_hashbang): ?>
      var hashbang = true;
      <?php endif; ?>
    </script>
    <?php endif; ?>

    <!-- css + javascript -->
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>

    <?php if($needs_bang): ?>
    <div class="bang-coverall"></div>
    <?php endif; ?>

    <div class="wrapper<?php if(is_array($scheduleStatus)){echo ' ' . $scheduleStatus[0];} ?>">
      <header role="banner">

        <?php
        if($is_home) get_template_part('template-intro');
        ?>

        <nav role="navigation">
          <div class="intro logo waq">
            <a href="/">
              <img src="/img/@2x/logo-waq.png" alt="<?= __('Web à Québec', 'waq') ?>" />
            </a>
          </div>

          <?php wp_nav_menu( array(
            'theme_location'  => 'secondary',
            'menu_class' => 'secondary'
          )); ?>

          <?php wp_nav_menu( array(
            'theme_location'  => 'main',
            'menu_class' => 'main'
          )); ?>
        </nav>

      </header>

      <main role="main">