<?php
/**
 * Sincronização de reservas Manhãs na Ilha a partir do Google Calendar.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Descrição do evento GCal em texto plano (mesma lógica do calendário público).
 *
 * @param array<string,mixed> $event
 */
function ilha_gcal_plain_description(array $event)
{
    $description_raw = isset($event['description']) ? (string) $event['description'] : '';
    $desc_work       = str_replace(array("\r\n", "\r"), "\n", $description_raw);
    $desc_work       = preg_replace('/<\/p>\s*<p[^>]*>/i', "\n\n", $desc_work);
    $desc_work       = preg_replace('/<p[^>]*>/i', '', (string) $desc_work);
    $desc_work       = preg_replace('/<\/p>/i', "\n\n", (string) $desc_work);
    $desc_work       = preg_replace('/<br\s*\/?>/i', "\n", (string) $desc_work);
    $desc_work       = wp_strip_all_tags((string) $desc_work, false);
    $desc_work       = preg_replace("/[ \t]+\n/u", "\n", (string) $desc_work);
    $desc_work       = preg_replace("/\n{3,}/u", "\n\n", (string) $desc_work);
    $desc_work       = html_entity_decode((string) $desc_work, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim((string) $desc_work);
}

/**
 * Extrai contacto de um evento GCal.
 *
 * @param array<string,mixed> $event
 * @return array{nome:string,email:string,telefone:string}
 */
function ilha_gcal_parse_contact_from_event(array $event)
{
    $summary     = trim(preg_replace('/\s+/u', ' ', (string) ($event['summary'] ?? '')));
    $description = ilha_gcal_plain_description($event);

    $email = '';
    $phone = '';
    $name  = '';

    if (preg_match('/(?:e-?mail|email)\s*:?\s*([^\s\n<>"\'()]+@[^\s\n<>"\'()]+)/iu', $description, $m)) {
        $email = sanitize_email($m[1]);
    }
    if ($email === '' && preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/iu', $description, $m)) {
        $email = sanitize_email($m[0]);
    }

    if (!empty($event['attendees']) && is_array($event['attendees'])) {
        foreach ($event['attendees'] as $att) {
            if (empty($att['email']) || !empty($att['self'])) {
                continue;
            }
            $att_email = sanitize_email($att['email']);
            if (!is_email($att_email)) {
                continue;
            }
            if ($email === '' || (!empty($att['organizer']) && $email === '')) {
                $email = $att_email;
            }
            if ($name === '' && !empty($att['displayName'])) {
                $name = trim((string) $att['displayName']);
            }
        }
    }

    if (preg_match('/(?:telefone|telemóvel|telemovel|tel\.?|phone)\s*:?\s*([+\d][\d\s().-]{7,})/iu', $description, $m)) {
        $phone = sanitize_text_field(trim($m[1]));
    }

    if (preg_match('/(?:nome|cliente|responsável|responsavel)\s*:?\s*(.+?)(?:\n|$)/iu', $description, $m)) {
        $name = sanitize_text_field(trim($m[1]));
    }

    if ($name === '' && $summary !== '') {
        $candidate = $summary;
        $candidate = preg_replace('/\s*[-–—|]\s*manh[aã]s\s+na\s+ilha.*$/iu', '', $candidate);
        $candidate = preg_replace('/^manh[aã]s\s+na\s+ilha\s*[-–—|:]\s*/iu', '', (string) $candidate);
        $candidate = preg_replace('/^\[?\s*a\s+ilha\s*\]?\s*reserva\s+manh[aã]s.*?\s*[-–—|:]\s*/iu', '', (string) $candidate);
        $candidate = preg_replace('/^reserva\s+(?:manh[aã]s\s+na\s+ilha\s*)?[-–—|:]\s*/iu', '', (string) $candidate);
        $candidate = trim((string) $candidate);
        if ($candidate !== '' && stripos($candidate, 'manhãs na ilha') === false && mb_strlen($candidate) < 80) {
            $name = sanitize_text_field($candidate);
        }
    }

    if ($name === '' && is_email($email)) {
        $local = strstr($email, '@', true);
        $name  = $local ? sanitize_text_field(ucfirst(str_replace(array('.', '_'), ' ', $local))) : __('Cliente', 'EventsHouse');
    }

    return array(
        'nome'     => $name,
        'email'    => $email,
        'telefone' => $phone,
    );
}

/**
 * Evento é slot/sessão publicada (não reserva de cliente).
 *
 * @param array<string,mixed> $event
 * @param array{nome:string,email:string,telefone:string} $contact
 */
function ilha_gcal_event_is_availability_slot(array $event, array $contact)
{
    if ($contact['email'] !== '' && is_email($contact['email'])) {
        return false;
    }

    $summary = mb_strtolower(trim((string) ($event['summary'] ?? '')));
    if ($summary === '' || $summary === 'manhãs na ilha' || $summary === 'manhas na ilha') {
        return true;
    }
    if (preg_match('/^manh[aã]s\s+na\s+ilha(\s|$)/iu', $summary) && mb_strlen($summary) < 35) {
        return true;
    }

    return false;
}

/**
 * Data e hora do evento.
 *
 * @param array<string,mixed> $event
 * @return array{data:string,event_time:string,event_title:string}|null
 */
function ilha_gcal_event_datetime_fields(array $event)
{
    $tz = wp_timezone();

    if (!empty($event['start']['dateTime'])) {
        try {
            $start = (new DateTimeImmutable($event['start']['dateTime']))->setTimezone($tz);
            $end   = !empty($event['end']['dateTime'])
                ? (new DateTimeImmutable($event['end']['dateTime']))->setTimezone($tz)
                : $start;
        } catch (Exception $e) {
            return null;
        }

        $summary = trim((string) ($event['summary'] ?? ''));

        return array(
            'data'         => $start->format('Y-m-d'),
            'event_time'   => $start->format('H:i') . ' - ' . $end->format('H:i'),
            'event_title'  => $summary !== '' ? $summary : __('Manhãs na Ilha', 'EventsHouse'),
        );
    }

    if (!empty($event['start']['date'])) {
        $startD = DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['start']['date'], $tz);
        if (!$startD) {
            return null;
        }
        $summary = trim((string) ($event['summary'] ?? ''));

        return array(
            'data'        => $startD->format('Y-m-d'),
            'event_time'  => __('Dia inteiro', 'EventsHouse'),
            'event_title' => $summary !== '' ? $summary : __('Manhãs na Ilha', 'EventsHouse'),
        );
    }

    return null;
}

