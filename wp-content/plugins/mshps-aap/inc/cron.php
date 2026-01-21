<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

function console_log($output, $with_script_tags = true) {
$js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
');';
if ($with_script_tags) {
$js_code = '<script>' . $js_code . '</script>';
}
echo $js_code;
}


// 1. LE PLANIFICATEUR (inchangé)
add_action( 'init', 'msh_schedule_wave_cron' );
function msh_schedule_wave_cron() {
    if ( ! wp_next_scheduled( 'msh_event_daily_wave_check' ) ) {
        wp_schedule_event( strtotime('tomorrow 01:00'), 'daily', 'msh_event_daily_wave_check' );
    }
}

// 2. L'EXÉCUTEUR (Logique Highlander simplifiée et blindée)
add_action( 'msh_event_daily_wave_check', 'msh_run_daily_wave_logic' );

function msh_run_daily_wave_logic() {
    $today = (int) current_time('Ymd');
    
    $terms = get_terms([
        'taxonomy'   => 'projet_vague',
        'hide_empty' => false,
    ]);

    if ( empty( $terms ) || is_wp_error( $terms ) ) { return; }

    $winning_term_id = 0;

    // PASSE 1 : Trouver le gagnant
    foreach ( $terms as $term ) {
        $term_id = $term->term_id;

        // On force le cast en (int) pour comparer des nombres purs
        $start = (int) get_field( 'vague_date_ouverture', $term, false );
        $end   = (int) get_field( 'vague_date_cloture', $term, false );

        if ( ! $start || ! $end ) continue;

        if ( $today >= $start && $today <= $end ) {
            $winning_term_id = $term_id;
            break; 
        }
    }

    // PASSE 2 : Appliquer
    foreach ( $terms as $term ) {
        $term_id = $term->term_id;
        $should_be_active = ( $term_id === $winning_term_id );
        console_log($should_be_active);
        console_log('projet_vague_' . $term_id);
        // On force la mise à jour
        update_field( 'is_current', $should_be_active, 'term_' . $term_id );
        
    }
}

// 3. ACTION MANUELLE (Production)
add_action('admin_post_msh_force_refresh_waves', 'msh_force_refresh_waves_handler');

function msh_force_refresh_waves_handler() {
    // Sécurité
    if ( ! current_user_can('edit_others_posts') ) { 
        wp_die('Non autorisé'); 
    }

    // On lance votre logique (celle que vous avez corrigée)
    msh_run_daily_wave_logic();

    // Redirection propre vers la page précédente avec un paramètre de succès
    $redirect = wp_get_referer() ? wp_get_referer() : admin_url();
    
    // On ajoute ?msh_cron=refreshed à l'URL pour afficher un petit message (optionnel)
    wp_redirect( add_query_arg('msh_cron', 'refreshed', $redirect) );
    exit;
}