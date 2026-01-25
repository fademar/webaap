<?php
/**
 * Template Name: MSH - Inscription
 */

get_header('front');
$shortcode = '[ws_form id="9"]';

?>

<main class="flex-grow flex items-center justify-center p-4 md:p-8 font-sans bg-gradient-to-br from-slate-50 to-slate-100">
    
    <div class="w-full max-w-4xl">
        
        <!-- En-tête avec logo et présentation -->

        <!-- Formulaire dans une card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-6 w-40 h-full mx-auto">
                <img src="/wp-content/uploads/2026/01/logo-MSH2025.png" alt="MSH Paris-Saclay" class="w-full h-full object-contain">
            </div>
            <h1 class="text-xl md:text-3xl font-bold text-gray-900 mb-3">
                Créer un compte
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Rejoignez la plateforme de la MSH Paris-Saclay pour gérer vos candidatures aux appels à projets.
            </p>
        </div>
            <?php echo do_shortcode($shortcode); ?>
        </div>

        <!-- Pied de page optionnel -->
        <div class="text-center mt-6 text-sm text-gray-500">
            <p>Vous avez déjà un compte ? <a href="<?php echo home_url('/'); ?>" class="text-primaire font-medium hover:underline">Se connecter</a></p>
        </div>

    </div>

</main>

<?php
get_footer('front');
?>