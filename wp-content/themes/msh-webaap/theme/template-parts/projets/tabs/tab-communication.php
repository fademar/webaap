<?php
// Récupération des champs
$kit_com = get_field('cand_kit_com');
$com_justification = get_field('cand_com_justification');
?>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Kit de communication</h3>
  <div class="mt-3">
    <?php echo $kit_com; ?>
  </div>
</section>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Justification du besoin</h3>
  <div class="mt-3">
    <?php echo $com_justification ? $com_justification : 'Aucune justification renseignée.'; ?>
  </div>
</section>

<?php msh_render_notes_module( get_the_ID(), 'communication' ); ?>