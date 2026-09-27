<?php
/**
 * Dashboard wp-admin: check-ins, KPIs, listagens e limpeza de menu (Posts/Comentários).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Intervalos de datas no fuso do site (para SQL em data_utilizacao).
 *
 * @return array{start:string,end:string}
 */
function EventsHouse_checkins_month_bounds_mysql()
{
    $tz   = wp_timezone();
    $now  = new DateTimeImmutable('now', $tz);
    $first = $now->modify('first day of this month')->setTime(0, 0, 0);
    $last  = $now->modify('last day of this month')->setTime(23, 59, 59);

    return array(
        'start' => $first->format('Y-m-d H:i:s'),
        'end'   => $last->format('Y-m-d H:i:s'),
    );
}

/**
 * Semana corrente: segunda 00:00 — domingo 23:59:59.
 *
 * @return array{start:string,end:string}
 */
function EventsHouse_checkins_week_bounds_mysql()
{
    $tz  = wp_timezone();
    $now = new DateTimeImmutable('now', $tz);
    $dow = (int) $now->format('N');
    $monday = $now->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
    $sunday = $monday->modify('+6 days')->setTime(23, 59, 59);

    return array(
        'start' => $monday->format('Y-m-d H:i:s'),
        'end'   => $sunday->format('Y-m-d H:i:s'),
    );
}

/**
 * @return object{today:int,week:int,month:int,total_all:int,active_passes:int,by_tipo:array<string,int>}
 */
function EventsHouse_checkins_dashboard_stats()
{
    global $wpdb;
    $u = $wpdb->prefix . 'ilha_utilizacoes';
    $p = $wpdb->prefix . 'ilha_passes';

    $today = current_time('Y-m-d');
    $week  = EventsHouse_checkins_week_bounds_mysql();
    $month = EventsHouse_checkins_month_bounds_mysql();

    $today_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $u WHERE DATE(data_utilizacao) = %s",
            $today
        )
    );

    $week_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $u WHERE data_utilizacao >= %s AND data_utilizacao <= %s",
            $week['start'],
            $week['end']
        )
    );

    $month_count = (int) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $u WHERE data_utilizacao >= %s AND data_utilizacao <= %s",
            $month['start'],
            $month['end']
        )
    );

    $total_all = (int) $wpdb->get_var("SELECT COUNT(*) FROM $u");

    $active_passes = (int) $wpdb->get_var(
        "SELECT COUNT(*) FROM $p WHERE status = 'ativo'"
    );

    $by_tipo_rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p2.tipo, COUNT(*) AS c
            FROM $u u
            INNER JOIN $p p2 ON u.passe_id = p2.id
            WHERE u.data_utilizacao >= %s AND u.data_utilizacao <= %s
            GROUP BY p2.tipo
            ORDER BY c DESC",
            $week['start'],
            $week['end']
        ),
        ARRAY_A
    );

    $by_tipo = array();
    if (is_array($by_tipo_rows)) {
        foreach ($by_tipo_rows as $row) {
            $by_tipo[$row['tipo']] = (int) $row['c'];
        }
    }

    return (object) array(
        'today'         => $today_count,
        'week'          => $week_count,
        'month'         => $month_count,
        'total_all'     => $total_all,
        'active_passes' => $active_passes,
        'by_tipo'       => $by_tipo,
    );
}

/**
 * Top clientes por número de check-ins (total).
 *
 * @param int $limit
 * @return array<int, object>
 */
function EventsHouse_checkins_top_clients($limit = 10)
{
    global $wpdb;
    $u = $wpdb->prefix . 'ilha_utilizacoes';
    $c = $wpdb->prefix . 'ilha_clientes';

    $limit = max(1, min(50, (int) $limit));

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.id, c.nome, c.email, COUNT(u.id) AS checkins
            FROM $u u
            INNER JOIN $c c ON u.cliente_id = c.id
            GROUP BY c.id, c.nome, c.email
            ORDER BY checkins DESC
            LIMIT %d",
            $limit
        )
    );
}

/**
 * Passes ativos com visitas por usar (dias_restantes > 0).
 *
 * @param int $limit
 * @return array<int, object>
 */
function EventsHouse_checkins_pending_passes($limit = 40)
{
    global $wpdb;
    $p = $wpdb->prefix . 'ilha_passes';
    $c = $wpdb->prefix . 'ilha_clientes';

    $limit = max(1, min(100, (int) $limit));

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT p.id AS passe_id, p.tipo, p.dias_total, p.dias_usados,
                (p.dias_total - p.dias_usados) AS restantes,
                p.data_expiracao, c.nome, c.email, c.id AS cliente_id
            FROM $p p
            INNER JOIN $c c ON p.cliente_id = c.id
            WHERE p.status = 'ativo' AND p.dias_usados < p.dias_total
            ORDER BY restantes DESC, c.nome ASC
            LIMIT %d",
            $limit
        )
    );
}

