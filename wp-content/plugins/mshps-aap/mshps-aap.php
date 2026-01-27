<?php
/**
 * Plugin Name: MSH Paris-Saclay — Appels à projets (noyau métier)
 * Description: Rôles, statuts et logique métier autour du CPT projet de la MSH Paris-Saclay.
 * Author: MSH Paris-Saclay
 * Version: 0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MSHPS_AAP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MSHPS_AAP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// 1. ACTIVATION : Rôles et configuration initiale
register_activation_hook( __FILE__, 'mshps_aap_add_roles' );

// 2. DÉSACTIVATION : Nettoyage
register_deactivation_hook( __FILE__, 'mshps_aap_deactivate' );

function mshps_aap_deactivate() {
    // On nettoie la tâche planifiée (CRON) pour ne pas laisser de processus fantôme
    // Assurez-vous que le nom du hook ('msh_event_daily_wave_check') est identique à celui dans inc/cron.php
    wp_clear_scheduled_hook( 'msh_event_daily_wave_check' );
}

// Charger la configuration WS Form (IDs centralisés)
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/wsform-config.php';

// Charger les modules
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/logger.php'; // Logger des événements métiers
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/setup.php'; // Création des statuts de projets customs (post-status)
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/users.php'; // Création des rôles customs
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/utils.php'; // log, génération de la référence de projet
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/cron.php'; // gestion des vagues
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/display-functions.php'; // Fonctions métiers (Application de la référence au passage en instruction, etc.)
require_once MSHPS_AAP_PLUGIN_DIR . 'inc/security.php';

