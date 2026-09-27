<?php
/**
 * Template Name: Home Page
 * Front Page Template
 *
 * @package EventsHouse
 */

// ── Customizer vars ──────────────────────────────────────────
$banner_mode = get_theme_mod('banner_mode', 'slide');
$hero_badge = get_theme_mod('hero_badge', '🎉 Espaço de Eventos Infantis');
$hero_title = get_theme_mod('hero_title', 'Venham brincar, sonhar e ser felizes!');
$hero_subtitle = get_theme_mod('hero_subtitle', 'Um espaço mágico feito para criar momentos inesquecíveis. Festas de aniversário, eventos e muita diversão para os mais pequenos!');
$hero_btn1_text = get_theme_mod('hero_btn1_text', 'Ver Festas & Preços');
$hero_btn2_text = get_theme_mod('hero_btn2_text', 'Reservar Agora!');
$hero_btn1_url = get_theme_mod('hero_btn1_url', '');
$hero_btn2_url = get_theme_mod('hero_btn2_url', '');
if ($hero_btn1_url === '') {
    $hero_btn1_url = home_url('/orcamento');
}
if ($hero_btn2_url === '') {
    $hero_btn2_url = home_url('/contato');
}

$steps_title = get_theme_mod('steps_title', EventsHouse_home_default('steps_title'));
$steps_subtitle = get_theme_mod('steps_subtitle', EventsHouse_home_default('steps_subtitle'));

$gallery_home_title = get_theme_mod('gallery_home_title', 'O Nosso Espaço 📸');
$gallery_home_subtitle = get_theme_mod('gallery_home_subtitle', 'Descubra cada cantinho mágico do EventsHouse');

$benefits_title = get_theme_mod('benefits_title', EventsHouse_home_default('benefits_title'));
$benefits_subtitle = get_theme_mod('benefits_subtitle', EventsHouse_home_default('benefits_subtitle'));

$testimonials_title = get_theme_mod('testimonials_title', EventsHouse_home_default('testimonials_title'));
$testimonials_subtitle = get_theme_mod('testimonials_subtitle', EventsHouse_home_default('testimonials_subtitle'));

// ── Slides do Hero ───────────────────────────────────────────
$hero_slides = array();
$hero_slide_defaults = array(
    1 => array('img' => EventsHouse_URI . '/assets/images/hero-1.jpg', 'alt' => 'Espaço de festas infantis'),
    2 => array('img' => EventsHouse_URI . '/assets/images/hero-2.jpg', 'alt' => 'Área de brincadeiras'),
    3 => array('img' => EventsHouse_URI . '/assets/images/hero-3.jpg', 'alt' => 'Decoração de festas'),
);
for ($i = 1; $i <= 3; $i++) {
    $hero_slides[] = array(
        'img' => get_theme_mod("hero_slide_{$i}_image", $hero_slide_defaults[$i]['img']) ?: $hero_slide_defaults[$i]['img'],
        'alt' => get_theme_mod("hero_slide_{$i}_alt", $hero_slide_defaults[$i]['alt']) ?: $hero_slide_defaults[$i]['alt'],
    );
}

// ── Steps ────────────────────────────────────────────────────
$steps_defaults = array(
    1 => array('icon' => EventsHouse_home_default('step_1_icon'), 'title' => EventsHouse_home_default('step_1_title'), 'desc' => EventsHouse_home_default('step_1_desc')),
    2 => array('icon' => EventsHouse_home_default('step_2_icon'), 'title' => EventsHouse_home_default('step_2_title'), 'desc' => EventsHouse_home_default('step_2_desc')),
    3 => array('icon' => EventsHouse_home_default('step_3_icon'), 'title' => EventsHouse_home_default('step_3_title'), 'desc' => EventsHouse_home_default('step_3_desc')),
);
$steps = array();
for ($i = 1; $i <= 3; $i++) {
    $steps[] = array(
        'number' => $i,
        'icon' => get_theme_mod("step_{$i}_icon", $steps_defaults[$i]['icon']),
        'title' => get_theme_mod("step_{$i}_title", $steps_defaults[$i]['title']),
        'desc' => get_theme_mod("step_{$i}_desc", $steps_defaults[$i]['desc']),
    );
}