/**
 * @param string $gcal_event_id
 */
function ilha_reserva_get_id_by_gcal_event($gcal_event_id)
{
    global $wpdb;
    $gcal_event_id = sanitize_text_field($gcal_event_id);
    if ($gcal_event_id === '') {
        return 0;
    }

    return (int) $wpdb->get_var($wpdb->prepare(
        'SELECT id FROM ' . ilha_reservas_table() . ' WHERE gcal_event_id = %s LIMIT 1',
        $gcal_event_id
    ));
}

/**
 * @param string $email
 * @param string $date Y-m-d
 */
function ilha_reserva_exists_email_date($email, $date)
{
    if (!is_email($email) || $date === '') {
        return false;
    }

    global $wpdb;

    return (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM " . ilha_reservas_table() . " WHERE email = %s AND data_reserva = %s AND status != 'cancelada' LIMIT 1",
        $email,
        $date
    ));
}

/**
 * Converte um evento GCal em dados de reserva (ou null se ignorar).
 *
 * @param array<string,mixed> $event
 * @return array<string,mixed>|null
 */
function ilha_gcal_event_to_reserva_data(array $event)
{
    $event_id = isset($event['id']) ? sanitize_text_field((string) $event['id']) : '';
    if ($event_id === '') {
        return null;
    }

    if (!empty($event['status']) && $event['status'] === 'cancelled') {
        return null;
    }

    $contact = ilha_gcal_parse_contact_from_event($event);
    if (ilha_gcal_event_is_availability_slot($event, $contact)) {
        return null;
    }

    if ($contact['email'] === '' || !is_email($contact['email'])) {
        return null;
    }

    $when = ilha_gcal_event_datetime_fields($event);
    if ($when === null) {
        return null;
    }

    $cliente_id = null;
    $passe_id   = null;
    $cliente    = ilha_get_cliente_by_email($contact['email']);
    if ($cliente) {
        $cliente_id = (int) $cliente->id;
        if ($contact['nome'] === '' && !empty($cliente->nome)) {
            $contact['nome'] = $cliente->nome;
        }
        $passe = ilha_get_reservable_pass_for_cliente($cliente_id);
        if (!$passe) {
            global $wpdb;
            $passe = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ilha_passes WHERE cliente_id = %d ORDER BY id DESC LIMIT 1",
                $cliente_id
            ));
        }
        if ($passe) {
            $passe_id = (int) $passe->id;
        }
    }

    if ($contact['nome'] === '') {
        return null;
    }

    $event_link = '';
    $desc_raw   = isset($event['description']) ? (string) $event['description'] : '';
    if (preg_match('#https?://[^\s<>"\']+#i', $desc_raw, $m)) {
        $event_link = esc_url_raw($m[0]);
    }

    $criada_em = current_time('mysql');
    if (!empty($event['created'])) {
        try {
            $created = new DateTimeImmutable($event['created']);
            $criada_em = $created->setTimezone(wp_timezone())->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Mantém agora.
        }
    }

    return array(
        'gcal_event_id' => $event_id,
        'cliente_id'    => $cliente_id,
        'passe_id'      => $passe_id,
        'data_reserva'  => $when['data'],
        'event_title'   => $when['event_title'],
        'event_time'    => $when['event_time'],
        'nome'          => $contact['nome'],
        'email'         => $contact['email'],
        'telefone'      => $contact['telefone'],
        'criada_em'     => $criada_em,
        'event_link'    => $event_link,
    );
}

