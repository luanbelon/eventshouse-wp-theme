<?php
/**
 * Textos editáveis da ficha «Produto Evento» (EventsThemes → Produto Evento).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chave da opção em wp_options.
 *
 * @return string
 */
function EventsHouse_event_ui_labels_option_key()
{
    return 'eventshouse_event_ui_labels';
}

/**
 * Valores por omissão (tradução incluída).
 *
 * @return array<string, string>
 */
function EventsHouse_event_ui_defaults()
{
    return array(
        'about_heading'             => __('Sobre o evento', 'EventsHouse'),
        'tagline'                   => __('Inscrição · 1 adulto + 1 criança incluídos', 'EventsHouse'),
        'datetime_label'            => __('Data e hora', 'EventsHouse'),
        'price_base_label'          => __('Preço da inscrição base', 'EventsHouse'),
        'price_base_hint'           => __('(1 adulto + 1 criança)', 'EventsHouse'),
        'column_extra_price_format' => __('Cada participante extra: + %s', 'EventsHouse'),
        'total_estimated_label'     => __('Total estimado', 'EventsHouse'),
        'enroll_button'             => __('Inscrever-se', 'EventsHouse'),
        'form_section_title'        => __('Formulário de inscrição', 'EventsHouse'),
        'back_link'                 => __('Voltar', 'EventsHouse'),
        'form_extra_price_format'   => __('Cada participante extra (criança ou adulto): + %s €', 'EventsHouse'),
        'form_extra_free_text'      => __('Não há custo adicional por participante extra neste evento.', 'EventsHouse'),
        'extra_max_format'          => __('Máximo: %d', 'EventsHouse'),
        'aria_extra_minus'          => __('Menos um participante extra', 'EventsHouse'),
        'aria_extra_plus'           => __('Mais um participante extra', 'EventsHouse'),
        'label_child_name'          => __('Nome da criança', 'EventsHouse'),
        'label_child_dob'           => __('Data de nascimento', 'EventsHouse'),
        'label_adult_name'          => __('Nome do adulto', 'EventsHouse'),
        'label_email'               => __('Email', 'EventsHouse'),
        'label_phone'               => __('Telefone', 'EventsHouse'),
        'label_extra_participants'  => __('Participantes extra', 'EventsHouse'),
        'label_photo_consent'       => __('Permite a partilha de imagens nas redes sociais e newsletter da Ilha?', 'EventsHouse'),
        'hint_photo_consent'        => __('Iremos sempre tapar o rosto dos bebés/crianças.', 'EventsHouse'),
        'label_yes'                 => __('Sim', 'EventsHouse'),
        'label_no'                  => __('Não', 'EventsHouse'),
        'add_to_cart_button'        => __('Adicionar ao carrinho', 'EventsHouse'),
        'catalog_extra_price_format' => __('+ %s € por cada participante extra (opcional na ficha do produto).', 'EventsHouse'),
        'js_extra_child'            => __('Criança', 'EventsHouse'),
        'js_extra_adult'            => __('Adulto', 'EventsHouse'),
        'js_participant_name'       => __('Nome', 'EventsHouse'),
        'js_participant_dob'        => __('Data de nascimento', 'EventsHouse'),
        'js_participant_heading'    => __('Participante extra', 'EventsHouse'),
        'js_extra_free'             => __('Gratuito', 'EventsHouse'),
        'label_base_participants'   => __('Participantes incluídos na inscrição', 'EventsHouse'),
        'label_base_adult_n'        => __('Adulto %d', 'EventsHouse'),
        'label_base_child_n'        => __('Criança %d — nome', 'EventsHouse'),
        'form_extra_prices_hint'    => __('Adulto extra e criança extra têm preços distintos; crianças até à idade configurada são gratuitas.', 'EventsHouse'),
        'price_table_title'         => __('Valores', 'EventsHouse'),
        'price_table_base'          => __('Inscrição base', 'EventsHouse'),
        'price_table_adult_extra'   => __('Adulto', 'EventsHouse'),
        'price_table_child_extra'   => __('Criança', 'EventsHouse'),
        'price_table_child_range'   => __('Crianças dos %1$d aos %2$d anos', 'EventsHouse'),
        'price_table_child_free'    => __('Até aos %d anos', 'EventsHouse'),
        'price_table_free'          => __('Gratuito', 'EventsHouse'),
        'info_day_label'            => __('Dia', 'EventsHouse'),
        'info_time_label'           => __('Horário', 'EventsHouse'),
        'info_location_label'       => __('Local', 'EventsHouse'),
        'err_no_selection'          => __('Seleccione pelo menos um bilhete.', 'EventsHouse'),
        'cart_qty_adult'            => __('Adultos: %d', 'EventsHouse'),
        'cart_qty_child'            => __('Crianças: %d', 'EventsHouse'),
        'cart_qty_child_free'       => __('Até aos %1$d anos: %2$d', 'EventsHouse'),
    );
}

