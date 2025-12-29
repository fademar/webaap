<?php
/**
 * Template Name: Old Single Projet
 * Template Post Type: projet
 */

if ( ! is_user_logged_in() ) auth_redirect();

// --- LOGIQUE METIER (Droits, etc.) ---
$user_id = get_current_user_id();
$can_view = current_user_can('edit_others_posts') || (int) $post->post_author === $user_id;
if ( ! $can_view ) { wp_safe_redirect( home_url( '/dashboard/' ) ); exit; }


while ( have_posts() ) : the_post();
    $projet_id = get_the_ID();

    // 1. RÉCUPÉRATION DE TOUS LES CHAMPS ACF (Une seule fois pour la performance)
    $all_fields = get_field_objects( $projet_id );
    
    // 2. CONFIGURATION : QUELS CHAMPS DANS QUEL ONGLET ?
    $fields_mapping = [
        'projet' => [
            'title'  => 'Vue d’ensemble',
            'fields' => [
                'tax_proj_vague',
                'tax_proj_type',
                'proj_ref',
                'proj_acronyme',
                'proj_resume_court',
                'proj_disciplines',
                'proj_mots_cles',
                'proj_porteur',      // Déclenchera l'affichage spécial "Équipe"
                // 'proj_porteurs',  // Pas besoin de le lister, il est géré par proj_porteur
                'proj_objectifs',
                'proj_methodologie',
                'proj_etat_art',
                'proj_interdisciplinarite',
                'proj_partenariat',
                'cand_rgpd',
                'cand_eco_engagement',
                'cand_validation_finale'
                ],
        ],
        'calendrier' => [
            'title'  => 'Calendrier',
            'fields' => [
                'cand_date_debut',
                'cand_date_fin',
                'cand_date_event',
                'cand_seances'
            ]
        ],
        'budget' => [
            'title'  => 'Budget et Financement',
            'fields' => [
                'cand_budget_total',
                'cand_budget_detail',
                'cand_cofinancements',
                'cand_cofinancements_detail',
                'suiv_budget' // Champ de suivi MSH
            ]
        ],
        'communication' => [
            'title'  => 'Plan de communication',
            'fields' => [
                'cand_kit_com',
                'cand_com_justification'
            ]
        ],
        'edition' => [
            'title'  => 'Projet éditorial',
            'fields' => [
                'cand_edition', // Booléen
                'cand_edition_details'
            ]
        ],
        'plateformes' => [
            'title'  => 'Usage des plateformes',
            'fields' => [
                'cand_plateformes', // Booléen
                'cand_plateformes_details'
            ]
        ],
        'evaluation' => [
            'title'  => 'Évaluation scientifique',
            'fields' => [] // À remplir plus tard avec vos champs d'évaluation
        ]
    ];



    // Définition des onglets (Même logique)
    $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'projet';
    $tabs = [
        'projet'        => [ 'label' => 'Vue d’ensemble', 'icon' => 'fa-solid fa-chart-pie' ],
        'calendrier'       => [ 'label' => 'Calendrier',        'icon' => 'fa-solid fa-calendar-days' ],
        'budget'        => [ 'label' => 'Budget',         'icon' => 'fa-solid fa-euro-sign' ],
        'communication' => [ 'label' => 'Communication',  'icon' => 'fa-solid fa-bullhorn' ],
        'edition'       => [ 'label' => 'Édition',        'icon' => 'fa-solid fa-book' ],
        'plateformes'   => [ 'label' => 'Plateformes',    'icon' => 'fa-solid fa-server' ],
        'evaluation'    => [ 'label' => 'Évaluation',     'icon' => 'fa-solid fa-star' ],
    ];

    $status = get_post_status();
    $statuses = function_exists('mshps_aap_projet_custom_statuses') ? mshps_aap_projet_custom_statuses() : [];
    $status_label = $statuses[$status] ?? $status;
    $ref = get_post_meta($projet_id, 'proj_ref', true);
    ?>


