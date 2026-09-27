<?php
/**
 * Admin: rodapé — textos e links (theme_mod).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve URL guardada (caminho relativo ou URL completa).
 *
 * @param string $stored Valor em theme_mod.
 */
function EventsHouse_footer_resolve_url($stored)
{
    $stored = trim((string) $stored);
    if ($stored === '' || $stored === '#') {
        return '#';
    }
    if (preg_match('#^https?://#i', $stored)) {
        return $stored;
    }
    $path = $stored[0] === '/' ? $stored : '/' . $stored;

    return home_url($path);
}

/**
 * @return array<string, string>
 */
function EventsHouse_footer_defaults_map()
{
    return array(
        'footer_description'      => 'Criamos momentos felizes para crianças e pais. Um espaço feito de sonhos.',
        'footer_copyright'        => 'A Ilha. Todos os direitos reservados.',
        'footer_heading_menu'     => 'Menu',
        'footer_heading_legal'    => 'Legal',
        'footer_heading_contact'  => 'Contactos',
        'footer_link_1_label'     => 'Início',
        'footer_link_1_url'       => '/',
        'footer_link_2_label'     => 'Festas na Ilha',
        'footer_link_2_url'       => '/orcamento',
        'footer_link_3_label'     => 'Manhãs na Ilha',
        'footer_link_3_url'       => '/manhas-na-ilha',
        'footer_link_4_label'     => 'Disponibilidade',
        'footer_link_4_url'       => '/disponibilidade',
        'footer_link_5_label'     => 'Galeria',
        'footer_link_5_url'       => '/galeria',
        'footer_link_6_label'     => 'Contactos',
        'footer_link_6_url'       => '/contato',
        'footer_legal_1_label'    => 'Política de Privacidade',
        'footer_legal_1_url'      => '/politica-de-privacidade',
        'footer_legal_2_label'    => 'Termos e Condições',
        'footer_legal_2_url'      => '/termos-e-condicoes',
        'footer_legal_3_label'    => 'Política de Cookies',
        'footer_legal_3_url'      => '#',
        'social_instagram'        => 'https://www.instagram.com/ailha.pt/',
        'social_facebook'         => 'https://www.facebook.com/ailha.pt/?locale=pt_PT',
        'social_whatsapp'         => '',
    );
}

/**
 * @param string $key Theme mod key.
 */
function EventsHouse_footer_default($key)
{
    $m = EventsHouse_footer_defaults_map();
    return isset($m[$key]) ? $m[$key] : '';
}

/**
 * Grava rodapé a partir do POST.
 */
function EventsHouse_save_footer_from_post()
{
    $st = 'sanitize_text_field';
    $ta = 'sanitize_textarea_field';

    $map = EventsHouse_footer_defaults_map();
    foreach (array_keys($map) as $key) {
        if (!isset($_POST[$key])) {
            continue;
        }
        $raw = wp_unslash($_POST[$key]);
        if ($key === 'footer_description') {
            set_theme_mod($key, $ta($raw));
            continue;
        }
        if (strpos($key, '_url') !== false || strpos($key, 'social_') === 0) {
            $raw = trim((string) $raw);
            if ($raw === '' || $raw === '#') {
                set_theme_mod($key, $key === 'social_whatsapp' ? '' : '#');
                continue;
            }
            if (preg_match('#^https?://#i', $raw)) {
                set_theme_mod($key, esc_url_raw($raw));
            } else {
                set_theme_mod($key, $st($raw));
            }
            continue;
        }
        set_theme_mod($key, $st($raw));
    }
}

/**
 * Separador Rodapé no editor de opções.
 */
