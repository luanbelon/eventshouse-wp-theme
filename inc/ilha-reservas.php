<?php
/**
 * Reservas Manhãs na Ilha — persistência, API e painel admin.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nome da tabela de reservas.
 */
function ilha_reservas_table()
{
    global $wpdb;

    return $wpdb->prefix . 'ilha_reservas';
}

/**
 * Limite diário de reservas (crianças/vagas).
 */
function ilha_get_reservas_capacidade()
{
    $cap = (int) get_option('eventshouse_ilha_reservas_capacidade', 15);

    return max(1, min(100, $cap));
}

/**
 * Conta reservas activas num dia (confirmada ou presente).
 *
 * @param string $date       Y-m-d
 * @param int    $exclude_id ID a excluir (reagendamentos)
 */
function ilha_count_reservas_for_date($date, $exclude_id = 0)
{
    global $wpdb;
    $table = ilha_reservas_table();
    $exclude_id = absint($exclude_id);

    if ($exclude_id > 0) {
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE data_reserva = %s AND status IN ('confirmada','presente') AND id != %d",
            $date,
            $exclude_id
        ));
    }

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE data_reserva = %s AND status IN ('confirmada','presente')",
        $date
    ));
}

/**
 * @param string $date Y-m-d
 */
function ilha_has_capacity_for_date($date)
{
    return ilha_count_reservas_for_date($date) < ilha_get_reservas_capacidade();
}

/**
 * @param int    $cliente_id
 * @param string $date Y-m-d
 */
function ilha_cliente_has_reserva_on_date($cliente_id, $date)
{
    global $wpdb;
    $cliente_id = absint($cliente_id);
    if (!$cliente_id || $date === '') {
        return false;
    }

    $table = ilha_reservas_table();

    return (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE cliente_id = %d AND data_reserva = %s AND status IN ('confirmada','presente') LIMIT 1",
        $cliente_id,
        $date
    ));
}

/**
 * Reserva confirmada do cliente num dia (para aviso na receção).
 *
 * @param int    $cliente_id
 * @param string $date       Y-m-d
 * @return object|null
 */
function ilha_get_cliente_reserva_on_date($cliente_id, $date)
{
    global $wpdb;
    $cliente_id = absint($cliente_id);
    if (!$cliente_id || $date === '') {
        return null;
    }

    $table = ilha_reservas_table();

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE cliente_id = %d AND data_reserva = %s AND status IN ('confirmada','presente') ORDER BY id DESC LIMIT 1",
        $cliente_id,
        $date
    ));
}

/**
 * Insere reserva após validação externa.
 *
 * @param array<string,mixed> $data
 * @return int|false ID inserido ou false
 */
function ilha_insert_reserva(array $data)
{
    global $wpdb;
    $table = ilha_reservas_table();

    $status = sanitize_key($data['status'] ?? 'confirmada');
    if (!in_array($status, array('confirmada', 'presente', 'cancelada'), true)) {
        $status = 'confirmada';
    }

    $row = array(
        'data_reserva'  => sanitize_text_field($data['data_reserva'] ?? ''),
        'event_title'   => sanitize_text_field($data['event_title'] ?? ''),
        'event_time'    => sanitize_text_field($data['event_time'] ?? ''),
        'nome'          => sanitize_text_field($data['nome'] ?? ''),
        'email'         => sanitize_email($data['email'] ?? ''),
        'telefone'      => sanitize_text_field($data['telefone'] ?? ''),
        'status'        => $status,
        'criada_em'     => !empty($data['criada_em']) ? sanitize_text_field($data['criada_em']) : current_time('mysql'),
    );

    if (!empty($data['cliente_id'])) {
        $row['cliente_id'] = absint($data['cliente_id']);
    }
    if (!empty($data['passe_id'])) {
        $row['passe_id'] = absint($data['passe_id']);
    }
    if (isset($data['registado_por']) && $data['registado_por']) {
        $row['registado_por'] = absint($data['registado_por']);
    }
    if (!empty($data['gcal_event_id'])) {
        $row['gcal_event_id'] = sanitize_text_field($data['gcal_event_id']);
    }
    if (isset($data['idade_crianca']) && $data['idade_crianca'] !== '' && $data['idade_crianca'] !== null) {
        $row['idade_crianca'] = max(1, min(18, absint($data['idade_crianca'])));
    }

    if ($row['data_reserva'] === '' || $row['nome'] === '') {
        return false;
    }

    $ok = $wpdb->insert($table, $row);
    if ($ok === false) {
        return false;
    }

    $insert_id = (int) $wpdb->insert_id;
    if ($insert_id && $status !== 'cancelada') {
        do_action('ilha_reserva_created', $insert_id);
    }

    return $insert_id;
}

