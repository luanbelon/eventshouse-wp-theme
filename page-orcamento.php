<?php
/**
 * Template Name: Orçamento
 * Budget/Pricing Page Template
 *
 * @package EventsHouse
 */

// Page header
$page_title = get_option('eventshouse_orcamento_header_title', get_theme_mod('orcamento_header_title', 'Festas'));
$page_subtitle = get_option('eventshouse_orcamento_header_subtitle', get_theme_mod('orcamento_header_subtitle', 'Escolha o pacote ideal para a sua celebração'));

// Pricing tabs
$orc_fimsemana = get_option('eventshouse_orcamento_fimsemana', array());
if (empty($orc_fimsemana)) {
    $orc_fimsemana = array(
        array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '200'),
        array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '250'),
        array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => 'Acesso total durante todo o dia sem interrupções.', 'price' => '380'),
    );
}
$orc_uteis = get_option('eventshouse_orcamento_uteis', array());
if (empty($orc_uteis)) {
    $orc_uteis = array(
        array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '150'),
        array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => 'Inclui o acesso a todas as comodidades e serviço de limpeza.', 'price' => '180'),
        array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => 'Acesso total durante todo o dia sem interrupções.', 'price' => '300'),
    );
}
$orc_extras = get_option('eventshouse_orcamento_extras', array());
if (empty($orc_extras)) {
    $orc_extras = array(
        array('title' => 'Hora Extra', 'time' => '+ 1 hora', 'desc' => 'Adicione tempo extra à sua festa.', 'price' => '50'),
    );
}

// Caução (reembolsável), cobrada junto com o sinal da reserva.
$orc_caucao_valor = function_exists('EventsHouse_festa_order_default_caucao_value')
    ? EventsHouse_festa_order_default_caucao_value()
    : '50';

$orc_tab_fimsemana = get_option('eventshouse_orcamento_tab_fimsemana', 'Fim de Semana e Feriados');
$orc_tab_uteis     = get_option('eventshouse_orcamento_tab_uteis', '2ª a 6ª feira');
$orc_tab_extras    = get_option('eventshouse_orcamento_tab_extras', 'Extras');

$pricing_tabs = array(
    'fimsemana' => array(
        'label' => $orc_tab_fimsemana,
        'cards' => $orc_fimsemana
    ),
    'uteis' => array(
        'label' => $orc_tab_uteis,
        'cards' => $orc_uteis
    ),
    'extras' => array(
        'label' => $orc_tab_extras,
        'cards' => $orc_extras
    )
);
$faq_title = get_theme_mod('faq_title', 'Perguntas Frequentes ❓');
$faqs = array();
for ($i = 1; $i <= 8; $i++) {
    $q = get_theme_mod("faq_{$i}_question", '');
    $a = get_theme_mod("faq_{$i}_answer",   '');
    if (!empty($q)) {
        $faqs[] = array('q' => $q, 'a' => $a);
    }
}

// Themes
$themes_title    = get_theme_mod('themes_title',    'Temas que Adoramos 🎭');
$themes_subtitle = get_theme_mod('themes_subtitle', 'Ou escolha o seu Tema...');
$themes = array();
for ($i = 1; $i <= 12; $i++) {
    $emoji = get_theme_mod("theme_{$i}_emoji", '');
    $name  = get_theme_mod("theme_{$i}_name",  '');
    if (!empty($name)) {
        $themes[] = array('emoji' => $emoji, 'name' => $name);
    }
}

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
     PRICING TABS E CARDS
     ═══════════════════════════════════════ -->
