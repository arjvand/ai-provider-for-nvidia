<?php

declare(strict_types=1);

/**
 * PHPStan-only WordPress function signatures used by src/.
 */

function __( string $text, string $domain = 'default' ): string {}

function esc_attr( string $text ): string {}

function esc_html__( string $text, string $domain = 'default' ): string {}

function esc_url( string $url ): string {}

function get_option( string $option, $default = false ) {}

function plugins_url( string $path = '', string $plugin = '' ): string {}

function register_setting( string $option_group, string $option_name, array $args = array() ): void {}

function wp_add_inline_script( string $handle, string $data, string $position = 'after' ): bool {}

function wp_enqueue_script( string $handle, string $src = '', array $deps = array(), $ver = false, $args = false ): void {}

function wp_enqueue_style( string $handle, string $src = '', array $deps = array(), $ver = false, string $media = 'all' ): void {}

function wp_json_encode( $data, int $options = 0, int $depth = 512 ): string {}

function add_options_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = null, int $position = null ) {}

function current_user_can( string $capability, ...$args ): bool {}

function esc_html( string $text ): string {}

function settings_fields( string $option_group ): void {}
