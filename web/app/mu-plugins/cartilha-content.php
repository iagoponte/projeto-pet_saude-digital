<?php

declare(strict_types=1);

/**
 * Plugin: Cartilha Content
 * Cadastro de materiais educativos e seus metadados.
 */

add_action('init', function (): void {
    register_post_type('material_educativo', [
        'labels' => [
            'name' => 'Materiais educativos',
            'singular_name' => 'Material educativo',
            'add_new_item' => 'Adicionar material',
            'edit_item' => 'Editar material',
            'new_item' => 'Novo material',
            'view_item' => 'Ver material',
            'search_items' => 'Buscar materiais',
            'not_found' => 'Nenhum material encontrado',
            'all_items' => 'Todos os materiais',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'rewrite' => [
            'slug' => 'materiais',
        ],
        'menu_icon' => 'dashicons-media-document',
        'supports' => [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'revisions',
            'custom-fields',
        ],
    ]);

    register_taxonomy('tipo_material', ['material_educativo'], [
        'labels' => [
            'name' => 'Tipos de material',
            'singular_name' => 'Tipo de material',
            'add_new_item' => 'Adicionar tipo de material',
        ],
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite' => [
            'slug' => 'tipo-material',
        ],
    ]);

    register_taxonomy('tema_material', ['material_educativo'], [
        'labels' => [
            'name' => 'Temas',
            'singular_name' => 'Tema',
            'add_new_item' => 'Adicionar tema',
        ],
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite' => [
            'slug' => 'tema',
        ],
    ]);

    $meta_fields = [
        '_ci_formato' => [
            'type' => 'string',
            'sanitize_callback' => static function ($value): string {
                $allowed = ['pdf', 'audio', 'video', 'jogo'];

                return in_array($value, $allowed, true)
                    ? $value
                    : '';
            },
        ],
        '_ci_pdf_id' => [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
        ],
        '_ci_audio_id' => [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
        ],
        '_ci_video_url' => [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ],
        '_ci_jogo_url' => [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ],
        '_ci_transcricao' => [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ],
        '_ci_legendas_url' => [
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ],
    ];

    foreach ($meta_fields as $key => $config) {
        register_post_meta('material_educativo', $key, [
            'type' => $config['type'],
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => $config['sanitize_callback'],
            'auth_callback' => static function (
                $allowed,
                $meta_key,
                $post_id,
            ): bool {
                return current_user_can('edit_post', $post_id);
            },
        ]);
    }
});

/**
 * Adiciona a caixa de campos personalizados ao editor.
 */
add_action('add_meta_boxes_material_educativo', function (): void {
    add_meta_box(
        'ci_dados_material',
        'Configuração do material',
        'ci_render_dados_material',
        'material_educativo',
        'normal',
        'high',
    );
});

/**
 * Renderiza os campos administrativos.
 */
