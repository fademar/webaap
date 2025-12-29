<?php
/**
 * Template Name: Test Projet Notion
 * 
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

  <main class="mx-auto max-w-[1400px] p-6">
    
    <div class="flex flex-col lg:flex-row gap-6 items-start">
      
      <article class="flex-5/6">
        <div class="bg-white border rounded-2xl p-6 sm:p-8">
          <h1 class="text-3xl font-bold text-slate-900 leading-tight">
            <?php the_title(); ?>
          </h1>

          <p class="mt-2 text-slate-600">
            Acronyme : <span class="font-semibold text-slate-900">ALGO-MEMO</span>
          </p>

          <section class="mt-6">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
              Propriétés
            </h2>

            <div class="rounded-xl border bg-slate-50 p-4">
              <dl class="divide-y divide-slate-200 text-sm">
              <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Référence</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">26-1-EM-01</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Vague</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">26-1</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Type de projet</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">Émergence</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">État</dt>
                  <dd class="col-span-7 sm:col-span-8">
                    <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-800">
                      En instruction
                    </span>
                  </dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Services sollicités</dt>
                  <dd class="col-span-7 sm:col-span-8 text-slate-700 italic">Aucun</dd>
                </div>
              </dl>
            </div>
          </section>

          <section class="mt-8 space-y-8">
            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Résumé court</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <p>
                  (Texte du résumé court ici. Dans l’interface finale, tu peux faire un bloc repliable.)
                </p>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Argumentaire scientifique</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <p class="mb-3">
                  (Bloc de texte long, avec possibilité d’insérer des images si besoin.)
                </p>
                <p>
                  (Deuxième paragraphe…)
                </p>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Pièces jointes</h3>
              <div class="rounded-xl border bg-white p-4">
                <ul class="space-y-2 text-sm">
                  <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-700">
                      <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">📎</span>
                      <span class="font-medium">Budget_previsionnel.pdf</span>
                    </div>
                    <a href="#" class="text-slate-600 hover:text-slate-900">Télécharger</a>
                  </li>
                  <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-700">
                      <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">📎</span>
                      <span class="font-medium">Annexe_methodo.docx</span>
                    </div>
                    <a href="#" class="text-slate-600 hover:text-slate-900">Télécharger</a>
                  </li>
                </ul>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Historique</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <ul class="space-y-3">
                  <li class="flex gap-3">
                    <span class="text-slate-400">12/12/2025</span>
                    <span>Soumission déposée par le porteur.</span>
                  </li>
                  <li class="flex gap-3">
                    <span class="text-slate-400">13/12/2025</span>
                    <span>Passage au statut “En instruction”.</span>
                  </li>
                </ul>
              </div>
            </div>
          </section>
        </div>
      </article>

      <aside class="w-full flex-1/6 shrink-0 space-y-6 sticky top-24">
        <section class="bg-white border rounded-2xl p-6">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
            Navigation
          </h2>
          <div class="space-y-2 text-sm">
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Projet
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Gestion
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Évaluation
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Budget
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Communication
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Édition
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Plateformes
            </a>
          </div>
        </section>

        <section class="bg-white border rounded-2xl p-6">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
            Actions rapides
          </h2>
          <div class="flex flex-col gap-2">
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Changer le statut
            </button>
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Exporter PDF
            </button>
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Contacter le porteur
            </button>
          </div>
        </section>
      </aside>
    </div>
  </main>
</div>

<div id="status-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/30"></div>
  <div class="relative mx-auto mt-24 w-full max-w-lg">
    <div class="rounded-2xl bg-white shadow-xl border p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Changer le statut</h3>
          <p class="text-sm text-slate-600 mt-1">
            Statut actuel : <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">En instruction</span>
          </p>
        </div>
        <button data-close-status-modal class="rounded-lg p-2 hover:bg-slate-100" aria-label="Fermer">✕</button>
      </div>

      <div class="mt-5 space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-1">Nouveau statut</label>
          <select class="w-full rounded-lg border px-3 py-2 text-sm">
            <option>En instruction</option>
            <option>En évaluation</option>
            <option>Labellisé</option>
            <option>Non retenu</option>
            <option>En exécution</option>
            <option>Clôturé</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-1">Note interne (optionnel)</label>
          <textarea class="w-full rounded-lg border px-3 py-2 text-sm" rows="3" placeholder="Contexte, décision, référence au comité, etc."></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" class="rounded border" />
          Notifier le porteur par email
        </label>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button data-close-status-modal class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Annuler
        </button>
        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
          Confirmer le changement
        </button>
      </div>
    </div>
  </div>
</div>

<?php endwhile; get_footer( 'app' ); ?>