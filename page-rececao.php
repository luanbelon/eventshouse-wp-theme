<?php
/**
 * Template Name: Área da Receção
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('EventsHouse_rececao_render_logo')) {
    /**
     * Logótipo do tema (Personalizar → Identidade do site) ou marca textual.
     *
     * @param string $class Classe do <img> ou do contentor de texto.
     */
    function EventsHouse_rececao_render_logo($class = 'rececao-brand-logo-img')
    {
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id && has_custom_logo()) {
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
            if (!empty($logo[0])) {
                echo '<img class="' . esc_attr($class) . '" src="' . esc_url($logo[0]) . '" alt="' . esc_attr(get_bloginfo('name')) . '" width="160" height="48" loading="lazy" decoding="async" />';

                return;
            }
        }
        echo '<span class="rececao-brand-wordmark" aria-hidden="true">A Ilha</span>';
    }
}

if (!function_exists('EventsHouse_rececao_greeting_line')) {
    /**
     * Saudação contextual + primeiro nome do utilizador.
     */
    function EventsHouse_rececao_greeting_line()
    {
        $user = wp_get_current_user();
        $first = trim($user->first_name);
        if ($first === '') {
            $dn = trim($user->display_name);
            if ($dn !== '') {
                $parts = preg_split('/\s+/u', $dn, 2);
                $first   = $parts[0];
            } else {
                $first = __('Equipa', 'EventsHouse');
            }
        }

        // Hora sempre em Portugal (continental — Europe/Lisbon, com horário de verão).
        $tz_pt = new \DateTimeZone('Europe/Lisbon');
        $h     = (int) wp_date('G', null, $tz_pt);
        if ($h < 12) {
            $salut = __('Bom dia', 'EventsHouse');
        } elseif ($h < 19) {
            $salut = __('Boa tarde', 'EventsHouse');
        } else {
            $salut = __('Boa noite', 'EventsHouse');
        }

        return $salut . ', ' . $first;
    }
}

// Lógica de Login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rececao_login'])) {
    $creds = array(
        'user_login'    => sanitize_user(wp_unslash($_POST['log'] ?? '')),
        'user_password' => $_POST['pwd'] ?? '',
        'remember'      => isset($_POST['rememberme']),
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        wp_safe_redirect(get_permalink());
        exit;
    }
}

// Verifica acesso
$is_logged_in = is_user_logged_in();
$has_access   = false;
if ($is_logged_in) {
    if (ilha_user_can_access_rececao()) {
        $has_access = true;
    } else {
        $login_error = __('O seu utilizador não tem permissões para aceder a esta área.', 'EventsHouse');
    }
}

