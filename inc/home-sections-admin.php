<?php
/**
 * Admin: secções da página inicial (passos, promos, benefícios, serviços título, testemunhos).
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Grava theme_mods a partir do separador «Secções da página inicial».
 */
function EventsHouse_save_home_sections_from_post()
{
    if (!isset($_POST['eh_home_sections_save'])) {
        return;
    }

    $st = 'sanitize_text_field';
    $ta = 'sanitize_textarea_field';

    set_theme_mod('steps_title', $st(wp_unslash($_POST['steps_title'] ?? '')));
    set_theme_mod('steps_subtitle', $st(wp_unslash($_POST['steps_subtitle'] ?? '')));
    for ($i = 1; $i <= 3; $i++) {
        set_theme_mod("step_{$i}_icon", $st(wp_unslash($_POST["step_{$i}_icon"] ?? '')));
        set_theme_mod("step_{$i}_title", $st(wp_unslash($_POST["step_{$i}_title"] ?? '')));
        set_theme_mod("step_{$i}_desc", $ta(wp_unslash($_POST["step_{$i}_desc"] ?? '')));
    }

    set_theme_mod('benefits_title', $st(wp_unslash($_POST['benefits_title'] ?? '')));
    set_theme_mod('benefits_subtitle', $st(wp_unslash($_POST['benefits_subtitle'] ?? '')));
    for ($i = 1; $i <= 3; $i++) {
        set_theme_mod("benefit_{$i}_icon", $st(wp_unslash($_POST["benefit_{$i}_icon"] ?? '')));
        set_theme_mod("benefit_{$i}_title", $st(wp_unslash($_POST["benefit_{$i}_title"] ?? '')));
        set_theme_mod("benefit_{$i}_desc", $ta(wp_unslash($_POST["benefit_{$i}_desc"] ?? '')));
    }

    set_theme_mod('services_title', $st(wp_unslash($_POST['services_title'] ?? '')));
    set_theme_mod('services_subtitle', $st(wp_unslash($_POST['services_subtitle'] ?? '')));

    set_theme_mod('testimonials_title', $st(wp_unslash($_POST['testimonials_title'] ?? '')));
    set_theme_mod('testimonials_subtitle', $st(wp_unslash($_POST['testimonials_subtitle'] ?? '')));
    for ($i = 1; $i <= 3; $i++) {
        set_theme_mod("testimonial_{$i}_text", $ta(wp_unslash($_POST["testimonial_{$i}_text"] ?? '')));
        set_theme_mod("testimonial_{$i}_name", $st(wp_unslash($_POST["testimonial_{$i}_name"] ?? '')));
        set_theme_mod("testimonial_{$i}_role", $st(wp_unslash($_POST["testimonial_{$i}_role"] ?? '')));
        set_theme_mod("testimonial_{$i}_avatar", $st(wp_unslash($_POST["testimonial_{$i}_avatar"] ?? '')));
    }

    $festas_keys = array(
        'festas_promo_tag', 'festas_promo_title', 'festas_promo_desc_1', 'festas_promo_desc_2',
        'festas_promo_foot', 'festas_promo_btn_text',
    );
    foreach ($festas_keys as $k) {
        if (strpos($k, 'desc') !== false) {
            set_theme_mod($k, $ta(wp_unslash($_POST[$k] ?? '')));
        } else {
            set_theme_mod($k, $st(wp_unslash($_POST[$k] ?? '')));
        }
    }
    for ($i = 1; $i <= 5; $i++) {
        set_theme_mod("festas_promo_li_{$i}", $st(wp_unslash($_POST["festas_promo_li_{$i}"] ?? '')));
    }
    set_theme_mod('festas_promo_image', esc_url_raw($_POST['festas_promo_image'] ?? ''));
    set_theme_mod('festas_promo_btn_url', esc_url_raw($_POST['festas_promo_btn_url'] ?? ''));

    set_theme_mod('manhas_promo_tag', $st(wp_unslash($_POST['manhas_promo_tag'] ?? '')));
    set_theme_mod('manhas_promo_title', $st(wp_unslash($_POST['manhas_promo_title'] ?? '')));
    set_theme_mod('manhas_promo_desc', $ta(wp_unslash($_POST['manhas_promo_desc'] ?? '')));
    set_theme_mod('manhas_promo_foot', $ta(wp_unslash($_POST['manhas_promo_foot'] ?? '')));
    for ($i = 1; $i <= 4; $i++) {
        set_theme_mod("manhas_promo_li_{$i}", $st(wp_unslash($_POST["manhas_promo_li_{$i}"] ?? '')));
    }
    set_theme_mod('manhas_promo_image', esc_url_raw($_POST['manhas_promo_image'] ?? ''));
    set_theme_mod('manhas_promo_btn_text', $st(wp_unslash($_POST['manhas_promo_btn_text'] ?? '')));
    set_theme_mod('manhas_promo_btn_url', esc_url_raw($_POST['manhas_promo_btn_url'] ?? ''));
}

