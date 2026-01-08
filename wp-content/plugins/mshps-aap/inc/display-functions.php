<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Affiche le module de notes (Liste + Formulaire) pour un onglet donné.
 *
 * @param int    $project_id L'ID du projet.
 * @param string $scope      L'onglet (budget, gestion, evaluation...).
 */
function msh_render_notes_module( int $project_id, string $scope ) {
    
    // 1. Récupérer les notes existantes pour cet onglet précis
    $notes = get_comments( [
        'post_id'    => $project_id,
        'type'       => 'mshps_note', // Notre type personnalisé
        'meta_key'   => 'scope',      // On filtre par scope
        'meta_value' => $scope,       // La valeur du scope actuel
        'orderby'    => 'comment_date',
        'order'      => 'DESC',       // Les plus récentes en haut
        'status'     => 'approve',
    ] );
    ?>

    <div class="bg-pink-50 border-pink-200 rounded-xl border border-gray-200 p-6 mt-10">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-message"></i>
            Notes et échanges (<?php echo ucfirst($scope); ?>)
        </h3>

        <div class="space-y-4 mb-6 max-h-96 overflow-y-auto pr-2">
            <?php if ( empty( $notes ) ) : ?>
                <p class="text-gray-400 italic text-sm">Aucune note pour le moment sur cet onglet.</p>
            <?php else : ?>
                <?php foreach ( $notes as $note ) : ?>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-semibold text-sm text-primaire">
                                <?php echo get_comment_author( $note ); ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <?php echo get_comment_date( 'd/m/Y à H:i', $note ); ?>
                            </span>
                        </div>
                        <div class="text-gray-700 text-sm whitespace-pre-line">
                            <?php echo wp_kses_post( $note->comment_content ); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" class="relative">
            <input type="hidden" name="action" value="mshps_add_project_note">
            
            <input type="hidden" name="project_id" value="<?php echo esc_attr( $project_id ); ?>">
            <input type="hidden" name="scope" value="<?php echo esc_attr( $scope ); ?>">
            
            <input type="hidden" name="redirect" value="<?php echo esc_url( add_query_arg('tab', $scope, get_permalink($project_id)) ); ?>">

            <?php wp_nonce_field( 'mshps_add_project_note' ); ?>

            <label for="note_content_<?php echo $scope; ?>" class="sr-only">Ajouter une note</label>
            <textarea 
                id="note_content_<?php echo $scope; ?>"
                name="content" 
                rows="3" 
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-3"
                placeholder="Écrire une remarque interne, une décision..."
                required
            ></textarea>

            <div class="mt-2 text-right">
                <button type="submit" class="bg-primaire hover:bg-primaire/80 text-white text-sm font-medium py-2 px-4 rounded-lg transition-colors">
                    Ajouter la note
                </button>
            </div>
        </form>
    </div>
    <?php
}