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
    if (! function_exists('get_field')) {
        return;
    }

    $script = (string) get_field($field_name, 'option');

    if ('' === trim($script)) {
        return;
    }

    echo $script; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Administrator-managed tracking scripts from ACF Theme Settings.
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

add_action('acf/init', 'matamko_register_theme_settings');
function matamko_register_theme_settings(): void
{
    if (! function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => esc_html__('Theme Settings', 'matamko'),
        'menu_title' => esc_html__('Theme Settings', 'matamko'),
        'menu_slug'  => 'matamko-theme-settings',
        'capability' => 'edit_theme_options',
        'redirect'   => false,
        'position'   => 59,
        'icon_url'   => 'dashicons-admin-generic',
    ]);
}

add_action('acf/include_fields', 'matamko_register_theme_settings_fields');
function matamko_register_theme_settings_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key'    => 'group_matamko_theme_settings',
        'title'  => esc_html__('Theme Settings', 'matamko'),
        'fields' => [
            [
                'key' => 'field_matamko_logo_tab',
                'label' => esc_html__('Logo', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_site_logo',
                'label' => esc_html__('Logo', 'matamko'),
                'name' => 'site_logo',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ],
            [
                'key' => 'field_matamko_company_tab',
                'label' => esc_html__('Company Information', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_company_name',
                'label' => esc_html__('Company Name', 'matamko'),
                'name' => 'company_name',
                'type' => 'text',
            ],
            [
                'key' => 'field_matamko_company_address',
                'label' => esc_html__('Company Address', 'matamko'),
                'name' => 'company_address',
                'type' => 'textarea',
                'rows' => 3,
            ],
            [
                'key' => 'field_matamko_contact_tab',
                'label' => esc_html__('Contact Information', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_contact_phone',
                'label' => esc_html__('Phone', 'matamko'),
                'name' => 'contact_phone',
                'type' => 'text',
            ],
            [
                'key' => 'field_matamko_contact_email',
                'label' => esc_html__('Email', 'matamko'),
                'name' => 'contact_email',
                'type' => 'email',
            ],
            [
                'key' => 'field_matamko_social_tab',
                'label' => esc_html__('Social Links', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_social_links',
                'label' => esc_html__('Social Links', 'matamko'),
                'name' => 'social_links',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => esc_html__('Add Link', 'matamko'),
                'sub_fields' => [
                    [
                        'key' => 'field_matamko_social_label',
                        'label' => esc_html__('Label', 'matamko'),
                        'name' => 'label',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_matamko_social_url',
                        'label' => esc_html__('URL', 'matamko'),
                        'name' => 'url',
                        'type' => 'url',
                    ],
                ],
            ],
            [
                'key' => 'field_matamko_tracking_tab',
                'label' => esc_html__('Tracking Scripts', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_tracking_head',
                'label' => esc_html__('Head Scripts', 'matamko'),
                'name' => 'tracking_head_scripts',
                'type' => 'textarea',
                'rows' => 8,
            ],
            [
                'key' => 'field_matamko_tracking_body',
                'label' => esc_html__('Body Scripts', 'matamko'),
                'name' => 'tracking_body_scripts',
                'type' => 'textarea',
                'rows' => 8,
            ],
            [
                'key' => 'field_matamko_tracking_footer',
                'label' => esc_html__('Footer Scripts', 'matamko'),
                'name' => 'tracking_footer_scripts',
                'type' => 'textarea',
                'rows' => 8,
            ],
            [
                'key' => 'field_matamko_header_tab',
                'label' => esc_html__('Header Settings', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_disable_header',
                'label' => esc_html__('Disable Header', 'matamko'),
                'name' => 'disable_header',
                'type' => 'true_false',
                'ui' => 1,
            ],
            [
                'key' => 'field_matamko_footer_tab',
                'label' => esc_html__('Footer Settings', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_disable_footer',
                'label' => esc_html__('Disable Footer', 'matamko'),
                'name' => 'disable_footer',
                'type' => 'true_false',
                'ui' => 1,
            ],
            [
                'key' => 'field_matamko_global_tab',
                'label' => esc_html__('Global Theme Settings', 'matamko'),
                'name' => '',
                'type' => 'tab',
            ],
            [
                'key' => 'field_matamko_accent_color',
                'label' => esc_html__('Accent Color', 'matamko'),
                'name' => 'accent_color',
                'type' => 'color_picker',
                'default_value' => '#111111',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'matamko-theme-settings',
                ],
            ],
        ],
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}
