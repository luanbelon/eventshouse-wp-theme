<?php
/**
 * Template Name: Coming Soon - Manhas na Ilha
 *
 * @package EventsHouse
 */

get_header();

$hero_badge = get_theme_mod('ilha_coming_soon_badge', __('Manhãs na Ilha', 'EventsHouse'));
$hero_title = get_theme_mod('ilha_coming_soon_title', __('Brevemente', 'EventsHouse'));
$hero_subtitle = get_theme_mod('ilha_coming_soon_subtitle', __('Estamos a preparar o lançamento das manhãs na ilha! Um espaço para pais e crianças durante a semana.', 'EventsHouse'));
$hero_slide_defaults = array(
    1 => array(
        'img' => EventsHouse_URI . '/assets/images/hero-1.jpg',
        'alt' => __('Manhas na Ilha - em breve', 'EventsHouse'),
    ),
    2 => array(
        'img' => EventsHouse_URI . '/assets/images/hero-2.jpg',
        'alt' => __('Manhas na Ilha - espaco', 'EventsHouse'),
    ),
    3 => array(
        'img' => EventsHouse_URI . '/assets/images/hero-3.jpg',
        'alt' => __('Manhas na Ilha - detalhes', 'EventsHouse'),
    ),
);
$hero_slides = array(
    array(
        'img' => get_theme_mod('ilha_coming_soon_slide1_image', $hero_slide_defaults[1]['img']) ?: $hero_slide_defaults[1]['img'],
        'alt' => get_theme_mod('ilha_coming_soon_slide1_alt', $hero_slide_defaults[1]['alt']) ?: $hero_slide_defaults[1]['alt'],
    ),
    array(
        'img' => get_theme_mod('ilha_coming_soon_slide2_image', $hero_slide_defaults[2]['img']) ?: $hero_slide_defaults[2]['img'],
        'alt' => get_theme_mod('ilha_coming_soon_slide2_alt', $hero_slide_defaults[2]['alt']) ?: $hero_slide_defaults[2]['alt'],
    ),
    array(
        'img' => get_theme_mod('ilha_coming_soon_slide3_image', $hero_slide_defaults[3]['img']) ?: $hero_slide_defaults[3]['img'],
        'alt' => get_theme_mod('ilha_coming_soon_slide3_alt', $hero_slide_defaults[3]['alt']) ?: $hero_slide_defaults[3]['alt'],
    ),
);
?>

<section class="hero-section mode-slide" id="hero">
    <div class="hero-background">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
    </div>

    <div class="container hero-layout">
        <div class="hero-content">
            <div class="hero-badge"><?php echo esc_html($hero_badge); ?></div>
            <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
            <p class="hero-subtitle"><?php echo esc_html($hero_subtitle); ?></p>
            <h3 style="font-size: 24px; color: var(--color-primary, #2ecc71); margin-top: 24px; margin-bottom: 12px;">
                <!-- <?php echo esc_html__('Deixe o seu email para ser o primeiro a saber', 'EventsHouse'); ?> -->
            </h3>
            <div id="omnisend-embedded-v2-69ccf8be18c8c086279c1c6a"></div>
        </div>

        <div class="hero-slider hero-slider--desktop" id="heroSlider">
            <?php foreach ($hero_slides as $slide_idx => $slide) : ?>
                <div class="hero-slide <?php echo $slide_idx === 0 ? 'active' : ''; ?>" data-slide="<?php echo (int) $slide_idx; ?>">
                    <img src="<?php echo esc_url($slide['img']); ?>" alt="<?php echo esc_attr($slide['alt']); ?>" loading="<?php echo $slide_idx === 0 ? 'eager' : 'lazy'; ?>" decoding="async" <?php echo $slide_idx === 0 ? 'fetchpriority="high"' : ''; ?>>
                </div>
            <?php endforeach; ?>
            <div class="hero-slider-dots">
                <?php for ($d = 0; $d < count($hero_slides); $d++) : ?>
                    <span class="slider-dot <?php echo $d === 0 ? 'active' : ''; ?>" data-slide="<?php echo (int) $d; ?>"></span>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
