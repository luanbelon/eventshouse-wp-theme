<?php
/**
 * EventsHouse Theme Functions
 *
 * @package EventsHouse
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('EventsHouse_VERSION', '1.4.5');
define('EventsHouse_DIR', get_template_directory());
define('EventsHouse_URI', get_template_directory_uri());
/** Email e telefone por omissão (formulários enviam para este email; SMTP via plugin). */
define('EventsHouse_DEFAULT_CONTACT_EMAIL', 'geral@ailha.pt');
define('EventsHouse_DEFAULT_CONTACT_PHONE', '+351 916 319 475');

require_once EventsHouse_DIR . '/inc/admin-events-themes.php';
require_once EventsHouse_DIR . '/inc/event-product-ui-labels.php';
require_once EventsHouse_DIR . '/inc/home-sections-admin.php';
require_once EventsHouse_DIR . '/inc/ilha-passes-page-admin.php';
require_once EventsHouse_DIR . '/inc/verao-ilha-page-admin.php';
require_once EventsHouse_DIR . '/inc/calendar-booking-markup.php';
require_once EventsHouse_DIR . '/inc/gcal-availability.php';
require_once EventsHouse_DIR . '/inc/footer-admin.php';
require_once EventsHouse_DIR . '/inc/admin-checkins-dashboard.php';
require_once EventsHouse_DIR . '/inc/error-report.php';
require_once EventsHouse_DIR . '/inc/ilha-reservas.php';
require_once EventsHouse_DIR . '/inc/ilha-pass-extras.php';
require_once EventsHouse_DIR . '/inc/ilha-rececao-meias.php';
require_once EventsHouse_DIR . '/inc/ilha-reservation-emails.php';
require_once EventsHouse_DIR . '/inc/ilha-pack-purchase-email.php';
require_once EventsHouse_DIR . '/inc/ilha-gcal-sync.php';
require_once EventsHouse_DIR . '/inc/woocommerce-theme.php';
require_once EventsHouse_DIR . '/inc/woocommerce-emails.php';
require_once EventsHouse_DIR . '/inc/woocommerce-cart-empty.php';
require_once EventsHouse_DIR . '/inc/admin-festa-order.php';
require_once EventsHouse_DIR . '/inc/festa-cliente-dados.php';
require_once EventsHouse_DIR . '/inc/contact-map.php';
require_once EventsHouse_DIR . '/inc/performance.php';

/**
 * Carrega o tipo de produto «event» (o tema carrega depois do WooCommerce,
 * por isso woocommerce_loaded pode já ter corrido — não depender só desse hook).
 */
function EventsHouse_wc_event_maybe_bootstrap()
{
    static $done = false;
    if ($done || !class_exists('WooCommerce')) {
        return;
    }
    $done = true;
    require_once EventsHouse_DIR . '/inc/class-wc-product-event.php';
    require_once EventsHouse_DIR . '/inc/woocommerce-product-event.php';
    require_once EventsHouse_DIR . '/inc/event-product-config.php';
    require_once EventsHouse_DIR . '/inc/event-product-admin.php';
    require_once EventsHouse_DIR . '/inc/event-product-form.php';
}

if (did_action('woocommerce_loaded')) {
    EventsHouse_wc_event_maybe_bootstrap();
} else {
    add_action('woocommerce_loaded', 'EventsHouse_wc_event_maybe_bootstrap', 1);
}

/**
 * Theme Setup
 */
function EventsHouse_setup()
{
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    add_theme_support('html5', array(
        'search-form', 'comment-form', 'comment-list',
        'gallery', 'caption', 'style', 'script',
    ));

    // Suporte ao WooCommerce
    add_theme_support('woocommerce');

    register_nav_menus(array(
        'primary' => __('Menu Principal', 'EventsHouse'),
        'footer'  => __('Menu Footer', 'EventsHouse'),
    ));

    add_image_size('gallery-thumb', 400, 300, true);
    add_image_size('gallery-large', 800, 600, true);
    add_image_size('hero-banner', 1920, 800, true);
}
add_action('after_setup_theme', 'EventsHouse_setup');

/**
 * ═══════════════════════════════════════════════════
 * AUTO-CRIAÇÃO DE PÁGINAS NA ATIVAÇÃO DO TEMA
 * ═══════════════════════════════════════════════════
 */
function EventsHouse_create_pages()
{
    // Executa apenas se ainda não foram verificadas ou após troca de tema
    if (did_action('after_switch_theme') === 0 && get_option('eventshouse_pages_created_v2') === '1') {
        return;
    }

    $pages = array(
        array(
            'title'    => 'Início',
            'slug'     => 'inicio',
            'template' => 'front-page.php',
        ),
        array(
            'title'    => 'Festas & Preços',
            'slug'     => 'orcamento',
            'template' => 'page-orcamento.php',
        ),
        array(
            'title'    => 'Galeria',
            'slug'     => 'galeria',
            'template' => 'page-galeria.php',
        ),
        array(
            'title'    => 'Contactos',
            'slug'     => 'contato',
            'template' => 'page-contato.php',
        ),
        array(
            'title'    => 'Disponibilidade',
            'slug'     => 'disponibilidade',
            'template' => 'page-disponibilidade.php',
        ),
        array(
            'title'    => 'Manhãs na Ilha',
            'slug'     => 'manhas-na-ilha',
            'template' => 'page-ilha-passes.php',
        ),
        array(
            'title'    => 'Verão na Ilha',
            'slug'     => 'verao-na-ilha',
            'template' => 'page-verao-na-ilha.php',
        ),
        array(
            'title'    => 'Receção (A Ilha)',
            'slug'     => 'rececao',
            'template' => 'page-rececao.php',
        ),
        array(
            'title'    => 'Política de Privacidade',
            'slug'     => 'politica-de-privacidade',
            'template' => 'page-politica-de-privacidade.php',
        ),
        array(
            'title'    => 'Termos e Condições',
            'slug'     => 'termos-e-condicoes',
            'template' => 'page-termos-e-condicoes.php',
        ),
    );

    foreach ($pages as $page) {
        $existing = get_page_by_path($page['slug']);
        if (!$existing) {
            $page_id = wp_insert_post(array(
                'post_title'   => $page['title'],
                'post_name'    => $page['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));
            if ($page_id && !is_wp_error($page_id)) {
                update_post_meta($page_id, '_wp_page_template', $page['template']);
            }
        } else {
            // Garante que o template está definido mesmo que a página já exista
            update_post_meta($existing->ID, '_wp_page_template', $page['template']);
        }
    }

    // Define a página "Início" como página inicial (front page)
    $front_page = get_page_by_path('inicio');
    if ($front_page) {
        update_option('page_on_front', $front_page->ID);
        update_option('show_on_front', 'page');
    }

    update_option('eventshouse_pages_created_v2', '1', true);
}
add_action('after_switch_theme', 'EventsHouse_create_pages');
// Também executa em admin_init para garantir que as páginas existem
add_action('admin_init', 'EventsHouse_create_pages');

/**
 * ═══════════════════════════════════════════════════
 * SISTEMA DE PASSES - SETUP DB, ROLES, E SEGURANÇA
 * ═══════════════════════════════════════════════════
 */
function ilha_setup_system() {
    // Evita executar dbDelta em 4 tabelas a cada requisição no wp-admin
    if (did_action('after_switch_theme') === 0 && get_option('ilha_tables_installed_v2') === '1') {
        return;
    }

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Tabela: wp_ilha_clientes
    $table_clientes = $wpdb->prefix . 'ilha_clientes';
    $sql_clientes = "CREATE TABLE $table_clientes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) DEFAULT NULL,
        nome varchar(255) NOT NULL,
        email varchar(255) DEFAULT NULL,
        telefone varchar(50) DEFAULT NULL,
        notas text DEFAULT NULL,
        data_registo datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql_clientes);

    // Tabela: wp_ilha_passes
    $table_passes = $wpdb->prefix . 'ilha_passes';
    $sql_passes = "CREATE TABLE $table_passes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        cliente_id bigint(20) NOT NULL,
        tipo varchar(50) NOT NULL,
        dias_total int(11) NOT NULL DEFAULT 0,
        dias_usados int(11) NOT NULL DEFAULT 0,
        data_compra datetime NOT NULL,
        data_inicio datetime DEFAULT NULL,
        data_expiracao datetime DEFAULT NULL,
        status varchar(50) DEFAULT 'ativo' NOT NULL,
        produto_wc_id bigint(20) DEFAULT NULL,
        order_wc_id bigint(20) DEFAULT NULL,
        extra_adultos_total int(11) NOT NULL DEFAULT 0,
        extra_criancas_total int(11) NOT NULL DEFAULT 0,
        extra_adultos_usados int(11) NOT NULL DEFAULT 0,
        extra_criancas_usados int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql_passes);

    // Tabela: wp_ilha_utilizacoes
    $table_utilizacoes = $wpdb->prefix . 'ilha_utilizacoes';
    $sql_utilizacoes = "CREATE TABLE $table_utilizacoes (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        passe_id bigint(20) NOT NULL,
        cliente_id bigint(20) NOT NULL,
        data_utilizacao datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        registado_por bigint(20) DEFAULT NULL,
        notas text DEFAULT NULL,
        extra_adultos_usados int(11) NOT NULL DEFAULT 0,
        extra_criancas_usados int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    dbDelta($sql_utilizacoes);

    // Tabela: wp_ilha_reservas (Manhãs na Ilha)
    $table_reservas = $wpdb->prefix . 'ilha_reservas';
    $sql_reservas = "CREATE TABLE $table_reservas (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        cliente_id bigint(20) DEFAULT NULL,
        passe_id bigint(20) DEFAULT NULL,
        data_reserva date NOT NULL,
        event_title varchar(255) DEFAULT NULL,
        event_time varchar(100) DEFAULT NULL,
        nome varchar(255) NOT NULL,
        email varchar(255) DEFAULT NULL,
        telefone varchar(50) DEFAULT NULL,
        idade_crianca smallint(3) unsigned DEFAULT NULL,
        status varchar(20) DEFAULT 'confirmada' NOT NULL,
        criada_em datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        registado_por bigint(20) DEFAULT NULL,
        gcal_event_id varchar(128) DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY data_reserva (data_reserva),
        KEY cliente_id (cliente_id),
        KEY status (status),
        UNIQUE KEY gcal_event_id (gcal_event_id)
    ) $charset_collate;";
    dbDelta($sql_reservas);

    // Criar Cargo "Rececionista"
    if (!get_role('rececionista')) {
        add_role('rececionista', 'Rececionista', array('read' => true));
    }

    update_option('ilha_tables_installed_v2', '1', true);
}
add_action('after_switch_theme', 'ilha_setup_system');
add_action('admin_init', 'ilha_setup_system');

/**
 * Bloquear acesso ao wp-admin para o Rececionista
 */
function ilha_block_wp_admin_for_rececionista() {
    if (is_admin() && !defined('DOING_AJAX') && ilha_user_can_access_rececao() && !user_can(get_current_user_id(), 'manage_options')) {
        wp_redirect(home_url('/rececao'));
        exit;
    }
}
add_action('admin_init', 'ilha_block_wp_admin_for_rececionista');

/**
 * Redirecionar para home/receção após o login caso seja rececionista
 */
function ilha_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('rececionista', $user->roles)) {
            return home_url('/rececao');
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'ilha_login_redirect', 10, 3);

/**
 * Enqueue Scripts and Styles
 */
