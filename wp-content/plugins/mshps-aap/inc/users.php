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
            'upload_files'     => true,  
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
 * Redirige vers une page front (ex: /dashboard/).
 */
add_action('admin_init', function () {
    
    // On récupère le nom du fichier en cours d'exécution (ex: index.php, admin-post.php)
    global $pagenow;

    // -----------------------------------------------------------
    // 1. EXCEPTIONS (Ceux qui ont le droit d'entrer dans /wp-admin/)
    // -----------------------------------------------------------

    // A. L'administrateur suprême
    if ( current_user_can('manage_options') ) {
        return;
    }

    // B. Les processus techniques (AJAX et Sauvegardes de formulaire)
    if ( ( defined('DOING_AJAX') && DOING_AJAX ) || $pagenow === 'admin-post.php' ) {
        return;
    }

    // -----------------------------------------------------------
    // 2. REDIRECTIONS (Pour ceux qui n'ont rien à faire là)
    // -----------------------------------------------------------
    
    $user = wp_get_current_user();
    $roles = (array) $user->roles;

    // Cas MSH MEMBER -> Dashboard
    if ( in_array( 'msh_member', $roles ) ) {
        wp_redirect( home_url('/dashboard/') );
        exit;
    }

    // Cas CANDIDAT (et tous les autres par défaut) -> Mes Projets
    wp_redirect( home_url('/mes-projets/') );
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


/* -------------------------------------------------------------------------
 * GESTION DU PROFIL UTILISATEUR (FRONT-END)
 * ------------------------------------------------------------------------- */

// 1. MISE À JOUR DES INFOS (Nom, Prénom, Email)
add_action( 'admin_post_msh_update_profile_info', 'msh_handle_update_profile_info' );

function msh_handle_update_profile_info() {
    // 1. SÉCURITÉ
    if ( ! is_user_logged_in() ) { wp_die('Non connecté'); }
    check_admin_referer( 'msh_update_profile_info_action', 'msh_profile_nonce' );

    $user_id = get_current_user_id();
    $redirect_url = wp_get_referer() ?: home_url('/profil');

    // 2. RÉCUPÉRATION
    $first_name = sanitize_text_field( $_POST['first_name'] );
    $last_name  = sanitize_text_field( $_POST['last_name'] );
    $email      = sanitize_email( $_POST['email'] );
    
    // Clés simples
    $labo   = isset($_POST['laboratoire']) ? sanitize_text_field($_POST['laboratoire']) : '';
    $etab   = isset($_POST['etablissement']) ? sanitize_text_field($_POST['etablissement']) : '';
    $statut = isset($_POST['statut']) ? sanitize_text_field($_POST['statut']) : '';

    // 3. VALIDATION EMAIL
    if ( ! is_email( $email ) ) {
        wp_redirect( add_query_arg( 'msg', 'email_invalid', $redirect_url ) );
        exit;
    }
    if ( email_exists( $email ) && email_exists( $email ) !== $user_id ) {
        wp_redirect( add_query_arg( 'msg', 'email_exists', $redirect_url ) );
        exit;
    }

    // 4. UPDATE USER (Table wp_users)
    $userdata = [
        'ID'           => $user_id,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'user_email'   => $email,
        'display_name' => $first_name . ' ' . $last_name
    ];

    $updated_user_id = wp_update_user( $userdata );

    if ( is_wp_error( $updated_user_id ) ) {
        wp_redirect( add_query_arg( 'msg', 'error', $redirect_url ) );
        exit;
    }

    // 5. UPDATE METAS (Table wp_usermeta) - CLÉS SIMPLES
    
    if ( ! empty($labo) ) {
        update_user_meta( $user_id, 'laboratoire', $labo );
    } else {
        delete_user_meta( $user_id, 'laboratoire' );
    }

    if ( ! empty($etab) ) {
        update_user_meta( $user_id, 'etablissement', $etab );
    } else {
        delete_user_meta( $user_id, 'etablissement' );
    }

    if ( ! empty($statut) ) {
        update_user_meta( $user_id, 'statut', $statut );
    } else {
        delete_user_meta( $user_id, 'statut' );
    }

    // 6. REDIRECTION
    wp_redirect( add_query_arg( 'msg', 'info_success', $redirect_url ) );
    exit;
}

// 2. MISE À JOUR DU MOT DE PASSE
add_action( 'admin_post_msh_update_profile_password', 'msh_handle_update_profile_password' );

function msh_handle_update_profile_password() {
    // Sécurité
    if ( ! is_user_logged_in() ) { wp_die('Non connecté'); }
    check_admin_referer( 'msh_update_profile_password_action', 'msh_pwd_nonce' );

    $user_id = get_current_user_id();
    $user = get_user_by( 'id', $user_id );
    $redirect_url = wp_get_referer() ?: home_url('/profil');

    $current_pass = $_POST['current_pass'];
    $new_pass     = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // 1. Vérifier l'ancien mot de passe
    if ( ! wp_check_password( $current_pass, $user->user_pass, $user_id ) ) {
        wp_redirect( add_query_arg( 'msg', 'pwd_wrong', $redirect_url ) );
        exit;
    }

    // 2. Vérifier la correspondance des nouveaux
    if ( $new_pass !== $confirm_pass ) {
        wp_redirect( add_query_arg( 'msg', 'pwd_mismatch', $redirect_url ) );
        exit;
    }

    // 3. Vérifier la complexité (Optionnel, ici min 6 caractères)
    if ( strlen( $new_pass ) < 6 ) {
        wp_redirect( add_query_arg( 'msg', 'pwd_short', $redirect_url ) );
        exit;
    }

    // 4. Mise à jour
    wp_set_password( $new_pass, $user_id );

    // wp_set_password déconnecte l'utilisateur. On le reconnecte automatiquement pour l'UX.
    $creds = [
        'user_login'    => $user->user_login,
        'user_password' => $new_pass,
        'remember'      => true
    ];
    wp_signon( $creds, false );

    wp_redirect( add_query_arg( 'msg', 'pwd_success', $redirect_url ) );
    exit;
}