/**
 * Textos efetivos (guardados não vazios sobrepõem a omissão).
 *
 * @return array<string, string>
 */
function EventsHouse_get_event_ui_strings()
{
    $defaults = EventsHouse_event_ui_defaults();
    $saved    = get_option(EventsHouse_event_ui_labels_option_key(), array());
    if (!is_array($saved)) {
        $saved = array();
    }
    $out = array();
    $legacy_label_map = array(
        'price_table_adult_extra' => array(
            'Adultos extra' => 'Adulto',
            'Adulto extra'  => 'Adulto',
            'Adultos'       => 'Adulto',
        ),
        'price_table_child_extra' => array(
            'Crianças extra' => 'Criança',
            'Criança extra'  => 'Criança',
            'Crianças'       => 'Criança',
        ),
        'js_extra_adult' => array(
            'Adultos extra' => 'Adulto',
            'Adulto extra'  => 'Adulto',
            'Adultos'       => 'Adulto',
        ),
        'js_extra_child' => array(
            'Crianças extra' => 'Criança',
            'Criança extra'  => 'Criança',
            'Crianças'       => 'Criança',
        ),
    );

    foreach ($defaults as $key => $def) {
        if (isset($saved[$key]) && is_string($saved[$key]) && $saved[$key] !== '') {
            $value = $saved[$key];
            if (isset($legacy_label_map[$key][$value])) {
                $value = $legacy_label_map[$key][$value];
            }
            $out[$key] = $value;
        } else {
            $out[$key] = $def;
        }
    }
    return $out;
}

/**
 * Sanitiza um campo guardado no painel.
 *
 * @param string $key   Chave.
 * @param mixed  $value Valor cru.
 * @return string
 */
function EventsHouse_event_ui_sanitize_field($key, $value)
{
    $textareas = array('hint_photo_consent', 'label_photo_consent', 'form_extra_free_text');
    $raw       = is_string($value) ? $value : '';
    if (in_array($key, $textareas, true)) {
        return sanitize_textarea_field(wp_unslash($raw));
    }
    return sanitize_text_field(wp_unslash($raw));
}

/**
 * Ecrã EventsThemes — Produto Evento.
 *
 * @param array<string, mixed> $cfg Config da página.
 */
