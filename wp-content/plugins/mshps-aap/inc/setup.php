<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enregistrer les statuts personnalisés de projet.
 * NB : tu peux continuer à utiliser ta taxonomie "projet_statut" en parallèle si tu veux,
 * mais ces post_status serviront au workflow interne.
 */
add_action( 'init', 'mshps_aap_register_post_statuses' );
function mshps_aap_register_post_statuses() {

    register_post_status( 'projet-depose', [
        'label'                     => 'Déposé',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'Déposé (%s)', 'Déposés (%s)', 'mshps-aap' ),
    ] );
    
    register_post_status( 'projet-instruction', [
        'label'                     => 'En instruction',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'En instruction (%s)', 'En instruction (%s)', 'mshps-aap' ),
    ] );

    register_post_status( 'projet-evaluation', [
        'label'                     => 'En évaluation',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'En évaluation (%s)', 'En évaluation (%s)', 'mshps-aap' ),
    ] );

    register_post_status( 'projet-labellise', [
        'label'                     => 'Labellisé',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'Labellisé (%s)', 'Labellisés (%s)', 'mshps-aap' ),
    ] );

    register_post_status( 'projet-en-cours', [
        'label'                     => 'En cours',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'En cours (%s)', 'En cours (%s)', 'mshps-aap' ),
    ] );
    
    register_post_status( 'projet-non-retenu', [
        'label'                     => 'Non retenu',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'Non retenu (%s)', 'Non retenus (%s)', 'mshps-aap' ),
    ] );

    register_post_status( 'projet-cloture', [
        'label'                     => 'Clôturé',
        'public'                    => false,
        'protected'                 => true,
        'internal'                  => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'show_in_rest'              => true,
        'label_count'               => _n_noop( 'Clôturé (%s)', 'Clôturés (%s)', 'mshps-aap' ),
    ] );
}

/**
 * WP-Admin : inclure explicitement les statuts custom du CPT "projet" dans la liste.
 *
 * Contexte: un projet peut être créé en statut "projet-depose" (etc.). Sur certaines
 * configurations WP, la vue admin du CPT n'inclut pas ces statuts dans la requête
 * par défaut, donc le projet n'apparaît pas alors qu'il existe bien en base.
 */
add_action( 'pre_get_posts', function ( WP_Query $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // On ne touche qu'à l'écran liste du CPT "projet".
    if ( ( $query->get( 'post_type' ) ?: 'post' ) !== 'projet' ) {
        return;
    }

    // Si un post_status a été explicitement demandé (filtre admin), ne pas surcharger.
    $requested_status = $query->get( 'post_status' );
    if ( ! empty( $requested_status ) && $requested_status !== 'all' ) {
        return;
    }

    $custom_statuses = [
        'projet-depose',
        'projet-instruction',
        'projet-evaluation',
        'projet-labellise',
        'projet-en-cours',
        'projet-non-retenu',
        'projet-cloture',
    ];

    // Inclure aussi les statuts WP classiques pour ne pas "cacher" des drafts éventuels.
    $query->set(
        'post_status',
        array_merge( [ 'publish', 'draft', 'pending', 'future', 'private' ], $custom_statuses )
    );
} );

/**
 * WP-Admin : afficher les statuts custom dans le dropdown "État" de l'éditeur.
 *
 * Par défaut, WordPress n'ajoute pas les post_status custom au select "post_status".
 * On injecte donc les options via JS, uniquement pour le post type "projet".
 */
function mshps_aap_projet_custom_statuses(): array {
    return [
        'projet-depose'      => 'Déposé',
        'projet-instruction' => 'En instruction',
        'projet-evaluation'  => 'En évaluation',
        'projet-labellise'   => 'Labellisé',
        'projet-en-cours'    => 'En cours',
        'projet-non-retenu'  => 'Non retenu',
        'projet-cloture'     => 'Clôturé',
    ];
}

add_action( 'post_submitbox_misc_actions', function () {
    global $post;
    if ( ! $post || $post->post_type !== 'projet' ) {
        return;
    }

    $statuses = mshps_aap_projet_custom_statuses();
    if ( isset( $statuses[ $post->post_status ] ) ) {
        // Affiche l'état actuel dans le panneau (pour cohérence visuelle).
        echo '<script>document.addEventListener("DOMContentLoaded",function(){var el=document.getElementById("post-status-display"); if(el){el.textContent=' . wp_json_encode( $statuses[ $post->post_status ] ) . ';}});</script>';
    }
} );

add_action( 'admin_footer-post.php', 'mshps_aap_inject_projet_statuses_dropdown' );
add_action( 'admin_footer-post-new.php', 'mshps_aap_inject_projet_statuses_dropdown' );
function mshps_aap_inject_projet_statuses_dropdown() {
    global $post;
    if ( ! $post || $post->post_type !== 'projet' ) {
        return;
    }

    $statuses = mshps_aap_projet_custom_statuses();
    $current  = $post->post_status;
    ?>
    <script>
      (function () {
        var select = document.querySelector('select#post_status');
        if (!select) return;

        var custom = <?php echo wp_json_encode( $statuses ); ?>;
        var current = <?php echo wp_json_encode( $current ); ?>;

        // Ajoute les options si elles n'existent pas déjà.
        Object.keys(custom).forEach(function (value) {
          var exists = Array.prototype.some.call(select.options, function (opt) { return opt.value === value; });
          if (!exists) {
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = custom[value];
            select.appendChild(opt);
          }
        });

        // Met à jour l'affichage de l'état courant.
        if (custom[current]) {
          var display = document.getElementById('post-status-display');
          if (display) display.textContent = custom[current];
        }
      })();
    </script>
    <?php
}

/**
 * Restreint la médiathèque (media modal) aux fichiers de l'utilisateur courant
 * pour les rôles non privilégiés (ex: candidat).
 *
 * Les admins/équipe MSH gardent la vue complète grâce à edit_others_posts.
 */
add_filter('ajax_query_attachments_args', function(array $query) : array {

    if ( ! is_user_logged_in() ) {
        return $query;
    }

    // Si l'utilisateur fait partie de l'équipe (admin/éditeur/etc.), on ne restreint pas.
    if ( current_user_can('edit_others_posts') ) {
        return $query;
    }

    // Sinon (candidat), on ne montre que ses propres médias.
    $query['author'] = get_current_user_id();

    return $query;
});

add_filter('wsf_redirect_after_login', function($form_object, $submit_object) {

    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();

    // Exemple 1 : équipe MSH / admin -> dashboard
    if (
        in_array( 'msh_member', $user->roles, true )
        || in_array( 'administrator', $user->roles, true )
    ) {
        return [
            'redirect' => [
                'url' => home_url('/dashboard/')
            ]
        ];
    }

    // Exemple 2 : rôle "candidat" -> /mes-projets/
    if (in_array('candidat', $user->roles, true)) {
        return [
            'redirect' => [
                'url' => home_url('/nouveau-projet/'),
            ]
        ];
    }

    // Exemple 3 : fallback
    return [
        'redirect' => [
            'url' => home_url('/'),
        ]
    ];

}, 10, 2);