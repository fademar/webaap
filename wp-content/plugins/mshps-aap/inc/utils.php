<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Génère une référence de projet de la forme "26-1-SE-01".
 *
 * - vague : slug du terme projet_vague (ex: "26-1")
 * - type  : slug du terme projet_type en MAJUSCULES (ex: "se" => "SE")
 * - index : nombre incrémental des projets déjà référencés pour ce couple vague+type,
 *           formaté sur 2 chiffres (01, 02, 03, …)
 *
 * ATTENTION :
 * - suppose exactement 1 projet_type et 1 projet_vague par projet
 * - ne fait que calculer la référence, ne l’enregistre pas
 */
function mshps_aap_generate_reference( int $post_id ): string {

    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'projet' ) {
        return '';
    }

    // 1) Type de projet (taxonomie projet_type) — doit être unique
    $type_terms = wp_get_post_terms( $post_id, 'projet_type' );
    if ( is_wp_error( $type_terms ) || empty( $type_terms ) ) {
        mshps_aap_log("Référence: aucun projet_type pour le post $post_id");
        return '';
    }
    if ( count( $type_terms ) > 1 ) {
        mshps_aap_log("Référence: plusieurs projet_type pour le post $post_id, cas non supporté");
        return '';
    }
    $type_term  = $type_terms[0];
    $type_code  = strtoupper( $type_term->slug ); // ex: "em", "ma", "se", "ws" => "EM", "MA", "SE", "WS"

    // 2) Vague (taxonomie projet_vague) — doit être unique
    $vague_terms = wp_get_post_terms( $post_id, 'projet_vague' );
    if ( is_wp_error( $vague_terms ) || empty( $vague_terms ) ) {
        mshps_aap_log("Référence: aucun projet_vague pour le post $post_id");
        return '';
    }
    if ( count( $vague_terms ) > 1 ) {
        mshps_aap_log("Référence: plusieurs projet_vague pour le post $post_id, ce cas devrait être impossible");
        return '';
    }
    $vague_term = $vague_terms[0];
    $vague_slug = $vague_term->slug; // ex: "26-1"

    // 3) Compter combien de projets ont DÉJÀ une référence pour ce couple vague+type
    //    On se contente de regarder le champ meta ACF proj_ref.
    //    IMPORTANT : On exclut les projets en corbeille pour que la suppression remette bien à zéro le compteur.
    $q = new WP_Query( [
        'post_type'      => 'projet',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => ['projet-depose', 'projet-instruction', 'projet-evaluation', 'projet-labellise', 'projet-en-cours', 'projet-non-retenu', 'projet-cloture', 'publish'],
        'post__not_in'   => [ $post_id ], // par sécurité, même si le projet n’a pas encore de ref
        'tax_query'      => [
            'relation' => 'AND',
            [
                'taxonomy' => 'projet_type',
                'field'    => 'slug',
                'terms'    => $type_term->slug,
            ],
            [
                'taxonomy' => 'projet_vague',
                'field'    => 'slug',
                'terms'    => $vague_term->slug,
            ],
        ],
        'meta_query'     => [
            [
                'key'     => 'proj_ref',
                'compare' => 'EXISTS',
            ],
        ],
    ] );

    $count_existing = (int) $q->found_posts;
    wp_reset_postdata();

    // 4) Index = nombre trouvé + 1, formaté sur 2 digits
    $index = str_pad( (string) ( $count_existing + 1 ), 2, '0', STR_PAD_LEFT );

    // 5) Concat finale : "26-1-SE-01"
    return $vague_slug . '-' . $type_code . '-' . $index;
}

/**
 * Récupère le slug de la vague actuellement active (marquée via ACF is_current).
 * @return string|false Le slug de la vague (ex: '26-1') ou false.
 */
function mshps_aap_get_current_wave_slug() {
    $vague_term = mshps_aap_get_current_wave_term();
    return $vague_term ? $vague_term->slug : false;
}

/**
 * Récupère le terme complet de la vague actuellement active.
 * @return WP_Term|false Le terme de la vague ou false.
 */
function mshps_aap_get_current_wave_term() {
    // On cherche les termes de la taxonomie 'projet_vague'
    $terms = get_terms( array(
        'taxonomy'   => 'projet_vague',
        'hide_empty' => false,
        'meta_query' => array(
            array(
                'key'     => 'is_current',
                'value'   => '1',
                'compare' => '='
            )
        ),
        'number'     => 1
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        return $terms[0];
    }

    return false;
}

/**
 * Récupère l'ID du terme de la vague actuellement active.
 * @return int|false L'ID du terme ou false.
 */
function mshps_aap_get_current_wave_id() {
    $vague_term = mshps_aap_get_current_wave_term();
    return $vague_term ? $vague_term->term_id : false;
}

/**
 * Shortcode pour WS Form - Retourne l'ID de la vague active.
 * WS Form a besoin du term_id pour pré-remplir un champ taxonomie.
 * Usage : [mshps_vague_active]
 */
add_shortcode( 'mshps_vague_active', 'mshps_aap_get_current_wave_id' );

/**
 * Shortcode alternatif pour WS Form - Retourne le slug de la vague active.
 * Usage : [mshps_vague_active_slug]
 */
add_shortcode( 'mshps_vague_active_slug', 'mshps_aap_get_current_wave_slug' );

/**
 * Fonction de log/debug simple.
 * Écrit dans le fichier debug.log de WordPress si WP_DEBUG_LOG est activé.
 */
function mshps_aap_log( $message ) {
    if ( defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ) {
        if ( is_array( $message ) || is_object( $message ) ) {
            $message = print_r( $message, true );
        }
        error_log( '[MSHPS-AAP] ' . $message );
    }
}

/**
 * Enregistrer l'endpoint REST pour supprimer un brouillon WS Form.
 */
add_action('rest_api_init', function() {
    register_rest_route('mshps/v1', '/draft/(?P<id>\d+)', array(
        'methods'  => 'DELETE',
        'callback' => 'mshps_aap_delete_draft',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
        'args' => array(
            'id' => array(
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    // Endpoint DataTables (backoffice MSH) : liste des projets
    register_rest_route('mshps/v1', '/projets', array(
        'methods'  => 'GET',
        'callback' => 'mshps_aap_rest_get_projets',
        'permission_callback' => function() {
            // Admin + msh_member (qui a edit_others_posts)
            return is_user_logged_in() && current_user_can('edit_others_posts');
        },
    ));
});

/**
 * ---------------------------------------------------------------------------
 * Backoffice MSH (front) — Actions via admin-post.php
 * ---------------------------------------------------------------------------
 *
 * Notes: on stocke les notes comme des commentaires WP avec:
 * - comment_type = 'mshps_note'
 * - comment_meta 'scope' = onglet (projet|gestion|evaluation|budget|communication|edition|plateformes)
 *
 * Avantages: auteur + date natifs, pagination facile, pas besoin d'un CPT.
 */

add_action( 'admin_post_mshps_add_project_note', 'mshps_aap_add_project_note' );
add_action( 'admin_post_mshps_update_project_status', 'mshps_aap_update_project_status' );
add_action( 'admin_post_mshps_generate_project_ref', 'mshps_aap_generate_project_ref' );
add_action( 'admin_post_mshps_update_project_budget', 'mshps_aap_update_project_budget' );
add_action( 'admin_post_mshps_delete_project', 'mshps_aap_delete_project' ); // Ajout de l'action de suppression

function mshps_aap_require_msh_access(): void {
	if ( ! is_user_logged_in() || ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( 'Accès refusé.', 403 );
	}
}

function mshps_aap_redirect_back( string $fallback = '/' ): void {
	$redirect = isset( $_POST['redirect'] ) ? (string) wp_unslash( $_POST['redirect'] ) : '';
	$redirect = $redirect ? $redirect : wp_get_referer();
	$redirect = $redirect ? $redirect : home_url( $fallback );
	wp_safe_redirect( $redirect );
	exit;
}

function mshps_aap_add_project_note(): void {
	mshps_aap_require_msh_access();
	check_admin_referer( 'mshps_add_project_note' );

	$project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
	$scope      = isset( $_POST['scope'] ) ? sanitize_key( (string) wp_unslash( $_POST['scope'] ) ) : '';
	$content    = isset( $_POST['content'] ) ? trim( (string) wp_unslash( $_POST['content'] ) ) : '';

	$allowed_scopes = [ 'presentation', 'calendrier', 'budget', 'communication', 'edition', 'plateformes', 'evaluation', 'historique' ];
	if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
		wp_die( 'Projet invalide.', 400 );
	}
	if ( ! in_array( $scope, $allowed_scopes, true ) ) {
		wp_die( 'Onglet invalide.', 400 );
	}
	if ( $content === '' ) {
		mshps_aap_redirect_back();
	}

	$comment_id = wp_insert_comment( [
		'comment_post_ID'  => $project_id,
		'comment_content'  => wp_kses_post( $content ),
		'user_id'          => get_current_user_id(),
		'comment_type'     => 'mshps_note',
		'comment_approved' => 1,
	] );

	if ( $comment_id && ! is_wp_error( $comment_id ) ) {
		add_comment_meta( (int) $comment_id, 'scope', $scope, true );
        mshps_log_projet_event( $project_id, 'comment_add', 'Note ajoutée dans l\'onglet ' . $scope);
	}

	mshps_aap_redirect_back();
}

function mshps_aap_update_project_status(): void {
	mshps_aap_require_msh_access();
	check_admin_referer( 'mshps_update_project_status' );

	$project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
	$new_status = isset( $_POST['new_status'] ) ? sanitize_key( (string) wp_unslash( $_POST['new_status'] ) ) : '';

	$allowed_statuses = [
		'projet-depose',
		'projet-instruction',
		'projet-evaluation',
		'projet-labellise',
		'projet-en-cours',
		'projet-non-retenu',
		'projet-cloture',
	];

	if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
		wp_die( 'Projet invalide.', 400 );
	}
	if ( ! in_array( $new_status, $allowed_statuses, true ) ) {
		wp_die( 'Statut invalide.', 400 );
	}

	// Changement de statut.
	wp_update_post( [
		'ID'          => $project_id,
		'post_status' => $new_status,
	] );

	mshps_log_projet_event( $project_id, 'status_change', 'Statut modifié', [ 'old_status' => $old_status, 'new_status' => $new_status ] );

	mshps_aap_redirect_back();
}

function mshps_aap_generate_project_ref(): void {
	mshps_aap_require_msh_access();
	check_admin_referer( 'mshps_generate_project_ref' );

	$project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
	if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
		wp_die( 'Projet invalide.', 400 );
	}

	if ( function_exists( 'mshps_aap_generate_reference' ) ) {
		$ref = mshps_aap_generate_reference( $project_id );
		if ( $ref ) {
			$ref_field_key = 'field_69270cf873e73';
			if ( function_exists( 'update_field' ) ) {
				update_field( $ref_field_key, $ref, $project_id );
			} else {
				update_post_meta( $project_id, 'proj_ref', $ref );
			}
		}
	}

	mshps_aap_redirect_back();
}

function mshps_aap_update_project_budget(): void {
    // 1. Vérification des droits
    mshps_aap_require_msh_access();

    // 2. Vérification du Nonce (Sécurité)
    // Param 1: L'action définie dans wp_nonce_field
    // Param 2: Le nom de l'input défini dans wp_nonce_field ('msh_budget_nonce')
    check_admin_referer( 'msh_save_budget_action', 'msh_budget_nonce' );

    // 3. Récupération de l'ID
    $project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
    if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
        wp_die( 'Projet invalide.', 'Erreur', array( 'response' => 400 ) );
    }

    // 4. Récupération du montant
    $amount_raw = isset( $_POST['suiv_budget'] ) ? (string) wp_unslash( $_POST['suiv_budget'] ) : '';
    
    // Nettoyage (remplace virgule par point, enlève les espaces)
    $amount_raw = str_replace( ',', '.', trim( $amount_raw ) );
    $amount     = $amount_raw === '' ? '' : (string) (float) $amount_raw;

    // 5. Sauvegarde
    $budget_field_key = 'field_6940114908e06';
    update_field( $budget_field_key, $amount, $project_id );
    
    mshps_log_projet_event( $project_id, 'budget', 'Budget accordé modifié', [ 'amount' => $amount ] );
    // 6. Redirection
    mshps_aap_redirect_back();
}

/**
 * Supprime un projet définitivement.
 *
 * @return void
 */
function mshps_aap_delete_project(): void {
    mshps_aap_require_msh_access();
    check_admin_referer( 'mshps_delete_project' );

    $project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
    
    if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
        wp_die( 'Projet invalide.', 400 );
    }

    // Suppression définitive (bypass trash)
    $result = wp_delete_post( $project_id, true );

    if ( ! $result ) {
        wp_die( 'Erreur lors de la suppression du projet.', 500 );
    }

    // Redirection vers le dashboard principal (puisque le projet n'existe plus)
    $redirect = home_url( '/dashboard/' );
    if ( isset( $_POST['redirect'] ) && ! empty( $_POST['redirect'] ) ) {
        $redirect = (string) wp_unslash( $_POST['redirect'] );
    }
    
    wp_safe_redirect( $redirect );
    exit;
}