$rececao_greeting = $has_access ? EventsHouse_rececao_greeting_line() : '';
$rececao_date     = $has_access
    ? wp_date('l, j \d\e F \d\e Y', current_time('timestamp'), wp_timezone())
    : '';

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html(get_bloginfo('name')); ?> — <?php esc_html_e('Receção', 'EventsHouse'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo esc_url(EventsHouse_URI . '/assets/css/rececao.css'); ?>?v=<?php echo esc_attr(EventsHouse_VERSION); ?>">
    <?php wp_head(); ?>
</head>
<body class="rececao-body">

<?php if (!$has_access) : ?>
    <div class="rececao-login-wrapper">
        <div class="rececao-login-panel">
            <div class="rececao-login-card">
                <div class="rececao-login-brand">
                    <?php EventsHouse_rececao_render_logo('rececao-login-logo-img'); ?>
                    <p class="rececao-login-tagline"><?php esc_html_e('Área reservada · Receção', 'EventsHouse'); ?></p>
                </div>

                <?php if ($login_error) : ?>
                    <div class="rececao-alert rececao-alert--error" role="alert"><?php echo esc_html($login_error); ?></div>
                <?php endif; ?>

                <form method="post" action="" class="rececao-form" autocomplete="on">
                    <input type="hidden" name="rececao_login" value="1">
                    <div class="rececao-field">
                        <label class="rececao-label" for="user_login"><?php esc_html_e('Email ou utilizador', 'EventsHouse'); ?></label>
                        <input class="rececao-input" type="text" name="log" id="user_login" required autocomplete="username">
                    </div>
                    <div class="rececao-field">
                        <label class="rececao-label" for="user_pass"><?php esc_html_e('Palavra-passe', 'EventsHouse'); ?></label>
                        <input class="rececao-input" type="password" name="pwd" id="user_pass" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="rececao-btn rececao-btn--primary"><?php esc_html_e('Entrar', 'EventsHouse'); ?></button>
                </form>
            </div>
            <p class="rececao-login-foot"><?php esc_html_e('Acesso exclusivo a rececionistas e administradores.', 'EventsHouse'); ?></p>
        </div>
    </div>
<?php else : ?>
    <div class="rececao-dashboard">
        <aside class="rececao-sidebar" aria-label="<?php esc_attr_e('Navegação da receção', 'EventsHouse'); ?>">
            <div class="rececao-sidebar-brand">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="rececao-sidebar-logo-link" title="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <?php EventsHouse_rececao_render_logo('rececao-sidebar-logo-img'); ?>
                </a>
                <span class="rececao-sidebar-badge"><?php esc_html_e('Receção', 'EventsHouse'); ?></span>
            </div>
            <nav class="rececao-nav" aria-label="<?php esc_attr_e('Secções', 'EventsHouse'); ?>">
                <a href="#tab-checkin" class="rececao-nav-item active" data-tab="checkin">
                    <svg class="rececao-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <?php esc_html_e('Check-in', 'EventsHouse'); ?>
                </a>
                <a href="#tab-reservas" class="rececao-nav-item" data-tab="reservas">
                    <svg class="rececao-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <?php esc_html_e('Reservas', 'EventsHouse'); ?>
                </a>
                <a href="#tab-historico" class="rececao-nav-item" data-tab="historico">
                    <svg class="rececao-nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?php esc_html_e('Histórico hoje', 'EventsHouse'); ?>
                </a>
            </nav>
            <div class="rececao-sidebar-foot">
                <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="rececao-logout"><?php esc_html_e('Terminar sessão', 'EventsHouse'); ?></a>
            </div>
        </aside>

        <div class="rececao-workspace">
            <header class="rececao-topbar">
                <div class="rececao-topbar-greet">
                    <h1 class="rececao-greeting"><?php echo esc_html($rececao_greeting); ?></h1>
                    <p class="rececao-greeting-sub"><?php esc_html_e('Painel da receção · A Ilha', 'EventsHouse'); ?></p>
                </div>
                <div class="rececao-topbar-meta">
                    <time class="rececao-date" datetime="<?php echo esc_attr(wp_date('c', current_time('timestamp'), wp_timezone())); ?>"><?php echo esc_html($rececao_date); ?></time>
                </div>
            </header>

            <main class="rececao-main">
                <section id="tab-checkin" class="rececao-tab is-active" aria-labelledby="rececao-checkin-title">
                    <header class="rececao-section-head">
                        <h2 id="rececao-checkin-title"><?php esc_html_e('Registar entrada', 'EventsHouse'); ?></h2>
                        <p class="rececao-section-desc"><?php esc_html_e('Pesquise o cliente pelo nome ou email para registar o check-in do dia.', 'EventsHouse'); ?></p>
                    </header>

                    <div class="rececao-search-wrap">
                        <label class="screen-reader-text" for="search-client-input"><?php esc_html_e('Pesquisar cliente', 'EventsHouse'); ?></label>
                        <input type="text" id="search-client-input" class="rececao-search-input" placeholder="<?php esc_attr_e('Pesquisar cliente…', 'EventsHouse'); ?>" autocomplete="off">
                        <div id="search-spinner" class="rececao-spinner" style="display: none;" aria-hidden="true"></div>
                        <ul id="search-results" class="rececao-search-results"></ul>
                    </div>

                    <div id="client-pass-details" class="rececao-client-panel" style="display: none;">
                        <div class="rececao-client-card">
                            <h3 id="cp-nome"><?php esc_html_e('Nome do cliente', 'EventsHouse'); ?></h3>
                            <p id="cp-email">email@cliente.com</p>
                        </div>

                        <div id="active-pass-card" class="rececao-pass-card">
                            <!-- Conteúdo dinâmico via JS -->
                        </div>
                    </div>
                </section>

                <section id="tab-reservas" class="rececao-tab" aria-labelledby="rececao-reservas-title">
                    <header class="rececao-section-head rececao-section-head--row">
                        <div>
                            <h2 id="rececao-reservas-title"><?php esc_html_e('Reservas do dia', 'EventsHouse'); ?></h2>
                            <p class="rececao-section-desc"><?php esc_html_e('Clientes com manhã reservada na data seleccionada.', 'EventsHouse'); ?></p>
                        </div>
                        <button type="button" id="btn-refresh-reservas" class="rececao-btn rececao-btn--ghost"><?php esc_html_e('Atualizar', 'EventsHouse'); ?></button>
                    </header>

                    <div class="rececao-reservas-toolbar">
                        <label for="rececao-reservas-date" class="rececao-label"><?php esc_html_e('Data', 'EventsHouse'); ?></label>
                        <input type="date" id="rececao-reservas-date" class="rececao-input rececao-input--date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>" />
                    </div>

                    <div id="rececao-reservas-summary" class="rececao-reservas-summary" aria-live="polite"></div>

                    <div class="rececao-table-wrap">
                        <table class="rececao-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Horário', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Idade', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Contacto', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Pack', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Extras', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Estado', 'EventsHouse'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="reservas-tbody"></tbody>
                        </table>
                    </div>
                </section>

                <section id="tab-historico" class="rececao-tab" aria-labelledby="rececao-hist-title">
                    <header class="rececao-section-head rececao-section-head--row">
                        <div>
                            <h2 id="rececao-hist-title"><?php esc_html_e('Histórico de hoje', 'EventsHouse'); ?></h2>
                            <p class="rececao-section-desc"><?php esc_html_e('Check-ins registados na data de hoje.', 'EventsHouse'); ?></p>
                        </div>
                        <button type="button" id="btn-refresh-historico" class="rececao-btn rececao-btn--ghost"><?php esc_html_e('Atualizar', 'EventsHouse'); ?></button>
                    </header>

                    <div class="rececao-table-wrap">
                        <table class="rececao-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Hora', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Passe / pack', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Dias usados', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Extras', 'EventsHouse'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="historico-tbody">
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>

        <div id="rececao-meias-modal" class="rececao-modal" hidden aria-hidden="true">
            <div class="rececao-modal__backdrop" data-meias-close></div>
            <div class="rececao-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="rececao-meias-modal-title">
                <div id="rececao-meias-modal-body" class="rececao-modal__body"></div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$rececao_meias_payload = ilha_rececao_meias_product_payload();
if (!$rececao_meias_payload) {
    $rececao_meias_payload = array(
        'id'              => ilha_get_meias_product_id(),
        'nome'            => __('Meias antiderrapantes', 'EventsHouse'),
        'preco'           => 0,
        'preco_formatado' => '',
        'stock'           => null,
        'em_stock'        => true,
    );
}
?>

<script>
    var ilhaRececaoData = {
        ajax_url: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('EventsHouse_nonce')); ?>,
        meias: <?php echo wp_json_encode($rececao_meias_payload); ?>
    };
</script>
<script src="<?php echo esc_url(EventsHouse_URI . '/assets/js/rececao.js'); ?>?v=<?php echo esc_attr(EventsHouse_VERSION); ?>"></script>
<?php wp_footer(); ?>
</body>
</html>
