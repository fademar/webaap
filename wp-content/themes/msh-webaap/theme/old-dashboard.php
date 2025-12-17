<?php
/**
 * Template Name: MSH - Dashboard MSH
 */

if ( ! is_user_logged_in() ) {
    auth_redirect();
}

// Accès équipe MSH (et admins) : capacité "edit_others_posts"
if ( ! current_user_can( 'edit_others_posts' ) ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

// Assets DataTables (CDN)
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'mshps-datatables', 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js', [ 'jquery' ], '1.13.8', true );

get_header( 'app' );

$current_user = wp_get_current_user();

$current_wave = function_exists( 'mshps_aap_get_current_wave_term' ) ? mshps_aap_get_current_wave_term() : false;
$current_wave_id = $current_wave ? (int) $current_wave->term_id : 0;

$vagues = get_terms( [
    'taxonomy'   => 'projet_vague',
    'hide_empty' => false,
] );

$types = get_terms( [
    'taxonomy'   => 'projet_type',
    'hide_empty' => false,
] );

$status_options = [
    'all'               => 'Tous',
    'projet-depose'      => 'Déposé',
    'projet-instruction' => 'En instruction',
    'projet-evaluation'  => 'En évaluation',
    'projet-labellise'   => 'Labellisé',
    'projet-en-cours'    => 'En cours',
    'projet-non-retenu'  => 'Non retenu',
    'projet-cloture'     => 'Clôturé',
    'publish'            => 'Publié (WP)',
];

$projet_id = isset( $_GET['projet_id'] ) ? (int) $_GET['projet_id'] : 0;
?>

<main id="app-main-content" class="min-h-screen bg-slate-50/50">

    <?php get_template_part( 'template-parts/layout/topbar', null, [ 'active_page' => 'tous-les-projets' ] ); ?>
    <div class="max-w-[1400px] mx-auto my-4 px-6 lg:px-8 py-8 bg-white border border-gray-200/60 rounded-2xl">

    <?php if ( $projet_id ) : ?>
        <?php
        $projet = get_post( $projet_id );
        if ( ! $projet || $projet->post_type !== 'projet' ) {
            wp_safe_redirect( home_url( '/dashboard/' ) );
            exit;
        }

        $tab = isset( $_GET['tab'] ) ? sanitize_key( (string) $_GET['tab'] ) : 'projet';
        $tabs = [
            'projet'        => 'Projet',
            'gestion'       => 'Gestion',
            'evaluation'    => 'Évaluation',
            'budget'        => 'Budget',
            'communication' => 'Communication',
            'edition'       => 'Édition',
            'plateformes'   => 'Plateformes',
        ];
        if ( ! isset( $tabs[ $tab ] ) ) {
            $tab = 'projet';
        }

        $ref = (string) get_post_meta( $projet_id, 'proj_ref', true );
        $status = (string) $projet->post_status;
        $status_label = $status_options[ $status ] ?? $status;
        $resume_court = trim( wp_strip_all_tags( (string) $projet->post_content ) );

        $author_id = (int) $projet->post_author;
        $author = get_userdata( $author_id );
        $first = $author ? (string) get_user_meta( $author_id, 'first_name', true ) : '';
        $last  = $author ? (string) get_user_meta( $author_id, 'last_name', true ) : '';
        $email = $author ? (string) $author->user_email : '';
        $porteur = trim( trim( $first . ' ' . $last ) );
        $porteur = $porteur !== '' ? $porteur : '—';
        $lab = (string) get_user_meta( $author_id, 'laboratoire', true );
        $etab = (string) get_user_meta( $author_id, 'etablissement', true );

        $types = get_the_terms( $projet_id, 'projet_type' );
        $type_name = ( ! empty( $types ) && ! is_wp_error( $types ) ) ? $types[0]->name : '—';
        $vagues = get_the_terms( $projet_id, 'projet_vague' );
        $vague_name = ( ! empty( $vagues ) && ! is_wp_error( $vagues ) ) ? $vagues[0]->name : '—';

        $cand_com = (string) get_field( 'field_69282f56f517b', $projet_id );
        $cand_edition = (string) get_field( 'field_69299166e183c', $projet_id );
        $cand_plateformes = (string) get_field( 'field_6929b6b4d5d9d', $projet_id );
        $need_com = ( $cand_com !== '' && $cand_com !== 'none');
        $need_ed  = ( $cand_edition === '1' || $cand_edition === true );
        $need_pl  = ( $cand_plateformes === '1' || $cand_plateformes === true );

        $notes = get_comments( [
            'post_id'    => $projet_id,
            'type'       => 'mshps_note',
            'status'     => 'approve',
            'orderby'    => 'comment_date_gmt',
            'order'      => 'DESC',
            'meta_key'   => 'scope',
            'meta_value' => $tab,
            'number'     => 100,
        ] );
        ?>

        <?php
        get_template_part( 'template-parts/layout/page-header', null, [
            'title'   => $projet->post_title ?: ( 'Dossier #' . $projet_id ),
            'actions' => [
                [
                    'label' => '← Retour à la liste',
                    'url'   => home_url( '/dashboard/' ),
                ],
            ],
        ] );
        ?>

        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <nav class="flex flex-wrap gap-2 border-b border-gray-100 pb-4 mb-6">
                <?php foreach ( $tabs as $key => $label ) : ?>
                    <?php
                    $url = add_query_arg(
                        [ 'projet_id' => $projet_id, 'tab' => $key ],
                        home_url( '/dashboard/' )
                    );
                    $active = ( $key === $tab );
                    ?>
                    <a href="<?php echo esc_url( $url ); ?>"
                       class="<?php echo esc_attr( $active ? 'bg-primaire text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' ); ?> px-3 py-2 rounded-lg text-sm font-medium transition">
                        <?php echo esc_html( $label ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ( $tab === 'projet' ) : ?>
                <?php
                $acf_fields = function_exists( 'get_field_objects' ) ? get_field_objects( $projet_id ) : [];
                ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Statut du projet</h2>
                        <div class="p-4 rounded-xl border border-gray-200 h-full flex flex-col justify-center">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-xs text-gray-500">État actuel</div>
                                <div class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800"><?php echo esc_html( $status_label ); ?></div>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-xs text-gray-500">Date de dépôt</div>
                                <div class="text-sm font-medium text-gray-900"><?php echo esc_html( mysql2date( 'd/m/Y', $projet->post_date, true ) ); ?></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">Référence</div>
                                <div class="font-mono text-sm font-medium text-gray-900"><?php echo esc_html( $ref ?: '—' ); ?></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Services sollicités</h2>
                        <div class="p-4 rounded-xl border border-gray-200 h-full flex flex-col justify-center">
                            <div class="flex flex-wrap gap-2">
                                <?php if ( ! $need_com && ! $need_ed && ! $need_pl ) : ?>
                                    <span class="text-sm text-gray-400 italic flex items-center gap-2">
                                        <i class="fa-regular fa-circle-xmark"></i> Aucun service sollicité
                                    </span>
                                <?php else : ?>
                                    <?php if ( $need_com ) : ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <i class="fa-solid fa-bullhorn mr-2"></i> Communication
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( $need_ed ) : ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                            <i class="fa-solid fa-book mr-2"></i> Édition
                                        </span>
                                    <?php endif; ?>
                                    <?php if ( $need_pl ) : ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                            <i class="fa-solid fa-server mr-2"></i> Plateformes
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Détails du projet</h2>
                    <?php if ( empty( $acf_fields ) ) : ?>
                        <p class="text-sm text-gray-500 italic">Aucun champ ACF trouvé pour ce projet (ou ACF non disponible).</p>
                    <?php else : ?>
                        <div class="divide-y divide-gray-100 border border-gray-200 rounded-xl overflow-hidden">
                            

                            <!-- Boucle triée manuellement sur tous les champs ACF -->
                            <?php 
                            // Ordre forcé des champs (basé sur le JSON ACF)
                            $ordered_keys = [
                                'tax_proj_vague',
                                'tax_proj_type',
                                'proj_ref',
                                'proj_titre',
                                'proj_acronyme',
                                'proj_resume_court',
                                'proj_disciplines',
                                'proj_mots_cles',
                                'proj_porteur',      // Le groupe Porteur
                                'proj_porteurs',     // Le repeater Co-porteurs
                                'proj_objectifs',
                                'proj_methodologie',
                                'proj_etat_art',
                                'proj_interdisciplinarite',
                                'proj_partenariat',
                                'cand_date_debut',
                                'cand_date_fin',
                                'cand_date_event',
                                'cand_seances',
                                'cand_kit_com',
                                'cand_com_justification',
                                'cand_edition',
                                'cand_edition_details',
                                'cand_plateformes',
                                'cand_plateformes_details',
                                'cand_budget_detail',
                                'cand_budget_total',
                                'cand_cofinancements',
                                'cand_cofinancements_detail',
                                'cand_rgpd',
                                'cand_eco_engagement',
                                'cand_validation_finale',
                                'suiv_budget'
                            ];

                            // On trie $acf_fields selon cet ordre
                            $sorted_fields = [];
                            foreach ($ordered_keys as $key) {
                                if (isset($acf_fields[$key])) {
                                    $sorted_fields[$key] = $acf_fields[$key];
                                    unset($acf_fields[$key]); // On l'enlève pour ne pas le doubler
                                }
                            }
                            // On ajoute à la fin les champs qui n'étaient pas dans notre liste ordonnée (au cas où)
                            $sorted_fields = array_merge($sorted_fields, $acf_fields);
                            
                            foreach ( $sorted_fields as $name => $field ) : 
                            ?>
                                <?php
                                // On exclut les champs affichés dans "L'essentiel" ou techniques
                                $excluded_fields = [
                                    'proj_ref', 
                                    'suiv_budget', 
                                    'proj_titre', 
                                    'proj_porteurs', // Géré manuellement avec le porteur
                                ];

                                if ( in_array( $name, $excluded_fields, true ) ) continue;
                                
                                // On exclut aussi les séparateurs d'onglets (type 'tab')
                                if ( ($field['type'] ?? '') === 'tab' ) continue;

                                // --- CAS SPÉCIAL : ÉQUIPE (Porteur + Co-porteurs) ---
                                if ( $name === 'proj_porteur' ) {
                                    $porteur_data = $field['value'] ?? [];
                                    $coporteurs_data = $sorted_fields['proj_porteurs']['value'] ?? []; // On récupère les co-porteurs ici

                                    // On construit une liste unifiée
                                    $team = [];
                                    
                                    // 1. Porteur (transformé en format compatible)
                                    if ( ! empty( $porteur_data ) ) {
                                        $team[] = [
                                            'role' => 'Porteur',
                                            'nom' => $porteur_data['nom'] ?? '',
                                            'prenom' => $porteur_data['prenom'] ?? '',
                                            'email' => $porteur_data['email'] ?? '',
                                            'laboratoire' => $porteur_data['laboratoire'] ?? '',
                                            'etablissement' => $porteur_data['etablissement'] ?? '',
                                            'cv' => $porteur_data['cv'] ?? '',
                                        ];
                                    }

                                    // 2. Co-porteurs
                                    if ( is_array( $coporteurs_data ) ) {
                                        foreach ( $coporteurs_data as $co ) {
                                            $team[] = [
                                                'role' => 'Co-porteur',
                                                'nom' => $co['nom'] ?? '',
                                                'prenom' => $co['prenom'] ?? '',
                                                'email' => $co['email'] ?? '',
                                                'laboratoire' => $co['laboratoire'] ?? '',
                                                'etablissement' => $co['etablissement'] ?? '',
                                                'cv' => $co['cv'] ?? '',
                                            ];
                                        }
                                    }
                                    ?>
                                    <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white">
                                        <div class="text-xs font-medium text-gray-500 md:col-span-1">Équipe du projet</div>
                                        <div class="text-sm text-gray-800 md:col-span-3">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Identité</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Laboratoire</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Établissement</th>
                                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-16">CV</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 bg-white">
                                                        <?php foreach ( $team as $index => $member ) : 
                                                            $is_porteur = ( $index === 0 ); // Le premier est le porteur
                                                        ?>
                                                            <tr>
                                                                <td class="px-3 py-2 text-sm text-gray-900 <?php echo $is_porteur ? 'font-bold' : ''; ?>">
                                                                    <?php echo esc_html( trim( ( $member['prenom'] ?? '' ) . ' ' . ( $member['nom'] ?? '' ) ) ); ?>
                                                                    <?php if ( $is_porteur ) : ?>
                                                                        <span class="ml-1 text-xs font-normal text-gray-500">(Porteur)</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="px-3 py-2 text-sm text-gray-500">
                                                                    <?php echo esc_html( $member['laboratoire'] ); ?>
                                                                </td>
                                                                <td class="px-3 py-2 text-sm text-gray-500">
                                                                    <?php echo esc_html( $member['etablissement'] ); ?>
                                                                </td>
                                                                <td class="px-3 py-2 text-sm text-blue-600">
                                                                    <a href="mailto:<?php echo esc_attr( $member['email'] ); ?>" class="hover:underline"><?php echo esc_html( $member['email'] ); ?></a>
                                                                </td>
                                                                <td class="px-3 py-2 text-sm text-center">
                                                                    <?php if ( ! empty( $member['cv'] ) ) : 
                                                                        // Gestion flexible: ID ou array
                                                                        $cv_url = '';
                                                                        if ( is_array( $member['cv'] ) ) {
                                                                            $cv_url = $member['cv']['url'] ?? '';
                                                                        } elseif ( is_numeric( $member['cv'] ) ) {
                                                                            $cv_url = wp_get_attachment_url( (int) $member['cv'] );
                                                                        }
                                                                        
                                                                        if ( $cv_url ) :
                                                                    ?>
                                                                        <a href="<?php echo esc_url( $cv_url ); ?>" target="_blank" class="text-gray-500 hover:text-red-600 transition-colors" title="Télécharger le CV">
                                                                            <i class="fa-solid fa-file-pdf text-lg"></i>
                                                                        </a>
                                                                    <?php endif; else : ?>
                                                                        <span class="text-gray-300">—</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    continue; // On passe au champ suivant (co-porteurs est exclu plus haut)
                                }

                                $label = $field['label'] ?? $name;
                                ?>
                                <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-4 bg-white">
                                    <div class="text-xs font-medium text-gray-500 md:col-span-1">
                                        <?php echo esc_html( $label ); ?>
                                    </div>
                                    <div class="text-sm text-gray-800 md:col-span-3">
                                        <?php echo mshps_render_acf_field_value( $field ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( $tab === 'gestion' ) : ?>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="p-4 rounded-xl border border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Changer le statut</h2>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="flex flex-col gap-3">
                            <?php wp_nonce_field( 'mshps_update_project_status' ); ?>
                            <input type="hidden" name="action" value="mshps_update_project_status">
                            <input type="hidden" name="project_id" value="<?php echo esc_attr( (string) $projet_id ); ?>">
                            <input type="hidden" name="redirect" value="<?php echo esc_attr( (string) add_query_arg( [ 'projet_id' => $projet_id, 'tab' => 'gestion' ], home_url( '/dashboard/' ) ) ); ?>">
                            <select name="new_status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                                <?php foreach ( $status_options as $val => $label ) : ?>
                                    <?php if ( $val === 'all' ) continue; ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status, $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-primaire text-white text-sm font-medium hover:opacity-90">
                                Enregistrer
                            </button>
                        </form>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-900 mb-3">Référence</h2>
                        <p class="text-sm text-gray-600 mb-3">La référence est générée automatiquement au passage en instruction (si vide). Vous pouvez aussi la générer manuellement.</p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="flex items-center gap-3">
                            <?php wp_nonce_field( 'mshps_generate_project_ref' ); ?>
                            <input type="hidden" name="action" value="mshps_generate_project_ref">
                            <input type="hidden" name="project_id" value="<?php echo esc_attr( (string) $projet_id ); ?>">
                            <input type="hidden" name="redirect" value="<?php echo esc_attr( (string) add_query_arg( [ 'projet_id' => $projet_id, 'tab' => 'gestion' ], home_url( '/dashboard/' ) ) ); ?>">
                            <div class="font-mono text-sm text-gray-900 flex-1"><?php echo esc_html( $ref ?: '—' ); ?></div>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Générer
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-8 p-4 rounded-xl border border-red-200 bg-red-50">
                    <h2 class="text-sm font-semibold text-red-600 mb-2">Zone de danger</h2>
                    <p class="text-sm text-red-600 mb-4">La suppression d'un projet est irréversible. Toutes les données associées seront perdues.</p>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Êtes-vous certain de vouloir supprimer définitivement ce projet ? Cette action est irréversible.');">
                        <?php wp_nonce_field( 'mshps_delete_project' ); ?>
                        <input type="hidden" name="action" value="mshps_delete_project">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr( (string) $projet_id ); ?>">
                        <input type="hidden" name="redirect" value="<?php echo esc_attr( home_url( '/dashboard/' ) ); ?>">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-red-600 border border-transparent text-white text-sm font-medium hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            <i class="fa-solid fa-trash-can mr-2"></i> Supprimer le projet
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ( $tab === 'budget' ) : ?>
                <?php
                // Budget accordé stocké dans ACF suiv_budget (field key), avec fallback meta.
                $budget_accorde = '';
                if ( function_exists( 'get_field' ) ) {
                    $budget_accorde = (string) get_field( 'field_6940114908e06', $projet_id );
                }
                if ( $budget_accorde === '' ) {
                    $budget_accorde = (string) get_post_meta( $projet_id, 'suiv_budget', true );
                }
                ?>
                <div class="p-4 rounded-xl border border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900 mb-3">Budget accordé</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="flex flex-col md:flex-row gap-3 items-end">
                        <?php wp_nonce_field( 'mshps_update_project_budget' ); ?>
                        <input type="hidden" name="action" value="mshps_update_project_budget">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr( (string) $projet_id ); ?>">
                        <input type="hidden" name="redirect" value="<?php echo esc_attr( (string) add_query_arg( [ 'projet_id' => $projet_id, 'tab' => 'budget' ], home_url( '/dashboard/' ) ) ); ?>">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Montant accordé (EUR)</label>
                            <input type="number" step="0.01" name="budget_accorde" value="<?php echo esc_attr( $budget_accorde ); ?>" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire" />
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-primaire text-white text-sm font-medium hover:opacity-90">
                            Enregistrer
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ( $tab === 'evaluation' ) : ?>
                <div class="p-4 rounded-xl border border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1">Évaluations</h2>
                    <p class="text-sm text-gray-600">Le CPT “evaluation” sera relié au projet (2 évaluateurs minimum). Onglet en cours d’implémentation.</p>
                </div>
            <?php endif; ?>

            <?php if ( in_array( $tab, [ 'communication', 'edition', 'plateformes' ], true ) ) : ?>
                <div class="p-4 rounded-xl border border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900 mb-1">Notes — <?php echo esc_html( $tabs[ $tab ] ); ?></h2>
                    <p class="text-sm text-gray-600">Notes internes pour le service.</p>
                </div>
            <?php endif; ?>

            <div class="mt-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-3">Notes</h2>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="mb-4">
                    <?php wp_nonce_field( 'mshps_add_project_note' ); ?>
                    <input type="hidden" name="action" value="mshps_add_project_note">
                    <input type="hidden" name="project_id" value="<?php echo esc_attr( (string) $projet_id ); ?>">
                    <input type="hidden" name="scope" value="<?php echo esc_attr( $tab ); ?>">
                    <input type="hidden" name="redirect" value="<?php echo esc_attr( (string) add_query_arg( [ 'projet_id' => $projet_id, 'tab' => $tab ], home_url( '/dashboard/' ) ) ); ?>">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Ajouter une note (<?php echo esc_html( $tabs[ $tab ] ); ?>)</label>
                    <textarea name="content" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire" placeholder="Écrire une note…"></textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:opacity-90">
                            Enregistrer la note
                        </button>
                    </div>
                </form>

                <?php if ( empty( $notes ) ) : ?>
                    <p class="text-sm text-gray-500 italic">Aucune note pour cet onglet.</p>
                <?php else : ?>
                    <div class="space-y-3">
                        <?php foreach ( $notes as $n ) : ?>
                            <?php
                            $note_user = $n->user_id ? get_userdata( (int) $n->user_id ) : null;
                            $note_name = $note_user ? ( $note_user->first_name || $note_user->last_name ? trim( $note_user->first_name . ' ' . $note_user->last_name ) : $note_user->display_name ) : $n->comment_author;
                            ?>
                            <div class="p-4 rounded-xl border border-gray-200 bg-white">
                                <div class="flex items-center justify-between gap-3 mb-2">
                                    <div class="text-sm font-medium text-gray-900"><?php echo esc_html( $note_name ?: '—' ); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo esc_html( mysql2date( 'd/m/Y à H:i', $n->comment_date, true ) ); ?></div>
                                </div>
                                <div class="text-sm text-gray-700 whitespace-pre-line"><?php echo wp_kses_post( $n->comment_content ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else : ?>

        <?php
        get_template_part( 'template-parts/layout/page-header', null, [
            'title'    => 'Tableau de bord',
            'subtitle' => 'Bonjour, ' . ( $current_user->first_name ?: $current_user->display_name ) . '. Filtrez, cherchez et ouvrez les dossiers.',
        ] );
        ?>

        <section class="mb-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Vague</label>
                        <select id="filter-vague" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <option value="all">Toutes</option>
                            <?php if ( ! empty( $vagues ) && ! is_wp_error( $vagues ) ) : ?>
                                <?php foreach ( $vagues as $v ) : ?>
                                    <option value="<?php echo esc_attr( (string) $v->term_id ); ?>" <?php selected( $current_wave_id, (int) $v->term_id ); ?>>
                                        <?php echo esc_html( $v->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                        <select id="filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <?php foreach ( $status_options as $val => $label ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                        <select id="filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <option value="all">Tous</option>
                            <?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
                                <?php foreach ( $types as $t ) : ?>
                                    <option value="<?php echo esc_attr( (string) $t->term_id ); ?>"><?php echo esc_html( $t->name ); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Communication</label>
                        <select id="filter-com" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <option value="all">Tous</option>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Édition</label>
                        <select id="filter-edition" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <option value="all">Tous</option>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Plateformes</label>
                        <select id="filter-plat" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire">
                            <option value="all">Tous</option>
                            <option value="1">Oui</option>
                            <option value="0">Non</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <table id="mshps-projets-table" class="display w-full">
                    <thead>
                        <tr>
                            <th>Statut</th>
                            <th>Référence</th>
                            <th>Titre</th>
                            <th>Porteur</th>
                            <th>Date de dépôt</th>
                            <th>Services</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </section>

        <script>
        window.mshpsDashboard = {
            restUrl: <?php echo wp_json_encode( esc_url_raw( rest_url( 'mshps/v1/projets' ) ) ); ?>,
            nonce: <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>,
        };
        </script>

        <script>
        jQuery(function($) {
            var table = $('#mshps-projets-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                dom: "<'dt-toolbar'lf>rt<'dt-footer'ip>",
                pageLength: 25,
                order: [[4, 'desc']],
                ajax: {
                    url: window.mshpsDashboard.restUrl,
                    type: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', window.mshpsDashboard.nonce);
                    },
                    data: function(d) {
                        d.vague = $('#filter-vague').val();
                        d.status = $('#filter-status').val();
                        d.type = $('#filter-type').val();
                        d.needs_com = $('#filter-com').val();
                        d.needs_edition = $('#filter-edition').val();
                        d.needs_plateformes = $('#filter-plat').val();
                    }
                },
                columns: [
                    { data: 'status', orderable: true, searchable: false },
                    { data: 'ref', orderable: true },
                    { data: 'title', orderable: true },
                    { data: 'owner', orderable: true, searchable: true },
                    { data: 'date_depot', orderable: true, searchable: false },
                    { data: 'needs', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
                },
                initComplete: function() {
                    // Force recalcul de la largeur après rendu
                    this.api().columns.adjust();
                }
            });

            $('#filter-vague, #filter-status, #filter-type, #filter-com, #filter-edition, #filter-plat').on('change', function() {
                table.ajax.reload();
            });

            // Re-ajuster au resize (sidebar / responsive)
            $(window).on('resize', function() {
                table.columns.adjust();
            });
        });
        </script>

    <?php endif; ?>
    </div>
</main>

<?php get_footer( 'app' ); ?>
