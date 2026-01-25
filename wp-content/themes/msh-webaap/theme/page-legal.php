<?php
/**
 * Template Name: MSH - Page Légale
 * Description: Pour les mentions légales, politique de confidentialité, CGU, etc.
 */

get_header('front');
?>

<main class="flex-grow flex items-center justify-center p-4 md:p-8 font-sans bg-gradient-to-br from-slate-50 to-slate-100">
    
    <div class="w-full max-w-4xl">
        
        <!-- Contenu dans une card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-6 w-40 h-full mx-auto">
                    <img src="/wp-content/uploads/2026/01/logo-MSH2025.png" alt="MSH Paris-Saclay" class="w-full h-full object-contain">
                </div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-900 mb-3">
                    <?php the_title(); ?>
                </h1>
                <?php if (get_the_modified_date()) : ?>
                    <p class="text-xs text-gray-400">
                        Dernière mise à jour : <?php echo get_the_modified_date('d/m/Y'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Contenu de la page -->
            <article class="prose prose-slate prose-lg max-w-none">
                <?php
                while ( have_posts() ) :
                    the_post();
                    the_content();
                endwhile;
                ?>
            </article>
        </div>

        <!-- Lien de retour -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <a href="<?php echo home_url('/'); ?>" class="text-primaire hover:underline font-medium">
                ← Retour à l'accueil
            </a>
        </div>

    </div>

</main>

<?php
get_footer('front');
?>
