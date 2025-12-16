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
    $q = new WP_Query( [
        'post_type'      => 'projet',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
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

	$allowed_scopes = [ 'projet', 'gestion', 'evaluation', 'budget', 'communication', 'edition', 'plateformes' ];
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

	// Génération automatique de la référence si passage en instruction et ref vide.
	if ( $new_status === 'projet-instruction' ) {
		// proj_ref est un champ ACF (field key). On privilégie ACF pour garder la méta "_proj_ref" cohérente.
		$ref_field_key = 'field_69270cf873e73';
		if ( function_exists( 'get_field' ) ) {
			$existing_ref = (string) get_field( $ref_field_key, $project_id );
		} else {
			$existing_ref = (string) get_post_meta( $project_id, 'proj_ref', true );
		}
		if ( $existing_ref === '' && function_exists( 'mshps_aap_generate_reference' ) ) {
			$ref = mshps_aap_generate_reference( $project_id );
			if ( $ref ) {
				if ( function_exists( 'update_field' ) ) {
					update_field( $ref_field_key, $ref, $project_id );
				} else {
					update_post_meta( $project_id, 'proj_ref', $ref );
				}
			}
		}
	}

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
	mshps_aap_require_msh_access();
	check_admin_referer( 'mshps_update_project_budget' );

	$project_id = isset( $_POST['project_id'] ) ? (int) $_POST['project_id'] : 0;
	if ( ! $project_id || get_post_type( $project_id ) !== 'projet' ) {
		wp_die( 'Projet invalide.', 400 );
	}

	$amount_raw = isset( $_POST['budget_accorde'] ) ? (string) wp_unslash( $_POST['budget_accorde'] ) : '';
	$amount_raw = str_replace( ',', '.', trim( $amount_raw ) );
	$amount     = $amount_raw === '' ? '' : (string) (float) $amount_raw;

	// Budget accordé = champ ACF suiv_budget (field key)
	$budget_field_key = 'field_6940114908e06';
	if ( function_exists( 'update_field' ) ) {
		update_field( $budget_field_key, $amount, $project_id );
	} else {
		// Fallback si ACF non chargé
		update_post_meta( $project_id, 'suiv_budget', $amount );
	}

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
        $view_url = add_query_arg( [ 'projet_id' => $id ], home_url( '/dashboard/' ) );
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
 * @param int $field_titre_id ID du champ Titre (par défaut 229)
 * @param int $field_type_id  ID du champ Type (par défaut 227)
 * @return array              Tableau d'objets avec les brouillons (valeurs désérialisées)
 */
function mshps_get_user_drafts( $form_id, $user_id, $field_titre_id = 229, $field_type_id = 227 ) {
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
add_action('wsf_submit_create', 'mshps_create_keywords_on_draft_save', 10, 1);
add_action('wsf_submit_update', 'mshps_create_keywords_on_draft_save', 10, 1);

function mshps_create_keywords_on_draft_save($submit) {
    global $wpdb;
    

    // Récupérer la valeur du champ mots-clés (field_232)
    $meta_value = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}wsf_submit_meta 
         WHERE parent_id = %d AND field_id = 232",
        $submit->id
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
            array('parent_id' => $submit->id, 'field_id' => 232),
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
 * Formulaire ID 8 (candidature)
 * Champs : 233 (nom), 234 (prénom), 235 (email), 236 (labo), 237 (établissement)
 */
add_filter('wsf_pre_render_8', 'mshps_prefill_user_info_in_form', 10, 2);
function mshps_prefill_user_info_in_form($form, $preview) {
    // Ne pré-remplir que si l'utilisateur est connecté
    if (!is_user_logged_in()) {
        return $form;
    }
    
    $current_user = wp_get_current_user();
    
    // Récupérer les informations de l'utilisateur
    $user_data = array(
        233 => $current_user->last_name,           // Nom
        234 => $current_user->first_name,          // Prénom
        235 => $current_user->user_email,          // Email
        236 => get_user_meta($current_user->ID, 'laboratoire', true),     // Laboratoire
        237 => get_user_meta($current_user->ID, 'etablissement', true),   // Établissement
    );
    
    // Parcourir les groupes du formulaire
    foreach ($form->groups as $group) {
        // Parcourir les sections
        foreach ($group->sections as $section) {
            // Parcourir les champs
            foreach ($section->fields as $field) {
                // Si c'est un des champs à pré-remplir
                if (isset($user_data[$field->id]) && !empty($user_data[$field->id])) {
                    // Définir la valeur par défaut
                    $field->default_value = $user_data[$field->id];
                }
            }
        }
    }
    
    return $form;
}

/**
 * WS Form (Post Management) : post-traitement après création/mise à jour du post.
 *
 * Objectifs (CPT ACF "projet") :
 * - Assigner automatiquement la vague courante (taxonomie projet_vague) si absente
 * - Générer la référence proj_ref dès le dépôt si absente
 * - Utiliser la référence comme slug (post_name)
 *
 * Hook fourni par l'add-on WS Form Post : wsf_action_post_post_meta
 * Arguments: ($form, $submit, $config, $post_id, $list_id, $taxonomy_tags)
 */
add_action( 'wsf_action_post_post_meta', 'mshps_aap_wsform_project_post_process', 20, 6 );
function mshps_aap_wsform_project_post_process( $form, $submit, $config, $post_id, $list_id, $taxonomy_tags ): void {
	$post_id = (int) $post_id;
	if ( ! $post_id || (string) $list_id !== 'projet' ) {
		return;
	}

	// 1) Assigner la vague courante si aucune vague n'est définie
	$current_wave_id = function_exists( 'mshps_aap_get_current_wave_id' ) ? (int) mshps_aap_get_current_wave_id() : 0;
	if ( $current_wave_id ) {
		$existing = wp_get_post_terms( $post_id, 'projet_vague', [ 'fields' => 'ids' ] );
		if ( empty( $existing ) && ! is_wp_error( $existing ) ) {
			wp_set_post_terms( $post_id, [ $current_wave_id ], 'projet_vague', false );
		}
	}

	// 2) Générer la référence si vide
	$ref_field_key = 'field_69270cf873e73'; // ACF key de proj_ref (déjà utilisée ailleurs)
	$existing_ref  = function_exists( 'get_field' ) ? (string) get_field( $ref_field_key, $post_id ) : (string) get_post_meta( $post_id, 'proj_ref', true );
	$existing_ref  = trim( $existing_ref );

	if ( $existing_ref === '' && function_exists( 'mshps_aap_generate_reference' ) ) {
		$ref = trim( (string) mshps_aap_generate_reference( $post_id ) );
		if ( $ref !== '' ) {
			if ( function_exists( 'update_field' ) ) {
				update_field( $ref_field_key, $ref, $post_id );
			} else {
				update_post_meta( $post_id, 'proj_ref', $ref );
			}

			// 3) Slug basé sur la référence (stable, court)
			$slug = sanitize_title( $ref );
			$slug = wp_unique_post_slug( $slug, $post_id, get_post_status( $post_id ), 'projet', 0 );

			// Éviter des updates inutiles
			$current_slug = (string) get_post_field( 'post_name', $post_id );
			if ( $slug !== '' && $slug !== $current_slug ) {
				wp_update_post( [
					'ID'        => $post_id,
					'post_name' => $slug,
				] );
			}
		}
	}
}