/**
 * Renderiza o separador com postboxes.
 */
function EventsHouse_render_admin_tab_home_sections()
{
    $d = 'EventsHouse_home_default';
    ?>
    <div class="eh-tab-content" id="tab-home-sections">
        <h2><?php esc_html_e('Secções da página inicial', 'EventsHouse'); ?></h2>
        <p class="description"><?php esc_html_e('Edite cada bloco da home. Os ícones dos passos e benefícios podem ser emojis.', 'EventsHouse'); ?></p>

        <!-- Passos -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Festa dos Sonhos em 3 Passos!', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th><label for="steps_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="steps_title" id="steps_title" value="<?php echo esc_attr(get_theme_mod('steps_title', call_user_func($d, 'steps_title'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="steps_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="steps_subtitle" id="steps_subtitle" value="<?php echo esc_attr(get_theme_mod('steps_subtitle', call_user_func($d, 'steps_subtitle'))); ?>" /></td>
                    </tr>
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(__('Passo %d', 'EventsHouse'), $i)); ?></strong></th></tr>
                    <tr>
                        <th><label for="step_<?php echo (int) $i; ?>_icon"><?php esc_html_e('Ícone', 'EventsHouse'); ?></label></th>
                        <td><input type="text" name="step_<?php echo (int) $i; ?>_icon" id="step_<?php echo (int) $i; ?>_icon" value="<?php echo esc_attr(get_theme_mod("step_{$i}_icon", call_user_func($d, "step_{$i}_icon"))); ?>" class="small-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="step_<?php echo (int) $i; ?>_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="step_<?php echo (int) $i; ?>_title" id="step_<?php echo (int) $i; ?>_title" value="<?php echo esc_attr(get_theme_mod("step_{$i}_title", call_user_func($d, "step_{$i}_title"))); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="step_<?php echo (int) $i; ?>_desc"><?php esc_html_e('Descrição', 'EventsHouse'); ?></label></th>
                        <td><textarea name="step_<?php echo (int) $i; ?>_desc" id="step_<?php echo (int) $i; ?>_desc" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod("step_{$i}_desc", call_user_func($d, "step_{$i}_desc"))); ?></textarea></td>
                    </tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <!-- Festas na Ilha -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Festas na Ilha (bloco promocional)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="festas_promo_tag"><?php esc_html_e('Etiqueta (tag)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="festas_promo_tag" id="festas_promo_tag" value="<?php echo esc_attr(get_theme_mod('festas_promo_tag', call_user_func($d, 'festas_promo_tag'))); ?>" /></td></tr>
                    <tr><th><label for="festas_promo_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="festas_promo_title" id="festas_promo_title" value="<?php echo esc_attr(get_theme_mod('festas_promo_title', call_user_func($d, 'festas_promo_title'))); ?>" /></td></tr>
                    <tr><th><label for="festas_promo_desc_1"><?php esc_html_e('Parágrafo 1', 'EventsHouse'); ?></label></th>
                        <td><textarea name="festas_promo_desc_1" id="festas_promo_desc_1" class="large-text" rows="2"><?php echo esc_textarea(get_theme_mod('festas_promo_desc_1', call_user_func($d, 'festas_promo_desc_1'))); ?></textarea></td></tr>
                    <tr><th><label for="festas_promo_desc_2"><?php esc_html_e('Parágrafo 2', 'EventsHouse'); ?></label></th>
                        <td><textarea name="festas_promo_desc_2" id="festas_promo_desc_2" class="large-text" rows="2"><?php echo esc_textarea(get_theme_mod('festas_promo_desc_2', call_user_func($d, 'festas_promo_desc_2'))); ?></textarea></td></tr>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <tr><th><label for="festas_promo_li_<?php echo (int) $i; ?>"><?php echo esc_html(sprintf(__('Lista — item %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="festas_promo_li_<?php echo (int) $i; ?>" id="festas_promo_li_<?php echo (int) $i; ?>" value="<?php echo esc_attr(get_theme_mod("festas_promo_li_{$i}", call_user_func($d, "festas_promo_li_{$i}"))); ?>" /></td></tr>
                    <?php endfor; ?>
                    <tr><th><label for="festas_promo_foot"><?php esc_html_e('Texto final (rodapé do bloco)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="festas_promo_foot" id="festas_promo_foot" value="<?php echo esc_attr(get_theme_mod('festas_promo_foot', call_user_func($d, 'festas_promo_foot'))); ?>" /></td></tr>
                    <tr><th><label for="festas_promo_image"><?php esc_html_e('Imagem (URL)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="text" class="large-text" name="festas_promo_image" id="festas_promo_image" value="<?php echo esc_url(get_theme_mod('festas_promo_image', call_user_func($d, 'festas_promo_image'))); ?>" />
                            <button type="button" class="button eh-media-btn" data-target="festas_promo_image"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            <div class="eh-hero-slide-preview" style="margin-top:8px;"><?php
                            $fi = get_theme_mod('festas_promo_image', '');
                            if ($fi) {
                                echo '<img src="' . esc_url($fi) . '" alt="" style="max-width:220px;height:auto;border:1px solid #ccc;border-radius:4px;" />';
                            }
                            ?></div>
                        </td></tr>
                    <tr><th><label for="festas_promo_btn_text"><?php esc_html_e('Texto do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="festas_promo_btn_text" id="festas_promo_btn_text" value="<?php echo esc_attr(get_theme_mod('festas_promo_btn_text', call_user_func($d, 'festas_promo_btn_text'))); ?>" /></td></tr>
                    <tr><th><label for="festas_promo_btn_url"><?php esc_html_e('Link do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="festas_promo_btn_url" id="festas_promo_btn_url" value="<?php echo esc_url(get_theme_mod('festas_promo_btn_url', call_user_func($d, 'festas_promo_btn_url'))); ?>" placeholder="https://..." /></td></tr>
                </table>
            </div>
        </div>

        <!-- Manhãs na Ilha -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Manhãs na Ilha (bloco promocional)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="manhas_promo_tag"><?php esc_html_e('Etiqueta (tag)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="manhas_promo_tag" id="manhas_promo_tag" value="<?php echo esc_attr(get_theme_mod('manhas_promo_tag', call_user_func($d, 'manhas_promo_tag'))); ?>" /></td></tr>
                    <tr><th><label for="manhas_promo_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="manhas_promo_title" id="manhas_promo_title" value="<?php echo esc_attr(get_theme_mod('manhas_promo_title', call_user_func($d, 'manhas_promo_title'))); ?>" /></td></tr>
                    <tr><th><label for="manhas_promo_desc"><?php esc_html_e('Descrição', 'EventsHouse'); ?></label></th>
                        <td><textarea name="manhas_promo_desc" id="manhas_promo_desc" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod('manhas_promo_desc', call_user_func($d, 'manhas_promo_desc'))); ?></textarea></td></tr>
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <tr><th><label for="manhas_promo_li_<?php echo (int) $i; ?>"><?php echo esc_html(sprintf(__('Lista — item %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="manhas_promo_li_<?php echo (int) $i; ?>" id="manhas_promo_li_<?php echo (int) $i; ?>" value="<?php echo esc_attr(get_theme_mod("manhas_promo_li_{$i}", call_user_func($d, "manhas_promo_li_{$i}"))); ?>" /></td></tr>
                    <?php endfor; ?>
                    <tr><th><label for="manhas_promo_foot"><?php esc_html_e('Texto final', 'EventsHouse'); ?></label></th>
                        <td><textarea name="manhas_promo_foot" id="manhas_promo_foot" class="large-text" rows="2"><?php echo esc_textarea(get_theme_mod('manhas_promo_foot', call_user_func($d, 'manhas_promo_foot'))); ?></textarea></td></tr>
                    <tr><th><label for="manhas_promo_image"><?php esc_html_e('Imagem (URL)', 'EventsHouse'); ?></label></th>
                        <td>
                            <input type="text" class="large-text" name="manhas_promo_image" id="manhas_promo_image" value="<?php echo esc_url(get_theme_mod('manhas_promo_image', '')); ?>" />
                            <button type="button" class="button eh-media-btn" data-target="manhas_promo_image"><?php esc_html_e('Escolher imagem', 'EventsHouse'); ?></button>
                            <p class="description"><?php esc_html_e('Deixe vazio para usar a primeira imagem da grelha «O Nosso Espaço» (Personalizar) ou a imagem por omissão.', 'EventsHouse'); ?></p>
                            <div class="eh-hero-slide-preview" style="margin-top:8px;"><?php
                            $mi = get_theme_mod('manhas_promo_image', '');
                            if ($mi) {
                                echo '<img src="' . esc_url($mi) . '" alt="" style="max-width:220px;height:auto;border:1px solid #ccc;border-radius:4px;" />';
                            }
                            ?></div>
                        </td></tr>
                    <tr><th><label for="manhas_promo_btn_text"><?php esc_html_e('Texto do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="manhas_promo_btn_text" id="manhas_promo_btn_text" value="<?php echo esc_attr(get_theme_mod('manhas_promo_btn_text', call_user_func($d, 'manhas_promo_btn_text'))); ?>" /></td></tr>
                    <tr><th><label for="manhas_promo_btn_url"><?php esc_html_e('Link do botão', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="manhas_promo_btn_url" id="manhas_promo_btn_url" value="<?php echo esc_url(get_theme_mod('manhas_promo_btn_url', call_user_func($d, 'manhas_promo_btn_url'))); ?>" placeholder="https://..." /></td></tr>
                </table>
            </div>
        </div>

        <!-- Porquê escolher -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Porquê escolher A Ilha? (vantagens)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="benefits_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="benefits_title" id="benefits_title" value="<?php echo esc_attr(get_theme_mod('benefits_title', call_user_func($d, 'benefits_title'))); ?>" /></td></tr>
                    <tr><th><label for="benefits_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="benefits_subtitle" id="benefits_subtitle" value="<?php echo esc_attr(get_theme_mod('benefits_subtitle', call_user_func($d, 'benefits_subtitle'))); ?>" /></td></tr>
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(__('Vantagem %d', 'EventsHouse'), $i)); ?></strong></th></tr>
                    <tr><th><label><?php esc_html_e('Ícone', 'EventsHouse'); ?></label></th>
                        <td><input type="text" name="benefit_<?php echo (int) $i; ?>_icon" value="<?php echo esc_attr(get_theme_mod("benefit_{$i}_icon", call_user_func($d, "benefit_{$i}_icon"))); ?>" /></td></tr>
                    <tr><th><label><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="benefit_<?php echo (int) $i; ?>_title" value="<?php echo esc_attr(get_theme_mod("benefit_{$i}_title", call_user_func($d, "benefit_{$i}_title"))); ?>" /></td></tr>
                    <tr><th><label><?php esc_html_e('Descrição', 'EventsHouse'); ?></label></th>
                        <td><textarea name="benefit_<?php echo (int) $i; ?>_desc" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod("benefit_{$i}_desc", call_user_func($d, "benefit_{$i}_desc"))); ?></textarea></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <!-- Serviços título -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Disponível no nosso Espaço (título da secção)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('A lista de ícones e nomes continua no separador «Serviços / Amenidades».', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr><th><label for="services_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="services_title" id="services_title" value="<?php echo esc_attr(get_theme_mod('services_title', call_user_func($d, 'services_title'))); ?>" /></td></tr>
                    <tr><th><label for="services_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="services_subtitle" id="services_subtitle" value="<?php echo esc_attr(get_theme_mod('services_subtitle', call_user_func($d, 'services_subtitle'))); ?>" /></td></tr>
                </table>
            </div>
        </div>

        <!-- Testemunhos -->
        <div class="postbox eventshouse-section-box">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('O que dizem os Pais', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="testimonials_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="testimonials_title" id="testimonials_title" value="<?php echo esc_attr(get_theme_mod('testimonials_title', call_user_func($d, 'testimonials_title'))); ?>" /></td></tr>
                    <tr><th><label for="testimonials_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="testimonials_subtitle" id="testimonials_subtitle" value="<?php echo esc_attr(get_theme_mod('testimonials_subtitle', call_user_func($d, 'testimonials_subtitle'))); ?>" /></td></tr>
                    <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <tr><th colspan="2"><strong><?php echo esc_html(sprintf(__('Testemunho %d', 'EventsHouse'), $i)); ?></strong></th></tr>
                    <tr><th><label><?php esc_html_e('Texto', 'EventsHouse'); ?></label></th>
                        <td><textarea name="testimonial_<?php echo (int) $i; ?>_text" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod("testimonial_{$i}_text", call_user_func($d, "testimonial_{$i}_text"))); ?></textarea></td></tr>
                    <tr><th><label><?php esc_html_e('Nome', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="testimonial_<?php echo (int) $i; ?>_name" value="<?php echo esc_attr(get_theme_mod("testimonial_{$i}_name", call_user_func($d, "testimonial_{$i}_name"))); ?>" /></td></tr>
                    <tr><th><label><?php esc_html_e('Função / nota', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="testimonial_<?php echo (int) $i; ?>_role" value="<?php echo esc_attr(get_theme_mod("testimonial_{$i}_role", call_user_func($d, "testimonial_{$i}_role"))); ?>" /></td></tr>
                    <tr><th><label><?php esc_html_e('Avatar (letra ou emoji)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" name="testimonial_<?php echo (int) $i; ?>_avatar" value="<?php echo esc_attr(get_theme_mod("testimonial_{$i}_avatar", call_user_func($d, "testimonial_{$i}_avatar"))); ?>" maxlength="4" style="width:5em;" /></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <input type="hidden" name="eh_home_sections_save" value="1" />
    </div>
    <?php
}

/**
 * Valores por omissão (alinhados ao HTML antigo da home).
 *
 * @param string $key Theme mod key.
 */
function EventsHouse_home_default($key)
{
    $map = array(
        'steps_title'    => 'Festa dos Sonhos em 3 Passos! 🎊',
        'steps_subtitle' => 'Organizar a festa perfeita nunca foi tão fácil',
        'step_1_icon'    => '📋',
        'step_1_title'   => 'Escolha a Modalidade',
        'step_1_desc'    => 'Selecione o pacote ideal para a sua celebração e personalize ao seu gosto.',
        'step_2_icon'    => '📅',
        'step_2_title'   => 'Escolha a Data',
        'step_2_desc'    => 'Verifique a disponibilidade e reserve o melhor dia para a festa.',
        'step_3_icon'    => '🎉',
        'step_3_title'   => 'Celebre!',
        'step_3_desc'    => 'Aproveite o dia com os mais pequenos num espaço seguro e divertido.',
        'benefits_title'    => 'Porquê escolher A Ilha? 🌟',
        'benefits_subtitle' => 'Tudo o que precisa para uma festa inesquecível',
        'benefit_1_icon' => '🏠',
        'benefit_1_title' => 'Espaço Exclusivo',
        'benefit_1_desc' => 'O espaço é todo seu durante o evento. Sem partilha, sem interrupções. Total privacidade para a sua festa.',
        'benefit_2_icon' => '🛡️',
        'benefit_2_title' => 'Segurança Total',
        'benefit_2_desc' => 'Ambiente seguro com proteções em todos os cantos, pensado especialmente para crianças de todas as idades.',
        'benefit_3_icon' => '✨',
        'benefit_3_title' => 'Atividades Grátis',
        'benefit_3_desc' => 'Jogos, brinquedos e áreas temáticas incluídas. As crianças vão adorar cada momento!',
        'services_title'    => 'Disponível no nosso Espaço',
        'services_subtitle' => 'Tudo para que não tenha de se preocupar com nada',
        'testimonials_title'    => 'O que dizem os Pais 💬',
        'testimonials_subtitle' => 'A opinião de quem já celebrou connosco',
        'testimonial_1_text' => '"O espaço é simplesmente maravilhoso! A minha filha adorou a festa de aniversário. Tudo limpo, organizado e com muita diversão para as crianças. Recomendo imenso!"',
        'testimonial_1_name' => 'Ana Silva',
        'testimonial_1_role' => 'Mãe da Beatriz, 6 anos',
        'testimonial_1_avatar' => 'A',
        'testimonial_2_text' => '"Fizemos a festa do nosso filho aqui e foi perfeito! O espaço tem tudo o que é preciso e as crianças não pararam de brincar. Sem dúvida que voltaremos!"',
        'testimonial_2_name' => 'Miguel Santos',
        'testimonial_2_role' => 'Pai do Tomás, 4 anos',
        'testimonial_2_avatar' => 'M',
        'testimonial_3_text' => '"Excelente relação qualidade-preço! O atendimento foi fantástico e a decoração ficou lindíssima. A melhor festa de sempre! Obrigada EventsHouse!"',
        'testimonial_3_name' => 'Sofia Costa',
        'testimonial_3_role' => 'Mãe da Carolina, 5 anos',
        'testimonial_3_avatar' => 'S',
        'festas_promo_tag'   => 'Ao fim de semana',
        'festas_promo_title' => 'Festas na Ilha',
        'festas_promo_desc_1' => 'Criamos o cenário perfeito para celebrar momentos especiais.',
        'festas_promo_desc_2' => 'Um espaço só para si, onde as crianças se divertem e os pais aproveitam sem preocupações.',
        'festas_promo_li_1' => 'Espaço exclusivo para a sua festa',
        'festas_promo_li_2' => 'Ambiente seguro e preparado para crianças',
        'festas_promo_li_3' => 'Zona para brincar e explorar',
        'festas_promo_li_4' => 'Apoio na organização',
        'festas_promo_li_5' => 'Espaço confortável para pais',
        'festas_promo_foot' => 'Porque os momentos especiais merecem um lugar à altura.',
        'festas_promo_image' => EventsHouse_URI . '/assets/images/hero-2.jpg',
        'festas_promo_btn_text' => 'Ver Festas',
        'festas_promo_btn_url'  => '',
        'manhas_promo_tag'   => 'Durante a semana',
        'manhas_promo_title' => 'Manhãs na Ilha',
        'manhas_promo_desc'  => 'Enquanto as crianças brincam, tem tempo para trabalhar, relaxar ou simplesmente estar.',
        'manhas_promo_li_1'  => 'Até 3 dias por semana',
        'manhas_promo_li_2'  => 'Espaço de brincar',
        'manhas_promo_li_3'  => 'Copa com café e chá',
        'manhas_promo_li_4'  => 'Wi-Fi e zona confortável',
        'manhas_promo_foot'  => 'Porque nem todos os dias têm de ser passados em casa.',
        'manhas_promo_btn_text' => 'Descobrir os Passes',
        'manhas_promo_btn_url'  => '',
    );
    return isset($map[$key]) ? $map[$key] : '';
}
