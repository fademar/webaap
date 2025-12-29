<?php
/**
 * Template Name: MSH - Espace Candidat
 */

if ( ! is_user_logged_in() ) {
    auth_redirect();
}

get_header('test'); 

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$form_id = 8; // <--- VÉRIFIEZ L'ID FORMULAIRE

// --- RÉCUPÉRATION DES PROJETS DÉPOSÉS (Via WordPress CPT) ---
// On garde cette partie car c'est la seule qui connaît les statuts "Instruction", "Refusé", etc.
$args = [
    'post_type'      => 'projet',
    'post_status'    => ['projet-depose', 'projet-instruction', 'projet-evaluation', 'projet-labellise', 'projet-en-cours', 'projet-non-retenu', 'projet-cloture'],
    'author'         => $user_id,
    'posts_per_page' => -1,
];
$submitted_projects = new WP_Query($args);
?>


<main id="app-main-content" class="min-h-screen bg-slate-50/50">
<?php get_template_part( 'template-parts/layout/topbar', null, [ 'active_page' => 'mes-projets' ] ); ?>
<div class="max-w-[1400px] mx-auto my-4 px-6 lg:px-8 py-8 bg-white border border-gray-200/60 rounded-2xl">
    
    <?php
    get_template_part( 'template-parts/layout/page-header', null, [
        'title'    => 'Bonjour, ' . ( $current_user->first_name ?: $current_user->display_name ),
        'subtitle' => 'Gérez vos candidatures aux appels à projet de la MSH Paris-Saclay et suivez leur avancement.',
    ] );
    ?>

    <section class="mb-12">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <span class="bg-blue-50 text-blue-600 p-1.5 rounded-md mr-2">📂</span>
            Dossiers déposés
        </h2>

        <?php if ( $submitted_projects->have_posts() ) : ?>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Projet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php while ( $submitted_projects->have_posts() ) : $submitted_projects->the_post(); 
                            $status_obj = get_post_status_object( get_post_status() );
                            $status_label = $status_obj ? $status_obj->label : 'Inconnu';
                            $ref = get_post_meta( get_the_ID(), 'proj_ref', true );
                            
                            // Récupération type
                            $types = get_the_terms( get_the_ID(), 'projet_type' );
                            $type_name = !empty($types) && !is_wp_error($types) ? $types[0]->name : '—';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-sm text-gray-900"><?php the_title(); ?></div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500"><?php echo esc_html($type_name); ?></td>
                            <td class="px-6 py-4 text-xs text-gray-500"><?php echo esc_html(($ref) ? $ref : '—'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <p class="text-gray-500 italic text-sm p-4 bg-gray-50 rounded-lg border border-gray-100">Aucun dossier déposé.</p>
        <?php endif; wp_reset_postdata(); ?>
    </section>

    <section class="mb-12">
        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <span class="bg-gray-100 text-gray-600 p-1.5 rounded-md mr-2">✏️</span>
            Brouillons en cours
        </h2>

        <?php
        // Récupération des brouillons via fonction PHP simple
        $brouillons = mshps_get_user_drafts( $form_id, $user_id );

        if ( ! empty( $brouillons ) ) :
            // Mapping des types
            $types_labels = [
                'em' => 'Émergence',
                'ma' => 'Maturation',
                'ws' => 'Colloque',
                'se' => 'Séminaire',
            ];
            ?>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date de création</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dernière modification</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                    <?php foreach ( $brouillons as $draft ) :
                        $type_label = isset( $types_labels[ $draft->type ] ) ? $types_labels[ $draft->type ] : $draft->type;
                        $titre = $draft->titre ?: '(Sans titre)';
                        $date_creation = date_i18n( 'd/m/Y à H:i', strtotime( $draft->date_added ) );
                        $date_modif = date_i18n( 'd/m/Y à H:i', strtotime( $draft->date_updated ) );

                        // Construire l'URL d'édition
                        $payload = [
                            [
                                'id'    => $draft->form_id,
                                'hash'  => $draft->hash,
                                'token' => $draft->token,
                            ],
                        ];
                        $wsf_hash = rawurlencode( wp_json_encode( $payload ) );
                        $edit_url = home_url( "/nouveau-projet/?ptype={$draft->type}&wsf_hash={$wsf_hash}" );
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo esc_html( $type_label ); ?></td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo esc_html( $titre ); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo esc_html( $date_creation ); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo esc_html( $date_modif ); ?></td>
                            <td class="px-6 py-4 text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo esc_url( $edit_url ); ?>"
                                       class="inline-flex items-center justify-center w-8 h-8 text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                                       title="Reprendre ce brouillon"
                                       aria-label="Reprendre ce brouillon">
                                        <i class="fa-solid fa-pen text-sm"></i>
                                    </a>
                                    <button type="button"
                                            data-submit-id="<?php echo esc_attr( $draft->id ); ?>"
                                            class="mshps-delete-draft inline-flex items-center justify-center w-8 h-8 text-red-700 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                                            title="Supprimer ce brouillon"
                                            aria-label="Supprimer ce brouillon">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-center text-xs text-gray-400 mt-2">Ces brouillons n'ont pas encore été soumis à la MSH.</p>

        <?php else : ?>

            <div class="text-sm text-gray-500 italic p-4 bg-gray-50 rounded-lg border border-gray-100">
                Aucun brouillon en cours. Vous pouvez en créer un depuis <a class="text-primaire font-medium hover:underline" href="<?php echo esc_url( home_url( '/nouveau-projet/' ) ); ?>">Nouveau projet</a>.
            </div>

        <?php endif; ?>
    </section>
</div>
</main>

<?php get_footer('test'); ?>