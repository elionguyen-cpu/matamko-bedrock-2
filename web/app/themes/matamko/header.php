<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();

if (function_exists('matamko_render_active_header') && matamko_render_active_header()) {
    return;
}
?>
<header class="site-header">
    <div class="site-header__inner">
        <div class="site-header__brand">
            <?php
            if (has_custom_logo()) {
                the_custom_logo();
            } else {
                ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <?php echo esc_html(get_bloginfo('name')); ?>
                </a>
                <?php
            }
?>
        </div>

        <?php if (has_nav_menu('primary')) : ?>
            <nav class="site-header__navigation" aria-label="<?php echo esc_attr__('Primary Menu', 'matamko'); ?>">
                <?php
    wp_nav_menu([
        'theme_location' => 'primary',
        'container' => false,
        'menu_class' => 'primary-menu',
        'fallback_cb' => false,
    ]);
            ?>
            </nav>
        <?php endif; ?>
    </div>
</header>
