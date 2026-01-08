<?php
$cand_plateformes = get_field('cand_plateformes');
$plateformes_details = get_field('cand_plateformes_details');
?>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Usage des plateformes</h3>
  <div class="mt-3">
    <?php echo $cand_plateformes ? 'Oui' : 'Non'; ?>
  </div>
</section>

<section class="flex flex-col mt-6">
  <h3 class="msh-label">Détails de l'usage des plateformes</h3>
  <div class="mt-3">
    <?php echo $plateformes_details ? $plateformes_details : 'Aucun détail renseigné.'; ?>
  </div>
</section>  

<?php msh_render_notes_module( get_the_ID(), 'plateformes' ); ?>