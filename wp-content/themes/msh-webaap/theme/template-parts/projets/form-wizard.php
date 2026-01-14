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

// Récupérer l'ID de la vague active pour pré-remplir le champ vague
$vague_active_id = function_exists('mshps_aap_get_current_wave_id') ? mshps_aap_get_current_wave_id() : false;

// Utiliser les constantes centralisées pour les IDs
$form_id = defined( 'MSHPS_WSFORM_CANDIDATURE_ID' ) ? MSHPS_WSFORM_CANDIDATURE_ID : 8;
$field_vague_id = defined( 'MSHPS_WSFORM_FIELD_VAGUE' ) ? MSHPS_WSFORM_FIELD_VAGUE : 226;

// Construction du shortcode WS Form avec pré-remplissage de la vague active
$shortcode = '[ws_form id="' . esc_attr( $form_id ) . '"';
if ( $vague_active_id ) {
    $shortcode .= ' field_' . esc_attr( $field_vague_id ) . '="' . esc_attr( $vague_active_id ) . '"';
}
$shortcode .= ']';

?>

<div class="flex items-center justify-between mb-8">
    <h2 class="text-2xl font-bold text-gray-800">Formulaire de candidature<span class="text-primaire font-bold"> • <?php echo esc_html( $ptype_label ); ?></span></h2>
    <a href="<?php echo esc_url( home_url('/nouveau-projet') ); ?>" class="w-10 h-10 flex items-center justify-center bg-gray-50 rounded-full hover:bg-red-50 hover:text-red-600 transition-colors">
        <i class="fa-solid fa-times"></i>
    </a>
</div>

<div class="flex flex-col">

    <div class="flex-1">
        <?php echo do_shortcode($shortcode); ?>
    </div>
</div>