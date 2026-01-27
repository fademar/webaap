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
?>