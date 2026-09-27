<?php
/**
 * Admin: página Manhãs na Ilha (template A Ilha Passes) — textos e IDs WooCommerce.
 *
 * @package EventsHouse
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Valores por omissão (conteúdo original da página).
 *
 * @return array<string, string>
 */
function EventsHouse_ilha_passes_defaults_map()
{
    return array(
        'ilha_passes_intro_title'       => 'Manhãs na Ilha',
        'ilha_passes_intro_body'       => "Trabalhe, descanse ou simplesmente saia de casa — enquanto o seu filho brinca em segurança, ao seu lado. As manhãs na Ilha foram criadas para famílias que precisam de equilíbrio no dia a dia. Aqui, não tem de escolher entre produtividade e tempo com o seu filho.",
        'ilha_passes_intro_btn_text'   => 'Ver disponibilidade',
        'ilha_passes_intro_btn_url'    => '#ilha-disponibilidade',
        'ilha_passes_intro_li1'        => 'Sem compromissos',
        'ilha_passes_intro_li2'        => 'Ambiente calmo e acolhedor',
        'ilha_passes_intro_li3'        => 'Outras famílias na mesma fase',
        'ilha_passes_intro_li4'        => 'Flexibilidade total (packs até 6 meses)',
        'ilha_passes_intro_image_url'  => '',
        'ilha_passes_intro_image_alt'  => 'Adulto a trabalhar e criança a brincar no espaço A Ilha',
        'ilha_passes_cal_title'         => 'Consulte a disponibilidade',
        'ilha_passes_cal_subtitle'      => 'Selecione uma data para ver quando pode vir ao espaço nas manhãs.',
        'ilha_passes_includes_title'    => 'Packs de Manhãs na Ilha',
        'ilha_passes_includes_subtitle' => '',
        'ilha_passes_include1_icon'     => '🧸',
        'ilha_passes_include1_name'     => 'Espaço de brincar',
        'ilha_passes_include2_icon'     => '🛋️',
        'ilha_passes_include2_name'     => 'Zona confortável para os pais',
        'ilha_passes_include3_icon'     => '📶',
        'ilha_passes_include3_name'     => 'Wi-Fi',
        'ilha_passes_include4_icon'     => '☕',
        'ilha_passes_include4_name'     => 'Copa com café, chá e fruta',
        'ilha_passes_audience_title'       => 'Para quem é',
        'ilha_passes_audience_badge'      => '',
        'ilha_passes_audience_image_url'  => '',
        'ilha_passes_audience_image_alt'  => 'Famílias no espaço A Ilha',
        'ilha_passes_audience_intro'      => "O espaço foi pensado para o dia a dia de famílias com crianças pequenas.\n\nEncontre equilíbrio entre trabalho, descanso e tempo em família num ambiente acolhedor.",
        'ilha_passes_audience_li1'        => 'Pais em licença',
        'ilha_passes_audience_li2'        => 'Trabalho remoto',
        'ilha_passes_audience_li3'        => 'Freelancers',
        'ilha_passes_audience_li4'        => 'Famílias que querem sair de casa',
        'ilha_passes_audience_li5'        => 'Quem procura um espaço tranquilo para estar com crianças',
        'ilha_passes_audience_footer'     => '',
        'ilha_passes_audience_btn_text'   => 'Consultar disponibilidade',
        'ilha_passes_audience_btn_url'    => '#ilha-disponibilidade',
        'ilha_passes_conceito_title'    => '1. Conceito 🏝️',
        'ilha_passes_conceito_p1'       => 'A Ilha é um espaço pensado para famílias com crianças pequenas, onde os pais podem trabalhar, relaxar ou conviver, enquanto as crianças brincam num ambiente seguro e preparado para elas.',
        'ilha_passes_conceito_p2'       => 'Durante a semana, o espaço funciona como um ponto de encontro para pais e crianças, com acesso através de passes limitados para garantir a diversão e o conforto de todos (máximo de 15 crianças por dia).',
        'ilha_passes_nossos_title'     => '2. Os Nossos Passes 🎫',
        'ilha_passes_vagas_template'   => 'Restam %s vagas neste período!',
        'ilha_passes_packs_title'      => 'Para dias mais leves com os seus filhos',
        'ilha_passes_packs_subtitle'   => 'Escolha o pack ideal e tenha flexibilidade total para utilizar quando quiser.',
        'ilha_passes_notice_title_strong'   => 'Vagas limitadas',
        'ilha_passes_notice_title_rest'     => 'todas as semanas',
        'ilha_passes_notice_body'           => 'Para garantir conforto e segurança, limitamos o número de crianças por manhã.',
        'ilha_passes_notice_emphasis_before' => 'Alguns dias ',
        'ilha_passes_notice_emphasis_italic' => 'esgotam com antecedência',
        'ilha_passes_notice_emphasis_after'  => '.',
        'ilha_passes_notice_cta_strong'     => 'Reserve',
        'ilha_passes_notice_cta_rest'       => 'com tempo para garantir lugar.',
        'ilha_passes_notice_footer_before'  => 'Mais de ',
        'ilha_passes_notice_footer_strong'  => ' 50 famílias ',
        'ilha_passes_notice_footer_after'   => ' já visitaram a Ilha',
        'ilha_passes_notice_img_main'       => '',
        'ilha_passes_notice_img_bl'         => '',
        'ilha_passes_notice_img_br'         => '',
        'ilha_passes_notice_img_alt'        => 'Famílias e crianças no espaço A Ilha',
        'ilha_passes_pack1_title'      => 'Pack 1 Manhã',
        'ilha_passes_pack1_tagline'    => 'Acesso pontual',
        'ilha_passes_pack1_btn'        => 'Comprar Pack',
        'ilha_passes_pack1_li1'        => '1 entrada (1 adulto + 1 criança)',
        'ilha_passes_pack1_li2'        => 'Válido por 6 meses',
        'ilha_passes_pack1_li3'        => 'Acesso à copa e brincadeiras',
        'ilha_passes_pack6_title'      => 'Pack 6 Manhãs',
        'ilha_passes_pack6_tagline'    => 'Ideal para rotina flexível',
        'ilha_passes_pack6_btn'        => 'Comprar Pack 6',
        'ilha_passes_pack6_li1'        => '6 entradas (1 adulto + 1 criança)',
        'ilha_passes_pack6_li2'        => 'Válido por 6 meses',
        'ilha_passes_pack6_li3'        => 'Pode partilhar com amigos',
        'ilha_passes_pack12_title'     => 'Pack 12 Manhãs',
        'ilha_passes_pack12_tagline'   => 'A diversão rende mais',
        'ilha_passes_pack12_btn'       => 'Comprar Pack 12',
        'ilha_passes_pack12_badge'     => 'Melhor Valor',
        'ilha_passes_pack12_li1'       => '12 entradas (1 adulto + 1 criança)',
        'ilha_passes_pack12_li2'       => 'Válido por 6 meses',
        'ilha_passes_pack12_li3'       => 'Pode partilhar com amigos',
        'ilha_passes_recorrentes_title'       => 'Passes Recorrentes 🎫',
        'ilha_passes_recorrentes_subtitle'    => 'Acesso garantido para famílias com rotina fixa semanal ou mensal.',
        'ilha_passes_weekly_title'     => 'Passe Semanal',
        'ilha_passes_weekly_tagline'   => 'Rotina semanal fora de casa',
        'ilha_passes_weekly_btn'       => 'Comprar Passe Semanal',
        'ilha_passes_weekly_li1'       => 'Acesso ao espaço numa mesma semana',
        'ilha_passes_weekly_li2'       => 'Até 3 dias de utilização',
        'ilha_passes_weekly_li3'       => 'Acesso à área de brincar e trabalho',
        'ilha_passes_weekly_li4'       => 'Utilização da copa partilhada',
        'ilha_passes_monthly_title'    => 'Passe Mensal',
        'ilha_passes_monthly_tagline'  => 'A opção ideal para freelancers e licenças',
        'ilha_passes_monthly_btn'      => 'Comprar Passe Mensal',
        'ilha_passes_monthly_badge'    => 'Mais Popular',
        'ilha_passes_monthly_li1'      => 'Acesso ao espaço durante 1 mês',
        'ilha_passes_monthly_li2'      => 'Até 3 dias por semana',
        'ilha_passes_monthly_li3'      => 'Prioridade no acesso (limite das vagas)',
        'ilha_passes_monthly_li4'      => 'Utilização da copa partilhada',
        'ilha_passes_footnote'         => '* O acesso diário é limitado a 15 crianças para manter o conforto e segurança. A venda dos passes é controlada automaticamente.',
        'ilha_passes_regras_title'     => 'Regras e Informações 📌',
        'ilha_passes_regras_html'      => '<p>Lotação: Temos um controlo rigoroso de limite de 15 crianças por dia. Ao adquirir o passe, a sua vaga está garantida na lotação do período correspondente.</p><p>Acompanhamento: As crianças devem ser sempre supervisionadas pelo responsável (1 passe = 1 adulto acompanhante + 1 criança).</p><p>Horário: Aberto nos dias úteis especificados pela administração.</p>',
    );
}

