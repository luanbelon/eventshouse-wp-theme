<?php
/**
 * Template Name: A Ilha Passes
 * Página «Manhãs na Ilha» — passes e packs (slug recomendado: manhas-na-ilha).
 *
 * @package EventsHouse
 */

get_header();

global $post;
$ilha_show_default_regras = !$post || trim((string) $post->post_content) === '';

if (!function_exists('eh_ilha_passes_txt')) {
    /**
     * @param string $key Theme mod key (ilha_passes_*).
     */
    function eh_ilha_passes_txt($key)
    {
        $default = EventsHouse_ilha_passes_default($key);
        $mod = get_theme_mod($key, null);
        if ($mod !== null && $mod !== '') {
            return $mod;
        }
        // Compatibilidade: alguns fluxos admin podem persistir estes campos em options.
        $opt = get_option($key, null);
        if ($opt !== null && $opt !== '') {
            return $opt;
        }
        return $default;
    }
}

$passes_page = get_page_by_path('manhas-na-ilha');
if (!$passes_page) {
    $passes_page = get_page_by_path('ilha-passes');
}
$passes_base = $passes_page ? get_permalink($passes_page->ID) : home_url('/manhas-na-ilha/');

$ilha_passes_recorrentes_ativos = (get_theme_mod('ilha_passes_recorrentes_show', '') === '1');

$weekly_pass_url  = $passes_base;
$monthly_pass_url = $passes_base;
$pack_1_url       = $passes_base;
$pack_6_url       = $passes_base;
$pack_12_url      = $passes_base;

$weekly_pass_id  = get_theme_mod('woocommerce_weekly_pass_id', '');
$monthly_pass_id = get_theme_mod('woocommerce_monthly_pass_id', '');
$pack_1_id       = get_theme_mod('woocommerce_pack_1_id', '');
$pack_6_id       = get_theme_mod('woocommerce_pack_6_id', '');
$pack_12_id      = get_theme_mod('woocommerce_pack_12_id', '');

if (!empty($weekly_pass_id)) {
    $weekly_pass_url = esc_url(add_query_arg('add-to-cart', absint($weekly_pass_id), $passes_base));
}
if (!empty($monthly_pass_id)) {
    $monthly_pass_url = esc_url(add_query_arg('add-to-cart', absint($monthly_pass_id), $passes_base));
}
if (!empty($pack_1_id)) {
    $pack_1_url = esc_url(add_query_arg('add-to-cart', absint($pack_1_id), $passes_base));
}
if (!empty($pack_6_id)) {
    $pack_6_url = esc_url(add_query_arg('add-to-cart', absint($pack_6_id), $passes_base));
}
if (!empty($pack_12_id)) {
    $pack_12_url = esc_url(add_query_arg('add-to-cart', absint($pack_12_id), $passes_base));
}

/**
 * Preço WooCommerce por ID.
 *
 * @param string     $id      Product ID.
 * @param string|int $default Default.
 */
function ilha_get_price_by_id($id, $default = '??')
{
    if (empty($id) || !class_exists('WooCommerce')) {
        return $default;
    }
    $product = wc_get_product($id);
    if ($product) {
        return $product->get_price();
    }
    return $default;
}

$pack_1_price   = ilha_get_price_by_id($pack_1_id, '15');
$pack_6_price   = ilha_get_price_by_id($pack_6_id, '80');
$pack_12_price  = ilha_get_price_by_id($pack_12_id, '150');
$weekly_price   = ilha_get_price_by_id($weekly_pass_id, '15');
$monthly_price  = ilha_get_price_by_id($monthly_pass_id, '49');

$vagas_disponiveis = '';
if (!empty($monthly_pass_id) && class_exists('WooCommerce')) {
    $product_monthly = wc_get_product($monthly_pass_id);
    if ($product_monthly && $product_monthly->managing_stock()) {
        $vagas_disponiveis = $product_monthly->get_stock_quantity();
    }
}

$vagas_tpl = eh_ilha_passes_txt('ilha_passes_vagas_template');

