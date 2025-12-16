<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Lorsque le chargé de projet passe un projet de "déposé" à "en instruction"
 * (taxonomie projet_statut), on génère proj_ref si elle n’existe pas encore.
 *
 * WS Form ou l’admin peuvent déclencher la sauvegarde, on s’en fiche :
 * on se branche sur acf/save_post.
 */
add_action('acf/save_post', function ($post_id) {

    if ( get_post_type($post_id) !== 'projet' ) {
        return;
    }

    // Seuls les gestionnaires (msh_member + admin) déclenchent la génération
    if ( ! current_user_can('edit_others_posts') ) {
        return;
    }

    if ( ! function_exists('get_field') || ! function_exists('update_field') ) {
        return;
    }

    // Si proj_ref existe déjà, on ne touche pas
    $existing = get_field('proj_ref', $post_id);
    if ( ! empty($existing) ) {
        return;
    }

    // Statut via taxonomie "projet_statut"
    $terms = wp_get_post_terms($post_id, 'projet_statut');
    if ( is_wp_error($terms) || empty($terms) ) {
        return;
    }

    $has_instruction = false;
    foreach ($terms as $t) {
        if ($t->slug === 'instruction') {
            $has_instruction = true;
            break;
        }
    }

    if ( ! $has_instruction ) {
        // Le projet n’est pas "en instruction" → on ne génère pas
        return;
    }

    // Générer et enregistrer la référence
    if ( function_exists('mshps_aap_generate_reference') ) {
        $ref = mshps_aap_generate_reference($post_id);
        if ( $ref ) {
            update_field('proj_ref', $ref, $post_id);
        } else {
            mshps_aap_log("Impossible de générer une référence pour le post $post_id");
        }
    }

}, 30);