// ── Galeria Home ─────────────────────────────────────────────
$gallery_home_defaults = array(
    array('img' => EventsHouse_URI . '/assets/images/gallery-1.jpg', 'label' => 'Sala Principal'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-2.jpg', 'label' => 'Área de Brincadeiras'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-3.jpg', 'label' => 'Espaço Kids'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-4.jpg', 'label' => 'Cozinha Equipada'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-5.jpg', 'label' => 'Zona de Refeições'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-6.jpg', 'label' => 'Decoração Temática'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-7.jpg', 'label' => 'Exterior'),
    array('img' => EventsHouse_URI . '/assets/images/gallery-8.jpg', 'label' => 'Jogos e Atividades'),
);
$gallery_home_items = array();
for ($i = 1; $i <= 8; $i++) {
    $gallery_home_items[] = array(
        'img' => get_theme_mod("gallery_home_{$i}_image", $gallery_home_defaults[$i - 1]['img']) ?: $gallery_home_defaults[$i - 1]['img'],
        'label' => get_theme_mod("gallery_home_{$i}_label", $gallery_home_defaults[$i - 1]['label']),
    );
}

// ── Benefits ─────────────────────────────────────────────────
$benefits_defaults = array(
    1 => array('icon' => EventsHouse_home_default('benefit_1_icon'), 'title' => EventsHouse_home_default('benefit_1_title'), 'desc' => EventsHouse_home_default('benefit_1_desc')),
    2 => array('icon' => EventsHouse_home_default('benefit_2_icon'), 'title' => EventsHouse_home_default('benefit_2_title'), 'desc' => EventsHouse_home_default('benefit_2_desc')),
    3 => array('icon' => EventsHouse_home_default('benefit_3_icon'), 'title' => EventsHouse_home_default('benefit_3_title'), 'desc' => EventsHouse_home_default('benefit_3_desc')),
);
$benefits = array();
for ($i = 1; $i <= 3; $i++) {
    $benefits[] = array(
        'icon' => get_theme_mod("benefit_{$i}_icon", $benefits_defaults[$i]['icon']),
        'title' => get_theme_mod("benefit_{$i}_title", $benefits_defaults[$i]['title']),
        'desc' => get_theme_mod("benefit_{$i}_desc", $benefits_defaults[$i]['desc']),
    );
}

// ── Services / Amenidades ────────────────────────────────────
$services_title = get_theme_mod('services_title', EventsHouse_home_default('services_title'));
$services_subtitle = get_theme_mod('services_subtitle', EventsHouse_home_default('services_subtitle'));

// Source of truth for the admin list:
// - Appearance > Conteúdo do site stores: eventshouse_services
$services = get_option('eventshouse_services', array());

// If admin list is empty, fallback to Customizer values.
if (empty($services)) {
    for ($i = 1; $i <= 12; $i++) {
        $s_icon = get_theme_mod("service_{$i}_icon", '');
        $s_name = get_theme_mod("service_{$i}_name", '');
        if (!empty($s_name)) {
            $services[] = array('icon' => $s_icon, 'name' => $s_name);
        }
    }
}

// Last fallback to defaults so the section isn't completely empty.
if (empty($services) && empty(get_theme_mod('service_1_name'))) {
    $services = array(
        array('icon' => '❄️', 'name' => 'Ar Condicionado'),
        array('icon' => '🎮', 'name' => 'Jogos e Consolas'),
        array('icon' => '🛡️', 'name' => 'Segurança'),
        array('icon' => '🏪', 'name' => 'Microondas'),
        array('icon' => '☕', 'name' => 'Máquina de Café'),
        array('icon' => '📶', 'name' => 'Wi-Fi Grátis'),
        array('icon' => '🎵', 'name' => 'Sistema de Som'),
        array('icon' => '📺', 'name' => 'Projetor'),
        array('icon' => '🧸', 'name' => 'Brinquedos'),
        array('icon' => '🪑', 'name' => 'Mesas e Cadeiras'),
        array('icon' => '🎈', 'name' => 'Balões e Decoração'),
        array('icon' => '🍽️', 'name' => 'Pratos e Talheres'),
    );
}

// ── Testimonials ─────────────────────────────────────────────
$testimonials_defaults = array(
    1 => array(
        'text' => EventsHouse_home_default('testimonial_1_text'),
        'name' => EventsHouse_home_default('testimonial_1_name'),
        'role' => EventsHouse_home_default('testimonial_1_role'),
        'avatar' => EventsHouse_home_default('testimonial_1_avatar'),
    ),
    2 => array(
        'text' => EventsHouse_home_default('testimonial_2_text'),
        'name' => EventsHouse_home_default('testimonial_2_name'),
        'role' => EventsHouse_home_default('testimonial_2_role'),
        'avatar' => EventsHouse_home_default('testimonial_2_avatar'),
    ),
    3 => array(
        'text' => EventsHouse_home_default('testimonial_3_text'),
        'name' => EventsHouse_home_default('testimonial_3_name'),
        'role' => EventsHouse_home_default('testimonial_3_role'),
        'avatar' => EventsHouse_home_default('testimonial_3_avatar'),
    ),
);
$testimonials = array();
for ($i = 1; $i <= 3; $i++) {
    $testimonials[] = array(
        'text' => get_theme_mod("testimonial_{$i}_text", $testimonials_defaults[$i]['text']),
        'name' => get_theme_mod("testimonial_{$i}_name", $testimonials_defaults[$i]['name']),
        'role' => get_theme_mod("testimonial_{$i}_role", $testimonials_defaults[$i]['role']),
        'avatar' => get_theme_mod("testimonial_{$i}_avatar", $testimonials_defaults[$i]['avatar']),
    );
}

