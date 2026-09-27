<?php
/**
 * Formulário modular na ficha do produto event.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param WC_Product $product Produto.
 */
function EventsHouse_wc_event_before_add_to_cart_button()
{
    global $product;
    if (!EventsHouse_wc_is_event_product($product)) {
        return;
    }

    $config = EventsHouse_event_get_config($product);
    $ui     = EventsHouse_get_event_ui_strings();
    $base   = $config['base'] ?? array();
    $n_adults = min(5, max(0, absint($base['adults'] ?? 0)));
    $n_children = min(5, max(0, absint($base['children'] ?? 0)));
    $max_extras = EventsHouse_wc_event_get_max_extras($product);
    $adult_extra = EventsHouse_wc_event_get_adult_extra_price($product);
    $child_extra = EventsHouse_wc_event_get_child_extra_price($product);
    $free_age = absint($config['pricing']['child_free_max_age'] ?? 3);
    $extras_enabled = !empty($config['extras']['enabled']) && $max_extras > 0;

    ?>
    <div class="eh-wc-event-fields" data-event-date="<?php echo esc_attr(EventsHouse_event_get_event_date($product)); ?>">
        <?php if ($n_adults > 0 || $n_children > 0) : ?>
            <div class="eh-wc-event-base-participants">
                <span class="eh-wc-event-label"><?php echo esc_html($ui['label_base_participants'] ?? __('Participantes incluídos na inscrição', 'EventsHouse')); ?></span>
                <?php for ($i = 0; $i < $n_adults; $i++) : ?>
                    <div class="eh-wc-event-field eh-wc-event-field--full">
                        <label class="eh-wc-event-label" for="eh_evt_base_adult_<?php echo (int) $i; ?>">
                            <?php echo esc_html(sprintf($ui['label_base_adult_n'] ?? __('Adulto %d', 'EventsHouse'), $i + 1)); ?> *
                        </label>
                        <input type="text" class="input-text eh-wc-event-input" name="eh_evt_base_adults[<?php echo (int) $i; ?>][name]" id="eh_evt_base_adult_<?php echo (int) $i; ?>" required maxlength="255" data-eh-field="eh_evt_base_adults_<?php echo (int) $i; ?>" />
                    </div>
                <?php endfor; ?>
                <?php for ($i = 0; $i < $n_children; $i++) : ?>
                    <div class="eh-wc-event-form-row eh-wc-event-form-row--2col">
                        <div class="eh-wc-event-field">
                            <label class="eh-wc-event-label" for="eh_evt_base_child_name_<?php echo (int) $i; ?>">
                                <?php echo esc_html(sprintf($ui['label_base_child_n'] ?? __('Criança %d — nome', 'EventsHouse'), $i + 1)); ?> *
                            </label>
                            <input type="text" class="input-text eh-wc-event-input" name="eh_evt_base_children[<?php echo (int) $i; ?>][name]" id="eh_evt_base_child_name_<?php echo (int) $i; ?>" required maxlength="255" data-eh-field="eh_evt_base_children_<?php echo (int) $i; ?>_name" />
                        </div>
                        <div class="eh-wc-event-field">
                            <label class="eh-wc-event-label" for="eh_evt_base_child_dob_<?php echo (int) $i; ?>">
                                <?php echo esc_html($ui['label_child_dob']); ?> *
                            </label>
                            <input type="date" class="input-text eh-wc-event-input" name="eh_evt_base_children[<?php echo (int) $i; ?>][dob]" id="eh_evt_base_child_dob_<?php echo (int) $i; ?>" required data-eh-field="eh_evt_base_children_<?php echo (int) $i; ?>_dob" />
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php EventsHouse_event_render_custom_fields($config); ?>

        <?php if ($extras_enabled) : ?>
            <div class="eh-event-extra-block">
                <span class="eh-wc-event-label eh-event-extra-block__label"><?php echo esc_html($ui['label_extra_participants']); ?></span>
                <p class="eh-event-extra-block__hint description">
                    <?php
                    if ($adult_extra > 0 || $child_extra > 0) {
                        echo esc_html($ui['form_extra_prices_hint'] ?? __('Adulto extra e criança extra têm preços distintos; crianças até à idade configurada são gratuitas.', 'EventsHouse'));
                    } else {
                        echo esc_html($ui['form_extra_free_text']);
                    }
                    ?>
                </p>
                <div class="eh-event-extra-stepper">
                    <button type="button" class="button eh-event-extra-stepper__btn eh-event-extra-stepper__btn--minus" aria-label="<?php echo esc_attr($ui['aria_extra_minus']); ?>">−</button>
                    <span class="eh-event-extra-stepper__value">0</span>
                    <button type="button" class="button eh-event-extra-stepper__btn eh-event-extra-stepper__btn--plus" aria-label="<?php echo esc_attr($ui['aria_extra_plus']); ?>">+</button>
                    <input type="hidden" name="eh_evt_extra_count" id="eh_evt_extra_count" value="0" />
                    <span class="eh-event-extra-stepper__max description">
                        <?php echo esc_html(sprintf($ui['extra_max_format'], $max_extras)); ?>
                    </span>
                </div>
            </div>
            <div id="ehWcEventExtraCards" class="eh-wc-event-extra-cards" style="display:none;" aria-live="polite"
                data-adult-extra="<?php echo esc_attr((string) $adult_extra); ?>"
                data-child-extra="<?php echo esc_attr((string) $child_extra); ?>"
                data-free-max-age="<?php echo esc_attr((string) $free_age); ?>"
            ></div>
        <?php endif; ?>
    </div>
    <?php
}
if (function_exists('EventsHouse_event_registration_form_enabled') && EventsHouse_event_registration_form_enabled()) {
    add_action('woocommerce_event_before_add_to_cart_button', 'EventsHouse_wc_event_before_add_to_cart_button', 5);
}