/**
 * REST: Liste des projets (server-side DataTables).
 * Retourne: draw, recordsTotal, recordsFiltered, data[]
 */
function mshps_aap_rest_get_projets( WP_REST_Request $request ) {
    global $wpdb;

    $draw   = max( 0, (int) $request->get_param('draw') );
    $start  = max( 0, (int) $request->get_param('start') );
    $length = (int) $request->get_param('length');
    if ( $length <= 0 || $length > 200 ) {
        $length = 25;
    }

    $search_param = $request->get_param( 'search' );
    $search = is_array( $search_param ) ? (string) ( $search_param['value'] ?? '' ) : '';
    $search = trim( wp_unslash( $search ) );

    // Filtres métier
    $vague         = $request->get_param('vague'); // term_id | 'all' | null
    $status_filter = $request->get_param('status'); // 'all' | post_status
    $type_filter   = $request->get_param('type'); // term_id | 'all'
    $needs_com     = $request->get_param('needs_com'); // all|0|1
    $needs_edition = $request->get_param('needs_edition'); // all|0|1
    $needs_plat    = $request->get_param('needs_plateformes'); // all|0|1

    if ( $vague === null || $vague === '' ) {
        $current_wave_id = mshps_aap_get_current_wave_id();
        if ( $current_wave_id ) {
            $vague = (string) $current_wave_id;
        } else {
            $vague = 'all';
        }
    }

    // Mapping tri (whitelist)
    $columns = (array) $request->get_param('columns');
    $order   = (array) $request->get_param('order');
    $order_by = 'p.post_date';
    $order_dir = 'DESC';

    if ( isset( $order[0]['column'] ) && isset( $columns[ (int) $order[0]['column'] ]['data'] ) ) {
        $col = (string) $columns[ (int) $order[0]['column'] ]['data'];
        $dir = strtoupper( (string) ( $order[0]['dir'] ?? 'DESC' ) );
        $order_dir = in_array( $dir, [ 'ASC', 'DESC' ], true ) ? $dir : 'DESC';

        $order_map = [
            'status'    => 'p.post_status',
            'ref'       => 'pm_ref.meta_value',
            'title'     => 'p.post_title',
            'owner'     => 'um_last.meta_value', // tri par nom
            'date_depot'=> 'p.post_date',
        ];
        if ( isset( $order_map[ $col ] ) ) {
            $order_by = $order_map[ $col ];
        }
    }

    $posts_table = $wpdb->posts;
    $users_table = $wpdb->users;
    $umeta_table = $wpdb->usermeta;
    $pm_table    = $wpdb->postmeta;
    $tr_table    = $wpdb->term_relationships;
    $tt_table    = $wpdb->term_taxonomy;

    // Joins meta nécessaires aux colonnes + besoins
    // NB: Les champs ACF sont stockés en postmeta sous le NOM du champ (ex: cand_kit_com),
    // et la clé de champ est stockée en _cand_kit_com. Ici on joint les valeurs.
    $join = "
        LEFT JOIN {$pm_table} pm_ref ON (pm_ref.post_id = p.ID AND pm_ref.meta_key = 'proj_ref')
        LEFT JOIN {$pm_table} pm_com ON (pm_com.post_id = p.ID AND pm_com.meta_key = 'cand_kit_com')
        LEFT JOIN {$pm_table} pm_edition ON (pm_edition.post_id = p.ID AND pm_edition.meta_key = 'cand_edition')
        LEFT JOIN {$pm_table} pm_plat ON (pm_plat.post_id = p.ID AND pm_plat.meta_key = 'cand_plateformes')
        LEFT JOIN {$users_table} u ON (u.ID = p.post_author)
        LEFT JOIN {$umeta_table} um_first ON (um_first.user_id = u.ID AND um_first.meta_key = 'first_name')
        LEFT JOIN {$umeta_table} um_last  ON (um_last.user_id  = u.ID AND um_last.meta_key  = 'last_name')
    ";

    $where = [
        "p.post_type = 'projet'",
        "p.post_status NOT IN ('trash','auto-draft')",
    ];
    $params = [];

    if ( $status_filter && $status_filter !== 'all' ) {
        $where[] = "p.post_status = %s";
        $params[] = $status_filter;
    }

    // Filtre vague (taxonomie projet_vague)
    if ( $vague !== 'all' && is_numeric( $vague ) ) {
        $where[] = "EXISTS (
            SELECT 1 FROM {$tr_table} tr
            JOIN {$tt_table} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            WHERE tr.object_id = p.ID AND tt.taxonomy = 'projet_vague' AND tt.term_id = %d
        )";
        $params[] = (int) $vague;
    }

    // Filtre type (taxonomie projet_type)
    if ( $type_filter && $type_filter !== 'all' && is_numeric( $type_filter ) ) {
        $where[] = "EXISTS (
            SELECT 1 FROM {$tr_table} tr2
            JOIN {$tt_table} tt2 ON tt2.term_taxonomy_id = tr2.term_taxonomy_id
            WHERE tr2.object_id = p.ID AND tt2.taxonomy = 'projet_type' AND tt2.term_id = %d
        )";
        $params[] = (int) $type_filter;
    }

    // Besoins (alignés sur les champs ACF)
    // - Communication = select ACF cand_kit_com (none|cible|complet)
    // - Édition = true_false ACF cand_edition (0/1)
    // - Plateformes = true_false ACF cand_plateformes (0/1)
    if ( $needs_com === '1' ) {
        $where[] = "(pm_com.meta_value IS NOT NULL AND pm_com.meta_value <> '' AND pm_com.meta_value <> 'none')";
    } elseif ( $needs_com === '0' ) {
        $where[] = "(pm_com.meta_value IS NULL OR pm_com.meta_value = '' OR pm_com.meta_value = 'none')";
    }

    if ( $needs_edition === '1' ) {
        $where[] = "(pm_edition.meta_value = '1')";
    } elseif ( $needs_edition === '0' ) {
        $where[] = "(pm_edition.meta_value IS NULL OR pm_edition.meta_value = '' OR pm_edition.meta_value = '0')";
    }

    if ( $needs_plat === '1' ) {
        $where[] = "(pm_plat.meta_value = '1')";
    } elseif ( $needs_plat === '0' ) {
        $where[] = "(pm_plat.meta_value IS NULL OR pm_plat.meta_value = '' OR pm_plat.meta_value = '0')";
    }

    // Search
    if ( $search !== '' ) {
        $like = '%' . $wpdb->esc_like( $search ) . '%';
        $where[] = "(p.post_title LIKE %s OR pm_ref.meta_value LIKE %s OR u.user_email LIKE %s OR um_first.meta_value LIKE %s OR um_last.meta_value LIKE %s)";
        array_push( $params, $like, $like, $like, $like, $like );
    }

    $where_sql = 'WHERE ' . implode( ' AND ', $where );

    // Total (sans filtres) + total filtré (avec filtres/recherche)
    $sql_total_all = "SELECT COUNT(DISTINCT p.ID) FROM {$posts_table} p WHERE p.post_type = 'projet' AND p.post_status NOT IN ('trash','auto-draft')";
    $records_total = (int) $wpdb->get_var( $sql_total_all );

    // IMPORTANT: doit inclure les JOIN car $where_sql référence des alias (pm_*, u, um_*).
    $sql_total_filtered = "SELECT COUNT(DISTINCT p.ID) FROM {$posts_table} p {$join} {$where_sql}";
    if ( ! empty( $params ) ) {
        $records_filtered = (int) $wpdb->get_var( $wpdb->prepare( $sql_total_filtered, ...$params ) );
    } else {
        $records_filtered = (int) $wpdb->get_var( $sql_total_filtered );
    }

    // Data
    $sql = "
        SELECT DISTINCT
            p.ID,
            p.post_title,
            p.post_status,
            p.post_date,
            u.user_email,
            um_first.meta_value AS first_name,
            um_last.meta_value  AS last_name,
            pm_ref.meta_value     AS proj_ref,
            pm_com.meta_value     AS cand_kit_com,
            pm_edition.meta_value AS cand_edition,
            pm_plat.meta_value    AS cand_plateformes
        FROM {$posts_table} p
        {$join}
        {$where_sql}
        ORDER BY {$order_by} {$order_dir}
        LIMIT %d OFFSET %d
    ";

    $sql_params = array_merge( $params, [ $length, $start ] );
    $rows = $wpdb->get_results( $wpdb->prepare( $sql, ...$sql_params ), ARRAY_A );

    $status_labels = [
        'projet-depose'      => 'Déposé',
        'projet-instruction' => 'En instruction',
        'projet-evaluation'  => 'En évaluation',
        'projet-labellise'   => 'Labellisé',
        'projet-en-cours'    => 'En cours',
        'projet-non-retenu'  => 'Non retenu',
        'projet-cloture'     => 'Clôturé',
        'publish'            => 'Publié',
        'draft'              => 'Brouillon',
        'pending'            => 'En attente',
        'private'            => 'Privé',
    ];

    $data = [];
    foreach ( $rows as $r ) {
        $first = trim( (string) ( $r['first_name'] ?? '' ) );
        $last  = trim( (string) ( $r['last_name'] ?? '' ) );
        $email = (string) ( $r['user_email'] ?? '' );
        $owner_line = trim( trim( "{$first} {$last}" ) );
        if ( $owner_line === '' ) {
            $owner_line = '—';
        }

        $owner_html = '<div class="text-sm font-medium text-gray-900">' . esc_html( $owner_line ) . '</div>';
        $owner_html .= '<div class="text-xs text-gray-500">' . esc_html( $email ) . '</div>';

        $status_key = (string) $r['post_status'];
        $status_label = $status_labels[ $status_key ] ?? $status_key;
        $status_html = '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">' . esc_html( $status_label ) . '</span>';

        $ref = (string) ( $r['proj_ref'] ?? '' );
        $ref = $ref !== '' ? $ref : '—';

        $date_depot = mysql2date( 'd/m/Y H:i', $r['post_date'], true );

        // Besoins (icônes) : vert/gray
        $com_on = isset( $r['cand_kit_com'] ) && $r['cand_kit_com'] !== '' && $r['cand_kit_com'] !== 'none';
        $ed_on  = isset( $r['cand_edition'] ) && (string) $r['cand_edition'] === '1';
        $pl_on  = isset( $r['cand_plateformes'] ) && (string) $r['cand_plateformes'] === '1';

        $needs_html = '<div class="flex items-center gap-2 justify-center">';
        $needs_html .= '<span title="Communication" class="' . ( $com_on ? 'text-green-600' : 'text-gray-300' ) . '"><i class="fa-solid fa-bullhorn"></i></span>';
        $needs_html .= '<span title="Édition" class="' . ( $ed_on ? 'text-green-600' : 'text-gray-300' ) . '"><i class="fa-solid fa-book"></i></span>';
        $needs_html .= '<span title="Plateformes" class="' . ( $pl_on ? 'text-green-600' : 'text-gray-300' ) . '"><i class="fa-solid fa-server"></i></span>';
        $needs_html .= '</div>';

        $id = (int) $r['ID'];
        $view_url = get_permalink( $id );
        $actions_html = '<a class="inline-flex items-center justify-center w-8 h-8 text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors" href="' . esc_url( $view_url ) . '" title="Voir"><i class="fa-solid fa-eye"></i></a>';

        $data[] = [
            'status'     => $status_html,
            'ref'        => esc_html( $ref ),
            'title'      => esc_html( (string) $r['post_title'] ),
            'owner'      => $owner_html,
            'date_depot' => esc_html( $date_depot ),
            'needs'      => $needs_html,
            'actions'    => $actions_html,
        ];
    }

    return rest_ensure_response( [
        'draw'            => $draw,
        'recordsTotal'    => $records_total,
        'recordsFiltered' => $records_filtered,
        'data'            => $data,
    ] );
}


