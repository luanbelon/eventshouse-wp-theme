<?php
/**
 * Admin: página Verão na Ilha — textos e imagens (theme_mod).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Valores por omissão (conteúdo atual da página).
 *
 * @return array<string, string>
 */
function EventsHouse_verao_ilha_defaults_map()
{
    $uri = EventsHouse_URI;

    return array(
        'verao_ilha_hero_badge'            => 'Programa de Verão',
        'verao_ilha_hero_title'            => 'Uma experiencia criativa de verao para os 3 aos 5 anos',
        'verao_ilha_hero_subtitle'         => 'Um programa premium com acompanhamento proximo, ambiente seguro e grupos pequenos. Nao e ATL, e uma experiencia criativa pensada para cada crianca.',
        'verao_ilha_hero_btn_text'         => 'Pedir informacoes',
        'verao_ilha_hero_btn_url'          => '',
        'verao_ilha_hero_banner_url'       => $uri . '/assets/images/verao-na-ilha-banner.png',
        'verao_ilha_hero_banner_alt'       => 'Verao na Ilha - experiencia criativa',
        'verao_ilha_feature_1_image_url'   => $uri . '/assets/images/verao-ilha/icon-grupos-pequenos.svg',
        'verao_ilha_feature_1_title'       => 'Grupos pequenos',
        'verao_ilha_feature_1_text'        => 'Máximo 16 crianças por grupo para garantir atenção individualizada e segurança.',
        'verao_ilha_feature_2_image_url'   => $uri . '/assets/images/verao-ilha/icon-equipa-dedicada.svg',
        'verao_ilha_feature_2_title'       => 'Equipa dedicada',
        'verao_ilha_feature_2_text'        => '1 educadora e 1 auxiliar em cada turno, com experiência e dedicação.',
        'verao_ilha_feature_3_image_url'   => $uri . '/assets/images/verao-ilha/icon-semanas-tematicas.svg',
        'verao_ilha_feature_3_title'       => 'Semanas temáticas',
        'verao_ilha_feature_3_text'        => 'Atividades criativas diferentes em cada semana para aprender brincando.',
        'verao_ilha_feature_4_image_url'   => $uri . '/assets/images/verao-ilha/icon-ambiente-seguro.svg',
        'verao_ilha_feature_4_title'       => 'Ambiente seguro',
        'verao_ilha_feature_4_text'        => 'Acolhimento atento e acompanhamento próximo em todas as atividades.',
        'verao_ilha_features_section_title'    => '',
        'verao_ilha_features_section_subtitle' => '',
        'verao_ilha_schedule_section_title'    => 'Horários',
        'verao_ilha_schedule_section_subtitle'   => 'Escolha o turno ideal para a sua família.',
        'verao_ilha_meta_section_title'        => 'Em cada experiencia',
        'verao_ilha_meta_section_subtitle'     => 'Equipa dedicada, faixa etaria e grupo pequeno.',
        'verao_ilha_cta_section_title'         => '',
        'verao_ilha_cta_section_subtitle'      => '',
        'verao_ilha_schedule_morning_icon_url'       => $uri . '/assets/images/verao-ilha/icon-manha.svg',
        'verao_ilha_schedule_afternoon_icon_url'     => $uri . '/assets/images/verao-ilha/icon-tarde.svg',
        'verao_ilha_schedule_morning_illustration_url'   => $uri . '/assets/images/verao-ilha/ilustracao-manha.svg',
        'verao_ilha_schedule_afternoon_illustration_url' => $uri . '/assets/images/verao-ilha/ilustracao-tarde.svg',
        'verao_ilha_slot_morning_label'    => 'Manhã',
        'verao_ilha_slot_morning_time'     => '08h30 - 12h00',
        'verao_ilha_slot_afternoon_label'  => 'Tarde',
        'verao_ilha_slot_afternoon_time'   => '14h00 - 17h30',
        'verao_ilha_schedule_note'         => 'Sem opção de dia completo.',
        'verao_ilha_slot_morning_bullets'  => "Recepção das crianças\nAtividades criativas\nLanche saudável\nBrincadeiras ao ar livre",
        'verao_ilha_slot_afternoon_bullets' => "Recepção das crianças\nAtividades lúdicas\nLanche saudável\nBrincadeiras ao ar livre",
        'verao_ilha_meta_1_icon'          => '👩‍🏫',
        'verao_ilha_meta_1_name'           => 'Educadora + auxiliar',
        'verao_ilha_meta_2_icon'           => '🧒',
        'verao_ilha_meta_2_name'           => 'Dos 3 aos 5 anos',
        'verao_ilha_meta_3_icon'           => '👥',
        'verao_ilha_meta_3_name'           => 'Max. 16 criancas',
        'verao_ilha_cta_text'              => 'Enviar mensagem para mais informacoes',
        'verao_ilha_cta_url'               => '',
        'verao_ilha_pricing_section_title'    => 'Preços',
        'verao_ilha_pricing_section_subtitle' => 'Valores por semana e turno (manhã ou tarde).',
        'verao_ilha_pricing_icon_url'         => $uri . '/assets/images/verao-ilha/icon-calendario.svg',
        'verao_ilha_price_1_label'            => '1 semana / turno',
        'verao_ilha_price_1_amount'           => '125€',
        'verao_ilha_price_1_badge'            => '',
        'verao_ilha_price_2_label'            => '2 semanas / turno',
        'verao_ilha_price_2_amount'           => '240€',
        'verao_ilha_price_2_badge'            => '4% de desconto',
        'verao_ilha_price_3_label'            => '3 semanas / turno',
        'verao_ilha_price_3_amount'           => '345€',
        'verao_ilha_price_3_badge'            => '8% de desconto',
        'verao_ilha_price_4_label'            => '4 semanas / turno',
        'verao_ilha_price_4_amount'           => '450€',
        'verao_ilha_price_4_badge'            => '10% de desconto',
        'verao_ilha_pricing_note'             => 'Irmãos: 5% de desconto',
        'verao_ilha_faq_section_title'     => 'Perguntas frequentes',
        'verao_ilha_faq_section_subtitle'  => 'Respostas rapidas sobre o programa de verao.',
        'verao_ilha_faq_slot_count'        => '4',
        'verao_ilha_faq_1_question'        => 'Para que idades e o programa?',
        'verao_ilha_faq_1_answer'          => 'Para criancas dos 3 aos 5 anos.',
        'verao_ilha_faq_2_question'        => 'Ha opcao de dia completo?',
        'verao_ilha_faq_2_answer'          => 'Nao. Pode escolher o turno da manha ou da tarde.',
        'verao_ilha_faq_3_question'        => 'Quantas criancas ha por grupo?',
        'verao_ilha_faq_3_answer'          => 'No maximo 16 criancas por grupo, com educadora e auxiliar.',
        'verao_ilha_faq_4_question'        => 'Como peco mais informacoes ou inscricao?',
        'verao_ilha_faq_4_answer'          => 'Use o botao de contacto no final desta pagina ou a nossa pagina de contactos.',
        'verao_ilha_faq_5_question'        => '',
        'verao_ilha_faq_5_answer'          => '',
    );
}

