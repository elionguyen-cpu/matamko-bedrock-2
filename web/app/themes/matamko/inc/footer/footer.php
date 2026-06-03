<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_register_footer_post_type');
function matamko_register_footer_post_type(): void
{
    register_post_type('theme_footer', [
        'labels' => [
            'name' => esc_html__('Footers', 'matamko'),
            'singular_name' => esc_html__('Footer', 'matamko'),
            'add_new_item' => esc_html__('Add New Footer', 'matamko'),
            'edit_item' => esc_html__('Edit Footer', 'matamko'),
            'new_item' => esc_html__('New Footer', 'matamko'),
            'view_item' => esc_html__('View Footer', 'matamko'),
            'search_items' => esc_html__('Search Footers', 'matamko'),
            'not_found' => esc_html__('No footers found.', 'matamko'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => 'matamko-theme-builder',
        'show_in_admin_bar' => true,
        'show_in_rest' => true,
        'supports' => ['title'],
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => null,
    ]);
}

add_filter('elementor/cpt_support', 'matamko_add_footer_elementor_support');
function matamko_add_footer_elementor_support(array $post_types): array
{
    if (! in_array('theme_footer', $post_types, true)) {
        $post_types[] = 'theme_footer';
    }

    return $post_types;
}

add_action('elementor/init', 'matamko_enable_footer_elementor_support');
function matamko_enable_footer_elementor_support(): void
{
    add_post_type_support('theme_footer', 'elementor');

    $supported = get_option('elementor_cpt_support', ['page', 'post']);

    if (! is_array($supported)) {
        $supported = ['page', 'post'];
    }

    if (! in_array('theme_footer', $supported, true)) {
        $supported[] = 'theme_footer';
        update_option('elementor_cpt_support', array_values($supported));
    }
}

add_filter('single_template', 'matamko_filter_footer_single_template');
function matamko_filter_footer_single_template(string $template): string
{
    if (! is_singular('theme_footer')) {
        return $template;
    }

    $candidate = get_template_directory() . '/template-parts/blank-builder-template.php';

    return file_exists($candidate) ? $candidate : $template;
}

add_action('acf/include_fields', 'matamko_register_footer_fields');
function matamko_register_footer_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_matamko_footer_builder',
        'title' => esc_html__('Footer Settings', 'matamko'),
        'fields' => [
            [
                'key' => 'field_matamko_footer_is_active',
                'label' => esc_html__('Active Footer', 'matamko'),
                'name' => 'is_active',
                'type' => 'true_false',
                'ui' => 1,
                'default_value' => 0,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'theme_footer',
                ],
            ],
        ],
        'position' => 'side',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
}

add_action('save_post_theme_footer', 'matamko_enforce_single_active_footer', 20, 3);
function matamko_enforce_single_active_footer(int $post_id, WP_Post $post, bool $update): void
{
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) {
        return;
    }

    if (! current_user_can('edit_post', $post_id) || ! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }

    if (true !== (bool) get_field('is_active', $post_id)) {
        return;
    }

    $footers = get_posts([
        'post_type' => 'theme_footer',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'exclude' => [$post_id],
        'meta_query' => [
            [
                'key' => 'is_active',
                'value' => '1',
                'compare' => '=',
            ],
        ],
    ]);

    foreach ($footers as $footer_id) {
        update_field('field_matamko_footer_is_active', false, (int) $footer_id);
    }
}

function matamko_get_active_footer_id(): int
{
    $footers = get_posts([
        'post_type' => 'theme_footer',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => 'is_active',
        'meta_value' => '1',
        'no_found_rows' => true,
    ]);

    return isset($footers[0]) ? (int) $footers[0] : 0;
}

function matamko_render_active_footer(): bool
{
    if (function_exists('matamko_get_theme_setting') && '1' === (string) matamko_get_theme_setting('disable_footer')) {
        return false;
    }

    $footer_id = matamko_get_active_footer_id();

    if ($footer_id <= 0 || ! did_action('elementor/loaded') || ! class_exists('\Elementor\Plugin')) {
        return false;
    }

    $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($footer_id);

    if (! is_string($content) || '' === trim($content)) {
        return false;
    }

    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns prepared frontend HTML.

    return true;
}