/**
 * Enriquece objeto de reserva para listagens.
 *
 * @param object $r
 * @return object
 */
function ilha_enrich_reserva_row($r)
{
    $r->tipo_formatado = !empty($r->passe_tipo) && function_exists('formatar_tipo_passe')
        ? formatar_tipo_passe($r->passe_tipo)
        : '';
    $r->data_hora_criada = !empty($r->criada_em) ? wp_date('d/m/Y H:i', strtotime($r->criada_em)) : '';
    if (!empty($r->data_reserva)) {
        $r->data_formatada = wp_date('d/m/Y', strtotime($r->data_reserva . ' 12:00:00'));
    }
    $r->status_label = ilha_reserva_status_label($r->status);

    if (function_exists('ilha_pass_extras_payload')) {
        $extras_src = (object) array(
            'extra_adultos_total'   => $r->extra_adultos_total ?? 0,
            'extra_criancas_total'  => $r->extra_criancas_total ?? 0,
            'extra_adultos_usados'  => $r->extra_adultos_usados ?? 0,
            'extra_criancas_usados' => $r->extra_criancas_usados ?? 0,
        );
        $r->extras = ilha_pass_extras_payload($extras_src);
        $r->extras_label = !empty($r->extras['tem_extras']) ? $r->extras['label_resumo'] : '';
    } else {
        $r->extras       = null;
        $r->extras_label = '';
    }

    return $r;
}

/**
 * Reservas num intervalo de datas (inclusive).
 *
 * @param string $start_date Y-m-d
 * @param string $end_date   Y-m-d
 * @return array<int, object>
 */
function ilha_get_reservas_between_dates($start_date, $end_date)
{
    global $wpdb;
    $table  = ilha_reservas_table();
    $passes = $wpdb->prefix . 'ilha_passes';

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT r.*, p.tipo AS passe_tipo,
            p.extra_adultos_total, p.extra_criancas_total,
            p.extra_adultos_usados, p.extra_criancas_usados
        FROM $table r
        LEFT JOIN $passes p ON r.passe_id = p.id
        WHERE r.data_reserva >= %s AND r.data_reserva <= %s AND r.status != 'cancelada'
        ORDER BY r.data_reserva ASC, r.event_time ASC, r.criada_em ASC",
        $start_date,
        $end_date
    ));

    if (!is_array($rows)) {
        return array();
    }

    foreach ($rows as $r) {
        ilha_enrich_reserva_row($r);
    }

    return $rows;
}

/**
 * Lista reservas de um dia com dados do passe.
 *
 * @param string $date Y-m-d
 * @return array<int, object>
 */
function ilha_get_reservas_for_date($date)
{
    return ilha_get_reservas_between_dates($date, $date);
}

/**
 * @param string $status
 */
function ilha_reserva_status_label($status)
{
    $map = array(
        'confirmada' => __('Confirmada', 'EventsHouse'),
        'presente'   => __('Presente', 'EventsHouse'),
        'cancelada'  => __('Cancelada', 'EventsHouse'),
    );

    return $map[$status] ?? $status;
}

/**
 * Marca reserva como presente após check-in.
 *
 * @param int    $cliente_id
 * @param string $date Y-m-d
 */
function ilha_mark_reserva_presente_for_checkin($cliente_id, $date = null)
{
    global $wpdb;
    $cliente_id = absint($cliente_id);
    if (!$cliente_id) {
        return;
    }
    if ($date === null || $date === '') {
        $date = current_time('Y-m-d');
    }

    $wpdb->update(
        ilha_reservas_table(),
        array('status' => 'presente'),
        array(
            'cliente_id'   => $cliente_id,
            'data_reserva' => $date,
            'status'       => 'confirmada',
        ),
        array('%s'),
        array('%d', '%s', '%s')
    );
}