$ilha_extra_adult_price_html = '';
$ilha_extra_child_price_html = '';
if (class_exists('WooCommerce') && function_exists('EventsHouse_ilha_extra_product_ids')) {
    $ilha_extras_ids = EventsHouse_ilha_extra_product_ids();
    $ilha_pa = wc_get_product($ilha_extras_ids['adult']);
    if ($ilha_pa) {
        $ilha_extra_adult_price_html = wc_price($ilha_pa->get_price());
    }
    $ilha_pc = wc_get_product($ilha_extras_ids['child']);
    if ($ilha_pc) {
        $ilha_extra_child_price_html = wc_price($ilha_pc->get_price());
    }
}

if (!function_exists('eh_ilha_render_pack_buy_block')) {
    /**
     * Extras opcionais + botão «Comprar» (AJAX pack + extras).
     * Quantidades iniciais a zero; aviso de preço por entrada/manhã no bloco de texto.
     *
     * @param string $suffix Id único para os inputs.
     */
    function eh_ilha_render_pack_buy_block($pack_id, $suffix, $btn_class, $btn_text, $fallback_url, $adult_price_html, $child_price_html)
    {
        $pack_id = absint($pack_id);
        if (!class_exists('WooCommerce') || !$pack_id) {
            echo '<a href="' . esc_url($fallback_url) . '" class="' . esc_attr($btn_class) . '" style="width: 100%; display: block; text-align: center;">' . esc_html($btn_text) . '</a>';

            return;
        }

        $entrances = function_exists('EventsHouse_ilha_entrances_for_pack_id')
            ? EventsHouse_ilha_entrances_for_pack_id($pack_id)
            : 1;
        ?>
        <div class="eh-ilha-extras" style="margin: var(--space-md) 0 0; padding-top: var(--space-md); border-top: 1px solid #eaeded; font-size: 0.88rem; color: var(--color-text);">
            <p style="margin: 0 0 6px; font-weight: 600; color: var(--color-primary-dark);"><?php esc_html_e('Extras por entrada (cada manhã)', 'EventsHouse'); ?></p>
            <p class="eh-ilha-extras__hint" style="margin: 0 0 10px; font-size: 0.82rem; color: var(--color-text-light); line-height: 1.45;"><?php esc_html_e('Preço por manhã: adicione um extra por cada dia em que queira levar alguém extra.', 'EventsHouse'); ?></p>
            <div style="display:flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
                <label for="eh-ilha-extra-a-<?php echo esc_attr($suffix); ?>" style="flex:1;"><?php esc_html_e('Adultos extra', 'EventsHouse'); ?> <span style="font-weight: 400; color: var(--color-text-light);"><?php esc_html_e('(por entrada)', 'EventsHouse'); ?></span><?php if ($adult_price_html !== '') : ?> <span style="color: var(--color-text-light); font-weight: normal;">(<?php echo wp_kses_post($adult_price_html); ?>)</span><?php endif; ?></label>
                <input type="number" id="eh-ilha-extra-a-<?php echo esc_attr($suffix); ?>" class="eh-ilha-extra-adults" min="0" max="20" value="0" inputmode="numeric" aria-label="<?php esc_attr_e('Quantidade de adultos extra (por entrada)', 'EventsHouse'); ?>" style="width: 56px; padding: 6px 8px; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
            <div style="display:flex; justify-content: space-between; align-items: center; gap: 8px;">
                <label for="eh-ilha-extra-c-<?php echo esc_attr($suffix); ?>" style="flex:1;"><?php esc_html_e('Crianças extra', 'EventsHouse'); ?> <span style="font-weight: 400; color: var(--color-text-light);"><?php esc_html_e('(por entrada)', 'EventsHouse'); ?></span><?php if ($child_price_html !== '') : ?> <span style="color: var(--color-text-light); font-weight: normal;">(<?php echo wp_kses_post($child_price_html); ?>)</span><?php endif; ?></label>
                <input type="number" id="eh-ilha-extra-c-<?php echo esc_attr($suffix); ?>" class="eh-ilha-extra-children" min="0" max="20" value="0" inputmode="numeric" aria-label="<?php esc_attr_e('Quantidade de crianças extra (por entrada)', 'EventsHouse'); ?>" style="width: 56px; padding: 6px 8px; border: 1px solid #ccc; border-radius: 6px;" />
            </div>
        </div>
        <button type="button" class="<?php echo esc_attr($btn_class); ?> eh-ilha-pack-buy" style="width: 100%; display: block; text-align: center; margin-top: var(--space-md); border: none; cursor: pointer; font: inherit;" data-pack-id="<?php echo esc_attr($pack_id); ?>" data-pack-entrances="<?php echo esc_attr((string) (int) $entrances); ?>"><?php echo esc_html($btn_text); ?></button>
        <?php
    }
}
?>

