<?php
global $post;
setup_postdata($post);
?>

<section id="<?= $post->post_name ?>" class="intro dark loading">
          
  <div class="container">
    <div class="cols-2">

      <div class="main col left">
        <div class="top">
          <img src="/img/intro-top.png" alt="5 ans"/> 
        </div>
        <div class="middle">
          <img src="/img/intro-middle.png" alt="WAQ"/> 
        </div>
        <div class="bottom">
          <img src="/img/intro-bottom.png" alt="Bonne fête"/> 
        </div>
      </div>

      <div class="main col right">
        <div class="top cols-2 xl-margin border-left">
          <?php 
          $intro = get_field('intro')[0];
          ?>
          <div class="date col left">
            <h2 class="huge title"><?= $intro['top'][0]['date'] ?></h2>
          </div>
          <div class="location col right">
            <h2 class="huge title"><?= $intro['top'][0]['location'] ?></h2>
          </div>

          <div class="border-bottom expandable"></div>
        </div>

        <div class="middle">
          <h1 class="main title"><?= $intro['middle'][0]['tagline'] ?></h1>
        </div>

        <div class="bottom border-left">
          <div class="border-top expandable"></div>
          
          <a class="btn seamless eventbrite" href="<?= get_field('eventbrite_url') ?>" target="_blank"></a>
            <?= $intro['bottom'][0]['cta'] ?>
            <img class="logo" src="/img/logo-eventbrite.png" alt="Eventbrite" /> 
          </a>
        </div>
      </div>

    </div>
  </div>

  <div class="bg">
    <?php
    $video = array(
        'mp4' => get_field('video_mp4'),
        'ogv' => get_field('video_ogv'),
        'webm' => get_field('video_webm'),
        'image' => get_field('video_image')
      );
    ?>
    <video autoplay loop <?php if($video['image']): ?>poster="<?= $video['image']['url'] ?>"<?php endif; ?>>
      <?php if($video['mp4']): ?><source src="<?= $video['mp4'] ?>" type="video/mp4" /><?php endif; ?>
      <?php if($video['webm']): ?><source src="<?= $video['webm'] ?>" type="video/webm" /><?php endif; ?>
      <?php if($video['ogv']): ?><source src="<?= $video['ogv'] ?>" type="video/ogg" /><?php endif; ?>
      <?= __('Votre navitageur ne supporte pas les vidéos', 'waq'); ?>
    </video>
  </div>
</section>