function ci_render_dados_material(WP_Post $post): void
{
    wp_nonce_field(
        'ci_salvar_dados_material',
        'ci_dados_material_nonce',
    );

    $formato = get_post_meta($post->ID, '_ci_formato', true);
    $pdf_id = absint(get_post_meta($post->ID, '_ci_pdf_id', true));
    $audio_id = absint(get_post_meta($post->ID, '_ci_audio_id', true));
    $video_url = get_post_meta($post->ID, '_ci_video_url', true);
    $jogo_url = get_post_meta($post->ID, '_ci_jogo_url', true);
    $transcricao = get_post_meta($post->ID, '_ci_transcricao', true);
    $legendas_url = get_post_meta($post->ID, '_ci_legendas_url', true);

    $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
    $audio_url = $audio_id ? wp_get_attachment_url($audio_id) : '';

    ?>
    <style>
        .ci-field {
            margin: 20px 0;
        }

        .ci-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .ci-field input[type="url"],
        .ci-field input[type="text"],
        .ci-field select,
        .ci-field textarea {
            width: 100%;
            max-width: 720px;
        }

        .ci-field-description {
            margin-top: 5px;
            color: #646970;
        }

        .ci-media-status {
            display: block;
            margin: 8px 0;
            overflow-wrap: anywhere;
        }

        .ci-media-status:empty {
            display: none;
        }
    </style>

    <p>
        Escolha o formato principal do material e preencha os campos
        correspondentes. Os campos editoriais continuam disponíveis
        no editor normal do WordPress.
    </p>

    <div class="ci-field">
        <label for="ci_formato">Formato do material</label>

        <select id="ci_formato" name="ci_formato">
            <option value="">Selecione um formato</option>
            <option value="pdf" <?php selected($formato, 'pdf'); ?>>
                PDF
            </option>
            <option value="audio" <?php selected($formato, 'audio'); ?>>
                Áudio
            </option>
            <option value="video" <?php selected($formato, 'video'); ?>>
                Vídeo
            </option>
            <option value="jogo" <?php selected($formato, 'jogo'); ?>>
                Jogo interativo
            </option>
        </select>
    </div>

    <div class="ci-field" data-ci-formato="pdf">
        <label>Arquivo PDF</label>

        <input
            type="hidden"
            id="ci_pdf_id"
            name="ci_pdf_id"
            value="<?php echo esc_attr((string) $pdf_id); ?>"
        >

        <button
            type="button"
            class="button ci-select-media"
            data-ci-target="pdf"
            data-ci-library-type="application/pdf"
        >
            Selecionar PDF
        </button>

        <button
            type="button"
            class="button ci-remove-media"
            data-ci-target="pdf"
        >
            Remover arquivo
        </button>

        <span id="ci_pdf_status" class="ci-media-status">
            <?php if ($pdf_url) : ?>
                <a
                    href="<?php echo esc_url($pdf_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                ><?php echo esc_html(wp_basename($pdf_url)); ?></a>
            <?php endif; ?>
        </span>
    </div>

    <div class="ci-field" data-ci-formato="audio">
        <label>Arquivo de áudio</label>

        <input
            type="hidden"
            id="ci_audio_id"
            name="ci_audio_id"
            value="<?php echo esc_attr((string) $audio_id); ?>"
        >

        <button
            type="button"
            class="button ci-select-media"
            data-ci-target="audio"
            data-ci-library-type="audio"
        >
            Selecionar áudio
        </button>

        <button
            type="button"
            class="button ci-remove-media"
            data-ci-target="audio"
        >
            Remover arquivo
        </button>

        <span id="ci_audio_status" class="ci-media-status">
            <?php if ($audio_url) : ?>
                <a
                    href="<?php echo esc_url($audio_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                ><?php echo esc_html(wp_basename($audio_url)); ?></a>
            <?php endif; ?>
        </span>
    </div>

    <div class="ci-field" data-ci-formato="video">
        <label for="ci_video_url">URL do vídeo</label>

        <input
            type="url"
            id="ci_video_url"
            name="ci_video_url"
            value="<?php echo esc_attr($video_url); ?>"
            placeholder="https://www.youtube.com/watch?v=..."
        >

        <p class="ci-field-description">
            Informe o link público do vídeo. A incorporação será
            implementada no template do tema.
        </p>
    </div>

    <div class="ci-field" data-ci-formato="jogo">
        <label for="ci_jogo_url">URL do jogo interativo</label>

        <input
            type="url"
            id="ci_jogo_url"
            name="ci_jogo_url"
            value="<?php echo esc_attr($jogo_url); ?>"
            placeholder="https://exemplo.org/jogos/nome-do-jogo/"
        >

        <p class="ci-field-description">
            Informe o endereço público da versão web do jogo.
            O endereço deve estar acessível aos visitantes.
        </p>
    </div>

    <div class="ci-field" data-ci-formato="audio,video">
        <label for="ci_transcricao">Transcrição ou texto equivalente</label>

        <textarea
            id="ci_transcricao"
            name="ci_transcricao"
            rows="5"
        ><?php echo esc_textarea($transcricao); ?></textarea>

        <p class="ci-field-description">
            Campo recomendado para acessibilidade e consulta textual.
        </p>
    </div>

    <div class="ci-field" data-ci-formato="video">
        <label for="ci_legendas_url">URL das legendas (opcional)</label>

        <input
            type="url"
            id="ci_legendas_url"
            name="ci_legendas_url"
            value="<?php echo esc_attr($legendas_url); ?>"
            placeholder="https://exemplo.org/legendas/video.vtt"
        >
    </div>

    <script>
    (function () {
        const formatSelect = document.getElementById('ci_formato');

        function updateFormatFields() {
            const selected = formatSelect.value;

            document.querySelectorAll('[data-ci-formato]').forEach(function (field) {
                const formats = field.dataset.ciFormato.split(',');
                field.hidden = !formats.includes(selected);
            });
        }

        formatSelect.addEventListener('change', updateFormatFields);
        updateFormatFields();
    })();
    </script>
    <?php
}

