<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', 'matamko_setup_theme');
function matamko_setup_theme(): void
{
    load_theme_textdomain('matamko', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', [
        'height'      => 120,
        'width'       => 360,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'navigation-widgets',
        'script',
        'search-form',
        'style',
    ]);
    add_theme_support('menus');

    register_nav_menus([
        'primary' => esc_html__('Primary Menu', 'matamko'),
        'footer'  => esc_html__('Footer Menu', 'matamko'),
    ]);
}

add_action('wp_enqueue_scripts', 'matamko_enqueue_assets');
function matamko_enqueue_assets(): void
{
    $css_path = get_template_directory() . '/assets/css/main.css';
    $js_path  = get_template_directory() . '/assets/js/main.js';

    wp_enqueue_style(
        'matamko-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        file_exists($css_path) ? (string) filemtime($css_path) : wp_get_theme()->get('Version'),
    );

    wp_enqueue_script(
        'matamko-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        file_exists($js_path) ? (string) filemtime($js_path) : wp_get_theme()->get('Version'),
        true,
    );
}

add_action('wp_head', 'matamko_output_head_tracking_scripts', 20);
function matamko_output_head_tracking_scripts(): void
{
    matamko_output_tracking_script('tracking_head_scripts');
}

add_action('wp_body_open', 'matamko_output_body_tracking_scripts', 5);
function matamko_output_body_tracking_scripts(): void
{
    matamko_output_tracking_script('tracking_body_scripts');
}

add_action('wp_footer', 'matamko_output_footer_tracking_scripts', 5);
function matamko_output_footer_tracking_scripts(): void
{
    matamko_output_tracking_script('tracking_footer_scripts');
}

function matamko_output_tracking_script(string $field_name): void
{
    $script = (string) matamko_get_theme_setting($field_name);

    if ('' === trim($script)) {
        return;
    }

    echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Administrator-managed tracking scripts from ACF Theme Settings.
}

function matamko_get_theme_setting(string $key, mixed $default = ''): mixed
{
    $settings = get_option('matamko_theme_settings', []);

    if (! is_array($settings)) {
        return $default;
    }

    return $settings[$key] ?? $default;
}

add_action('admin_menu', 'matamko_register_theme_builder_menu');
function matamko_register_theme_builder_menu(): void
{
    add_menu_page(
        esc_html__('Theme Builder', 'matamko'),
        esc_html__('Theme Builder', 'matamko'),
        'edit_theme_options',
        'matamko-theme-builder',
        'matamko_render_theme_builder_page',
        'dashicons-layout',
        58,
    );
}

function matamko_render_theme_builder_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'matamko'));
    }

    $headers_url = admin_url('edit.php?post_type=theme_header');
    $footers_url = admin_url('edit.php?post_type=theme_footer');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Theme Builder', 'matamko'); ?></h1>
        <p><?php echo esc_html__('Manage Elementor-based theme headers and footers.', 'matamko'); ?></p>
        <p>
            <a class="button button-primary" href="<?php echo esc_url($headers_url); ?>">
                <?php echo esc_html__('Headers', 'matamko'); ?>
            </a>
            <a class="button" href="<?php echo esc_url($footers_url); ?>">
                <?php echo esc_html__('Footers', 'matamko'); ?>
            </a>
        </p>
    </div>
    <?php
}

add_action('admin_init', 'matamko_register_theme_settings');
function matamko_register_theme_settings(): void
{
    register_setting(
        'matamko_theme_settings',
        'matamko_theme_settings',
        [
            'type' => 'array',
            'sanitize_callback' => 'matamko_sanitize_theme_settings',
            'default' => [],
        ],
    );
}

add_action('admin_menu', 'matamko_register_theme_settings_page');
function matamko_register_theme_settings_page(): void
{
    add_menu_page(
        esc_html__('Theme Settings', 'matamko'),
        esc_html__('Theme Settings', 'matamko'),
        'edit_theme_options',
        'matamko-theme-settings',
        'matamko_render_theme_settings_page',
        'dashicons-admin-generic',
        59,
    );
}

function matamko_sanitize_theme_settings(mixed $input): array
{
    if (! is_array($input)) {
        return [];
    }

    return [
        'site_logo' => isset($input['site_logo']) ? esc_url_raw((string) $input['site_logo']) : '',
        'company_name' => isset($input['company_name']) ? sanitize_text_field((string) $input['company_name']) : '',
        'company_address' => isset($input['company_address']) ? sanitize_textarea_field((string) $input['company_address']) : '',
        'contact_phone' => isset($input['contact_phone']) ? sanitize_text_field((string) $input['contact_phone']) : '',
        'contact_email' => isset($input['contact_email']) ? sanitize_email((string) $input['contact_email']) : '',
        'facebook_url' => isset($input['facebook_url']) ? esc_url_raw((string) $input['facebook_url']) : '',
        'instagram_url' => isset($input['instagram_url']) ? esc_url_raw((string) $input['instagram_url']) : '',
        'linkedin_url' => isset($input['linkedin_url']) ? esc_url_raw((string) $input['linkedin_url']) : '',
        'tracking_head_scripts' => isset($input['tracking_head_scripts']) ? wp_kses_post((string) $input['tracking_head_scripts']) : '',
        'tracking_body_scripts' => isset($input['tracking_body_scripts']) ? wp_kses_post((string) $input['tracking_body_scripts']) : '',
        'tracking_footer_scripts' => isset($input['tracking_footer_scripts']) ? wp_kses_post((string) $input['tracking_footer_scripts']) : '',
        'disable_header' => ! empty($input['disable_header']) ? '1' : '',
        'disable_footer' => ! empty($input['disable_footer']) ? '1' : '',
        'accent_color' => isset($input['accent_color']) ? sanitize_hex_color((string) $input['accent_color']) : '',
    ];
}