/**
 * Cancela reserva por ID.
 *
 * @param int $reserva_id
 * @return bool
 */
function ilha_cancel_reserva($reserva_id)
{
    global $wpdb;
    $reserva_id = absint($reserva_id);
    if (!$reserva_id) {
        return false;
    }

    $updated = $wpdb->update(
        ilha_reservas_table(),
        array('status' => 'cancelada'),
        array('id' => $reserva_id),
        array('%s'),
        array('%d')
    );

    if ($updated !== false) {
        do_action('ilha_reserva_cancelled', $reserva_id);
    }

    return $updated !== false;
}

/**
 * Utilizador pode gerir reservas (receção ou admin).
 */
function ilha_user_can_manage_reservas()
{
    return ilha_user_can_access_rececao();
}

/**
 * Payload de um dia a partir da lista de reservas.
 *
 * @param string             $date     Y-m-d
 * @param array<int, object> $reservas
 * @return array<string, mixed>
 */
function ilha_reservas_build_day_payload($date, array $reservas)
{
    $cap      = ilha_get_reservas_capacidade();
    $ocupadas = count($reservas);
    $hoje     = current_time('Y-m-d');

    return array(
        'data'            => $date,
        'data_formatada'  => wp_date('l, j \d\e F \d\e Y', strtotime($date . ' 12:00:00')),
        'data_curta'      => wp_date('d/m/Y', strtotime($date . ' 12:00:00')),
        'capacidade'      => $cap,
        'ocupadas'        => $ocupadas,
        'disponiveis'     => max(0, $cap - $ocupadas),
        'reservas'        => $reservas,
        'is_today'        => ($date === $hoje),
        'cheio'           => $ocupadas >= $cap,
    );
}

/**
 * Payload JSON para listagem por data.
 *
 * @param string $date Y-m-d
 */
function ilha_reservas_day_payload($date)
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = current_time('Y-m-d');
    }

    return ilha_reservas_build_day_payload($date, ilha_get_reservas_for_date($date));
}

/**
 * Próximos N dias (inclui hoje), agrupados por bloco/dia.
 *
 * @param int $days Número de dias (máx. 31).
 * @return array<string, mixed>
 */
function ilha_reservas_upcoming_days_payload($days = 15)
{
    $days = max(1, min(31, absint($days)));
    $tz   = wp_timezone();
    $hoje = new DateTimeImmutable(current_time('Y-m-d'), $tz);
    $fim  = $hoje->modify('+' . ($days - 1) . ' days');

    $start_str = $hoje->format('Y-m-d');
    $end_str   = $fim->format('Y-m-d');

    $all = ilha_get_reservas_between_dates($start_str, $end_str);
    $grouped = array();
    foreach ($all as $r) {
        $d = $r->data_reserva;
        if (!isset($grouped[$d])) {
            $grouped[$d] = array();
        }
        $grouped[$d][] = $r;
    }

    $day_payloads   = array();
    $total_reservas = 0;
    $dias_cheios    = 0;

    for ($i = 0; $i < $days; $i++) {
        $date_str = $hoje->modify('+' . $i . ' days')->format('Y-m-d');
        $list     = isset($grouped[$date_str]) ? $grouped[$date_str] : array();
        $payload  = ilha_reservas_build_day_payload($date_str, $list);
        $total_reservas += $payload['ocupadas'];
        if ($payload['cheio']) {
            $dias_cheios++;
        }
        $day_payloads[] = $payload;
    }

    return array(
        'days'             => $day_payloads,
        'total_reservas'   => $total_reservas,
        'dias_cheios'      => $dias_cheios,
        'num_dias'         => $days,
        'inicio'           => $start_str,
        'fim'              => $end_str,
        'periodo_label'    => sprintf(
            /* translators: 1: start date, 2: end date */
            __('De %1$s a %2$s', 'EventsHouse'),
            wp_date('j M Y', strtotime($start_str . ' 12:00:00')),
            wp_date('j M Y', strtotime($end_str . ' 12:00:00'))
        ),
        'capacidade'       => ilha_get_reservas_capacidade(),
    );
}