/**
 * Clientes com passe ativo, visitas em falta, e sem check-in hoje (para ação na receção).
 *
 * @param int $limit
 * @return array<int, object>
 */
function EventsHouse_checkins_pending_today($limit = 25)
{
    global $wpdb;
    $p = $wpdb->prefix . 'ilha_passes';
    $c = $wpdb->prefix . 'ilha_clientes';
    $u = $wpdb->prefix . 'ilha_utilizacoes';

    $limit = max(1, min(80, (int) $limit));
    $hoje  = current_time('Y-m-d');

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT c.id AS cliente_id, c.nome, c.email, p.id AS passe_id, p.tipo,
                (p.dias_total - p.dias_usados) AS restantes
            FROM $p p
            INNER JOIN $c c ON p.cliente_id = c.id
            WHERE p.status = 'ativo'
            AND p.dias_usados < p.dias_total
            AND NOT EXISTS (
                SELECT 1 FROM $u ux
                WHERE ux.passe_id = p.id AND DATE(ux.data_utilizacao) = %s
            )
            ORDER BY c.nome ASC
            LIMIT %d",
            $hoje,
            $limit
        )
    );
}

/**
 * Últimos check-ins de hoje.
 *
 * @param int $limit
 * @return array<int, object>
 */
function EventsHouse_checkins_today_list($limit = 30)
{
    global $wpdb;
    $u = $wpdb->prefix . 'ilha_utilizacoes';
    $c = $wpdb->prefix . 'ilha_clientes';
    $p = $wpdb->prefix . 'ilha_passes';

    $limit = max(1, min(100, (int) $limit));
    $hoje  = current_time('Y-m-d');

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.data_utilizacao, c.nome, c.email, p.tipo
            FROM $u u
            INNER JOIN $c c ON u.cliente_id = c.id
            INNER JOIN $p p ON u.passe_id = p.id
            WHERE DATE(u.data_utilizacao) = %s
            ORDER BY u.data_utilizacao DESC
            LIMIT %d",
            $hoje,
            $limit
        )
    );
}

/**
 * @param array<string,int> $by_tipo
 * @param int               $max_for_bar
 */
function EventsHouse_checkins_render_tipo_bars($by_tipo, $max_for_bar)
{
    if (empty($by_tipo)) {
        echo '<p class="eh-dash-empty">' . esc_html__('Sem check-ins esta semana.', 'EventsHouse') . '</p>';

        return;
    }

    $max = max(1, (int) $max_for_bar);
    foreach ($by_tipo as $tipo => $count) {
        $label = function_exists('formatar_tipo_passe') ? formatar_tipo_passe($tipo) : $tipo;
        $pct   = min(100, round(100 * $count / $max));
        echo '<div class="eh-dash-bar-row">';
        echo '<span class="eh-dash-bar-label">' . esc_html($label) . '</span>';
        echo '<div class="eh-dash-bar-track"><div class="eh-dash-bar-fill" style="width:' . esc_attr((string) $pct) . '%"></div></div>';
        echo '<span class="eh-dash-bar-num">' . esc_html((string) (int) $count) . '</span>';
        echo '</div>';
    }
}

/**
 * Página do dashboard.
 */
