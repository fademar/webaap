<?php

// post meta attendu : 'em' | 'ma' | 'ws' | 'se'
$ptype = sanitize_key((string) get_post_meta(get_the_ID(), 'projet_type', true));

$date_debut = get_field('cand_date_debut');
$date_fin   = get_field('cand_date_fin');
$date_event = get_field('cand_date_event');
$seances    = get_field('cand_seances'); // repeater (array)
?>

<section class="flex flex-col gap-8 mt-6">
<div class="mt-3 rounded-xl border border-slate-200 bg-white p-4 text-sm">

    <?php if (in_array($ptype, ['em', 'ma'], true)): ?>

      <div class="flex flex-col gap-3 sm:flex-row sm:gap-6">
        <div class="flex flex-col">
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date de début</div>
          <div class="mt-1 text-slate-900">
            <?php echo $date_debut ? esc_html($date_debut) : '<span class="text-slate-400">—</span>'; ?>
          </div>
        </div>

        <div class="flex flex-col">
          <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date de fin</div>
          <div class="mt-1 text-slate-900">
            <?php echo $date_fin ? esc_html($date_fin) : '<span class="text-slate-400">—</span>'; ?>
          </div>
        </div>
      </div>

    <?php elseif ($ptype === 'ws'): ?>

      <div class="flex flex-col">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date de l’événement</div>
        <div class="mt-1 text-slate-900">
          <?php echo $date_event ? esc_html($date_event) : '<span class="text-slate-400">—</span>'; ?>
        </div>
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
                  <span class="text-slate-700"><?php echo esc_html($d); ?></span>
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

  </div>
    
</section>