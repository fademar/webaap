<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Journalise un événement métier lié à un projet.
 *
 * @param int    $project_id ID du projet
 * @param string $type       Type d’événement (status_change, comment, budget…)
 * @param string $label      Libellé lisible
 * @param mixed  $payload    Données optionnelles (array / string)
 */
function mshps_log_projet_event($project_id, $type, $label, $payload = null) {

    if (empty($project_id) || empty($type) || empty($label)) {
        return;
    }

    $meta = [
        'log_project' => (int) $project_id,
        'log_type'    => sanitize_key($type),
        'log_actor'   => get_current_user_id(),
    ];

    if (!empty($payload)) {
        $meta['log_payload'] = is_array($payload)
            ? wp_json_encode($payload)
            : wp_strip_all_tags($payload);
    }

    wp_insert_post([
        'post_type'   => 'projet_log',
        'post_status' => 'publish',
        'post_title'  => wp_strip_all_tags($label),
        'meta_input'  => $meta,
    ]);
}