function EventsHouse_render_checkins_dashboard_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sem permissão para ver esta página.', 'EventsHouse'));
    }

    $stats = EventsHouse_checkins_dashboard_stats();
    $top   = EventsHouse_checkins_top_clients(10);
    $pend  = EventsHouse_checkins_pending_passes(40);
    $pend_hoje = EventsHouse_checkins_pending_today(25);
    $hoje_list = EventsHouse_checkins_today_list(30);

    $week_max = 1;
    foreach ($stats->by_tipo as $n) {
        $week_max = max($week_max, $n);
    }

    $week_bounds = EventsHouse_checkins_week_bounds_mysql();
    $month_bounds = EventsHouse_checkins_month_bounds_mysql();
    ?>
    <div class="wrap eh-dash-wrap">
        <div class="eh-dash-header">
            <h1><?php esc_html_e('Check-ins & passes', 'EventsHouse'); ?></h1>
            <p><?php esc_html_e('Resumo operacional da A Ilha: utilizações registadas na receção.', 'EventsHouse'); ?></p>
            <p>
                <a class="button button-secondary" href="<?php echo esc_url(admin_url('admin.php?page=eventshouse-ilha-reservas')); ?>">
                    <?php esc_html_e('Ver reservas Manhãs na Ilha', 'EventsHouse'); ?>
                </a>
            </p>
        </div>

        <div class="eh-dash-kpis">
            <div class="eh-dash-kpi">
                <div class="eh-dash-kpi-label"><?php esc_html_e('Hoje', 'EventsHouse'); ?></div>
                <div class="eh-dash-kpi-value"><?php echo esc_html((string) $stats->today); ?></div>
                <div class="eh-dash-kpi-hint"><?php echo esc_html(wp_date('l j F Y', current_time('timestamp'))); ?></div>
            </div>
            <div class="eh-dash-kpi eh-dash-kpi--week">
                <div class="eh-dash-kpi-label"><?php esc_html_e('Esta semana', 'EventsHouse'); ?></div>
                <div class="eh-dash-kpi-value"><?php echo esc_html((string) $stats->week); ?></div>
                <div class="eh-dash-kpi-hint"><?php echo esc_html(sprintf(__('Desde %s', 'EventsHouse'), wp_date('j M', strtotime($week_bounds['start'])))); ?></div>
            </div>
            <div class="eh-dash-kpi eh-dash-kpi--month">
                <div class="eh-dash-kpi-label"><?php esc_html_e('Este mês', 'EventsHouse'); ?></div>
                <div class="eh-dash-kpi-value"><?php echo esc_html((string) $stats->month); ?></div>
                <div class="eh-dash-kpi-hint"><?php echo esc_html(wp_date('F Y', strtotime($month_bounds['start']))); ?></div>
            </div>
            <div class="eh-dash-kpi eh-dash-kpi--total">
                <div class="eh-dash-kpi-label"><?php esc_html_e('Total histórico', 'EventsHouse'); ?></div>
                <div class="eh-dash-kpi-value"><?php echo esc_html((string) $stats->total_all); ?></div>
                <div class="eh-dash-kpi-hint"><?php echo esc_html(sprintf(__('%d passes ativos', 'EventsHouse'), $stats->active_passes)); ?></div>
            </div>
        </div>

        <div class="eh-dash-grid-2">
            <div class="eh-dash-card">
                <div class="eh-dash-card-head">
                    <h2><?php esc_html_e('Por tipo de passe (esta semana)', 'EventsHouse'); ?></h2>
                </div>
                <?php EventsHouse_checkins_render_tipo_bars($stats->by_tipo, $week_max); ?>
            </div>
            <div class="eh-dash-card">
                <div class="eh-dash-card-head">
                    <h2><?php esc_html_e('Check-ins de hoje', 'EventsHouse'); ?></h2>
                    <span><?php echo esc_html((string) $stats->today); ?> <?php esc_html_e('registos', 'EventsHouse'); ?></span>
                </div>
                <?php if (empty($hoje_list)) : ?>
                    <p class="eh-dash-empty"><?php esc_html_e('Ainda não há check-ins hoje.', 'EventsHouse'); ?></p>
                <?php else : ?>
                    <div class="eh-dash-table-wrap">
                        <table class="eh-dash-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Hora', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Passe', 'EventsHouse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hoje_list as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html(wp_date('H:i', strtotime($row->data_utilizacao))); ?></td>
                                    <td><strong><?php echo esc_html($row->nome); ?></strong></td>
                                    <td><?php echo esc_html(function_exists('formatar_tipo_passe') ? formatar_tipo_passe($row->tipo) : $row->tipo); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="eh-dash-card" style="margin-bottom: 28px;">
            <div class="eh-dash-card-head">
                <h2><?php esc_html_e('Clientes com mais check-ins', 'EventsHouse'); ?></h2>
                <span><?php esc_html_e('Total acumulado', 'EventsHouse'); ?></span>
            </div>
            <?php if (empty($top)) : ?>
                <p class="eh-dash-empty"><?php esc_html_e('Ainda não há dados de check-in.', 'EventsHouse'); ?></p>
            <?php else : ?>
                <div class="eh-dash-table-wrap">
                    <table class="eh-dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Email', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Check-ins', 'EventsHouse'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($top as $row) :
                                ?>
                            <tr>
                                <td><?php echo esc_html((string) $i); ?></td>
                                <td><strong><?php echo esc_html($row->nome); ?></strong></td>
                                <td><?php echo esc_html($row->email ? $row->email : '—'); ?></td>
                                <td><span class="eh-dash-badge"><?php echo esc_html((string) (int) $row->checkins); ?></span></td>
                            </tr>
                                <?php
                                ++$i;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="eh-dash-grid-2">
            <div class="eh-dash-card">
                <div class="eh-dash-card-head">
                    <h2><?php esc_html_e('Visitas por utilizar (passes ativos)', 'EventsHouse'); ?></h2>
                    <span><?php esc_html_e('Quota pendente no passe', 'EventsHouse'); ?></span>
                </div>
                <?php if (empty($pend)) : ?>
                    <p class="eh-dash-empty"><?php esc_html_e('Nenhum passe ativo com visitas em falta.', 'EventsHouse'); ?></p>
                <?php else : ?>
                    <div class="eh-dash-table-wrap">
                        <table class="eh-dash-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Passe', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Usadas / Total', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Restantes', 'EventsHouse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pend as $row) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($row->nome); ?></strong></td>
                                    <td><?php echo esc_html(function_exists('formatar_tipo_passe') ? formatar_tipo_passe($row->tipo) : $row->tipo); ?></td>
                                    <td><?php echo esc_html((string) (int) $row->dias_usados . ' / ' . (int) $row->dias_total); ?></td>
                                    <td><span class="eh-dash-badge eh-dash-badge--warn"><?php echo esc_html((string) (int) $row->restantes); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="eh-dash-card">
                <div class="eh-dash-card-head">
                    <h2><?php esc_html_e('Sem check-in hoje (com visitas disponíveis)', 'EventsHouse'); ?></h2>
                    <span><?php esc_html_e('Útil para contacto / lembrete', 'EventsHouse'); ?></span>
                </div>
                <?php if (empty($pend_hoje)) : ?>
                    <p class="eh-dash-empty"><?php esc_html_e('Todos os passes ativos com quota já registaram entrada hoje, ou não há passes pendentes.', 'EventsHouse'); ?></p>
                <?php else : ?>
                    <div class="eh-dash-table-wrap">
                        <table class="eh-dash-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Passe', 'EventsHouse'); ?></th>
                                    <th><?php esc_html_e('Restantes', 'EventsHouse'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pend_hoje as $row) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($row->nome); ?></strong></td>
                                    <td><?php echo esc_html(function_exists('formatar_tipo_passe') ? formatar_tipo_passe($row->tipo) : $row->tipo); ?></td>
                                    <td><?php echo esc_html((string) (int) $row->restantes); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Menu principal (abaixo de Páginas — posição 21).
 */