// ── Festas na Ilha / Manhãs na Ilha (blocos promocionais) ─────
$festas_promo_tag = get_theme_mod('festas_promo_tag', EventsHouse_home_default('festas_promo_tag'));
$festas_promo_title = get_theme_mod('festas_promo_title', EventsHouse_home_default('festas_promo_title'));
$festas_promo_desc_1 = get_theme_mod('festas_promo_desc_1', EventsHouse_home_default('festas_promo_desc_1'));
$festas_promo_desc_2 = get_theme_mod('festas_promo_desc_2', EventsHouse_home_default('festas_promo_desc_2'));
$festas_promo_foot = get_theme_mod('festas_promo_foot', EventsHouse_home_default('festas_promo_foot'));
$festas_promo_btn_text = get_theme_mod('festas_promo_btn_text', EventsHouse_home_default('festas_promo_btn_text'));
$festas_promo_btn_url = get_theme_mod('festas_promo_btn_url', '');
if ($festas_promo_btn_url === '') {
    $festas_promo_btn_url = home_url('/orcamento');
}
$festas_promo_image = get_theme_mod('festas_promo_image', EventsHouse_home_default('festas_promo_image'));
if ($festas_promo_image === '') {
    $festas_promo_image = EventsHouse_home_default('festas_promo_image');
}
$festas_promo_li = array();
for ($fi = 1; $fi <= 5; $fi++) {
    $festas_promo_li[] = get_theme_mod("festas_promo_li_{$fi}", EventsHouse_home_default("festas_promo_li_{$fi}"));
}

$manhas_promo_tag = get_theme_mod('manhas_promo_tag', EventsHouse_home_default('manhas_promo_tag'));
$manhas_promo_title = get_theme_mod('manhas_promo_title', EventsHouse_home_default('manhas_promo_title'));
$manhas_promo_desc = get_theme_mod('manhas_promo_desc', EventsHouse_home_default('manhas_promo_desc'));
$manhas_promo_foot = get_theme_mod('manhas_promo_foot', EventsHouse_home_default('manhas_promo_foot'));
$manhas_promo_btn_text = get_theme_mod('manhas_promo_btn_text', EventsHouse_home_default('manhas_promo_btn_text'));
$manhas_promo_btn_url = get_theme_mod('manhas_promo_btn_url', '');
if ($manhas_promo_btn_url === '') {
    $manhas_promo_btn_url = home_url('/manhas-na-ilha');
}
$manhas_promo_image = get_theme_mod('manhas_promo_image', '');
if ($manhas_promo_image === '') {
    $manhas_promo_image = !empty($gallery_home_items[0]['img'])
        ? $gallery_home_items[0]['img']
        : EventsHouse_URI . '/assets/images/hero-1.jpg';
}
$manhas_promo_li = array();
for ($mi = 1; $mi <= 4; $mi++) {
    $manhas_promo_li[] = get_theme_mod("manhas_promo_li_{$mi}", EventsHouse_home_default("manhas_promo_li_{$mi}"));
}

get_header();
?>