/**
 * Importa reservas do calendário Manhãs na Ilha.
 *
 * @param array<string,int> $args days_past, days_future
 * @return array<string,mixed>
 */
function ilha_sync_reservas_from_gcal(array $args = array())
{
    $days_past   = max(0, min(365, (int) ($args['days_past'] ?? 60)));
    $days_future = max(1, min(365, (int) ($args['days_future'] ?? 90)));

    $api_key     = get_option('eventshouse_gcal_api_key', '');
    $calendar_id = get_option('eventshouse_gcal_ilha_calendar_id', '');

    $tz    = wp_timezone();
    $start = (new DateTimeImmutable('now', $tz))->setTime(0, 0, 0)->modify('-' . $days_past . ' days');
    $end   = (new DateTimeImmutable('now', $tz))->setTime(23, 59, 59)->modify('+' . $days_future . ' days');

    $time_min = $start->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    $time_max = $end->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');

    $fetch = EventsHouse_gcal_fetch_events_raw($calendar_id, $api_key, $time_min, $time_max);
    if (!$fetch['ok']) {
        return array(
            'success' => false,
            'message' => $fetch['message'] ?? __('Erro ao ler o Google Calendar.', 'EventsHouse'),
        );
    }

    $stats = array(
        'success'        => true,
        'events_total'   => count($fetch['items']),
        'imported'       => 0,
        'skipped_exists' => 0,
        'skipped_slot'   => 0,
        'skipped_no_data'=> 0,
        'errors'         => 0,
    );

    foreach ($fetch['items'] as $event) {
        if (!is_array($event)) {
            continue;
        }

        $data = ilha_gcal_event_to_reserva_data($event);
        if ($data === null) {
            $contact = ilha_gcal_parse_contact_from_event($event);
            if (ilha_gcal_event_is_availability_slot($event, $contact)) {
                $stats['skipped_slot']++;
            } else {
                $stats['skipped_no_data']++;
            }
            continue;
        }

        $gcal_id = $data['gcal_event_id'];
        if (ilha_reserva_get_id_by_gcal_event($gcal_id) > 0) {
            $stats['skipped_exists']++;
            continue;
        }

        if (ilha_reserva_exists_email_date($data['email'], $data['data_reserva'])) {
            $stats['skipped_exists']++;
            continue;
        }

        $insert_id = ilha_insert_reserva($data);
        if ($insert_id) {
            $stats['imported']++;
        } else {
            $stats['errors']++;
        }
    }

    $stats['message'] = sprintf(
        /* translators: 1: imported count, 2: total events */
        __('Sincronização concluída: %1$d reservas importadas (analisados %2$d eventos no Google Calendar).', 'EventsHouse'),
        (int) $stats['imported'],
        (int) $stats['events_total']
    );

    return $stats;
}

/**
 * AJAX: sincronizar reservas a partir do GCal (admin).
 */
function ajax_ilha_sync_reservas_gcal()
{
    check_ajax_referer('EventsHouse_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('Sem permissão.', 'EventsHouse')));
    }

    $days_past   = isset($_POST['days_past']) ? absint($_POST['days_past']) : 60;
    $days_future = isset($_POST['days_future']) ? absint($_POST['days_future']) : 90;

    $result = ilha_sync_reservas_from_gcal(array(
        'days_past'   => $days_past,
        'days_future' => $days_future,
    ));

    if (empty($result['success'])) {
        wp_send_json_error(array('message' => $result['message'] ?? __('Falha na sincronização.', 'EventsHouse')));
    }

    wp_send_json_success($result);
}
add_action('wp_ajax_ilha_sync_reservas_gcal', 'ajax_ilha_sync_reservas_gcal');
