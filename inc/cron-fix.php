<?php
/**
 * Registo seguro do intervalo every_minute (Action Scheduler / WooCommerce).
 *
 * Não intercepta pre_reschedule_event nem reescreve o cron array —
 * essas abordagens podem inflar a opção cron e esgotar a memória (503).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Garante o intervalo `every_minute` usado pelo Action Scheduler.
 *
 * @param array $schedules Schedules existentes.
 * @return array
 */
function EventsHouse_register_every_minute_schedule($schedules)
{
    if (!is_array($schedules)) {
        $schedules = array();
    }
    if (empty($schedules['every_minute']['interval'])) {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display'  => 'Every Minute',
        );
    }
    return $schedules;
}
add_filter('cron_schedules', 'EventsHouse_register_every_minute_schedule', 1);
add_filter('cron_schedules', 'EventsHouse_register_every_minute_schedule', 99999);
