<?php
/**
 * Configuration centralisée des IDs WS Form
 * 
 * ⚠️ IMPORTANT : Lors d'une migration (local → staging → production),
 * mettre à jour uniquement ce fichier avec les nouveaux IDs.
 * 
 * Comment trouver les IDs après import ?
 * 1. WS Form → Aller dans le formulaire
 * 2. Cliquer sur un champ → L'ID s'affiche dans l'URL ou l'inspecteur
 * 3. Ou : Inspecter le HTML du formulaire (data-id="XXX")
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================================
// FORMULAIRES
// ============================================================================

/**
 * ID du formulaire de candidature principal
 */
define( 'MSHPS_WSFORM_CANDIDATURE_ID', 8 );

// ============================================================================
// CHAMPS DU FORMULAIRE DE CANDIDATURE
// ============================================================================

/**
 * Champ Vague (Taxonomie projet_vague)
 * Type : Select / Radio
 */
define( 'MSHPS_WSFORM_FIELD_VAGUE', 226 );

/**
 * Champ Type de projet (Taxonomie projet_type)
 * Type : Select / Radio
 * Valeurs : em, ma, ws, se
 */
define( 'MSHPS_WSFORM_FIELD_TYPE', 227 );

/**
 * Champ Titre du projet
 * Type : Text
 */
define( 'MSHPS_WSFORM_FIELD_TITRE', 229 );

/**
 * Champ Mots-clés (Taxonomie projet_mot_cle)
 * Type : Select2 / Checkboxes
 */
define( 'MSHPS_WSFORM_FIELD_KEYWORDS', 232 );

/**
 * Champ Nom du porteur
 * Type : Text
 */
define( 'MSHPS_WSFORM_FIELD_NOM', 233 );

/**
 * Champ Prénom du porteur
 * Type : Text
 */
define( 'MSHPS_WSFORM_FIELD_PRENOM', 234 );

/**
 * Champ Email du porteur
 * Type : Email
 */
define( 'MSHPS_WSFORM_FIELD_EMAIL', 235 );

/**
 * Champ Laboratoire du porteur
 * Type : Text
 */
define( 'MSHPS_WSFORM_FIELD_LABORATOIRE', 236 );

/**
 * Champ Établissement du porteur
 * Type : Text
 */
define( 'MSHPS_WSFORM_FIELD_ETABLISSEMENT', 237 );

/**
 * Champ Statut du porteur
 * Type : Select
 */
define( 'MSHPS_WSFORM_FIELD_STATUT', 409 );

// ============================================================================
// FONCTIONS HELPER (optionnelles, pour validation)
// ============================================================================

/**
 * Vérifie si les constantes WS Form sont définies
 * Utile pour diagnostiquer les problèmes de configuration
 * 
 * @return bool True si toutes les constantes sont définies
 */
function mshps_wsform_config_is_valid() {
    $required_constants = [
        'MSHPS_WSFORM_CANDIDATURE_ID',
        'MSHPS_WSFORM_FIELD_VAGUE',
        'MSHPS_WSFORM_FIELD_TYPE',
        'MSHPS_WSFORM_FIELD_TITRE',
        'MSHPS_WSFORM_FIELD_KEYWORDS',
        'MSHPS_WSFORM_FIELD_NOM',
        'MSHPS_WSFORM_FIELD_PRENOM',
        'MSHPS_WSFORM_FIELD_EMAIL',
        'MSHPS_WSFORM_FIELD_LABORATOIRE',
        'MSHPS_WSFORM_FIELD_ETABLISSEMENT',
        'MSHPS_WSFORM_FIELD_STATUT',
    ];
    
    foreach ( $required_constants as $constant ) {
        if ( ! defined( $constant ) ) {
            return false;
        }
    }
    
    return true;
}

/**
 * Récupère la liste des IDs pour affichage debug
 * 
 * @return array Tableau associatif [nom_constant => valeur]
 */
function mshps_wsform_get_config_map() {
    return [
        'Formulaire Candidature' => MSHPS_WSFORM_CANDIDATURE_ID,
        'Champ Vague' => MSHPS_WSFORM_FIELD_VAGUE,
        'Champ Type' => MSHPS_WSFORM_FIELD_TYPE,
        'Champ Titre' => MSHPS_WSFORM_FIELD_TITRE,
        'Champ Mots-clés' => MSHPS_WSFORM_FIELD_KEYWORDS,
        'Champ Nom' => MSHPS_WSFORM_FIELD_NOM,
        'Champ Prénom' => MSHPS_WSFORM_FIELD_PRENOM,
        'Champ Email' => MSHPS_WSFORM_FIELD_EMAIL,
        'Champ Laboratoire' => MSHPS_WSFORM_FIELD_LABORATOIRE,
        'Champ Établissement' => MSHPS_WSFORM_FIELD_ETABLISSEMENT,
        'Champ Statut' => MSHPS_WSFORM_FIELD_STATUT,
    ];
}
