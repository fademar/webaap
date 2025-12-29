<?php
/**
 * Template Name: Single Projet (Layout Vertical)
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

<div class="min-h-screen bg-slate-50">
  
  <main class="mx-auto max-w-screen-xl px-4 lg:px-8 py-8">
    
    <div class="flex flex-col lg:flex-row gap-8 items-start">
      
      <article class="flex-1 min-w-0 w-full">
        
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm">
          <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-6 border-b border-slate-100">
             <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
                    Algorithmes de la Mémoire
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Référence : <span class="font-mono text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">26-1-EM-01</span>
                </p>
             </div>
             <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                En instruction
             </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Porteur</div>
                <div class="font-medium text-slate-900">Dr. Sarah Connor</div>
                <div class="text-sm text-slate-500">Laboratoire LISN</div>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-xs font-semibold text-slate-500 uppercase mb-1">Vague</div>
                <div class="font-medium text-slate-900">Appel 2026 - Session 1</div>
                <div class="text-sm text-slate-500">Type : Émergence</div>
            </div>
          </div>

          <div class="space-y-8">
            <section>
              <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3 uppercase tracking-wide">
                <i class="fa-regular fa-file-lines text-slate-400"></i> Résumé court
              </h3>
              <div class="prose prose-slate prose-sm max-w-none text-slate-600">
                <p>Ce projet vise à cartographier les mémoires urbaines des quartiers populaires en utilisant des techniques d'intelligence artificielle participative. Il s'agit de confronter les données massives aux récits individuels pour créer une nouvelle forme d'archive vivante.</p>
              </div>
            </section>

            <section>
              <h3 class="flex items-center gap-2 text-sm font-bold text-slate-900 mb-3 uppercase tracking-wide">
                <i class="fa-solid fa-flask text-slate-400"></i> Argumentaire scientifique
              </h3>
              <div class="prose prose-slate prose-sm max-w-none text-slate-600 bg-white">
                <p>L'approche méthodologique repose sur trois piliers : la collecte de données, l'analyse sémantique et la restitution cartographique. Nous utiliserons des modèles de NLP pour traiter les corpus d'entretiens...</p>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.</p>
                <p>Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              </div>
            </section>
          </div>
        </div>
      </article>

      <aside class="w-full lg:w-80 shrink-0 space-y-6 lg:sticky lg:top-24">
        
        <nav class="bg-white border border-slate-200 rounded-2xl p-2 shadow-sm">
          <div class="px-4 py-3 border-b border-slate-100 mb-2">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Navigation</h2>
          </div>
          <div class="space-y-1">
            <?php 
            $nav_items = [
                ['label' => 'Vue d\'ensemble', 'icon' => 'fa-chart-pie', 'active' => true],
                ['label' => 'Gestion', 'icon' => 'fa-sliders', 'active' => false],
                ['label' => 'Budget', 'icon' => 'fa-euro-sign', 'active' => false],
                ['label' => 'Évaluation', 'icon' => 'fa-star', 'active' => false],
                ['label' => 'Communication', 'icon' => 'fa-bullhorn', 'active' => false],
            ];
            foreach($nav_items as $item): 
                $active_class = $item['active'] ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900';
            ?>
            <a href="#" class="<?php echo $active_class; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all">
              <i class="fa-solid <?php echo $item['icon']; ?> w-6 text-center <?php echo $item['active']?'text-white':'text-slate-400 group-hover:text-slate-500'; ?> text-sm"></i>
              <?php echo $item['label']; ?>
            </a>
            <?php endforeach; ?>
          </div>
        </nav>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
          <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Contact</h2>
          <div class="flex items-center gap-3 mb-4">
             <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-500">SC</div>
             <div class="leading-tight">
                <div class="font-bold text-slate-900 text-sm">Sarah Connor</div>
                <div class="text-xs text-slate-500">s.connor@lisn.fr</div>
             </div>
          </div>
          <button class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
            Envoyer un message
          </button>
        </div>

      </aside>

    </div>
  </main>
</div>

<?php endwhile; get_footer( 'app' ); ?>