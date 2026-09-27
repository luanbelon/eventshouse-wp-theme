<?php
/**
 * Emails automáticos de reservas Manhãs na Ilha (confirmação, lembrete, pós-visita).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Hora do lembrete (1 dia antes) — Europe/Lisbon. */
define('ILHA_RESERVATION_REMINDER_HOUR', 21);
define('ILHA_RESERVATION_REMINDER_MINUTE', 0);

/** Hora do email pós-visita (no próprio dia) — Europe/Lisbon. */
define('ILHA_RESERVATION_POSTVISIT_HOUR', 17);
define('ILHA_RESERVATION_POSTVISIT_MINUTE', 0);

/**
 * Chaves de opções de email de reserva (defaults + admin).
 *
 * @return array<string, string>
 */
function ilha_reservation_email_options_map()
{
    $reserve_url = function_exists('EventsHouse_ilha_passes_page_url')
        ? EventsHouse_ilha_passes_page_url('#ilha-disponibilidade')
        : home_url('/manhas-na-ilha/#ilha-disponibilidade');

    return array(
        'eventshouse_email_ilha_reservation_subject' => 'A sua reserva nas Manhãs na Ilha — {date_human}',
        'eventshouse_email_ilha_reservation_title'   => 'Reserva confirmada',
        'eventshouse_email_ilha_reservation_body'    => "<p>Olá %name%!</p>\n<p>A sua reserva nas <strong>Manhãs na Ilha</strong> foi confirmada com sucesso.</p>\n<p><strong>📅 Data:</strong> %date_long%<br><strong>🕘 Horário:</strong> %horario%</p>\n<p>Na receção, basta indicar o email utilizado na compra do passe.</p>\n<p>Até breve!<br>Equipa A Ilha 💛</p>",
        'eventshouse_email_ilha_reservation_success_msg' => 'Reserva confirmada! Enviámos a confirmação para o seu email.',
        'eventshouse_email_ilha_reservation_error_msg'   => 'Não foi possível enviar a reserva. Tente novamente.',
        'eventshouse_email_ilha_reservation_admin_subject' => '[A Ilha] Nova reserva Manhãs — {name} em {date_human}',
        'eventshouse_email_ilha_reservation_admin_title'   => 'Nova reserva — Manhãs na Ilha',
        'eventshouse_email_ilha_reservation_admin_body'    => "<p>Recebemos um novo pedido de reserva para Manhãs na Ilha.</p>\n<p><strong>Data:</strong> %date_long%<br><strong>Evento:</strong> %event_title%<br><strong>Horário:</strong> %horario%</p>\n<p><strong>Nome:</strong> %name%<br><strong>Email:</strong> %email%<br><strong>Telefone:</strong> %phone%<br><strong>Idade da criança:</strong> %child_age%</p>",
        'eventshouse_email_ilha_reservation_reminder_subject' => 'É amanhã! Manhãs na Ilha — {date_human}',
        'eventshouse_email_ilha_reservation_reminder_title'   => 'Lembrete da sua visita',
        'eventshouse_email_ilha_reservation_reminder_body'    => "<p>Olá!</p>\n<p>É já amanhã que o esperamos nas Manhãs na Ilha.</p>\n<p><strong>📅 Data:</strong> %date_long%<br><strong>🕘 Horário:</strong> %horario%</p>\n<p>A nossa Patrícia estará pronta para receber os mais pequenos, enquanto aproveita uma manhã mais tranquila para trabalhar, relaxar ou simplesmente desfrutar do espaço.</p>\n<p><strong>Não se esqueça:</strong></p>\n<ul>\n<li>Basta indicar o email utilizado na compra do passe.</li>\n<li>A presença de um adulto responsável é obrigatória durante toda a permanência.</li>\n</ul>\n<p>Estamos ansiosos por vos receber!</p>\n<p>Até amanhã! 💛<br>Equipa A Ilha</p>",
        'eventshouse_email_ilha_reservation_postvisit_subject' => 'Obrigado pela visita de hoje — Manhãs na Ilha',
        'eventshouse_email_ilha_reservation_postvisit_title'   => 'Obrigado pela visita',
        'eventshouse_email_ilha_reservation_postvisit_body'    => "<p>Olá!</p>\n<p>Foi um prazer receber-vos hoje na Ilha.</p>\n<p>Esperamos que tenham desfrutado da manhã e que as crianças se tenham divertido muito!</p>\n<p>Se ainda tem entradas disponíveis no seu passe, já pode <a href=\"%reserve_url%\">reservar a sua próxima visita</a> através do nosso site.</p>\n<p>Adorávamos também saber como foi a vossa experiência. O seu feedback é muito importante para continuarmos a melhorar.</p>\n<p>Obrigado por fazer parte da comunidade da Ilha.</p>\n<p>Até breve!<br>Equipa A Ilha 💛</p>",
        'eventshouse_email_ilha_horario_padrao' => '09h00 – 13h00',
        'eventshouse_email_gcal_reservation_webhook' => '',
    );
}

