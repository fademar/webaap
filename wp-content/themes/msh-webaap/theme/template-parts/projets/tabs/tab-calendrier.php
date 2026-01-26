<?php

// post term attendu : 'em' | 'ma' | 'ws' | 'se'
$terms = get_the_terms( get_the_ID(), 'projet_type' );
$ptype = '';

if ( $terms && ! is_wp_error( $terms ) ) {
    // On prend le premier terme trouvé et on récupère son SLUG (ex: 'em', 'ma', 'se')
    $term = reset( $terms );
    $ptype = $term->slug;
}

$date_calendrier_previsionnel = get_field('cand_calendrier_previsionnel');
$date_event = get_field('cand_date_event');
$duree_event = get_field('cand_duree_event');
$lieu_event = get_field('cand_lieu_event');
$autre_lieu = get_field('cand_autre_lieu');
$seances    = get_field('cand_seances'); // repeater (array)
?>

<section class="flex flex-col mt-10">
    <?php if (in_array($ptype, ['em', 'ma'], true)): ?>

      <div class="mb-8"> 
          <h3 class="text-sm font-bold text-slate-600 uppercase mb-2">
          Calendrier prévisionnel
          </h3>
          
          <div class="prose prose-slate max-w-none">
            <?php echo $date_calendrier_previsionnel ? esc_html($calendrier_previsionnel) : '<span class="text-slate-400">—</span>'; ?>
          </div>
      </div>

    <?php elseif ($ptype === 'ws'): ?>

      <div class="mb-8"> 
          <h3 class="text-sm font-bold text-slate-600 uppercase mb-2">
          Date de l'évenement
          </h3>
          
          <div class="prose prose-slate max-w-none">
            <?php echo $date_event ? esc_html(date_format(date_create($date_event), 'd/m/Y')) : '<span class="text-slate-400">—</span>'; ?>
          </div>
      </div>
      <div class="mb-8"> 
          <h3 class="text-sm font-bold text-slate-600 uppercase mb-2">
          Lieu de l'évenement
          </h3>
          
          <div class="prose prose-slate max-w-none">
            <?php echo $lieu_event === 'autre' ? esc_html($autre_lieu) : '<span class="text-slate-400">ENS</span>'; ?>
          </div>

          <?php if ($autre_lieu): ?>
            <div class="text-slate-600">Autre lieu : <?php echo esc_html($autre_lieu); ?></div>
          <?php endif; ?>
      </div>

    <?php elseif ($ptype === 'se'): ?>

      <?php if (is_array($seances) && !empty($seances)): ?>
        <div class="flex flex-col gap-2">
          <?php foreach ($seances as $i => $s): ?>
            <?php
              // Adaptez aux clés exactes de vos sous-champs
              $d = $s['date']  ?? '';
              $t = $s['titre'] ?? '';
              $l = $s['lieu']  ?? '';
            ?>
            <div class="flex flex-col gap-1 rounded-lg border border-slate-200 bg-slate-50 p-3">
              <div class="flex items-baseline gap-2">
                <span class="font-semibold text-slate-900"><?php echo esc_html('Séance ' . ($i + 1)); ?></span>
                <?php if ($d): ?>
                  <span class="text-slate-700"><?php echo esc_html(date_format(date_create($d), 'd/m/Y')); ?></span>
                <?php endif; ?>
              </div>

              <?php if ($t): ?>
                <div class="text-slate-800"><?php echo esc_html($t); ?></div>
              <?php endif; ?>

              <?php if ($l): ?>
                <div class="text-slate-600"><?php echo esc_html($l); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-slate-600">Aucune séance renseignée.</p>
      <?php endif; ?>

    <?php else: ?>

      <p class="text-slate-600">Type de projet non renseigné.</p>

    <?php endif; ?>
    
</section>

<?php msh_render_notes_module( get_the_ID(), 'calendrier' ); ?>