/**
 * @param string $key Theme mod key (verao_ilha_*).
 */
function EventsHouse_verao_ilha_default($key)
{
    $map = EventsHouse_verao_ilha_defaults_map();

    return isset($map[$key]) ? $map[$key] : '';
}

/**
 * Valor gravado ou omissão (permite limpar um campo no admin — não reaplica o default).
 *
 * @param string $key Theme mod key (verao_ilha_*).
 */
function eh_verao_ilha_raw($key)
{
    $mod = get_theme_mod($key);
    if ($mod !== false) {
        return is_string($mod) ? $mod : (string) $mod;
    }
    $opt = get_option($key);
    if ($opt !== false && $opt !== null) {
        return (string) $opt;
    }

    return EventsHouse_verao_ilha_default($key);
}

/**
 * URL de recurso (ícone / ilustração / banner): URLs antigas ailha.pt nesse caminho devolvem 404 — usa o ficheiro local do tema.
 *
 * @param string $key Theme mod key (verao_ilha_*).
 */
function eh_verao_ilha_asset_url($key)
{
    $defaults = EventsHouse_verao_ilha_defaults_map();
    $fallback = isset($defaults[$key]) ? $defaults[$key] : '';
    $raw      = trim((string) eh_verao_ilha_raw($key));
    if ($raw === '') {
        return $fallback;
    }
    if (strpos($raw, 'ailha.pt/wp-content/uploads/2023/04') !== false) {
        return $fallback !== '' ? $fallback : $raw;
    }

    return $raw;
}