/**
 * AJAX: listar reservas por data.
 */
function ajax_ilha_get_reservas()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_manage_reservas()) {
        wp_send_json_error(__('Acesso negado.', 'EventsHouse'));
    }

    $date = sanitize_text_field($_POST['date'] ?? '');
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = current_time('Y-m-d');
    }

    wp_send_json_success(ilha_reservas_day_payload($date));
}
add_action('wp_ajax_ilha_get_reservas', 'ajax_ilha_get_reservas');

/**
 * AJAX: cancelar reserva.
 */
function ajax_ilha_cancel_reserva()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!ilha_user_can_manage_reservas()) {
        wp_send_json_error(__('Acesso negado.', 'EventsHouse'));
    }

    $id = absint($_POST['reserva_id'] ?? 0);
    if (!$id) {
        wp_send_json_error(__('Reserva inválida.', 'EventsHouse'));
    }

    if (!ilha_cancel_reserva($id)) {
        wp_send_json_error(__('Não foi possível cancelar.', 'EventsHouse'));
    }

    $date = sanitize_text_field($_POST['date'] ?? current_time('Y-m-d'));
    wp_send_json_success(array(
        'message' => __('Reserva cancelada.', 'EventsHouse'),
        'day'     => ilha_reservas_day_payload($date),
    ));
}
add_action('wp_ajax_ilha_cancel_reserva', 'ajax_ilha_cancel_reserva');

/**
 * Guardar capacidade (admin).
 */
function EventsHouse_save_ilha_reservas_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    if (!isset($_POST['eventshouse_ilha_reservas_capacidade'])) {
        return;
    }
    check_admin_referer('eventshouse_ilha_reservas_settings');

    $cap = absint($_POST['eventshouse_ilha_reservas_capacidade'] ?? 15);
    update_option('eventshouse_ilha_reservas_capacidade', max(1, min(100, $cap)));

    $vista = isset($_POST['vista']) ? sanitize_key(wp_unslash($_POST['vista'])) : 'proximos';
    if ($vista !== 'dia') {
        $vista = 'proximos';
    }

    wp_safe_redirect(add_query_arg(
        array(
            'page'              => 'eventshouse-ilha-reservas',
            'vista'             => $vista,
            'settings-updated'  => '1',
        ),
        admin_url('admin.php')
    ));
    exit;
}

/**
 * Página admin — Reservas Manhãs.
 */