/**
 * HTML das regras (omissão do tema ou campos antigos p1–p3).
 *
 * @return string HTML seguro para eco com wp_kses_post.
 */
function EventsHouse_ilha_passes_get_regras_html_for_front()
{
    $html = get_theme_mod('ilha_passes_regras_html', '');
    if ($html !== '') {
        return $html;
    }
    $p1 = get_theme_mod('ilha_passes_regras_p1', '');
    $p2 = get_theme_mod('ilha_passes_regras_p2', '');
    $p3 = get_theme_mod('ilha_passes_regras_p3', '');
    if ($p1 !== '' || $p2 !== '' || $p3 !== '') {
        $out = '';
        foreach (array($p1, $p2, $p3) as $p) {
            if ($p !== '') {
                $out .= '<p>' . esc_html($p) . '</p>';
            }
        }

        return $out;
    }

    return EventsHouse_ilha_passes_default('ilha_passes_regras_html');
}

/**
 * Conteúdo inicial do editor (tema novo ou migração dos três campos antigos).
 *
 * @return string
 */
function EventsHouse_ilha_passes_get_regras_html_for_editor()
{
    $html = get_theme_mod('ilha_passes_regras_html', '');
    if ($html !== '') {
        return $html;
    }
    $p1 = get_theme_mod('ilha_passes_regras_p1', '');
    $p2 = get_theme_mod('ilha_passes_regras_p2', '');
    $p3 = get_theme_mod('ilha_passes_regras_p3', '');
    if ($p1 !== '' || $p2 !== '' || $p3 !== '') {
        $out = '';
        foreach (array($p1, $p2, $p3) as $p) {
            if ($p !== '') {
                $out .= '<p>' . esc_html($p) . '</p>';
            }
        }

        return $out;
    }

    return EventsHouse_ilha_passes_default('ilha_passes_regras_html');
}