<?php
$ilha_gcal_ok = !empty(get_option('eventshouse_gcal_api_key', '')) && !empty(get_option('eventshouse_gcal_ilha_calendar_id', ''));

$ilha_intro_img = trim((string) eh_ilha_passes_txt('ilha_passes_intro_image_url'));
$ilha_intro_has_image = ($ilha_intro_img !== '');
$ilha_intro_btn_url = trim((string) eh_ilha_passes_txt('ilha_passes_intro_btn_url'));
$ilha_intro_btn_href = '';
if ($ilha_intro_btn_url !== '') {
    if ($ilha_intro_btn_url[0] === '#') {
        $ilha_intro_btn_href = esc_url(untrailingslashit($passes_base) . $ilha_intro_btn_url);
    } else {
        $ilha_intro_btn_href = esc_url($ilha_intro_btn_url);
    }
}
$ilha_intro_btn_text = trim((string) eh_ilha_passes_txt('ilha_passes_intro_btn_text'));
?>
<section class="section ilha-intro-section" id="ilha-intro">
    <div class="container ilha-intro-container">
        <div class="ilha-intro-grid<?php echo $ilha_intro_has_image ? '' : ' ilha-intro-grid--text-only'; ?>">
            <div class="ilha-intro-col ilha-intro-col--text">
                <h1 class="ilha-intro-title"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_intro_title')); ?></h1>
                <?php
                $ilha_intro_body = eh_ilha_passes_txt('ilha_passes_intro_body');
                if (trim((string) $ilha_intro_body) !== '') {
                    $ilha_intro_norm = str_replace("\r\n", "\n", (string) $ilha_intro_body);
                    foreach (array_filter(array_map('trim', preg_split('/\n\s*\n/', $ilha_intro_norm))) as $ilha_intro_p) {
                        echo '<p class="ilha-intro-lead">' . esc_html($ilha_intro_p) . '</p>';
                    }
                }
                ?>
                <?php if ($ilha_intro_btn_text !== '' && $ilha_intro_btn_href !== '') : ?>
                <a class="btn ilha-intro-cta" href="<?php echo esc_url($ilha_intro_btn_href); ?>"><span class="ilha-intro-cta-icon" aria-hidden="true">✦</span><?php echo esc_html($ilha_intro_btn_text); ?></a>
                <?php endif; ?>
                <div class="ilha-intro-checklist-card">
                    <ul class="ilha-intro-checklist">
                        <?php for ($hi = 1; $hi <= 4; $hi++) : ?>
                            <?php
                            $li_txt = trim((string) eh_ilha_passes_txt('ilha_passes_intro_li' . $hi));
                            if ($li_txt === '') {
                                continue;
                            }
                            ?>
                        <li>
                            <span class="ilha-intro-check" aria-hidden="true">✓</span>
                            <span><?php echo esc_html($li_txt); ?></span>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
            <?php if ($ilha_intro_has_image) : ?>
            <div class="ilha-intro-col ilha-intro-col--media">
                <figure class="ilha-intro-figure">
                    <img src="<?php echo esc_url($ilha_intro_img); ?>" alt="<?php echo esc_attr(eh_ilha_passes_txt('ilha_passes_intro_image_alt')); ?>" width="640" height="480" loading="eager" decoding="async" />
                </figure>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section ilha-packs-section" id="ilha-packs" style="padding: var(--space-3xl) 0; background: var(--color-white);">
    <div class="container" style="max-width: 1100px; margin: 0 auto;">

        <div style="margin-bottom: var(--space-4xl);">
            <?php if ($ilha_passes_recorrentes_ativos && $vagas_disponiveis !== '') : ?>
            <p style="text-align: center; color: var(--color-secondary); font-weight: bold; font-size: 1.1rem; margin-bottom: var(--space-lg);" class="reveal">
                <?php
                if (strpos($vagas_tpl, '%s') !== false) {
                    echo esc_html(sprintf($vagas_tpl, $vagas_disponiveis));
                } else {
                    echo esc_html($vagas_tpl . ' ' . $vagas_disponiveis);
                }
                ?>
            </p>
            <?php endif; ?>

            <div class="section-title reveal" style="margin-top: 0;">
                <h2><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_packs_title')); ?></h2>
                <p style="color: var(--color-text-light);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_packs_subtitle')); ?></p>
            </div>

            <div class="pricing-tabs reveal" style="display: flex; gap: var(--space-xl); flex-wrap: wrap; justify-content: center; margin-top: var(--space-xl);">
                <div class="pricing-card eh-ilha-pack-card" style="flex: 1; min-width: 250px; border: 2px solid #eaeded; background: var(--color-white);">
                    <h3 style="color: var(--color-primary-dark); font-size: 1.3rem;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack1_title')); ?></h3>
                    <p style="color: var(--color-text-light); margin-bottom: var(--space-lg);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack1_tagline')); ?></p>
                    <div class="pricing-value" style="color: var(--color-text); font-size: 3rem;"><?php echo esc_html($pack_1_price); ?><span class="currency">€</span></div>
                    <ul style="text-align: left; margin: var(--space-xl) 0; color: var(--color-text); line-height: 1.8;">
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack1_li1')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack1_li2')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack1_li3')); ?></li>
                    </ul>
                    <?php
                    eh_ilha_render_pack_buy_block(
                        $pack_1_id,
                        'pack1',
                        'btn btn-outline',
                        eh_ilha_passes_txt('ilha_passes_pack1_btn'),
                        $pack_1_url,
                        $ilha_extra_adult_price_html,
                        $ilha_extra_child_price_html
                    );
                    ?>
                </div>

                <div class="pricing-card eh-ilha-pack-card" style="flex: 1; min-width: 250px; border: 2px solid var(--color-primary-light); background: var(--color-white); position: relative;">
                    <h3 style="color: var(--color-primary-dark); font-size: 1.3rem;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack6_title')); ?></h3>
                    <p style="color: var(--color-text-light); margin-bottom: var(--space-lg);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack6_tagline')); ?></p>
                    <div class="pricing-value" style="color: var(--color-primary); font-size: 3rem;"><?php echo esc_html($pack_6_price); ?><span class="currency">€</span></div>
                    <ul style="text-align: left; margin: var(--space-xl) 0; color: var(--color-text); line-height: 1.8;">
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack6_li1')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack6_li2')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack6_li3')); ?></li>
                    </ul>
                    <?php
                    eh_ilha_render_pack_buy_block(
                        $pack_6_id,
                        'pack6',
                        'btn btn-primary',
                        eh_ilha_passes_txt('ilha_passes_pack6_btn'),
                        $pack_6_url,
                        $ilha_extra_adult_price_html,
                        $ilha_extra_child_price_html
                    );
                    ?>
                </div>

                <div class="pricing-card eh-ilha-pack-card" style="flex: 1; min-width: 250px; border: 2px solid var(--color-secondary); background: var(--color-bg-alt); position: relative;">
                    <div style="position: absolute; top: -15px; right: 20px; background: var(--color-accent); color: var(--color-white); padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; box-shadow: var(--shadow-sm);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_badge')); ?></div>
                    <h3 style="color: var(--color-primary-dark); font-size: 1.3rem;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_title')); ?></h3>
                    <p style="color: var(--color-text-light); margin-bottom: var(--space-lg);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_tagline')); ?></p>
                    <div class="pricing-value" style="color: var(--color-secondary); font-size: 3rem;"><?php echo esc_html($pack_12_price); ?><span class="currency">€</span></div>
                    <ul style="text-align: left; margin: var(--space-xl) 0; color: var(--color-text); line-height: 1.8;">
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_li1')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_li2')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_pack12_li3')); ?></li>
                    </ul>
                    <?php
                    eh_ilha_render_pack_buy_block(
                        $pack_12_id,
                        'pack12',
                        'btn btn-cta',
                        eh_ilha_passes_txt('ilha_passes_pack12_btn'),
                        $pack_12_url,
                        $ilha_extra_adult_price_html,
                        $ilha_extra_child_price_html
                    );
                    ?>
                </div>
            </div>

            <?php
            $ilha_notice_main = trim((string) eh_ilha_passes_txt('ilha_passes_notice_img_main'));
            $ilha_notice_bl = trim((string) eh_ilha_passes_txt('ilha_passes_notice_img_bl'));
            $ilha_notice_br = trim((string) eh_ilha_passes_txt('ilha_passes_notice_img_br'));
            $ilha_notice_alt  = eh_ilha_passes_txt('ilha_passes_notice_img_alt');
            $ilha_notice_triple = ($ilha_notice_main !== '' && $ilha_notice_bl !== '' && $ilha_notice_br !== '');
            $ilha_notice_show_media = ($ilha_notice_main !== '');
            ?>
            <aside class="ilha-passes-notice reveal<?php echo $ilha_notice_show_media ? '' : ' ilha-passes-notice--text-only'; ?>" aria-label="<?php esc_attr_e('Informação sobre vagas', 'EventsHouse'); ?>">
                <div class="ilha-passes-notice__inner">
                    <div class="ilha-passes-notice__text">
                        <p class="ilha-passes-notice__kicker">
                            <svg class="ilha-passes-notice__warn-icon" width="24" height="22" viewBox="0 0 24 22" aria-hidden="true" focusable="false">
                                <path fill="currentColor" d="M12 2L2 21h20z" />
                                <path fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" d="M12 8v5" />
                                <circle cx="12" cy="17" r="1.2" fill="#fff" />
                            </svg>
                            <span class="ilha-passes-notice__kicker-line">
                                <?php if (trim((string) eh_ilha_passes_txt('ilha_passes_notice_title_strong')) !== '') : ?>
                                <strong><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_title_strong')); ?></strong>
                                <?php endif; ?>
                                <?php if (trim((string) eh_ilha_passes_txt('ilha_passes_notice_title_rest')) !== '') : ?>
                                <span class="ilha-passes-notice__kicker-rest"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_title_rest')); ?></span>
                                <?php endif; ?>
                            </span>
                        </p>
                        <?php if (trim((string) eh_ilha_passes_txt('ilha_passes_notice_body')) !== '') : ?>
                        <p class="ilha-passes-notice__body"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_body')); ?></p>
                        <?php endif; ?>
                        <p class="ilha-passes-notice__emphasis">
                            <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_emphasis_before')); ?><em><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_emphasis_italic')); ?></em><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_emphasis_after')); ?>
                        </p>
                        <p class="ilha-passes-notice__cta">
                            <span class="ilha-passes-notice__cta-arrow" aria-hidden="true">→</span>
                            <?php if (trim((string) eh_ilha_passes_txt('ilha_passes_notice_cta_strong')) !== '') : ?>
                            <strong class="ilha-passes-notice__cta-strong"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_cta_strong')); ?></strong>
                            <?php endif; ?>
                            <?php if (trim((string) eh_ilha_passes_txt('ilha_passes_notice_cta_rest')) !== '') : ?>
                            <span class="ilha-passes-notice__cta-rest"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_cta_rest')); ?></span>
                            <?php endif; ?>
                        </p>
                        <div class="ilha-passes-notice__footer">
                            <span class="ilha-passes-notice__stat-plus" aria-hidden="true">+</span>
                            <p class="ilha-passes-notice__stat">
                                <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_footer_before')); ?><strong class="ilha-passes-notice__stat-strong"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_footer_strong')); ?></strong><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_notice_footer_after')); ?>
                            </p>
                        </div>
                    </div>
                    <?php if ($ilha_notice_show_media) : ?>
                    <div class="ilha-passes-notice__media">
                        <?php if ($ilha_notice_triple) : ?>
                        <div class="ilha-passes-notice__collage ilha-passes-notice__collage--triple">
                            <img class="ilha-passes-notice__img ilha-passes-notice__img--main" src="<?php echo esc_url($ilha_notice_main); ?>" alt="" width="400" height="300" loading="lazy" decoding="async" />
                            <img class="ilha-passes-notice__img ilha-passes-notice__img--bl" src="<?php echo esc_url($ilha_notice_bl); ?>" alt="" width="200" height="150" loading="lazy" decoding="async" />
                            <img class="ilha-passes-notice__img ilha-passes-notice__img--br" src="<?php echo esc_url($ilha_notice_br); ?>" alt="" width="200" height="150" loading="lazy" decoding="async" />
                        </div>
                        <?php else : ?>
                        <div class="ilha-passes-notice__collage ilha-passes-notice__collage--single">
                            <img class="ilha-passes-notice__img ilha-passes-notice__img--single" src="<?php echo esc_url($ilha_notice_main); ?>" alt="<?php echo esc_attr($ilha_notice_alt); ?>" width="520" height="400" loading="lazy" decoding="async" />
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </aside>

            <?php if ($ilha_passes_recorrentes_ativos) : ?>
            <div class="section-title reveal" style="margin-top: 60px;">
                <h2><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_recorrentes_title')); ?></h2>
                <p style="color: var(--color-text-light);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_recorrentes_subtitle')); ?></p>
            </div>

            <div class="pricing-tabs reveal" style="display: flex; gap: var(--space-xl); flex-wrap: wrap; justify-content: center; margin-top: var(--space-xl);">
                <div class="pricing-card" style="flex: 1; min-width: 300px; border: 2px solid var(--color-primary-light); background: var(--color-white);">
                    <h3 style="color: var(--color-primary-dark); font-size: 1.5rem;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_title')); ?></h3>
                    <p style="color: var(--color-text-light); margin-bottom: var(--space-lg);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_tagline')); ?></p>
                    <div class="pricing-value" style="color: var(--color-primary); font-size: 3.5rem;"><?php echo esc_html($weekly_price); ?><span class="currency">€</span></div>
                    <ul style="text-align: left; margin: var(--space-xl) 0; color: var(--color-text); line-height: 1.8;">
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_li1')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_li2')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_li3')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_li4')); ?></li>
                    </ul>
                    <a href="<?php echo esc_url($weekly_pass_url); ?>" data-quantity="1" data-product_id="<?php echo esc_attr($weekly_pass_id); ?>" class="btn btn-primary add_to_cart_button ajax_add_to_cart" style="width: 100%; display: block; text-align: center;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_weekly_btn')); ?></a>
                </div>

                <div class="pricing-card" style="flex: 1; min-width: 300px; border: 2px solid var(--color-secondary); background: var(--color-bg-alt); position: relative;">
                    <div style="position: absolute; top: -15px; right: 20px; background: var(--color-accent); color: var(--color-white); padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; box-shadow: var(--shadow-sm);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_badge')); ?></div>
                    <h3 style="color: var(--color-primary-dark); font-size: 1.5rem;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_title')); ?></h3>
                    <p style="color: var(--color-text-light); margin-bottom: var(--space-lg);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_tagline')); ?></p>
                    <div class="pricing-value" style="color: var(--color-secondary); font-size: 3.5rem;"><?php echo esc_html($monthly_price); ?><span class="currency">€</span></div>
                    <ul style="text-align: left; margin: var(--space-xl) 0; color: var(--color-text); line-height: 1.8;">
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_li1')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_li2')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_li3')); ?></li>
                        <li><span style="color: var(--color-primary);">✅</span> <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_li4')); ?></li>
                    </ul>
                    <a href="<?php echo esc_url($monthly_pass_url); ?>" data-quantity="1" data-product_id="<?php echo esc_attr($monthly_pass_id); ?>" class="btn btn-cta add_to_cart_button ajax_add_to_cart" style="width: 100%; display: block; text-align: center;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_monthly_btn')); ?></a>
                </div>
            </div>
            <?php endif; ?>

            <p class="ilha-passes-pack-footnote">
                <?php echo esc_html(eh_ilha_passes_txt('ilha_passes_footnote')); ?>
            </p>
        </div>

    </div>