function EventsHouse_render_admin_tab_footer()
{
    $d = 'EventsHouse_footer_default';
    ?>
    <div class="eh-tab-content" id="tab-footer">
        <h2><?php esc_html_e('🦶 Rodapé', 'EventsHouse'); ?></h2>
        <p class="description"><?php esc_html_e('Textos da coluna de marca, menus, links legais, redes sociais e copyright. Os contactos (morada, telefone, email) continuam no separador Contactos.', 'EventsHouse'); ?></p>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Marca e copyright', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><label for="footer_description"><?php esc_html_e('Descrição (abaixo do logo)', 'EventsHouse'); ?></label></th>
                        <td><textarea name="footer_description" id="footer_description" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod('footer_description', call_user_func($d, 'footer_description'))); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="footer_copyright"><?php esc_html_e('Texto de copyright (após o ano)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="footer_copyright" id="footer_copyright" value="<?php echo esc_attr(get_theme_mod('footer_copyright', call_user_func($d, 'footer_copyright'))); ?>" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Redes sociais (ícones no rodapé)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="social_instagram">Instagram</label></th>
                        <td><input type="url" class="large-text" name="social_instagram" id="social_instagram" value="<?php echo esc_attr(get_theme_mod('social_instagram', call_user_func($d, 'social_instagram'))); ?>" /></td></tr>
                    <tr><th><label for="social_facebook">Facebook</label></th>
                        <td><input type="url" class="large-text" name="social_facebook" id="social_facebook" value="<?php echo esc_attr(get_theme_mod('social_facebook', call_user_func($d, 'social_facebook'))); ?>" /></td></tr>
                    <tr><th><label for="social_whatsapp"><?php esc_html_e('WhatsApp (número, ex. 351912345678)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="social_whatsapp" id="social_whatsapp" value="<?php echo esc_attr(get_theme_mod('social_whatsapp', call_user_func($d, 'social_whatsapp'))); ?>" /></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Títulos das colunas', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="footer_heading_menu"><?php esc_html_e('Coluna menu', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="footer_heading_menu" id="footer_heading_menu" value="<?php echo esc_attr(get_theme_mod('footer_heading_menu', call_user_func($d, 'footer_heading_menu'))); ?>" /></td></tr>
                    <tr><th><label for="footer_heading_legal"><?php esc_html_e('Coluna legal', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="footer_heading_legal" id="footer_heading_legal" value="<?php echo esc_attr(get_theme_mod('footer_heading_legal', call_user_func($d, 'footer_heading_legal'))); ?>" /></td></tr>
                    <tr><th><label for="footer_heading_contact"><?php esc_html_e('Coluna contactos', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="footer_heading_contact" id="footer_heading_contact" value="<?php echo esc_attr(get_theme_mod('footer_heading_contact', call_user_func($d, 'footer_heading_contact'))); ?>" /></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Links do menu (6 itens)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('URL: use caminho relativo (ex. /orcamento) ou URL completa.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <?php for ($i = 1; $i <= 6; $i++) : ?>
                    <tr><th><?php echo esc_html(sprintf(__('Item %d — etiqueta', 'EventsHouse'), $i)); ?></th>
                        <td><input type="text" class="regular-text" name="footer_link_<?php echo (int) $i; ?>_label" value="<?php echo esc_attr(get_theme_mod('footer_link_' . $i . '_label', call_user_func($d, 'footer_link_' . $i . '_label'))); ?>" /></td></tr>
                    <tr><th><?php echo esc_html(sprintf(__('Item %d — URL', 'EventsHouse'), $i)); ?></th>
                        <td><input type="text" class="large-text" name="footer_link_<?php echo (int) $i; ?>_url" value="<?php echo esc_attr(get_theme_mod('footer_link_' . $i . '_url', call_user_func($d, 'footer_link_' . $i . '_url'))); ?>" placeholder="/orcamento" /></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Links legais (3 itens)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <tr><th><?php echo esc_html(sprintf(__('Legal %d — etiqueta', 'EventsHouse'), $i)); ?></th>
                        <td><input type="text" class="regular-text" name="footer_legal_<?php echo (int) $i; ?>_label" value="<?php echo esc_attr(get_theme_mod('footer_legal_' . $i . '_label', call_user_func($d, 'footer_legal_' . $i . '_label'))); ?>" /></td></tr>
                    <tr><th><?php echo esc_html(sprintf(__('Legal %d — URL', 'EventsHouse'), $i)); ?></th>
                        <td><input type="text" class="large-text" name="footer_legal_<?php echo (int) $i; ?>_url" value="<?php echo esc_attr(get_theme_mod('footer_legal_' . $i . '_url', call_user_func($d, 'footer_legal_' . $i . '_url'))); ?>" placeholder="#" /></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>
    </div>
    <?php
}
