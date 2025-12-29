<?php
/**
 * Template Name: Single Projet
 * Template Post Type: projet
 */

if ( ! is_user_logged_in() ) auth_redirect();

// --- LOGIQUE METIER (Droits, etc.) ---
$user_id = get_current_user_id();
$can_view = current_user_can('edit_others_posts') || (int) $post->post_author === $user_id;
if ( ! $can_view ) { wp_safe_redirect( home_url( '/dashboard/' ) ); exit; }


while ( have_posts() ) : the_post();
    $projet_id = get_the_ID();


    // Définition des onglets (Même logique)
    $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'presentation';
    $tabs = [
        'presentation'        => [ 'label' => 'Présentation', 'icon' => 'fa-solid fa-clipboard-list' ],
        'calendrier'       => [ 'label' => 'Calendrier',        'icon' => 'fa-solid fa-calendar-days' ],
        'budget'        => [ 'label' => 'Budget',         'icon' => 'fa-solid fa-euro-sign' ],
        'communication' => [ 'label' => 'Communication',  'icon' => 'fa-solid fa-comments' ],
        'edition'       => [ 'label' => 'Édition',        'icon' => 'fa-solid fa-book' ],
        'plateformes'   => [ 'label' => 'Plateformes',    'icon' => 'fa-solid fa-server' ],
        'evaluation'    => [ 'label' => 'Évaluation',     'icon' => 'fa-solid fa-star' ],
        'historique'    => [ 'label' => 'Historique',     'icon' => 'fa-solid fa-clock' ],
    ];

    $default_tab = 'presentation';
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

          <p class="mt-2 text-slate-500">
            Acronyme : <span class="font-semibold text-slate-900"><?php echo get_field('proj_acronyme'); ?></span>
          </p>

          <section class="mt-6" id="tab-<?php echo esc_attr($tab); ?>">
              <?php
              $part = 'template-parts/projets/tabs/tab-' . $tab;

              if (locate_template($part . '.php')) {
                get_template_part('template-parts/projets/tabs/tab', $tab);
              } else {
                // fallback (au cas où)
                get_template_part('template-parts/projets/tabs/tab', $default_tab);
              }
              ?>
            </section>
        </div>
      </article>

      <aside class="w-full flex-1/6 shrink-0 space-y-6 sticky top-20">
        <nav class="bg-white border rounded-2xl p-6">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
            Navigation
          </h2>
          <div class="space-y-1 text-sm">

          <?php foreach ( $tabs as $key => $conf ) : 
                $is_active = ( $key === $tab );
                // Style : Menu vertical type "Réglages"
                $class = $is_active 
                    ? 'bg-primaire text-white' 
                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
            ?>
                <a href="<?php echo esc_url( add_query_arg(['tab'=>$key], get_permalink()) ); ?>" 
                    class="<?php echo $class; ?> group flex items-center px-3 py-2.5 text-sm font-medium rounded-xl transition-all w-full">
                    <span class="<?php echo $is_active ? 'text-white' : 'text-gray-400 group-hover:text-gray-500'; ?> flex-shrink-0 w-8 text-center mr-1 text-lg">
                        <i class="<?php echo esc_attr( $conf['icon'] ); ?>"></i>
                    </span>
                    <span class="truncate"><?php echo esc_html( $conf['label'] ); ?></span>
                </a>
            <?php endforeach; ?>
          </div>
        </nav>

        <nav class="bg-white border rounded-2xl p-6">
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
        </nav>
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