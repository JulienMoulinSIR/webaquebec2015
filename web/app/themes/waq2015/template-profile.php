<?php
/*
 * Template Name: Profile
 */
global $current_user;
get_currentuserinfo();
$loggedin = is_user_logged_in();
$isTeam = current_user_can( 'edit_posts' );
if(!$loggedin){
  wp_redirect(get_permalink(get_ID_from_slug('connexion')));
  exit;
}
get_header_once();
?>
<?php if(have_posts()): while(have_posts()): the_post(); ?>

<section id="<?= $post->post_name ?>" class="account">

  <header>

    <div class="container">

      <?php
      // echo link to dashboard
      if(is_user_logged_in() && in_array('administrator', $current_user->roles)): ?>
      <a class="dashboard-link note" href="/admin"><?= __('Administration du site','waq') ?></a>
      <?php endif; ?>

      <h1 class="main title border-left">
        <?= $current_user->data->display_name ?>
        <div class="border-bottom"></div>
      </h1>

    </div>


  </header>

</section>

<section class="program dark">
  <?php
  $schedules = new WP_query(array(
    'post_type' => 'grid',
    'posts_per_page' => -1,
    'orderby'=> 'menu_order',
  ));
  ?>

  <?php if($schedules->have_posts()): ?>
  <div class="schedules">
  <?php

  // loop throught schedules
  foreach($schedules->posts as $k=>$post):
    ?>
    <article class="schedule<?php if($k==0) echo ' active' ?>" schedule="<?= $post->ID ?>">
    <?php
    //
    // format for time labels
    $time_labels_format = '<div class="time">'.
                            '<span class="sub title">%kh</span>'.
                            '<span class="small title">%M</span>'.
                          '<div>';

    // get schedule object
    $schedule = new schedule(array(
      'grid_ID'=>$post->ID,
      'table_class' => 'light',
      'render_thead'=> true,
      'render_time_labels' => true,
      'time_labels_format' => $time_labels_format,
    ));

    //
    // loop throught each column header of the grid
    if($schedule->have_sessions()):
      while($schedule->have_headers()):
        $header = $schedule->the_header();
        ?>
        <div class="location" style="border-color:<?= $header->color ?>;" location="<?= $header->ID ?>" >
          <span class="sub title"><?= __('Salle','waq') ?></span>
          <span class="title"><?= $header->title ?></span>
          <span class="note title" style="color:<?= $header->color ?>;"><?= $header->subtitle ?></span>
        </div>
        <?php
        $schedule->after_header();
      endwhile;
    endif;

    //
    // loop throught each session of the grid
    if($schedule->have_sessions()):
      while($schedule->have_sessions()):
        $session = $schedule->the_session();
        if($session->location->hide):
        ?>

        <div class="pause">
          <h3 class="sub title"><?= $session->title ?></h3>
        </div>

        <?php elseif($session->location->class=='pause'||$session->location->class == 'lunch'): ?>

        <div class="session <?= $session->location->class ?> <?= $wide ? 'wide' : 'small' ?>" themes>
          <h3 class="sub title">
            <span class="location">
              <?= ($session->location->class!='pause' ? __('Salle', 'waq').' ' : '').$session->location->title?>
            </span>
            <span class="separator">·</span>
            <?= $session->title ?>
          </h3>
        </div>

        <?php else:
        $themes = '|';
        foreach($session->themes as $theme){
          $themes .= $theme->term_id.'|';
        }
        $wide = $session->columns->span > 1;
        ?>
        <div>
          <div class="session btn light <?= $session->location->class ?> <?= $wide ? 'wide' : 'small' ?><?php if($session->speaker->image) echo ' has-thumb' ?>" location="<?= $session->location->ID ?>" themes="<?= $themes ?>" >
            <div class="wrap">

              <?php if($wide && $session->speaker->image): ?>
                <div class="thumb">
                  <img src="<?= $session->speaker->image['sizes']['thumbnail'] ?>" alt="<?= $session->speaker->name ?>" />
                </div>
              <?php endif; ?>

              <button class="btn seamless toggle favorite icon-only" toggle-content="<?= __('À mon horaire', 'waq') ?>" schedule="<?= $schedule->grid_ID ?>" session="<?= $session->ID ?>">
                <span>
                  <?= __('Ajouter à mon horaire', 'waq') ?>
                </span>
              </button>

              <div class="location border-bottom">
                <span class="small sub title">
                  <?= __('Salle', 'waq').' '.$session->location->title ?>
                </span>
              </div>

              <a href="<?= $session->permalink ?>">
                <h3 class="session-title title"><?= $session->title ?></h3>

                <div class="speaker">
                  <?php if(!$wide && $session->speaker->image): ?>
                  <div class="thumb">
                    <img src="<?= $session->speaker->image['sizes']['thumbnail'] ?>" alt="<?= $session->speaker->name ?>" />
                  </div>
                  <?php endif; ?>

                  <h4 class="infos">
                    <span class="wrap">
                      <span class="name small title"><?= $session->speaker->name ?></span>
                      <?php if(has($session->speaker->job)) : ?>
                      <span class="job note"><?= $session->speaker->job ?></span>
                      <?php endif; ?>
                    </span>
                  </h4>
                </div>
              </a>
            </div>
          </div>
        </div>

        <?php
        endif;
        $schedule->after_session();
      endwhile;
    endif;

    //
    // Print messages and errors
    if(is_user_logged_in() && in_array('administrator', $current_user->roles)){
      $schedule->print_messages();
      $schedule->print_errors();
    }
    ?>
    </article>
  <?php endforeach; ?>
  </div>
  <?php
  wp_reset_postdata();
  endif;
  ?>


</section>

<?php endwhile; endif; ?>

<?php
get_footer_once();
?>


