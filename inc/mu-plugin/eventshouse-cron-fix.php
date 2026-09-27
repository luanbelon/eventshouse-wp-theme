<?php
/**
 * Plugin Name: EventsHouse - Action Scheduler Cron Fix (Must-Use)
 * Description: Regista apenas o intervalo every_minute. Sem interceptar pre_reschedule (evita inflar o cron e esgotar memória).
 * Version: 1.1.0
 * Author: EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('cron_schedules', function ($schedules) {
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
}, 1);

add_filter('cron_schedules', function ($schedules) {
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
}, 99999);
