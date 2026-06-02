<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

add_action('init', 'matamko_register_header_post_type');
function matamko_register_header_post_type(): void
{
    register_post_type('theme_header', [
        'labels' => [
            'name' => esc_html__('Headers', 'matamko'),
            'singular_name' => esc_html__('Header', 'matamko'),
            'add_new_item' => esc_html__('Add New Header', 'matamko'),
            'edit_item' => esc_html__('Edit Header', 'matamko'),
            'new_item' => esc_html__('New Header', 'matamko'),
            'view_item' => esc_html__('View Header', 'matamko'),
            'search_items' => esc_html__('Search Headers', 'matamko'),
            'not_found' => esc_html__('No headers found.', 'matamko'),
        ],
        'public' => true,
        'publicly_queryable' => false,
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

add_filter('elementor/cpt_support', 'matamko_add_header_elementor_support');
function matamko_add_header_elementor_support(array $post_types): array
{
    if (! in_array('theme_header', $post_types, true)) {
        $post_types[] = 'theme_header';
    }

    return $post_types;
}

add_action('acf/include_fields', 'matamko_register_header_fields');
function matamko_register_header_fields(): void
{
    if (! function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_matamko_header_builder',
        'title' => esc_html__('Header Settings', 'matamko'),
        'fields' => [
            [
                'key' => 'field_matamko_header_is_active',
                'label' => esc_html__('Active Header', 'matamko'),
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
                    'value' => 'theme_header',
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

add_action('save_post_theme_header', 'matamko_enforce_single_active_header', 20, 3);
function matamko_enforce_single_active_header(int $post_id, WP_Post $post, bool $update): void
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

    $headers = get_posts([
        'post_type' => 'theme_header',
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

    foreach ($headers as $header_id) {
        update_field('field_matamko_header_is_active', false, (int) $header_id);
    }
}

function matamko_get_active_header_id(): int
{
    $headers = get_posts([
        'post_type' => 'theme_header',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_key' => 'is_active',
        'meta_value' => '1',
        'no_found_rows' => true,
    ]);

    return isset($headers[0]) ? (int) $headers[0] : 0;
}

function matamko_render_active_header(): bool
{
    if (function_exists('get_field') && true === (bool) get_field('disable_header', 'option')) {
        return false;
    }

    $header_id = matamko_get_active_header_id();

    if ($header_id <= 0 || ! did_action('elementor/loaded')) {
        return false;
    }

    $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($header_id);

    if (! is_string($content) || '' === trim($content)) {
        return false;
    }

    echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor returns prepared frontend HTML.

    return true;
}
