<?php
/**
 * Template pour afficher une évaluation complète
 * Template Post Type: evaluation
 */

if ( ! is_user_logged_in() ) {
    wp_safe_redirect(home_url('/'));
    exit;
}

// Vérifier les droits d'accès (équipe MSH uniquement)
if ( ! current_user_can('edit_others_posts') ) {
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header('app');

while ( have_posts() ) : the_post();
    
    $eval_id = get_the_ID();
    
    // Récupérer les informations de l'évaluateur
    $nom = get_field('evaluateur_nom');
    $prenom = get_field('evaluateur_prenom');
    $email = get_field('evaluateur_email');
    $institution = get_field('evaluateur_institution');
    
    // Note et commentaire général
    $note_generale = get_field('note_generale');
    $commentaire_general = get_field('commentaire_general');
    
    // Configuration des critères
    $criteres_config = [
        'interinstit'    => 'Interinstitutionnalité',
        'interdisc'      => 'Interdisciplinarité',
        'novateur'       => 'Caractère novateur du projet',
        'objectifs'      => 'Objectifs et faisabilité',
        'science'        => 'Qualité scientifique et méthodologique',
        'equipe'         => 'Composition de l\'équipe',
        'perspectives'   => 'Perspectives après financement',
        'budget'         => 'Cohérence du budget'
    ];
    
    // ID du projet associé
    $projet_id = get_field('eval_projet_id');
    $projet_url = $projet_id ? add_query_arg('tab', 'evaluation', get_permalink($projet_id)) : '';
    
    // Badge de note
    $badge_class = match($note_generale) {
        'A' => 'bg-green-100 text-green-800 border-green-200',
        'B' => 'bg-blue-100 text-blue-800 border-blue-200',
        'C' => 'bg-orange-100 text-orange-800 border-orange-200',
        'D' => 'bg-red-100 text-red-800 border-red-200',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
?>

<div class="min-h-screen bg-slate-50">
    <main class="mx-auto max-w-5xl p-6">
        
        <!-- Header avec navigation -->
        <div class="mb-6 flex items-center justify-between">
            <?php if ($projet_url): ?>
                <a href="<?php echo esc_url($projet_url); ?>" 
                   class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-900 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                    Retour au projet
                </a>
            <?php endif; ?>
            
            <div class="flex items-center gap-3">
                <a href="<?php echo esc_url(home_url('/nouvelle-evaluation/?eval_id=' . $eval_id . '&projet_id=' . $projet_id)); ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-pen"></i>
                    Modifier
                </a>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette évaluation ? Cette action est irréversible.');"
                      class="m-0">
                    <?php wp_nonce_field('delete_evaluation_' . $eval_id, 'delete_eval_nonce'); ?>
                    <input type="hidden" name="action" value="delete_evaluation">
                    <input type="hidden" name="eval_id" value="<?php echo esc_attr($eval_id); ?>">
                    <input type="hidden" name="projet_id" value="<?php echo esc_attr($projet_id); ?>">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition-colors">
                        <i class="fa-solid fa-trash"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <!-- Contenu principal -->
        <article class="bg-white rounded-2xl border border-slate-200 p-8 space-y-8">
            
            <!-- En-tête de l'évaluation -->
            <header class="border-b border-slate-200 pb-6">
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex h-20 w-20 items-center justify-center rounded-xl border-2 text-4xl font-bold <?php echo $badge_class; ?>">
                            <?php echo esc_html($note_generale ?: '-'); ?>
                        </div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mt-2">Note</div>
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-slate-900 mb-2">
                            Évaluation par <?php echo esc_html($prenom . ' ' . $nom); ?>
                        </h1>
                        <?php if ($institution): ?>
                            <p class="text-slate-600"><?php echo esc_html($institution); ?></p>
                        <?php endif; ?>
                        <?php if ($email): ?>
                            <p class="text-sm text-slate-500 mt-1">
                                <i class="fa-solid fa-envelope mr-1"></i>
                                <?php echo esc_html($email); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Appréciation générale -->
            <section>
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg border-2 text-sm font-bold <?php echo $badge_class; ?>">
                        <?php echo esc_html($note_generale ?: '-'); ?>
                    </span>
                    Appréciation générale
                </h2>
                <div class="bg-slate-50 rounded-lg p-6 border border-slate-200">
                    <p class="text-slate-700 whitespace-pre-wrap leading-relaxed">
                        <?php echo esc_html($commentaire_general ?: 'Aucun commentaire'); ?>
                    </p>
                </div>
            </section>

            <!-- Critères d'évaluation détaillés -->
            <section>
                <h2 class="text-lg font-bold text-slate-900 mb-4">Critères d'évaluation détaillés</h2>
                <div class="space-y-4">
                    <?php foreach ($criteres_config as $key => $label): 
                        $note = get_field('criteres_' . $key . '_note');
                        $commentaire = get_field('criteres_' . $key . '_comment');
                        
                        $critere_badge_class = match($note) {
                            'A' => 'bg-green-100 text-green-800 border-green-200',
                            'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'C' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'D' => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                    ?>
                        <div class="border border-slate-200 rounded-lg p-5 bg-white hover:bg-slate-50 transition-colors">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-base font-semibold text-slate-900"><?php echo esc_html($label); ?></h3>
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg border-2 text-base font-bold <?php echo $critere_badge_class; ?>">
                                    <?php echo esc_html($note ?: '-'); ?>
                                </span>
                            </div>
                            <?php if ($commentaire): ?>
                                <div class="bg-slate-50 rounded p-4 text-sm text-slate-700 leading-relaxed">
                                    <p class="whitespace-pre-wrap"><?php echo esc_html($commentaire); ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-slate-400 italic">Aucun commentaire pour ce critère</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

        </article>

    </main>
</div>

<?php endwhile; get_footer('app'); ?>