/**
 * Récupère les brouillons de l'utilisateur (version simple sans DataKit)
 * 
 * @param int $form_id        ID du formulaire WS Form
 * @param int $user_id        ID de l'utilisateur
 * @param int $field_titre_id ID du champ Titre (utilise MSHPS_WSFORM_FIELD_TITRE si non fourni)
 * @param int $field_type_id  ID du champ Type (utilise MSHPS_WSFORM_FIELD_TYPE si non fourni)
 * @return array              Tableau d'objets avec les brouillons (valeurs désérialisées)
 */
function mshps_get_user_drafts( $form_id, $user_id, $field_titre_id = null, $field_type_id = null ) {
    // Utiliser les constantes si les IDs ne sont pas fournis
    if ( $field_titre_id === null ) {
        $field_titre_id = defined( 'MSHPS_WSFORM_FIELD_TITRE' ) ? MSHPS_WSFORM_FIELD_TITRE : 229;
    }
    if ( $field_type_id === null ) {
        $field_type_id = defined( 'MSHPS_WSFORM_FIELD_TYPE' ) ? MSHPS_WSFORM_FIELD_TYPE : 227;
    }
    global $wpdb;

    $table_submit = $wpdb->prefix . 'wsf_submit';
    $table_meta   = $wpdb->prefix . 'wsf_submit_meta';

    $query = "
        SELECT 
            s.id,
            s.hash,
            s.token,
            s.form_id,
            s.date_added,
            s.date_updated,
            m_titre.meta_value as titre,
            m_type.meta_value as type
        FROM {$table_submit} s
        LEFT JOIN {$table_meta} m_titre 
            ON ( s.id = m_titre.parent_id AND m_titre.field_id = %d )
        LEFT JOIN {$table_meta} m_type 
            ON ( s.id = m_type.parent_id AND m_type.field_id = %d )
        WHERE s.form_id = %d
            AND s.user_id = %d
            AND s.status = 'draft'
        ORDER BY s.date_updated DESC
    ";

    $sql = $wpdb->prepare( $query, $field_titre_id, $field_type_id, $form_id, $user_id );
    $results = $wpdb->get_results( $sql );
    
    // Désérialiser les valeurs WS Form si nécessaire
    foreach ( $results as $draft ) {
        // Désérialiser le type (checkbox/select multiple sont sérialisés)
        if ( isset( $draft->type ) && is_serialized( $draft->type ) ) {
            $unserialized = @unserialize( $draft->type );
            // Si c'est un tableau, prendre la première valeur
            $draft->type = is_array( $unserialized ) ? $unserialized[0] : $unserialized;
        }
        
        // Désérialiser le titre si nécessaire (au cas où)
        if ( isset( $draft->titre ) && is_serialized( $draft->titre ) ) {
            $unserialized = @unserialize( $draft->titre );
            $draft->titre = is_array( $unserialized ) ? $unserialized[0] : $unserialized;
        }
    }
    
    return $results;
}
/**
 * Supprime un brouillon WS Form (soumission en status "draft").
 * Vérifie que l'utilisateur est propriétaire du brouillon.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function mshps_aap_delete_draft( WP_REST_Request $request ) {
    global $wpdb;
    
    $submit_id = (int) $request['id'];
    $user_id   = get_current_user_id();
    
    $table_submit = $wpdb->prefix . 'wsf_submit';
    $table_meta   = $wpdb->prefix . 'wsf_submit_meta';
    
    // Vérifier que la soumission existe, appartient à l'utilisateur et est en statut "draft"
    $submit = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, user_id, status FROM $table_submit WHERE id = %d",
            $submit_id
        )
    );
    
    if ( ! $submit ) {
        return new WP_Error(
            'not_found',
            'Brouillon introuvable.',
            array('status' => 404)
        );
    }
    
    // Vérifier que l'utilisateur est bien le propriétaire
    if ( (int) $submit->user_id !== $user_id ) {
        return new WP_Error(
            'forbidden',
            'Vous n\'êtes pas autorisé à supprimer ce brouillon.',
            array('status' => 403)
        );
    }
    
    // Vérifier que c'est bien un brouillon (status = 'draft')
    if ( $submit->status !== 'draft' ) {
        return new WP_Error(
            'invalid_status',
            'Seuls les brouillons peuvent être supprimés.',
            array('status' => 400)
        );
    }
    
    // Supprimer les métas associées (la colonne s'appelle 'parent_id' dans WS Form)
    $wpdb->delete( $table_meta, array('parent_id' => $submit_id), array('%d') );
    
    // Supprimer la soumission
    $deleted = $wpdb->delete( $table_submit, array('id' => $submit_id), array('%d') );
    
    if ( $deleted === false ) {
        mshps_aap_log("Erreur lors de la suppression du brouillon $submit_id");
        return new WP_Error(
            'delete_failed',
            'Erreur lors de la suppression du brouillon.',
            array('status' => 500)
        );
    }
    
    mshps_aap_log("Brouillon $submit_id supprimé par l'utilisateur $user_id");
    
    return rest_ensure_response( array(
        'success' => true,
        'message' => 'Brouillon supprimé avec succès.',
        'deleted_id' => $submit_id
    ) );
}

/**
 * Crée automatiquement les termes de mots-clés dans la taxonomie WordPress
 * lors de l'enregistrement d'un brouillon (pas seulement à la soumission finale)
 * Ainsi, lors de la reprise du brouillon, les termes existent et peuvent être rechargés
 * 
 * Utilise les vrais hooks WS Form : wsf_submit_create et wsf_submit_update
 */