/**
 * @param int $reserva_id
 * @return object|null
 */
function ilha_get_reserva_by_id($reserva_id)
{
    global $wpdb;
    $reserva_id = absint($reserva_id);
    if (!$reserva_id) {
        return null;
    }

    return $wpdb->get_row($wpdb->prepare(
        'SELECT * FROM ' . ilha_reservas_table() . ' WHERE id = %d',
        $reserva_id
    ));
}

/**
 * Timestamp em Europe/Lisbon para agendamento.
 *
 * @param string $date_ymd   Y-m-d da reserva
 * @param int    $day_offset Dias relativos à data da reserva (-1 = véspera)
 * @param int    $hour
 * @param int    $minute
 * @return int
 */
function ilha_reserva_schedule_ts($date_ymd, $day_offset, $hour, $minute)
{
    $tz = new DateTimeZone('Europe/Lisbon');
    $dt = DateTime::createFromFormat('Y-m-d', $date_ymd, $tz);
    if (!$dt) {
        return 0;
    }
    if ($day_offset !== 0) {
        $dt->modify(($day_offset > 0 ? '+' : '') . $day_offset . ' day');
    }
    $dt->setTime((int) $hour, (int) $minute, 0);

    return $dt->getTimestamp();
}

/**
 * Variáveis comuns nos templates de reserva.
 *
 * @param object $reserva
 * @return array<string, string>
 */
function ilha_reservation_email_vars($reserva)
{
    $date = (string) ($reserva->data_reserva ?? '');
    $ts   = $date !== '' ? strtotime($date . ' 12:00:00') : false;
    $horario = EventsHouse_email_opt('eventshouse_email_ilha_horario_padrao');
    if ($horario === '') {
        $horario = '09h00 – 13h00';
    }

    $reserve_url = function_exists('EventsHouse_ilha_passes_page_url')
        ? EventsHouse_ilha_passes_page_url('#ilha-disponibilidade')
        : home_url('/manhas-na-ilha/#ilha-disponibilidade');

    return array(
        'name'        => (string) ($reserva->nome ?? ''),
        'email'       => (string) ($reserva->email ?? ''),
        'phone'       => (string) ($reserva->telefone ?? ''),
        'child_age'   => isset($reserva->idade_crianca) ? (string) (int) $reserva->idade_crianca : '',
        'date'        => $date,
        'date_human'  => $ts ? wp_date('d/m/Y', $ts) : $date,
        'date_long'   => $ts ? wp_date('l, j \d\e F \d\e Y', $ts) : $date,
        'DATA'        => $ts ? wp_date('l, j \d\e F \d\e Y', $ts) : $date,
        'horario'     => $horario,
        'event_title' => (string) ($reserva->event_title ?? 'Manhãs na Ilha'),
        'event_time'  => (string) ($reserva->event_time ?? $horario),
        'event_link'  => $reserve_url,
        'reserve_url' => $reserve_url,
        'site_name'   => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
    );
}

