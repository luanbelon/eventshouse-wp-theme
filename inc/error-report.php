<?php
/**
 * Registo de erros do tema — painel em Ferramentas → Relatório de erros.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Nome da tabela de logs.
 */
function EventsHouse_error_log_table()
{
    global $wpdb;

    return $wpdb->prefix . 'eventshouse_error_log';
}

/**
 * Cria/atualiza a tabela de logs.
 */
function EventsHouse_error_log_install_table($force = false)
{
    if (!$force && get_option('eventshouse_error_log_installed_v2') === '1') {
        return;
    }

    global $wpdb;

    $table = EventsHouse_error_log_table();
    $charset_collate = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        level varchar(20) NOT NULL DEFAULT 'error',
        context varchar(100) NOT NULL DEFAULT '',
        message text NOT NULL,
        details longtext DEFAULT NULL,
        user_email varchar(255) DEFAULT NULL,
        request_uri varchar(500) DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY context (context),
        KEY level (level),
        KEY created_at (created_at)
    ) $charset_collate;";

    dbDelta($sql);

    update_option('eventshouse_error_log_installed_v2', '1', true);
}
add_action('after_switch_theme', 'EventsHouse_error_log_install_table');
add_action('admin_init', 'EventsHouse_error_log_install_table');

/**
 * Regista um erro ou aviso.
 *
 * @param string               $context Contexto (ex.: ilha_reservation).
 * @param string               $message Mensagem legível.
 * @param array<string,mixed>  $details Dados extra (será JSON).
 * @param string               $level   error|warning|info.
 */
function EventsHouse_log_error($context, $message, array $details = array(), $level = 'error')
{
    global $wpdb;

    $level = in_array($level, array('error', 'warning', 'info'), true) ? $level : 'error';
    $context = sanitize_key($context);
    $message = sanitize_text_field($message);

    if ($message === '') {
        return;
    }

    EventsHouse_error_log_install_table(false);

    $table = EventsHouse_error_log_table();

    $wpdb->insert(
        $table,
        array(
            'level'        => $level,
            'context'      => substr($context, 0, 100),
            'message'      => $message,
            'details'      => $details ? wp_json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            'user_email'   => isset($details['email']) ? sanitize_email((string) $details['email']) : null,
            'request_uri'  => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : null,
            'ip_address'   => EventsHouse_error_log_client_ip(),
            'created_at'   => current_time('mysql'),
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
    );

    // Mantém no máximo ~1000 entradas (executa apenas esporadicamente, 1 em 50 chamadas, para evitar overhead de I/O)
    if (mt_rand(1, 50) === 1) {
        $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 1000) {
            $wpdb->query("DELETE FROM $table ORDER BY id ASC LIMIT " . (int) ($count - 1000));
        }
    }
}

/**
 * @return string
 */
function EventsHouse_error_log_client_ip()
{
    $ip = '';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $parts = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($parts[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = (string) $_SERVER['REMOTE_ADDR'];
    }

    return sanitize_text_field($ip);
}

/**
 * Última mensagem PHPMailer após wp_mail falhar.
 *
 * @return string
 */
function EventsHouse_last_mail_error_message()
{
    global $phpmailer;

    if (isset($phpmailer) && is_object($phpmailer) && !empty($phpmailer->ErrorInfo)) {
        return (string) $phpmailer->ErrorInfo;
    }

    return '';
}

/**
 * Lista entradas do log.
 *
 * @param array<string,mixed> $args Filtros.
 * @return array<int, object>
 */
function EventsHouse_get_error_logs(array $args = array())
{
    global $wpdb;

    $defaults = array(
        'limit'   => 100,
        'offset'  => 0,
        'context' => '',
        'level'   => '',
    );
    $args  = wp_parse_args($args, $defaults);
    $table = EventsHouse_error_log_table();

    $where  = array('1=1');
    $params = array();

    if ($args['context'] !== '') {
        $where[]  = 'context = %s';
        $params[] = sanitize_key($args['context']);
    }
    if ($args['level'] !== '') {
        $where[]  = 'level = %s';
        $params[] = sanitize_key($args['level']);
    }

    $limit  = max(1, min(500, absint($args['limit'])));
    $offset = max(0, absint($args['offset']));

    $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) .
        " ORDER BY id DESC LIMIT %d OFFSET %d";

    $params[] = $limit;
    $params[] = $offset;

    $prepared = $wpdb->prepare($sql, $params);
    $rows     = $wpdb->get_results($prepared);

    return is_array($rows) ? $rows : array();
}

/**
 * Apaga todos os logs.
 */
function EventsHouse_clear_error_logs()
{
    global $wpdb;

    $wpdb->query('TRUNCATE TABLE ' . EventsHouse_error_log_table());
}

/**
 * Captura falhas globais de wp_mail.
 *
 * @param WP_Error $error Erro do wp_mail.
 */
function EventsHouse_on_wp_mail_failed($error)
{
    if (!$error instanceof WP_Error) {
        return;
    }

    EventsHouse_log_error(
        'wp_mail',
        $error->get_error_message(),
        array(
            'code' => $error->get_error_code(),
            'data' => $error->get_error_data(),
        ),
        'error'
    );
}
add_action('wp_mail_failed', 'EventsHouse_on_wp_mail_failed');