// Désactivé : WS Form gère maintenant les taxonomies nativement
// add_action('wsf_submit_create', 'mshps_create_keywords_on_draft_save', 10, 1);
// add_action('wsf_submit_update', 'mshps_create_keywords_on_draft_save', 10, 1);

function mshps_create_keywords_on_draft_save($submit) {
    // Fonction désactivée - WS Form gère les taxonomies nativement
    return;
    global $wpdb;
    
    // IMPORTANT : Ne traiter que les brouillons, pas les soumissions finales
    // Sinon WS Form va créer les termes une première fois ici, puis les associer
    // une deuxième fois via son action Post, créant des doublons
    if ($submit->status !== 'draft') {
        return;
    }
    
    // Utiliser la constante pour l'ID du champ mots-clés
    $field_keywords_id = defined( 'MSHPS_WSFORM_FIELD_KEYWORDS' ) ? MSHPS_WSFORM_FIELD_KEYWORDS : 232;

    // Récupérer la valeur du champ mots-clés
    $meta_value = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}wsf_submit_meta 
         WHERE parent_id = %d AND field_id = %d",
        $submit->id,
        $field_keywords_id
    ));

    
    if (!$meta_value || !is_serialized($meta_value)) {
        return;
    }
    
    // Désérialiser les mots-clés
    $keywords = unserialize($meta_value);
    
    if (!is_array($keywords)) {
        $keywords = array($keywords);
    }
    
    // Créer/récupérer les termes dans la taxonomie
    $term_ids = array();
    foreach ($keywords as $keyword) {
        if (empty($keyword)) continue;
        
        // Vérifier si c'est un ID valide ou un nom
        $term = null;
        
        // Si c'est numérique, vérifier si le terme avec cet ID existe
        if (is_numeric($keyword)) {
            $possible_term = get_term($keyword, 'projet_mot_cle');
            // Si le terme existe, c'est un ID
            if ($possible_term && !is_wp_error($possible_term)) {
                $term = $possible_term;
            }
        }
        
        // Si on n'a pas trouvé de terme (pas un ID ou ID invalide),
        // traiter comme un nom (même si c'est un nombre comme "1900")
        if (!$term) {
            $term = term_exists($keyword, 'projet_mot_cle');
            if (!$term) {
                $term = wp_insert_term($keyword, 'projet_mot_cle');
            }
        }
        
        // Récupérer l'ID du terme
        if ($term && !is_wp_error($term)) {
            // term_exists retourne un array, get_term retourne un objet
            $term_id = is_array($term) ? $term['term_id'] : $term->term_id;
            $term_ids[] = $term_id;
        }
    }
    
    // Logger pour debug
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf(
            '[MSHPS Keywords] Created/found %d terms for submit %d: %s (IDs: %s)',
            count($term_ids),
            $submit->id,
            implode(', ', $keywords),
            implode(', ', $term_ids)
        ));
    }
    
    // IMPORTANT : Mettre à jour la meta avec les IDs au lieu des noms
    // pour que WS Form puisse repopuler le champ correctement
    if (!empty($term_ids)) {
        $serialized_ids = serialize($term_ids);
        $wpdb->update(
            $wpdb->prefix . 'wsf_submit_meta',
            array('meta_value' => $serialized_ids),
            array('parent_id' => $submit->id, 'field_id' => $field_keywords_id),
            array('%s'),
            array('%d', '%d')
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[MSHPS Keywords] Updated meta with term IDs');
        }
    }
}

/**
 * Pré-remplit automatiquement les informations du porteur de projet
 * avec les données de l'utilisateur connecté dans le formulaire de candidature
 * 
 * Note : Le filtre est dynamique basé sur MSHPS_WSFORM_CANDIDATURE_ID
 * Champs utilisés : Statut (via JavaScript car source ACF)
 * 
 * Les autres champs (Nom, Prénom, etc.) sont désactivés pour éviter
 * le pré-remplissage des co-porteurs dans les sections répétables
 */

/**
 * Script JavaScript pour pré-sélectionner le champ Statut
 * Nécessaire car le champ utilise ACF comme source de données dynamique
 */
