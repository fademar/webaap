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

<main id="app-main-content" class="min-h-screen">

    <div class="max-w-[1400px] mx-auto my-4 px-6 lg:px-8 py-8 bg-white border border-gray-200/60 rounded-2xl">


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
    </div>
</main>

<?php get_footer( 'app' ); ?>
