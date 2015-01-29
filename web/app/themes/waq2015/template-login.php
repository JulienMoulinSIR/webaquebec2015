<?php
/*
 * Template Name: Login
 */
get_header();
if(have_posts()): while(have_posts()): the_post();
?>


<section id="login">

  <header>
    <div class="container narrow">
      <h1 class="main title border-left">
        <?= get_the_title() ?>
        <div class="border-bottom"></div>
      </h1>
    </div>
  </header>
  <div class="container narrow">
   <article>
      <div class="card form">
        <div class="tabs">
          <nav>
            <ul>
              <li>
                <button tab="login" class="tag-trigger"><?= __('J\'ai déjà un compte', 'waq') ?></button>
              </li>
              <li>
                <button tab="register" class="tag-trigger"><?= __('Je veux me créer un compte', 'waq') ?></button>
              </li>
            </ul>
          </nav>

          <div>
            <?php
            $login =  isset($_GET['login']) ? explode(' ', $_GET['login']) : [];
            $registration =  isset($_GET['registration']) ? explode(' ', $_GET['registration']) : [];
            ?>
            <div tab="login" class="tab-content">
              <?php
              $loginForm = wp_login_form( array(
                'echo' => false,
                'id_submit' => 'submit-login',
                'redirect'       => get_permalink(get_ID_from_slug('mon-horaire')),
                'label_username' => __( 'Nom d\'utilisateur', 'waq' ),
                    'label_password' => __( 'Mot de passe', 'waq' ),
                    'label_remember' => __( 'Rester connecté', 'waq' ),
                    'label_log_in'   => __( 'Connexion','waq' )
              ));
              $loginForm = preg_replace('/<p(.*?)>(.*?)<\/p>/is', "<div class=\"field\"><p$1>$2</p></div>", $loginForm);
              echo $loginForm;
              ?>
            </div>

            <div tab="register" class="tab-content">
              <form id="registerform" class="form" action="<?= site_url('wp-login.php?action=register', 'login_post') ?>" method="post">
                <?php
              // Success
              if(in_array('success', $registration)):?>
              <h3 class="message success sub title">
                <?= __( 'Votre compte a bien été créé. Vérifiez vos courriels pour récupérer votre mot de passe', 'waq' ) ?>
              </h3>
              <?php endif; ?>
                <div class="field required">
                  <label for="user_login"><?= __( 'Nom d\'utilisateur', 'waq' ) ?></label>
                  <?php
                  // empty username
                  if(in_array('empty_username', $registration)):?>
                    <p class="error message note"><?= __( 'Un nom d\'utilisateur est requis', 'waq' ) ?></p>
                  <?php endif; ?>
                  <?php
                  // username already exists
                  if(in_array('username_exists', $registration)):?>
                    <p class="error message note"><?= __( 'Le nom d\'utilisateur est déjà utilisé', 'waq' ) ?></p>
                  <?php endif; ?>
                  <input type="text" name="user_login" id="user_login" class="input" />
                </div>
                <div class="field">
                  <label for="user_name"><?= __( 'Nom complet (affiché sur le site)', 'waq' ) ?></label>
                  <input type="text" name="user_name" id="user_name" class="input" />
                </div>
                 <div class="field required">
                  <label for="user_email"><?= __( 'Adresse courriel', 'waq' ) ?></label>
                  <?php
                  // email is empty
                  if(in_array('empty_email', $registration)):?>
                    <p class="error message note"><?= __( 'Une adresse courriel est requise', 'waq' ) ?></p>
                  <?php endif; ?>
                  <?php
                  // email is invalid
                  if(in_array('invalid_email', $registration)):?>
                    <p class="error message note"><?= __('L\'adresse courrielle est invalide','waq') ?></p>
                  <?php endif; ?>
                  <?php
                  // email already exists
                  if(in_array('email_exists', $registration)):?>
                    <p class="error message note"><?= __( 'L\'adresse courriel est déjà utilisée', 'waq' ) ?></p>
                  <?php endif; ?>
                  <input type="text" name="user_email" id="user_email" class="input"  />
                </div>
                <?php do_action('register_form'); ?>
                <div class="field">
                  <input type="submit" value="<?= __( 'Envoyer', 'waq' ) ?>" id="register" />
                  <p class="small title"><?= __( 'Un mot de passe vous sera envoyé par courriel.', 'waq' ) ?></p>
                </div>
              </form>

            </div>
          </div>
      </div>
    </article>
  </div>
</section>
<?php
endwhile; endif;
get_footer();
?>