/**
 * @param array<string, mixed> $config Config.
 */
function EventsHouse_event_render_custom_fields(array $config)
{
    $fields = EventsHouse_event_get_enabled_fields($config);
    if ($fields === array()) {
        return;
    }
    echo '<div class="eh-wc-event-custom-fields">';
    foreach ($fields as $field) {
        EventsHouse_event_render_one_field($field);
    }
    echo '</div>';
}

/**
 * @param array<string, mixed> $field Campo.
 */
function EventsHouse_event_render_one_field(array $field)
{
    $id = $field['id'] ?? '';
    if ($id === '') {
        return;
    }
    $type = $field['type'] ?? 'text';
    $label = $field['label'] ?? $id;
    $required = !empty($field['required']);
    $hint = $field['hint'] ?? '';
    $name = 'eh_evt_custom[' . esc_attr($id) . ']';
    $show_when = $field['show_when'] ?? null;
    $attrs = 'class="eh-wc-event-field eh-wc-event-custom-field" data-eh-field-id="' . esc_attr($id) . '"';
    if (is_array($show_when) && !empty($show_when['field'])) {
        $attrs .= ' data-show-when="' . esc_attr(wp_json_encode($show_when)) . '" style="display:none;"';
    }

    echo '<div ' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    if ($type === 'radio') {
        echo '<span class="eh-wc-event-label eh-wc-event-consent__question">' . esc_html($label);
        if ($required) {
            echo ' *';
        }
        echo '</span>';
        if ($hint !== '') {
            echo '<span class="eh-wc-event-consent__hint description">' . esc_html($hint) . '</span>';
        }
        foreach ($field['options'] ?? array() as $opt) {
            $val = $opt['value'] ?? '';
            $lab = $opt['label'] ?? $val;
            echo '<label><input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($val) . '" data-eh-field="' . esc_attr($id) . '"';
            if ($required) {
                echo ' required';
            }
            echo ' /> ' . esc_html($lab) . '</label>';
        }
    } elseif ($type === 'checkbox') {
        echo '<label><input type="checkbox" name="' . esc_attr($name) . '" value="yes" data-eh-field="' . esc_attr($id) . '"';
        if ($required) {
            echo ' required';
        }
        echo ' /> ' . esc_html($label) . '</label>';
    } elseif ($type === 'select') {
        echo '<label class="eh-wc-event-label" for="eh_evt_' . esc_attr($id) . '">' . esc_html($label);
        if ($required) {
            echo ' *';
        }
        echo '</label>';
        echo '<select name="' . esc_attr($name) . '" id="eh_evt_' . esc_attr($id) . '" class="eh-wc-event-input" data-eh-field="' . esc_attr($id) . '"';
        if ($required) {
            echo ' required';
        }
        echo '><option value="">' . esc_html__('— Selecionar —', 'EventsHouse') . '</option>';
        foreach ($field['options'] ?? array() as $opt) {
            echo '<option value="' . esc_attr($opt['value'] ?? '') . '">' . esc_html($opt['label'] ?? '') . '</option>';
        }
        echo '</select>';
    } else {
        $input_type = in_array($type, array('email', 'tel', 'date'), true) ? $type : 'text';
        $tag = ($type === 'textarea') ? 'textarea' : 'input';
        echo '<label class="eh-wc-event-label" for="eh_evt_' . esc_attr($id) . '">' . esc_html($label);
        if ($required) {
            echo ' *';
        }
        echo '</label>';
        if ($tag === 'textarea') {
            echo '<textarea name="' . esc_attr($name) . '" id="eh_evt_' . esc_attr($id) . '" class="input-text eh-wc-event-input" data-eh-field="' . esc_attr($id) . '" rows="3"';
            if ($required) {
                echo ' required';
            }
            echo '></textarea>';
        } else {
            echo '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($name) . '" id="eh_evt_' . esc_attr($id) . '" class="input-text eh-wc-event-input" data-eh-field="' . esc_attr($id) . '" maxlength="500"';
            if ($required) {
                echo ' required';
            }
            echo ' />';
        }
    }
    echo '</div>';
}

/**
 * @param WC_Product|null $product Produto.
 * @return array<int, array{type:string,name:string,dob:string}>
 */
