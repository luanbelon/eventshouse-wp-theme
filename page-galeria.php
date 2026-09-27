<?php
/**
 * Template Name: Galeria
 * Gallery Page Template
 *
 * @package EventsHouse
 */

// Ler imagens da galeria da base de dados (opções admin)
$gallery_page_defaults = array(
    array('url' => EventsHouse_URI . '/assets/images/gallery-1.jpg', 'alt' => 'Sala principal', 'category' => 'espaco'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-2.jpg', 'alt' => 'Área de brincadeiras', 'category' => 'espaco'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-3.jpg', 'alt' => 'Festa de aniversário', 'category' => 'festas'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-4.jpg', 'alt' => 'Decoração temática', 'category' => 'decoracao'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-5.jpg', 'alt' => 'Cozinha equipada', 'category' => 'espaco'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-6.jpg', 'alt' => 'Crianças a brincar', 'category' => 'festas'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-7.jpg', 'alt' => 'Decoração com balões', 'category' => 'decoracao'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-8.jpg', 'alt' => 'Exterior do espaço', 'category' => 'espaco'),
    array('url' => EventsHouse_URI . '/assets/images/hero-1.jpg', 'alt' => 'Festa temática', 'category' => 'festas'),
    array('url' => EventsHouse_URI . '/assets/images/hero-2.jpg', 'alt' => 'Mesa decorada', 'category' => 'decoracao'),
    array('url' => EventsHouse_URI . '/assets/images/hero-3.jpg', 'alt' => 'Área de jogos', 'category' => 'espaco'),
    array('url' => EventsHouse_URI . '/assets/images/gallery-1.jpg', 'alt' => 'Celebração', 'category' => 'festas'),
);
$gallery_items = get_option('eventshouse_gallery_page', $gallery_page_defaults);

$category_labels = array(
    'espaco' => 'Espaço',
    'festas' => 'Festas',
    'decoracao' => 'Decoração',
);

// Descobrir categorias usadas
$used_categories = array();
foreach ($gallery_items as $item) {
    if (!empty($item['category']) && !in_array($item['category'], $used_categories)) {
        $used_categories[] = $item['category'];
    }
}

get_header();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Galeria</h1>
        <p>Explore o nosso espaço e veja momentos das nossas festas</p>
    </div>
</div>

<!-- ═══════════════════════════════════════
     GALLERY
     ═══════════════════════════════════════ -->
<section class="gallery-page-section" id="gallery-page">
    <div class="container container-wide">
        <!-- Filters -->
        <div class="gallery-filters reveal">
            <button class="filter-btn active" data-filter="all">Todos</button>
            <?php foreach ($used_categories as $cat): ?>
                <button class="filter-btn" data-filter="<?php echo esc_attr($cat); ?>">
                    <?php echo esc_html($category_labels[$cat] ?? ucfirst($cat)); ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Masonry Grid -->
        <div class="gallery-masonry reveal" id="galleryGrid">
            <?php foreach ($gallery_items as $item): ?>
                <?php if (!empty($item['url'])): ?>
                    <div class="masonry-item" data-category="<?php echo esc_attr($item['category']); ?>">
                        <img src="<?php echo esc_url($item['url']); ?>" alt="<?php echo esc_attr($item['alt']); ?>"
                            loading="lazy">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose" aria-label="Fechar">&times;</button>
    <button class="lightbox-nav lightbox-prev" id="lightboxPrev" aria-label="Anterior">‹</button>
    <div class="lightbox-content">
        <img id="lightboxImage" src="" alt="Imagem ampliada">
    </div>
    <button class="lightbox-nav lightbox-next" id="lightboxNext" aria-label="Próxima">›</button>
</div>

<?php get_footer();
