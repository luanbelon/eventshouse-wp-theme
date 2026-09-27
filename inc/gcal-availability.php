<?php
/**
 * Google Calendar: disponibilidade por slot (não marcar o dia inteiro por eventos parciais).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param string               $dateYmd Y-m-d.
 * @param string               $slotLine e.g. "10:00-12:30".
 * @param DateTimeZone         $tz      Fuso do site.
 * @return array{0:DateTimeImmutable,1:DateTimeImmutable}|null [start, endExclusive)
 */
function EventsHouse_gcal_slot_to_range($dateYmd, $slotLine, $tz)
{
    if (!preg_match('/^\s*(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})\s*$/u', trim($slotLine), $m)) {
        return null;
    }

    $h1 = (int) $m[1];
    $m1 = (int) $m[2];
    $h2 = (int) $m[3];
    $m2 = (int) $m[4];

    $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', sprintf('%s %02d:%02d:00', $dateYmd, $h1, $m1), $tz);
    if (!$start instanceof DateTimeImmutable) {
        return null;
    }
    $endInclusive = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', sprintf('%s %02d:%02d:00', $dateYmd, $h2, $m2), $tz);
    if (!$endInclusive instanceof DateTimeImmutable) {
        return null;
    }
    if ($endInclusive <= $start) {
        $endInclusive = $endInclusive->modify('+1 day');
    }

    $endExclusive = $endInclusive->modify('+1 minute');

    return array($start, $endExclusive);
}

/**
 * Intervalos half-open [a0,a1) e [b0,b1).
 */
function EventsHouse_gcal_ranges_overlap(DateTimeImmutable $a0, DateTimeImmutable $a1, DateTimeImmutable $b0, DateTimeImmutable $b1)
{
    return $a0 < $b1 && $a1 > $b0;
}

/**
 * @param array $items Resposta Google `items`.
 * @return array{all_day:array<string,bool>,intervals:array<int,array{start:DateTimeImmutable,end:DateTimeImmutable}>}
 */
function EventsHouse_gcal_parse_google_items(array $items, DateTimeZone $tz)
{
    $all_day   = array();
    $intervals = array();

    foreach ($items as $event) {
        if (!empty($event['start']['date'])) {
            $start = DateTimeImmutable::createFromFormat('Y-m-d', $event['start']['date'], $tz);
            if (!$start) {
                continue;
            }
            $start = $start->setTime(0, 0, 0);
            $endExclusive = $start->modify('+1 day');
            if (!empty($event['end']['date'])) {
                $endD = DateTimeImmutable::createFromFormat('Y-m-d', $event['end']['date'], $tz);
                if ($endD) {
                    $endExclusive = $endD->setTime(0, 0, 0);
                }
            }
            for ($d = $start; $d < $endExclusive; $d = $d->modify('+1 day')) {
                $all_day[$d->format('Y-m-d')] = true;
            }
        } elseif (!empty($event['start']['dateTime'])) {
            try {
                $s = new DateTimeImmutable($event['start']['dateTime']);
                $e = !empty($event['end']['dateTime'])
                    ? new DateTimeImmutable($event['end']['dateTime'])
                    : $s;
            } catch (Exception $e) {
                continue;
            }
            $intervals[] = array(
                'start' => $s->setTimezone($tz),
                'end'   => $e->setTimezone($tz),
            );
        }
    }

    return array(
        'all_day'   => $all_day,
        'intervals' => $intervals,
    );
}

/**
 * @param array  $items       Itens da API Google.
 * @param string $slots_raw   Textarea de slots.
 * @param int    $months      Meses para percorrer a partir de hoje.
 * @return array{fully_occupied:array<string>,blocked_slots:array<string,array<int,string>>}
 */
