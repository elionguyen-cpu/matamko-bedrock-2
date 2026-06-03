<?php

declare(strict_types=1);

/**
 * Plugin Name: MATAMKO Theme Activation
 * Description: Activates the Bedrock default theme when configured through WP_DEFAULT_THEME.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('setup_theme', 'matamko_activate_default_theme', 0);
function matamko_activate_default_theme(): void
{
    if (! defined('WP_DEFAULT_THEME') || ! is_string(WP_DEFAULT_THEME) || WP_DEFAULT_THEME === '') {
        return;
    }

    if (get_stylesheet() === WP_DEFAULT_THEME) {
        return;
    }

    $theme = wp_get_theme(WP_DEFAULT_THEME);

    if (! $theme->exists() || $theme->errors()) {
        return;
    }

    switch_theme(WP_DEFAULT_THEME);
}