/**
 * Grava campos do formulário Conteúdos do Site.
 */
function EventsHouse_save_verao_ilha_page_from_post()
{
    $map = EventsHouse_verao_ilha_defaults_map();
    $st  = 'sanitize_text_field';
    $ta  = 'sanitize_textarea_field';

    $textarea_keys = array(
        'verao_ilha_hero_subtitle',
        'verao_ilha_feature_1_text',
        'verao_ilha_feature_2_text',
        'verao_ilha_feature_3_text',
        'verao_ilha_feature_4_text',
        'verao_ilha_features_section_subtitle',
        'verao_ilha_schedule_section_subtitle',
        'verao_ilha_meta_section_subtitle',
        'verao_ilha_cta_section_subtitle',
        'verao_ilha_pricing_section_subtitle',
        'verao_ilha_slot_morning_bullets',
        'verao_ilha_slot_afternoon_bullets',
        'verao_ilha_faq_section_subtitle',
    );

    for ($fi = 1; $fi <= 5; $fi++) {
        $textarea_keys[] = 'verao_ilha_faq_' . $fi . '_answer';
    }

    $url_keys = array(
        'verao_ilha_hero_btn_url',
        'verao_ilha_cta_url',
        'verao_ilha_hero_banner_url',
        'verao_ilha_feature_1_image_url',
        'verao_ilha_feature_2_image_url',
        'verao_ilha_feature_3_image_url',
        'verao_ilha_feature_4_image_url',
        'verao_ilha_schedule_morning_icon_url',
        'verao_ilha_schedule_afternoon_icon_url',
        'verao_ilha_schedule_morning_illustration_url',
        'verao_ilha_schedule_afternoon_illustration_url',
        'verao_ilha_pricing_icon_url',
    );

    foreach (array_keys($map) as $key) {
        if (!isset($_POST[$key])) {
            continue;
        }
        $raw = wp_unslash($_POST[$key]);

        if ($key === 'verao_ilha_faq_slot_count') {
            $value = (string) max(1, min(5, absint($raw)));
            set_theme_mod($key, $value);
            update_option($key, $value);
            continue;
        }

        if (in_array($key, $url_keys, true)) {
            $value = esc_url_raw($raw);
        } elseif (in_array($key, $textarea_keys, true)) {
            $value = $ta($raw);
        } else {
            $value = $st($raw);
        }

        set_theme_mod($key, $value);
        update_option($key, $value);
    }
}

/**
 * Renderiza o separador #tab-verao-ilha.
 */
