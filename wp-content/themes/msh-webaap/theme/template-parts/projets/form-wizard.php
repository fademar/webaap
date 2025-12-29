<?php
/**
 * Template Part: Formulaire Wizard
 * Fichier : template-parts/projects/form-wizard.php
 */

$ptype = isset( $args['ptype'] ) ? sanitize_key( (string) $args['ptype'] ) : '';
if ( $ptype === '' && isset( $_GET['ptype'] ) ) {
    // Fallback si le template part est appelé sans args (compat).
    $ptype = sanitize_key( (string) wp_unslash( $_GET['ptype'] ) );
}

$ptype_labels = [
    'em' => 'Émergence',
    'ma' => 'Maturation',
    'ws' => "Colloque / Workshop / Journée d'étude",
    'se' => 'Séminaire',
];
$ptype_label = $ptype_labels[ $ptype ] ?? '—';

// Récupérer l'ID de la vague active pour pré-remplir le champ vague (field_226)
$vague_active_id = function_exists('mshps_aap_get_current_wave_id') ? mshps_aap_get_current_wave_id() : false;

// Construction du shortcode WS Form avec pré-remplissage de la vague active
$shortcode = '[ws_form id="8"';
if ($vague_active_id) {
    $shortcode .= ' field_226="' . esc_attr($vague_active_id) . '"';
}
$shortcode .= ']';

?>

<div class="flex items-center justify-between mb-8">
    <h2 class="text-2xl font-bold text-gray-800">Formulaire de candidature<span class="text-primaire font-bold"> • <?php echo $ptype_label; ?></span></h2>
    <a href="<?php echo home_url('/nouveau-projet'); ?>" class="w-10 h-10 flex items-center justify-center bg-gray-50 rounded-full hover:bg-red-50 hover:text-red-600 transition-colors">
        <i class="fa-solid fa-times"></i>
    </a>
</div>

<div class="flex flex-col">

    <div class="flex-1">
        <?php echo do_shortcode($shortcode); ?>
    </div>
</div>