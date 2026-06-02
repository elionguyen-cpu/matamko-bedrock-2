<?php

declare(strict_types=1);

/**
 * Plugin Name: MATAMKO Theme Activation
 * Description: Activates the Bedrock default theme when configured through WP_DEFAULT_THEME.
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_activate_default_theme', 1);
function matamko_activate_default_theme(): void
{
    if (! defined('WP_DEFAULT_THEME') || WP_DEFAULT_THEME !== 'matamko') {
        return;
    }

    if (get_stylesheet() === 'matamko') {
        return;
    }

    $theme = wp_get_theme('matamko');

    if (! $theme->exists() || $theme->errors()) {
        return;
    }

    switch_theme('matamko');
}