/**
 * Carrega a biblioteca de mídia somente na edição dos materiais.
 */
add_action('admin_enqueue_scripts', function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'material_educativo') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    $script = <<<'JS'
jQuery(function ($) {
    $('.ci-select-media').on('click', function (event) {
        event.preventDefault();

        const button = $(this);
        const target = button.data('ci-target');
        const libraryType = button.data('ci-library-type');

        const frame = wp.media({
            title: target === 'pdf' ? 'Selecionar PDF' : 'Selecionar áudio',
            button: { text: 'Usar este arquivo' },
            library: { type: libraryType },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();

            $('#' + (target === 'pdf' ? 'ci_pdf_id' : 'ci_audio_id'))
                .val(attachment.id);

            $('#' + (target === 'pdf' ? 'ci_pdf_status' : 'ci_audio_status'))
                .html(
                    $('<a>', {
                        href: attachment.url,
                        target: '_blank',
                        rel: 'noopener noreferrer',
                        text: attachment.filename || attachment.url
                    })
                );
        });

        frame.open();
    });

    $('.ci-remove-media').on('click', function (event) {
        event.preventDefault();

        const target = $(this).data('ci-target');

        $('#' + (target === 'pdf' ? 'ci_pdf_id' : 'ci_audio_id'))
            .val('');

        $('#' + (target === 'pdf' ? 'ci_pdf_status' : 'ci_audio_status'))
            .empty();
    });
});
JS;

    wp_add_inline_script('jquery', $script);
});

/**
 * Persiste os campos enviados pelo editor.
 */
add_action('save_post_material_educativo', function (int $post_id): void {
    if (
        !isset($_POST['ci_dados_material_nonce'])
        || !is_string($_POST['ci_dados_material_nonce'])
        || !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['ci_dados_material_nonce'])),
            'ci_salvar_dados_material',
        )
    ) {
        return;
    }

    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_revision($post_id)
        || !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $text_fields = [
        'ci_formato' => [
            '_ci_formato',
            static function ($value): string {
                $allowed = ['pdf', 'audio', 'video', 'jogo'];
                $value = sanitize_key($value);

                return in_array($value, $allowed, true) ? $value : '';
            },
        ],
        'ci_video_url' => ['_ci_video_url', 'esc_url_raw'],
        'ci_jogo_url' => ['_ci_jogo_url', 'esc_url_raw'],
        'ci_transcricao' => ['_ci_transcricao', 'sanitize_textarea_field'],
        'ci_legendas_url' => ['_ci_legendas_url', 'esc_url_raw'],
    ];

    foreach ($text_fields as $input => [$meta_key, $sanitize_callback]) {
        $value = isset($_POST[$input]) && is_string($_POST[$input])
            ? wp_unslash($_POST[$input])
            : '';

        $value = call_user_func($sanitize_callback, $value);

        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }

    $attachment_fields = [
        'ci_pdf_id' => '_ci_pdf_id',
        'ci_audio_id' => '_ci_audio_id',
    ];

    foreach ($attachment_fields as $input => $meta_key) {
        $attachment_id = isset($_POST[$input])
            ? absint($_POST[$input])
            : 0;

        if (
            $attachment_id > 0
            && get_post_type($attachment_id) !== 'attachment'
        ) {
            $attachment_id = 0;
        }

        if ($attachment_id === 0) {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $attachment_id);
        }
    }
});


/**
 * Renderiza o recurso associado ao material educativo.
 */