/**
 * @param string $key Theme mod key (full name including ilha_passes_ prefix).
 */
function EventsHouse_ilha_passes_default($key)
{
    $map = EventsHouse_ilha_passes_defaults_map();
    return isset($map[$key]) ? $map[$key] : '';
}

/**
 * Grava textos e IDs WooCommerce (mesmos theme_mod do Personalizar).
 */
function EventsHouse_save_ilha_passes_page_from_post()
{
    $map   = EventsHouse_ilha_passes_defaults_map();
    $st    = 'sanitize_text_field';
    $ta    = 'sanitize_textarea_field';

    $textarea_keys = array(
        'ilha_passes_conceito_p1',
        'ilha_passes_conceito_p2',
        'ilha_passes_footnote',
        'ilha_passes_cal_subtitle',
        'ilha_passes_includes_subtitle',
        'ilha_passes_audience_intro',
        'ilha_passes_intro_body',
        'ilha_passes_notice_body',
    );
    foreach (array_keys($map) as $key) {
        if ($key === 'ilha_passes_regras_html') {
            continue;
        }
        if (!isset($_POST[$key])) {
            continue;
        }
        $raw = wp_unslash($_POST[$key]);
        if (in_array($key, $textarea_keys, true)) {
            $value = $ta($raw);
            set_theme_mod($key, $value);
            // Compatibilidade com ecrãs/admins que possam ler por option.
            update_option($key, $value);
        } else {
            $value = $st($raw);
            set_theme_mod($key, $value);
            update_option($key, $value);
        }
    }

    if (isset($_POST['ilha_passes_regras_html'])) {
        $html = wp_kses_post(wp_unslash($_POST['ilha_passes_regras_html']));
        set_theme_mod('ilha_passes_regras_html', $html);
        update_option('ilha_passes_regras_html', $html);
    }

    $rec = isset($_POST['ilha_passes_recorrentes_show']) ? '1' : '';
    set_theme_mod('ilha_passes_recorrentes_show', $rec);
    update_option('ilha_passes_recorrentes_show', $rec);

    $ids = array(
        'woocommerce_weekly_pass_id',
        'woocommerce_monthly_pass_id',
        'woocommerce_pack_1_id',
        'woocommerce_pack_6_id',
        'woocommerce_pack_12_id',
        'woocommerce_extra_adult_id',
        'woocommerce_extra_child_id',
        'woocommerce_meias_antiderrapantes_id',
    );
    foreach ($ids as $id_key) {
        $idv = absint($_POST[$id_key] ?? 0);
        set_theme_mod($id_key, $idv);
        update_option($id_key, $idv);
    }
}

/**
 * Renderiza o separador #tab-manhas.
 */