function EventsHouse_scripts()
{
    wp_enqueue_style(
        'EventsHouse-google-fonts',
        'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'EventsHouse-main',
        EventsHouse_URI . '/assets/css/main.css',
        array(),
        EventsHouse_VERSION
    );

    wp_enqueue_style(
        'EventsHouse-style',
        get_stylesheet_uri(),
        array('EventsHouse-main'),
        EventsHouse_VERSION
    );

    wp_enqueue_script(
        'EventsHouse-main',
        EventsHouse_URI . '/assets/js/main.js',
        array(),
        EventsHouse_VERSION,
        true
    );

    wp_localize_script('EventsHouse-main', 'EventsHouseData', array(
        'ajaxUrl'  => admin_url('admin-ajax.php'),
        'themeUrl' => EventsHouse_URI,
        'nonce'    => wp_create_nonce('EventsHouse_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'EventsHouse_scripts');

/**
 * CUSTOMIZER — apenas largura do logo (opções do tema em Conteúdos do Site / EventsThemes).
 */
function EventsHouse_customize_register($wp_customize)
{
    $wp_customize->add_setting('logo_max_width', array(
        'default'           => 200,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('logo_max_width', array(
        'label'       => __('Largura máxima do logo (px)', 'EventsHouse'),
        'section'     => 'title_tagline',
        'type'        => 'number',
        'description' => __('Largura do logo no cabeçalho (omissão: 200px).', 'EventsHouse'),
    ));
}
add_action('customize_register', 'EventsHouse_customize_register');

/**
 * ═══════════════════════════════════════════════════
 * PÁGINA DE OPÇÕES ADMIN — Galeria & Serviços
 * ═══════════════════════════════════════════════════
 */
function EventsHouse_admin_menu()
{
    add_theme_page(
        __('Conteúdos do Site', 'EventsHouse'),
        __('🎉 Conteúdos do Site', 'EventsHouse'),
        'manage_options',
        'eventshouse-options',
        'EventsHouse_options_page'
    );
}
add_action('admin_menu', 'EventsHouse_admin_menu');

/**
 * Oculta «Conteúdos do Site» em Aparência; a página continua em themes.php?page=eventshouse-options (atalhos no EventsThemes).
 */
function EventsHouse_admin_menu_hide_options_under_appearance()
{
    remove_submenu_page('themes.php', 'eventshouse-options');
}
add_action('admin_menu', 'EventsHouse_admin_menu_hide_options_under_appearance', 99);

function EventsHouse_admin_scripts($hook)
{
    $is_legacy_options = ($hook === 'appearance_page_eventshouse-options');
    $is_events_themes  = (strpos((string) $hook, 'events-themes') !== false);
    if (!$is_legacy_options && !$is_events_themes) {
        return;
    }
    wp_enqueue_editor();
    wp_enqueue_media();
    wp_enqueue_style('eventshouse-admin-css', EventsHouse_URI . '/assets/css/admin-options.css', array(), EventsHouse_VERSION);
    wp_enqueue_style('eventshouse-admin-gcal-css', EventsHouse_URI . '/assets/css/admin-gcal.css', array(), EventsHouse_VERSION);
    if ($is_events_themes) {
        wp_enqueue_style('eventshouse-events-themes-css', EventsHouse_URI . '/assets/css/admin-events-themes.css', array('eventshouse-admin-css'), EventsHouse_VERSION);
    }
    wp_enqueue_script('eventshouse-admin-js', EventsHouse_URI . '/assets/js/admin-options.js', array('jquery'), EventsHouse_VERSION, true);
    wp_localize_script('eventshouse-admin-js', 'EHAdminEmailTest', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('eventshouse_send_test_email'),
    ));
}
add_action('admin_enqueue_scripts', 'EventsHouse_admin_scripts');

function render_orcamento_card_fields($sec, $idx, $card, $is_extra = false) {
    ?>
    <div class="orcamento-card-admin" <?php if($is_extra) echo 'data-index="'.$idx.'"'; ?> style="background:#fff; border:1px solid #ccd0d4; padding:15px; margin-bottom:15px;">
        <div style="display:flex; gap:10px; margin-bottom:10px;">
            <div style="flex:1;">
                <label><strong>Título</strong></label><br>
                <input type="text" name="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][title]" value="<?php echo esc_attr($card['title'] ?? ''); ?>" style="width:100%" />
            </div>
            <div style="flex:1;">
                <label><strong>Horário</strong></label><br>
                <input type="text" name="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][time]" value="<?php echo esc_attr($card['time'] ?? ''); ?>" style="width:100%" />
            </div>
            <div style="flex:0.5;">
                <label><strong>Preço (€)</strong></label><br>
                <input type="text" name="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][price]" value="<?php echo esc_attr($card['price'] ?? ''); ?>" style="width:100%" />
            </div>
        </div>
        <div>
            <label><strong>Descrição</strong></label><br>
            <textarea name="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][desc]" rows="2" style="width:100%"><?php echo esc_textarea($card['desc'] ?? ''); ?></textarea>
        </div>
        <?php if ($sec === 'extras') : ?>
        <div style="margin-top:10px;" class="orcamento-image-wrapper">
            <label><strong>Imagem</strong></label><br>
            <div style="display:flex; gap:10px; align-items:center;">
                <input type="text" name="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][image]" value="<?php echo esc_url($card['image'] ?? ''); ?>" style="flex:1;" />
                <button type="button" class="button eh-media-btn" data-target="orcamento_<?php echo $sec; ?>[<?php echo $idx; ?>][image]">🖼 Escolher Imagem</button>
            </div>
            <div class="orcamento-image-preview" style="margin-top:5px; max-width: 150px;">
                <?php if (!empty($card['image'])) : ?>
                    <img src="<?php echo esc_url($card['image']); ?>" alt="" style="max-width:100%; height:auto;" />
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($is_extra) : ?>
        <p style="text-align:right;margin:10px 0 0 0;">
            <button type="button" class="button button-small remove-orcamento-btn" data-section="<?php echo esc_attr($sec); ?>" style="color:#b22;">✕ Remover</button>
        </p>
        <?php endif; ?>
    </div>
    <?php
}

function EventsHouse_options_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Salvar opções
    if (isset($_POST['eventshouse_save']) && check_admin_referer('eventshouse_options_nonce')) {

        // Serviços / Amenidades
        $services = array();
        if (isset($_POST['services'])) {
            foreach ($_POST['services'] as $s) {
                $services[] = array(
                    'icon' => sanitize_text_field($s['icon']),
                    'name' => sanitize_text_field($s['name']),
                );
            }
        }
        update_option('eventshouse_services', $services);

        // Galeria — Página Galeria
        $gallery_page = array();
        if (isset($_POST['gallery_page'])) {
            foreach ($_POST['gallery_page'] as $g) {
                $gallery_page[] = array(
                    'url'      => esc_url_raw($g['url']),
                    'alt'      => sanitize_text_field($g['alt']),
                    'category' => sanitize_text_field($g['category']),
                );
            }
        }
        update_option('eventshouse_gallery_page', $gallery_page);

        // Google Calendar Settings
        if (isset($_POST['gcal_api_key'])) {
            update_option('eventshouse_gcal_api_key',     sanitize_text_field($_POST['gcal_api_key']));
            update_option('eventshouse_gcal_calendar_id', sanitize_text_field($_POST['gcal_calendar_id']));
            update_option('eventshouse_gcal_months',      absint($_POST['gcal_months'] ?? 2));
            update_option('eventshouse_gcal_slots',       sanitize_textarea_field($_POST['gcal_slots']));
            update_option('eventshouse_gcal_ilha_calendar_id', sanitize_text_field($_POST['gcal_ilha_calendar_id'] ?? ''));
            update_option('eventshouse_gcal_ilha_slots', sanitize_textarea_field($_POST['gcal_ilha_slots'] ?? ''));
            // Limpar cache ao guardar
            EventsHouse_invalidate_gcal_cache();
        }

        // Orçamentos
        $sections = array('fimsemana', 'uteis', 'extras');
        foreach ($sections as $sec) {
            $data = array();
            if (isset($_POST['orcamento_' . $sec]) && is_array($_POST['orcamento_' . $sec])) {
                foreach ($_POST['orcamento_' . $sec] as $card) {
                    $item = array(
                        'title' => sanitize_text_field($card['title'] ?? ''),
                        'time'  => sanitize_text_field($card['time'] ?? ''),
                        'desc'  => sanitize_textarea_field($card['desc'] ?? ''),
                        'price' => sanitize_text_field($card['price'] ?? ''),
                    );
                    if ($sec === 'extras') {
                        $item['image'] = esc_url_raw($card['image'] ?? '');
                    }
                    $data[] = $item;
                }
            }
            update_option('eventshouse_orcamento_' . $sec, $data);
        }
        update_option('eventshouse_orcamento_header_title', sanitize_text_field($_POST['orcamento_header_title'] ?? 'Festas'));
        update_option('eventshouse_orcamento_header_subtitle', sanitize_text_field($_POST['orcamento_header_subtitle'] ?? 'Escolha o pacote ideal para a sua celebração'));
        update_option('eventshouse_orcamento_tab_fimsemana', sanitize_text_field(wp_unslash($_POST['orcamento_tab_fimsemana'] ?? 'Fim de Semana e Feriados')));
        update_option('eventshouse_orcamento_tab_uteis', sanitize_text_field(wp_unslash($_POST['orcamento_tab_uteis'] ?? '2ª a 6ª feira')));
        update_option('eventshouse_orcamento_tab_extras', sanitize_text_field(wp_unslash($_POST['orcamento_tab_extras'] ?? 'Extras')));

        // Hero / página inicial (theme_mod — igual ao Personalizar)
        if (isset($_POST['eh_hero_fields'])) {
            set_theme_mod('banner_mode', sanitize_text_field($_POST['banner_mode'] ?? 'slide'));
            set_theme_mod('hero_badge', sanitize_text_field(wp_unslash($_POST['hero_badge'] ?? '')));
            set_theme_mod('hero_title', sanitize_text_field(wp_unslash($_POST['hero_title'] ?? '')));
            set_theme_mod('hero_subtitle', sanitize_textarea_field(wp_unslash($_POST['hero_subtitle'] ?? '')));
            set_theme_mod('hero_btn1_text', sanitize_text_field(wp_unslash($_POST['hero_btn1_text'] ?? '')));
            set_theme_mod('hero_btn2_text', sanitize_text_field(wp_unslash($_POST['hero_btn2_text'] ?? '')));
            set_theme_mod('hero_btn1_url', esc_url_raw($_POST['hero_btn1_url'] ?? ''));
            set_theme_mod('hero_btn2_url', esc_url_raw($_POST['hero_btn2_url'] ?? ''));
            for ($hi = 1; $hi <= 3; $hi++) {
                set_theme_mod("hero_slide_{$hi}_image", esc_url_raw($_POST["hero_slide_{$hi}_image"] ?? ''));
                set_theme_mod("hero_slide_{$hi}_alt", sanitize_text_field(wp_unslash($_POST["hero_slide_{$hi}_alt"] ?? '')));
            }
        }
        // Coming Soon (template page-coming-soon-manhas.php)
        set_theme_mod('ilha_coming_soon_badge', sanitize_text_field(wp_unslash($_POST['ilha_coming_soon_badge'] ?? '')));
        set_theme_mod('ilha_coming_soon_title', sanitize_text_field(wp_unslash($_POST['ilha_coming_soon_title'] ?? '')));
        set_theme_mod('ilha_coming_soon_subtitle', sanitize_textarea_field(wp_unslash($_POST['ilha_coming_soon_subtitle'] ?? '')));
        for ($ci = 1; $ci <= 3; $ci++) {
            set_theme_mod("ilha_coming_soon_slide{$ci}_image", esc_url_raw($_POST["ilha_coming_soon_slide{$ci}_image"] ?? ''));
            set_theme_mod("ilha_coming_soon_slide{$ci}_alt", sanitize_text_field(wp_unslash($_POST["ilha_coming_soon_slide{$ci}_alt"] ?? '')));
        }

        EventsHouse_save_home_sections_from_post();

        // Contactos (theme_mod — mesmos campos do Personalizar)
        set_theme_mod('contact_phone', sanitize_text_field(wp_unslash($_POST['contact_phone'] ?? '')));
        set_theme_mod('contact_email', sanitize_email(wp_unslash($_POST['contact_email'] ?? '')));
        set_theme_mod('contact_address', sanitize_text_field(wp_unslash($_POST['contact_address'] ?? '')));
        set_theme_mod('contato_header_title', sanitize_text_field(wp_unslash($_POST['contato_header_title'] ?? '')));
        set_theme_mod('contato_header_subtitle', sanitize_text_field(wp_unslash($_POST['contato_header_subtitle'] ?? '')));
        set_theme_mod('contato_schedule', sanitize_textarea_field(wp_unslash($_POST['contato_schedule'] ?? '')));
        set_theme_mod('contato_maps_url', sanitize_textarea_field(wp_unslash($_POST['contato_maps_url'] ?? '')));

        // Emails (textos e template padrão)
        $email_defaults = EventsHouse_email_defaults_map();
        foreach ($email_defaults as $opt_key => $default_value) {
            $raw_val = isset($_POST[$opt_key]) ? wp_unslash($_POST[$opt_key]) : $default_value;
            if (strpos($opt_key, 'color') !== false) {
                $val = sanitize_hex_color($raw_val);
                if (!$val) {
                    $val = $default_value;
                }
            } elseif (strpos($opt_key, '_body') !== false) {
                $val = wp_kses($raw_val, EventsHouse_email_allowed_html_tags());
            } elseif (strpos($opt_key, 'footer_note') !== false) {
                $val = sanitize_textarea_field($raw_val);
            } elseif (strpos($opt_key, 'webhook') !== false) {
                $val = esc_url_raw($raw_val);
            } else {
                $val = sanitize_text_field($raw_val);
            }
            update_option($opt_key, $val);
        }

        if (isset($_POST['eventshouse_festa_order_email_subject'])) {
            update_option('eventshouse_festa_order_email_subject', sanitize_text_field(wp_unslash($_POST['eventshouse_festa_order_email_subject'])));
            update_option('eventshouse_festa_order_email_title', sanitize_text_field(wp_unslash($_POST['eventshouse_festa_order_email_title'] ?? '')));
            update_option('eventshouse_festa_order_email_body', wp_kses(wp_unslash($_POST['eventshouse_festa_order_email_body'] ?? ''), EventsHouse_email_allowed_html_tags()));
        }

        EventsHouse_save_ilha_passes_page_from_post();

        EventsHouse_save_verao_ilha_page_from_post();

        EventsHouse_save_footer_from_post();

        echo '<div class="notice notice-success"><p>✅ <strong>Configurações guardadas com sucesso!</strong></p></div>';
    }

    // Ler opções salvas
    $services_defaults = array(
        array('icon' => '❄️',  'name' => 'Ar Condicionado'),
        array('icon' => '🎮', 'name' => 'Jogos e Consolas'),
        array('icon' => '🛡️', 'name' => 'Segurança'),
        array('icon' => '🏪', 'name' => 'Microondas'),
        array('icon' => '☕', 'name' => 'Máquina de Café'),
        array('icon' => '📶', 'name' => 'Wi-Fi Grátis'),
        array('icon' => '🎵', 'name' => 'Sistema de Som'),
        array('icon' => '📺', 'name' => 'Projetor'),
        array('icon' => '🧸', 'name' => 'Brinquedos'),
        array('icon' => '🪑', 'name' => 'Mesas e Cadeiras'),
        array('icon' => '🎈', 'name' => 'Balões e Decoração'),
        array('icon' => '🍽️', 'name' => 'Pratos e Talheres'),
    );
    $services = get_option('eventshouse_services', $services_defaults);

    $gallery_page_defaults = array(
        array('url' => EventsHouse_URI . '/assets/images/gallery-1.jpg', 'alt' => 'Sala principal',       'category' => 'espaco'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-2.jpg', 'alt' => 'Área de brincadeiras', 'category' => 'espaco'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-3.jpg', 'alt' => 'Festa de aniversário', 'category' => 'festas'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-4.jpg', 'alt' => 'Decoração temática',  'category' => 'decoracao'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-5.jpg', 'alt' => 'Cozinha equipada',    'category' => 'espaco'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-6.jpg', 'alt' => 'Crianças a brincar',  'category' => 'festas'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-7.jpg', 'alt' => 'Decoração com balões','category' => 'decoracao'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-8.jpg', 'alt' => 'Exterior do espaço',  'category' => 'espaco'),
        array('url' => EventsHouse_URI . '/assets/images/hero-1.jpg',    'alt' => 'Festa temática',      'category' => 'festas'),
        array('url' => EventsHouse_URI . '/assets/images/hero-2.jpg',    'alt' => 'Mesa decorada',       'category' => 'decoracao'),
        array('url' => EventsHouse_URI . '/assets/images/hero-3.jpg',    'alt' => 'Área de jogos',       'category' => 'espaco'),
        array('url' => EventsHouse_URI . '/assets/images/gallery-1.jpg', 'alt' => 'Celebração',          'category' => 'festas'),
    );
    $gallery_page = get_option('eventshouse_gallery_page', $gallery_page_defaults);

    $hero_badge     = get_theme_mod('hero_badge', '🎉 Espaço de Eventos Infantis');
    $hero_title     = get_theme_mod('hero_title', 'Venham brincar, sonhar e ser felizes!');
    $hero_subtitle  = get_theme_mod('hero_subtitle', 'Um espaço mágico feito para criar momentos inesquecíveis. Festas de aniversário, eventos e muita diversão para os mais pequenos!');
    $hero_btn1_text = get_theme_mod('hero_btn1_text', 'Ver Festas & Preços');
    $hero_btn2_text = get_theme_mod('hero_btn2_text', 'Reservar Agora!');
    $hero_btn1_url  = get_theme_mod('hero_btn1_url', '');
    $hero_btn2_url  = get_theme_mod('hero_btn2_url', '');
    $banner_mode    = get_theme_mod('banner_mode', 'slide');

    $contact_phone           = get_theme_mod('contact_phone', EventsHouse_DEFAULT_CONTACT_PHONE);
    $contact_email           = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
    $contact_address         = get_theme_mod('contact_address', 'Rua Virgílio Martinho 3A, 1900-821 Lisboa');
    $contato_header_title    = get_theme_mod('contato_header_title', 'Contactos 💌');
    $contato_header_subtitle = get_theme_mod('contato_header_subtitle', 'Estamos aqui para ajudar! Entre em contacto connosco');
    $contato_schedule        = get_theme_mod('contato_schedule', "Segunda a Domingo\n10h00 - 20h00");
    $contato_maps_url        = get_theme_mod('contato_maps_url', '');
    $hero_slide_img = array();
    $hero_slide_alt = array();
    for ($hi = 1; $hi <= 3; $hi++) {
        $hero_slide_img[$hi] = get_theme_mod("hero_slide_{$hi}_image", '');
        $hero_slide_alt[$hi] = get_theme_mod("hero_slide_{$hi}_alt", '');
    }
    $coming_soon_badge    = get_theme_mod('ilha_coming_soon_badge', 'Manhãs na Ilha');
    $coming_soon_title    = get_theme_mod('ilha_coming_soon_title', 'Brevemente');
    $coming_soon_subtitle = get_theme_mod('ilha_coming_soon_subtitle', 'Estamos a preparar o lançamento das manhãs na ilha! Um espaço para pais e crianças durante a semana.');
    $coming_soon_slide_img = array();
    $coming_soon_slide_alt = array();
    for ($ci = 1; $ci <= 3; $ci++) {
        $coming_soon_slide_img[$ci] = get_theme_mod("ilha_coming_soon_slide{$ci}_image", '');
        $coming_soon_slide_alt[$ci] = get_theme_mod("ilha_coming_soon_slide{$ci}_alt", '');
    }
    $email_defaults = EventsHouse_email_defaults_map();
    $email_values = array();
    foreach ($email_defaults as $opt_key => $default_value) {
        $email_values[$opt_key] = get_option($opt_key, $default_value);
    }
    $festa_email_subject_cur = get_option('eventshouse_festa_order_email_subject', function_exists('EventsHouse_festa_order_default_email_subject') ? EventsHouse_festa_order_default_email_subject() : '');
    $festa_email_title_cur   = get_option('eventshouse_festa_order_email_title', function_exists('EventsHouse_festa_order_default_email_title') ? EventsHouse_festa_order_default_email_title() : __('Pagamento da Reserva', 'EventsHouse'));
    $festa_email_body_cur    = get_option('eventshouse_festa_order_email_body', function_exists('EventsHouse_festa_order_default_email_body') ? EventsHouse_festa_order_default_email_body() : '');

    $eh_ctx           = isset($GLOBALS['eventshouse_editor_context']) ? $GLOBALS['eventshouse_editor_context'] : null;
    $eh_preview_url   = isset($GLOBALS['eventshouse_editor_preview_url']) ? $GLOBALS['eventshouse_editor_preview_url'] : '';
    $eh_editor_h1     = isset($GLOBALS['eventshouse_editor_h1']) ? $GLOBALS['eventshouse_editor_h1'] : '';

    ?>
    <div class="wrap eventshouse-admin-wrap<?php echo $eh_ctx ? ' eventshouse-admin-wrap--context' : ''; ?>">
        <?php if ($eh_preview_url) : ?>
        <div class="eventshouse-editor-preview-bar">
            <iframe class="eventshouse-preview-iframe" src="<?php echo esc_url($eh_preview_url); ?>" title="<?php esc_attr_e('Pré-visualização da página', 'EventsHouse'); ?>"></iframe>
            <p class="description" style="margin:8px 0 16px;">
                <a href="<?php echo esc_url($eh_preview_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir página no site ↗', 'EventsHouse'); ?></a>
            </p>
        </div>
        <?php endif; ?>
        <?php if ($eh_editor_h1) : ?>
        <h1><?php echo esc_html($eh_editor_h1); ?></h1>
        <?php else : ?>
        <h1>🎉 EventsHouse — Conteúdos do Site</h1>
        <?php endif; ?>
        <?php if (!$eh_ctx) : ?>
        <p>Edite os conteúdos abaixo e clique em <strong>Guardar Alterações</strong>. No <strong>Personalizar</strong> mantém-se apenas a <strong>identidade do site</strong> (logo) e a <strong>largura do logo</strong>; o restante do tema está aqui.</p>
        <?php else : ?>
        <p class="description"><?php esc_html_e('Os campos abaixo correspondem a esta página. Guarde no fim do formulário.', 'EventsHouse'); ?> <a href="<?php echo esc_url(admin_url('themes.php?page=eventshouse-options')); ?>"><?php esc_html_e('Abrir editor completo (todas as secções)', 'EventsHouse'); ?></a></p>
        <?php endif; ?>
        <?php
        if ($eh_ctx) {
            if ($eh_ctx === 'inicio') {
                echo '<style id="eventshouse-tab-context-css">
                .eventshouse-admin-wrap--context .eventshouse-tabs{display:none!important;}
                .eventshouse-admin-wrap--context .eh-tab-content{display:none!important;}
                .eventshouse-admin-wrap--context #tab-hero,
                .eventshouse-admin-wrap--context #tab-home-sections,
                .eventshouse-admin-wrap--context #tab-services{display:block!important;}
                </style>';
            } else {
                $tab_map = array(
                    'services'  => 'services',
                    'gallery'   => 'gallery',
                    'gcal'      => 'gcal',
                    'orcamento' => 'orcamento',
                    'hero'      => 'hero',
                    'contact'   => 'contact',
                    'emails'    => 'emails',
                    'manhas'    => 'manhas',
                    'verao_ilha' => 'verao-ilha',
                    'coming-soon' => 'coming-soon',
                    'footer'    => 'footer',
                    'rodape'    => 'footer',
                );
                $active = isset($tab_map[$eh_ctx]) ? $tab_map[$eh_ctx] : $eh_ctx;
                echo '<style id="eventshouse-tab-context-css">
                .eventshouse-admin-wrap--context .eventshouse-tabs{display:none!important;}
                .eventshouse-admin-wrap--context .eh-tab-content{display:none!important;}
                .eventshouse-admin-wrap--context #tab-' . esc_attr($active) . '{display:block!important;}
                </style>';
            }
        }
        ?>

        <form method="post" action="">
            <?php wp_nonce_field('eventshouse_options_nonce'); ?>

            <div class="eventshouse-tabs">
                <button type="button" class="eh-tab-btn active" data-tab="hero">🏠 Hero / Início</button>
                <button type="button" class="eh-tab-btn" data-tab="home-sections">📦 Secções da página inicial</button>
                <button type="button" class="eh-tab-btn" data-tab="services">🛠️ Serviços / Amenidades</button>
                <button type="button" class="eh-tab-btn" data-tab="gallery">🖼️ Galeria (Página Galeria)</button>
                <button type="button" class="eh-tab-btn" data-tab="gcal">🗓️ Google Agenda</button>
                <button type="button" class="eh-tab-btn" data-tab="orcamento">💰 Preços / Orçamento</button>
                <button type="button" class="eh-tab-btn" data-tab="contact">💌 Contactos</button>
                <button type="button" class="eh-tab-btn" data-tab="emails">✉️ Emails</button>
                <button type="button" class="eh-tab-btn" data-tab="manhas">🌅 Manhãs na Ilha</button>
                <button type="button" class="eh-tab-btn" data-tab="verao-ilha">☀️ Verão na Ilha</button>
                <button type="button" class="eh-tab-btn" data-tab="coming-soon">⏳ Coming Soon</button>
                <button type="button" class="eh-tab-btn" data-tab="footer">🦶 Rodapé</button>
            </div>

            <input type="hidden" name="eh_hero_fields" value="1" />

            <!-- TAB: HERO -->
            <div class="eh-tab-content active" id="tab-hero">
                <h2>🏠 Hero — Página inicial</h2>
                <p class="description"><?php esc_html_e('Estes campos são os mesmos de Aparência → Personalizar → Hero / Banner. Altere o texto principal, botões e imagens do slideshow.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="banner_mode"><?php esc_html_e('Modo do banner', 'EventsHouse'); ?></label></th>
                        <td>
                            <select name="banner_mode" id="banner_mode">
                                <option value="slide" <?php selected($banner_mode, 'slide'); ?>><?php esc_html_e('Slideshow', 'EventsHouse'); ?></option>
                                <option value="bubbles" <?php selected($banner_mode, 'bubbles'); ?>><?php esc_html_e('Bubbles animadas', 'EventsHouse'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_badge"><?php esc_html_e('Badge (linha pequena acima do título)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="hero_badge" id="hero_badge" value="<?php echo esc_attr($hero_badge); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_title"><?php esc_html_e('Título principal', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="hero_title" id="hero_title" value="<?php echo esc_attr($hero_title); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_subtitle"><?php esc_html_e('Texto / parágrafo', 'EventsHouse'); ?></label></th>
                        <td><textarea name="hero_subtitle" id="hero_subtitle" class="large-text" rows="4"><?php echo esc_textarea($hero_subtitle); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_btn1_text"><?php esc_html_e('Texto do botão 1', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="hero_btn1_text" id="hero_btn1_text" value="<?php echo esc_attr($hero_btn1_text); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_btn2_text"><?php esc_html_e('Texto do botão 2', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="hero_btn2_text" id="hero_btn2_text" value="<?php echo esc_attr($hero_btn2_text); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_btn1_url"><?php esc_html_e('Link do botão 1', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="hero_btn1_url" id="hero_btn1_url" value="<?php echo esc_attr($hero_btn1_url); ?>" placeholder="<?php echo esc_attr(home_url('/orcamento')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="hero_btn2_url"><?php esc_html_e('Link do botão 2', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="hero_btn2_url" id="hero_btn2_url" value="<?php echo esc_attr($hero_btn2_url); ?>" placeholder="<?php echo esc_attr(home_url('/contato')); ?>" /></td>
                    </tr>
                    <?php for ($hi = 1; $hi <= 3; $hi++) : ?>
                    <tr>
                        <th scope="row"><?php echo esc_html(sprintf(__('Slide %d — imagem', 'EventsHouse'), $hi)); ?></th>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="text" class="large-text" name="hero_slide_<?php echo (int) $hi; ?>_image" id="hero_slide_<?php echo (int) $hi; ?>_image" value="<?php echo esc_url($hero_slide_img[$hi]); ?>" placeholder="URL" />
                                <button type="button" class="button eh-media-btn" data-target="hero_slide_<?php echo (int) $hi; ?>_image"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            </div>
                            <div class="eh-hero-slide-preview" style="margin-top:8px;">
                            <?php if (!empty($hero_slide_img[$hi])) : ?>
                                <img src="<?php echo esc_url($hero_slide_img[$hi]); ?>" alt="" style="max-width:220px;height:auto;border:1px solid #ccc;border-radius:4px;" />
                            <?php endif; ?>
                            </div>
                            <p><label for="hero_slide_<?php echo (int) $hi; ?>_alt"><?php esc_html_e('Descrição (alt)', 'EventsHouse'); ?></label><br />
                            <input type="text" class="large-text" name="hero_slide_<?php echo (int) $hi; ?>_alt" id="hero_slide_<?php echo (int) $hi; ?>_alt" value="<?php echo esc_attr($hero_slide_alt[$hi]); ?>" /></p>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>

            <?php EventsHouse_render_admin_tab_home_sections(); ?>

            <!-- TAB: SERVIÇOS -->
            <div class="eh-tab-content" id="tab-services">
                <h2>Serviços / Amenidades</h2>
                <p>Os ícones são emojis. Pode usar qualquer emoji como ícone.</p>
                <table class="wp-list-table widefat fixed striped" id="services-table">
                    <thead><tr><th style="width:80px">Ordem</th><th style="width:100px">Ícone</th><th>Nome</th><th style="width:80px">Ação</th></tr></thead>
                    <tbody>
                        <?php foreach ($services as $idx => $service) : ?>
                        <tr class="service-row">
                            <td><span class="drag-handle">⠿</span></td>
                            <td><input type="text" name="services[<?php echo $idx; ?>][icon]" value="<?php echo esc_attr($service['icon']); ?>" style="width:60px;font-size:20px;text-align:center;" /></td>
                            <td><input type="text" name="services[<?php echo $idx; ?>][name]" value="<?php echo esc_attr($service['name']); ?>" style="width:100%;" /></td>
                            <td><button type="button" class="button button-small remove-row">✕ Remover</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><button type="button" id="add-service" class="button">+ Adicionar Serviço</button></p>
            </div>

            <!-- TAB: GALERIA DA PÁGINA -->
            <div class="eh-tab-content" id="tab-gallery">
                <h2>Galeria — Página Galeria</h2>
                <p>Adicione, remova ou altere as imagens da página de galeria. Cada imagem tem uma categoria para os filtros.</p>
                <div id="gallery-page-grid">
                    <?php foreach ($gallery_page as $idx => $img) : ?>
                    <div class="gallery-admin-item" data-index="<?php echo $idx; ?>">
                        <div class="gallery-admin-preview">
                            <?php if (!empty($img['url'])) : ?>
                                <img src="<?php echo esc_url($img['url']); ?>" alt="" />
                            <?php else : ?>
                                <div class="no-image">Sem imagem</div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="gallery_page[<?php echo $idx; ?>][url]" value="<?php echo esc_url($img['url']); ?>" class="gallery-url-input" />
                        <div class="gallery-admin-fields">
                            <button type="button" class="button eh-media-btn" data-target="gallery_page[<?php echo $idx; ?>][url]">🖼 Escolher Imagem</button>
                            <input type="text" name="gallery_page[<?php echo $idx; ?>][alt]" value="<?php echo esc_attr($img['alt']); ?>" placeholder="Alt / Descrição" style="width:100%;margin-top:5px;" />
                            <select name="gallery_page[<?php echo $idx; ?>][category]" style="width:100%;margin-top:5px;">
                                <?php $cats = array('espaco' => 'Espaço', 'festas' => 'Festas', 'decoracao' => 'Decoração'); ?>
                                <?php foreach ($cats as $val => $label) : ?>
                                    <option value="<?php echo $val; ?>" <?php selected($img['category'], $val); ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="button button-small remove-gallery-item" style="margin-top:5px;">✕ Remover</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p><button type="button" id="add-gallery-item" class="button button-primary">+ Adicionar Foto</button></p>
            </div>

            <!-- TAB: GOOGLE AGENDA -->
            <?php
            $gcal_api_key          = get_option('eventshouse_gcal_api_key', '');
            $gcal_calendar_id      = get_option('eventshouse_gcal_calendar_id', '');
            $gcal_ilha_calendar_id = get_option('eventshouse_gcal_ilha_calendar_id', '');
            $gcal_ilha_slots       = get_option('eventshouse_gcal_ilha_slots', '');
            $gcal_months           = get_option('eventshouse_gcal_months', 6);
            $gcal_slots            = get_option('eventshouse_gcal_slots', "10:00-12:30\n13:30-16:00\n17:00-19:30");
            $orcamento_header_title = get_option('eventshouse_orcamento_header_title', get_theme_mod('orcamento_header_title', 'Festas'));
            $orcamento_header_subtitle = get_option('eventshouse_orcamento_header_subtitle', get_theme_mod('orcamento_header_subtitle', 'Escolha o pacote ideal para a sua celebração'));
            ?>
            <div class="eh-tab-content" id="tab-gcal">
                <h2>🗓️ Integração Google Agenda</h2>

                <div class="gcal-guide">
                    <h3>📖 Como configurar</h3>
                    <ol>
                        <li>Abra o <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> e crie um projeto.</li>
                        <li>Aceda a <strong>APIs & Serviços → Biblioteca</strong> e ative a <strong>Google Calendar API</strong>.</li>
                        <li>Aceda a <strong>APIs & Serviços → Credenciais</strong> e clique em <strong>Criar Credenciais → Chave de API</strong>. Copie a chave e cole abaixo.</li>
                        <li>No <a href="https://calendar.google.com" target="_blank">Google Agenda</a>, abra as definições do calendário desejado → <strong>Partilhar com pessoas específicas</strong> → marque <strong>Disponível para todos</strong> (público).</li>
                        <li>Na mesma página de definições, copie o <strong>ID do Calendário</strong> (parece um email, ex: <code>xxxx@group.calendar.google.com</code>) e cole abaixo.</li>
                        <li>Os eventos marcados no Google Agenda aparecerão como dias ocupados 🔴 no calendário do site.</li>
                    </ol>
                </div>

                <table class="form-table">
                    <tr>
                        <th><label for="gcal_api_key">🔑 API Key do Google</label></th>
                        <td>
                            <input type="text" id="gcal_api_key" name="gcal_api_key" value="<?php echo esc_attr($gcal_api_key); ?>" class="regular-text" placeholder="AIzaSy..." />
                            <p class="description">Chave de API do Google Cloud Console (leitura pública).</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gcal_calendar_id">📅 ID do Calendário (Festas / Disponibilidade)</label></th>
                        <td>
                            <input type="text" id="gcal_calendar_id" name="gcal_calendar_id" value="<?php echo esc_attr($gcal_calendar_id); ?>" class="regular-text" placeholder="xxxx@group.calendar.google.com" />
                            <p class="description">O ID do calendário público do Google Agenda para reservas de festas (página Disponibilidade).</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gcal_ilha_calendar_id">🌅 ID do Calendário (Manhãs na Ilha)</label></th>
                        <td>
                            <input type="text" id="gcal_ilha_calendar_id" name="gcal_ilha_calendar_id" value="<?php echo esc_attr($gcal_ilha_calendar_id); ?>" class="regular-text" placeholder="outro-calendario@group.calendar.google.com" />
                            <p class="description">Calendário <strong>separado</strong> para disponibilidade de manhãs (página Manhãs na Ilha). Usa a mesma API Key acima.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gcal_months">📆 Meses a mostrar</label></th>
                        <td>
                            <select id="gcal_months" name="gcal_months">
                                <?php for ($m = 1; $m <= 6; $m++) : ?>
                                    <option value="<?php echo $m; ?>" <?php selected($gcal_months, $m); ?>><?php echo $m; ?> <?php echo $m === 1 ? 'mês' : 'meses'; ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gcal_slots">🕐 Horários disponíveis</label></th>
                        <td>
                            <textarea id="gcal_slots" name="gcal_slots" rows="5" class="large-text"><?php echo esc_textarea($gcal_slots); ?></textarea>
                            <p class="description">Um horário por linha no formato <code>HH:MM-HH:MM</code>. O site cruza cada slot com os eventos do Google: <strong>só os slots em conflito ficam indisponíveis</strong>; o dia só aparece vermelho (sem vagas) quando <strong>todos</strong> os slots estão ocupados ou há evento de dia inteiro.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="gcal_ilha_slots">🕐 Horários (Manhãs na Ilha)</label></th>
                        <td>
                            <textarea id="gcal_ilha_slots" name="gcal_ilha_slots" rows="5" class="large-text" placeholder="<?php esc_attr_e('Deixe vazio para usar os mesmos horários de cima.', 'EventsHouse'); ?>"><?php echo esc_textarea($gcal_ilha_slots); ?></textarea>
                            <p class="description"><?php esc_html_e('Opcional: horários para o calendário da página Manhãs na Ilha. Se vazio, repetem-se os horários da secção anterior.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php if (!empty($gcal_api_key) && !empty($gcal_calendar_id)) : ?>
                <div class="notice notice-info" style="margin:16px 0;">
                    <p>✅ Google Calendar (festas) configurado. O calendário aparece na página de Disponibilidade.</p>
                    <p><a href="<?php echo esc_url(home_url('/disponibilidade')); ?>" target="_blank">Ver página de Disponibilidade →</a></p>
                    <?php if (!empty($gcal_ilha_calendar_id)) : ?>
                        <p>✅ Calendário Manhãs na Ilha definido. <a href="<?php echo esc_url(home_url('/manhas-na-ilha')); ?>" target="_blank">Ver página Manhãs na Ilha →</a></p>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="notice notice-warning" style="margin:16px 0;">
                    <p>⚠️ Google Calendar ainda não configurado. Preencha a API Key e o ID do Calendário acima.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- TAB: ORÇAMENTOS -->
            <?php
            $orc_fimsemana = get_option('eventshouse_orcamento_fimsemana', array(
                array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '200'),
                array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '250'),
                array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => 'Acesso total durante todo o dia sem interrupções.', 'price' => '380')
            ));
            $orc_uteis = get_option('eventshouse_orcamento_uteis', array(
                array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '150'),
                array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '180'),
                array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => 'Acesso total durante todo o dia sem interrupções.', 'price' => '300')
            ));
            $orc_extras = get_option('eventshouse_orcamento_extras', array(
                array('title' => 'Hora Extra', 'time' => '+ 1 hora', 'desc' => 'Adicione tempo extra à sua festa.', 'price' => '50')
            ));
            $orc_tab_fimsemana = get_option('eventshouse_orcamento_tab_fimsemana', 'Fim de Semana e Feriados');
            $orc_tab_uteis     = get_option('eventshouse_orcamento_tab_uteis', '2ª a 6ª feira');
            $orc_tab_extras    = get_option('eventshouse_orcamento_tab_extras', 'Extras');
            ?>

            <div class="eh-tab-content" id="tab-orcamento">
                <h2>💰 Preços / Orçamento</h2>
                <p>Configure aqui os blocos de preços que aparecem na aba respetiva na página de Orçamentos (<a href="<?php echo home_url('/orcamento'); ?>" target="_blank">Ver Página</a>).</p>

                <table class="form-table" style="margin-top:10px;">
                    <tr>
                        <th><label for="orcamento_header_title">Título da página</label></th>
                        <td>
                            <input type="text" id="orcamento_header_title" name="orcamento_header_title" value="<?php echo esc_attr($orcamento_header_title); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="orcamento_header_subtitle">Descrição da página</label></th>
                        <td>
                            <input type="text" id="orcamento_header_subtitle" name="orcamento_header_subtitle" value="<?php echo esc_attr($orcamento_header_subtitle); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="orcamento_tab_fimsemana"><?php esc_html_e('Título da aba (Fim de semana e feriados)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="text" id="orcamento_tab_fimsemana" name="orcamento_tab_fimsemana" value="<?php echo esc_attr($orc_tab_fimsemana); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Texto do botão da primeira tab na página de preços.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="orcamento_tab_uteis"><?php esc_html_e('Título da aba (2ª a 6ª feira)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="text" id="orcamento_tab_uteis" name="orcamento_tab_uteis" value="<?php echo esc_attr($orc_tab_uteis); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Texto do botão da segunda tab.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="orcamento_tab_extras"><?php esc_html_e('Título da aba (Extras)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="text" id="orcamento_tab_extras" name="orcamento_tab_extras" value="<?php echo esc_attr($orc_tab_extras); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Texto do botão da terceira tab.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                </table>

                <div style="display:flex; gap:30px;">
                    <!-- Coluna Fim de Semana -->
                    <div style="flex:1;">
                        <h3 style="border-bottom:2px solid #2271b1; padding-bottom:5px;"><?php echo esc_html($orc_tab_fimsemana); ?></h3>
                        <p class="description">Adicione quantos cards quiser (cards editáveis e removíveis).</p>
                        <div id="orcamento-fimsemana-container">
                            <?php foreach ($orc_fimsemana as $i => $c) : ?>
                                <?php render_orcamento_card_fields('fimsemana', $i, $c, true); ?>
                            <?php endforeach; ?>
                        </div>
                        <p>
                            <button type="button" class="button button-primary" id="add-fimsemana-btn">+ Adicionar Card</button>
                        </p>
                    </div>
                    <!-- Coluna Dias Úteis -->
                    <div style="flex:1;">
                        <h3 style="border-bottom:2px solid #2271b1; padding-bottom:5px;"><?php echo esc_html($orc_tab_uteis); ?></h3>
                        <p class="description">Adicione quantos cards quiser (cards editáveis e removíveis).</p>
                        <div id="orcamento-uteis-container">
                            <?php foreach ($orc_uteis as $i => $c) : ?>
                                <?php render_orcamento_card_fields('uteis', $i, $c, true); ?>
                            <?php endforeach; ?>
                        </div>
                        <p>
                            <button type="button" class="button button-primary" id="add-uteis-btn">+ Adicionar Card</button>
                        </p>
                    </div>
                </div>

                <div style="margin-top:30px;">
                    <h3 style="border-bottom:2px solid #2271b1; padding-bottom:5px;"><?php echo esc_html($orc_tab_extras); ?></h3>
                    <p class="description"><?php esc_html_e('Adicione os serviços ou opções extras que quiser. Estes cards aparecem na aba', 'EventsHouse'); ?> «<?php echo esc_html($orc_tab_extras); ?>».</p>
                    <div id="orcamento-extras-container">
                        <?php
                        foreach ($orc_extras as $idx => $c) {
                            render_orcamento_card_fields('extras', $idx, $c, true);
                        }
                        ?>
                    </div>
                    <p>
                        <button type="button" class="button button-primary" id="add-extra-btn">+ Adicionar Extra</button>
                    </p>
                </div>
            </div>

            <!-- TAB: CONTACTOS -->
            <div class="eh-tab-content" id="tab-contact">
                <h2><?php esc_html_e('💌 Contactos — Página e dados', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('Estes campos alimentam a página de Contactos e coincidem com Aparência → Personalizar → Contactos.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="contato_header_title"><?php esc_html_e('Título da página (cabeçalho)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="contato_header_title" id="contato_header_title" value="<?php echo esc_attr($contato_header_title); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contato_header_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="contato_header_subtitle" id="contato_header_subtitle" value="<?php echo esc_attr($contato_header_subtitle); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contact_address"><?php esc_html_e('Morada', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="contact_address" id="contact_address" value="<?php echo esc_attr($contact_address); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contact_phone"><?php esc_html_e('Telefone', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="contact_phone" id="contact_phone" value="<?php echo esc_attr($contact_phone); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contact_email"><?php esc_html_e('Email', 'EventsHouse'); ?></label></th>
                        <td><input type="email" class="large-text" name="contact_email" id="contact_email" value="<?php echo esc_attr($contact_email); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contato_schedule"><?php esc_html_e('Horário de atendimento', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="contato_schedule" id="contato_schedule" class="large-text" rows="4"><?php echo esc_textarea($contato_schedule); ?></textarea>
                            <p class="description"><?php esc_html_e('Pode usar várias linhas; aparecem na caixa «Horário».', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="contato_maps_url"><?php esc_html_e('Mapa — morada ou link do Google Maps', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="contato_maps_url" id="contato_maps_url" class="large-text" rows="3" placeholder="<?php echo esc_attr__('Morada completa, ou link https://maps.app.goo.gl/... , ou URL de incorporar mapa', 'EventsHouse'); ?>"><?php echo esc_textarea($contato_maps_url); ?></textarea>
                            <p class="description"><?php esc_html_e('Pode colar só a morada (usa-se também a morada acima se isto estiver vazio), um link curto do Google Maps (Partilhar), ou a URL «Incorporar mapa». O site gera o mapa automaticamente.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="eh-tab-content" id="tab-emails">
                <h2><?php esc_html_e('✉️ Emails — textos e padrão visual', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('Ajuste aqui todos os emails automáticos do sistema (Contacto, Disponibilidade, Manhãs na Ilha e Festas na Ilha).', 'EventsHouse'); ?></p>
                <p class="description"><?php esc_html_e('Variáveis: pode usar %name% ou {name}. Exemplos: %email%, %date%, %phone%, %time_slot%, %event_title%, %site_name%.', 'EventsHouse'); ?></p>
                <div class="notice notice-info" style="padding:10px 14px;">
                    <p style="margin:0 0 8px;"><strong><?php esc_html_e('Teste rápido de envio', 'EventsHouse'); ?></strong></p>
                    <p style="margin:0 0 8px;"><?php esc_html_e('Informe abaixo o email de destino e use o botão de teste em cada bloco.', 'EventsHouse'); ?></p>
                    <p style="margin:0;display:flex;gap:8px;align-items:center;max-width:560px;">
                        <input type="email" id="eventshouse_test_email_to" class="regular-text" style="flex:1;" placeholder="teste@exemplo.com" />
                        <span id="eventshouse_test_email_feedback" class="description"></span>
                    </p>
                </div>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="eventshouse_email_brand_color"><?php esc_html_e('Cor principal do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="eventshouse_email_brand_color" id="eventshouse_email_brand_color" value="<?php echo esc_attr($email_values['eventshouse_email_brand_color']); ?>" placeholder="#1f6fb2" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_footer_note"><?php esc_html_e('Texto do rodapé do email', 'EventsHouse'); ?></label></th>
                        <td><textarea name="eventshouse_email_footer_note" id="eventshouse_email_footer_note" class="large-text" rows="2"><?php echo esc_textarea($email_values['eventshouse_email_footer_note']); ?></textarea></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Formulário de Contacto', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_contact_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_contact_subject" id="eventshouse_email_contact_subject" value="<?php echo esc_attr($email_values['eventshouse_email_contact_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_contact_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_contact_title" id="eventshouse_email_contact_title" value="<?php echo esc_attr($email_values['eventshouse_email_contact_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_contact_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_contact_body" id="eventshouse_email_contact_body" class="large-text code" rows="8"><?php echo esc_textarea($email_values['eventshouse_email_contact_body']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis úteis: %name%, %email%, %phone%, %date%, %message%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_contact_success_msg"><?php esc_html_e('Mensagem de sucesso (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_contact_success_msg" id="eventshouse_email_contact_success_msg" value="<?php echo esc_attr($email_values['eventshouse_email_contact_success_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_contact_error_msg"><?php esc_html_e('Mensagem de erro (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_contact_error_msg" id="eventshouse_email_contact_error_msg" value="<?php echo esc_attr($email_values['eventshouse_email_contact_error_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="contact"><?php esc_html_e('Enviar email teste — Contacto', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Formulário de Disponibilidade', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_subject_festas"><?php esc_html_e('Assunto (Festas)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_subject_festas" id="eventshouse_email_avail_subject_festas" value="<?php echo esc_attr($email_values['eventshouse_email_avail_subject_festas']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_title_festas"><?php esc_html_e('Título do cabeçalho (Festas)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_title_festas" id="eventshouse_email_avail_title_festas" value="<?php echo esc_attr($email_values['eventshouse_email_avail_title_festas']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_body_festas"><?php esc_html_e('Corpo (Festas)', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_avail_body_festas" id="eventshouse_email_avail_body_festas" class="large-text code" rows="9"><?php echo esc_textarea($email_values['eventshouse_email_avail_body_festas']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis úteis: %source%, %date%, %time_slot%, %package%, %guests%, %extras%, %name%, %email%, %phone%, %message%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_subject_ilha"><?php esc_html_e('Assunto (Manhãs na Ilha)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_subject_ilha" id="eventshouse_email_avail_subject_ilha" value="<?php echo esc_attr($email_values['eventshouse_email_avail_subject_ilha']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_title_ilha"><?php esc_html_e('Título do cabeçalho (Manhãs na Ilha)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_title_ilha" id="eventshouse_email_avail_title_ilha" value="<?php echo esc_attr($email_values['eventshouse_email_avail_title_ilha']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_body_ilha"><?php esc_html_e('Corpo (Manhãs na Ilha)', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_avail_body_ilha" id="eventshouse_email_avail_body_ilha" class="large-text code" rows="9"><?php echo esc_textarea($email_values['eventshouse_email_avail_body_ilha']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis úteis: %date%, %time_slot%, %package%, %name%, %email%, %phone%, %message%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_success_msg"><?php esc_html_e('Mensagem de sucesso (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_success_msg" id="eventshouse_email_avail_success_msg" value="<?php echo esc_attr($email_values['eventshouse_email_avail_success_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_avail_error_msg"><?php esc_html_e('Mensagem de erro (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_avail_error_msg" id="eventshouse_email_avail_error_msg" value="<?php echo esc_attr($email_values['eventshouse_email_avail_error_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td style="display:flex;gap:8px;flex-wrap:wrap;">
                            <button type="button" class="button button-secondary eh-send-test-email" data-template="avail_festas"><?php esc_html_e('Enviar teste — Disponibilidade (Festas)', 'EventsHouse'); ?></button>
                            <button type="button" class="button button-secondary eh-send-test-email" data-template="avail_ilha"><?php esc_html_e('Enviar teste — Disponibilidade (Manhãs)', 'EventsHouse'); ?></button>
                        </td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Compra de pack/passe — Manhãs na Ilha', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <td colspan="2"><p class="description"><?php esc_html_e('Enviado ao cliente imediatamente após confirmação do pagamento (encomenda concluída com pack ou passe Manhãs na Ilha).', 'EventsHouse'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_pack_purchase_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_pack_purchase_subject" id="eventshouse_email_ilha_pack_purchase_subject" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_pack_purchase_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_pack_purchase_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_pack_purchase_title" id="eventshouse_email_ilha_pack_purchase_title" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_pack_purchase_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_pack_purchase_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_ilha_pack_purchase_body" id="eventshouse_email_ilha_pack_purchase_body" class="large-text code" rows="16"><?php echo esc_textarea($email_values['eventshouse_email_ilha_pack_purchase_body']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis: %name%, %pack_label%, %horario%, %reserve_url%, %order_id%, %site_name%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="ilha_pack_purchase"><?php esc_html_e('Enviar email teste — Compra de pack', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Reserva rápida — Manhãs na Ilha (confirmação ao cliente)', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <td colspan="2"><p class="description"><?php esc_html_e('Enviado imediatamente ao cliente após reservar uma manhã. A equipa recebe uma cópia interna separada (bloco abaixo).', 'EventsHouse'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_subject" id="eventshouse_email_ilha_reservation_subject" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_title" id="eventshouse_email_ilha_reservation_title" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_ilha_reservation_body" id="eventshouse_email_ilha_reservation_body" class="large-text code" rows="8"><?php echo esc_textarea($email_values['eventshouse_email_ilha_reservation_body']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis úteis: %name%, %email%, %phone%, %date%, %date_human%, %date_long%, %horario%, %event_title%, %event_time%, %reserve_url%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_success_msg"><?php esc_html_e('Mensagem de sucesso (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_success_msg" id="eventshouse_email_ilha_reservation_success_msg" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_success_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_error_msg"><?php esc_html_e('Mensagem de erro (site)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_error_msg" id="eventshouse_email_ilha_reservation_error_msg" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_error_msg']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="ilha_reservation"><?php esc_html_e('Enviar email teste — Confirmação reserva', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Reserva Manhãs — Notificação interna (equipa)', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_admin_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_admin_subject" id="eventshouse_email_ilha_reservation_admin_subject" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_admin_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_admin_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_admin_title" id="eventshouse_email_ilha_reservation_admin_title" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_admin_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_admin_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_ilha_reservation_admin_body" id="eventshouse_email_ilha_reservation_admin_body" class="large-text code" rows="8"><?php echo esc_textarea($email_values['eventshouse_email_ilha_reservation_admin_body']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="ilha_reservation_admin"><?php esc_html_e('Enviar email teste — Notificação interna', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Lembrete da reserva — Manhãs na Ilha', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <td colspan="2"><p class="description"><?php esc_html_e('Enviado automaticamente 1 dia antes da reserva às 21h00 (hora de Lisboa).', 'EventsHouse'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_horario_padrao"><?php esc_html_e('Horário padrão (%horario%)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="eventshouse_email_ilha_horario_padrao" id="eventshouse_email_ilha_horario_padrao" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_horario_padrao']); ?>" placeholder="09h00 – 13h00" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_reminder_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_reminder_subject" id="eventshouse_email_ilha_reservation_reminder_subject" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_reminder_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_reminder_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_reminder_title" id="eventshouse_email_ilha_reservation_reminder_title" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_reminder_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_reminder_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_ilha_reservation_reminder_body" id="eventshouse_email_ilha_reservation_reminder_body" class="large-text code" rows="12"><?php echo esc_textarea($email_values['eventshouse_email_ilha_reservation_reminder_body']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis: %name%, %date_long%, %date_human%, %horario%, %reserve_url%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="ilha_reservation_reminder"><?php esc_html_e('Enviar email teste — Lembrete', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Após a visita — Manhãs na Ilha', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <td colspan="2"><p class="description"><?php esc_html_e('Enviado automaticamente no dia da reserva às 17h00 (hora de Lisboa).', 'EventsHouse'); ?></p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_postvisit_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_postvisit_subject" id="eventshouse_email_ilha_reservation_postvisit_subject" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_postvisit_subject']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_postvisit_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_email_ilha_reservation_postvisit_title" id="eventshouse_email_ilha_reservation_postvisit_title" value="<?php echo esc_attr($email_values['eventshouse_email_ilha_reservation_postvisit_title']); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_ilha_reservation_postvisit_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_email_ilha_reservation_postvisit_body" id="eventshouse_email_ilha_reservation_postvisit_body" class="large-text code" rows="12"><?php echo esc_textarea($email_values['eventshouse_email_ilha_reservation_postvisit_body']); ?></textarea>
                            <p class="description"><?php esc_html_e('Variáveis: %name%, %date_long%, %reserve_url%, %site_name%.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="ilha_reservation_postvisit"><?php esc_html_e('Enviar email teste — Após visita', 'EventsHouse'); ?></button></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_email_gcal_reservation_webhook"><?php esc_html_e('Webhook Google Calendar (opcional)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="url" class="large-text" name="eventshouse_email_gcal_reservation_webhook" id="eventshouse_email_gcal_reservation_webhook" value="<?php echo esc_attr($email_values['eventshouse_email_gcal_reservation_webhook']); ?>" placeholder="https://script.google.com/macros/s/.../exec" />
                            <p class="description"><?php esc_html_e('Opcional. Se preencher, o sistema envia um POST com os dados da reserva para automatizar criação/notificação no Google Calendar (via Apps Script/Make/Zapier).', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr><th colspan="2"><h3 style="margin:8px 0 0;"><?php esc_html_e('Festas na Ilha — Email de pagamento', 'EventsHouse'); ?></h3></th></tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_festa_order_email_subject"><?php esc_html_e('Assunto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_festa_order_email_subject" id="eventshouse_festa_order_email_subject" value="<?php echo esc_attr($festa_email_subject_cur); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_festa_order_email_title"><?php esc_html_e('Título do cabeçalho', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="eventshouse_festa_order_email_title" id="eventshouse_festa_order_email_title" value="<?php echo esc_attr($festa_email_title_cur); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="eventshouse_festa_order_email_body"><?php esc_html_e('Corpo do email', 'EventsHouse'); ?></label></th>
                        <td>
                            <textarea name="eventshouse_festa_order_email_body" id="eventshouse_festa_order_email_body" class="large-text code" rows="10"><?php echo esc_textarea($festa_email_body_cur); ?></textarea>
                            <p class="description"><?php esc_html_e('Placeholders aceitos no modelo de Festas: {nome_cliente}, {email_cliente}, {numero_encomenda}, {link_pagamento}, {total_encomenda}, {fase_pagamento}, {data_evento}, {data_limite_pagamento}, {aviso_caucao}, {bloco_dados_reserva}, {link_dados_reserva}.', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Teste deste email', 'EventsHouse'); ?></th>
                        <td><button type="button" class="button button-secondary eh-send-test-email" data-template="festa_order"><?php esc_html_e('Enviar email teste — Festas na Ilha', 'EventsHouse'); ?></button></td>
                    </tr>
                </table>
            </div>

            <?php EventsHouse_render_admin_tab_ilha_passes(); ?>

            <?php EventsHouse_render_admin_tab_verao_ilha(); ?>

            <div class="eh-tab-content" id="tab-coming-soon">
                <h2><?php esc_html_e('⏳ Coming Soon — Manhãs na Ilha', 'EventsHouse'); ?></h2>
                <p class="description"><?php esc_html_e('Configure textos e imagens da página/template "Coming Soon - Manhas na Ilha".', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ilha_coming_soon_badge"><?php esc_html_e('Badge', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_coming_soon_badge" id="ilha_coming_soon_badge" value="<?php echo esc_attr($coming_soon_badge); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ilha_coming_soon_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_coming_soon_title" id="ilha_coming_soon_title" value="<?php echo esc_attr($coming_soon_title); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ilha_coming_soon_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><textarea name="ilha_coming_soon_subtitle" id="ilha_coming_soon_subtitle" class="large-text" rows="3"><?php echo esc_textarea($coming_soon_subtitle); ?></textarea></td>
                    </tr>
                    <?php for ($ci = 1; $ci <= 3; $ci++) : ?>
                    <tr>
                        <th scope="row"><label for="ilha_coming_soon_slide<?php echo (int) $ci; ?>_image"><?php echo esc_html(sprintf(__('Imagem %d', 'EventsHouse'), $ci)); ?></label></th>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="url" class="large-text" name="ilha_coming_soon_slide<?php echo (int) $ci; ?>_image" id="ilha_coming_soon_slide<?php echo (int) $ci; ?>_image" value="<?php echo esc_url($coming_soon_slide_img[$ci]); ?>" placeholder="https://..." />
                                <button type="button" class="button eh-media-btn" data-target="ilha_coming_soon_slide<?php echo (int) $ci; ?>_image"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            </div>
                            <?php if (!empty($coming_soon_slide_img[$ci])) : ?>
                            <p style="margin-top:8px;">
                                <img src="<?php echo esc_url($coming_soon_slide_img[$ci]); ?>" alt="" style="max-width:220px;height:auto;border:1px solid #ccc;border-radius:4px;" />
                            </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ilha_coming_soon_slide<?php echo (int) $ci; ?>_alt"><?php echo esc_html(sprintf(__('Imagem %d — texto alternativo', 'EventsHouse'), $ci)); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_coming_soon_slide<?php echo (int) $ci; ?>_alt" id="ilha_coming_soon_slide<?php echo (int) $ci; ?>_alt" value="<?php echo esc_attr($coming_soon_slide_alt[$ci]); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>

            <?php EventsHouse_render_admin_tab_footer(); ?>

            <p class="submit">
                <input type="submit" name="eventshouse_save" class="button button-primary button-hero" value="💾 Guardar Alterações" />
            </p>
        </form>
    </div>
    <?php
}

/**
 * Loja WooCommerce: redirecionar /shop/ para a página Manhãs na Ilha.
 */
function EventsHouse_redirect_shop_to_manhas_na_ilha()
{
    if (!function_exists('is_shop') || !function_exists('is_product')) {
        return;
    }
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (!is_shop() || is_search()) {
        return;
    }
    if (is_page('manhas-na-ilha')) {
        return;
    }
    $page = get_page_by_path('manhas-na-ilha');
    if ($page && function_exists('wc_get_page_id') && (int) wc_get_page_id('shop') === (int) $page->ID) {
        return;
    }
    $url  = $page ? get_permalink($page->ID) : home_url('/manhas-na-ilha/');
    
    // Evita redirecionar se o visitante já estiver na mesma página
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'manhas-na-ilha') !== false) {
        return;
    }

    wp_safe_redirect($url, 302);
    exit;
}
add_action('template_redirect', 'EventsHouse_redirect_shop_to_manhas_na_ilha', 5);

/**
 * Register Widget Areas
 */
function EventsHouse_widgets_init()
{
    register_sidebar(array(
        'name'          => __('Footer Widget Area', 'EventsHouse'),
        'id'            => 'footer-widgets',
        'description'   => __('Widgets do rodapé', 'EventsHouse'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'EventsHouse_widgets_init');

/**
 * Defaults de textos de emails (admin).
 *
 * @return array<string,string>
 */
function EventsHouse_email_defaults_map()
{
    $map = array(
        'eventshouse_email_brand_color'            => '#1f6fb2',
        'eventshouse_email_footer_note'            => 'Este e-mail foi enviado automaticamente pelo site {site_name}.',
        'eventshouse_email_contact_subject'        => '[A ilha] Nova mensagem de {name}',
        'eventshouse_email_contact_title'          => 'Novo contacto do site',
        'eventshouse_email_contact_body'           => "<p>Recebemos um novo contacto pelo site.</p>\n<p><strong>Nome:</strong> %name%<br><strong>Email:</strong> %email%<br><strong>Telefone:</strong> %phone%<br><strong>Data do evento:</strong> %date%</p>\n<p><strong>Mensagem:</strong><br>%message%</p>",
        'eventshouse_email_contact_success_msg'    => 'Mensagem enviada com sucesso! Entraremos em contacto brevemente.',
        'eventshouse_email_contact_error_msg'      => 'Erro ao enviar mensagem. Tente novamente.',
        'eventshouse_email_avail_subject_festas'   => '[A ilha] Pedido de Disponibilidade — {name} em {date}',
        'eventshouse_email_avail_subject_ilha'     => '[A ilha] Manhãs na Ilha — Pedido de disponibilidade — {name} em {date}',
        'eventshouse_email_avail_title_festas'     => 'Pedido de disponibilidade - Festas',
        'eventshouse_email_avail_title_ilha'       => 'Manhãs na Ilha - Pedido de disponibilidade',
        'eventshouse_email_avail_body_festas'      => "<p>Recebemos um novo pedido de disponibilidade.</p>\n<p><strong>Origem:</strong> %source%<br><strong>Data:</strong> %date%<br><strong>Horário:</strong> %time_slot%<br><strong>Pacote:</strong> %package%<br><strong>N. convidados:</strong> %guests%<br><strong>Extras:</strong> %extras%</p>\n<p><strong>Nome:</strong> %name%<br><strong>Email:</strong> %email%<br><strong>Telefone:</strong> %phone%</p>\n<p><strong>Mensagem:</strong><br>%message%</p>",
        'eventshouse_email_avail_body_ilha'        => "<p>Recebemos um novo pedido para Manhãs na Ilha.</p>\n<p><strong>Data:</strong> %date%<br><strong>Horário:</strong> %time_slot%<br><strong>Evento:</strong> %package%</p>\n<p><strong>Nome:</strong> %name%<br><strong>Email:</strong> %email%<br><strong>Telefone:</strong> %phone%</p>\n<p><strong>Mensagem:</strong><br>%message%</p>",
        'eventshouse_email_avail_success_msg'      => 'Pedido enviado com sucesso! Entraremos em contacto consigo brevemente.',
        'eventshouse_email_avail_error_msg'        => 'Erro ao enviar. Tente novamente ou contacte-nos por telefone.',
    );

    if (function_exists('ilha_reservation_email_options_map')) {
        $map = array_merge($map, ilha_reservation_email_options_map());
    }

    if (function_exists('ilha_pack_purchase_email_options_map')) {
        $map = array_merge($map, ilha_pack_purchase_email_options_map());
    }

    return $map;
}

/**
 * Lê valor de opção de email com fallback para default.
 */
function EventsHouse_email_opt($key)
{
    $defaults = EventsHouse_email_defaults_map();
    $default = isset($defaults[$key]) ? $defaults[$key] : '';
    return (string) get_option($key, $default);
}

/**
 * Aplica placeholders básicos nos textos de email.
 *
 * @param string $text
 * @param array<string,string> $vars
 * @return string
 */
function EventsHouse_email_replace_vars($text, array $vars)
{
    $repl = array();
    foreach ($vars as $k => $v) {
        $repl['{' . $k . '}'] = (string) $v;
        $repl['%' . $k . '%'] = (string) $v;
    }
    return strtr((string) $text, $repl);
}

function EventsHouse_email_allowed_html_tags()
{
    return array(
        'p'      => array('style' => array(), 'class' => array()),
        'br'     => array(),
        'strong' => array(),
        'em'     => array(),
        'a'      => array('href' => array(), 'title' => array(), 'target' => array(), 'rel' => array()),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'span'   => array('style' => array(), 'class' => array()),
        'div'    => array('style' => array(), 'class' => array()),
    );
}

function EventsHouse_email_render_template_body($template, array $vars)
{
    $text = EventsHouse_email_replace_vars((string) $template, $vars);
    return wpautop(wp_kses($text, EventsHouse_email_allowed_html_tags()));
}

/**
 * Wrapper HTML comum para emails do site.
 */
function EventsHouse_build_email_template_html($title, $content_html)
{
    $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $brand = trim(EventsHouse_email_opt('eventshouse_email_brand_color'));
    if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $brand)) {
        $brand = '#1f6fb2';
    }

    $logo_url = '';
    $custom_logo_id = absint(get_theme_mod('custom_logo'));
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
    }
    if (!$logo_url) {
        $logo_url = EventsHouse_URI . '/assets/images/logo.png';
    }

    $footer_tpl = EventsHouse_email_opt('eventshouse_email_footer_note');
    $footer_txt = EventsHouse_email_replace_vars($footer_tpl, array('site_name' => $site_name));

    $html  = '<!doctype html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:22px;background:#eef3f8;font-family:Arial,sans-serif;color:#1f2937;">';
    $html .= '<div style="max-width:720px;margin:0 auto;background:#ffffff;border:1px solid #d9e2ec;border-radius:16px;overflow:hidden;box-shadow:0 10px 28px rgba(15,23,42,0.08);">';
    $html .= '<div style="background:linear-gradient(135deg,' . esc_attr($brand) . ' 0%,#0f4c81 100%);padding:24px 26px;color:#ffffff;">';
    $html .= '<table role="presentation" style="width:100%;border-collapse:collapse;"><tr>';
    $html .= '<td style="vertical-align:middle;"><img src="' . esc_url($logo_url) . '" alt="' . esc_attr($site_name) . '" style="max-height:58px;width:auto;display:block;"></td>';
    $html .= '<td style="text-align:right;vertical-align:middle;"><div style="font-size:12px;letter-spacing:.06em;text-transform:uppercase;opacity:.88;">Notificacao</div><div style="font-size:22px;font-weight:700;line-height:1.2;margin-top:2px;">' . esc_html($title) . '</div></td>';
    $html .= '</tr></table></div>';
    $html .= '<div style="padding:24px 26px;background:#ffffff;line-height:1.6;font-size:15px;color:#273444;">' . $content_html . '</div>';
    $html .= '<div style="padding:16px 26px;background:#f8fafc;border-top:1px solid #e5e7eb;color:#64748b;font-size:12px;">' . esc_html($footer_txt) . '</div>';
    $html .= '</div></body></html>';
    return $html;
}

/**
 * Custom Contact Form Handler (AJAX)
 */
function EventsHouse_handle_contact_form()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $phone   = sanitize_text_field($_POST['phone'] ?? '');
    $date    = sanitize_text_field($_POST['event_date'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(array('message' => 'Por favor preencha todos os campos obrigatórios.'));
    }

    $to = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
    if (!is_email($to)) {
        $to = EventsHouse_DEFAULT_CONTACT_EMAIL;
    }
    $subject_tpl = EventsHouse_email_opt('eventshouse_email_contact_subject');
    $subject = EventsHouse_email_replace_vars($subject_tpl, array(
        'name' => $name,
        'email' => $email,
        'date' => $date,
    ));

    $title = EventsHouse_email_opt('eventshouse_email_contact_title');
    $body_tpl = EventsHouse_email_opt('eventshouse_email_contact_body');
    $body_content = EventsHouse_email_render_template_body($body_tpl, array(
        'name'      => $name,
        'email'     => antispambot($email),
        'phone'     => $phone ?: 'Não fornecido',
        'date'      => $date ?: 'Não especificada',
        'message'   => nl2br(esc_html($message)),
        'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    ));
    $body = EventsHouse_build_email_template_html($title, $body_content);
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    );

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(array('message' => EventsHouse_email_opt('eventshouse_email_contact_success_msg')));
    } else {
        wp_send_json_error(array('message' => EventsHouse_email_opt('eventshouse_email_contact_error_msg')));
    }
}
add_action('wp_ajax_EventsHouse_contact', 'EventsHouse_handle_contact_form');
add_action('wp_ajax_nopriv_EventsHouse_contact', 'EventsHouse_handle_contact_form');

/**
 * Rótulos legíveis para "Como chegou até nós?" (formulário de disponibilidade).
 *
 * @return array<string,string>
 */
function EventsHouse_availability_how_found_labels()
{
    return array(
        'site'          => 'Site',
        'redes-sociais' => 'Redes Sociais',
        'motores-busca' => 'Motores de busca (google, etc)',
        'referencia'    => 'Referência de família ou amigos',
        'olx'           => 'OLX',
    );
}

/**
 * Rótulos legíveis para "Qual é a finalidade do evento?" (formulário de disponibilidade).
 *
 * @return array<string,string>
 */
function EventsHouse_availability_event_purpose_labels()
{
    return array(
        'aniversario-0-3'    => 'Festa de Aniversário (0 - 3 anos)',
        'aniversario-4-10'   => 'Festa de Aniversário (4 - 10 anos)',
        'baby-shower'        => 'Baby Shower / Chá do Bebé',
        'revelacao-genero'   => 'Revelação de Género / Chá de Revelação',
        'batizado'           => 'Batizado',
        'aniversario-adulto' => 'Festa de Aniversário Adulto',
    );
}

/**
 * Processa e valida os ficheiros enviados no campo "Cópia do CC".
 * Guarda os ficheiros temporariamente numa pasta protegida (sem acesso direto),
 * para serem anexados ao email e apagados de seguida.
 *
 * @return array{files: string[], error: string}
 */
function EventsHouse_handle_availability_cc_uploads()
{
    $max_files = 5;
    $max_size  = 10 * 1024 * 1024; // 10 MB

    if (empty($_FILES['cc_files']) || empty($_FILES['cc_files']['name'][0])) {
        return array('files' => array(), 'error' => 'missing');
    }

    $names = (array) $_FILES['cc_files']['name'];
    $count = count(array_filter($names, function ($n) {
        return $n !== '';
    }));
    if ($count < 1) {
        return array('files' => array(), 'error' => 'missing');
    }
    if ($count > $max_files) {
        return array('files' => array(), 'error' => 'too_many');
    }

    foreach ((array) $_FILES['cc_files']['size'] as $size) {
        if ((int) $size > $max_size) {
            return array('files' => array(), 'error' => 'too_large');
        }
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';

    $upload_dir_filter = function ($dirs) {
        $dirs['subdir'] = '/eventshouse-cc-tmp' . $dirs['subdir'];
        $dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
        $dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
        return $dirs;
    };
    add_filter('upload_dir', $upload_dir_filter);

    $allowed_mimes = array(
        'jpg|jpeg' => 'image/jpeg',
        'png'      => 'image/png',
        'webp'     => 'image/webp',
        'pdf'      => 'application/pdf',
    );

    $saved = array();
    for ($i = 0; $i < $count; $i++) {
        if (empty($_FILES['cc_files']['name'][$i])) {
            continue;
        }
        if (!empty($_FILES['cc_files']['error'][$i]) && $_FILES['cc_files']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $file = array(
            'name'     => $_FILES['cc_files']['name'][$i],
            'type'     => $_FILES['cc_files']['type'][$i],
            'tmp_name' => $_FILES['cc_files']['tmp_name'][$i],
            'error'    => $_FILES['cc_files']['error'][$i],
            'size'     => $_FILES['cc_files']['size'][$i],
        );
        $overrides = array(
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        );
        $result = wp_handle_upload($file, $overrides);
        if (!empty($result['file'])) {
            $saved[] = $result['file'];
        }
    }

    remove_filter('upload_dir', $upload_dir_filter);

    if (empty($saved)) {
        return array('files' => array(), 'error' => 'invalid');
    }

    // Protege a pasta temporária de acesso direto via navegador.
    $upload_base  = wp_upload_dir();
    $protect_dir  = trailingslashit($upload_base['basedir']) . 'eventshouse-cc-tmp';
    if (is_dir($protect_dir)) {
        if (!file_exists($protect_dir . '/.htaccess')) {
            @file_put_contents($protect_dir . '/.htaccess', "Deny from all\n");
        }
        if (!file_exists($protect_dir . '/index.php')) {
            @file_put_contents($protect_dir . '/index.php', "<?php // Silence is golden.\n");
        }
    }

    return array('files' => $saved, 'error' => '');
}

/**
 * ═══════════════════════════════════════════════════
 * AJAX: FORMULÁRIO DE DISPONIBILIDADE
 * ═══════════════════════════════════════════════════
 */
function EventsHouse_handle_availability_form()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');

    $name      = sanitize_text_field($_POST['name'] ?? '');
    $email     = sanitize_email($_POST['email'] ?? '');
    $phone     = sanitize_text_field($_POST['phone'] ?? '');
    $date      = sanitize_text_field($_POST['event_date'] ?? '');
    $time_slot = sanitize_text_field($_POST['time_slot'] ?? '');
    $package   = sanitize_text_field($_POST['package'] ?? '');
    $guests    = sanitize_text_field($_POST['guests'] ?? '');
    $message   = sanitize_textarea_field($_POST['message'] ?? '');
    $extras_in = isset($_POST['extras']) ? (array) wp_unslash($_POST['extras']) : array();
    $extras_in = array_values(array_filter(array_map('sanitize_text_field', $extras_in)));

    $cal_context = sanitize_text_field($_POST['cal_context'] ?? '');
    $is_ilha     = ($cal_context === 'ilha');

    if (empty($name) || empty($email) || empty($date)) {
        wp_send_json_error(array('message' => 'Por favor preencha o nome, email e data.'));
    }

    if (!$is_ilha) {
        if (trim($phone) === '') {
            wp_send_json_error(array('message' => 'Por favor indique o número de telefone.'));
        }
        if (trim($time_slot) === '') {
            wp_send_json_error(array('message' => 'Por favor escolha um horário.'));
        }
        $guests_num = absint($guests);
        if ($guests_num < 1) {
            wp_send_json_error(array('message' => 'Por favor indique o número de convidados.'));
        }
        $guests = (string) $guests_num;
    }

    $to = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
    if (!is_email($to)) {
        $to = EventsHouse_DEFAULT_CONTACT_EMAIL;
    }
    if ($is_ilha) {
        $subject_tpl = EventsHouse_email_opt('eventshouse_email_avail_subject_ilha');
        $email_title = EventsHouse_email_opt('eventshouse_email_avail_title_ilha');
    } else {
        $subject_tpl = EventsHouse_email_opt('eventshouse_email_avail_subject_festas');
        $email_title = EventsHouse_email_opt('eventshouse_email_avail_title_festas');
    }
    $subject = EventsHouse_email_replace_vars($subject_tpl, array(
        'name' => $name,
        'email' => $email,
        'date' => $date,
    ));

    $extras_catalog = get_option('eventshouse_orcamento_extras', array());
    $extras_map = array();
    if (is_array($extras_catalog)) {
        foreach ($extras_catalog as $extra) {
            $extra_title = sanitize_text_field($extra['title'] ?? '');
            if ($extra_title === '') {
                continue;
            }
            $extra_slug = sanitize_title($extra_title);
            $extra_price = sanitize_text_field($extra['price'] ?? '');
            $extras_map[$extra_slug] = $extra_title . ($extra_price !== '' ? ' (+' . $extra_price . 'EUR)' : '');
        }
    }

    $extras_readable = array();
    foreach ($extras_in as $extra_slug) {
        if (isset($extras_map[$extra_slug])) {
            $extras_readable[] = $extras_map[$extra_slug];
            continue;
        }
        $extras_readable[] = ucwords(str_replace('-', ' ', $extra_slug));
    }
    $extras_display = !empty($extras_readable) ? implode(', ', $extras_readable) : 'Nenhum';

    $source_label = $is_ilha ? 'Manhãs na Ilha' : 'Festas / Disponibilidade';
    $message_html = $message !== '' ? nl2br(esc_html($message)) : 'Sem mensagem adicional.';

    $body_tpl = EventsHouse_email_opt($is_ilha ? 'eventshouse_email_avail_body_ilha' : 'eventshouse_email_avail_body_festas');
    $body_content = EventsHouse_email_render_template_body($body_tpl, array(
        'source'         => $source_label,
        'date'           => $date ?: 'Não especificado',
        'time_slot'      => $time_slot ?: 'Não especificado',
        'package'        => $package ?: 'Não especificado',
        'guests'         => $guests ?: 'Não especificado',
        'extras'         => $extras_display,
        'name'           => $name,
        'email'          => antispambot($email),
        'phone'          => $phone ?: 'Não fornecido',
        'message'        => $message_html,
        'site_name'      => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    ));
    $body = EventsHouse_build_email_template_html($email_title, $body_content);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        sprintf('Reply-To: %s <%s>', $name, $email),
    );

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(array('message' => EventsHouse_email_opt('eventshouse_email_avail_success_msg')));
    } else {
        wp_send_json_error(array('message' => EventsHouse_email_opt('eventshouse_email_avail_error_msg')));
    }
}
add_action('wp_ajax_EventsHouse_availability', 'EventsHouse_handle_availability_form');
add_action('wp_ajax_nopriv_EventsHouse_availability', 'EventsHouse_handle_availability_form');

function EventsHouse_notify_gcal_reservation_webhook(array $payload)
{
    $webhook = trim((string) EventsHouse_email_opt('eventshouse_email_gcal_reservation_webhook'));
    if ($webhook === '' || !wp_http_validate_url($webhook)) {
        return;
    }

    $args = array(
        'timeout' => 8,
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body'    => wp_json_encode($payload),
    );
    $response = wp_remote_post($webhook, $args);
    if (is_wp_error($response)) {
        error_log('EventsHouse webhook Google Calendar falhou: ' . $response->get_error_message());
    }
}

function EventsHouse_handle_ilha_reservation_form()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');

    $name      = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email     = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone     = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $child_age = isset($_POST['child_age']) ? absint(wp_unslash($_POST['child_age'])) : 0;

    $log_base = array(
        'email'     => $email,
        'name'      => $name,
        'phone'     => $phone,
        'child_age' => $child_age,
    );

    if ($email === '' || !is_email($email)) {
        EventsHouse_log_error('ilha_reservation', 'Email inválido no pedido de reserva.', $log_base, 'info');
        wp_send_json_error(array('message' => __('Indique um email válido.', 'EventsHouse')));
    }

    $cliente = ilha_get_cliente_by_email($email);
    $passe   = $cliente ? ilha_get_reservable_pass_for_cliente((int) $cliente->id) : null;
    if (!$cliente || !$passe) {
        EventsHouse_log_error('ilha_reservation', 'Reserva recusada: cliente sem pack ativo ou sem manhãs disponíveis.', array_merge($log_base, array(
            'cliente_id' => $cliente ? (int) $cliente->id : 0,
            'has_pass'   => (bool) $passe,
        )), 'info');
        wp_send_json_error(array(
            'message'   => __('Reservas permitidas apenas para utilizadores com pacote ativo.', 'EventsHouse'),
            'no_pass'   => true,
            'packs_url' => EventsHouse_ilha_passes_page_url('#ilha-packs'),
        ));
    }

    if ($name === '' && !empty($cliente->nome)) {
        $name = $cliente->nome;
    }
    $date        = sanitize_text_field(wp_unslash($_POST['event_date'] ?? ''));
    $event_title = sanitize_text_field(wp_unslash($_POST['event_title'] ?? ''));
    $event_time  = sanitize_text_field(wp_unslash($_POST['event_time'] ?? ''));
    $event_link  = esc_url_raw(wp_unslash($_POST['event_link'] ?? ''));

    $log_base['event_date']  = $date;
    $log_base['event_title'] = $event_title;
    $log_base['event_time']  = $event_time;
    $log_base['cliente_id']  = (int) $cliente->id;
    $log_base['passe_id']    = (int) $passe->id;

    if ($name === '' || $email === '' || $date === '' || $child_age < 1 || $child_age > 18) {
        EventsHouse_log_error('ilha_reservation', 'Campos obrigatórios em falta.', $log_base, 'info');
        wp_send_json_error(array('message' => __('Preencha nome, email, idade da criança e data para reservar.', 'EventsHouse')));
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 0) {
        EventsHouse_log_error('ilha_reservation', 'Data inválida.', $log_base, 'info');
        wp_send_json_error(array('message' => __('Data inválida.', 'EventsHouse')));
    }

    if (ilha_cliente_has_reserva_on_date((int) $cliente->id, $date)) {
        EventsHouse_log_error('ilha_reservation', 'Cliente já tem reserva neste dia.', $log_base, 'info');
        wp_send_json_error(array('message' => __('Já tem uma reserva para este dia.', 'EventsHouse')));
    }

    if (!ilha_has_capacity_for_date($date)) {
        EventsHouse_log_error('ilha_reservation', 'Dia sem vagas (capacidade esgotada).', array_merge($log_base, array(
            'capacidade' => ilha_get_reservas_capacidade(),
            'ocupadas'   => ilha_count_reservas_for_date($date),
        )), 'info');
        wp_send_json_error(array(
            'message'     => __('Não há vagas disponíveis neste dia.', 'EventsHouse'),
            'no_capacity' => true,
        ));
    }

    $reserva_id = ilha_insert_reserva(array(
        'cliente_id'   => (int) $cliente->id,
        'passe_id'     => (int) $passe->id,
        'data_reserva' => $date,
        'event_title'  => $event_title,
        'event_time'   => $event_time,
        'nome'          => $name,
        'email'         => $email,
        'telefone'      => $phone,
        'idade_crianca' => $child_age,
    ));

    if (!$reserva_id) {
        global $wpdb;
        EventsHouse_log_error('ilha_reservation', 'Falha ao gravar reserva na base de dados.', array_merge($log_base, array(
            'db_error' => $wpdb->last_error,
        )));
        wp_send_json_error(array(
            'message' => __('Não foi possível registar a reserva. Tente novamente ou contacte-nos.', 'EventsHouse'),
        ));
    }

    $log_base['reserva_id'] = $reserva_id;

    $client_sent = ilha_send_reservation_confirmation_emails($reserva_id);

    if (!$client_sent) {
        EventsHouse_log_error(
            'ilha_reservation',
            'Reserva criada mas o email de confirmação ao cliente falhou.',
            array_merge($log_base, array(
                'mail_error' => EventsHouse_last_mail_error_message(),
            )),
            'warning'
        );
    }

    EventsHouse_notify_gcal_reservation_webhook(array(
        'source'      => 'manha_na_ilha',
        'reserva_id'  => $reserva_id,
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
        'child_age'   => $child_age,
        'date'        => $date,
        'event_title' => $event_title,
        'event_time'  => $event_time,
        'event_link'  => $event_link,
        'created_at'  => current_time('mysql'),
    ));

    wp_send_json_success(array('message' => EventsHouse_email_opt('eventshouse_email_ilha_reservation_success_msg')));
}
add_action('wp_ajax_EventsHouse_ilha_reservation', 'EventsHouse_handle_ilha_reservation_form');
add_action('wp_ajax_nopriv_EventsHouse_ilha_reservation', 'EventsHouse_handle_ilha_reservation_form');

function EventsHouse_send_test_email_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Sem permissão.', 'EventsHouse')), 403);
    }
    check_ajax_referer('eventshouse_send_test_email', 'nonce');

    $to = sanitize_email(wp_unslash($_POST['to'] ?? ''));
    if (!is_email($to)) {
        wp_send_json_error(array('message' => __('Indique um email válido para teste.', 'EventsHouse')));
    }

    $template = sanitize_key(wp_unslash($_POST['template'] ?? ''));
    $brand_color = sanitize_hex_color(wp_unslash($_POST['brand_color'] ?? ''));
    if (!$brand_color) {
        $brand_color = EventsHouse_email_opt('eventshouse_email_brand_color');
    }
    $footer_note = sanitize_textarea_field(wp_unslash($_POST['footer_note'] ?? EventsHouse_email_opt('eventshouse_email_footer_note')));
    $prev_brand = get_option('eventshouse_email_brand_color', '');
    $prev_footer = get_option('eventshouse_email_footer_note', '');
    update_option('eventshouse_email_brand_color', $brand_color);
    update_option('eventshouse_email_footer_note', $footer_note);

    $vars = array(
        'name' => 'Cliente Teste',
        'email' => $to,
        'phone' => '+351 900 000 000',
        'child_age' => '5',
        'date' => wp_date('Y-m-d'),
        'date_human' => wp_date('d/m/Y'),
        'date_long' => wp_date('l, j \d\e F \d\e Y'),
        'horario' => '09h00 – 13h00',
        'time_slot' => '09:00 - 13:00',
        'package' => 'Manhãs na Ilha',
        'source' => 'Painel EventsThemes',
        'guests' => '25',
        'extras' => 'Hora extra (+50EUR)',
        'message' => 'Este é um email de teste enviado pelo painel.',
        'event_title' => 'Manhãs na Ilha',
        'event_time' => '09:00 - 13:00',
        'pack_label' => 'Pack 6 Manhãs',
        'order_id' => '1234',
        'event_link' => home_url('/manhas-na-ilha/'),
        'reserve_url' => function_exists('EventsHouse_ilha_passes_page_url') ? EventsHouse_ilha_passes_page_url('#ilha-disponibilidade') : home_url('/manhas-na-ilha/#ilha-disponibilidade'),
        'site_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    );

    $subject = '';
    $title = '';
    $body_html = '';

    if ($template === 'contact') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'avail_festas' || $template === 'avail_ilha') {
        $vars['source'] = ($template === 'avail_ilha') ? 'Manhãs na Ilha' : 'Festas / Disponibilidade';
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'ilha_reservation') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'ilha_reservation_admin') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'ilha_reservation_reminder') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'ilha_reservation_postvisit') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'ilha_pack_purchase') {
        $subject = sanitize_text_field(EventsHouse_email_replace_vars(wp_unslash($_POST['subject'] ?? ''), $vars));
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_html = EventsHouse_email_render_template_body(wp_unslash($_POST['body'] ?? ''), $vars);
    } elseif ($template === 'festa_order') {
        $subject_tpl = (string) wp_unslash($_POST['subject'] ?? '');
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $body_tpl = (string) wp_unslash($_POST['body'] ?? '');
        $festa_map = array(
            '{nome_cliente}' => 'Cliente Teste',
            '{email_cliente}' => $to,
            '{numero_encomenda}' => '1001',
            '{link_pagamento}' => esc_url(home_url('/checkout/order-pay/1001/')),
            '{total_encomenda}' => '150,00 EUR',
            '{nome_loja}' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
            '{site_url}' => home_url('/'),
            '{fase_pagamento}' => '1ª Parte',
            '{data_evento}' => wp_date('d/m/Y H:i'),
            '{data_limite_pagamento}' => wp_date('d/m/Y H:i', time() + DAY_IN_SECONDS),
            '{aviso_caucao}' => '',
            '{link_dados_reserva}' => esc_url(home_url('/checkout/order-pay/1001/')),
            '{bloco_dados_reserva}' => 'Na página Confirmar reserva poderá preencher os dados pedidos (cópia do CC, como chegou até nós, finalidade do evento e NIF na fatura).',
        );
        $subject = sanitize_text_field(strtr($subject_tpl, $festa_map));
        $body_html = wpautop(wp_kses(strtr($body_tpl, $festa_map), EventsHouse_email_allowed_html_tags()));
    } else {
        update_option('eventshouse_email_brand_color', $prev_brand);
        update_option('eventshouse_email_footer_note', $prev_footer);
        wp_send_json_error(array('message' => __('Template de teste inválido.', 'EventsHouse')));
    }

    if ($subject === '') {
        $subject = __('[Teste] Preview de email', 'EventsHouse');
    }
    if ($title === '') {
        $title = __('Email de teste', 'EventsHouse');
    }

    $wrapped = EventsHouse_build_email_template_html($title, $body_html);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $sent = wp_mail($to, '[TESTE] ' . $subject, $wrapped, $headers);

    update_option('eventshouse_email_brand_color', $prev_brand);
    update_option('eventshouse_email_footer_note', $prev_footer);

    if ($sent) {
        wp_send_json_success(array('message' => sprintf(__('Email teste enviado para %s', 'EventsHouse'), $to)));
    }
    wp_send_json_error(array('message' => __('Falha no envio. Verifique SMTP/configuração de email do WordPress.', 'EventsHouse')));
}
add_action('wp_ajax_eventshouse_send_test_email', 'EventsHouse_send_test_email_ajax');

/**
 * ═══════════════════════════════════════════════════
 * AJAX: BUSCAR EVENTOS DO GOOGLE CALENDAR
 * ═══════════════════════════════════════════════════
 */
function EventsHouse_get_calendar_events()
{
    $api_key = get_option('eventshouse_gcal_api_key', '');
    $context = isset($_POST['cal_context']) ? sanitize_text_field(wp_unslash($_POST['cal_context'])) : '';

    if ($context === 'ilha') {
        $calendar_id = get_option('eventshouse_gcal_ilha_calendar_id', '');
        $cache_key   = 'eventshouse_gcal_avail_ilha';
    } else {
        $calendar_id = get_option('eventshouse_gcal_calendar_id', '');
        $cache_key   = 'eventshouse_gcal_avail_main';
    }

    if (empty($api_key) || empty($calendar_id)) {
        wp_send_json_success(array(
            'events'        => array(),
            'blockedSlots'  => new stdClass(),
            'configured'    => false,
        ));
    }

    $cached = ($context === 'ilha') ? false : get_transient($cache_key);
    if ($cached !== false && is_array($cached)) {
        if ($context === 'ilha' && isset($cached['events']) && isset($cached['eventItemsByDate'])) {
            wp_send_json_success(array(
                'events'           => $cached['events'],
                'eventItemsByDate' => $cached['eventItemsByDate'],
                'configured'       => true,
                'cached'           => true,
            ));
        }
        if ($context !== 'ilha' && isset($cached['events']) && isset($cached['blockedSlots'])) {
            wp_send_json_success(array(
                'events'        => $cached['events'],
                'blockedSlots'  => $cached['blockedSlots'],
                'configured'    => true,
                'cached'        => true,
            ));
        }
    }

    $months = max(1, min(12, intval(get_option('eventshouse_gcal_months', 6))));

    $slots_raw = get_option('eventshouse_gcal_slots', "10:00-12:30\n13:30-16:00\n17:00-19:30");
    if ($context === 'ilha') {
        $ilha_slots = get_option('eventshouse_gcal_ilha_slots', '');
        if ($ilha_slots !== '' && $ilha_slots !== null) {
            $slots_raw = $ilha_slots;
        }
    }

    $time_min = urlencode(gmdate('Y-m-d\TH:i:s\Z'));
    $time_max = urlencode(gmdate('Y-m-d\TH:i:s\Z', strtotime("+{$months} months")));
    $cal_id   = urlencode($calendar_id);

    $url = "https://www.googleapis.com/calendar/v3/calendars/{$cal_id}/events";
    $url .= "?key={$api_key}&timeMin={$time_min}&timeMax={$time_max}";
    $url .= "&singleEvents=true&orderBy=startTime&maxResults=250";

    $response = wp_remote_get($url, array('timeout' => 15));

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Erro ao contactar Google Calendar: ' . $response->get_error_message()));
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);

    if ($code !== 200) {
        $err = $body['error']['message'] ?? 'Erro desconhecido';
        wp_send_json_error(array('message' => "Google Calendar: $err"));
    }

    $items  = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();
    if ($context === 'ilha') {
        $payload = EventsHouse_gcal_build_ilha_events_payload($items, $months);
    } else {
        $avail  = EventsHouse_gcal_compute_slot_availability($items, $slots_raw, $months);
        $payload = array(
            'events'       => $avail['fully_occupied'],
            'blockedSlots' => $avail['blocked_slots'],
        );
    }

    if ($context === 'ilha') {
        // Ilha precisa refletir alterações de eventos quase em tempo real.
        set_transient($cache_key, $payload, 60);
    } else {
        set_transient($cache_key, $payload, 30 * MINUTE_IN_SECONDS);
    }

    wp_send_json_success(array_merge($payload, array('configured' => true)));
}
add_action('wp_ajax_EventsHouse_get_calendar', 'EventsHouse_get_calendar_events');
add_action('wp_ajax_nopriv_EventsHouse_get_calendar', 'EventsHouse_get_calendar_events');

/**
 * Monta payload para «Manhãs na Ilha»: lista de eventos por dia + datas com evento.
 *
 * @param array $items Itens da API Google Calendar.
 * @param int   $months Janela de meses para calendário.
 * @return array{events:array<int,string>,eventItemsByDate:array<string,array<int,array<string,string>>>}
 */
function EventsHouse_gcal_build_ilha_events_payload(array $items, $months)
{
    $tz = wp_timezone();
    $today = (new DateTimeImmutable('now', $tz))->setTime(0, 0, 0);
    $until = $today->add(new DateInterval('P' . max(1, (int) $months) . 'M'))->setTime(23, 59, 59);

    $dates_with_events = array();
    $events_by_date = array();

    foreach ($items as $event) {
        // 1) TÍTULO
        $summary_raw = isset($event['summary']) ? (string) $event['summary'] : '';
        $summary = trim(preg_replace('/\s+/u', ' ', $summary_raw));

        // 2) DESCRIÇÃO
        $description_raw = isset($event['description']) ? (string) $event['description'] : '';
        // Preserva formatação (quebras de linha/parágrafos) vinda do Google Calendar.
        $desc_work = str_replace(array("\r\n", "\r"), "\n", $description_raw);
        // Mantém separação de parágrafos quando a descrição vier com HTML.
        $desc_work = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $desc_work);
        $desc_work = preg_replace('/<p[^>]*>/i', '', $desc_work);
        $desc_work = preg_replace('/<\/p>/i', "\n\n", $desc_work);
        $desc_work = preg_replace('/<br\s*\/?>/i', "\n", $desc_work);
        $desc_work = wp_strip_all_tags((string) $desc_work, false);
        // Limpeza de linhas/brancos em excesso sem perder estrutura.
        $desc_work = preg_replace("/[ \t]+\n/u", "\n", (string) $desc_work);
        $desc_work = preg_replace("/\n{3,}/u", "\n\n", (string) $desc_work);
        $desc_work = html_entity_decode((string) $desc_work, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = trim($desc_work);
        if ($description === '' && !empty($event['location'])) {
            $description = trim(wp_strip_all_tags((string) $event['location']));
        }

        // 3) LINK (preferir links explícitos de inscrição no conteúdo)
        $event_link = '';
        if (preg_match('#https?://[^\s<>"\']+#i', $description_raw, $m)) {
            $event_link = esc_url_raw($m[0]);
        } elseif (!empty($event['source']['url'])) {
            $event_link = esc_url_raw((string) $event['source']['url']);
        } elseif (!empty($event['attachments']) && is_array($event['attachments'])) {
            foreach ($event['attachments'] as $att) {
                if (!empty($att['fileUrl'])) {
                    $event_link = esc_url_raw((string) $att['fileUrl']);
                    break;
                }
            }
        }

        if (!empty($event['start']['dateTime'])) {
            try {
                $start = (new DateTimeImmutable($event['start']['dateTime']))->setTimezone($tz);
                $end = !empty($event['end']['dateTime'])
                    ? (new DateTimeImmutable($event['end']['dateTime']))->setTimezone($tz)
                    : $start;
            } catch (Exception $e) {
                continue;
            }
            if ($end < $today || $start > $until) {
                continue;
            }
            $date_key = $start->format('Y-m-d');
            $time_label = $start->format('H:i') . ' - ' . $end->format('H:i');

            $dates_with_events[$date_key] = true;
            if (!isset($events_by_date[$date_key])) {
                $events_by_date[$date_key] = array();
            }
            $events_by_date[$date_key][] = array(
                'title'       => $summary !== '' ? $summary : __('Manhãs na Ilha', 'EventsHouse'),
                'description' => $description,
                'link'        => $event_link,
                'time'        => $time_label,
                '_sort'       => $start->format('H:i:s'),
            );
            continue;
        }

        if (!empty($event['start']['date'])) {
            $startD = DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['start']['date'], $tz);
            if (!$startD instanceof DateTimeImmutable) {
                continue;
            }
            $startD = $startD->setTime(0, 0, 0);
            $endD = !empty($event['end']['date'])
                ? DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['end']['date'], $tz)
                : $startD->modify('+1 day');
            if (!$endD instanceof DateTimeImmutable) {
                $endD = $startD->modify('+1 day');
            }
            $endExclusive = $endD->setTime(0, 0, 0);
            for ($d = $startD; $d < $endExclusive; $d = $d->modify('+1 day')) {
                if ($d < $today || $d > $until) {
                    continue;
                }
                $date_key = $d->format('Y-m-d');
                $dates_with_events[$date_key] = true;
                if (!isset($events_by_date[$date_key])) {
                    $events_by_date[$date_key] = array();
                }
                $events_by_date[$date_key][] = array(
                    'title'       => $summary !== '' ? $summary : __('Manhãs na Ilha', 'EventsHouse'),
                    'description' => $description,
                    'link'        => $event_link,
                    'time'        => __('Dia inteiro', 'EventsHouse'),
                    '_sort'       => '00:00:00',
                );
            }
        }
    }

    foreach ($events_by_date as $date_key => $list) {
        usort($list, function ($a, $b) {
            return strcmp((string) ($a['_sort'] ?? ''), (string) ($b['_sort'] ?? ''));
        });
        foreach ($list as &$ev) {
            unset($ev['_sort']);
        }
        unset($ev);
        $events_by_date[$date_key] = $list;
    }

    $event_days = array_values(array_keys($dates_with_events));
    sort($event_days);

    return array(
        'events' => $event_days,
        'eventItemsByDate' => $events_by_date,
    );
}

/**
 * Enqueue calendar assets on contact page
 */
function EventsHouse_calendar_scripts()
{
    if (!is_page_template('page-disponibilidade.php') && !is_page_template('page-ilha-passes.php')) {
        return;
    }

    $gcal_api_key      = get_option('eventshouse_gcal_api_key', '');
    $gcal_calendar_id  = get_option('eventshouse_gcal_calendar_id', '');
    $gcal_ilha_id      = get_option('eventshouse_gcal_ilha_calendar_id', '');
    $gcal_slots        = get_option('eventshouse_gcal_slots', "10:00-12:30\n13:30-16:00\n17:00-19:30");
    $gcal_ilha_slots   = get_option('eventshouse_gcal_ilha_slots', '');
    if ($gcal_ilha_slots === '' || $gcal_ilha_slots === null) {
        $gcal_ilha_slots = $gcal_slots;
    }
    $slots_main = array_filter(array_map('trim', explode("\n", $gcal_slots)));
    $slots_ilha = array_filter(array_map('trim', explode("\n", (string) $gcal_ilha_slots)));

    wp_enqueue_style('eventshouse-calendar', EventsHouse_URI . '/assets/css/calendar.css', array(), EventsHouse_VERSION);
    wp_enqueue_script('eventshouse-calendar', EventsHouse_URI . '/assets/js/calendar.js', array(), EventsHouse_VERSION, true);
    wp_localize_script('eventshouse-calendar', 'EHCalendar', array(
        'ajaxUrl'         => admin_url('admin-ajax.php'),
        'nonce'           => wp_create_nonce('EventsHouse_nonce'),
        'mainConfigured'  => !empty($gcal_api_key) && !empty($gcal_calendar_id),
        'ilhaConfigured'  => !empty($gcal_api_key) && !empty($gcal_ilha_id),
        'packsUrl'        => EventsHouse_ilha_passes_page_url('#ilha-packs'),
        'slots'           => array_values($slots_main),
        'ilhaSlots'       => array_values($slots_ilha),
        'months'          => max(1, min(12, intval(get_option('eventshouse_gcal_months', 6)))),
        'packageOptions'  => array(
            ''          => 'Selecionar pacote...',
            'aluguer'   => 'Só Aluguer do Espaço',
            'chave-mao' => 'Pacote Chave na Mão',
            'decoracao' => 'Decoração & Bolo',
            'catering'  => 'Catering',
            'domicilio' => 'Vamos até Si',
        ),
    ));
}
add_action('wp_enqueue_scripts', 'EventsHouse_calendar_scripts');

/**
 * Custom page templates body class
 */
function EventsHouse_body_classes($classes)
{
    if (is_front_page())                            $classes[] = 'page-home';
    if (is_page_template('page-orcamento.php'))     $classes[] = 'page-orcamento';
    if (is_page_template('page-galeria.php'))       $classes[] = 'page-galeria';
    if (is_page_template('page-contato.php'))       $classes[] = 'page-contato';
    if (is_page_template('page-disponibilidade.php')) $classes[] = 'page-disponibilidade';
    if (is_page_template('page-ilha-passes.php'))     $classes[] = 'page-ilha-passes';
    if (is_page_template('page-verao-na-ilha.php'))    $classes[] = 'page-verao-na-ilha';
    if (is_page_template('page-politica-de-privacidade.php')) $classes[] = 'page-politica-privacidade';
    if (is_page_template('page-termos-e-condicoes.php'))      $classes[] = 'page-termos-e-condicoes';
    return $classes;
}
add_filter('body_class', 'EventsHouse_body_classes');

/**
 * IDs dos produtos WooCommerce «Extra Adulto» e «Extra Criança» (Personalizar / EventsThemes).
 * Omissão: 62 e 65.
 *
 * @return array{adult:int, child:int}
 */
function EventsHouse_ilha_extra_product_ids()
{
    $adult = absint(get_theme_mod('woocommerce_extra_adult_id'));
    $child = absint(get_theme_mod('woocommerce_extra_child_id'));
    if (!$adult) {
        $adult = 62;
    }
    if (!$child) {
        $child = 65;
    }

    return array(
        'adult' => $adult,
        'child' => $child,
    );
}

/**
 * Pack 1 / 6 / 12 configurados no tema.
 *
 * @return int[]
 */
function EventsHouse_ilha_pack_product_ids()
{
    return array_values(array_filter(array_map('absint', array(
        get_theme_mod('woocommerce_pack_1_id'),
        get_theme_mod('woocommerce_pack_6_id'),
        get_theme_mod('woocommerce_pack_12_id'),
    ))));
}

/**
 * Número de entradas/manhãs de cada pack (extras de adulto sugeridos = este valor).
 *
 * @param int $pack_id ID do produto WooCommerce do pack.
 */
function EventsHouse_ilha_entrances_for_pack_id($pack_id)
{
    $pack_id = absint($pack_id);
    if (!$pack_id) {
        return 1;
    }
    if ($pack_id === absint(get_theme_mod('woocommerce_pack_6_id'))) {
        return 6;
    }
    if ($pack_id === absint(get_theme_mod('woocommerce_pack_12_id'))) {
        return 12;
    }
    if ($pack_id === absint(get_theme_mod('woocommerce_pack_1_id'))) {
        return 1;
    }

    return 1;
}

/**
 * Adiciona um pack de manhãs + quantidades opcionais de extras ao carrinho.
 *
 * @return true|WP_Error
 */
function EventsHouse_ilha_process_pack_with_extras()
{
    if (!function_exists('WC') || !WC()->cart) {
        return new WP_Error('no_wc', __('Loja indisponível.', 'EventsHouse'));
    }

    $pack_id = isset($_POST['pack_id']) ? absint(wp_unslash($_POST['pack_id'])) : 0;
    $extra_a = max(0, min(20, absint($_POST['extra_adults'] ?? 0)));
    $extra_c = max(0, min(20, absint($_POST['extra_children'] ?? 0)));

    $allowed = EventsHouse_ilha_pack_product_ids();
    if (!$pack_id || !in_array($pack_id, $allowed, true)) {
        return new WP_Error('bad_pack', __('Pack inválido.', 'EventsHouse'));
    }

    $extras = EventsHouse_ilha_extra_product_ids();
    $adult_id = $extras['adult'];
    $child_id = $extras['child'];

    $pack = wc_get_product($pack_id);
    if (!$pack || !$pack->is_purchasable()) {
        return new WP_Error('nopack', __('Este pack não está disponível.', 'EventsHouse'));
    }

    if ($extra_a > 0) {
        $pa = wc_get_product($adult_id);
        if (!$pa || !$pa->is_purchasable()) {
            return new WP_Error('noadult', __('O produto «adulto extra» não está disponível.', 'EventsHouse'));
        }
    }
    if ($extra_c > 0) {
        $pc = wc_get_product($child_id);
        if (!$pc || !$pc->is_purchasable()) {
            return new WP_Error('nochild', __('O produto «criança extra» não está disponível.', 'EventsHouse'));
        }
    }

    if (!WC()->cart->add_to_cart($pack_id, 1)) {
        return new WP_Error('cart', __('Não foi possível adicionar o pack ao carrinho.', 'EventsHouse'));
    }
    if ($extra_a > 0 && !WC()->cart->add_to_cart($adult_id, $extra_a)) {
        return new WP_Error('cart', __('Não foi possível adicionar adultos extra.', 'EventsHouse'));
    }
    if ($extra_c > 0 && !WC()->cart->add_to_cart($child_id, $extra_c)) {
        return new WP_Error('cart', __('Não foi possível adicionar crianças extra.', 'EventsHouse'));
    }

    return true;
}

/**
 * Carrinho: pack + extras (AJAX ou POST clássico).
 */
function EventsHouse_ajax_ilha_add_pack_with_extras()
{
    $nonce = isset($_POST['security']) ? sanitize_text_field(wp_unslash($_POST['security'])) : '';
    if ($nonce === '' && isset($_POST['eh_ilha_pack_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['eh_ilha_pack_nonce']));
    }
    if (!wp_verify_nonce($nonce, 'eh_ilha_pack')) {
        if (wp_doing_ajax()) {
            wp_send_json_error(array(
                'message' => __('Sessão expirou. Atualize a página e tente de novo.', 'EventsHouse'),
            ));
        }
        if (function_exists('wc_add_notice')) {
            wc_add_notice(__('Sessão expirou. Tente novamente.', 'EventsHouse'), 'error');
        }
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : home_url('/'));
        exit;
    }

    $result = EventsHouse_ilha_process_pack_with_extras();
    if (is_wp_error($result)) {
        if (wp_doing_ajax()) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        if (function_exists('wc_add_notice')) {
            wc_add_notice($result->get_error_message(), 'error');
        }
        wp_safe_redirect(wp_get_referer() ? wp_get_referer() : home_url('/'));
        exit;
    }

    if (wp_doing_ajax() && class_exists('WC_AJAX')) {
        \WC_AJAX::get_refreshed_fragments();
    }

    if (function_exists('wc_add_notice')) {
        wc_add_notice(__('Produtos adicionados ao carrinho.', 'EventsHouse'), 'success');
    }
    wp_safe_redirect(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/'));
    exit;
}

add_action('wp_ajax_eh_ilha_add_pack_with_extras', 'EventsHouse_ajax_ilha_add_pack_with_extras');
add_action('wp_ajax_nopriv_eh_ilha_add_pack_with_extras', 'EventsHouse_ajax_ilha_add_pack_with_extras');

/**
 * ═══════════════════════════════════════════════════
 * VINCULAR INVENTÁRIO (STOCK) ENTRE PASSES MENSAL E SEMANAL
 * ═══════════════════════════════════════════════════
 * Quando o stock de um passe é atualizado (venda ou no wp-admin),
 * o outro passe fica instantaneamente com o mesmo stock.
 */
function EventsHouse_sync_passes_stock($product)
{
    // Usa static flag para precaver loop infinito de updates
    static $syncing = false;
    if ($syncing) return;

    $weekly_id  = get_theme_mod('woocommerce_weekly_pass_id');
    $monthly_id = get_theme_mod('woocommerce_monthly_pass_id');

    if (!$weekly_id || !$monthly_id) return;

    $product_id = $product->get_id();

    // Apenas sincroniza se o produto que está a ser guardado for um dos Passes
    if ($product_id != $weekly_id && $product_id != $monthly_id) return;

    // Define qual é o "outro" passe
    $target_id = ($product_id == $weekly_id) ? $monthly_id : $weekly_id;
    $target_product = wc_get_product($target_id);

    // Se o outro passe existe e gere stock, copiamos o stock deste
    if ($target_product && $target_product->managing_stock()) {
        $new_stock = $product->get_stock_quantity();
        
        $syncing = true;
        wc_update_product_stock($target_product, $new_stock);
        $syncing = false;
    }
}
add_action('woocommerce_product_set_stock', 'EventsHouse_sync_passes_stock', 10, 1);

/**
 * ═══════════════════════════════════════════════════
 * SINCRONIZAÇÃO WOOCOMMERCE → SISTEMA DE PASSES
 * ═══════════════════════════════════════════════════
 */
function ilha_sync_woocommerce_order($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    // Apenas processa se não tiver sido processado antes (para evitar duplicados)
    if ($order->get_meta('_ilha_passes_processed')) return;

    $weekly_id  = get_theme_mod('woocommerce_weekly_pass_id');
    $monthly_id = get_theme_mod('woocommerce_monthly_pass_id');
    $pack_1_id  = get_theme_mod('woocommerce_pack_1_id');
    $pack_6_id  = get_theme_mod('woocommerce_pack_6_id');
    $pack_12_id = get_theme_mod('woocommerce_pack_12_id');

    $passes_ids = array(
        'semanal' => $weekly_id,
        'mensal'  => $monthly_id,
        'pack_1'  => $pack_1_id,
        'pack_6'  => $pack_6_id,
        'pack_12' => $pack_12_id
    );

    $has_passes = false;
    $passes_to_create = array();

    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        $qty = $item->get_quantity();

        foreach ($passes_ids as $tipo => $id) {
            if ($id && $product_id == $id) {
                $has_passes = true;
                for ($i = 0; $i < $qty; $i++) {
                    $passes_to_create[] = array(
                        'tipo' => $tipo,
                        'produto_wc_id' => $product_id
                    );
                }
            }
        }
    }

    if (!$has_passes) return;

    $extras_order = ilha_count_extras_in_order($order);

    global $wpdb;

    // 1. Procurar ou Criar Cliente
    $billing_email = $order->get_billing_email();
    $billing_first_name = $order->get_billing_first_name();
    $billing_last_name = $order->get_billing_last_name();
    $billing_phone = $order->get_billing_phone();
    if (empty($billing_phone) && function_exists('EventsHouse_wc_event_find_phone_in_order')) {
        $billing_phone = EventsHouse_wc_event_find_phone_in_order($order);
    }
    $user_id = $order->get_user_id();

    $nome_completo = trim($billing_first_name . ' ' . $billing_last_name);

    $table_clientes = $wpdb->prefix . 'ilha_clientes';
    $cliente = null;

    if ($billing_email) {
        $cliente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_clientes WHERE email = %s", $billing_email));
    }

    $cliente_id = 0;
    if ($cliente) {
        $cliente_id = $cliente->id;
        // Atualiza telefone se estiver vazio
        if (empty($cliente->telefone) && !empty($billing_phone)) {
            $wpdb->update($table_clientes, array('telefone' => $billing_phone), array('id' => $cliente_id));
        }
    } else {
        $wpdb->insert($table_clientes, array(
            'user_id' => $user_id ? $user_id : null,
            'nome' => $nome_completo,
            'email' => $billing_email,
            'telefone' => $billing_phone
        ));
        $cliente_id = $wpdb->insert_id;
    }

    if (!$cliente_id) return;

    // 2. Criar Passes
    $table_passes = $wpdb->prefix . 'ilha_passes';
    $data_compra = current_time('mysql');
    $pass_index   = 0;
    $pass_count   = count($passes_to_create);

    foreach ($passes_to_create as $pass_data) {
        $pass_index++;
        $dias_total = 0;
        $tipo = $pass_data['tipo'];
        
        if ($tipo === 'pack_1') $dias_total = 1;
        elseif ($tipo === 'pack_6') $dias_total = 6;
        elseif ($tipo === 'pack_12') $dias_total = 12;
        elseif ($tipo === 'semanal') $dias_total = 3; // 1 semana, até 3 dias
        elseif ($tipo === 'mensal') $dias_total = 12; // 1 mês, até 3 dias/semana (média 12)

        // Data expiração
        $data_expiracao = null;
        if ($tipo === 'semanal') {
            $data_expiracao = gmdate('Y-m-d H:i:s', strtotime('+7 days', current_time('timestamp')));
        } elseif ($tipo === 'mensal') {
            $data_expiracao = gmdate('Y-m-d H:i:s', strtotime('+1 month', current_time('timestamp')));
        } elseif (strpos($tipo, 'pack') !== false) {
            $data_expiracao = gmdate('Y-m-d H:i:s', strtotime('+6 months', current_time('timestamp'))); // packs validade 6 meses
        }

        $extra_row = array(
            'extra_adultos_total'   => 0,
            'extra_criancas_total'  => 0,
            'extra_adultos_usados'  => 0,
            'extra_criancas_usados' => 0,
        );
        // Extras da mesma encomenda ficam no passe (um pack por encomenda é o caso habitual).
        if ($pass_count === 1 || $pass_index === 1) {
            $extra_row['extra_adultos_total']  = $extras_order['adultos'];
            $extra_row['extra_criancas_total'] = $extras_order['criancas'];
        }

        $wpdb->insert($table_passes, array_merge(array(
            'cliente_id' => $cliente_id,
            'tipo' => $tipo,
            'dias_total' => $dias_total,
            'dias_usados' => 0,
            'data_compra' => $data_compra,
            'data_expiracao' => $data_expiracao,
            'status' => 'ativo',
            'produto_wc_id' => $pass_data['produto_wc_id'],
            'order_wc_id' => $order_id
        ), $extra_row));
    }

    // Marca como processado
    $order->update_meta_data('_ilha_passes_processed', 'yes');
    $order->save();
}
add_action('woocommerce_order_status_completed', 'ilha_sync_woocommerce_order', 10, 1);

/**
 * URL da página Manhãs na Ilha (opcional âncora, ex. #ilha-packs).
 */
function EventsHouse_ilha_passes_page_url($anchor = '')
{
    $page = get_page_by_path('manhas-na-ilha');
    if (!$page) {
        $page = get_page_by_path('ilha-passes');
    }
    $url = $page ? get_permalink($page->ID) : home_url('/manhas-na-ilha/');
    if ($anchor !== '' && $anchor[0] !== '#') {
        $anchor = '#' . $anchor;
    }

    return $url . $anchor;
}

/**
 * IDs de todos os produtos WooCommerce ligados a Manhãs na Ilha.
 *
 * @return int[]
 */
function EventsHouse_ilha_all_product_ids()
{
    $ids = array(
        get_theme_mod('woocommerce_weekly_pass_id'),
        get_theme_mod('woocommerce_monthly_pass_id'),
        get_theme_mod('woocommerce_pack_1_id'),
        get_theme_mod('woocommerce_pack_6_id'),
        get_theme_mod('woocommerce_pack_12_id'),
    );
    $extras = EventsHouse_ilha_extra_product_ids();
    $ids[] = $extras['adult'];
    $ids[] = $extras['child'];

    return array_values(array_unique(array_filter(array_map('absint', $ids))));
}

/**
 * @param WC_Order|int $order
 */
function ilha_order_contains_ilha_products($order)
{
    $order = is_numeric($order) ? wc_get_order($order) : $order;
    if (!$order) {
        return false;
    }

    $ilha_ids = EventsHouse_ilha_all_product_ids();
    if (empty($ilha_ids)) {
        return false;
    }

    foreach ($order->get_items() as $item) {
        if (in_array((int) $item->get_product_id(), $ilha_ids, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Após pagamento confirmado, conclui encomendas de Manhãs na Ilha (em vez de ficar «Em processamento»).
 */
function ilha_auto_complete_ilha_order($order_id)
{
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);
    if (!$order || $order->get_status() === 'completed') {
        return;
    }

    if (!ilha_order_contains_ilha_products($order)) {
        return;
    }

    $order->update_status(
        'completed',
        __('Pagamento confirmado — encomenda concluída automaticamente (Manhãs na Ilha).', 'EventsHouse'),
        true
    );
}
add_action('woocommerce_order_status_processing', 'ilha_auto_complete_ilha_order', 20, 1);
add_action('woocommerce_payment_complete', 'ilha_auto_complete_ilha_order', 20, 1);

/**
 * Cliente Ilha pelo email de compra / faturação.
 *
 * @param string $email
 * @return object|null
 */
function ilha_get_cliente_by_email($email)
{
    $email = sanitize_email($email);
    if ($email === '' || !is_email($email)) {
        return null;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ilha_clientes';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE email = %s LIMIT 1",
        $email
    ));
}

/**
 * Cliente Ilha associado ao utilizador WordPress (por user_id ou email).
 *
 * @param WP_User|null $user
 * @return object|null
 */
function ilha_get_cliente_for_user($user = null)
{
    if (!$user) {
        $user = wp_get_current_user();
    }
    if (!$user || !$user->ID) {
        return null;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ilha_clientes';

    $cliente = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE user_id = %d LIMIT 1",
        $user->ID
    ));
    if ($cliente) {
        return $cliente;
    }

    if (!empty($user->user_email)) {
        return ilha_get_cliente_by_email($user->user_email);
    }

    return null;
}

/**
 * Passe ativo com manhãs disponíveis para reserva (sem fazer check-in).
 *
 * @param int $cliente_id
 * @return object|null
 */
function ilha_get_reservable_pass_for_cliente($cliente_id)
{
    $cliente_id = absint($cliente_id);
    if (!$cliente_id) {
        return null;
    }

    global $wpdb;
    $table_passes = $wpdb->prefix . 'ilha_passes';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_passes WHERE cliente_id = %d AND status = 'ativo' AND (data_expiracao IS NULL OR data_expiracao > NOW()) AND dias_usados < dias_total ORDER BY id DESC LIMIT 1",
        $cliente_id
    ));
}

/**
 * Verifica por email se o cliente tem manhãs disponíveis para reserva.
 */
function ajax_ilha_reservation_check_pass()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    if ($email === '' || !is_email($email)) {
        wp_send_json_error(array('message' => __('Indique um email válido.', 'EventsHouse')));
    }

    $cliente = ilha_get_cliente_by_email($email);
    if (!$cliente) {
        wp_send_json_error(array(
            'has_pass'  => false,
            'message'   => __('Reservas permitidas apenas para utilizadores com pacote ativo.', 'EventsHouse'),
            'packs_url' => EventsHouse_ilha_passes_page_url('#ilha-packs'),
        ));
    }

    $passe = ilha_get_reservable_pass_for_cliente((int) $cliente->id);
    if (!$passe) {
        wp_send_json_error(array(
            'has_pass'  => false,
            'message'   => __('Reservas permitidas apenas para utilizadores com pacote ativo.', 'EventsHouse'),
            'packs_url' => EventsHouse_ilha_passes_page_url('#ilha-packs'),
        ));
    }

    wp_send_json_success(array(
        'has_pass'       => true,
        'display_name'   => $cliente->nome ?: '',
        'email'          => $email,
        'dias_restantes' => (int) $passe->dias_total - (int) $passe->dias_usados,
        'tipo_formatado' => formatar_tipo_passe($passe->tipo),
    ));
}
add_action('wp_ajax_ilha_reservation_check_pass', 'ajax_ilha_reservation_check_pass');
add_action('wp_ajax_nopriv_ilha_reservation_check_pass', 'ajax_ilha_reservation_check_pass');

/**
 * ═══════════════════════════════════════════════════
 * AJAX ENDPOINTS - PAINEL DA RECEÇÃO
 * ═══════════════════════════════════════════════════
 */

// 1. Pesquisa de Clientes
function ajax_ilha_search_client() {
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_access_rececao()) wp_send_json_error('Acesso negado');

    $termo = sanitize_text_field($_POST['term'] ?? '');
    if (empty($termo)) wp_send_json_success(array());

    global $wpdb;
    $table = $wpdb->prefix . 'ilha_clientes';
    $like = '%' . $wpdb->esc_like($termo) . '%';
    
    $clientes = $wpdb->get_results($wpdb->prepare(
        "SELECT id, nome, email, telefone FROM $table WHERE nome LIKE %s OR email LIKE %s LIMIT 10",
        $like, $like
    ));

    wp_send_json_success($clientes);
}
add_action('wp_ajax_ilha_search_client', 'ajax_ilha_search_client');

// 2. Obter passe ativo de um cliente
function ajax_ilha_get_active_pass() {
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_access_rececao()) wp_send_json_error('Acesso negado');

    $cliente_id = absint($_POST['cliente_id'] ?? 0);
    if (!$cliente_id) wp_send_json_error('Cliente não especificado');

    global $wpdb;
    $table_passes = $wpdb->prefix . 'ilha_passes';

    // Procura passe ativo
    $passe = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_passes WHERE cliente_id = %d AND status = 'ativo' AND (data_expiracao IS NULL OR data_expiracao > NOW()) ORDER BY id DESC LIMIT 1",
        $cliente_id
    ));

    if ($passe) {
        $passe->tipo_formatado = formatar_tipo_passe($passe->tipo);
        $passe->extras         = ilha_pass_extras_payload($passe);
        $passe->meias          = ilha_rececao_meias_product_payload();
        $hoje = current_time('Y-m-d');
        $reserva_hoje = ilha_get_cliente_reserva_on_date($cliente_id, $hoje);
        $passe->reserva_hoje = null;
        if ($reserva_hoje) {
            $passe->reserva_hoje = array(
                'event_time'  => $reserva_hoje->event_time,
                'event_title' => $reserva_hoje->event_title,
                'status'      => $reserva_hoje->status,
            );
        }
        wp_send_json_success($passe);
    } else {
        wp_send_json_error('Nenhum passe ativo encontrado para este cliente.');
    }
}
add_action('wp_ajax_ilha_get_active_pass', 'ajax_ilha_get_active_pass');

// Helper para formatar o nome do passe
function formatar_tipo_passe($tipo) {
    $nomes = array(
        'pack_1' => 'Pack 1 Manhã',
        'pack_6' => 'Pack 6 Manhãs',
        'pack_12' => 'Pack 12 Manhãs',
        'semanal' => 'Passe Semanal',
        'mensal' => 'Passe Mensal'
    );
    return $nomes[$tipo] ?? $tipo;
}

// 3. Registar Check-in (Dar Baixa)
function ajax_ilha_checkin() {
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_access_rececao()) wp_send_json_error('Acesso negado');

    $passe_id = absint($_POST['passe_id'] ?? 0);
    $cliente_id = absint($_POST['cliente_id'] ?? 0);
    $usar_extra_adultos  = max(0, min(20, absint($_POST['extra_adultos'] ?? 0)));
    $usar_extra_criancas = max(0, min(20, absint($_POST['extra_criancas'] ?? 0)));
    $meias_order_id      = absint($_POST['meias_order_id'] ?? 0);

    if (!$passe_id || !$cliente_id) wp_send_json_error('Dados incompletos');

    global $wpdb;
    $table_passes = $wpdb->prefix . 'ilha_passes';
    $table_utilizacoes = $wpdb->prefix . 'ilha_utilizacoes';

    // Verificar se já fez checkin hoje
    $hoje = current_time('Y-m-d');
    $ja_fez_checkin = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_utilizacoes WHERE passe_id = %d AND DATE(data_utilizacao) = %s",
        $passe_id, $hoje
    ));

    if ($ja_fez_checkin > 0) {
        wp_send_json_error('Este cliente já fez check-in hoje com este passe.');
    }

    // Info do passe
    $passe = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_passes WHERE id = %d", $passe_id));
    if (!$passe || $passe->status !== 'ativo') {
        wp_send_json_error('Passe inválido ou não ativo.');
    }

    $extras_ok = ilha_validate_and_apply_extras_checkin($passe, $usar_extra_adultos, $usar_extra_criancas);
    if (is_wp_error($extras_ok)) {
        wp_send_json_error($extras_ok->get_error_message());
    }

    $meias_order = null;
    if ($meias_order_id) {
        $meias_ok = ilha_rececao_complete_meias_order($meias_order_id, $cliente_id);
        if (is_wp_error($meias_ok)) {
            wp_send_json_error($meias_ok->get_error_message());
        }
        $order = wc_get_order($meias_order_id);
        if ($order) {
            $meias_order = array(
                'order_id' => $meias_order_id,
                'total'    => ilha_rececao_format_order_total($order),
            );
        }
    }

    // Regista a utilização
    $wpdb->insert($table_utilizacoes, array(
        'passe_id' => $passe_id,
        'cliente_id' => $cliente_id,
        'data_utilizacao' => current_time('mysql'),
        'registado_por' => get_current_user_id(),
        'extra_adultos_usados' => $usar_extra_adultos,
        'extra_criancas_usados' => $usar_extra_criancas,
    ));

    // Atualiza dias usados
    $novos_dias_usados = $passe->dias_usados + 1;
    $novo_status = 'ativo';

    if ($novos_dias_usados >= $passe->dias_total) {
        $novo_status = 'esgotado';
    }

    $pass_update = array(
        'dias_usados' => $novos_dias_usados,
        'status'      => $novo_status,
    );
    if ($usar_extra_adultos > 0) {
        $pass_update['extra_adultos_usados'] = (int) $passe->extra_adultos_usados + $usar_extra_adultos;
    }
    if ($usar_extra_criancas > 0) {
        $pass_update['extra_criancas_usados'] = (int) $passe->extra_criancas_usados + $usar_extra_criancas;
    }

    $wpdb->update(
        $table_passes,
        $pass_update,
        array('id' => $passe_id)
    );

    ilha_mark_reserva_presente_for_checkin($cliente_id, $hoje);

    $msg = __('Check-in realizado!', 'EventsHouse');
    if ($usar_extra_adultos > 0 || $usar_extra_criancas > 0) {
        $msg .= ' ' . sprintf(
            __('Extras registados: %s.', 'EventsHouse'),
            ilha_format_extras_label($usar_extra_adultos, $usar_extra_criancas, false)
        );
    }

    if ($meias_order) {
        $msg .= ' ' . sprintf(
            /* translators: 1: order id, 2: order total */
            __('Meias: encomenda #%1$d concluída (%2$s).', 'EventsHouse'),
            $meias_order['order_id'],
            $meias_order['total']
        );
    }

    wp_send_json_success(array(
        'mensagem' => $msg,
        'dias_restantes' => $passe->dias_total - $novos_dias_usados,
        'status' => $novo_status,
        'extras_usados' => array(
            'adultos'  => $usar_extra_adultos,
            'criancas' => $usar_extra_criancas,
        ),
        'meias_order' => $meias_order,
    ));
}
add_action('wp_ajax_ilha_checkin', 'ajax_ilha_checkin');

// 4. Obter Histórico de hoje
function ajax_ilha_get_historico() {
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_access_rececao()) wp_send_json_error('Acesso negado');

    global $wpdb;
    $table_utilizacoes = $wpdb->prefix . 'ilha_utilizacoes';
    $table_clientes = $wpdb->prefix . 'ilha_clientes';
    $table_passes = $wpdb->prefix . 'ilha_passes';
    
    $hoje = current_time('Y-m-d');
    
    $historico = $wpdb->get_results($wpdb->prepare("
        SELECT u.data_utilizacao, u.extra_adultos_usados, u.extra_criancas_usados,
               c.nome, p.tipo, p.dias_total, p.dias_usados
        FROM $table_utilizacoes u
        JOIN $table_clientes c ON u.cliente_id = c.id
        JOIN $table_passes p ON u.passe_id = p.id
        WHERE DATE(u.data_utilizacao) = %s
        ORDER BY u.data_utilizacao DESC
    ", $hoje));

    foreach ($historico as $h) {
        $h->tipo_formatado = formatar_tipo_passe($h->tipo);
        $h->hora = wp_date('H:i', strtotime($h->data_utilizacao));
        $h->extras_label = ilha_format_extras_label(
            (int) ($h->extra_adultos_usados ?? 0),
            (int) ($h->extra_criancas_usados ?? 0),
            true
        );
    }

    wp_send_json_success($historico);
}
add_action('wp_ajax_ilha_get_historico', 'ajax_ilha_get_historico');
