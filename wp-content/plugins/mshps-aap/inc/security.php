<?php
add_filter( 'upload_mimes', 'msh_restrict_upload_mimes' );

function msh_restrict_upload_mimes( $mimes ) {
    
    // Si tu veux que l'admin (toi) puisse tout uploader quand même, garde ce bloc.
    // Sinon, efface-le pour que la restriction s'applique à tout le monde.
    if ( current_user_can( 'administrator' ) ) {
        return $mimes;
    }

    // 1. On vide la liste complète (Sécurité maximale)
    $mimes = array();

    // 2. On ré-autorise uniquement ce que tu veux
    
    // PDF
    $mimes['pdf']          = 'application/pdf';
    
    // Images standards
    $mimes['jpg|jpeg|jpe'] = 'image/jpeg';
    $mimes['png']          = 'image/png';
    $mimes['webp']      = 'image/webp';

    return $mimes;
}


add_filter( 'upload_size_limit', 'msh_filter_site_upload_size_limit', 20 );

function msh_filter_site_upload_size_limit( $size ) {
    // 1. Si c'est un administrateur, on ne change rien (on retourne la limite du serveur)
    if ( current_user_can( 'manage_options' ) ) {
        return $size;
    }

    // 2. Pour TOUS les autres (chercheurs, porteurs...), on impose une limite stricte
    // Exemple : 5 Mo (5 * 1024 * 1024)
    // Tu peux mettre 2 Mo (2 * 1024 * 1024) si tu veux être plus strict sur les images
    return 2 * 1024 * 1024;
}
?>