/**
 * Envia email HTML a partir de chaves de opção.
 *
 * @param string               $to
 * @param string               $subject_key
 * @param string               $title_key
 * @param string               $body_key
 * @param array<string,string> $vars
 * @param string               $reply_to_name
 * @param string               $reply_to_email
 * @return bool
 */
function ilha_send_reservation_email($to, $subject_key, $title_key, $body_key, array $vars, $reply_to_name = '', $reply_to_email = '')
{
    if (!is_email($to)) {
        return false;
    }

    $subject = sanitize_text_field(EventsHouse_email_replace_vars(EventsHouse_email_opt($subject_key), $vars));
    $title   = sanitize_text_field(EventsHouse_email_replace_vars(EventsHouse_email_opt($title_key), $vars));
    $body    = EventsHouse_build_email_template_html(
        $title,
        EventsHouse_email_render_template_body(EventsHouse_email_opt($body_key), $vars)
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');
    if ($reply_to_email !== '' && is_email($reply_to_email)) {
        $headers[] = sprintf('Reply-To: %s <%s>', $reply_to_name ?: $reply_to_email, $reply_to_email);
    }

    return (bool) wp_mail($to, $subject, $body, $headers);
}

/**
 * Email de confirmação ao cliente + cópia interna à equipa.
 *
 * @param int $reserva_id
 * @return bool Cliente recebeu email?
 */
function ilha_send_reservation_confirmation_emails($reserva_id)
{
    $reserva = ilha_get_reserva_by_id($reserva_id);
    if (!$reserva || empty($reserva->email) || !is_email($reserva->email)) {
        return false;
    }

    $vars = ilha_reservation_email_vars($reserva);

    $sent_client = ilha_send_reservation_email(
        $reserva->email,
        'eventshouse_email_ilha_reservation_subject',
        'eventshouse_email_ilha_reservation_title',
        'eventshouse_email_ilha_reservation_body',
        $vars
    );

    $admin_to = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
    if (!is_email($admin_to)) {
        $admin_to = EventsHouse_DEFAULT_CONTACT_EMAIL;
    }

    ilha_send_reservation_email(
        $admin_to,
        'eventshouse_email_ilha_reservation_admin_subject',
        'eventshouse_email_ilha_reservation_admin_title',
        'eventshouse_email_ilha_reservation_admin_body',
        $vars,
        $reserva->nome,
        $reserva->email
    );

    if (!$sent_client && function_exists('EventsHouse_log_error')) {
        EventsHouse_log_error('ilha_reservation', 'Falha ao enviar email de confirmação ao cliente.', array(
            'reserva_id' => $reserva_id,
            'email'      => $reserva->email,
            'mail_error' => function_exists('EventsHouse_last_mail_error_message') ? EventsHouse_last_mail_error_message() : '',
        ), 'warning');
    }

    return $sent_client;
}

/**
 * Agenda evento único WP-Cron.
 *
 * @param string $hook
 * @param int    $reserva_id
 * @param int    $timestamp
 */
function ilha_reservation_schedule_single($hook, $reserva_id, $timestamp)
{
    $reserva_id = absint($reserva_id);
    $timestamp  = (int) $timestamp;
    if (!$reserva_id || $timestamp <= 0) {
        return;
    }

    $args = array($reserva_id);
    $next = wp_next_scheduled($hook, $args);
    while ($next) {
        wp_unschedule_event($next, $hook, $args);
        $next = wp_next_scheduled($hook, $args);
    }

    wp_schedule_single_event($timestamp, $hook, $args);
}

/**
 * Remove emails agendados de uma reserva.
 *
 * @param int $reserva_id
 */
function ilha_unschedule_reservation_emails($reserva_id)
{
    $reserva_id = absint($reserva_id);
    if (!$reserva_id) {
        return;
    }

    $hooks = array(
        'eventshouse_ilha_reservation_reminder_email',
        'eventshouse_ilha_reservation_postvisit_email',
    );

    foreach ($hooks as $hook) {
        $args = array($reserva_id);
        $next = wp_next_scheduled($hook, $args);
        while ($next) {
            wp_unschedule_event($next, $hook, $args);
            $next = wp_next_scheduled($hook, $args);
        }
    }
}

/**
 * Agenda lembrete (véspera 21h) e pós-visita (17h no dia).
 *
 * @param int $reserva_id
 */
function ilha_schedule_reservation_followup_emails($reserva_id)
{
    $reserva = ilha_get_reserva_by_id($reserva_id);
    if (!$reserva || $reserva->status === 'cancelada' || empty($reserva->data_reserva)) {
        return;
    }

    ilha_unschedule_reservation_emails($reserva_id);

    $now = time();
    $date = $reserva->data_reserva;

    $reminder_ts = ilha_reserva_schedule_ts(
        $date,
        -1,
        ILHA_RESERVATION_REMINDER_HOUR,
        ILHA_RESERVATION_REMINDER_MINUTE
    );
    if ($reminder_ts > $now) {
        ilha_reservation_schedule_single('eventshouse_ilha_reservation_reminder_email', $reserva_id, $reminder_ts);
    }

    $postvisit_ts = ilha_reserva_schedule_ts(
        $date,
        0,
        ILHA_RESERVATION_POSTVISIT_HOUR,
        ILHA_RESERVATION_POSTVISIT_MINUTE
    );
    if ($postvisit_ts > $now) {
        ilha_reservation_schedule_single('eventshouse_ilha_reservation_postvisit_email', $reserva_id, $postvisit_ts);
    }
}

/**
 * @param int $reserva_id
 */
function ilha_on_reserva_created_schedule_emails($reserva_id)
{
    ilha_schedule_reservation_followup_emails($reserva_id);
}
add_action('ilha_reserva_created', 'ilha_on_reserva_created_schedule_emails', 20);

/**
 * @param int $reserva_id
 */
function ilha_on_reserva_cancelled_unschedule_emails($reserva_id)
{
    ilha_unschedule_reservation_emails($reserva_id);
}
add_action('ilha_reserva_cancelled', 'ilha_on_reserva_cancelled_unschedule_emails', 10);

/**
 * Cron: lembrete 1 dia antes (21h).
 *
 * @param int $reserva_id
 */
function ilha_cron_reservation_reminder_email($reserva_id)
{
    $reserva = ilha_get_reserva_by_id($reserva_id);
    if (!$reserva || !in_array($reserva->status, array('confirmada', 'presente'), true)) {
        return;
    }
    if (empty($reserva->email) || !is_email($reserva->email)) {
        return;
    }

    $vars = ilha_reservation_email_vars($reserva);
    ilha_send_reservation_email(
        $reserva->email,
        'eventshouse_email_ilha_reservation_reminder_subject',
        'eventshouse_email_ilha_reservation_reminder_title',
        'eventshouse_email_ilha_reservation_reminder_body',
        $vars
    );
}
add_action('eventshouse_ilha_reservation_reminder_email', 'ilha_cron_reservation_reminder_email', 10, 1);

/**
 * Cron: pós-visita no próprio dia (17h).
 *
 * @param int $reserva_id
 */
function ilha_cron_reservation_postvisit_email($reserva_id)
{
    $reserva = ilha_get_reserva_by_id($reserva_id);
    if (!$reserva || !in_array($reserva->status, array('confirmada', 'presente'), true)) {
        return;
    }
    if (empty($reserva->email) || !is_email($reserva->email)) {
        return;
    }

    $vars = ilha_reservation_email_vars($reserva);
    ilha_send_reservation_email(
        $reserva->email,
        'eventshouse_email_ilha_reservation_postvisit_subject',
        'eventshouse_email_ilha_reservation_postvisit_title',
        'eventshouse_email_ilha_reservation_postvisit_body',
        $vars
    );
}
add_action('eventshouse_ilha_reservation_postvisit_email', 'ilha_cron_reservation_postvisit_email', 10, 1);