<!-- ═══════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════ -->
<section class="hero-section mode-<?php echo esc_attr($banner_mode); ?>" id="hero">
    <!-- Background shapes -->
    <div class="hero-background">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
    </div>

    <!-- Bubbles container (for bubble mode — oculto no mobile; ver slider abaixo) -->
    <?php if ($banner_mode === 'bubbles'): ?>
        <div class="hero-bubbles-container" id="herobubblesContainer" aria-hidden="true"></div>
    <?php endif; ?>

    <div class="container hero-layout">
        <?php if ($banner_mode === 'bubbles'): ?>
        <!-- Mobile: slide rectangular em vez de bolhas; só visível em ecrãs pequenos -->
        <div class="hero-slider hero-slider--bubbles-mobile" id="heroSliderBubblesMobile">
            <?php foreach ($hero_slides as $slide_idx => $slide) : ?>
                <div class="hero-slide <?php echo $slide_idx === 0 ? 'active' : ''; ?>"
                    data-slide="<?php echo (int) $slide_idx; ?>">
                    <img src="<?php echo esc_url($slide['img']); ?>" alt="<?php echo esc_attr($slide['alt']); ?>"
                        loading="<?php echo $slide_idx === 0 ? 'eager' : 'lazy'; ?>"
                        decoding="async"
                        <?php echo $slide_idx === 0 ? 'fetchpriority="high"' : ''; ?>>
                </div>
            <?php endforeach; ?>
            <div class="hero-slider-dots">
                <?php for ($d = 0; $d < count($hero_slides); $d++) : ?>
                    <span class="slider-dot <?php echo $d === 0 ? 'active' : ''; ?>" data-slide="<?php echo (int) $d; ?>"></span>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="hero-content">
            <div class="hero-badge">
                <?php echo esc_html($hero_badge); ?>
            </div>
            <h1 class="hero-title">
                <?php echo esc_html($hero_title); ?>
            </h1>
            <p class="hero-subtitle">
                <?php echo esc_html($hero_subtitle); ?>
            </p>
            <div class="hero-buttons">
                <a href="<?php echo esc_url($hero_btn1_url); ?>" class="btn btn-primary">
                    <?php echo esc_html($hero_btn1_text); ?>
                </a>
                <a href="<?php echo esc_url($hero_btn2_url); ?>" class="btn btn-secondary">
                    <?php echo esc_html($hero_btn2_text); ?>
                </a>
            </div>
        </div>

        <!-- Slider (for slide mode) -->
        <?php if ($banner_mode === 'slide'): ?>
            <div class="hero-slider hero-slider--desktop" id="heroSlider">
                <?php foreach ($hero_slides as $slide_idx => $slide): ?>
                    <div class="hero-slide <?php echo $slide_idx === 0 ? 'active' : ''; ?>"
                        data-slide="<?php echo $slide_idx; ?>">
                        <img src="<?php echo esc_url($slide['img']); ?>" alt="<?php echo esc_attr($slide['alt']); ?>"
                            loading="<?php echo $slide_idx === 0 ? 'eager' : 'lazy'; ?>"
                            decoding="async"
                            <?php echo $slide_idx === 0 ? 'fetchpriority="high"' : ''; ?>>
                    </div>
                <?php endforeach; ?>
                <div class="hero-slider-dots">
                    <?php for ($d = 0; $d < count($hero_slides); $d++): ?>
                        <span class="slider-dot <?php echo $d === 0 ? 'active' : ''; ?>" data-slide="<?php echo $d; ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════
     3 STEPS SECTION
     ═══════════════════════════════════════ -->
