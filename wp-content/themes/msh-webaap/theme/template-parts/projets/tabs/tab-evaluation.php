<?php
/**
 * Onglet Évaluation (Version optimisée pour textes longs)
 */

$project_id = get_the_ID();

// 1. Récupération des évaluations
$evals_query = new WP_Query([
    'post_type'      => 'evaluation',
    'posts_per_page' => -1,
    'meta_key'       => 'eval_project_id',
    'meta_value'     => $project_id,
    'post_status'    => ['publish', 'private'],
]);

// Configuration des critères
$criteres_config = [
    'interinst'    => 'Interinstitutionnalité',
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
                class="flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2 text-sm font-bold text-white hover:bg-blue-800 shadow-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nouvelle évaluation
        </button>
    </div>

    <?php if ( $evals_query->have_posts() ) : ?>
        <div class="grid grid-cols-1 gap-6">
            <?php while ( $evals_query->have_posts() ) : $evals_query->the_post(); 
                $eval_id = get_the_ID();
                $nom = get_field('eval_nom');
                $discipline = get_field('eval_discipline');
                $note_globale = get_field('eval_globale_note'); 
                $pdf_file = get_field('eval_fichier');
                $commentaire = get_field('eval_commentaire_general'); // Texte long
                
                $badge_class = match($note_globale) {
                    'A' => 'bg-green-100 text-green-800 border-green-200',
                    'B' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'C' => 'bg-orange-100 text-orange-800 border-orange-200',
                    'D' => 'bg-red-100 text-red-800 border-red-200',
                    default => 'bg-slate-100 text-slate-600 border-slate-200',
                };
            ?>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
                
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
                                    <?php echo esc_html($nom ?: 'Évaluateur inconnu'); ?>
                                </h4>
                                <span class="inline-block bg-slate-100 text-slate-600 text-xs px-2 py-1 rounded border border-slate-200 mt-1">
                                    <?php echo esc_html($discipline ?: 'Discipline non renseignée'); ?>
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <?php if($pdf_file): ?>
                                    <a href="<?php echo esc_url($pdf_file['url']); ?>" target="_blank" class="p-2 text-slate-400 hover:text-red-600 transition-colors" title="Voir le PDF original">
                                        <i class="fa-solid fa-file-pdf text-xl"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo get_edit_post_link($eval_id); ?>" target="_blank" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Modifier">
                                    <i class="fa-solid fa-pen text-lg"></i>
                                </a>
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-lg p-4 border border-slate-100 relative">
                            <h5 class="text-xs font-bold text-slate-500 uppercase mb-2">Synthèse de l'évaluateur</h5>
                            
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

                        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-2 border-t border-slate-100 pt-3">
                            <?php 
                            // On affiche juste quelques critères clés pour info rapide
                            $preview_keys = ['novateur', 'science', 'equipe', 'budget'];
                            foreach($preview_keys as $k): 
                                $n = get_field('critere_'.$k.'_note');
                                $col = match($n) { 'A'=>'text-green-600', 'B'=>'text-blue-600', 'C'=>'text-orange-600', 'D'=>'text-red-600', default=>'text-slate-400' };
                            ?>
                                <div class="text-xs flex justify-between bg-white border border-slate-100 px-2 py-1 rounded">
                                    <span class="text-slate-500 truncate mr-2"><?php echo substr($criteres_config[$k], 0, 15); ?>.</span>
                                    <span class="font-bold <?php echo $col; ?>"><?php echo $n ?: '-'; ?></span>
                                </div>
                            <?php endforeach; ?>
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
        
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" enctype="multipart/form-data" class="flex flex-col h-full">
            
            <input type="hidden" name="action" value="mshps_add_evaluation">
            <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_url( get_permalink() ); ?>">
            <?php wp_nonce_field( 'mshps_add_eval_action' ); ?>

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50 shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Saisie d'évaluation</h3>
                </div>
                <button type="button" data-close-eval-modal class="text-slate-400 hover:text-slate-700">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-8 bg-white scroll-smooth">
                
                <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 mb-8">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-900 mb-4">L'Évaluateur</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="evaluateur_nom" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                            <input type="text" name="evaluateur_prenom" required class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                            <input type="email" name="evaluateur_email" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Institution</label>
                            <input type="text" name="evaluateur_institution" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900">
                        </div>
                    </div>
                </div>

                <div class="mb-10">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4 border-b pb-2">Appréciation Globale</h4>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <div class="lg:col-span-4 space-y-6">
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                                <label class="block text-sm font-bold text-slate-900 mb-2">Note Finale <span class="text-red-500">*</span></label>
                                <select name="note_generale" required class="w-full rounded-lg border-slate-300 font-bold text-lg text-blue-900 h-12">
                                    <option value="">-</option>
                                    <option value="A">A - Excellent</option>
                                    <option value="B">B - Bon</option>
                                    <option value="C">C - Moyen</option>
                                    <option value="D">D - Insuffisant</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">PDF Original (Si dispo)</label>
                                <input type="file" name="eval_file" accept=".pdf" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200"/>
                            </div>
                        </div>

                        <div class="lg:col-span-8">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Commentaire Général / Synthèse</label>
                            <textarea name="commentaire_general" rows="8" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900 leading-relaxed shadow-sm p-4" placeholder="Copier-coller ici la synthèse générale de l'évaluateur..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6 border-b pb-2">Détail par critères</h4>
                    
                    <div class="space-y-8">
                        <?php foreach ($criteres_config as $key => $label): ?>
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-8 items-start hover:bg-slate-50 p-4 rounded-lg transition-colors border border-transparent hover:border-slate-200">
                            
                            <div class="lg:col-span-4">
                                <label class="block text-sm font-bold text-slate-900 mb-3"><?php echo esc_html($label); ?></label>
                                <div class="flex gap-2">
                                    <?php foreach(['A','B','C','D'] as $note): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="criteres_<?php echo $key; ?>_note" value="<?php echo $note; ?>" class="peer sr-only" required>
                                        <div class="w-10 h-10 rounded-md flex items-center justify-center border border-slate-300 text-sm font-bold text-slate-500 bg-white peer-checked:bg-blue-900 peer-checked:text-white peer-checked:border-blue-900 hover:border-slate-400 transition-all shadow-sm">
                                            <?php echo $note; ?>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="lg:col-span-8">
                                <textarea name="criteres_<?php echo $key; ?>_comment" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-900 focus:border-blue-900" placeholder="Commentaire spécifique pour ce critère..."></textarea>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 px-6 py-4 border-t border-slate-200 bg-slate-50 shrink-0">
                <button type="button" data-close-eval-modal class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</button>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-900 text-sm font-semibold text-white hover:bg-blue-800 shadow-md">Enregistrer</button>
            </div>

        </form>
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