/**
 * Menu Ferramentas → Relatório de erros.
 */
function EventsHouse_register_error_report_menu()
{
    add_management_page(
        __('Relatório de erros', 'EventsHouse'),
        __('Relatório de erros', 'EventsHouse'),
        'manage_options',
        'eventshouse-error-report',
        'EventsHouse_render_error_report_page'
    );
}
add_action('admin_menu', 'EventsHouse_register_error_report_menu');

/**
 * Página admin do relatório.
 */
function EventsHouse_render_error_report_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Sem permissão.', 'EventsHouse'));
    }

    if (isset($_POST['eventshouse_clear_error_log']) && check_admin_referer('eventshouse_clear_error_log')) {
        EventsHouse_clear_error_logs();
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Registos apagados.', 'EventsHouse') . '</p></div>';
    }

    $filter_context = isset($_GET['context']) ? sanitize_key(wp_unslash($_GET['context'])) : '';
    $filter_level   = isset($_GET['level']) ? sanitize_key(wp_unslash($_GET['level'])) : '';

    $logs = EventsHouse_get_error_logs(array(
        'limit'   => 200,
        'context' => $filter_context,
        'level'   => $filter_level,
    ));

    $base_url = admin_url('tools.php?page=eventshouse-error-report');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Relatório de erros', 'EventsHouse'); ?></h1>
        <p class="description">
            <?php esc_html_e('Registo automático de falhas (reservas Manhãs na Ilha, envio de email, etc.). Últimas 200 entradas.', 'EventsHouse'); ?>
        </p>

        <form method="get" action="" style="margin:16px 0;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="page" value="eventshouse-error-report" />
            <p>
                <label for="eh-err-context"><?php esc_html_e('Contexto', 'EventsHouse'); ?></label><br>
                <select name="context" id="eh-err-context">
                    <option value=""><?php esc_html_e('Todos', 'EventsHouse'); ?></option>
                    <option value="ilha_reservation" <?php selected($filter_context, 'ilha_reservation'); ?>>ilha_reservation</option>
                    <option value="wp_mail" <?php selected($filter_context, 'wp_mail'); ?>>wp_mail</option>
                </select>
            </p>
            <p>
                <label for="eh-err-level"><?php esc_html_e('Nível', 'EventsHouse'); ?></label><br>
                <select name="level" id="eh-err-level">
                    <option value=""><?php esc_html_e('Todos', 'EventsHouse'); ?></option>
                    <option value="error" <?php selected($filter_level, 'error'); ?>>error</option>
                    <option value="warning" <?php selected($filter_level, 'warning'); ?>>warning</option>
                    <option value="info" <?php selected($filter_level, 'info'); ?>>info</option>
                </select>
            </p>
            <p>
                <button type="submit" class="button"><?php esc_html_e('Filtrar', 'EventsHouse'); ?></button>
                <a class="button" href="<?php echo esc_url($base_url); ?>"><?php esc_html_e('Limpar filtros', 'EventsHouse'); ?></a>
            </p>
        </form>

        <form method="post" action="" onsubmit="return confirm('<?php echo esc_js(__('Apagar todos os registos?', 'EventsHouse')); ?>');">
            <?php wp_nonce_field('eventshouse_clear_error_log'); ?>
            <p>
                <button type="submit" name="eventshouse_clear_error_log" value="1" class="button button-secondary">
                    <?php esc_html_e('Apagar todos os registos', 'EventsHouse'); ?>
                </button>
            </p>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th style="width:140px;"><?php esc_html_e('Data', 'EventsHouse'); ?></th>
                    <th style="width:70px;"><?php esc_html_e('Nível', 'EventsHouse'); ?></th>
                    <th style="width:130px;"><?php esc_html_e('Contexto', 'EventsHouse'); ?></th>
                    <th><?php esc_html_e('Mensagem', 'EventsHouse'); ?></th>
                    <th style="width:180px;"><?php esc_html_e('Email', 'EventsHouse'); ?></th>
                    <th><?php esc_html_e('Detalhes', 'EventsHouse'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr><td colspan="6"><?php esc_html_e('Nenhum registo.', 'EventsHouse'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($logs as $row) : ?>
                        <?php
                        $details_json = !empty($row->details) ? $row->details : '';
                        $details_arr  = $details_json ? json_decode($details_json, true) : array();
                        $details_pretty = '';
                        if (is_array($details_arr) && $details_arr !== array()) {
                            $details_pretty = wp_json_encode($details_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html(wp_date('d/m/Y H:i:s', strtotime($row->created_at))); ?></td>
                            <td><code><?php echo esc_html($row->level); ?></code></td>
                            <td><code><?php echo esc_html($row->context); ?></code></td>
                            <td><?php echo esc_html($row->message); ?></td>
                            <td><?php echo esc_html($row->user_email ?: '—'); ?></td>
                            <td>
                                <?php if ($details_pretty !== '') : ?>
                                    <details>
                                        <summary><?php esc_html_e('Ver', 'EventsHouse'); ?></summary>
                                        <pre style="white-space:pre-wrap;max-width:420px;font-size:11px;margin:8px 0 0;"><?php echo esc_html($details_pretty); ?></pre>
                                    </details>
                                <?php else : ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