</section>

<section class="section ilha-cal-section" id="ilha-disponibilidade" style="padding-bottom:0;">
    <div class="container">
        <div class="eh-availability-section eh-availability-section--ilha">
            <div class="section-title reveal" style="margin-bottom: var(--space-md);">
                <h2><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_cal_title')); ?></h2>
                <p style="color: var(--color-text-light); max-width: 640px; margin: var(--space-sm) auto 0; line-height: 1.5;"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_cal_subtitle')); ?></p>
            </div>
            <?php
            EventsHouse_print_calendar_booking_block(array(
                'context'         => 'ilha',
                'extras'          => array(),
                'show_extras'     => false,
                'gcal_configured' => $ilha_gcal_ok,
                'submit_label'    => '📨 Consultar disponibilidade',
            ));
            ?>
        </div>
    </div>
</section>

<section class="section ilha-includes-section" id="ilha-o-que-inclui">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_includes_title')); ?></h2>
        </div>
        <?php
        $ilha_includes_sub = trim((string) eh_ilha_passes_txt('ilha_passes_includes_subtitle'));
        if ($ilha_includes_sub !== '') :
            ?>
        <div class="section-subtitle reveal" style="text-align:center; color: var(--color-text-light); margin-bottom: var(--space-xl); max-width: 520px; margin-left: auto; margin-right: auto;">
            <?php echo esc_html($ilha_includes_sub); ?>
        </div>
        <?php endif; ?>
        <div class="services-grid ilha-includes-grid reveal">
            <?php
            for ($ii = 1; $ii <= 4; $ii++) :
                ?>
            <div class="service-item ilha-include-card">
                <div class="service-icon"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_include' . $ii . '_icon')); ?></div>
                <span class="service-name"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_include' . $ii . '_name')); ?></span>
            </div>
                <?php
            endfor;
            ?>
        </div>
    </div>
