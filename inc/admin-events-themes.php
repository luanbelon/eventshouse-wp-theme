<?php
/**
 * Menu de administração EventsThemes — edição por página com pré-visualização.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuração das páginas nativas do tema (alinhado a EventsHouse_create_pages).
 *
 * @return array<string, array{label:string, slug:string, tab:?string, preview_url:string, mode:string, customize_section?:string, customizer_button?:string}>
 */
function EventsHouse_get_events_themes_pages()
{
    $home = home_url('/');

    $pages = array(
        'inicio' => array(
            'label'       => __('Início', 'EventsHouse'),
            'slug'        => 'inicio',
            'tab'         => 'inicio',
            'preview_url' => $home,
            'mode'        => 'options',
            'description' => __('Hero, secções da home e serviços/amenidades no editor de opções.', 'EventsHouse'),
        ),
        'orcamento' => array(
            'label'       => __('Festas & Preços', 'EventsHouse'),
            'slug'        => 'orcamento',
            'tab'         => 'orcamento',
            'preview_url' => home_url('/orcamento/'),
            'mode'        => 'options',
            'description' => __('Títulos da página, preços e extras.', 'EventsHouse'),
        ),
        'galeria' => array(
            'label'       => __('Galeria', 'EventsHouse'),
            'slug'        => 'galeria',
            'tab'         => 'gallery',
            'preview_url' => home_url('/galeria/'),
            'mode'        => 'options',
            'description' => __('Imagens e categorias da galeria.', 'EventsHouse'),
        ),
        'contato' => array(
            'label'       => __('Contactos', 'EventsHouse'),
            'slug'        => 'contato',
            'tab'         => 'contact',
            'preview_url' => home_url('/contato/'),
            'mode'        => 'options',
            'description' => __('Cabeçalho, morada, telefone, email, horário e mapa.', 'EventsHouse'),
        ),
        'google_agenda' => array(
            'label'       => __('Google Agenda — API e calendários', 'EventsHouse'),
            'slug'        => 'google-agenda',
            'tab'         => null,
            'preview_url' => home_url('/disponibilidade/'),
            'mode'        => 'gcal_settings',
            'description' => __('Chave API (partilhada), ID do calendário de festas, ID do calendário Manhãs na Ilha, horários e meses a mostrar.', 'EventsHouse'),
        ),
        'disponibilidade' => array(
            'label'       => __('Disponibilidade', 'EventsHouse'),
            'slug'        => 'disponibilidade',
            'tab'         => 'gcal',
            'preview_url' => home_url('/disponibilidade/'),
            'mode'        => 'options',
            'description' => __('Textos da página. API e calendários: use «Google Agenda — API e calendários» neste menu.', 'EventsHouse'),
        ),
        'emails' => array(
            'label'       => __('Emails', 'EventsHouse'),
            'slug'        => 'emails',
            'tab'         => 'emails',
            'preview_url' => home_url('/contato/'),
            'mode'        => 'options',
            'description' => __('Modelos de emails automáticos, layout e placeholders (%variavel% / {variavel}).', 'EventsHouse'),
        ),
        'manhas' => array(
            'label'       => __('Manhãs na Ilha', 'EventsHouse'),
            'slug'        => 'manhas-na-ilha',
            'tab'         => 'manhas',
            'preview_url' => home_url('/manhas-na-ilha/'),
            'mode'        => 'options',
            'description' => __('Textos da página de passes, packs e IDs dos produtos WooCommerce.', 'EventsHouse'),
        ),
        'verao_ilha' => array(
            'label'       => __('Verão na Ilha', 'EventsHouse'),
            'slug'        => 'verao-na-ilha',
            'tab'         => 'verao_ilha',
            'preview_url' => home_url('/verao-na-ilha/'),
            'mode'        => 'options',
            'description' => __('Textos e imagens da página Verão na Ilha (hero, grelha, horários, preços e CTA).', 'EventsHouse'),
        ),
        'coming_soon' => array(
            'label'       => __('Coming Soon — Manhãs na Ilha', 'EventsHouse'),
            'slug'        => 'coming-soon-manhas',
            'tab'         => 'coming-soon',
            'preview_url' => home_url('/coming-soon-manhas/'),
            'mode'        => 'options',
            'description' => __('Título, subtítulo e imagens dos bubbles/slides da página Coming Soon.', 'EventsHouse'),
        ),
        'rodape' => array(
            'label'       => __('Rodapé', 'EventsHouse'),
            'slug'        => 'rodape',
            'tab'         => 'footer',
            'preview_url' => $home,
            'mode'        => 'options',
            'description' => __('Descrição, copyright, menus, links legais e redes sociais.', 'EventsHouse'),
        ),
    );

    if (class_exists('WooCommerce') && function_exists('wc_get_checkout_url')) {
        $pages['checkout'] = array(
            'label'             => __('Finalizar compra', 'EventsHouse'),
            'slug'              => 'checkout',
            'tab'               => null,
            'preview_url'       => wc_get_checkout_url(),
            'mode'              => 'customizer',
            'customize_section' => 'eventshouse_woocommerce_checkout',
            'description'       => __('Texto introdutório e até quatro documentos (título e link) no checkbox obrigatório do checkout.', 'EventsHouse'),
            'customizer_button' => __('Abrir Personalizar — documentos do checkout', 'EventsHouse'),
        );

        $shop_preview = home_url('/');
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id > 0) {
            $shop_preview = get_permalink($shop_page_id);
        }
        $pages['produto_evento'] = array(
            'label'       => __('Produto Evento', 'EventsHouse'),
            'slug'        => 'produto-evento',
            'tab'         => null,
            'preview_url' => $shop_preview ? $shop_preview : home_url('/'),
            'mode'        => 'event_product_strings',
            'description' => __('Textos da ficha de produto tipo evento: colunas, preços, botão Inscrever-se e formulário de inscrição.', 'EventsHouse'),
        );
    }

    return $pages;
}

