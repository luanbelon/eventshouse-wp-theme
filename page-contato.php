<?php
/**
 * Template Name: Contato
 * Contact Page Template
 *
 * @package EventsHouse
 */

$phone = get_theme_mod('contact_phone', EventsHouse_DEFAULT_CONTACT_PHONE);
$email = get_theme_mod('contact_email', EventsHouse_DEFAULT_CONTACT_EMAIL);
$address = get_theme_mod('contact_address', 'Rua Virgílio Martinho 3A, 1900-821 Lisboa');
$schedule = get_theme_mod('contato_schedule', "Segunda a Domingo\n10h00 - 20h00");
$maps_input   = get_theme_mod('contato_maps_url', '');
$maps_iframe  = EventsHouse_contact_map_iframe_src($maps_input, $address);
$maps_open    = EventsHouse_contact_map_open_url($maps_input, $address);

$page_title = get_theme_mod('contato_header_title', 'Contactos 💌');
$page_subtitle = get_theme_mod('contato_header_subtitle', 'Estamos aqui para ajudar! Entre em contacto connosco');

get_header();
?>

<!-- Page Header -->
<div class="page-header">

    <div class="container">
        <h1><?php echo esc_html($page_title); ?></h1>
        <p><?php echo esc_html($page_subtitle); ?></p>
    </div>
</div>

<!-- ═══════════════════════════════════════
     CONTACT SECTION
     ═══════════════════════════════════════ -->
<section class="contact-section section" id="contact">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form reveal" id="contactForm">
                <h3 style="margin-bottom:var(--space-xl);">Envie-nos uma mensagem</h3>

                <div class="form-message" id="formMessage"></div>

                <form id="EventsHouseContactForm" method="post">
                    <input type="hidden" name="action" value="EventsHouse_contact">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('EventsHouse_nonce'); ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact-name">Nome *</label>
                            <input type="text" id="contact-name" name="name" class="form-input" required
                                placeholder="O seu nome">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-email">Email *</label>
                            <input type="email" id="contact-email" name="email" class="form-input" required
                                placeholder="email@exemplo.pt">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="contact-phone">Telefone</label>
                            <input type="tel" id="contact-phone" name="phone" class="form-input"
                                placeholder="+351 900 000 000">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="contact-date">Data do Evento</label>
                            <input type="date" id="contact-date" name="event_date" class="form-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="contact-message">Mensagem *</label>
                        <textarea id="contact-message" name="message" class="form-textarea" required
                            placeholder="Conte-nos sobre o evento que pretende..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn" style="width:100%;">
                        Enviar Mensagem 📤
                    </button>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="contact-info-section">
                <div class="contact-info-card reveal">
                    <div class="contact-info-icon">📍</div>
                    <div class="contact-info-text">
                        <h4>Morada</h4>
                        <p><?php echo esc_html($address); ?></p>
                    </div>
                </div>

                <div class="contact-info-card reveal">
                    <div class="contact-info-icon">📞</div>
                    <div class="contact-info-text">
                        <h4>Telefone</h4>
                        <p><a href="tel:<?php echo esc_attr(str_replace(' ', '', $phone)); ?>">
                                <?php echo esc_html($phone); ?>
                            </a></p>
                    </div>
                </div>

                <div class="contact-info-card reveal">
                    <div class="contact-info-icon">✉️</div>
                    <div class="contact-info-text">
                        <h4>Email</h4>
                        <p><a href="mailto:<?php echo esc_attr($email); ?>">
                                <?php echo esc_html($email); ?>
                        </p></a>
                    </div>
                </div>

                <div class="contact-info-card reveal">
                    <div class="contact-info-icon">🕐</div>
                    <div class="contact-info-text">
                        <h4>Horário</h4>
                        <p><?php echo nl2br(esc_html($schedule)); ?></p>
                    </div>
                </div>

                <!-- Map -->
                <?php if ($maps_iframe !== '') : ?>
                <div class="contact-map reveal">
                    <div class="contact-map-frame">
                        <iframe src="<?php echo esc_url($maps_iframe); ?>" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e('Localização no mapa', 'EventsHouse'); ?>">
                        </iframe>
                    </div>
                    <?php if ($maps_open !== '') : ?>
                    <p class="contact-map-open-link">
                        <a href="<?php echo esc_url($maps_open); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Abrir no Google Maps', 'EventsHouse'); ?> ↗</a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
