<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Affiche les statuts personnalisés dans le dropdown de l'Éditeur Classique
 */
add_action( 'admin_footer-post.php', 'mshps_append_post_status_list' );
add_action( 'admin_footer-post-new.php', 'mshps_append_post_status_list' );

function mshps_append_post_status_list() {
    global $post;
    
    // On ne cible que ton type de post
    if ( 'projet' !== $post->post_type ) {
        return;
    }

    // Récupère le statut actuel pour le pré-sélectionner
    $current_status = $post->post_status;
    
    // Ta liste de statuts personnalisés (Label => Slug)
    // Adapte les slugs selon ta déclaration register_post_status
    $custom_statuses = array(
        'Déposé'      => 'projet-depose',
        'En Instruction' => 'projet-instruction',
        'En évaluation'      => 'projet-evaluation',
        'Labellisé'      => 'projet-labellise',
        'En cours'     => 'projet-en-cours',
        'Non retenu'     => 'projet-non-retenu',
        'Clôturé'     => 'projet-cloture'
    );

    ?>
    <script>
    jQuery(document).ready(function($){
        var currentStatus = '<?php echo $current_status; ?>';
        
        <?php foreach ( $custom_statuses as $label => $slug ) : ?>
            // On ajoute l'option dans le <select> standard de WordPress
            $('select#post_status').append('<option value="<?php echo $slug; ?>" <?php selected( $current_status, $slug ); ?>><?php echo $label; ?></option>');
            
            // Si c'est le statut actuel, on met à jour le texte affiché par défaut (le label en gras)
            if ( currentStatus === '<?php echo $slug; ?>' ) {
                 $('#post-status-display').text('<?php echo $label; ?>');
                 $('select#post_status').val('<?php echo $slug; ?>');
            }
        <?php endforeach; ?>
    });
    </script>
    <?php
}