function EventsHouse_wc_event_parse_base_adults_from_post()
{
    $out = array();
    if (!isset($_POST['eh_evt_base_adults']) || !is_array($_POST['eh_evt_base_adults'])) {
        return $out;
    }
    foreach ($_POST['eh_evt_base_adults'] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = sanitize_text_field(wp_unslash($row['name'] ?? ''));
        if ($name !== '') {
            $out[] = array('type' => 'adult', 'name' => $name, 'dob' => '');
        }
    }
    return $out;
}

/**
 * @return array<int, array{type:string,name:string,dob:string}>
 */
function EventsHouse_wc_event_parse_base_children_from_post()
{
    $out = array();
    if (!isset($_POST['eh_evt_base_children']) || !is_array($_POST['eh_evt_base_children'])) {
        return $out;
    }
    foreach ($_POST['eh_evt_base_children'] as $row) {
        if (!is_array($row)) {
            continue;
        }
        $name = sanitize_text_field(wp_unslash($row['name'] ?? ''));
        $dob  = sanitize_text_field(wp_unslash($row['dob'] ?? ''));
        if ($name !== '') {
            $out[] = array('type' => 'child', 'name' => $name, 'dob' => $dob);
        }
    }
    return $out;
}

/**
 * @return array<string, string>
 */
function EventsHouse_event_parse_custom_from_post()
{
    $out = array();
    if (!isset($_POST['eh_evt_custom']) || !is_array($_POST['eh_evt_custom'])) {
        return $out;
    }
    foreach ($_POST['eh_evt_custom'] as $key => $val) {
        $id = sanitize_key((string) $key);
        if ($id === '') {
            continue;
        }
        if (is_array($val)) {
            $out[$id] = implode(', ', array_map('sanitize_text_field', $val));
        } else {
            $out[$id] = sanitize_text_field(wp_unslash($val));
        }
    }
    return $out;
}

/**
 * Valores para avaliar condicionais (POST).
 *
 * @param array<string, mixed> $config Config.
 * @return array<string, string>
 */
function EventsHouse_event_collect_field_values_for_conditions(array $config)
{
    $values = EventsHouse_event_parse_custom_from_post();
    $base_adults = EventsHouse_wc_event_parse_base_adults_from_post();
    $base_children = EventsHouse_wc_event_parse_base_children_from_post();
    if ($base_adults !== array()) {
        $values['eh_evt_base_adults'] = '1';
    }
    if ($base_children !== array()) {
        $values['eh_evt_base_children'] = '1';
    }
    return $values;
}

/**
 * @param array<string, mixed> $config Config.
 * @param array<string, string> $custom  Custom fields.
 * @return array<string, string> id => label para display
 */
function EventsHouse_event_custom_labels_map(array $config)
{
    $map = array();
    foreach ($config['fields'] ?? array() as $field) {
        if (!is_array($field) || empty($field['id'])) {
            continue;
        }
        $map[$field['id']] = $field['label'] ?? $field['id'];
    }
    return $map;
}

/**
 * @param array<string, mixed> $config Config.
 * @param array<string, string> $reg     Registo (com custom).
 * @return true|WP_Error
 */
function EventsHouse_event_validate_custom_fields(array $config, array $reg)
{
    $custom = isset($reg['eh_evt_custom']) && is_array($reg['eh_evt_custom']) ? $reg['eh_evt_custom'] : array();
    $values = array_merge(EventsHouse_event_collect_field_values_for_conditions($config), $custom);

    foreach (EventsHouse_event_get_enabled_fields($config) as $field) {
        if (!EventsHouse_event_field_is_visible($field, $values)) {
            continue;
        }
        $id = $field['id'] ?? '';
        if ($id === '') {
            continue;
        }
        $val = $custom[$id] ?? '';
        if (!empty($field['required']) && trim((string) $val) === '') {
            return new WP_Error('eh_evt', sprintf(__('Preencha o campo «%s».', 'EventsHouse'), $field['label'] ?? $id));
        }
        if ($id === 'eh_evt_email' && trim((string) $val) !== '' && !is_email($val)) {
            return new WP_Error('eh_evt', __('Indique um email válido.', 'EventsHouse'));
        }
    }
    return true;
}

/**
 * @param array<int, array{type:string,name:string,dob:string}> $participants Participantes.
 * @return string
 */
function EventsHouse_wc_event_format_participants_for_display(array $participants)
{
    if ($participants === array()) {
        return '';
    }
    $lines = array();
    $i = 1;
    foreach ($participants as $p) {
        $type_label = ($p['type'] ?? '') === 'adult' ? __('Adulto', 'EventsHouse') : __('Criança', 'EventsHouse');
        $line = $i . '. ' . $type_label . ': ' . ($p['name'] ?? '');
        if (($p['type'] ?? '') !== 'adult' && !empty($p['dob'])) {
            $line .= ' (' . $p['dob'] . ')';
        }
        $lines[] = $line;
        ++$i;
    }
    return implode("\n", $lines);
}
