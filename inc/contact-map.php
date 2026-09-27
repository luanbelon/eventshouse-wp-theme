<?php
/**
 * Mapa na página de contactos: morada, link maps.app.goo.gl ou URL de incorporação.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Segue redirecionamentos de links curtos (maps.app.goo.gl, goo.gl/maps).
 *
 * @param string $url URL completa.
 * @return string URL final ou a original em caso de falha.
 */
function EventsHouse_resolve_google_maps_short_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }
    $url = esc_url_raw($url);
    if ($url === '') {
        return '';
    }

    $current = $url;
    for ($i = 0; $i < 12; $i++) {
        $response = wp_remote_head(
            $current,
            array(
                'timeout'     => 12,
                'redirection' => 0,
                'sslverify'   => true,
                'user-agent'  => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/'),
            )
        );
        if (is_wp_error($response)) {
            return $url;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code >= 300 && $code < 400) {
            $loc = wp_remote_retrieve_header($response, 'location');
            if ($loc) {
                if (strpos($loc, 'http') !== 0) {
                    $parts = wp_parse_url($current);
                    $base  = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '');
                    $loc   = (strpos($loc, '/') === 0) ? $base . $loc : $base . '/' . ltrim($loc, '/');
                }
                $current = $loc;
                continue;
            }
        }
        if ($code === 200) {
            return $current;
        }

        return $url;
    }

    return $current;
}

/**
 * URL para o atributo src do iframe do mapa.
 *
 * Aceita: morada em texto; link https://maps.app.goo.gl/...; URL completa do Google Maps;
 * ou URL de incorporação (embed) existente.
 *
 * @param string $raw              Valor guardado em contato_maps_url (ou vazio).
 * @param string $address_fallback Morada do tema se $raw estiver vazio.
 * @return string URL segura para iframe ou string vazia.
 */
function EventsHouse_contact_map_iframe_src($raw, $address_fallback)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        $raw = trim((string) $address_fallback);
    }
    if ($raw === '') {
        return '';
    }

    if (preg_match('#^https?://(www\.)?google\.[\w.]+/maps/embed#i', $raw)) {
        return esc_url_raw($raw);
    }

    if (preg_match('#^https?://maps\.google\.com/maps\?#i', $raw) && strpos($raw, 'output=embed') !== false) {
        return esc_url_raw($raw);
    }

    if (preg_match('#^https?://#i', $raw) && preg_match('#(maps\.app\.goo\.gl/|goo\.gl/maps)#i', $raw)) {
        $resolved = EventsHouse_resolve_google_maps_short_url($raw);
        $q        = ($resolved !== '' && $resolved !== $raw) ? $resolved : $raw;

        return 'https://www.google.com/maps?q=' . rawurlencode($q) . '&output=embed';
    }

    if (preg_match('#^https?://(www\.)?(google\.[\w.]+/maps|maps\.google\.com)#i', $raw)) {
        return 'https://www.google.com/maps?q=' . rawurlencode($raw) . '&output=embed';
    }

    return 'https://www.google.com/maps?q=' . rawurlencode($raw) . '&output=embed';
}

/**
 * URL para «Abrir no Google Maps» (novo separador).
 *
 * @param string $raw              Valor em contato_maps_url.
 * @param string $address_fallback Morada.
 * @return string URL ou vazio.
 */
function EventsHouse_contact_map_open_url($raw, $address_fallback)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        $raw = trim((string) $address_fallback);
    }
    if ($raw === '') {
        return '';
    }

    if (preg_match('#^https?://#i', $raw)) {
        if (preg_match('#(maps\.app\.goo\.gl/|goo\.gl/maps)#i', $raw)) {
            $resolved = EventsHouse_resolve_google_maps_short_url($raw);

            return ($resolved !== '') ? $resolved : esc_url_raw($raw);
        }

        return esc_url_raw($raw);
    }

    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($raw);
}