function ci_render_recurso_material_shortcode(): string
{
    $post_id = get_queried_object_id();

    if (!$post_id || get_post_type($post_id) !== 'material_educativo') {
        return '';
    }

    $formato = get_post_meta($post_id, '_ci_formato', true);
    $pdf_id = absint(get_post_meta($post_id, '_ci_pdf_id', true));
    $audio_id = absint(get_post_meta($post_id, '_ci_audio_id', true));
    $video_url = esc_url(get_post_meta($post_id, '_ci_video_url', true));
    $jogo_url = esc_url(get_post_meta($post_id, '_ci_jogo_url', true));
    $transcricao = get_post_meta($post_id, '_ci_transcricao', true);
    $legendas_url = esc_url(get_post_meta($post_id, '_ci_legendas_url', true));

    $html = '<section class="ci-recurso-material">';
    $html .= '<h2>Explore este material</h2>';

    switch ($formato) {
        case 'pdf':
            $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : false;

            if ($pdf_url) {
                $html .= '<p><a href="' . esc_url($pdf_url)
                    . '" target="_blank" rel="noopener noreferrer">'
                    . 'Abrir PDF em nova aba</a> · '
                    . '<a href="' . esc_url($pdf_url)
                    . '" download>Baixar PDF</a></p>';

                $html .= '<iframe class="ci-pdf-viewer" src="'
                    . esc_url($pdf_url)
                    . '" title="Documento PDF do material educativo" '
                    . 'loading="lazy"></iframe>';
            }
            break;

        case 'audio':
            $audio_url = $audio_id ? wp_get_attachment_url($audio_id) : false;

            if ($audio_url) {
                $html .= wp_audio_shortcode([
                    'src' => esc_url($audio_url),
                ]);
            }

            if (is_string($transcricao) && trim($transcricao) !== '') {
                $html .= '<details class="ci-transcricao">';
                $html .= '<summary>Ler transcrição do áudio</summary>';
                $html .= '<div>' . wpautop(esc_html($transcricao)) . '</div>';
                $html .= '</details>';
            }
            break;

        case 'video':
            if ($video_url) {
                $path = (string) wp_parse_url($video_url, PHP_URL_PATH);
                $is_direct_video = (bool) preg_match(
                    '/\.(mp4|webm|ogv)$/i',
                    $path,
                );

                if ($is_direct_video) {
                    $html .= '<video controls preload="metadata">';
                    $html .= '<source src="' . esc_url($video_url) . '">';

                    if ($legendas_url) {
                        $html .= '<track kind="captions" src="'
                            . esc_url($legendas_url)
                            . '" srclang="pt-BR" label="Português">';
                    }

                    $html .= 'Seu navegador não suporta a reprodução de vídeo.';
                    $html .= '</video>';
                } else {
                    $embed = wp_oembed_get($video_url);

                    if ($embed) {
                        $html .= '<div class="ci-video-embed">'
                            . $embed . '</div>';
                    } else {
                        $html .= '<p><a href="' . esc_url($video_url)
                            . '" target="_blank" rel="noopener noreferrer">'
                            . 'Assistir ao vídeo</a></p>';
                    }
                }

                if ($legendas_url && !$is_direct_video) {
                    $html .= '<p><a href="' . esc_url($legendas_url)
                        . '">Consultar arquivo de legendas</a></p>';
                }
            }
            break;

        case 'jogo':
            if ($jogo_url) {
                $html .= '<p><a class="ci-botao-jogo" href="'
                    . esc_url($jogo_url)
                    . '" target="_blank" rel="noopener noreferrer">'
                    . 'Acessar jogo interativo'
                    . '</a></p>';
            }
            break;
    }

    $html .= '</section>';

    return $html;
}

add_shortcode(
    'ci_recurso_material',
    'ci_render_recurso_material_shortcode',
);

/**
 * Restringe as pesquisas públicas aos materiais educativos.
 */
add_action('pre_get_posts', function ($query): void {
    if (is_admin() || ! $query->is_main_query() || ! $query->is_search()) {
        return;
    }

    $query->set('post_type', ['material_educativo']);
});