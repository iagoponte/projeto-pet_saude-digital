<?php

declare(strict_types=1);

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'cartilha-inteligente-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );
});