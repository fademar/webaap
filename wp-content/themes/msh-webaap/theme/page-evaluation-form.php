<?php
/**
 * Template Name: MSH - Formulaire d'évaluation
 */

if ( ! is_user_logged_in() ) {
    auth_redirect();
}

// Vérifier les droits d'accès (équipe MSH uniquement)
if ( ! current_user_can('edit_others_posts') ) {
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header('app');

// Récupérer les paramètres
$projet_id = isset($_GET['projet_id']) ? intval($_GET['projet_id']) : 0;
$eval_id = isset($_GET['eval_id']) ? intval($_GET['eval_id']) : 0;

// Déterminer le mode (création ou modification)
$is_edit = $eval_id > 0;
$titre = $is_edit ? 'Modifier l\'évaluation' : 'Nouvelle évaluation';
$sous_titre = $is_edit ? 'Modifiez les critères d\'évaluation ci-dessous' : 'Remplissez les critères d\'évaluation ci-dessous';

// URL de retour
$back_url = '';
if ($eval_id) {
    $back_url = get_permalink($eval_id);
} elseif ($projet_id) {
    $back_url = add_query_arg('tab', 'evaluation', get_permalink($projet_id));
}
?>

<div class="min-h-screen bg-slate-50">
    <main class="mx-auto max-w-6xl p-6">
        
        <!-- Header avec navigation -->
        <div class="mb-6">
            <?php if ($back_url): ?>
                <a href="<?php echo esc_url($back_url); ?>" 
                   class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    Retour
                </a>
            <?php endif; ?>
        </div>

        <!-- Formulaire -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            
            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-200 bg-slate-50">
                <h1 class="text-2xl font-bold text-slate-900"><?php echo esc_html($titre); ?></h1>
                <p class="text-sm text-slate-600 mt-1"><?php echo esc_html($sous_titre); ?></p>
            </div>
            
            <!-- Formulaire WS Form -->
            <div class="px-8 py-6">
                <?php 
                // Construction du shortcode avec pré-remplissage si mode édition
                if ($eval_id && get_post_type($eval_id) === 'evaluation') {
                    // Récupérer toutes les données ACF de l'évaluation
                    $fields = get_fields($eval_id);
                    
                    // Construire le shortcode dynamiquement
                    $shortcode = '[ws_form id="11"';
                    $shortcode .= ' field_390="' . esc_attr($projet_id) . '"';
                    $shortcode .= ' field_457="' . esc_attr($eval_id) . '"'; // ID de l'évaluation pour update
                    
                    // Champs évaluateur (groupe ACF)
                    if (isset($fields['evaluateur'])) {
                        $shortcode .= ' field_365="' . esc_attr($fields['evaluateur']['nom'] ?? '') . '"';
                        $shortcode .= ' field_366="' . esc_attr($fields['evaluateur']['prenom'] ?? '') . '"';
                        $shortcode .= ' field_367="' . esc_attr($fields['evaluateur']['email'] ?? '') . '"';
                        $shortcode .= ' field_368="' . esc_attr($fields['evaluateur']['institution'] ?? '') . '"';
                    }
                    
                    // Note et commentaire général
                    if (isset($fields['note_generale'])) {
                        $shortcode .= ' field_407="' . esc_attr($fields['note_generale']) . '"';
                    }
                    if (isset($fields['commentaire_general'])) {
                        $shortcode .= ' field_386="' . esc_attr($fields['commentaire_general']) . '"';
                    }
                    
                    // Critères (groupe ACF)
                    if (isset($fields['criteres'])) {
                        $criteres_mapping = [
                            'interinstit_note' => 392,    // IDs WS Form à compléter
                            'interinstit_comment' => 370,
                            'interdisc_note' => 393,
                            'interdisc_comment' => 372,
                            'novateur_note' => 403,
                            'novateur_comment' => 374,
                            'objectifs_note' => 402,
                            'objectifs_comment' => 376,
                            'science_note' => 401,
                            'science_comment' => 378,
                            'equipe_note' => 404,
                            'equipe_comment' => 380,
                            'perspectives_note' => 405,
                            'perspectives_comment' => 382,
                            'budget_note' => 406,
                            'budget_comment' => 384,
                        ];
                        
                        foreach ($criteres_mapping as $acf_key => $wsf_id) {
                            if ($wsf_id !== null && isset($fields['criteres'][$acf_key])) {
                                $shortcode .= ' field_' . $wsf_id . '="' . esc_attr($fields['criteres'][$acf_key]) . '"';
                            }
                        }
                    }
                    
                    $shortcode .= ']';
                    echo do_shortcode($shortcode);
                    
                } else {
                    // Mode création : ID du projet + champ 457 vide (important pour éviter l'erreur "invalid post type")
                    echo do_shortcode('[ws_form id="11" field_390="'.$projet_id.'" field_457=""]');
                }
                ?>
            </div>
            
        </div>

    </main>
</div>

<?php get_footer('app'); ?>
