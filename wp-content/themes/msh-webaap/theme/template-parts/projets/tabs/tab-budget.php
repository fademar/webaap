<?php
// Récupération des champs
$budget_detail         = get_field('cand_budget_detail'); // repeater
$budget_total          = get_field('cand_budget_total');
$cofinancements        = get_field('cand_cofinancements');
$cofinancements_detail = get_field('cand_cofinancements_detail');
$suiv_budget           = get_field('suiv_budget'); // Le budget accordé

// Vérification des droits pour l'édition
$can_edit_budget = current_user_can('edit_others_posts');
?>


<section class="flex flex-col mt-6">
  <h3 class="msh-label">Budget demandé par le porteur</h3>
  
  <?php if (is_array($budget_detail) && !empty($budget_detail)): ?>
  <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
        <tr>
            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Poste de dépense</th>
            <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-900">Montant (€)</th>
        </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
        <?php foreach ($budget_detail as $b): ?>
          <?php
            $p = $b['poste']  ?? '';
            $m = $b['montant'] ?? 0;
          ?>
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3 text-slate-900 uppercase">
                    <?php echo esc_html($p); ?>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-900 text-right font-mono">
                    <?php echo number_format((float)$m, 2, ',', ' '); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        
        <tr class="bg-slate-50/50">
          <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900 uppercase text-right">
            Total demandé
          </td>
          <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-900 text-right font-mono text-base">
            <?php echo number_format((float)$budget_total, 2, ',', ' '); ?> €
          </td>
        </tr>
        </tbody>
    </table>
  </div>
  <?php else: ?>
    <p class="text-slate-500 italic mt-2">Aucun détail budgétaire renseigné.</p>
  <?php endif; ?>
</section>


<section class="flex flex-col mt-10">
  <h3 class="msh-label">Cofinancements</h3> 
  <?php if ($cofinancements && is_array($cofinancements_detail) && !empty($cofinancements_detail)): ?> 
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50">
          <tr>
            <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Source</th>
            <th scope="col" class="px-4 py-3 text-right font-semibold text-slate-900">Montant (€)</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($cofinancements_detail as $c): ?>
            <?php
              $s = $c['source']  ?? '';
              $m = $c['montant'] ?? 0;
            ?>
              <tr class="hover:bg-slate-50">
              <td class="whitespace-nowrap px-4 py-3 text-slate-900 uppercase">
                <?php echo esc_html($s); ?>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-slate-900 text-right font-mono">
                <?php echo number_format((float)$m, 2, ',', ' '); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p class="text-slate-500 italic mt-2">Aucun cofinancement renseigné.</p>
  <?php endif; ?>
</section>

<section class="flex flex-col mt-10 mb-8 rounded-xl border border-blue-100 bg-blue-50/50 p-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="flex items-center font-bold text-blue-900 uppercase tracking-wide">
              Budget accordé : <?php echo $suiv_budget ? number_format((float)$suiv_budget, 2, ',', ' ') : '-'; ?> €
            </h3>
        </div>
        <div class="text-right">
            <?php if ( $can_edit_budget ) : ?>
                <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="action" value="mshps_update_project_budget">
                    <input type="hidden" name="project_id" value="<?php the_ID(); ?>">
                    <?php wp_nonce_field( 'msh_save_budget_action', 'msh_budget_nonce' ); ?>

                    <div class="relative w-fit"> <input 
                            type="number" 
                            name="suiv_budget" 
                            value="<?php echo esc_attr( $suiv_budget ); ?>" 
                            placeholder="0"
                            step="0.01"
                            class="w-32 rounded-lg border border-blue-200 bg-white pr-8 pl-3 py-2 text-right font-bold text-blue-900 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                        >
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 pointer-events-none font-medium">€</span>
                    </div>

                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                        <?php echo $suiv_budget ? 'Modifier' : 'Enregistrer'; ?>
                    </button>
                </form>
            <?php else : ?>
                <?php if ( ! empty($suiv_budget) ) : ?>
                    <div class="text-2xl font-bold text-blue-900">
                        <?php echo number_format((float)$suiv_budget, 2, ',', ' '); ?> €
                    </div>
                    <span class="text-xs font-semibold uppercase text-blue-600 bg-blue-100 px-2 py-1 rounded">Accordé</span>
                <?php else : ?>
                    <span class="text-sm italic text-blue-400">En cours d'instruction</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php msh_render_notes_module( get_the_ID(), 'budget' ); ?>