function EventsHouse_gcal_compute_slot_availability(array $items, $slots_raw, $months)
{
    $tz    = wp_timezone();
    $today = (new DateTimeImmutable('now', $tz))->setTime(0, 0, 0);
    $until = $today->add(new DateInterval('P' . max(1, (int) $months) . 'M'));

    $parsed = EventsHouse_gcal_parse_google_items($items, $tz);
    $all_day        = $parsed['all_day'];
    $busy_intervals = $parsed['intervals'];

    $slot_lines = array_values(array_filter(array_map('trim', explode("\n", (string) $slots_raw))));

    $fully_occupied = array();
    $blocked_slots  = array();

    if (empty($slot_lines)) {
        foreach ($all_day as $ymd => $_) {
            $fully_occupied[] = $ymd;
        }
        $fully_occupied = array_unique($fully_occupied);
        sort($fully_occupied);
        return array(
            'fully_occupied' => $fully_occupied,
            'blocked_slots'  => $blocked_slots,
        );
    }

    for ($d = $today; $d <= $until; $d = $d->modify('+1 day')) {
        $ymd = $d->format('Y-m-d');

        if (!empty($all_day[$ymd])) {
            $fully_occupied[] = $ymd;
            continue;
        }

        $day_start = $d;
        $day_end   = $d->modify('+1 day');

        $day_blocked  = array();
        $has_free_slot = false;

        foreach ($slot_lines as $line) {
            $range = EventsHouse_gcal_slot_to_range($ymd, $line, $tz);
            if ($range === null) {
                continue;
            }
            list($slotStart, $slotEndExcl) = $range;

            $slot_busy = false;
            foreach ($busy_intervals as $iv) {
                if ($iv['end'] <= $day_start || $iv['start'] >= $day_end) {
                    continue;
                }
                if (EventsHouse_gcal_ranges_overlap($slotStart, $slotEndExcl, $iv['start'], $iv['end'])) {
                    $slot_busy = true;
                    break;
                }
            }
            if ($slot_busy) {
                $day_blocked[] = $line;
            } else {
                $has_free_slot = true;
            }
        }

        if (!$has_free_slot) {
            $fully_occupied[] = $ymd;
        } elseif (!empty($day_blocked)) {
            $blocked_slots[$ymd] = $day_blocked;
        }
    }

    $fully_occupied = array_values(array_unique($fully_occupied));
    sort($fully_occupied);

    return array(
        'fully_occupied' => $fully_occupied,
        'blocked_slots'  => $blocked_slots,
    );
}

/**
 * Limpa cache AJAX dos calendários (após alterar API, IDs ou slots).
 */
function EventsHouse_invalidate_gcal_cache()
{
    delete_transient('eventshouse_gcal_avail_main');
    delete_transient('eventshouse_gcal_avail_ilha');
    delete_transient('eventshouse_gcal_events');
    delete_transient('eventshouse_gcal_events_ilha');
}

/**
 * Busca eventos brutos na Google Calendar API (com paginação).
 *
 * @param string $calendar_id ID do calendário.
 * @param string $api_key     Chave API.
 * @param string $time_min    ISO 8601 UTC (ex. 2026-01-01T00:00:00Z).
 * @param string $time_max    ISO 8601 UTC.
 * @return array{ok:bool,items:array<int,array>,message?:string}
 */
function EventsHouse_gcal_fetch_events_raw($calendar_id, $api_key, $time_min, $time_max)
{
    $calendar_id = trim((string) $calendar_id);
    $api_key     = trim((string) $api_key);

    if ($calendar_id === '' || $api_key === '') {
        return array(
            'ok'      => false,
            'items'   => array(),
            'message' => __('API Key ou ID do calendário Manhãs na Ilha em falta.', 'EventsHouse'),
        );
    }

    $all_items  = array();
    $page_token = '';
    $cal_id     = rawurlencode($calendar_id);
    $max_pages  = 20;

    for ($page = 0; $page < $max_pages; $page++) {
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$cal_id}/events";
        $url .= '?key=' . rawurlencode($api_key);
        $url .= '&timeMin=' . rawurlencode($time_min);
        $url .= '&timeMax=' . rawurlencode($time_max);
        $url .= '&singleEvents=true&orderBy=startTime&maxResults=250';
        if ($page_token !== '') {
            $url .= '&pageToken=' . rawurlencode($page_token);
        }

        $response = wp_remote_get($url, array('timeout' => 20));
        if (is_wp_error($response)) {
            return array(
                'ok'      => false,
                'items'   => $all_items,
                'message' => $response->get_error_message(),
            );
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code !== 200) {
            $err = is_array($body) && isset($body['error']['message'])
                ? (string) $body['error']['message']
                : __('Erro desconhecido na API Google.', 'EventsHouse');

            return array(
                'ok'      => false,
                'items'   => $all_items,
                'message' => $err,
            );
        }

        $items = isset($body['items']) && is_array($body['items']) ? $body['items'] : array();
        $all_items = array_merge($all_items, $items);

        $page_token = isset($body['nextPageToken']) ? (string) $body['nextPageToken'] : '';
        if ($page_token === '') {
            break;
        }
    }

    return array(
        'ok'    => true,
        'items' => $all_items,
    );
}
