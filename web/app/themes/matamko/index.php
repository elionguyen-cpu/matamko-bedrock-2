<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="primary" class="site-main">
    <?php
    if (have_posts()) {
        while (have_posts()) {
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
                <header class="entry__header">
                    <?php the_title('<h1 class="entry__title">', '</h1>'); ?>
                </header>
                <div class="entry__content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
        }
    }
?>
</main>
<?php
get_footer();