<?php get_header( 'app' ); ?>

    <main class="mx-auto max-w-[1400px] px-4 lg:px-6 py-6 w-full flex-1">
        
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?php the_title(); ?></h1>
                <div class="flex items-center gap-3 text-sm text-gray-500 mt-2">
                    <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                        <?php echo esc_html($status_label); ?>
                    </span>
                    <span class="font-mono text-gray-400"><?php echo esc_html($ref ?: 'Brouillon'); ?></span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <aside class="lg:col-span-2 lg:sticky lg:top-24">
                <nav class="space-y-0.5">
                    <?php foreach ( $tabs as $key => $conf ) : 
                        $is_active = ( $key === $tab );
                        // Style : Menu vertical type "Réglages"
                        $class = $is_active 
                            ? 'bg-gray-900 text-white shadow-md' 
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
                    ?>
                        <a href="<?php echo esc_url( add_query_arg(['tab'=>$key], get_permalink()) ); ?>" 
                           class="<?php echo $class; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all w-full">
                            <span class="<?php echo $is_active ? 'text-white' : 'text-gray-400 group-hover:text-gray-500'; ?> flex-shrink-0 w-8 text-center mr-1 text-lg">
                                <i class="<?php echo esc_attr( $conf['icon'] ); ?>"></i>
                            </span>
                            <span class="truncate"><?php echo esc_html( $conf['label'] ); ?></span>
                            
                            <?php if ($is_active): ?>
                                <i class="fa-solid fa-chevron-right ml-auto text-xs opacity-50"></i>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="border-t border-gray-200 m-4"></div>

                <div class="space-y-1">
                    <div class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Actions</div>
                    
                    <a href="<?php echo esc_url($pdf_url); ?>" target="_blank"
                       class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-white hover:text-blue-600 hover:shadow-sm transition-all border border-transparent hover:border-gray-100">
                        <i class="fa-solid fa-file-pdf w-5 text-center"></i>
                        <span>Exporter PDF</span>
                    </a>

                    <button onclick="window.print()" 
                            class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-white hover:text-gray-900 hover:shadow-sm transition-all border border-transparent hover:border-gray-100">
                        <i class="fa-solid fa-print w-5 text-center"></i>
                        <span>Imprimer</span>
                    </button>

                    <?php if (current_user_can('edit_others_posts')) : ?>
                        <a href="<?php echo get_edit_post_link(); ?>" 
                           class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-white hover:text-primaire hover:shadow-sm transition-all border border-transparent hover:border-gray-100">
                            <i class="fa-solid fa-pen-to-square w-5 text-center"></i>
                            <span>Éditer (WP)</span>
                        </a>
                        
                        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" onsubmit="return confirm('Confirmer la suppression ?');" class="pt-2">
                            <input type="hidden" name="action" value="mshps_delete_project">
                            <input type="hidden" name="project_id" value="<?php echo $projet_id; ?>">
                            <?php wp_nonce_field('mshps_delete_project'); ?>
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-all">
                                <i class="fa-solid fa-trash-can w-5 text-center"></i>
                                <span>Supprimer</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </aside>

            <div class="lg:col-span-10 space-y-6">

            <?php 
            // On vérifie si l'onglet actuel a une config de champs
            if ( isset( $fields_mapping[ $tab ] ) ) : 
                $current_config = $fields_mapping[ $tab ];
            ?>
                <div class="bg-white shadow-sm border border-gray-200 rounded-2xl overflow-hidden min-h-[600px]">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="text-base font-semibold text-gray-900"><?php echo esc_html( $current_config['title'] ); ?></h2>
                    </div>
                    
                    <?php 
                    // On prépare les données pour le template loop
                    $fields_to_display = $current_config['fields'];
                    
                    // On inclut la boucle qui sait afficher ces champs
                    include( get_theme_file_path( 'template-parts/projects/single-acf-loop.php' ) ); 
                    ?>
                </div>

            <?php endif; ?>

            <?php if ( $tab === 'gestion' && current_user_can('edit_others_posts') ) : ?>
                <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-8 max-w-2xl mt-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Changer le statut (Admin)</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="space-y-4">
                        <?php wp_nonce_field( 'mshps_update_project_status' ); ?>
                        <input type="hidden" name="action" value="mshps_update_project_status">
                        <input type="hidden" name="project_id" value="<?php echo esc_attr( $projet_id ); ?>">
                        <input type="hidden" name="redirect" value="<?php echo esc_attr( add_query_arg( [ 'tab' => 'gestion' ], get_permalink() ) ); ?>">
                        <select name="new_status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primaire focus:ring-primaire">
                            <?php foreach ( $statuses as $val => $lbl ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gray-900 hover:bg-gray-800">
                            Mettre à jour
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            </div>

        </div>
    </main>

<?php endwhile; get_footer( 'app' ); ?>