function EventsHouse_render_admin_tab_verao_ilha()
{
    $d = 'EventsHouse_verao_ilha_default';
    ?>
    <div class="eh-tab-content" id="tab-verao-ilha">
        <h2><?php esc_html_e('Verão na Ilha — página promocional', 'EventsHouse'); ?></h2>
        <p class="description"><?php esc_html_e('Textos e imagens do template «Verao na Ilha». Guarde no fim do formulário principal.', 'EventsHouse'); ?></p>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Hero', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_badge"><?php esc_html_e('Badge', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_hero_badge" id="verao_ilha_hero_badge" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_badge', call_user_func($d, 'verao_ilha_hero_badge'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_title"><?php esc_html_e('Título (H1)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_hero_title" id="verao_ilha_hero_title" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_title', call_user_func($d, 'verao_ilha_hero_title'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_hero_subtitle" id="verao_ilha_hero_subtitle" class="large-text" rows="4"><?php echo esc_textarea(get_theme_mod('verao_ilha_hero_subtitle', call_user_func($d, 'verao_ilha_hero_subtitle'))); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_btn_text"><?php esc_html_e('Botão — texto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_hero_btn_text" id="verao_ilha_hero_btn_text" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_btn_text', call_user_func($d, 'verao_ilha_hero_btn_text'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_btn_url"><?php esc_html_e('Botão — link', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="url" class="large-text" name="verao_ilha_hero_btn_url" id="verao_ilha_hero_btn_url" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_btn_url', call_user_func($d, 'verao_ilha_hero_btn_url'))); ?>" placeholder="<?php echo esc_attr(home_url('/contato/')); ?>" />
                            <p class="description"><?php esc_html_e('Em branco usa o link para Contactos (/contato/).', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_banner_url"><?php esc_html_e('Imagem do banner (URL)', 'EventsHouse'); ?></label></th>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="url" class="large-text" name="verao_ilha_hero_banner_url" id="verao_ilha_hero_banner_url" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_banner_url', call_user_func($d, 'verao_ilha_hero_banner_url'))); ?>" placeholder="https://..." />
                                <button type="button" class="button eh-media-btn" data-target="verao_ilha_hero_banner_url"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_hero_banner_alt"><?php esc_html_e('Banner — texto alternativo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_hero_banner_alt" id="verao_ilha_hero_banner_alt" value="<?php echo esc_attr(get_theme_mod('verao_ilha_hero_banner_alt', call_user_func($d, 'verao_ilha_hero_banner_alt'))); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Grelha — 4 destaques', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Estes quatro cartões ficam na mesma área do hero (logo abaixo da linha texto + imagem). Cabeçalho opcional.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_features_section_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_features_section_title" id="verao_ilha_features_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_features_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_features_section_subtitle"><?php esc_html_e('Subtítulo da secção', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_features_section_subtitle" id="verao_ilha_features_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_features_section_subtitle')); ?></textarea></td>
                    </tr>
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(/* translators: %d: 1-4 */ __('Cartão %d', 'EventsHouse'), $i)); ?></strong></th></tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_feature_<?php echo (int) $i; ?>_image_url"><?php esc_html_e('Imagem (URL)', 'EventsHouse'); ?></label></th>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="url" class="large-text" name="verao_ilha_feature_<?php echo (int) $i; ?>_image_url" id="verao_ilha_feature_<?php echo (int) $i; ?>_image_url" value="<?php echo esc_attr(get_theme_mod('verao_ilha_feature_' . $i . '_image_url', call_user_func($d, 'verao_ilha_feature_' . $i . '_image_url'))); ?>" placeholder="https://..." />
                                <button type="button" class="button eh-media-btn" data-target="verao_ilha_feature_<?php echo (int) $i; ?>_image_url"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_feature_<?php echo (int) $i; ?>_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_feature_<?php echo (int) $i; ?>_title" id="verao_ilha_feature_<?php echo (int) $i; ?>_title" value="<?php echo esc_attr(get_theme_mod('verao_ilha_feature_' . $i . '_title', call_user_func($d, 'verao_ilha_feature_' . $i . '_title'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_feature_<?php echo (int) $i; ?>_text"><?php esc_html_e('Texto', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_feature_<?php echo (int) $i; ?>_text" id="verao_ilha_feature_<?php echo (int) $i; ?>_text" class="large-text" rows="2"><?php echo esc_textarea(get_theme_mod('verao_ilha_feature_' . $i . '_text', call_user_func($d, 'verao_ilha_feature_' . $i . '_text'))); ?></textarea></td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Horários e nota', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_section_title"><?php esc_html_e('Título da secção (acima dos turnos)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_schedule_section_title" id="verao_ilha_schedule_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_schedule_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_section_subtitle"><?php esc_html_e('Subtítulo da secção', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_schedule_section_subtitle" id="verao_ilha_schedule_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_schedule_section_subtitle')); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_morning_label"><?php esc_html_e('Manhã — etiqueta', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_slot_morning_label" id="verao_ilha_slot_morning_label" value="<?php echo esc_attr(get_theme_mod('verao_ilha_slot_morning_label', call_user_func($d, 'verao_ilha_slot_morning_label'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_morning_time"><?php esc_html_e('Manhã — horário', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_slot_morning_time" id="verao_ilha_slot_morning_time" value="<?php echo esc_attr(get_theme_mod('verao_ilha_slot_morning_time', call_user_func($d, 'verao_ilha_slot_morning_time'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_afternoon_label"><?php esc_html_e('Tarde — etiqueta', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_slot_afternoon_label" id="verao_ilha_slot_afternoon_label" value="<?php echo esc_attr(get_theme_mod('verao_ilha_slot_afternoon_label', call_user_func($d, 'verao_ilha_slot_afternoon_label'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_afternoon_time"><?php esc_html_e('Tarde — horário', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_slot_afternoon_time" id="verao_ilha_slot_afternoon_time" value="<?php echo esc_attr(get_theme_mod('verao_ilha_slot_afternoon_time', call_user_func($d, 'verao_ilha_slot_afternoon_time'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_morning_icon_url"><?php esc_html_e('Manhã — ícone (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="verao_ilha_schedule_morning_icon_url" id="verao_ilha_schedule_morning_icon_url" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_schedule_morning_icon_url')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_afternoon_icon_url"><?php esc_html_e('Tarde — ícone (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="verao_ilha_schedule_afternoon_icon_url" id="verao_ilha_schedule_afternoon_icon_url" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_schedule_afternoon_icon_url')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_morning_illustration_url"><?php esc_html_e('Manhã — ilustração canto (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="verao_ilha_schedule_morning_illustration_url" id="verao_ilha_schedule_morning_illustration_url" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_schedule_morning_illustration_url')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_afternoon_illustration_url"><?php esc_html_e('Tarde — ilustração canto (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="verao_ilha_schedule_afternoon_illustration_url" id="verao_ilha_schedule_afternoon_illustration_url" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_schedule_afternoon_illustration_url')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_schedule_note"><?php esc_html_e('Nota abaixo do calendário', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_schedule_note" id="verao_ilha_schedule_note" value="<?php echo esc_attr(get_theme_mod('verao_ilha_schedule_note', call_user_func($d, 'verao_ilha_schedule_note'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_morning_bullets"><?php esc_html_e('Manhã — lista (uma linha por item)', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_slot_morning_bullets" id="verao_ilha_slot_morning_bullets" class="large-text" rows="5"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_slot_morning_bullets')); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_slot_afternoon_bullets"><?php esc_html_e('Tarde — lista (uma linha por item)', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_slot_afternoon_bullets" id="verao_ilha_slot_afternoon_bullets" class="large-text" rows="5"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_slot_afternoon_bullets')); ?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Três cartões (faixa branca)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_meta_section_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_meta_section_title" id="verao_ilha_meta_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_meta_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_meta_section_subtitle"><?php esc_html_e('Subtítulo da secção', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_meta_section_subtitle" id="verao_ilha_meta_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_meta_section_subtitle')); ?></textarea></td>
                    </tr>
                    <?php for ($m = 1; $m <= 3; $m++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(/* translators: %d: 1-3 */ __('Cartão %d', 'EventsHouse'), $m)); ?></strong></th></tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_meta_<?php echo (int) $m; ?>_icon"><?php esc_html_e('Ícone (emoji)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="small-text" name="verao_ilha_meta_<?php echo (int) $m; ?>_icon" id="verao_ilha_meta_<?php echo (int) $m; ?>_icon" value="<?php echo esc_attr(get_theme_mod('verao_ilha_meta_' . $m . '_icon', call_user_func($d, 'verao_ilha_meta_' . $m . '_icon'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_meta_<?php echo (int) $m; ?>_name"><?php esc_html_e('Texto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_meta_<?php echo (int) $m; ?>_name" id="verao_ilha_meta_<?php echo (int) $m; ?>_name" value="<?php echo esc_attr(get_theme_mod('verao_ilha_meta_' . $m . '_name', call_user_func($d, 'verao_ilha_meta_' . $m . '_name'))); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Botão final', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_cta_section_title"><?php esc_html_e('Título acima do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_cta_section_title" id="verao_ilha_cta_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_cta_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_cta_section_subtitle"><?php esc_html_e('Subtítulo acima do botão', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_cta_section_subtitle" id="verao_ilha_cta_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_cta_section_subtitle')); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_cta_text"><?php esc_html_e('Texto do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_cta_text" id="verao_ilha_cta_text" value="<?php echo esc_attr(get_theme_mod('verao_ilha_cta_text', call_user_func($d, 'verao_ilha_cta_text'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_cta_url"><?php esc_html_e('Link do botão', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="url" class="large-text" name="verao_ilha_cta_url" id="verao_ilha_cta_url" value="<?php echo esc_attr(get_theme_mod('verao_ilha_cta_url', call_user_func($d, 'verao_ilha_cta_url'))); ?>" placeholder="<?php echo esc_attr(home_url('/contato/')); ?>" />
                            <p class="description"><?php esc_html_e('Em branco usa o link para Contactos (/contato/).', 'EventsHouse'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Preços (antes do FAQ)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_pricing_section_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_pricing_section_title" id="verao_ilha_pricing_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_pricing_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_pricing_section_subtitle"><?php esc_html_e('Subtítulo da secção', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_pricing_section_subtitle" id="verao_ilha_pricing_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_pricing_section_subtitle')); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_pricing_icon_url"><?php esc_html_e('Ícone dos cartões (URL)', 'EventsHouse'); ?></label></th>
                        <td>
                            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                                <input type="url" class="large-text" name="verao_ilha_pricing_icon_url" id="verao_ilha_pricing_icon_url" value="<?php echo esc_attr(get_theme_mod('verao_ilha_pricing_icon_url', call_user_func($d, 'verao_ilha_pricing_icon_url'))); ?>" placeholder="https://..." />
                                <button type="button" class="button eh-media-btn" data-target="verao_ilha_pricing_icon_url"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            </div>
                        </td>
                    </tr>
                    <?php for ($pi = 1; $pi <= 4; $pi++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(/* translators: %d: 1-4 */ __('Plano %d', 'EventsHouse'), $pi)); ?></strong></th></tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_price_<?php echo (int) $pi; ?>_label"><?php esc_html_e('Etiqueta', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_price_<?php echo (int) $pi; ?>_label" id="verao_ilha_price_<?php echo (int) $pi; ?>_label" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_price_' . $pi . '_label')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_price_<?php echo (int) $pi; ?>_amount"><?php esc_html_e('Preço', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_price_<?php echo (int) $pi; ?>_amount" id="verao_ilha_price_<?php echo (int) $pi; ?>_amount" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_price_' . $pi . '_amount')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_price_<?php echo (int) $pi; ?>_badge"><?php esc_html_e('Nota (ex.: desconto)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_price_<?php echo (int) $pi; ?>_badge" id="verao_ilha_price_<?php echo (int) $pi; ?>_badge" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_price_' . $pi . '_badge')); ?>" placeholder="<?php esc_attr_e('Opcional', 'EventsHouse'); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                    <tr>
                        <th scope="row"><label for="verao_ilha_pricing_note"><?php esc_html_e('Nota no rodapé', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_pricing_note" id="verao_ilha_pricing_note" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_pricing_note')); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('FAQ (final da página)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Até 5 perguntas. Use «Adicionar pergunta» para mostrar mais um campo (máx. 5) e «Remover» para limpar a linha; ao remover a última linha visível, o número de campos diminui.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="verao_ilha_faq_section_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="verao_ilha_faq_section_title" id="verao_ilha_faq_section_title" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_faq_section_title')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="verao_ilha_faq_section_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><textarea name="verao_ilha_faq_section_subtitle" id="verao_ilha_faq_section_subtitle" class="large-text" rows="2"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_faq_section_subtitle')); ?></textarea></td>
                    </tr>
                </table>
                <?php
                $faq_slots = (int) eh_verao_ilha_raw('verao_ilha_faq_slot_count');
                if ($faq_slots < 1 || $faq_slots > 5) {
                    $faq_slots = 4;
                }
                ?>
                <input type="hidden" name="verao_ilha_faq_slot_count" id="verao_ilha_faq_slot_count" value="<?php echo esc_attr((string) $faq_slots); ?>" />
                <div id="verao-ilha-faq-admin-rows" style="margin-top:1em;">
                    <?php for ($fq = 1; $fq <= 5; $fq++) : ?>
                    <div class="verao-ilha-faq-admin-row" data-slot="<?php echo (int) $fq; ?>"<?php echo $fq > $faq_slots ? ' hidden' : ''; ?> style="border:1px solid #ccd0d4;border-radius:4px;padding:12px 14px;margin-bottom:12px;background:#fcfcfc;">
                        <p style="margin:0 0 8px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                            <strong><?php echo esc_html(sprintf(/* translators: %d: 1-5 */ __('Pergunta %d', 'EventsHouse'), $fq)); ?></strong>
                            <button type="button" class="button-link-delete verao-ilha-faq-remove" data-slot="<?php echo (int) $fq; ?>"><?php esc_html_e('Remover', 'EventsHouse'); ?></button>
                        </p>
                        <p style="margin:0 0 8px;">
                            <label class="screen-reader-text" for="verao_ilha_faq_<?php echo (int) $fq; ?>_question"><?php echo esc_html(sprintf(__('Texto da pergunta %d', 'EventsHouse'), $fq)); ?></label>
                            <input type="text" class="large-text" name="verao_ilha_faq_<?php echo (int) $fq; ?>_question" id="verao_ilha_faq_<?php echo (int) $fq; ?>_question" value="<?php echo esc_attr(eh_verao_ilha_raw('verao_ilha_faq_' . $fq . '_question')); ?>" placeholder="<?php esc_attr_e('Pergunta…', 'EventsHouse'); ?>" />
                        </p>
                        <p style="margin:0;">
                            <label for="verao_ilha_faq_<?php echo (int) $fq; ?>_answer"><strong><?php esc_html_e('Resposta', 'EventsHouse'); ?></strong></label><br />
                            <textarea name="verao_ilha_faq_<?php echo (int) $fq; ?>_answer" id="verao_ilha_faq_<?php echo (int) $fq; ?>_answer" class="large-text" rows="3" placeholder="<?php esc_attr_e('Resposta…', 'EventsHouse'); ?>"><?php echo esc_textarea(eh_verao_ilha_raw('verao_ilha_faq_' . $fq . '_answer')); ?></textarea>
                        </p>
                    </div>
                    <?php endfor; ?>
                </div>
                <p style="margin-top:4px;">
                    <button type="button" class="button button-secondary" id="verao-ilha-faq-add"><?php esc_html_e('Adicionar pergunta', 'EventsHouse'); ?></button>
                </p>
                <script>
                (function () {
                    var max = 5;
                    var wrap = document.getElementById('verao-ilha-faq-admin-rows');
                    var countInput = document.getElementById('verao_ilha_faq_slot_count');
                    if (!wrap || !countInput) return;
                    function visibleCount() {
                        var n = parseInt(countInput.value, 10);
                        if (isNaN(n) || n < 1) n = 1;
                        if (n > max) n = max;
                        return n;
                    }
                    function setVisibleCount(n) {
                        n = Math.max(1, Math.min(max, n));
                        countInput.value = String(n);
                        for (var i = 1; i <= max; i++) {
                            var row = wrap.querySelector('.verao-ilha-faq-admin-row[data-slot="' + i + '"]');
                            if (row) row.hidden = i > n;
                        }
                        var addBtn = document.getElementById('verao-ilha-faq-add');
                        if (addBtn) addBtn.disabled = n >= max;
                    }
                    wrap.addEventListener('click', function (e) {
                        var btn = e.target.closest && e.target.closest('.verao-ilha-faq-remove');
                        if (!btn) return;
                        var slot = parseInt(btn.getAttribute('data-slot'), 10);
                        if (isNaN(slot)) return;
                        var q = document.getElementById('verao_ilha_faq_' + slot + '_question');
                        var a = document.getElementById('verao_ilha_faq_' + slot + '_answer');
                        if (q) q.value = '';
                        if (a) a.value = '';
                        var n = visibleCount();
                        if (slot === n && n > 1) setVisibleCount(n - 1);
                    });
                    var addBtn = document.getElementById('verao-ilha-faq-add');
                    if (addBtn) {
                        addBtn.addEventListener('click', function () {
                            var n = visibleCount();
                            if (n < max) setVisibleCount(n + 1);
                        });
                        addBtn.disabled = visibleCount() >= max;
                    }
                })();
                </script>
            </div>
        </div>
    </div>
    <?php
}