function EventsHouse_render_ilha_reservas_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sem permissão para ver esta página.', 'EventsHouse'));
    }

    $settings_saved = isset($_GET['settings-updated']) && $_GET['settings-updated'] === '1';
    if (isset($_POST['eventshouse_ilha_reservas_capacidade'])) {
        EventsHouse_save_ilha_reservas_settings();
    }

    $cap = ilha_get_reservas_capacidade();
    $vista = isset($_GET['vista']) ? sanitize_key(wp_unslash($_GET['vista'])) : 'proximos';
    if ($vista !== 'dia' && $vista !== 'proximos') {
        $vista = 'proximos';
    }

    $date = isset($_GET['data']) ? sanitize_text_field(wp_unslash($_GET['data'])) : '';
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = current_time('Y-m-d');
    }

    $payload         = ilha_reservas_day_payload($date);
    $upcoming        = ($vista === 'proximos') ? ilha_reservas_upcoming_days_payload(15) : null;
    $admin_page_base = admin_url('admin.php?page=eventshouse-ilha-reservas');
    $rececao_url = '';
    $rececao_page = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'page-rececao.php',
        'number'     => 1,
    ));
    if (!empty($rececao_page[0])) {
        $rececao_url = get_permalink($rececao_page[0]);
    }

    $ajax_url = admin_url('admin-ajax.php');
    $nonce    = wp_create_nonce('EventsHouse_nonce');
    ?>
    <div class="wrap eh-dash-wrap eh-reservas-wrap" id="eh-reservas-admin"
         data-ajax-url="<?php echo esc_url($ajax_url); ?>"
         data-nonce="<?php echo esc_attr($nonce); ?>"
         data-date="<?php echo esc_attr($date); ?>"
         data-vista="<?php echo esc_attr($vista); ?>">
        <div class="eh-dash-header">
            <h1><?php esc_html_e('Reservas — Manhãs na Ilha', 'EventsHouse'); ?></h1>
            <p><?php esc_html_e('Lista de reservas por dia, ocupação e gestão de cancelamentos.', 'EventsHouse'); ?></p>
            <?php if ($rececao_url) : ?>
                <p><a class="button button-secondary" href="<?php echo esc_url($rececao_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Abrir painel da receção', 'EventsHouse'); ?></a></p>
            <?php endif; ?>
        </div>

        <?php if ($settings_saved) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Definições guardadas.', 'EventsHouse'); ?></p></div>
        <?php endif; ?>

        <nav class="nav-tab-wrapper eh-reservas-tabs" aria-label="<?php esc_attr_e('Vista das reservas', 'EventsHouse'); ?>">
            <a href="<?php echo esc_url(add_query_arg(array('vista' => 'proximos'), $admin_page_base)); ?>"
               class="nav-tab<?php echo $vista === 'proximos' ? ' nav-tab-active' : ''; ?>">
                <?php esc_html_e('Próximos 15 dias', 'EventsHouse'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg(array('vista' => 'dia', 'data' => $date), $admin_page_base)); ?>"
               class="nav-tab<?php echo $vista === 'dia' ? ' nav-tab-active' : ''; ?>">
                <?php esc_html_e('Um dia', 'EventsHouse'); ?>
            </a>
        </nav>

        <div class="eh-reservas-toolbar">
            <?php if ($vista === 'dia') : ?>
                <form method="get" action="" class="eh-reservas-date-form">
                    <input type="hidden" name="page" value="eventshouse-ilha-reservas" />
                    <input type="hidden" name="vista" value="dia" />
                    <label for="eh-reservas-date"><?php esc_html_e('Data', 'EventsHouse'); ?></label>
                    <input type="date" id="eh-reservas-date" name="data" value="<?php echo esc_attr($date); ?>" />
                    <button type="submit" class="button button-primary"><?php esc_html_e('Ver dia', 'EventsHouse'); ?></button>
                </form>
            <?php else : ?>
                <p class="eh-reservas-period-hint">
                    <?php
                    echo esc_html(
                        sprintf(
                            __('A mostrar reservas de %s (hoje + 14 dias seguintes).', 'EventsHouse'),
                            $upcoming ? $upcoming['periodo_label'] : ''
                        )
                    );
                    ?>
                </p>
            <?php endif; ?>

            <form method="post" action="" class="eh-reservas-cap-form">
                <?php wp_nonce_field('eventshouse_ilha_reservas_settings'); ?>
                <input type="hidden" name="vista" value="<?php echo esc_attr($vista); ?>" />
                <label for="eventshouse_ilha_reservas_capacidade"><?php esc_html_e('Vagas por dia', 'EventsHouse'); ?></label>
                <input type="number" min="1" max="100" name="eventshouse_ilha_reservas_capacidade" id="eventshouse_ilha_reservas_capacidade" value="<?php echo esc_attr((string) $cap); ?>" />
                <button type="submit" class="button"><?php esc_html_e('Guardar limite', 'EventsHouse'); ?></button>
            </form>

            <?php
            $gcal_ok = !empty(get_option('eventshouse_gcal_api_key', '')) && !empty(get_option('eventshouse_gcal_ilha_calendar_id', ''));
            ?>
            <div class="eh-reservas-sync-box">
                <button type="button" class="button button-secondary" id="eh-sync-gcal-btn" <?php disabled(!$gcal_ok); ?>>
                    <?php esc_html_e('Sincronizar com Google Calendar', 'EventsHouse'); ?>
                </button>
                <?php if (!$gcal_ok) : ?>
                    <p class="description"><?php esc_html_e('Configure a API Key e o calendário Manhãs na Ilha em Conteúdos do Site → Google Agenda.', 'EventsHouse'); ?></p>
                <?php else : ?>
                    <p class="description"><?php esc_html_e('Importa eventos do calendário que tenham email/nome de cliente (últimos 60 dias e próximos 90). Slots genéricos «Manhãs na Ilha» são ignorados.', 'EventsHouse'); ?></p>
                <?php endif; ?>
                <div id="eh-sync-gcal-result" class="eh-sync-gcal-result" style="display:none;" role="status"></div>
            </div>
        </div>

        <?php if ($vista === 'proximos' && $upcoming) : ?>
            <?php EventsHouse_render_ilha_reservas_upcoming_overview($upcoming); ?>
            <?php EventsHouse_render_ilha_reservas_upcoming_blocks($upcoming, $admin_page_base); ?>
        <?php else : ?>
            <?php EventsHouse_render_ilha_reservas_summary($payload); ?>
            <?php EventsHouse_render_ilha_reservas_day_panel($payload, $date); ?>
        <?php endif; ?>
    </div>
    <?php
}


