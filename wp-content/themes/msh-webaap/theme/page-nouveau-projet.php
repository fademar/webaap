<?php
/**
 * Template Name: MSH - Nouveau Projet
 */

// WS Form gère ses propres headers, on peut retirer acf_form_head()
get_header('test'); 

// 1. Récupération du paramètre d'URL
$ptype = isset($_GET['ptype']) ? sanitize_text_field($_GET['ptype']) : null;

// 2. Sécurité : Liste des slugs autorisés
// IMPORTANT : Doit correspondre aux slugs de ta taxonomie 'projet_type'
// et aux liens href du fichier selection-grid.php
$valid_types = ['se', 'ws', 'ma', 'em']; 

?>

    <main id="app-main-content" class="min-h-screen bg-slate-50/50">
        <?php get_template_part( 'template-parts/layout/topbar', null, [ 'active_page' => 'nouveau-projet' ] ); ?>

        <div class="max-w-[1400px] mx-auto my-4 px-6 lg:px-8 py-8 bg-white border border-gray-200/60 rounded-2xl">

        <?php 
        // 3. AIGUILLAGE
        if ( $ptype && in_array($ptype, $valid_types) ) {
            
            // CAS A : Affichage du Formulaire
            // On appelle le fichier template 'form-wizard.php'
            get_template_part('template-parts/projects/form-wizard', null, ['ptype' => $ptype]);
            
        } else {
            
            // CAS B : Affichage de la Grille de choix
            ?>
            
            <?php
            get_template_part( 'template-parts/layout/page-header', null, [
                'title'    => 'Nouveau projet',
                'subtitle' => 'Bonjour ' . ( $current_user->first_name ?: $current_user->display_name ) . ', sélectionnez un dispositif de financement.',
            ] );
            ?>

            <?php get_template_part('template-parts/projects/selection-grid'); ?>
            
            <?php
        }
        ?>

        </div>
    </main>

<?php get_footer('test'); ?>