/**
 * Regista o menu de topo após Plugins (posição 66).
 */
function EventsHouse_events_themes_register_menu()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    add_menu_page(
        __('EventsThemes', 'EventsHouse'),
        __('EventsThemes', 'EventsHouse'),
        'manage_options',
        'events-themes',
        'EventsHouse_events_themes_dashboard',
        'dashicons-layout',
        66
    );

    add_submenu_page(
        'events-themes',
        __('Visão geral', 'EventsHouse'),
        __('Visão geral', 'EventsHouse'),
        'manage_options',
        'events-themes',
        'EventsHouse_events_themes_dashboard'
    );

    $pages = EventsHouse_get_events_themes_pages();
    foreach ($pages as $key => $cfg) {
        add_submenu_page(
            'events-themes',
            $cfg['label'],
            $cfg['label'],
            'manage_options',
            'events-themes-' . $key,
            function () use ($key) {
                EventsHouse_events_themes_render_editor($key);
            }
        );
    }
}
add_action('admin_menu', 'EventsHouse_events_themes_register_menu');

/**
 * Painel inicial com atalhos para cada página.
 */
function EventsHouse_events_themes_dashboard()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $pages = EventsHouse_get_events_themes_pages();
    ?>
    <div class="wrap events-themes-dashboard">
        <h1><?php esc_html_e('EventsThemes', 'EventsHouse'); ?></h1>
        <p class="description">
            <?php esc_html_e('Escolha uma página para editar textos e imagens com pré-visualização. O editor reutiliza os mesmos campos de «Conteúdos do Site» (Aparência), filtrados por página.', 'EventsHouse'); ?>
        </p>
        <div class="events-themes-grid">
            <?php foreach ($pages as $key => $cfg) : ?>
                <div class="events-theme-card">
                    <h2><?php echo esc_html($cfg['label']); ?></h2>
                    <p><?php echo esc_html($cfg['description']); ?></p>
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=events-themes-' . $key)); ?>">
                        <?php esc_html_e('Editar', 'EventsHouse'); ?>
                    </a>
                    <a class="button" href="<?php echo esc_url($cfg['preview_url']); ?>" target="_blank" rel="noopener noreferrer" style="margin-left:6px;">
                        <?php esc_html_e('Ver site', 'EventsHouse'); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <p style="margin-top:24px;">
            <a href="<?php echo esc_url(admin_url('themes.php?page=eventshouse-options')); ?>"><?php esc_html_e('Abrir todos os conteúdos (modo clássico)', 'EventsHouse'); ?></a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Personalizar (Customizer)', 'EventsHouse'); ?></a>
        </p>
    </div>
    <?php
}

/**
 * Ecrã de edição por slug de página.
 *
 * @param string $key Chave interna (ex.: orcamento).
 */
function EventsHouse_events_themes_render_editor($key)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $pages = EventsHouse_get_events_themes_pages();
    if (!isset($pages[$key])) {
        wp_die(esc_html__('Página não encontrada.', 'EventsHouse'));
    }

    $cfg = $pages[$key];

    if ($cfg['mode'] === 'gcal_settings') {
        EventsHouse_events_themes_render_gcal_settings_page($cfg);
        return;
    }

    if ($cfg['mode'] === 'event_product_strings') {
        EventsHouse_events_themes_render_event_product_labels_page($cfg);
        return;
    }

    if ($cfg['mode'] === 'options' && !empty($cfg['tab'])) {
        $GLOBALS['eventshouse_editor_context']    = $cfg['tab'];
        $GLOBALS['eventshouse_editor_preview_url'] = $cfg['preview_url'];
        $GLOBALS['eventshouse_editor_h1']          = sprintf(
            /* translators: %s: page title */
            __('Editar: %s', 'EventsHouse'),
            $cfg['label']
        );
        EventsHouse_options_page();
        unset($GLOBALS['eventshouse_editor_context'], $GLOBALS['eventshouse_editor_preview_url'], $GLOBALS['eventshouse_editor_h1']);
        return;
    }

    if ($cfg['mode'] === 'customizer') {
        EventsHouse_events_themes_render_customizer_bridge($cfg);
        return;
    }

    if ($cfg['mode'] === 'page_edit') {
        EventsHouse_events_themes_render_page_edit_bridge($cfg);
    }
}

