<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Crée / met à jour les rôles utilisés par le portail AAP.
 * À appeler à l’activation du plugin.
 */
function mshps_aap_add_roles() {

    // 1) CANDIDAT : peut lire, déposer/éditer ses projets (via front),
    // pas d'admin, pas d'accès aux contenus des autres.
    add_role(
        'candidat',
        'Candidat AAP',
        [
            'read'             => true,
            'edit_posts'       => false, // on ne lui donne pas les posts classiques
            'upload_files'     => true,  // utile pour les uploads via ACF/Gravity
            'edit_others_posts'=> false,
            // Capacités WS Form nécessaires pour voir et gérer ses propres soumissions
            'read_submission'  => true,
            'edit_submission'  => true,
            'create_submission'=> true,
            'delete_submission'=> true,
        ]
    );

    // 2) MEMBRE MSH : peut voir / éditer tous les contenus (comme un "éditeur"),
    // mais on lui bloquera l'accès au wp-admin via un hook.
    add_role(
        'msh_member',
        'Membre MSH',
        [
            'read'                   => true,
            'edit_posts'             => true,
            'edit_others_posts'      => true,
            'publish_posts'          => true,
            'read_private_posts'     => true,
            'delete_posts'           => true,
            'delete_others_posts'    => true,
            'upload_files'           => true,
        ]
    );
}

/**
 * Bloque l’accès au wp-admin pour tous sauf les vrais admins.
 * Redirige vers une page front (ex: /dashboard-msh/).
 */
add_action('admin_init', function () {

    // Si l’utilisateur peut gérer les options (administrator), on le laisse tranquille
    if ( current_user_can('manage_options') ) {
        return;
    }

    // On autorise les appels AJAX admin-ajax.php
    if ( defined('DOING_AJAX') && DOING_AJAX ) {
        return;
    }

    // Tout le reste (candidat, msh_member, autres non-admin) → redirection front
    wp_redirect( home_url('/dashboard-msh/') );
    exit;
});

add_filter('show_admin_bar', function ($show) {
    if ( current_user_can('manage_options') ) {
        return $show; // admins : barre OK
    }
    return false; // tous les autres : pas de barre admin
});

/**
 * Personnaliser le message d'erreur WS Form quand l'utilisateur
 * est en attente de validation (New User Approval).
 */
function mshps_wsform_pending_approval_message( $error_message, $error_id, $form, $submit, $config ) {

    if ( 'pending_approval' === $error_id ) {
        $error_message = __(
            "Votre compte est en attente de validation par l'équipe de la MSH Paris-Saclay. " .
            "Vous recevrez un email dès qu'il sera activé.",
            'msh-webaap'
        );
    }

    return $error_message;
}
add_filter( 'wsf_action_user_signon_error', 'mshps_wsform_pending_approval_message', 10, 5 );