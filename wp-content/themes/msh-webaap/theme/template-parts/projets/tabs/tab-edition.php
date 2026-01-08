<?php 
$cand_edition = get_field('cand_edition');
$edition_details = get_field('cand_edition_details');
?>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Projet éditorial</h3>
  <div class="mt-3">
    <?php echo $cand_edition ? 'Oui' : 'Non'; ?>
  </div>
</section>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Détails du projet éditorial</h3>
  <div class="mt-3">
    <?php echo $edition_details ? $edition_details : 'Aucun détail renseigné.'; ?>
  </div>
</section>

<?php msh_render_notes_module( get_the_ID(), 'edition' ); ?>