</section>

<?php
$ilha_audience_img   = trim((string) eh_ilha_passes_txt('ilha_passes_audience_image_url'));
$ilha_audience_intro = eh_ilha_passes_txt('ilha_passes_audience_intro');
$ilha_aud_badge      = trim((string) eh_ilha_passes_txt('ilha_passes_audience_badge'));
$ilha_aud_btn        = trim((string) eh_ilha_passes_txt('ilha_passes_audience_btn_text'));
$ilha_aud_btn_url    = trim((string) eh_ilha_passes_txt('ilha_passes_audience_btn_url'));
$ilha_aud_btn_href   = '';
if ($ilha_aud_btn_url !== '') {
    if ($ilha_aud_btn_url[0] === '#') {
        $ilha_aud_btn_href = esc_url(untrailingslashit($passes_base) . $ilha_aud_btn_url);
    } else {
        $ilha_aud_btn_href = esc_url($ilha_aud_btn_url);
    }
}
?>
<section class="section ilha-audience-section" id="ilha-para-quem">
    <div class="container ilha-audience-container">
        <div class="ilha-audience-card reveal<?php echo $ilha_audience_img === '' ? ' ilha-audience-card--no-image' : ''; ?>">
            <?php if ($ilha_audience_img !== '') : ?>
            <div class="ilha-audience-card__media">
                <img src="<?php echo esc_url($ilha_audience_img); ?>" alt="<?php echo esc_attr(eh_ilha_passes_txt('ilha_passes_audience_image_alt')); ?>" loading="lazy" decoding="async" width="640" height="480" />
            </div>
            <?php endif; ?>
            <div class="ilha-audience-card__body">
                <?php if ($ilha_aud_badge !== '') : ?>
                <span class="ilha-audience-badge"><?php echo esc_html($ilha_aud_badge); ?></span>
                <?php endif; ?>
                <h2 class="ilha-audience-card__title"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_audience_title')); ?></h2>
                <?php
                if (trim((string) $ilha_audience_intro) !== '') {
                    $intro_norm = str_replace("\r\n", "\n", (string) $ilha_audience_intro);
                    foreach (array_filter(array_map('trim', preg_split('/\n\s*\n/', $intro_norm))) as $ilha_aud_p) {
                        echo '<p class="ilha-audience-card__lead">' . esc_html($ilha_aud_p) . '</p>';
                    }
                }
                ?>
                <ul class="ilha-audience-checklist">
                    <?php
                    for ($aj = 1; $aj <= 5; $aj++) :
                        ?>
                    <li>
                        <span class="ilha-audience-check" aria-hidden="true">✔</span>
                        <span><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_audience_li' . $aj)); ?></span>
                    </li>
                        <?php
                    endfor;
                    ?>
                </ul>
                <?php
                $ilha_aud_foot = trim((string) eh_ilha_passes_txt('ilha_passes_audience_footer'));
                if ($ilha_aud_foot !== '') :
                    ?>
                <p class="ilha-audience-card__footnote"><?php echo esc_html($ilha_aud_foot); ?></p>
                <?php endif; ?>
                <?php if ($ilha_aud_btn !== '' && $ilha_aud_btn_href !== '') : ?>
                <a class="btn ilha-audience-cta" href="<?php echo esc_url($ilha_aud_btn_href); ?>"><?php echo esc_html($ilha_aud_btn); ?> <span class="ilha-audience-cta__chev" aria-hidden="true">›</span></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section ilha-regras-section" id="ilha-regras" style="padding: var(--space-3xl) 0; background: var(--color-white);">
    <div class="container" style="max-width: 1100px; margin: 0 auto;">
        <div style="background: var(--color-bg-alt); padding: var(--space-2xl); border-radius: var(--radius-lg);" class="reveal">
            <h3 style="margin-bottom: var(--space-md); color: var(--color-primary-dark);"><?php echo esc_html(eh_ilha_passes_txt('ilha_passes_regras_title')); ?></h3>
            <div class="ilha-passes-regras-inner ilha-passes-regras-content entry-content" style="font-size: 0.95rem; color: var(--color-text-light); line-height: 1.7;">
                <?php
                while (have_posts()) :
                    the_post();
                    the_content();
                endwhile;
                ?>
                <?php if ($ilha_show_default_regras) : ?>
                    <?php echo wp_kses_post(EventsHouse_ilha_passes_get_regras_html_for_front()); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