add_action( 'wp_enqueue_scripts', function() {
    if (!is_user_logged_in()) {
        return;
    }
    
    $current_user = wp_get_current_user();
    $statut = get_user_meta($current_user->ID, 'statut', true);
    
    if (empty($statut)) {
        return;
    }
    
    $field_statut = defined( 'MSHPS_WSFORM_FIELD_STATUT' ) ? MSHPS_WSFORM_FIELD_STATUT : 409;
    
    $script = "
    jQuery(document).ready(function($) {
        function prefillStatut() {
            var statutValue = '" . esc_js($statut) . "';
            var fieldId = {$field_statut};
            
            // Le champ statut utilise la notation field_409[]
            var field = $('select[name=\"field_' + fieldId + '[]\"]');
            
            if (field.length) {
                field.val(statutValue).trigger('change');
            }
        }
        
        // Pré-remplir après le chargement du formulaire WS Form
        $(document).on('wsf-rendered', function() {
            setTimeout(prefillStatut, 100);
        });
        
        // Méthode backup au cas où l'événement ne se déclenche pas
        setTimeout(prefillStatut, 1500);
    });
    ";
    
    wp_add_inline_script('jquery', $script);
} );
/**
 * Hook WS Form : Déclenche la génération de référence APRES soumission
 * Priorité 50 : Indispensable pour que les taxonomies (Vague/Type) soient déjà là.
 */
add_action( 'wsf_action_post_post_meta', 'mshps_aap_wsform_project_post_process', 50, 6 );

function mshps_aap_wsform_project_post_process( $form, $submit, $config, $post_id, $list_id, $taxonomy_tags ): void {
    
    // 1. Sécurité : Est-ce bien un projet ?
    if ( ! $post_id || get_post_type( $post_id ) !== 'projet' ) {
        return;
    }

    // 2. Vérifier si une référence existe déjà (On ne touche pas à un projet déjà matriculé)
    $ref_field_key = 'field_69270cf873e73'; 
    // On checke le champ ACF ou le meta natif
    $existing = get_post_meta( $post_id, 'proj_ref', true );
    
    if ( ! empty( $existing ) ) {
        return; 
    }

    // 3. Appel du Calculateur (Ta fonction pure)
    if ( function_exists( 'mshps_aap_generate_reference' ) ) {
        
        // C'est ici que ça se joue : on récupère le string "26-1-SE-01"
        $new_ref = mshps_aap_generate_reference( $post_id );

        // Si ta fonction pure a bien marché (elle renvoie une chaine non vide)
        if ( ! empty( $new_ref ) ) {

            // A. SAUVEGARDE DE LA RÉFÉRENCE (ACF + Natif)
            if ( function_exists( 'update_field' ) ) {
                update_field( $ref_field_key, $new_ref, $post_id );
            } else {
                update_post_meta( $post_id, 'proj_ref', $new_ref );
            }

            // B. MISE À JOUR DU SLUG (URL)
            $slug_base = sanitize_title( $new_ref );
            
            // Unicité du slug
            $unique_slug = wp_unique_post_slug( $slug_base, $post_id, get_post_status( $post_id ), 'projet', 0 );
            $current_slug = get_post_field( 'post_name', $post_id );

            // On update seulement si nécessaire
            if ( $unique_slug && $unique_slug !== $current_slug ) {
                wp_update_post( [
                    'ID'        => $post_id,
                    'post_name' => $unique_slug,
                ] );
            }
            
            // Log de succès (facultatif)
            error_log("Projet $post_id matriculé avec succès : $new_ref");
        } else {
            // Si c'est vide, c'est que la fonction pure n'a pas trouvé les taxonomies
            error_log("Echec génération ref pour projet $post_id : Taxonomies manquantes ?");
        }
    }
}


/**
 * Helper pour WS Form : Convertit un slug de taxonomie en ID
 * Utilisé pour pré-remplir les champs radio/select via l'URL
 */
if ( ! function_exists( 'get_term_id_by_slug_wsform' ) ) {
    function get_term_id_by_slug_wsform( $slug, $taxonomy ) {
        // Sécurité : si le slug ou la taxonomie sont vides, on ne fait rien
        if ( empty( $slug ) || empty( $taxonomy ) ) {
            return '';
        }

        // On cherche le terme correspondant
        $term = get_term_by( 'slug', $slug, $taxonomy );

        // Si le terme existe, on renvoie son ID (integer), sinon une chaine vide
        return $term ? $term->term_id : '';
    }
}

/**
 * Version imprimable du dossier de projet
 */
add_action( 'admin_post_mshps_print_dossier', 'mshps_render_printable_dossier' );

