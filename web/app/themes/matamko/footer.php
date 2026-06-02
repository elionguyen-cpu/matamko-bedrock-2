<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

if (function_exists('matamko_render_active_footer') && matamko_render_active_footer()) {
    wp_footer();
    ?>
    </body>
    </html>
    <?php
    return;
}
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <p class="site-footer__copyright">
            &copy; <?php echo esc_html((string) date_i18n('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>
        </p>

        <?php if (has_nav_menu('footer')) : ?>
            <nav class="site-footer__navigation" aria-label="<?php echo esc_attr__('Footer Menu', 'matamko'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container' => false,
                    'menu_class' => 'footer-menu',
                    'fallback_cb' => false,
                ]);
            ?>
            </nav>
        <?php endif; ?>
    </div>
</footer>
<?php
wp_footer();
?>
</body>
</html>
