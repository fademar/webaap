<?php
/**
 * Onglet Évaluation (Version optimisée pour textes longs)
 */

$project_id = get_the_ID();

// 1. Récupération des évaluations
$evals_query = new WP_Query([
    'post_type'      => 'evaluation',
    'posts_per_page' => -1,
    'meta_key'       => 'eval_projet_id',
    'meta_value'     => $project_id,
    'post_status'    => ['publish', 'private'],
]);

// Configuration des critères
$criteres_config = [
    'interinstit'    => 'Interinstitutionnalité',
    'interdisc'    => 'Interdisciplinarité',
    'novateur'     => 'Caractère novateur du projet',
    'objectifs'    => 'Objectifs et faisabilité',
    'science'      => 'Qualité scientifique et méthodologique',
    'equipe'       => 'Composition de l\'équipe',
    'perspectives' => 'Perspectives après financement',
    'budget'       => 'Cohérence du budget'
];
?>

<section class="mt-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="msh-label mb-1">Synthèse des évaluations</h3>
            <p class="text-sm text-slate-500">
                <?php echo $evals_query->found_posts; ?> évaluation(s) enregistrée(s)
            </p>
        </div>
        
        <button type="button" data-open-eval-modal 
                class="flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm transition-all">
            <i class="fa-solid fa-plus"></i> Nouvelle évaluation
        </button>
    </div>

    <?php if ( $evals_query->have_posts() ) : ?>
        <div class="grid grid-cols-1 gap-6">
            <?php while ( $evals_query->have_posts() ) : $evals_query->the_post(); 
                $eval_id = get_the_ID();
                $nom = get_field('evaluateur_nom');
                $prenom = get_field('evaluateur_prenom');
                $email = get_field('evaluateur_email');
                $institution = get_field('evaluateur_institution');
                $note_globale = get_field('note_generale'); 
                $commentaire = get_field('commentaire_general'); // Texte long
                
                $badge_class = match($note_globale) {
                    'A' => 'bg-green-100 text-green-800 border-green-200',
                    'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'C' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'D' => 'bg-red-100 text-red-800 border-red-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            ?>
            <div class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 p-6">
                
                <div class="flex gap-5 items-start">
                    
                    <div class="shrink-0 flex flex-col gap-2 items-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl border-2 text-3xl font-bold <?php echo $badge_class; ?>">
                            <?php echo $note_globale ?: '-'; ?>
                        </div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Note</div>
                    </div>

                    <div class="grow min-w-0"> <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="text-lg font-bold text-slate-900">
                                    <?php echo esc_html($nom.' '.$prenom ?: 'Évaluateur inconnu'); ?>
                                </h4>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="<?php echo get_edit_post_link($eval_id); ?>" target="_blank" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Modifier">
                                    <i class="fa-solid fa-pen text-lg"></i>
                                </a>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 relative">
                            <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Synthèse générale</h5>
                            
                            <div class="prose prose-sm max-w-none text-slate-700 transition-all duration-300 line-clamp-3" id="comment-<?php echo $eval_id; ?>">
                                <?php echo nl2br(esc_html($commentaire)); ?>
                            </div>
                            
                            <?php if (str_word_count($commentaire) > 30): ?>
                                <button onclick="toggleComment('<?php echo $eval_id; ?>', this)" 
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 mt-2 focus:outline-none flex items-center gap-1">
                                    <span>Lire la suite</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    <?php else : ?>
        <div class="rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-12 text-center">
            <p class="text-slate-500 font-medium">Aucune évaluation pour ce projet.</p>
        </div>
    <?php endif; ?>

</section>


<div id="eval-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" data-close-eval-modal></div>
    
    <div class="relative mx-auto mt-2 w-full max-w-6xl h-[98vh] flex flex-col bg-white shadow-2xl rounded-xl overflow-hidden">
        
        <!-- Header fixe -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Saisie d'évaluation</h3>
                <p class="text-xs text-slate-500 mt-0.5">Remplissez les critères d'évaluation ci-dessous</p>
            </div>
            <button type="button" data-close-eval-modal class="text-slate-400 hover:text-slate-700 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Body scrollable avec padding -->
        <div class="flex-1 overflow-y-auto px-6 py-4 lg:px-8 lg:py-4 bg-white">
            <?php echo do_shortcode('[ws_form id="11"]'); ?>
        </div>
        
    </div>
</div>

<script>
// Fonction pour étendre/réduire le texte
function toggleComment(id, btn) {
    const content = document.getElementById('comment-' + id);
    const span = btn.querySelector('span');
    
    if (content.classList.contains('line-clamp-3')) {
        // Ouvrir
        content.classList.remove('line-clamp-3');
        span.textContent = 'Réduire';
    } else {
        // Fermer
        content.classList.add('line-clamp-3');
        span.textContent = 'Lire la suite';
    }
}

// Gestion Modale
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('eval-modal');
    const openBtns = document.querySelectorAll('[data-open-eval-modal]');
    const closeBtns = document.querySelectorAll('[data-close-eval-modal]');
    const body = document.body;

    function toggleModal(show) {
        if(show) {
            modal.classList.remove('hidden');
            body.style.overflow = 'hidden'; 
        } else {
            modal.classList.add('hidden');
            body.style.overflow = '';
        }
    }

    openBtns.forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); toggleModal(true); }));
    closeBtns.forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); toggleModal(false); }));
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) toggleModal(false);
    });
});
</script>