function mshps_render_printable_dossier() {
    // 1. SÉCURITÉ
    if ( ! is_user_logged_in() ) wp_die('Accès interdit');
    $project_id = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;
    if ( ! $project_id ) wp_die('Projet introuvable');

    $project = get_post($project_id);
    
    $logo_url = site_url('/wp-content/uploads/2026/01/logo-MSH2025.png');

    $current_user = wp_get_current_user();
    // --- RÉCUPÉRATION DES DONNÉES (Basé sur votre code) ---
    
    // A. Taxonomies
    $vague_terms = get_the_terms( $project_id, 'projet_vague' );
    $vague_name  = ( $vague_terms && ! is_wp_error( $vague_terms ) ) ? reset( $vague_terms )->name : 'N/A';
    
    $type_terms  = get_the_terms( $project_id, 'projet_type' );
    $ptype_obj   = ( $type_terms && ! is_wp_error( $type_terms ) ) ? reset( $type_terms ) : null;
    $ptype_name  = $ptype_obj ? $ptype_obj->name : 'N/A';
    $ptype_slug  = $ptype_obj ? $ptype_obj->slug : '';

    $disciplines = get_the_terms( $project_id, 'projet_discipline' ) ?: [];
    $mots_cles   = get_the_terms( $project_id, 'projet_mot_cle' ) ?: [];

    // B. Statut
    $status = get_post_status($project_id);
    $statuses = function_exists('mshps_aap_projet_custom_statuses') ? mshps_aap_projet_custom_statuses() : [];
    $status_label = $statuses[$status] ?? $status;

    // C. Services sollicités
    $services = [];
    $kit_com = get_field('cand_kit_com', $project_id);
    if (in_array($kit_com, ['cible', 'complet'], true)) $services[] = 'Communication';
    if ((int) get_field('cand_edition', $project_id) === 1) $services[] = 'Édition';
    if ((int) get_field('cand_plateformes', $project_id) === 1) $services[] = 'Plateformes';

    // D. Équipe (Fusion Porteur + Co-porteurs)
    $porteur  = get_field('proj_porteur', $project_id);
    $porteurs = get_field('proj_porteurs', $project_id);
    $team_rows = [];

    // Porteur principal
    if (is_array($porteur) && !empty($porteur)) {
        $team_rows[] = array_merge($porteur, ['role' => 'Porteur']);
    }
    // Co-porteurs
    if (is_array($porteurs)) {
        foreach ($porteurs as $p) {
            if (!is_array($p)) continue;
            $team_rows[] = array_merge($p, ['role' => 'Membre']);
        }
    }

    // E. Configuration des sections scientifiques (Logique conditionnelle)
    $sections = [ 'proj_objectifs' => 'Objectifs et hypothèses de recherche' ];
    
    if ( in_array( $ptype_slug, ['em', 'ma'], true ) ) {
        $sections['proj_methodologie'] = 'Méthodologie';
        $sections['proj_etat_art']     = "État de l'art";
    } elseif ( in_array( $ptype_slug, ['ws', 'se'], true ) ) {
        $sections['proj_public_vise'] = 'Public visé'; 
    }
    $sections['proj_interdisciplinarite'] = 'Dimension interdisciplinaire';
    $sections['proj_partenariat']         = 'Partenariat inter-institutionnel';

    mshps_log_projet_event( $project_id, 'export_pdf', 'PDF exporté');
    
    // --- DÉBUT DU HTML ---
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Dossier_<?php echo $project_id; ?>_<?php echo sanitize_title($project->post_title); ?></title>
        <style>
            /* CSS POUR L'IMPRESSION (A4 avec marges) */
            @media print {
                @page { 
                    margin: 1.5cm; 
                    size: A4; 
                }
                
                body { 
                    margin: 0; 
                    padding: 0; 
                    width: auto; 
                    box-shadow: none; 
                }
                
                /* Masquer les éléments non imprimables */
                .no-print, 
                button, 
                .print-btn { 
                    display: none !important; 
                }
                
                /* Liens */
                a { 
                    text-decoration: none; 
                    color: #000; 
                }
                
                /* CONTRÔLE DES SAUTS DE PAGE */
                
                /* Éviter absolument de couper ces éléments */
                h1, h2, h3, 
                header, 
                .props-grid,
                .prop-item {
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                }
                
                /* Titres : toujours garder avec le contenu qui suit */
                h1, h2, h3 {
                    break-after: avoid !important;
                    page-break-after: avoid !important;
                }
                
                /* Tableaux : répéter les en-têtes mais éviter de couper les lignes */
                table {
                    break-inside: auto;
                    page-break-inside: auto;
                }
                
                thead {
                    display: table-header-group;
                }
                
                tbody {
                    break-inside: auto;
                    page-break-inside: auto;
                }
                
                tr {
                    break-inside: avoid !important;
                    page-break-inside: avoid !important;
                }
                
                /* Sections scientifiques courtes : éviter les coupures */
                .section-scientifique {
                    break-inside: avoid;
                    page-break-inside: avoid;
                }
                
                /* Si section trop longue (>15 lignes), permettre la coupure propre */
                .content-box {
                    orphans: 3;
                    widows: 3;
                }
                
                .content-box p {
                    orphans: 2;
                    widows: 2;
                }
                
                /* Espacement des sections majeures */
                h2 {
                    margin-top: 30px;
                }
            }

            /* CSS VISUEL (ÉCRAN) */
            body { 
                font-family: 'Helvetica', 'Arial', sans-serif; 
                font-size: 11pt; 
                line-height: 1.5; 
                color: #1e293b; /* Slate-800 */
                max-width: 210mm; 
                margin: 40px auto; 
                padding: 40px; 
                background: #fff; 
                box-shadow: 0 0 15px rgba(0,0,0,0.1); 
            }

            /* TYPOGRAPHIE */
            h1 { font-size: 24px; color: #0f172a; margin-bottom: 5px; text-transform: uppercase; border-bottom: 2px solid #0f172a; padding-bottom: 15px; }
            h2 { font-size: 16px; color: #334155; margin-top: 30px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; padding: 8px; }
            h3 { font-size: 13px; font-weight: bold; color: #475569; text-transform: uppercase; margin-top: 20px; margin-bottom: 10px; }
            
            /* COMPOSANTS */
            /* Header */
            header { border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
            .header-info { text-align: right; font-size: 0.9em; color: #555; }  
            .logo img { max-height: 60px; }
            .header-title { font-size: 16px; margin-bottom: 5px; color: #901879; text-transform: uppercase; font-weight: bold; }
            /* Grille propriétés */
            .props-grid { display: grid; grid-template-columns: 20% 20% 60%; gap: 15px; margin-bottom: 20px; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; }
            .prop-item label { display: block; font-size: 0.7em; text-transform: uppercase; color: #64748b; font-weight: bold; }
            .prop-item span { display: block; font-weight: 600; color: #0f172a; }

            /* Tableaux */
            table { width: 100%; border-collapse: collapse; font-size: 0.9em; margin-top: 10px; }
            th { text-align: left; background: #f1f5f9; padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #334155; }
            td { padding: 8px; border: 1px solid #cbd5e1; vertical-align: top; }

            /* Tags */
            .tag { display: inline-block; background: #f1f5f9; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-right: 5px; border: 1px solid #cbd5e1; }
            
            /* Prose */
            .content-box { text-align: justify; }
            .content-box p { margin-bottom: 10px; }

            /* Bouton */
            .print-btn { position: fixed; top: 20px; right: 20px; background: #0f172a; color: #fff; padding: 10px 20px; border-radius: 6px; cursor: pointer; border: none; font-weight: bold; z-index: 999; }
        </style>
    </head>
    <body>
        <button onclick="window.print()" class="print-btn">🖨️ Enregistrer en PDF</button>

        <header>
            <div class="logo">
                <?php if($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="MSH" style="max-height: 60px;">
                <?php else: ?>
                    <strong>MSH DASHBOARD</strong>
                <?php endif; ?>
            </div>
            <div class="header-title">
                Appel à projets - Dossier de candidature
            </div>
            <div class="header-info">
                Généré le : <?php echo date('d/m/Y'); ?><br>
                Par : <?php echo $current_user->display_name; ?>
            </div>
        </header>

        <h1><?php echo esc_html($project->post_title); ?></h1>

        <div class="props-grid">
            <div class="prop-item">
                <label>Référence</label>
                <span><?php echo get_field('proj_ref', $project_id); ?></span>
            </div>
            <div class="prop-item">
                <label>Vague</label>
                <span><?php echo esc_html($vague_name); ?></span>
            </div>
            <div class="prop-item">
                <label>Type</label>
                <span><?php echo esc_html($ptype_name); ?></span>
            </div>
        </div>

        <?php if (!empty($team_rows)): ?>
            <h2>Équipe de recherche</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Laboratoire</th>
                        <th>Établissement</th>
                        <th>Statut</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($team_rows as $r): ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html(($r['nom'] ?? '') . ' ' . ($r['prenom'] ?? '')); ?></strong><br>
                        </td>
                        <td><?php echo esc_html($r['laboratoire'] ?? ''); ?></td>
                        <td><?php echo esc_html($r['etablissement'] ?? ''); ?></td>
                        <td><?php echo esc_html($r['statut'] ?? ''); ?></td>
                        <td><?php echo esc_html($r['email'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($disciplines) || !empty($mots_cles)): ?>
            <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                <?php if(!empty($disciplines)): ?>
                    <div style="margin-bottom: 10px;">
                        <strong>Disciplines : </strong>
                        <?php foreach($disciplines as $d) echo '<span class="tag">'.esc_html($d->name).'</span>'; ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($mots_cles)): ?>
                    <div>
                        <strong>Mots-clés : </strong>
                        <?php foreach($mots_cles as $m) echo '<span class="tag">'.esc_html($m->name).'</span>'; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2>Dossier Scientifique</h2>

        <h3>Résumé court</h3>
        <div class="content-box">
            <?php echo wpautop(wp_kses_post(get_field('proj_resume_court', $project_id))); ?>
        </div>

        <?php foreach ( $sections as $field_name => $label ) : 
            $content = get_field( $field_name, $project_id );
            if ( ! $content ) continue; 
            ?>
            <div class="section-scientifique">
                <h3><?php echo esc_html( $label ); ?></h3>
                <div class="content-box">
                    <?php echo wpautop( wp_kses_post( $content ) ); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <h2>Calendrier et Organisation</h2>

        <?php 
        // Récupération des champs calendrier
        $calendrier_previsionnel = get_field('cand_calendrier_previsionnel', $project_id);
        $date_event = get_field('cand_date_event', $project_id);
        $duree_event = get_field('cand_duree_event', $project_id);
        $lieu_event = get_field('cand_lieu_event', $project_id);
        $autre_lieu = get_field('cand_autre_lieu', $project_id);
        $programme_previsionnel = get_field('cand_prog_previ', $project_id);
        $seances    = get_field('cand_seances', $project_id);

        // Helper pour formatage date
        $fmt_date = function($d) {
            return $d ? date_format(date_create($d), 'd/m/Y') : '-';
        };
        ?>

        <?php if ( in_array($ptype_slug, ['em', 'ma'], true) ): ?>        
                <h3>Calendrier de recherche prévisionnel</h3>
                <div class="content-box">
                    <?php echo wpautop( wp_kses_post( $calendrier_previsionnel ) ); ?>
                </div>

        <?php elseif ( $ptype_slug === 'ws' ): ?>
            <div class="props-grid">
                <div class="prop-item">
                    <label>Date de l'événement</label>
                    <span><?php echo esc_html( $fmt_date($date_event) ); ?></span>
                </div>
                <div class="prop-item">
                    <label>Durée de l'événement</label>
                    <span><?php echo esc_html( $duree_event ); ?></span>
                </div>
                <div class="prop-item" style="grid-column: span 2;">
                    <label>Lieu</label>
                    <span><?php echo esc_html( $lieu_event === 'autre' ? $autre_lieu : 'ENS Paris-Saclay' ); ?></span>
                </div>
                <div class="prop-item">
                    <label>Programme prévisionnel</label>
                    <span><?php echo esc_html( $programme_previsionnel ); ?></span>
                </div>
            </div>

        <?php elseif ( $ptype_slug === 'se' ): ?>
            
            <?php if ( is_array($seances) && !empty($seances) ): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%;">Séance</th>
                            <th style="width: 15%;">Date</th>
                            <th style="width: 40%;">Titre</th>
                            <th style="width: 30%;">Lieu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seances as $i => $s): 
                            $d = $s['date']  ?? '';
                            $t = $s['titre'] ?? '';
                            $l = $s['lieu']  ?? '';
                        ?>
                        <tr>
                            <td style="font-weight:bold;">#<?php echo ($i + 1); ?></td>
                            <td><?php echo esc_html( $fmt_date($d) ); ?></td>
                            <td><?php echo esc_html( $t ); ?></td>
                            <td><?php echo esc_html( $l ); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="font-style: italic; color: #666;">Aucune séance renseignée.</p>
            <?php endif; ?>

        <?php else: ?>
            <p style="font-style: italic; color: #666;">Aucune information de calendrier spécifique pour ce type de projet.</p>
        <?php endif; ?>

        <h2>Budget et Financement</h2>

        <?php 
        // Récupération des données Budget
        $budget_detail         = get_field('cand_budget_detail', $project_id);
        $budget_total          = get_field('cand_budget_total', $project_id);
        $cofinancements        = get_field('cand_cofinancements', $project_id);
        $cofinancements_detail = get_field('cand_cofinancements_detail', $project_id);
        $suiv_budget           = get_field('suiv_budget', $project_id);

        // Helper affichage monétaire
        $fmt_money = function($amount) {
            return number_format((float)$amount, 2, ',', ' ') . ' €';
        };
        ?>

        <h3>Budget demandé par le porteur</h3>
        
        <?php if ( is_array($budget_detail) && !empty($budget_detail) ): ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 70%;">Poste de dépense</th>
                        <th style="width: 30%; text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($budget_detail as $b): 
                        $poste = $b['poste'] ?? '';
                        $montant = $b['montant'] ?? 0;
                    ?>
                    <tr>
                        <td style="text-transform: uppercase; font-size: 0.9em;"><?php echo esc_html($poste); ?></td>
                        <td style="text-align: right; font-family: monospace; font-size: 1.1em;">
                            <?php echo esc_html($fmt_money($montant)); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td style="text-align: right; text-transform: uppercase;">Total Demandé</td>
                        <td style="text-align: right; font-family: monospace; font-size: 1.1em;">
                            <?php echo esc_html($fmt_money($budget_total)); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p style="font-style: italic; color: #666;">Aucun détail budgétaire renseigné.</p>
        <?php endif; ?>

        <?php if ( $cofinancements && is_array($cofinancements_detail) && !empty($cofinancements_detail) ): ?>
            <h3>Cofinancements acquis ou demandés</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 70%;">Source</th>
                        <th style="width: 30%; text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cofinancements_detail as $c): 
                        $source = $c['source'] ?? '';
                        $montant = $c['montant'] ?? 0;
                    ?>
                    <tr>
                        <td style="text-transform: uppercase; font-size: 0.9em;"><?php echo esc_html($source); ?></td>
                        <td style="text-align: right; font-family: monospace; font-size: 1.1em;">
                            <?php echo esc_html($fmt_money($montant)); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <script>
            // Lancement automatique au chargement
            window.onload = function() { setTimeout(function(){ window.print(); }, 500); }
        </script>
    </body>
    </html>
    <?php
    exit;
}

add_action('wp_ajax_msh_create_wave', 'msh_plugin_handle_create_wave');

function msh_plugin_handle_create_wave() {
    // 1. SÉCURITÉ : Vérification du Nonce
    check_ajax_referer('msh_wave_nonce', 'nonce');
    
    // 2. SÉCURITÉ : Vérification du droit spécifique demandé
    if (!current_user_can('edit_others_posts')) {
        wp_send_json_error(['message' => 'Action non autorisée. Droits insuffisants.']);
        return; // Important d'arrêter l'exécution ici
    }

    // 3. RÉCUPÉRATION
    $name       = sanitize_text_field($_POST['wave_name']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date   = sanitize_text_field($_POST['end_date']);

    if (empty($name) || empty($start_date) || empty($end_date)) {
        wp_send_json_error(['message' => 'Tous les champs sont requis.']);
    }

    // 4. CRÉATION DU TERME
    $term = wp_insert_term($name, 'projet_vague');

    if (is_wp_error($term)) {
        wp_send_json_error(['message' => $term->get_error_message()]);
    }

    $term_id = $term['term_id'];
    $acf_term_id = 'projet_vague' . $term_id;

    // 5. FORMATAGE ET SAUVEGARDE ACF
    $start_ymd = date('Ymd', strtotime($start_date));
    $end_ymd   = date('Ymd', strtotime($end_date));

    update_field('vague_date_ouverture', $start_ymd, $acf_term_id);
    update_field('vague_date_cloture', $end_ymd, $acf_term_id);

    // 6. CALCUL DU STATUT
    $today = date('Ymd');
    $is_active = ($today >= $start_ymd && $today <= $end_ymd);

    update_field('is_current', $is_active, $acf_term_id);

    // 7. NETTOYAGE (Si nouvelle vague active, désactiver les autres)
    if ($is_active) {
        $terms = get_terms(['taxonomy' => 'vague_projet', 'hide_empty' => false, 'exclude' => [$term_id]]);
        if (!is_wp_error($terms)) {
            foreach ($terms as $other_term) {
                update_field('is_current', false, 'vague_projet_' . $other_term->term_id);
            }
        }
    }

    wp_send_json_success([
        'message' => 'Vague créée avec succès !', 
        'is_active' => $is_active
    ]);
}

// Dans votre fichier plugin (aap-msh-core.php ou similaire)

add_action( 'admin_post_msh_handle_create_wave_post', 'msh_handle_create_wave_submission' );

function msh_handle_create_wave_submission() {
    // 1. SÉCURITÉ
    if ( ! isset( $_POST['msh_wave_nonce'] ) || ! wp_verify_nonce( $_POST['msh_wave_nonce'], 'msh_create_wave_action' ) ) {
        wp_die( 'Sécurité invalide' );
    }

    if ( ! current_user_can( 'edit_others_posts' ) ) {
        wp_die( 'Non autorisé' );
    }

    // 2. RÉCUPÉRATION
    $name       = sanitize_text_field( $_POST['wave_name'] );
    $start_date = sanitize_text_field( $_POST['start_date'] );
    $end_date   = sanitize_text_field( $_POST['end_date'] );
    
    // URL de retour
    $redirect_url = wp_get_referer() ? wp_get_referer() : home_url('/dashboard');

    // 3. CRÉATION DU TERME
    // ICI : On utilise le slug exact 'projet_vague'
    $term = wp_insert_term( $name, 'projet_vague' );

    if ( is_wp_error( $term ) ) {
        wp_redirect( add_query_arg( 'msh_msg', 'error', $redirect_url ) );
        exit;
    }

    $term_id = $term['term_id'];
    
    // IMPORTANT POUR ACF : Le format est toujours 'taxonomie_termID'
    $acf_term_id = 'projet_vague_' . $term_id;

    // 4. ACF & LOGIQUE
    $start_ymd = date('Ymd', strtotime($start_date));
    $end_ymd   = date('Ymd', strtotime($end_date));
    $today     = date('Ymd');
    
    $is_active = ($today >= $start_ymd && $today <= $end_ymd);

    // Mise à jour des champs ACF
    update_field('vague_date_ouverture', $start_ymd, $acf_term_id);
    update_field('vague_date_cloture', $end_ymd, $acf_term_id);
    update_field('is_current', $is_active, $acf_term_id);

    // 5. NETTOYAGE (Désactiver les autres vagues si celle-ci est active)
    if ($is_active) {
        $terms = get_terms([
            'taxonomy'   => 'projet_vague', // ICI aussi
            'hide_empty' => false, 
            'exclude'    => [$term_id]
        ]);
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $other_term) {
                // Construction de l'ID ACF pour les autres termes
                update_field('is_current', false, 'projet_vague_' . $other_term->term_id);
            }
        }
    }

    // 6. REDIRECTION SUCCÈS
    wp_redirect( add_query_arg( 'msh_msg', 'success', $redirect_url ) );
    exit;
}

/**
 * Génère automatiquement le titre des évaluations
 * Format : "Référence projet - Prénom Nom"
 * 
 * Se déclenche après la création/mise à jour d'une évaluation via WS Form
 */
add_action('save_post_evaluation', 'mshps_auto_generate_evaluation_title', 20, 3);

function mshps_auto_generate_evaluation_title($post_id, $post, $update) {
    // Éviter les boucles infinies et les révisions
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    
    // Éviter de se déclencher pendant notre propre mise à jour
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Récupérer les données nécessaires
    $projet_id = get_field('eval_projet_id', $post_id);
    $prenom = get_field('evaluateur_prenom', $post_id);
    $nom = get_field('evaluateur_nom', $post_id);
    
    // Si on n'a pas les données minimales, on ne fait rien
    if (!$prenom || !$nom) {
        return;
    }
    
    // Récupérer la référence du projet
    $ref_projet = '';
    if ($projet_id) {
        $ref_projet = get_field('proj_ref', $projet_id);
    }
    
    // Générer le titre
    $new_title = '';
    if ($ref_projet) {
        $new_title = $ref_projet . ' - ' . $prenom . ' ' . $nom;
    } else {
        $new_title = 'Évaluation - ' . $prenom . ' ' . $nom;
    }
    
    // Mettre à jour le titre seulement s'il est différent (éviter boucle)
    if ($post->post_title !== $new_title) {
        // Retirer temporairement le hook pour éviter la boucle
        remove_action('save_post_evaluation', 'mshps_auto_generate_evaluation_title', 20);
        
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $new_title
        ]);
        
        // Remettre le hook
        add_action('save_post_evaluation', 'mshps_auto_generate_evaluation_title', 20, 3);
    }
}

/**
 * Logger la création d'une évaluation dans l'historique du projet
 */
add_action('save_post_evaluation', 'mshps_log_evaluation_creation', 30, 3);
function mshps_log_evaluation_creation($post_id, $post, $update) {
    // Logger UNIQUEMENT si c'est une nouvelle création (pas une mise à jour)
    if ($update) {
        return;
    }
    
    // Éviter les autosaves et révisions
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    $projet_id = get_post_meta($post_id, 'eval_projet_id', true);
    if ($projet_id && function_exists('mshps_log_projet_event')) {
        mshps_log_projet_event($projet_id, 'evaluation_add', 'Nouvelle évaluation ajoutée');
    }
}

/**
 * Logger la modification d'une évaluation dans l'historique du projet
 */
add_action('save_post_evaluation', 'mshps_log_evaluation_update', 30, 3);
function mshps_log_evaluation_update($post_id, $post, $update) {
    // Logger UNIQUEMENT si c'est une mise à jour (pas une création)
    if (!$update) {
        return;
    }
    
    // Éviter les autosaves et révisions
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // Éviter les logs multiples en utilisant un flag temporaire
    $flag_key = 'mshps_eval_update_logged_' . $post_id;
    if (get_transient($flag_key)) {
        return; // Déjà loggé dans cette requête
    }
    
    $projet_id = get_post_meta($post_id, 'eval_projet_id', true);
    if ($projet_id && function_exists('mshps_log_projet_event')) {
        mshps_log_projet_event($projet_id, 'evaluation_update', 'Évaluation modifiée');
        // Marquer comme loggé pour 30 secondes (le temps de la requête)
        set_transient($flag_key, true, 30);
    }
}

/**
 * Logger la suppression d'une évaluation dans l'historique du projet
 */
add_action('before_delete_post', 'mshps_log_evaluation_deletion', 10, 2);
function mshps_log_evaluation_deletion($post_id, $post) {
    if (!$post || $post->post_type !== 'evaluation') {
        return;
    }
    
    $projet_id = get_post_meta($post_id, 'eval_projet_id', true);
    if ($projet_id && function_exists('mshps_log_projet_event')) {
        mshps_log_projet_event($projet_id, 'evaluation_delete', 'Évaluation supprimée');
    }
}

/**
 * Handler pour la suppression d'une évaluation
 * Accessible via admin-post.php
 */
add_action('admin_post_delete_evaluation', 'mshps_delete_evaluation');
function mshps_delete_evaluation() {
    // Vérifier que l'utilisateur est connecté et a les droits
    if (!is_user_logged_in() || !current_user_can('edit_others_posts')) {
        wp_die('Vous n\'avez pas les droits pour effectuer cette action.');
    }
    
    // Récupérer les paramètres
    $eval_id = isset($_POST['eval_id']) ? intval($_POST['eval_id']) : 0;
    $projet_id = isset($_POST['projet_id']) ? intval($_POST['projet_id']) : 0;
    
    // Vérifier le nonce
    if (!isset($_POST['delete_eval_nonce']) || !wp_verify_nonce($_POST['delete_eval_nonce'], 'delete_evaluation_' . $eval_id)) {
        wp_die('Erreur de sécurité. Veuillez réessayer.');
    }
    
    // Vérifier que l'évaluation existe
    if (!$eval_id || get_post_type($eval_id) !== 'evaluation') {
        wp_die('Évaluation introuvable.');
    }
    
    // Supprimer l'évaluation (suppression définitive)
    $deleted = wp_delete_post($eval_id, true);
    
    if (!$deleted) {
        wp_die('Erreur lors de la suppression de l\'évaluation.');
    }
    
    // Rediriger vers le projet (onglet évaluation)
    $redirect_url = $projet_id 
        ? add_query_arg('tab', 'evaluation', get_permalink($projet_id))
        : home_url('/dashboard/');
    
    wp_safe_redirect($redirect_url);
    exit;
}


add_action('admin_post_mshps_handle_download_cvs', 'mshps_handle_download_cvs');
function mshps_handle_download_cvs() {
    // 1. SÉCURITÉ
    if (!isset($_GET['projet_id']) || !isset($_GET['nonce'])) {
        wp_die('Requête invalide.');
    }
    
    $projet_id = intval($_GET['projet_id']);
    
    // Vérification du nonce spécifique à ce projet
    if (!wp_verify_nonce($_GET['nonce'], 'msh_download_cvs_' . $projet_id)) {
        wp_die('Lien expiré ou invalide.');
    }

    // Vérification des droits
    if (!current_user_can('edit_others_posts')) {
        wp_die('Accès refusé.');
    }

    // Initialisation
    $files_to_zip = [];
    $compteur = 1;

    // ---------------------------------------------------------
    // A. RÉCUPÉRATION DU CV DU PORTEUR PRINCIPAL
    // ---------------------------------------------------------
    
    // ACF retourne un tableau (Array) car tu as choisi "File Array"
    $cv_porteur = get_field('proj_porteur_cv', $projet_id);

    if ( $cv_porteur && isset($cv_porteur['ID']) ) {
        // On récupère le chemin absolu sur le disque (ex: /var/www/html/wp-content/...)
        $path = get_attached_file( $cv_porteur['ID'] );
        
        if ( file_exists($path) ) {
            // On renomme le fichier pour que ce soit clair dans le ZIP
            // On garde l'extension d'origine (.pdf, .docx...)
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $nom_porteur = get_field('proj_porteur_nom', $projet_id);
            $safe_name = sanitize_file_name($nom_porteur);
            if(empty($safe_name)) $safe_name = "Porteur";
            $files_to_zip[] = [
                'path' => $path,
                'name' => '0-CV-' . $safe_name . '.' . $ext // Nom dans le ZIP
            ];
        }
    }

    // ---------------------------------------------------------
    // B. RÉCUPÉRATION DES CO-PORTEURS (REPEATER)
    // ---------------------------------------------------------
    
    // Remplace 'co_porteurs' par le slug de ton champ Répéteur
    if ( have_rows('proj_porteurs', $projet_id) ) {
        while ( have_rows('proj_porteurs', $projet_id) ) {
            the_row();

            // Remplace 'cv' par le nom du sous-champ fichier dans le répéteur
            $cv_coporteur = get_sub_field('cv');
            // Remplace 'nom' par le sous-champ nom (pour renommer le fichier)
            $nom_coporteur = get_sub_field('nom'); 
            
            // Vérification que le champ n'est pas vide et contient bien un ID
            if ( $cv_coporteur && isset($cv_coporteur['ID']) ) {
                $path = get_attached_file( $cv_coporteur['ID'] );
                
                if ( file_exists($path) ) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    
                    // Nettoyage du nom pour éviter les caractères spéciaux dans le ZIP
                    $safe_name = sanitize_file_name($nom_coporteur);
                    if(empty($safe_name)) $safe_name = "Coporteur-{$compteur}";

                    $files_to_zip[] = [
                        'path' => $path,
                        'name' => $compteur . '-CV-' . $safe_name . '.' . $ext
                    ];
                    $compteur++;
                }
            }
        }
    }

    // ---------------------------------------------------------
    // C. CRÉATION DU ZIP
    // ---------------------------------------------------------
    
    if (empty($files_to_zip)) {
        wp_die('Aucun CV trouvé (ni porteur, ni co-porteurs).');
    }
    $reference = get_field('proj_ref', $projet_id);
    $zip = new ZipArchive();
    $zip_name = 'Projet-' . $reference . '-CVs.zip';
    $tmp_file = tempnam(sys_get_temp_dir(), 'msh_cv_zip');

    if ($zip->open($tmp_file, ZipArchive::CREATE) !== TRUE) {
        wp_die('Erreur création ZIP.');
    }

    foreach ($files_to_zip as $file) {
        // addFile(Chemin Réel, Nom dans le ZIP)
        $zip->addFile($file['path'], $file['name']);
    }

    $zip->close();

    // 1. On vide le tampon de sortie système pour virer les espaces/erreurs qui traînent
    if (ob_get_length()) {
        ob_end_clean(); 
    }
    
    // 2. Désactivation de la compression gzip (souvent active sur les hébergeurs comme Hostinger)
    // Si le serveur compresse le ZIP déjà compressé, ça corrompt le fichier.
    if(ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'Off');
    }

    // Log & Download headers...
    mshps_log_projet_event( $projet_id, 'attachment', 'Téléchargement des CVs (ZIP)');


    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($tmp_file));
    
    readfile($tmp_file);
    
    // On supprime le fichier temporaire
    unlink($tmp_file);
    exit;
}

/**
 * Logger la soumission initiale d'un projet
 * Se déclenche quand le projet passe de draft à un statut "déposé"
 */
add_action('transition_post_status', 'mshps_log_projet_submission', 10, 3);
function mshps_log_projet_submission($new_status, $old_status, $post) {
    // Uniquement pour les projets
    if ($post->post_type !== 'projet') {
        return;
    }
    
    // Logger quand le projet passe de draft/auto-draft à "projet-depose"
    if (in_array($old_status, ['draft', 'auto-draft']) && $new_status === 'projet-depose') {
        if (function_exists('mshps_log_projet_event')) {
            mshps_log_projet_event($post->ID, 'submission', 'Projet déposé par le porteur');
        }
    }
}