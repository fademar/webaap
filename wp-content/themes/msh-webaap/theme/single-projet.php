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


    // Définition des onglets
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


    // Définition des statuts de projet pour la modale de changement de statut
    $statuses = [
      'projet-depose'      => 'Déposé',
      'projet-instruction' => 'En instruction',
      'projet-evaluation'  => 'En évaluation',
      'projet-labellise'   => 'Labellisé',
      'projet-en-cours'    => 'En cours',
      'projet-non-retenu'  => 'Non retenu',
      'projet-cloture'     => 'Clôturé',
    ];

    $current_status_key = get_post_status( get_the_ID() );
    $current_status_label = isset($statuses[$current_status_key]) ? $statuses[$current_status_key] : $current_status_key;

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
              <button type="button" data-open-status-modal class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 cursor-pointer">
                Changer le statut
              </button>
              <a href="<?php echo admin_url('admin-post.php?action=mshps_print_dossier&project_id=' . get_the_ID()); ?>" 
                target="_blank" 
                class="block text-center rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                  <span class="flex items-center justify-center gap-2">
                      Exporter (PDF)
                  </span>
              </a>
              <?php
              // Récupération de tous les emails de l'équipe
              $all_emails = [];
              
              // Email du porteur principal
              $porteur = get_field('proj_porteur');
              if (is_array($porteur) && !empty($porteur['email'])) {
                  $all_emails[] = $porteur['email'];
              }
              
              // Emails des co-porteurs
              $porteurs = get_field('proj_porteurs');
              if (is_array($porteurs)) {
                  foreach ($porteurs as $p) {
                      if (is_array($p) && !empty($p['email'])) {
                          $all_emails[] = $p['email'];
                      }
                  }
              }
              
              // Référence du projet
              $proj_ref = get_field('proj_ref');
              
              // Construction du lien mailto
              $mailto_to = implode(',', array_unique($all_emails));
              $mailto_subject = 'MSH Paris-Saclay - Votre projet ' . ($proj_ref ? $proj_ref : 'référencé');
              $mailto_link = 'mailto:' . $mailto_to . '?subject=' . rawurlencode($mailto_subject);
              ?>
              <a href="<?php echo esc_attr($mailto_link); ?>" 
                 class="block text-center rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Contacter les porteurs
              </a>
            </div>
        </nav>
    </div>
  </main>
</div>

<div id="status-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity" data-close-status-modal></div>
    
    <div class="relative mx-auto mt-24 w-full max-w-lg transform transition-all">
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="rounded-2xl bg-white shadow-xl border border-slate-200 p-6">
            
            <input type="hidden" name="action" value="mshps_update_project_status">
            <input type="hidden" name="project_id" value="<?php the_ID(); ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>">
            <?php wp_nonce_field( 'mshps_update_project_status' ); ?>

            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Changer le statut</h3>
                    <p class="text-sm text-slate-600 mt-1">
                        Statut actuel : 
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            <?php echo esc_html( $current_status_label ); ?>
                        </span>
                    </p>
                </div>
                <button type="button" data-close-status-modal class="rounded-lg p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <div class="space-y-4">
                
                <div>
                    <label for="new_status" class="block text-sm font-semibold text-slate-900 mb-1">Nouveau statut</label>
                    <select name="new_status" id="new_status" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 px-3 py-2 text-sm">
                        <?php foreach ( $statuses as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current_status_key, $key ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                    <input type="checkbox" name="notify_author" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-900" />
                    Notifier le porteur par email
                </label> -->
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" data-close-status-modal class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Annuler
                </button>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition-colors shadow-sm">
                    Confirmer le changement
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('status-modal');
    const openBtns = document.querySelectorAll('[data-open-status-modal]');
    const closeBtns = document.querySelectorAll('[data-close-status-modal]');

    function toggleModal(show) {
        if(show) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Empêche le scroll arrière-plan
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    openBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        toggleModal(true);
    }));

    closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        toggleModal(false);
    }));
    
    // Fermeture avec Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            toggleModal(false);
        }
    });
});
</script>

<?php endwhile; get_footer( 'app' ); ?>