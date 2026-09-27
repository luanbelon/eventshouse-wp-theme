<?php
/**
 * Template Name: Disponibilidade
 * Availability Page Template
 *
 * @package EventsHouse
 */

$page_title = 'Disponibilidade';
$page_subtitle = 'Verifique datas e envie o seu pedido de reserva';

// Google Calendar config
$gcal_configured = !empty(get_option('eventshouse_gcal_api_key', '')) && !empty(get_option('eventshouse_gcal_calendar_id', ''));

// Pricing and Extras
$orc_fimsemana = get_option('eventshouse_orcamento_fimsemana', array());
if (empty($orc_fimsemana)) {
    $orc_fimsemana = array(
        array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => '', 'price' => '200'),
        array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => '', 'price' => '250'),
        array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => '', 'price' => '380'),
    );
}
$orc_uteis = get_option('eventshouse_orcamento_uteis', array());
if (empty($orc_uteis)) {
    $orc_uteis = array(
        array('title' => 'Manhã', 'time' => '10h00 - 13h00', 'desc' => '', 'price' => '150'),
        array('title' => 'Tarde', 'time' => '15h00 - 19h00', 'desc' => '', 'price' => '180'),
        array('title' => 'Dia Completo', 'time' => '10h00 - 19h00', 'desc' => '', 'price' => '300'),
    );
}
$orc_extras = get_option('eventshouse_orcamento_extras', array());
if (empty($orc_extras)) {
    $orc_extras = array(
        array('title' => 'Hora Extra', 'time' => '+ 1 hora', 'desc' => '', 'price' => '50'),
    );
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
     AVAILABILITY CALENDAR
     ═══════════════════════════════════════ -->
<section class="section" id="availability" style="padding-bottom:0;">
    <div class="container">
        <div class="eh-availability-section">
            <h2>A sua festa começa aqui 🎈</h2>
            <p class="section-intro">Para avançarmos com a sua reserva, precisamos apenas de algumas informações. Preencha este formulário e deixe o resto connosco. 💛</p>
            <p class="section-intro">Aqui vamos recolher os dados essenciais da reserva e, depois, falamos consigo para tratar dos detalhes e personalizar tudo ao seu gosto caso tenha selecionado os nossos serviços extra ou packs.</p>

            <?php
            EventsHouse_print_calendar_booking_block(array(
                'context'         => 'default',
                'extras'          => $orc_extras,
                'show_extras'     => !empty($orc_extras),
                'gcal_configured' => $gcal_configured,
                'submit_label'    => '📨 Verificar Disponibilidade',
            ));
            ?>
        </div>
    </div>
</section>

<?php get_footer();