<section class="steps-section section" id="steps">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($steps_title); ?></h2>
        </div>
        <div class="section-subtitle reveal">
            <?php echo esc_html($steps_subtitle); ?>
        </div>

        <div class="steps-grid">
            <?php foreach ($steps as $step): ?>
                <div class="step-card reveal">
                    <span class="step-number"><?php echo $step['number']; ?></span>
                    <span class="step-icon"><?php echo esc_html($step['icon']); ?></span>
                    <h3><?php echo esc_html($step['title']); ?></h3>
                    <p><?php echo esc_html($step['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     GALLERY PREVIEW SECTION
     ═══════════════════════════════════════ -->
<?php /*
<section class="gallery-section section" id="gallery-preview">
<div class="container container-wide">
   <div class="section-title reveal">
       <h2><?php echo esc_html($gallery_home_title); ?></h2>
   </div>
   <div class="section-subtitle reveal">
       <?php echo esc_html($gallery_home_subtitle); ?>
   </div>

   <div class="gallery-grid reveal">
       <?php foreach ($gallery_home_items as $item) : ?>
       <div class="gallery-item">
           <img src="<?php echo esc_url($item['img']); ?>" alt="<?php echo esc_attr($item['label']); ?>" loading="lazy">
           <div class="gallery-overlay"><span><?php echo esc_html($item['label']); ?></span></div>
       </div>
       <?php endforeach; ?>
   </div>
</div>
</section>
*/ ?>

<!-- ═══════════════════════════════════════
     FESTAS NA ILHA (HOME PROMO)
     ═══════════════════════════════════════ -->
<section class="passes-home-section festas-home-section section" id="festas-promo">
    <div class="container container-wide">
        <div class="passes-home-card reveal">
            <div class="passes-home-media" aria-hidden="true">
                <img src="<?php echo esc_url($festas_promo_image); ?>" alt="<?php echo esc_attr($festas_promo_title); ?>" loading="lazy" decoding="async" />
            </div>
            <div class="passes-home-content">
                <div class="passes-home-tag tag-weekend"><?php echo esc_html($festas_promo_tag); ?></div>
                <h2 class="passes-home-title"><?php echo esc_html($festas_promo_title); ?></h2>
                <p class="passes-home-desc">
                    <?php echo esc_html($festas_promo_desc_1); ?>
                </p>
                <p class="passes-home-desc">
                    <?php echo esc_html($festas_promo_desc_2); ?>
                </p>

                <ul class="passes-home-list checklist">
                    <?php foreach ($festas_promo_li as $festas_li) : ?>
                        <?php if ($festas_li !== '') : ?>
                    <li><?php echo esc_html($festas_li); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <p class="passes-home-foot">
                    <?php echo esc_html($festas_promo_foot); ?>
                </p>

                <a href="<?php echo esc_url($festas_promo_btn_url); ?>" class="btn btn-orange passes-home-cta">
                    <?php echo esc_html($festas_promo_btn_text); ?> <span style="margin-left:8px;">›</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     ILHA PASSES (HOME PROMO) — oculto
     ═══════════════════════════════════════ -->
<?php /*
<section class="passes-home-section section" id="gallery-preview">
    <div class="container container-wide">
        <div class="passes-home-card reveal">
            <div class="passes-home-media" aria-hidden="true">
                <img src="<?php echo esc_url($manhas_promo_image); ?>" alt="<?php echo esc_attr($manhas_promo_title); ?>" loading="lazy" decoding="async" />
            </div>
            <div class="passes-home-content">
                <div class="passes-home-tag"><?php echo esc_html($manhas_promo_tag); ?></div>
                <h2 class="passes-home-title"><?php echo esc_html($manhas_promo_title); ?></h2>
                <p class="passes-home-desc">
                    <?php echo esc_html($manhas_promo_desc); ?>
                </p>

                <ul class="passes-home-list">
                    <?php foreach ($manhas_promo_li as $manhas_li) : ?>
                        <?php if ($manhas_li !== '') : ?>
                    <li><?php echo esc_html($manhas_li); ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>

                <p class="passes-home-foot">
                    <?php echo esc_html($manhas_promo_foot); ?>
                </p>

                <a href="<?php echo esc_url($manhas_promo_btn_url); ?>" class="btn btn-primary passes-home-cta">
                    <?php echo esc_html($manhas_promo_btn_text); ?>
                </a>
            </div>
        </div>
    </div>
</section>
*/ ?>

<!-- ═══════════════════════════════════════
     BENEFITS / WHY US SECTION
     ═══════════════════════════════════════ -->
<section class="benefits-section section" id="why-us">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($benefits_title); ?></h2>
        </div>
        <div class="section-subtitle reveal">
            <?php echo esc_html($benefits_subtitle); ?>
        </div>

        <div class="benefits-grid">
            <?php foreach ($benefits as $benefit): ?>
                <div class="benefit-card reveal">
                    <span class="benefit-icon"><?php echo esc_html($benefit['icon']); ?></span>
                    <h3><?php echo esc_html($benefit['title']); ?></h3>
                    <p><?php echo esc_html($benefit['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     WHAT WE OFFER SECTION
     ═══════════════════════════════════════ -->
<section class="services-section section" id="services">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($services_title); ?></h2>
        </div>
        <div class="section-subtitle reveal">
            <?php echo esc_html($services_subtitle); ?>
        </div>

        <div class="services-grid reveal">
            <?php foreach ($services as $service): ?>
                <div class="service-item">
                    <div class="service-icon"><?php echo esc_html($service['icon']); ?></div>
                    <span class="service-name"><?php echo esc_html($service['name']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════
     TESTIMONIALS SECTION
     ═══════════════════════════════════════ -->
<section class="testimonials-section section" id="testimonials">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($testimonials_title); ?></h2>
        </div>
        <div class="section-subtitle reveal">
            <?php echo esc_html($testimonials_subtitle); ?>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card reveal">
                    <div class="testimonial-stars">⭐⭐⭐⭐⭐</div>
                    <p class="testimonial-text">
                        <?php echo esc_html($t['text']); ?>
                    </p>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar"><?php echo esc_html($t['avatar']); ?></div>
                        <div>
                            <div class="testimonial-name"><?php echo esc_html($t['name']); ?></div>
                            <div class="testimonial-role"><?php echo esc_html($t['role']); ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer();
