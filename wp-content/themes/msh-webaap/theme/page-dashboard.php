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

<?php if ( isset($_GET['msh_msg']) ) : ?>
    <div <?php echo ($_GET['msh_msg'] === 'success') ? 'id="msg-success-banner"' : ''; ?> 
         class="mb-6 rounded-lg p-4 transition-opacity duration-500 <?php echo $_GET['msh_msg'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'; ?>">
        <?php 
        if ( $_GET['msh_msg'] === 'success' ) {
            echo '<i class="fa-solid fa-check-circle mr-2"></i> Vague créée avec succès !';
        } else {
            echo '<i class="fa-solid fa-exclamation-circle mr-2"></i> Une erreur est survenue lors de la création.';
        }
        ?>
    </div>
<?php endif; ?>

<?php if ( isset($_GET['msh_cron']) && $_GET['msh_cron'] === 'refreshed' ) : ?>
    <div class="mb-6 rounded-lg p-4 bg-blue-50 text-blue-800 border border-blue-200 flex items-center">
        <i class="fa-solid fa-sync fa-spin mr-2" style="animation-iteration-count: 1;"></i> 
        Statuts des vagues recalculés manuellement selon la date du jour (<?php echo date('d/m/Y'); ?>).
    </div>
<?php endif; ?>

<main id="app-main-content" class="min-h-screen">

    <div class="max-w-[1400px] mx-auto my-4 px-6 lg:px-8 py-8 bg-white border border-gray-200/60 rounded-2xl">


        <?php
        get_template_part( 'template-parts/layout/page-header', null, [
            'title'    => 'Tableau de bord',
            'subtitle' => 'Bonjour, ' . ( $current_user->first_name ?: $current_user->display_name ) . '. Filtrez, cherchez et ouvrez les dossiers.',
            'actions'  => [
                [ 
                    'label' => 'Programmer une vague', 
                    'url'   => '#', 
                    'class' => 'js-trigger-wave-modal inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-all shadow-md cursor-pointer' 
                ],
                // Action 2 : Forcer le calcul (Nouveau)
                [
                    'label' => 'Actualiser les vagues',
                    'url'   => admin_url('admin-post.php?action=msh_force_refresh_waves'),
                    'class' => 'inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-all ml-2'
                ]
            ],
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
    </div>
</main>

<div id="wave-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm transition-opacity" data-close-wave-modal></div>
    
    <div class="relative mx-auto mt-24 w-full max-w-lg transform transition-all">
        
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="rounded-2xl bg-white shadow-xl border border-slate-200 p-6">
            
            <input type="hidden" name="action" value="msh_handle_create_wave_post">
            <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
            <?php wp_nonce_field( 'msh_create_wave_action', 'msh_wave_nonce' ); ?>

            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Programmer une vague</h3>
                    <p class="text-sm text-slate-600 mt-1">Définissez les dates d'ouverture et de fermeture.</p>
                </div>
                <button type="button" data-close-wave-modal class="rounded-lg p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">✕</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="wave_name" class="block text-sm font-semibold text-slate-900 mb-1">Désignation</label>
                    <input type="text" name="wave_name" id="wave_name" placeholder="Ex: 26-1" required
                           class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 px-3 py-2 text-sm">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-slate-900 mb-1">Ouverture</label>
                        <input type="date" name="start_date" id="start_date" required
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-slate-900 mb-1">Clôture</label>
                        <input type="date" name="end_date" id="end_date" required
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-900 focus:ring-slate-900 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="rounded-lg bg-blue-50 p-3 border border-blue-100">
                    <div class="flex gap-2">
                        <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                        <p class="text-xs text-blue-700 leading-relaxed">
                            La vague sera active uniquement si la date d'aujourd'hui est comprise entre l'ouverture et la clôture.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" data-close-wave-modal class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Annuler
                </button>
                <button type="submit" class="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition-colors shadow-sm">
                    Créer et Enregistrer
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- DEBUGGING ---
    console.log('Script Dashboard chargé.');

    // 1. CIBLAGE DE LA MODALE
    const modal = document.getElementById('wave-modal');
    if (!modal) console.error('Erreur : La modale #wave-modal est introuvable dans le HTML.');

    // 2. FONCTION TOGGLE
    function toggleModal(show) {
        if (!modal) return;
        
        if(show) {
            modal.classList.remove('hidden'); // Enlève la classe Tailwind qui cache
            document.body.style.overflow = 'hidden'; // Bloque le scroll
            console.log('Modale ouverte');
        } else {
            modal.classList.add('hidden'); // Remet la classe cachée
            document.body.style.overflow = '';
            console.log('Modale fermée');
        }
    }

    // 3. ÉCOUTEUR GLOBAL (DÉLÉGATION)
    // On écoute n'importe quel clic sur la page
    document.addEventListener('click', function(e) {
        
        // La méthode magique : on vérifie si l'élément cliqué (ou un de ses parents) 
        // possède la classe 'js-trigger-wave-modal'
        const triggerBtn = e.target.closest('.js-trigger-wave-modal');

        // Si on a trouvé le bouton
        if (triggerBtn) {
            console.log('Clic détecté sur le bouton !');
            e.preventDefault(); // STOPPE LE DIÈSE DANS L'URL
            toggleModal(true);
        }

        // Gestion de la fermeture (Boutons croix ou Annuler)
        const closeBtn = e.target.closest('[data-close-wave-modal]');
        if (closeBtn) {
            e.preventDefault();
            toggleModal(false);
        }
    });
    
    // 4. FERMETURE AVEC ECHAP
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            toggleModal(false);
        }
    });
});

// --- AUTO-HIDE MESSAGE SUCCÈS ---
const successMsg = document.getElementById('msg-success-banner');

if (successMsg) {
    // 1. On attend 2 secondes (2000ms)
    setTimeout(() => {
        
        // 2. On rend l'élément transparent (Fondu grâce à la classe duration-500)
        successMsg.classList.add('opacity-0');
        
        // 3. On attend la fin de l'animation (500ms) pour supprimer l'élément du DOM
        // (Sinon il prendrait encore de la place vide)
        setTimeout(() => {
            successMsg.remove();
            
            // Optionnel : On nettoie l'URL pour enlever ?msh_msg=success
            // Comme ça si on rafraîchit la page, le message ne revient pas
            const url = new URL(window.location);
            url.searchParams.delete('msh_msg');
            window.history.replaceState({}, '', url);
            
        }, 500);
        
    }, 2000);
}
</script>



<?php get_footer( 'app' ); ?>