/**
 * Páginas cujo conteúdo está no Customizer (ex.: Contactos).
 *
 * @param array $cfg Config.
 */
function EventsHouse_events_themes_render_customizer_bridge($cfg)
{
    $customize = admin_url('customize.php');
    if (!empty($cfg['customize_section'])) {
        $customize = add_query_arg(array('autofocus[section]' => $cfg['customize_section']), $customize);
    }

    $customizer_btn = !empty($cfg['customizer_button'])
        ? $cfg['customizer_button']
        : __('Abrir Personalizar para editar textos e mapa', 'EventsHouse');

    ?>
    <div class="wrap events-themes-simple-editor">
        <h1><?php echo esc_html(sprintf(__('Editar: %s', 'EventsHouse'), $cfg['label'])); ?></h1>
        <p class="description"><?php echo esc_html($cfg['description']); ?></p>
        <div class="eventshouse-editor-preview-bar">
            <iframe class="eventshouse-preview-iframe" src="<?php echo esc_url($cfg['preview_url']); ?>" title="<?php esc_attr_e('Pré-visualização', 'EventsHouse'); ?>"></iframe>
            <p class="description" style="margin:8px 0 16px;">
                <a href="<?php echo esc_url($cfg['preview_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir página no site ↗', 'EventsHouse'); ?></a>
            </p>
        </div>
        <p>
            <a class="button button-primary button-hero" href="<?php echo esc_url($customize); ?>"><?php echo esc_html($customizer_btn); ?></a>
        </p>
        <p>
            <a href="<?php echo esc_url(admin_url('themes.php?page=eventshouse-options')); ?>"><?php esc_html_e('Conteúdos do site (outras secções)', 'EventsHouse'); ?></a>
        </p>
    </div>
    <?php
}

/**
 * Página com conteúdo no editor de páginas WordPress.
 *
 * @param array $cfg Config.
 */