function EventsHouse_events_themes_render_event_product_labels_page(array $cfg)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['eventshouse_event_ui_save']) && check_admin_referer('eventshouse_event_ui_page', 'eventshouse_event_ui_nonce')) {
        $in = array();
        if (isset($_POST['eventshouse_event_ui']) && is_array($_POST['eventshouse_event_ui'])) {
            $in = wp_unslash($_POST['eventshouse_event_ui']);
        }
        $clean = array();
        foreach (array_keys(EventsHouse_event_ui_defaults()) as $key) {
            $clean[$key] = isset($in[$key]) ? EventsHouse_event_ui_sanitize_field($key, $in[$key]) : '';
        }
        update_option(EventsHouse_event_ui_labels_option_key(), $clean);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Definições guardadas.', 'EventsHouse') . '</p></div>';
    }

    $v = EventsHouse_get_event_ui_strings();
    ?>
    <div class="wrap events-themes-event-product-ui">
        <h1><?php echo esc_html(sprintf(__('Editar: %s', 'EventsHouse'), $cfg['label'])); ?></h1>
        <p class="description"><?php echo esc_html($cfg['description']); ?></p>
        <p class="description">
            <?php esc_html_e('Em várias linhas pode usar %s (preço formatado ou número) ou %d (quantidade máxima), conforme indicado ao lado de cada campo.', 'EventsHouse'); ?>
        </p>
        <p>
            <a href="<?php echo esc_url($cfg['preview_url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir loja no site ↗', 'EventsHouse'); ?></a>
        </p>

        <form method="post" action="">
            <?php wp_nonce_field('eventshouse_event_ui_page', 'eventshouse_event_ui_nonce'); ?>

            <h2 class="title"><?php esc_html_e('Coluna «Sobre» e cabeçalho', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_tagline"><?php esc_html_e('Subtítulo (abaixo do título do produto)', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_tagline" name="eventshouse_event_ui[tagline]" value="<?php echo esc_attr($v['tagline']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_about_heading"><?php esc_html_e('Título «Sobre o evento»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_about_heading" name="eventshouse_event_ui[about_heading]" value="<?php echo esc_attr($v['about_heading']); ?>" /></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Coluna de preços e data', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_datetime_label"><?php esc_html_e('Rótulo da caixa de data e hora', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_datetime_label" name="eventshouse_event_ui[datetime_label]" value="<?php echo esc_attr($v['datetime_label']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_price_base_label"><?php esc_html_e('Rótulo do preço base', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_price_base_label" name="eventshouse_event_ui[price_base_label]" value="<?php echo esc_attr($v['price_base_label']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_price_base_hint"><?php esc_html_e('Nota ao lado do preço base', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_price_base_hint" name="eventshouse_event_ui[price_base_hint]" value="<?php echo esc_attr($v['price_base_hint']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_column_extra"><?php esc_html_e('Linha «participante extra» na caixa de preços', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" class="large-text" id="eh_evu_column_extra" name="eventshouse_event_ui[column_extra_price_format]" value="<?php echo esc_attr($v['column_extra_price_format']); ?>" />
                        <p class="description"><?php esc_html_e('Use %s uma vez — será substituído pelo preço formatado (HTML).', 'EventsHouse'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_total_est"><?php esc_html_e('Etiqueta «Total estimado»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_total_est" name="eventshouse_event_ui[total_estimated_label]" value="<?php echo esc_attr($v['total_estimated_label']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_enroll"><?php esc_html_e('Botão «Inscrever-se»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_enroll" name="eventshouse_event_ui[enroll_button]" value="<?php echo esc_attr($v['enroll_button']); ?>" /></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Secção do formulário', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_form_sec"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_form_sec" name="eventshouse_event_ui[form_section_title]" value="<?php echo esc_attr($v['form_section_title']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_back"><?php esc_html_e('Ligação «Voltar»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_back" name="eventshouse_event_ui[back_link]" value="<?php echo esc_attr($v['back_link']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_atc"><?php esc_html_e('Botão «Adicionar ao carrinho»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_atc" name="eventshouse_event_ui[add_to_cart_button]" value="<?php echo esc_attr($v['add_to_cart_button']); ?>" /></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Campos do formulário', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_ln"><?php esc_html_e('Nome da criança', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_ln" name="eventshouse_event_ui[label_child_name]" value="<?php echo esc_attr($v['label_child_name']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_ld"><?php esc_html_e('Data de nascimento (criança)', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_ld" name="eventshouse_event_ui[label_child_dob]" value="<?php echo esc_attr($v['label_child_dob']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_la"><?php esc_html_e('Nome do adulto', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_la" name="eventshouse_event_ui[label_adult_name]" value="<?php echo esc_attr($v['label_adult_name']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_le"><?php esc_html_e('Email', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_le" name="eventshouse_event_ui[label_email]" value="<?php echo esc_attr($v['label_email']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_lp"><?php esc_html_e('Telefone', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_lp" name="eventshouse_event_ui[label_phone]" value="<?php echo esc_attr($v['label_phone']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_lex"><?php esc_html_e('Participantes extra', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_lex" name="eventshouse_event_ui[label_extra_participants]" value="<?php echo esc_attr($v['label_extra_participants']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_fxp"><?php esc_html_e('Texto quando há taxa por extra (%s = valor)', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" class="large-text" id="eh_evu_fxp" name="eventshouse_event_ui[form_extra_price_format]" value="<?php echo esc_attr($v['form_extra_price_format']); ?>" />
                        <p class="description"><?php esc_html_e('Ex.: valor «7» aparece como número já localizado antes do símbolo € no texto.', 'EventsHouse'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_fxf"><?php esc_html_e('Texto quando não há taxa por extra', 'EventsHouse'); ?></label></th>
                    <td><textarea class="large-text" rows="2" id="eh_evu_fxf" name="eventshouse_event_ui[form_extra_free_text]"><?php echo esc_textarea($v['form_extra_free_text']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_mx"><?php esc_html_e('Texto «Máximo» no seletor (%d)', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_mx" name="eventshouse_event_ui[extra_max_format]" value="<?php echo esc_attr($v['extra_max_format']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_am"><?php esc_html_e('Aria-label botão menos', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_am" name="eventshouse_event_ui[aria_extra_minus]" value="<?php echo esc_attr($v['aria_extra_minus']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_ap"><?php esc_html_e('Aria-label botão mais', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_ap" name="eventshouse_event_ui[aria_extra_plus]" value="<?php echo esc_attr($v['aria_extra_plus']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_pc"><?php esc_html_e('Consentimento de imagens — pergunta', 'EventsHouse'); ?></label></th>
                    <td><textarea class="large-text" rows="2" id="eh_evu_pc" name="eventshouse_event_ui[label_photo_consent]"><?php echo esc_textarea($v['label_photo_consent']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_pch"><?php esc_html_e('Consentimento — nota', 'EventsHouse'); ?></label></th>
                    <td><textarea class="large-text" rows="2" id="eh_evu_pch" name="eventshouse_event_ui[hint_photo_consent]"><?php echo esc_textarea($v['hint_photo_consent']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_yes"><?php esc_html_e('«Sim»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="regular-text" id="eh_evu_yes" name="eventshouse_event_ui[label_yes]" value="<?php echo esc_attr($v['label_yes']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_no"><?php esc_html_e('«Não»', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="regular-text" id="eh_evu_no" name="eventshouse_event_ui[label_no]" value="<?php echo esc_attr($v['label_no']); ?>" /></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Cartões «Participante extra» (dinâmicos)', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_jc"><?php esc_html_e('Criança extra', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_jc" name="eventshouse_event_ui[js_extra_child]" value="<?php echo esc_attr($v['js_extra_child']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_ja"><?php esc_html_e('Adulto extra', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_ja" name="eventshouse_event_ui[js_extra_adult]" value="<?php echo esc_attr($v['js_extra_adult']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_jn"><?php esc_html_e('Nome', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_jn" name="eventshouse_event_ui[js_participant_name]" value="<?php echo esc_attr($v['js_participant_name']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_jd"><?php esc_html_e('Data de nascimento', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_jd" name="eventshouse_event_ui[js_participant_dob]" value="<?php echo esc_attr($v['js_participant_dob']); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="eh_evu_jp"><?php esc_html_e('Título do cartão (antes do número)', 'EventsHouse'); ?></label></th>
                    <td><input type="text" class="large-text" id="eh_evu_jp" name="eventshouse_event_ui[js_participant_heading]" value="<?php echo esc_attr($v['js_participant_heading']); ?>" /></td>
                </tr>
            </table>

            <h2 class="title"><?php esc_html_e('Listagens e catálogo', 'EventsHouse'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="eh_evu_cat"><?php esc_html_e('Nota junto ao preço (loja/arquivos)', 'EventsHouse'); ?></label></th>
                    <td>
                        <input type="text" class="large-text" id="eh_evu_cat" name="eventshouse_event_ui[catalog_extra_price_format]" value="<?php echo esc_attr($v['catalog_extra_price_format']); ?>" />
                        <p class="description"><?php esc_html_e('Use %s para o valor numérico localizado (€ incluído na frase por omissão).', 'EventsHouse'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="eventshouse_event_ui_save" class="button button-primary" value="1"><?php esc_html_e('Guardar', 'EventsHouse'); ?></button>
            </p>
        </form>
    </div>
    <?php
}