function matamko_render_theme_settings_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die(esc_html__('You do not have permission to access this page.', 'matamko'));
    }

    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Theme Settings', 'matamko'); ?></h1>

        <form method="post" action="options.php">
            <?php settings_fields('matamko_theme_settings'); ?>

            <h2><?php echo esc_html__('Logo', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_text_field('site_logo', esc_html__('Logo URL', 'matamko'), 'url'); ?>
            </table>

            <h2><?php echo esc_html__('Company Information', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_text_field('company_name', esc_html__('Company Name', 'matamko')); ?>
                <?php matamko_render_textarea_field('company_address', esc_html__('Company Address', 'matamko'), 3); ?>
            </table>

            <h2><?php echo esc_html__('Contact Information', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_text_field('contact_phone', esc_html__('Phone', 'matamko')); ?>
                <?php matamko_render_text_field('contact_email', esc_html__('Email', 'matamko'), 'email'); ?>
            </table>

            <h2><?php echo esc_html__('Social Links', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_text_field('facebook_url', esc_html__('Facebook URL', 'matamko'), 'url'); ?>
                <?php matamko_render_text_field('instagram_url', esc_html__('Instagram URL', 'matamko'), 'url'); ?>
                <?php matamko_render_text_field('linkedin_url', esc_html__('LinkedIn URL', 'matamko'), 'url'); ?>
            </table>

            <h2><?php echo esc_html__('Tracking Scripts', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_textarea_field('tracking_head_scripts', esc_html__('Head Scripts', 'matamko'), 8); ?>
                <?php matamko_render_textarea_field('tracking_body_scripts', esc_html__('Body Scripts', 'matamko'), 8); ?>
                <?php matamko_render_textarea_field('tracking_footer_scripts', esc_html__('Footer Scripts', 'matamko'), 8); ?>
            </table>

            <h2><?php echo esc_html__('Header Settings', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_checkbox_field('disable_header', esc_html__('Disable Header', 'matamko')); ?>
            </table>

            <h2><?php echo esc_html__('Footer Settings', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_checkbox_field('disable_footer', esc_html__('Disable Footer', 'matamko')); ?>
            </table>

            <h2><?php echo esc_html__('Global Theme Settings', 'matamko'); ?></h2>
            <table class="form-table" role="presentation">
                <?php matamko_render_text_field('accent_color', esc_html__('Accent Color', 'matamko'), 'text'); ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function matamko_render_text_field(string $key, string $label, string $type = 'text'): void
{
    $value = (string) matamko_get_theme_setting($key);
    ?>
    <tr>
        <th scope="row">
            <label for="matamko-theme-setting-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
        </th>
        <td>
            <input
                id="matamko-theme-setting-<?php echo esc_attr($key); ?>"
                class="regular-text"
                type="<?php echo esc_attr($type); ?>"
                name="matamko_theme_settings[<?php echo esc_attr($key); ?>]"
                value="<?php echo esc_attr($value); ?>"
            >
        </td>
    </tr>
    <?php
}

function matamko_render_textarea_field(string $key, string $label, int $rows = 5): void
{
    $value = (string) matamko_get_theme_setting($key);
    ?>
    <tr>
        <th scope="row">
            <label for="matamko-theme-setting-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
        </th>
        <td>
            <textarea
                id="matamko-theme-setting-<?php echo esc_attr($key); ?>"
                class="large-text code"
                rows="<?php echo esc_attr((string) $rows); ?>"
                name="matamko_theme_settings[<?php echo esc_attr($key); ?>]"
            ><?php echo esc_textarea($value); ?></textarea>
        </td>
    </tr>
    <?php
}

function matamko_render_checkbox_field(string $key, string $label): void
{
    $value = (string) matamko_get_theme_setting($key);
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($label); ?></th>
        <td>
            <label>
                <input
                    type="checkbox"
                    name="matamko_theme_settings[<?php echo esc_attr($key); ?>]"
                    value="1"
                    <?php checked($value, '1'); ?>
                >
                <?php echo esc_html__('Enabled', 'matamko'); ?>
            </label>
        </td>
    </tr>
    <?php
}
