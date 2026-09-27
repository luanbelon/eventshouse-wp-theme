<?php
/**
 * Template Name: Verao na Ilha
 *
 * @package EventsHouse
 */

get_header();

if (!function_exists('eh_verao_ilha_txt')) {
    /**
     * @param string $key Theme mod key (verao_ilha_*).
     */
    function eh_verao_ilha_txt($key)
    {
        $default = EventsHouse_verao_ilha_default($key);
        $mod     = get_theme_mod($key, null);
        if ($mod !== null && $mod !== '') {
            return $mod;
        }
        $opt = get_option($key, null);
        if ($opt !== null && $opt !== '') {
            return $opt;
        }

        return $default;
    }
}

if (!function_exists('eh_verao_ilha_bullet_lines')) {
    /**
     * @param string $key Theme mod com linhas separadas por newline.
     *
     * @return string[]
     */
    function eh_verao_ilha_bullet_lines($key)
    {
        $raw   = eh_verao_ilha_txt($key);
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $out   = array();
        foreach ($lines as $line) {
            $t = trim((string) $line);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out;
    }
}

if (!function_exists('eh_verao_ilha_faq_pairs')) {
    /**
     * FAQ: até 5 pares (pergunta + resposta), ordem 1–5; só entradas com pergunta não vazia.
     *
     * @return array<int, array{q: string, a: string}>
     */
    function eh_verao_ilha_faq_pairs()
    {
        $out = array();
        for ($i = 1; $i <= 5; $i++) {
            $q = trim((string) eh_verao_ilha_txt('verao_ilha_faq_' . $i . '_question'));
            if ($q === '') {
                continue;
            }
            $out[] = array(
                'q' => $q,
                'a' => trim((string) eh_verao_ilha_txt('verao_ilha_faq_' . $i . '_answer')),
            );
        }

        return $out;
    }
}

$verao_hero_btn_href = trim((string) eh_verao_ilha_txt('verao_ilha_hero_btn_url'));
if ($verao_hero_btn_href === '') {
    $verao_hero_btn_href = home_url('/contato/');
}
$verao_cta_href = trim((string) eh_verao_ilha_txt('verao_ilha_cta_url'));
if ($verao_cta_href === '') {
    $verao_cta_href = home_url('/contato/');
}

$verao_ilha_render_section_head = static function ($title_key, $subtitle_key) {
    $t = trim((string) eh_verao_ilha_raw($title_key));
    $s = trim((string) eh_verao_ilha_raw($subtitle_key));
    if ($t === '' && $s === '') {
        return;
    }
    echo '<div class="verao-ilha-section-head">';
    if ($t !== '') {
        echo '<div class="section-title"><h2>' . esc_html($t) . '</h2></div>';
    }
    if ($s !== '') {
        echo '<p class="section-subtitle">' . esc_html($s) . '</p>';
    }
    echo '</div>';
};

$morn_icon = trim((string) eh_verao_ilha_asset_url('verao_ilha_schedule_morning_icon_url'));
$aft_icon  = trim((string) eh_verao_ilha_asset_url('verao_ilha_schedule_afternoon_icon_url'));
$morn_ill  = trim((string) eh_verao_ilha_asset_url('verao_ilha_schedule_morning_illustration_url'));
$aft_ill   = trim((string) eh_verao_ilha_asset_url('verao_ilha_schedule_afternoon_illustration_url'));
?>

<div class="verao-ilha-fullpage">

<section class="hero-section mode-slide verao-ilha-page" id="hero">
    <div class="hero-background">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
    </div>

    <div class="verao-ilha-hero-inner">
        <div class="container hero-layout verao-ilha-layout">
            <div class="hero-content verao-ilha-hero-content">
                <div class="hero-badge"><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_hero_badge')); ?></div>
                <h1 class="hero-title"><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_hero_title')); ?></h1>
                <p class="hero-subtitle">
                    <?php echo esc_html(eh_verao_ilha_txt('verao_ilha_hero_subtitle')); ?>
                </p>
                <a href="<?php echo esc_url($verao_hero_btn_href); ?>" class="btn btn-primary verao-ilha-hero-cta">
                    <?php echo esc_html(eh_verao_ilha_txt('verao_ilha_hero_btn_text')); ?>
                </a>
            </div>

            <div class="hero-slider hero-slider--desktop verao-ilha-hero-image">
                <div class="hero-slide active">
                    <img src="<?php echo esc_url(eh_verao_ilha_asset_url('verao_ilha_hero_banner_url')); ?>" alt="<?php echo esc_attr(eh_verao_ilha_txt('verao_ilha_hero_banner_alt')); ?>" loading="eager" decoding="async" fetchpriority="high">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="benefits-section verao-ilha-benefits-panel">
    <div class="container">
        <?php $verao_ilha_render_section_head('verao_ilha_features_section_title', 'verao_ilha_features_section_subtitle'); ?>
        <div class="benefits-grid">
            <?php for ($vf = 1; $vf <= 4; $vf++) : ?>
            <article class="benefit-card">
                <div class="icon-wrapper">
                    <img src="<?php echo esc_url(eh_verao_ilha_asset_url('verao_ilha_feature_' . $vf . '_image_url')); ?>" alt="<?php echo esc_attr(eh_verao_ilha_txt('verao_ilha_feature_' . $vf . '_title')); ?>">
                </div>
                <h3><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_feature_' . $vf . '_title')); ?></h3>
                <p><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_feature_' . $vf . '_text')); ?></p>
            </article>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="verao-ilha-program">
    <section class="schedules-section verao-ilha-schedules-panel">
        <div class="container">
            <?php
            $sched_h2 = trim((string) eh_verao_ilha_raw('verao_ilha_schedule_section_title'));
            $sched_sub = trim((string) eh_verao_ilha_raw('verao_ilha_schedule_section_subtitle'));
            if ($sched_h2 !== '') {
                echo '<h2 class="schedules-section__title">' . esc_html($sched_h2) . '</h2>';
            }
            if ($sched_sub !== '') {
                echo '<p class="subtitle">' . esc_html($sched_sub) . '</p>';
            }
            ?>
            <div class="schedules-grid verao-ilha-schedules-grid">
                <div class="schedule-card morning">
                    <div class="schedule-header">
                        <?php if ($morn_icon !== '') : ?>
                        <img src="<?php echo esc_url($morn_icon); ?>" alt="" width="60" height="60" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <div class="schedule-header__text">
                            <h3><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_slot_morning_label')); ?></h3>
                            <p><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_slot_morning_time')); ?></p>
                        </div>
                    </div>
                    <?php
                    $bullets_am = eh_verao_ilha_bullet_lines('verao_ilha_slot_morning_bullets');
                    if (!empty($bullets_am)) :
                        ?>
                    <ul class="schedule-list">
                        <?php foreach ($bullets_am as $li) : ?>
                        <li><span class="check-icon check-icon--morning" aria-hidden="true"></span><?php echo esc_html($li); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if ($morn_ill !== '') : ?>
                    <img src="<?php echo esc_url($morn_ill); ?>" alt="" class="illustration" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>

                <div class="schedule-card afternoon">
                    <div class="schedule-header">
                        <?php if ($aft_icon !== '') : ?>
                        <img src="<?php echo esc_url($aft_icon); ?>" alt="" width="60" height="60" loading="lazy" decoding="async">
                        <?php endif; ?>
                        <div class="schedule-header__text">
                            <h3><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_slot_afternoon_label')); ?></h3>
                            <p><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_slot_afternoon_time')); ?></p>
                        </div>
                    </div>
                    <?php
                    $bullets_pm = eh_verao_ilha_bullet_lines('verao_ilha_slot_afternoon_bullets');
                    if (!empty($bullets_pm)) :
                        ?>
                    <ul class="schedule-list">
                        <?php foreach ($bullets_pm as $li) : ?>
                        <li><span class="check-icon check-icon--afternoon" aria-hidden="true"></span><?php echo esc_html($li); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if ($aft_ill !== '') : ?>
                    <img src="<?php echo esc_url($aft_ill); ?>" alt="" class="illustration" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
            </div>
            <p class="info-text">
                <span class="info-icon" aria-hidden="true"></span>
                <?php echo esc_html(eh_verao_ilha_txt('verao_ilha_schedule_note')); ?>
            </p>
        </div>
    </section>

    <section class="section ilha-includes-section verao-ilha-meta-band">
        <div class="container">
            <?php $verao_ilha_render_section_head('verao_ilha_meta_section_title', 'verao_ilha_meta_section_subtitle'); ?>
            <div class="services-grid ilha-includes-grid verao-ilha-includes-grid">
                <?php for ($vm = 1; $vm <= 3; $vm++) : ?>
                <div class="service-item ilha-include-card">
                    <div class="service-icon" aria-hidden="true"><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_meta_' . $vm . '_icon')); ?></div>
                    <span class="service-name"><?php echo esc_html(eh_verao_ilha_txt('verao_ilha_meta_' . $vm . '_name')); ?></span>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <div class="container verao-ilha-cta-wrap">
        <?php $verao_ilha_render_section_head('verao_ilha_cta_section_title', 'verao_ilha_cta_section_subtitle'); ?>
        <div class="verao-ilha-cta-area">
            <a href="<?php echo esc_url($verao_cta_href); ?>" class="btn btn-cta verao-ilha-cta-btn">
                <?php echo esc_html(eh_verao_ilha_txt('verao_ilha_cta_text')); ?>
            </a>
        </div>
    </div>
</section>

<?php
$pricing_icon = trim((string) eh_verao_ilha_asset_url('verao_ilha_pricing_icon_url'));
if ($pricing_icon === '' || strpos($pricing_icon, 'icon-tarde.svg') !== false) {
    $pricing_icon = EventsHouse_URI . '/assets/images/verao-ilha/icon-calendario.svg';
}
$pricing_note = trim((string) eh_verao_ilha_txt('verao_ilha_pricing_note'));
$pricing_tiers = array();
for ($vp = 1; $vp <= 4; $vp++) {
    $plabel = trim((string) eh_verao_ilha_txt('verao_ilha_price_' . $vp . '_label'));
    $pamount = trim((string) eh_verao_ilha_txt('verao_ilha_price_' . $vp . '_amount'));
    if ($plabel === '' && $pamount === '') {
        continue;
    }
    $pricing_tiers[] = array(
        'label'  => $plabel,
        'amount' => $pamount,
        'badge'  => trim((string) eh_verao_ilha_txt('verao_ilha_price_' . $vp . '_badge')),
    );
}
$pricing_title = trim((string) eh_verao_ilha_raw('verao_ilha_pricing_section_title'));
$pricing_sub   = trim((string) eh_verao_ilha_raw('verao_ilha_pricing_section_subtitle'));
if (!empty($pricing_tiers) || $pricing_title !== '' || $pricing_sub !== '' || $pricing_note !== '') :
    ?>
<section class="benefits-section verao-ilha-price-section" id="precos"<?php echo $pricing_title !== '' ? ' aria-labelledby="verao-ilha-pricing-heading"' : ' aria-label="' . esc_attr__('Preços', 'EventsHouse') . '"'; ?>>
    <div class="container">
        <?php $verao_ilha_render_section_head('verao_ilha_pricing_section_title', 'verao_ilha_pricing_section_subtitle'); ?>
        <?php if (!empty($pricing_tiers)) : ?>
        <div class="price-grid">
            <?php foreach ($pricing_tiers as $tier) : ?>
            <article class="price-card">
                <div class="price-card__icon">
                    <?php if ($pricing_icon !== '') : ?>
                    <img src="<?php echo esc_url($pricing_icon); ?>" alt="<?php echo esc_attr($tier['label']); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                </div>
                <h3><?php echo esc_html($tier['label']); ?></h3>
                <p class="price-card__amount"><?php echo esc_html($tier['amount']); ?></p>
                <?php if ($tier['badge'] !== '') : ?>
                <p class="price-card__badge"><?php echo esc_html($tier['badge']); ?></p>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($pricing_note !== '') : ?>
        <p class="price-note">
            <span class="price-note__icon" aria-hidden="true">
                <img src="<?php echo esc_url(EventsHouse_URI . '/assets/images/verao-ilha/icon-oferta.svg'); ?>" alt="" width="28" height="28" loading="lazy" decoding="async">
            </span>
            <span class="price-note__text"><?php echo esc_html($pricing_note); ?></span>
        </p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php
/**
 * FAQ do tema (acordeão): não duplique listas no conteúdo da página WordPress — apague HTML antigo no editor desta página.
 */
$verao_faq_pairs = eh_verao_ilha_faq_pairs();
$tfaq            = trim((string) eh_verao_ilha_raw('verao_ilha_faq_section_title'));
$sfaq            = trim((string) eh_verao_ilha_raw('verao_ilha_faq_section_subtitle'));
if (!empty($verao_faq_pairs) || $tfaq !== '' || $sfaq !== '') :
    ?>
<section class="verao-ilha-faq-section verao-ilha-faq-section--accordion"<?php echo $tfaq !== '' ? ' aria-labelledby="verao-ilha-faq-heading"' : ' aria-label="' . esc_attr__('Perguntas frequentes', 'EventsHouse') . '"'; ?>>
    <div class="container">
        <?php if ($tfaq !== '') : ?>
        <h2 id="verao-ilha-faq-heading" class="verao-ilha-faq-section__title"><?php echo esc_html($tfaq); ?></h2>
        <?php endif; ?>
        <?php if ($sfaq !== '') : ?>
        <p class="verao-ilha-faq-section__subtitle"><?php echo esc_html($sfaq); ?></p>
        <?php endif; ?>
        <?php if (!empty($verao_faq_pairs)) : ?>
        <div class="verao-ilha-faq-list">
            <?php foreach ($verao_faq_pairs as $fi => $pair) : ?>
            <details class="verao-ilha-faq-item">
                <summary class="verao-ilha-faq-item__summary">
                    <span class="verao-ilha-faq-item__q"><?php echo esc_html($pair['q']); ?></span>
                    <span class="verao-ilha-faq-item__toggle" aria-hidden="true"></span>
                </summary>
                <div class="verao-ilha-faq-item__body">
                    <p class="verao-ilha-faq-item__a"><?php echo nl2br(esc_html($pair['a'])); ?></p>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

</div>

<?php get_footer(); ?>