function EventsHouse_events_themes_render_page_edit_bridge($cfg)
{
    $page = get_page_by_path($cfg['slug']);
    $edit = $page ? get_edit_post_link($page->ID, '') : '';

    ?>
    <div class="wrap events-themes-simple-editor">
        <h1><?php echo esc_html(sprintf(__('Editar: %s', 'EventsHouse'), $cfg['label'])); ?></h1>
        <p class="description"><?php echo esc_html($cfg['description']); ?></p>
        <div class="eventshouse-editor-preview-bar">
            <iframe class="eventshouse-preview-iframe" src="<?php echo esc_url($cfg['preview_url']); ?>" title="<?php esc_attr_e('Pré-visualização', 'EventsHouse'); ?>"></iframe>
            <p class="description" style="margin:8px 0 16px;">
                <a href="<?php echo esc_url($cfg['preview_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir página no site ↗', 'EventsHouse'); ?></a>
            </p>
        </div>
        <?php if ($edit) : ?>
            <p>
                <a class="button button-primary button-hero" href="<?php echo esc_url($edit); ?>"><?php esc_html_e('Editar página no WordPress', 'EventsHouse'); ?></a>
            </p>
        <?php else : ?>
            <div class="notice notice-warning"><p><?php esc_html_e('Página ainda não existe. Guarde o tema ou crie a página «Receção».', 'EventsHouse'); ?></p></div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * EventsThemes — definições Google Agenda (2 calendários + API).
 *
 * @param array $cfg Config da página.
 */
function EventsHouse_events_themes_render_gcal_settings_page(array $cfg)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['eventshouse_gcal_page_save']) && check_admin_referer('eventshouse_gcal_page', 'eventshouse_gcal_page_nonce')) {
        update_option('eventshouse_gcal_api_key', sanitize_text_field(wp_unslash($_POST['gcal_api_key'] ?? '')));
        update_option('eventshouse_gcal_calendar_id', sanitize_text_field(wp_unslash($_POST['gcal_calendar_id'] ?? '')));
        update_option('eventshouse_gcal_ilha_calendar_id', sanitize_text_field(wp_unslash($_POST['gcal_ilha_calendar_id'] ?? '')));
        update_option('eventshouse_gcal_months', max(1, min(12, absint($_POST['gcal_months'] ?? 6))));
        update_option('eventshouse_gcal_slots', sanitize_textarea_field(wp_unslash($_POST['gcal_slots'] ?? '')));
        update_option('eventshouse_gcal_ilha_slots', sanitize_textarea_field(wp_unslash($_POST['gcal_ilha_slots'] ?? '')));
        if (function_exists('EventsHouse_invalidate_gcal_cache')) {
            EventsHouse_invalidate_gcal_cache();
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Definições guardadas.', 'EventsHouse') . '</p></div>';
    }

    $gcal_api_key           = get_option('eventshouse_gcal_api_key', '');
    $gcal_calendar_id       = get_option('eventshouse_gcal_calendar_id', '');
    $gcal_ilha_calendar_id  = get_option('eventshouse_gcal_ilha_calendar_id', '');
    $gcal_ilha_slots        = get_option('eventshouse_gcal_ilha_slots', '');
    $gcal_months            = get_option('eventshouse_gcal_months', 6);
    $gcal_slots             = get_option('eventshouse_gcal_slots', "10:00-12:30\n13:30-16:00\n17:00-19:30");

    ?>
    <div class="wrap events-themes-gcal-settings">
        <h1><?php echo esc_html($cfg['label']); ?></h1>
        <p class="description"><?php echo esc_html($cfg['description']); ?></p>
        <p>
            <a href="<?php echo esc_url($cfg['preview_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir página Disponibilidade', 'EventsHouse'); ?></a>
            &nbsp;·&nbsp;
            <a href="<?php echo esc_url(home_url('/manhas-na-ilha/')); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir Manhãs na Ilha', 'EventsHouse'); ?></a>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('eventshouse_gcal_page', 'eventshouse_gcal_page_nonce'); ?>

            <h2 class="title"><?php esc_html_e('Ligação à API', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_gcal_api_key"><?php esc_html_e('Chave API Google', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" id="eh_gcal_api_key" name="gcal_api_key" value="<?php echo esc_attr($gcal_api_key); ?>" class="regular-text" placeholder="AIzaSy..." />
                        <p class="description"><?php esc_html_e('Uma única chave para ler os dois calendários públicos.', 'EventsHouse'); ?></p>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Calendário 1 — Festas / Disponibilidade', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_gcal_calendar_id"><?php esc_html_e('ID do calendário', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" id="eh_gcal_calendar_id" name="gcal_calendar_id" value="<?php echo esc_attr($gcal_calendar_id); ?>" class="large-text" />
                        <p class="description"><?php esc_html_e('ID na página de definições do Google Calendar (formato tipo email).', 'EventsHouse'); ?></p>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Calendário 2 — Manhãs na Ilha', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_gcal_ilha_calendar_id"><?php esc_html_e('ID do calendário', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" id="eh_gcal_ilha_calendar_id" name="gcal_ilha_calendar_id" value="<?php echo esc_attr($gcal_ilha_calendar_id); ?>" class="large-text" />
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Horários e período', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_gcal_months"><?php esc_html_e('Meses a mostrar no site', 'EventsHouse'); ?></label></th>
                    <td>
                        <select id="eh_gcal_months" name="gcal_months">
                            <?php for ($m = 1; $m <= 6; $m++) : ?>
                                <option value="<?php echo (int) $m; ?>" <?php selected((int) $gcal_months, $m); ?>><?php echo (int) $m; ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Calendário no site e pedidos à API usam este número de meses a partir de hoje.', 'EventsHouse'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_gcal_slots"><?php esc_html_e('Slots — Festas', 'EventsHouse'); ?></label></th>
                    <td>
                        <textarea id="eh_gcal_slots" name="gcal_slots" rows="5" class="large-text"><?php echo esc_textarea($gcal_slots); ?></textarea>
                        <p class="description"><?php esc_html_e('Um intervalo por linha (HH:MM-HH:MM). Cada linha é comparada com os eventos: só o slot em conflito fica indisponível; o dia fica vermelho se todos os slots colidirem ou existir evento de dia inteiro.', 'EventsHouse'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_gcal_ilha_slots"><?php esc_html_e('Slots — Manhãs na Ilha', 'EventsHouse'); ?></label></th>
                    <td>
                        <textarea id="eh_gcal_ilha_slots" name="gcal_ilha_slots" rows="5" class="large-text" placeholder="<?php esc_attr_e('Vazio = usar os mesmos slots de Festas.', 'EventsHouse'); ?>"><?php echo esc_textarea($gcal_ilha_slots); ?></textarea>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="eventshouse_gcal_page_save" class="button button-primary" value="1"><?php esc_html_e('Guardar', 'EventsHouse'); ?></button>
            </p>
        </form>
    </div>
    <?php
}