function EventsHouse_render_admin_tab_ilha_passes()
{
    $d = 'EventsHouse_ilha_passes_default';
    $m = EventsHouse_ilha_passes_defaults_map();
    ?>
    <div class="eh-tab-content" id="tab-manhas">
        <h2><?php esc_html_e('🌅 Manhãs na Ilha — Página de passes', 'EventsHouse'); ?></h2>
        <p class="description"><?php esc_html_e('Textos da página «Manhãs na Ilha» e IDs dos produtos WooCommerce (add to cart). Os mesmos IDs estão em Personalizar → A Ilha Passes.', 'EventsHouse'); ?></p>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('IDs WooCommerce', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Se deixar em branco (0), o botão redireciona para a própria página de passes (a loja /shop redireciona para aqui).', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr>
                        <th><label for="woocommerce_weekly_pass_id"><?php esc_html_e('ID — Passe Semanal', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_weekly_pass_id" id="woocommerce_weekly_pass_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_weekly_pass_id', '')); ?>" placeholder="123" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_monthly_pass_id"><?php esc_html_e('ID — Passe Mensal', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_monthly_pass_id" id="woocommerce_monthly_pass_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_monthly_pass_id', '')); ?>" placeholder="124" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_pack_1_id"><?php esc_html_e('ID — Pack 1 Manhã', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_pack_1_id" id="woocommerce_pack_1_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_pack_1_id', '')); ?>" placeholder="125" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_pack_6_id"><?php esc_html_e('ID — Pack 6 Manhãs', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_pack_6_id" id="woocommerce_pack_6_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_pack_6_id', '')); ?>" placeholder="126" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_pack_12_id"><?php esc_html_e('ID — Pack 12 Manhãs', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_pack_12_id" id="woocommerce_pack_12_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_pack_12_id', '')); ?>" placeholder="127" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_extra_adult_id"><?php esc_html_e('ID — Extra Adulto (opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_extra_adult_id" id="woocommerce_extra_adult_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_extra_adult_id', '')); ?>" placeholder="62" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_extra_child_id"><?php esc_html_e('ID — Extra Criança (opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_extra_child_id" id="woocommerce_extra_child_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_extra_child_id', '')); ?>" placeholder="65" /></td>
                    </tr>
                    <tr>
                        <th><label for="woocommerce_meias_antiderrapantes_id"><?php esc_html_e('ID — Meias antiderrapantes (receção)', 'EventsHouse'); ?></label></th>
                        <td><input type="number" min="0" class="small-text" name="woocommerce_meias_antiderrapantes_id" id="woocommerce_meias_antiderrapantes_id" value="<?php echo esc_attr((string) get_theme_mod('woocommerce_meias_antiderrapantes_id', '392')); ?>" placeholder="392" /></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Intro (acima dos packs)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Duas colunas: texto + botão + lista à esquerda; imagem à direita. Substitui o antigo cabeçalho da página.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr><th><label for="ilha_passes_intro_title"><?php esc_html_e('Título (H1)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_intro_title" id="ilha_passes_intro_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_title', call_user_func($d, 'ilha_passes_intro_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_intro_body"><?php esc_html_e('Texto principal', 'EventsHouse'); ?></label></th>
                        <td><textarea class="large-text" rows="5" name="ilha_passes_intro_body" id="ilha_passes_intro_body"><?php echo esc_textarea(get_theme_mod('ilha_passes_intro_body', call_user_func($d, 'ilha_passes_intro_body'))); ?></textarea></td></tr>
                    <tr><th><label for="ilha_passes_intro_btn_text"><?php esc_html_e('Botão — texto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_intro_btn_text" id="ilha_passes_intro_btn_text" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_btn_text', call_user_func($d, 'ilha_passes_intro_btn_text'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_intro_btn_url"><?php esc_html_e('Botão — link', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_intro_btn_url" id="ilha_passes_intro_btn_url" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_btn_url', call_user_func($d, 'ilha_passes_intro_btn_url'))); ?>" placeholder="#ilha-disponibilidade" /></td></tr>
                    <?php for ($k = 1; $k <= 4; $k++) : ?>
                    <tr>
                        <th><label for="ilha_passes_intro_li<?php echo (int) $k; ?>"><?php echo esc_html(sprintf(/* translators: %d: line 1-4 */ __('Lista — linha %d', 'EventsHouse'), $k)); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_intro_li<?php echo (int) $k; ?>" id="ilha_passes_intro_li<?php echo (int) $k; ?>" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_li' . $k, call_user_func($d, 'ilha_passes_intro_li' . $k))); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                    <tr><th><label for="ilha_passes_intro_image_url"><?php esc_html_e('Imagem — URL', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="ilha_passes_intro_image_url" id="ilha_passes_intro_image_url" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_image_url', call_user_func($d, 'ilha_passes_intro_image_url'))); ?>" placeholder="https://…" />
                            <p class="description"><?php esc_html_e('URL da biblioteca de média. Se ficar vazio, a coluna da imagem não é mostrada.', 'EventsHouse'); ?></p></td></tr>
                    <tr><th><label for="ilha_passes_intro_image_alt"><?php esc_html_e('Imagem — texto alternativo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_intro_image_alt" id="ilha_passes_intro_image_alt" value="<?php echo esc_attr(get_theme_mod('ilha_passes_intro_image_alt', call_user_func($d, 'ilha_passes_intro_image_alt'))); ?>" /></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Aviso (abaixo dos packs)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Cartão com texto à esquerda e imagens à direita (URLs da biblioteca de média). Sem imagem principal, o bloco de imagens não aparece.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr><th><label for="ilha_passes_notice_title_strong"><?php esc_html_e('Linha 1 — parte em destaque', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_title_strong" id="ilha_passes_notice_title_strong" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_title_strong', call_user_func($d, 'ilha_passes_notice_title_strong'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_title_rest"><?php esc_html_e('Linha 1 — resto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_title_rest" id="ilha_passes_notice_title_rest" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_title_rest', call_user_func($d, 'ilha_passes_notice_title_rest'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_body"><?php esc_html_e('Parágrafo', 'EventsHouse'); ?></label></th>
                        <td><textarea class="large-text" rows="3" name="ilha_passes_notice_body" id="ilha_passes_notice_body"><?php echo esc_textarea(get_theme_mod('ilha_passes_notice_body', call_user_func($d, 'ilha_passes_notice_body'))); ?></textarea></td></tr>
                    <tr><th><label for="ilha_passes_notice_emphasis_before"><?php esc_html_e('Linha itálico — antes', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_emphasis_before" id="ilha_passes_notice_emphasis_before" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_emphasis_before', call_user_func($d, 'ilha_passes_notice_emphasis_before'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_emphasis_italic"><?php esc_html_e('Linha itálico — a destacar', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_emphasis_italic" id="ilha_passes_notice_emphasis_italic" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_emphasis_italic', call_user_func($d, 'ilha_passes_notice_emphasis_italic'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_emphasis_after"><?php esc_html_e('Linha itálico — depois', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_emphasis_after" id="ilha_passes_notice_emphasis_after" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_emphasis_after', call_user_func($d, 'ilha_passes_notice_emphasis_after'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_cta_strong"><?php esc_html_e('Chamada — palavra em destaque', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_cta_strong" id="ilha_passes_notice_cta_strong" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_cta_strong', call_user_func($d, 'ilha_passes_notice_cta_strong'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_cta_rest"><?php esc_html_e('Chamada — resto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_cta_rest" id="ilha_passes_notice_cta_rest" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_cta_rest', call_user_func($d, 'ilha_passes_notice_cta_rest'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_footer_before"><?php esc_html_e('Rodapé do cartão — antes do destaque', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_footer_before" id="ilha_passes_notice_footer_before" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_footer_before', call_user_func($d, 'ilha_passes_notice_footer_before'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_footer_strong"><?php esc_html_e('Rodapé — destaque (número/texto)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_footer_strong" id="ilha_passes_notice_footer_strong" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_footer_strong', call_user_func($d, 'ilha_passes_notice_footer_strong'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_footer_after"><?php esc_html_e('Rodapé — depois do destaque', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_footer_after" id="ilha_passes_notice_footer_after" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_footer_after', call_user_func($d, 'ilha_passes_notice_footer_after'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_img_main"><?php esc_html_e('Imagem principal (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="ilha_passes_notice_img_main" id="ilha_passes_notice_img_main" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_img_main', call_user_func($d, 'ilha_passes_notice_img_main'))); ?>" placeholder="https://…" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_img_bl"><?php esc_html_e('Imagem inferior esquerda (URL, opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="ilha_passes_notice_img_bl" id="ilha_passes_notice_img_bl" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_img_bl', call_user_func($d, 'ilha_passes_notice_img_bl'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_img_br"><?php esc_html_e('Imagem inferior direita (URL, opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="ilha_passes_notice_img_br" id="ilha_passes_notice_img_br" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_img_br', call_user_func($d, 'ilha_passes_notice_img_br'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_notice_img_alt"><?php esc_html_e('Texto alternativo (acessibilidade)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_notice_img_alt" id="ilha_passes_notice_img_alt" value="<?php echo esc_attr(get_theme_mod('ilha_passes_notice_img_alt', call_user_func($d, 'ilha_passes_notice_img_alt'))); ?>" /></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Disponibilidade, «O que inclui» e «Para quem é»', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p class="description"><?php esc_html_e('Textos das secções no topo da página (calendário Manhãs na Ilha, grelha de 4 ícones e lista «Para quem é»). O ID do calendário Google está em Conteúdos do Site → Google Agenda → Manhãs na Ilha.', 'EventsHouse'); ?></p>
                <table class="form-table">
                    <tr><th colspan="2"><strong><?php esc_html_e('Consulte a disponibilidade', 'EventsHouse'); ?></strong></th></tr>
                    <tr><th><label for="ilha_passes_cal_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_cal_title" id="ilha_passes_cal_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_cal_title', call_user_func($d, 'ilha_passes_cal_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_cal_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><textarea class="large-text" rows="2" name="ilha_passes_cal_subtitle" id="ilha_passes_cal_subtitle"><?php echo esc_textarea(get_theme_mod('ilha_passes_cal_subtitle', call_user_func($d, 'ilha_passes_cal_subtitle'))); ?></textarea></td></tr>
                    <tr><th colspan="2"><strong><?php esc_html_e('O que inclui no espaço', 'EventsHouse'); ?></strong></th></tr>
                    <tr><th><label for="ilha_passes_includes_title"><?php esc_html_e('Título', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_includes_title" id="ilha_passes_includes_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_includes_title', call_user_func($d, 'ilha_passes_includes_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_includes_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><textarea class="large-text" rows="2" name="ilha_passes_includes_subtitle" id="ilha_passes_includes_subtitle"><?php echo esc_textarea(get_theme_mod('ilha_passes_includes_subtitle', call_user_func($d, 'ilha_passes_includes_subtitle'))); ?></textarea></td></tr>
                    <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <tr>
                        <th><label for="ilha_passes_include<?php echo (int) $i; ?>_icon"><?php echo esc_html(sprintf(/* translators: %d: 1-4 */ __('Item %d — ícone (emoji)', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="small-text" name="ilha_passes_include<?php echo (int) $i; ?>_icon" id="ilha_passes_include<?php echo (int) $i; ?>_icon" value="<?php echo esc_attr(get_theme_mod('ilha_passes_include' . $i . '_icon', call_user_func($d, 'ilha_passes_include' . $i . '_icon'))); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="ilha_passes_include<?php echo (int) $i; ?>_name"><?php echo esc_html(sprintf(/* translators: %d: 1-4 */ __('Item %d — texto', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_include<?php echo (int) $i; ?>_name" id="ilha_passes_include<?php echo (int) $i; ?>_name" value="<?php echo esc_attr(get_theme_mod('ilha_passes_include' . $i . '_name', call_user_func($d, 'ilha_passes_include' . $i . '_name'))); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                    <tr><th colspan="2"><strong><?php esc_html_e('Para quem é (cartão com imagem)', 'EventsHouse'); ?></strong></th></tr>
                    <tr><th><label for="ilha_passes_audience_title"><?php esc_html_e('Título (H2)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_title" id="ilha_passes_audience_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_title', call_user_func($d, 'ilha_passes_audience_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_audience_badge"><?php esc_html_e('Etiqueta (opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_badge" id="ilha_passes_audience_badge" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_badge', call_user_func($d, 'ilha_passes_audience_badge'))); ?>" placeholder="<?php esc_attr_e('Ex.: Ao fim de semana', 'EventsHouse'); ?>" />
                            <p class="description"><?php esc_html_e('Pílula acima do título. Deixe vazio para ocultar.', 'EventsHouse'); ?></p></td></tr>
                    <tr><th><label for="ilha_passes_audience_image_url"><?php esc_html_e('Imagem (URL)', 'EventsHouse'); ?></label></th>
                        <td><input type="url" class="large-text" name="ilha_passes_audience_image_url" id="ilha_passes_audience_image_url" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_image_url', call_user_func($d, 'ilha_passes_audience_image_url'))); ?>" placeholder="https://…" />
                            <p class="description"><?php esc_html_e('Coluna esquerda do cartão. Biblioteca de média → copiar URL da imagem.', 'EventsHouse'); ?></p></td></tr>
                    <tr><th><label for="ilha_passes_audience_image_alt"><?php esc_html_e('Texto alternativo da imagem', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_image_alt" id="ilha_passes_audience_image_alt" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_image_alt', call_user_func($d, 'ilha_passes_audience_image_alt'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_audience_intro"><?php esc_html_e('Texto introdutório', 'EventsHouse'); ?></label></th>
                        <td><textarea class="large-text" rows="4" name="ilha_passes_audience_intro" id="ilha_passes_audience_intro"><?php echo esc_textarea(get_theme_mod('ilha_passes_audience_intro', call_user_func($d, 'ilha_passes_audience_intro'))); ?></textarea>
                            <p class="description"><?php esc_html_e('Um ou mais parágrafos (linha em branco separa parágrafos).', 'EventsHouse'); ?></p></td></tr>
                    <?php for ($j = 1; $j <= 5; $j++) : ?>
                    <tr>
                        <th><label for="ilha_passes_audience_li<?php echo (int) $j; ?>"><?php echo esc_html(sprintf(/* translators: %d: line number */ __('Linha %d', 'EventsHouse'), $j)); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_li<?php echo (int) $j; ?>" id="ilha_passes_audience_li<?php echo (int) $j; ?>" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_li' . $j, call_user_func($d, 'ilha_passes_audience_li' . $j))); ?>" /></td>
                    </tr>
                    <?php endfor; ?>
                    <tr><th><label for="ilha_passes_audience_footer"><?php esc_html_e('Frase final (opcional)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_footer" id="ilha_passes_audience_footer" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_footer', call_user_func($d, 'ilha_passes_audience_footer'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_audience_btn_text"><?php esc_html_e('Botão — texto', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_btn_text" id="ilha_passes_audience_btn_text" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_btn_text', call_user_func($d, 'ilha_passes_audience_btn_text'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_audience_btn_url"><?php esc_html_e('Botão — link', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_audience_btn_url" id="ilha_passes_audience_btn_url" value="<?php echo esc_attr(get_theme_mod('ilha_passes_audience_btn_url', call_user_func($d, 'ilha_passes_audience_btn_url'))); ?>" placeholder="#ilha-disponibilidade ou https://…" />
                            <p class="description"><?php esc_html_e('Use âncora nesta página, ex.: #ilha-disponibilidade', 'EventsHouse'); ?></p></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('1. Conceito', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="ilha_passes_conceito_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_conceito_title" id="ilha_passes_conceito_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_conceito_title', call_user_func($d, 'ilha_passes_conceito_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_conceito_p1"><?php esc_html_e('Parágrafo 1', 'EventsHouse'); ?></label></th>
                        <td><textarea name="ilha_passes_conceito_p1" id="ilha_passes_conceito_p1" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod('ilha_passes_conceito_p1', call_user_func($d, 'ilha_passes_conceito_p1'))); ?></textarea></td></tr>
                    <tr><th><label for="ilha_passes_conceito_p2"><?php esc_html_e('Parágrafo 2', 'EventsHouse'); ?></label></th>
                        <td><textarea name="ilha_passes_conceito_p2" id="ilha_passes_conceito_p2" class="large-text" rows="3"><?php echo esc_textarea(get_theme_mod('ilha_passes_conceito_p2', call_user_func($d, 'ilha_passes_conceito_p2'))); ?></textarea></td></tr>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('2. Os nossos passes & packs', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="ilha_passes_nossos_title"><?php esc_html_e('Título «Os nossos passes»', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_nossos_title" id="ilha_passes_nossos_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_nossos_title', call_user_func($d, 'ilha_passes_nossos_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_vagas_template"><?php esc_html_e('Texto de vagas (use %s para o número)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_vagas_template" id="ilha_passes_vagas_template" value="<?php echo esc_attr(get_theme_mod('ilha_passes_vagas_template', call_user_func($d, 'ilha_passes_vagas_template'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_packs_title"><?php esc_html_e('Título «Packs de manhãs»', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_packs_title" id="ilha_passes_packs_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_packs_title', call_user_func($d, 'ilha_passes_packs_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_packs_subtitle"><?php esc_html_e('Subtítulo dos packs', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_packs_subtitle" id="ilha_passes_packs_subtitle" value="<?php echo esc_attr(get_theme_mod('ilha_passes_packs_subtitle', call_user_func($d, 'ilha_passes_packs_subtitle'))); ?>" /></td></tr>
                </table>
                <h3><?php esc_html_e('Pack 1', 'EventsHouse'); ?></h3>
                <table class="form-table">
                    <?php
                    foreach (array('title' => 'ilha_passes_pack1_title', 'tagline' => 'ilha_passes_pack1_tagline', 'btn' => 'ilha_passes_pack1_btn') as $label => $fname) :
                        ?>
                    <tr><th><label for="<?php echo esc_attr($fname); ?>"><?php echo esc_html(ucfirst($label)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fname); ?>" id="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr(get_theme_mod($fname, call_user_func($d, $fname))); ?>" /></td></tr>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= 3; $i++) : $fk = "ilha_passes_pack1_li{$i}"; ?>
                    <tr><th><label for="<?php echo esc_attr($fk); ?>"><?php echo esc_html(sprintf(__('Lista %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fk); ?>" id="<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr(get_theme_mod($fk, call_user_func($d, $fk))); ?>" /></td></tr>
                    <?php endfor; ?>
                </table>
                <h3><?php esc_html_e('Pack 6', 'EventsHouse'); ?></h3>
                <table class="form-table">
                    <?php
                    foreach (array('title' => 'ilha_passes_pack6_title', 'tagline' => 'ilha_passes_pack6_tagline', 'btn' => 'ilha_passes_pack6_btn') as $label => $fname) :
                        ?>
                    <tr><th><label for="<?php echo esc_attr($fname); ?>"><?php echo esc_html(ucfirst($label)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fname); ?>" id="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr(get_theme_mod($fname, call_user_func($d, $fname))); ?>" /></td></tr>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= 3; $i++) : $fk = "ilha_passes_pack6_li{$i}"; ?>
                    <tr><th><label for="<?php echo esc_attr($fk); ?>"><?php echo esc_html(sprintf(__('Lista %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fk); ?>" id="<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr(get_theme_mod($fk, call_user_func($d, $fk))); ?>" /></td></tr>
                    <?php endfor; ?>
                </table>
                <h3><?php esc_html_e('Pack 12', 'EventsHouse'); ?></h3>
                <table class="form-table">
                    <tr><th><label for="ilha_passes_pack12_badge"><?php esc_html_e('Etiqueta (ex. Melhor valor)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="ilha_passes_pack12_badge" id="ilha_passes_pack12_badge" value="<?php echo esc_attr(get_theme_mod('ilha_passes_pack12_badge', call_user_func($d, 'ilha_passes_pack12_badge'))); ?>" /></td></tr>
                    <?php
                    foreach (array('title' => 'ilha_passes_pack12_title', 'tagline' => 'ilha_passes_pack12_tagline', 'btn' => 'ilha_passes_pack12_btn') as $label => $fname) :
                        ?>
                    <tr><th><label for="<?php echo esc_attr($fname); ?>"><?php echo esc_html(ucfirst($label)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fname); ?>" id="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr(get_theme_mod($fname, call_user_func($d, $fname))); ?>" /></td></tr>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= 3; $i++) : $fk = "ilha_passes_pack12_li{$i}"; ?>
                    <tr><th><label for="<?php echo esc_attr($fk); ?>"><?php echo esc_html(sprintf(__('Lista %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fk); ?>" id="<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr(get_theme_mod($fk, call_user_func($d, $fk))); ?>" /></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Passes recorrentes (semanal / mensal)', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <p><label><input type="checkbox" name="ilha_passes_recorrentes_show" value="1" <?php checked(get_theme_mod('ilha_passes_recorrentes_show', ''), '1'); ?> /> <?php esc_html_e('Mostrar secção «Passes recorrentes» na página', 'EventsHouse'); ?></label></p>
                <table class="form-table">
                    <tr><th><label for="ilha_passes_recorrentes_title"><?php esc_html_e('Título da secção', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_recorrentes_title" id="ilha_passes_recorrentes_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_recorrentes_title', call_user_func($d, 'ilha_passes_recorrentes_title'))); ?>" /></td></tr>
                    <tr><th><label for="ilha_passes_recorrentes_subtitle"><?php esc_html_e('Subtítulo', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_recorrentes_subtitle" id="ilha_passes_recorrentes_subtitle" value="<?php echo esc_attr(get_theme_mod('ilha_passes_recorrentes_subtitle', call_user_func($d, 'ilha_passes_recorrentes_subtitle'))); ?>" /></td></tr>
                </table>
                <h3><?php esc_html_e('Passe semanal', 'EventsHouse'); ?></h3>
                <table class="form-table">
                    <?php
                    $wk = array('ilha_passes_weekly_title', 'ilha_passes_weekly_tagline', 'ilha_passes_weekly_btn');
                    foreach ($wk as $fname) :
                        ?>
                    <tr><th><label for="<?php echo esc_attr($fname); ?>"><?php echo esc_html(str_replace('ilha_passes_weekly_', '', $fname)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fname); ?>" id="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr(get_theme_mod($fname, call_user_func($d, $fname))); ?>" /></td></tr>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= 4; $i++) : $fk = "ilha_passes_weekly_li{$i}"; ?>
                    <tr><th><label for="<?php echo esc_attr($fk); ?>"><?php echo esc_html(sprintf(__('Lista %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fk); ?>" id="<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr(get_theme_mod($fk, call_user_func($d, $fk))); ?>" /></td></tr>
                    <?php endfor; ?>
                </table>
                <h3><?php esc_html_e('Passe mensal', 'EventsHouse'); ?></h3>
                <table class="form-table">
                    <tr><th><label for="ilha_passes_monthly_badge"><?php esc_html_e('Etiqueta (ex. Mais popular)', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="regular-text" name="ilha_passes_monthly_badge" id="ilha_passes_monthly_badge" value="<?php echo esc_attr(get_theme_mod('ilha_passes_monthly_badge', call_user_func($d, 'ilha_passes_monthly_badge'))); ?>" /></td></tr>
                    <?php
                    foreach (array('ilha_passes_monthly_title', 'ilha_passes_monthly_tagline', 'ilha_passes_monthly_btn') as $fname) :
                        ?>
                    <tr><th><label for="<?php echo esc_attr($fname); ?>"><?php echo esc_html(str_replace('ilha_passes_monthly_', '', $fname)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fname); ?>" id="<?php echo esc_attr($fname); ?>" value="<?php echo esc_attr(get_theme_mod($fname, call_user_func($d, $fname))); ?>" /></td></tr>
                    <?php endforeach; ?>
                    <?php for ($i = 1; $i <= 4; $i++) : $fk = "ilha_passes_monthly_li{$i}"; ?>
                    <tr><th><label for="<?php echo esc_attr($fk); ?>"><?php echo esc_html(sprintf(__('Lista %d', 'EventsHouse'), $i)); ?></label></th>
                        <td><input type="text" class="large-text" name="<?php echo esc_attr($fk); ?>" id="<?php echo esc_attr($fk); ?>" value="<?php echo esc_attr(get_theme_mod($fk, call_user_func($d, $fk))); ?>" /></td></tr>
                    <?php endfor; ?>
                </table>
            </div>
        </div>

        <div class="postbox" style="margin-top:12px;">
            <div class="postbox-header"><h2 class="hndle"><?php esc_html_e('Rodapé da página & regras', 'EventsHouse'); ?></h2></div>
            <div class="inside">
                <table class="form-table">
                    <tr><th><label for="ilha_passes_footnote"><?php esc_html_e('Nota de rodapé (*)', 'EventsHouse'); ?></label></th>
                        <td><textarea name="ilha_passes_footnote" id="ilha_passes_footnote" class="large-text" rows="2"><?php echo esc_textarea(get_theme_mod('ilha_passes_footnote', call_user_func($d, 'ilha_passes_footnote'))); ?></textarea></td></tr>
                    <tr><th><label for="ilha_passes_regras_title"><?php esc_html_e('Título da caixa de regras', 'EventsHouse'); ?></label></th>
                        <td><input type="text" class="large-text" name="ilha_passes_regras_title" id="ilha_passes_regras_title" value="<?php echo esc_attr(get_theme_mod('ilha_passes_regras_title', call_user_func($d, 'ilha_passes_regras_title'))); ?>" /></td></tr>
                    <tr>
                        <th scope="row"><label for="ilha_passes_regras_html"><?php esc_html_e('Texto das regras (omissão)', 'EventsHouse'); ?></label></th>
                        <td>
                            <p class="description"><?php esc_html_e('Usado quando a página «Manhãs na Ilha» não tem conteúdo no editor do WordPress. Pode formatar títulos, subtítulos, listas e negritos.', 'EventsHouse'); ?></p>
                            <?php
                            wp_editor(
                                EventsHouse_ilha_passes_get_regras_html_for_editor(),
                                'ilha_passes_regras_html',
                                array(
                                    'textarea_name' => 'ilha_passes_regras_html',
                                    'textarea_rows' => 14,
                                    'media_buttons' => false,
                                    'teeny'         => false,
                                    'quicktags'     => true,
                                )
                            );
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
    <?php
}