<section class="pricing-section section" id="pricing">
    <div class="container">
        <div class="pricing-tabs reveal">
            <?php $first = true; foreach ($pricing_tabs as $key => $tab) : ?>
            <button class="pricing-tab <?php echo $first ? 'active' : ''; ?>" data-tab="<?php echo esc_attr($key); ?>">
                <?php echo esc_html($tab['label']); ?>
            </button>
            <?php $first = false; endforeach; ?>
        </div>

        <?php $first = true; foreach ($pricing_tabs as $key => $tab) : ?>
        <div class="pricing-content <?php echo $first ? 'active' : ''; ?>" id="tab-<?php echo esc_attr($key); ?>">
            <?php if ($key === 'extras') : ?>
                <div class="extras-grid">
                    <?php if (empty($tab['cards'])) : ?>
                        <p style="text-align:center; grid-column: 1 / -1; padding: 40px;">Não há opções configuradas para esta secção.</p>
                    <?php else : ?>
                        <?php foreach ($tab['cards'] as $card) : ?>
                        <div class="extra-item-horizontal reveal">
                            <div class="extra-image">
                                <?php if (!empty($card['image'])) : ?>
                                    <img src="<?php echo esc_url($card['image']); ?>" alt="<?php echo esc_attr($card['title']); ?>" />
                                <?php endif; ?>
                            </div>
                            <div class="extra-content">
                                <h2><?php echo esc_html($card['title']); ?></h2>
                                <?php if (!empty($card['time'])) : ?>
                                    <p class="pricing-time"><?php echo esc_html($card['time']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($card['desc'])) : ?>
                                <div class="pricing-schedule">
                                    <div class="schedule-item"><?php echo nl2br(esc_html($card['desc'])); ?></div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($card['price'])) : ?>
                                <div class="pricing-value"><?php echo esc_html($card['price']); ?><span class="currency">€</span></div>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url(home_url('/disponibilidade')); ?>" class="btn btn-primary">
                                    Solicitar Data
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else : ?>
            <div class="pricing-grid">
                <?php if (empty($tab['cards'])) : ?>
                    <p style="text-align:center; grid-column: 1 / -1; padding: 40px;">Não há opções configuradas para esta secção.</p>
                <?php else : ?>
                    <?php foreach ($tab['cards'] as $card) : ?>
                    <div class="pricing-card reveal" style="height: 100%; display: flex; flex-direction: column;">
                        <h2><?php echo esc_html($card['title']); ?></h2>
                        <?php if (!empty($card['time'])) : ?>
                            <p class="pricing-time"><?php echo esc_html($card['time']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($card['desc'])) : ?>
                        <div class="pricing-schedule" style="flex-grow: 1;">
                            <div class="schedule-item" style="border:none; text-align:center; padding:0; justify-content:center;"><?php echo nl2br(esc_html($card['desc'])); ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($card['price'])) : ?>
                        <div class="pricing-value"><?php echo esc_html($card['price']); ?><span class="currency">€</span></div>
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url(home_url('/disponibilidade')); ?>" class="btn btn-primary pricing-card-cta">
                            Solicitar Data
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <p style="margin-top:var(--space-lg);font-size:0.85rem;color:var(--color-text-muted);text-align:center;width:100%;">
                * Todos os preços incluem IVA. A disponibilização das datas está sujeita a confirmação.<br>
                🔒 É cobrada uma caução (reembolsável) de <?php echo esc_html($orc_caucao_valor); ?>€ na totalidade com o sinal da reserva (não é parcelada). Este valor é devolvido após a festa, desde que o espaço seja entregue em bom estado.
            </p>
        </div>
        <?php $first = false; endforeach; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════
     FAQ SECTION
     ═══════════════════════════════════════ -->
<?php if (!empty($faqs)) : ?>
<section class="faq-section section" id="faq">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($faq_title); ?></h2>
        </div>

        <div class="faq-list">
            <?php foreach ($faqs as $idx => $faq) : ?>
            <div class="faq-item <?php echo $idx === 0 ? 'active' : ''; ?> reveal">
                <div class="faq-question">
                    <span><?php echo esc_html($faq['q']); ?></span>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p><?php echo esc_html($faq['a']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════
     THEMES SECTION
     ═══════════════════════════════════════ -->
<?php if (!empty($themes)) : ?>
<section class="themes-section section" id="themes">
    <div class="container">
        <div class="section-title reveal">
            <h2><?php echo esc_html($themes_title); ?></h2>
        </div>
        <div class="section-subtitle reveal">
            <?php echo esc_html($themes_subtitle); ?>
        </div>

        <div class="themes-grid reveal">
            <?php foreach ($themes as $theme) : ?>
            <div class="theme-card">
                <span class="theme-emoji"><?php echo esc_html($theme['emoji']); ?></span>
                <span><?php echo esc_html($theme['name']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php get_footer();