jQuery(document).ready(function($) {
    var ehIlhaPasses = {
        ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('eh_ilha_pack')); ?>
    };

    $(document.body).on('click', '.eh-ilha-pack-buy', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $card = $btn.closest('.eh-ilha-pack-card');
        var packId = parseInt($btn.data('pack-id'), 10);
        if (!packId) {
            return;
        }
        var adults = parseInt($card.find('.eh-ilha-extra-adults').val(), 10);
        var children = parseInt($card.find('.eh-ilha-extra-children').val(), 10);
        if (isNaN(adults) || adults < 0) adults = 0;
        if (isNaN(children) || children < 0) children = 0;
        adults = Math.min(20, adults);
        children = Math.min(20, children);

        $btn.prop('disabled', true);
        $.post(ehIlhaPasses.ajaxUrl, {
            action: 'eh_ilha_add_pack_with_extras',
            security: ehIlhaPasses.nonce,
            pack_id: packId,
            extra_adults: adults,
            extra_children: children
        }).done(function(response) {
            if (response && response.fragments) {
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash || '', $btn]);
            }
        }).fail(function(xhr) {
            var msg = '<?php echo esc_js(__('Não foi possível adicionar ao carrinho. Tente novamente.', 'EventsHouse')); ?>';
            if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                msg = xhr.responseJSON.data.message;
            }
            window.alert(msg);
        }).always(function() {
            $btn.prop('disabled', false);
        });
    });

    if (typeof wc_add_to_cart_params === 'undefined') {
        return;
    }

    $(document.body).on('added_to_cart', function() {
        if ($('#eh-cart-toast').length === 0) {
            $('body').append('<div id="eh-cart-toast" style="position:fixed; bottom:30px; right:30px; background:#52b788; color:#fff; padding:15px 25px; border-radius:8px; box-shadow:0 5px 15px rgba(0,0,0,0.1); z-index:9999; font-weight:bold; display:none; align-items:center; gap:10px;"><span style="font-size:1.2rem;">✅</span> Adicionado ao carrinho! <a href="<?php echo function_exists('wc_get_cart_url') ? esc_url(wc_get_cart_url()) : ''; ?>" style="color:#fff; text-decoration:underline; margin-left:10px;">Ver Carrinho</a></div>');
        }
        var $toast = $('#eh-cart-toast');
        $toast.css('display', 'flex').hide().fadeIn(300);
        setTimeout(function() {
            $toast.fadeOut(400);
        }, 5000);
    });
});
</script>

<?php get_footer(); ?>