/**
 * Painel de um único dia (vista admin).
 *
 * @param array<string,mixed> $payload
 * @param string              $date Y-m-d
 */
function EventsHouse_render_ilha_reservas_day_panel($payload, $date)
{
    ?>
    <div class="eh-dash-panel">
        <h2><?php echo esc_html($payload['data_formatada']); ?></h2>
        <div class="eh-reservas-table-wrap">
            <table class="widefat striped eh-reservas-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Horário', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Idade', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Contacto', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Pack', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Estado', 'EventsHouse'); ?></th>
                        <th><?php esc_html_e('Reservado em', 'EventsHouse'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="eh-reservas-tbody">
                    <?php EventsHouse_render_ilha_reservas_table_rows($payload['reservas'], $date); ?>
                </tbody>
            </table>
        </div>
    
    </div>
    <?php
}

/**
 * Resumo global dos próximos 15 dias.
 *
 * @param array<string,mixed> $upcoming
 */
function EventsHouse_render_ilha_reservas_upcoming_overview($upcoming)
{
    $cap = (int) ($upcoming['capacidade'] ?? 15);
    ?>
    <div class="eh-dash-kpis eh-reservas-kpis eh-reservas-kpis--period">
        <div class="eh-dash-kpi">
            <div class="eh-dash-kpi-label"><?php esc_html_e('Total de reservas (15 dias)', 'EventsHouse'); ?></div>
            <div class="eh-dash-kpi-value"><?php echo esc_html((string) (int) $upcoming['total_reservas']); ?></div>
            <div class="eh-dash-kpi-hint"><?php echo esc_html($upcoming['periodo_label']); ?></div>
        </div>
        <div class="eh-dash-kpi eh-dash-kpi--week">
            <div class="eh-dash-kpi-label"><?php esc_html_e('Dias com lotação cheia', 'EventsHouse'); ?></div>
            <div class="eh-dash-kpi-value"><?php echo esc_html((string) (int) $upcoming['dias_cheios']); ?></div>
            <div class="eh-dash-kpi-hint"><?php echo esc_html(sprintf(__('Limite: %d por dia', 'EventsHouse'), $cap)); ?></div>
        </div>
        <div class="eh-dash-kpi">
            <div class="eh-dash-kpi-label"><?php esc_html_e('Dias no período', 'EventsHouse'); ?></div>
            <div class="eh-dash-kpi-value"><?php echo esc_html((string) (int) $upcoming['num_dias']); ?></div>
        </div>
    </div>
    <?php
}

/**
 * Blocos por dia — próximos 15 dias.
 *
 * @param array<string,mixed> $upcoming
 * @param string              $admin_page_base
 */
function EventsHouse_render_ilha_reservas_upcoming_blocks($upcoming, $admin_page_base)
{
    if (empty($upcoming['days']) || !is_array($upcoming['days'])) {
        echo '<p class="eh-dash-empty">' . esc_html__('Sem dados para o período.', 'EventsHouse') . '</p>';
        return;
    }
    ?>
    <div class="eh-reservas-days-grid">
        <?php foreach ($upcoming['days'] as $day) : ?>
            <?php
            $date_str = $day['data'];
            $panel_id = 'eh-day-' . str_replace('-', '', $date_str);
            $detail_url = add_query_arg(
                array('vista' => 'dia', 'data' => $date_str),
                $admin_page_base
            );
            $panel_class = 'eh-reservas-day-block';
            if (!empty($day['is_today'])) {
                $panel_class .= ' eh-reservas-day-block--today';
            }
            if (!empty($day['cheio'])) {
                $panel_class .= ' eh-reservas-day-block--full';
            }
            if (empty($day['reservas'])) {
                $panel_class .= ' eh-reservas-day-block--empty';
            }
            ?>
            <section class="<?php echo esc_attr($panel_class); ?>" id="<?php echo esc_attr($panel_id); ?>">
                <header class="eh-reservas-day-head">
                    
                    <div class="eh-reservas-day-title-wrap">
                        <h3 class="eh-reservas-day-title"><?php echo esc_html($day['data_formatada']); ?></h3>
                        <?php if (!empty($day['is_today'])) : ?>
                            <span class="eh-reservas-day-badge"><?php esc_html_e('Hoje', 'EventsHouse'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="eh-reservas-day-meta">
                        <span class="eh-reservas-day-count<?php echo !empty($day['cheio']) ? ' is-full' : ''; ?>">
                            <strong><?php echo esc_html((string) (int) $day['ocupadas']); ?></strong>
                            / <?php echo esc_html((string) (int) $day['capacidade']); ?>
                        </span>
                        <span class="eh-reservas-day-disp">
                            <?php
                            echo esc_html(
                                sprintf(
                                    _n('%d vaga livre', '%d vagas livres', (int) $day['disponiveis'], 'EventsHouse'),
                                    (int) $day['disponiveis']
                                )
                            );
                            ?>
                        </span>
                        <a class="button button-small" href="<?php echo esc_url($detail_url); ?>"><?php esc_html_e('Ver só este dia', 'EventsHouse'); ?></a>
                    </div>
                </header>
                <div class="eh-reservas-table-wrap">
                    <table class="widefat striped eh-reservas-table eh-reservas-table--compact">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Horário', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Cliente', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Idade', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Contacto', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Pack', 'EventsHouse'); ?></th>
                                <th><?php esc_html_e('Estado', 'EventsHouse'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php EventsHouse_render_ilha_reservas_table_rows($day['reservas'], $date_str); ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
    <?php
}

/**
 * Resumo de vagas (HTML partilhado admin).
 *
 * @param array<string,mixed> $payload
 */
function EventsHouse_render_ilha_reservas_summary($payload)
{
    $cap       = (int) ($payload['capacidade'] ?? 15);
    $ocupadas  = (int) ($payload['ocupadas'] ?? 0);
    $disp      = (int) ($payload['disponiveis'] ?? 0);
    $pct       = $cap > 0 ? min(100, round(100 * $ocupadas / $cap)) : 0;
    $full      = $ocupadas >= $cap;
    ?>
    <div class="eh-dash-kpis eh-reservas-kpis">
        <div class="eh-dash-kpi<?php echo $full ? ' eh-dash-kpi--warn' : ''; ?>">
            <div class="eh-dash-kpi-label"><?php esc_html_e('Reservas neste dia', 'EventsHouse'); ?></div>
            <div class="eh-dash-kpi-value"><?php echo esc_html((string) $ocupadas); ?> <span class="eh-dash-kpi-of">/ <?php echo esc_html((string) $cap); ?></span></div>
            <div class="eh-dash-bar-track eh-reservas-bar"><div class="eh-dash-bar-fill" style="width:<?php echo esc_attr((string) $pct); ?>%"></div></div>
        </div>
        <div class="eh-dash-kpi">
            <div class="eh-dash-kpi-label"><?php esc_html_e('Vagas disponíveis', 'EventsHouse'); ?></div>
            <div class="eh-dash-kpi-value"><?php echo esc_html((string) $disp); ?></div>
        </div>
    </div>
    <?php
}

/**
 * Linhas da tabela de reservas.
 *
 * @param array<int, object> $reservas
 * @param string             $date Y-m-d
 */
function EventsHouse_render_ilha_reservas_table_rows($reservas, $date)
{
    if (empty($reservas)) {
        echo '<tr><td colspan="8" class="eh-dash-empty">' . esc_html__('Nenhuma reserva neste dia.', 'EventsHouse') . '</td></tr>';

        return;
    }

    foreach ($reservas as $r) {
        $status_class = 'eh-res-status--' . esc_attr($r->status);
        echo '<tr data-reserva-id="' . esc_attr((string) (int) $r->id) . '">';
        echo '<td>' . esc_html($r->event_time ?: '—') . '</td>';
        echo '<td><strong>' . esc_html($r->nome) . '</strong></td>';
        echo '<td>' . esc_html(!empty($r->idade_crianca) ? (string) (int) $r->idade_crianca : '—') . '</td>';
        echo '<td>';
        if (!empty($r->email)) {
            echo '<a href="mailto:' . esc_attr($r->email) . '">' . esc_html($r->email) . '</a><br>';
        }
        echo esc_html($r->telefone ?: '—');
        echo '</td>';
        echo '<td>' . esc_html($r->tipo_formatado ?: '—') . '</td>';
        echo '<td><span class="eh-res-status ' . esc_attr($status_class) . '">' . esc_html($r->status_label) . '</span></td>';
        echo '<td>' . esc_html($r->data_hora_criada) . '</td>';
        echo '<td>';
        if ($r->status === 'confirmada') {
            echo '<button type="button" class="button button-link-delete eh-cancel-reserva" data-id="' . esc_attr((string) (int) $r->id) . '" data-date="' . esc_attr($date) . '">' . esc_html__('Cancelar', 'EventsHouse') . '</button>';
        } else {
            echo '—';
        }
        echo '</td>';
        echo '</tr>';
    }
}

/**
 * Menu wp-admin.
 */
function EventsHouse_register_ilha_reservas_menu()
{
    add_menu_page(
        __('Reservas Manhãs', 'EventsHouse'),
        __('Reservas Manhãs', 'EventsHouse'),
        'manage_options',
        'eventshouse-ilha-reservas',
        'EventsHouse_render_ilha_reservas_admin_page',
        'dashicons-calendar-alt',
        22
    );
}
add_action('admin_menu', 'EventsHouse_register_ilha_reservas_menu');

/**
 * CSS e JS do ecrã admin.
 *
 * @param string $hook_suffix
 */
function EventsHouse_ilha_reservas_admin_assets($hook_suffix)
{
    if ($hook_suffix !== 'toplevel_page_eventshouse-ilha-reservas') {
        return;
    }
    wp_enqueue_style(
        'eventshouse-checkins-dashboard',
        EventsHouse_URI . '/assets/css/admin-checkins-dashboard.css',
        array(),
        EventsHouse_VERSION
    );
    wp_enqueue_style(
        'eventshouse-ilha-reservas-admin',
        EventsHouse_URI . '/assets/css/admin-ilha-reservas.css',
        array('eventshouse-checkins-dashboard'),
        EventsHouse_VERSION
    );
    wp_enqueue_script(
        'eventshouse-ilha-reservas-admin',
        EventsHouse_URI . '/assets/js/admin-ilha-reservas.js',
        array(),
        EventsHouse_VERSION,
        true
    );
    wp_localize_script('eventshouse-ilha-reservas-admin', 'ehIlhaReservasAdmin', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('EventsHouse_nonce'),
        'i18n'     => array(
            'confirm_cancel' => __('Cancelar esta reserva?', 'EventsHouse'),
            'error'          => __('Não foi possível cancelar.', 'EventsHouse'),
            'sync_running'   => __('A sincronizar com o Google Calendar…', 'EventsHouse'),
            'sync_done'      => __('Sincronização concluída.', 'EventsHouse'),
            'sync_fail'      => __('Não foi possível sincronizar.', 'EventsHouse'),
        ),
    ));
}
add_action('admin_enqueue_scripts', 'EventsHouse_ilha_reservas_admin_assets');