function EventsHouse_register_checkins_dashboard_menu()
{
    add_menu_page(
        __('Check-ins A Ilha', 'EventsHouse'),
        __('Check-ins A Ilha', 'EventsHouse'),
        'manage_options',
        'eventshouse-checkins-dashboard',
        'EventsHouse_render_checkins_dashboard_page',
        'dashicons-chart-area',
        21
    );
}
add_action('admin_menu', 'EventsHouse_register_checkins_dashboard_menu');

/**
 * Estilos só neste ecrã.
 *
 * @param string $hook_suffix
 */
function EventsHouse_checkins_dashboard_assets($hook_suffix)
{
    if ($hook_suffix !== 'toplevel_page_eventshouse-checkins-dashboard') {
        return;
    }
    wp_enqueue_style(
        'eventshouse-checkins-dashboard',
        EventsHouse_URI . '/assets/css/admin-checkins-dashboard.css',
        array(),
        EventsHouse_VERSION
    );
}
add_action('admin_enqueue_scripts', 'EventsHouse_checkins_dashboard_assets');

/**
 * Remove Artigos e Comentários do menu lateral.
 */
function EventsHouse_admin_remove_posts_comments_menus()
{
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'EventsHouse_admin_remove_posts_comments_menus', 999);

/**
 * Remove atalho de comentários na barra de administração.
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function EventsHouse_admin_bar_remove_comments($wp_admin_bar)
{
    $wp_admin_bar->remove_node('comments');
}
add_action('admin_bar_menu', 'EventsHouse_admin_bar_remove_comments', 999);

/**
 * Desativa comentários no front-office e oculta UI no admin.
 */
function EventsHouse_disable_comments_globally()
{
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    add_filter('comments_array', '__return_empty_array', 10, 2);
}
add_action('init', 'EventsHouse_disable_comments_globally');

/**
 * Esconde caixas de comentários nos ecrãs de edição de conteúdos.
 */
function EventsHouse_admin_hide_comment_metaboxes()
{
    $types = get_post_types(array('public' => true), 'names');
    foreach ($types as $pt) {
        remove_meta_box('commentstatusdiv', $pt, 'normal');
        remove_meta_box('commentsdiv', $pt, 'normal');
    }
}
add_action('admin_init', 'EventsHouse_admin_hide_comment_metaboxes');
