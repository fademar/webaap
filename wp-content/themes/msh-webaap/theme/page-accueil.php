<?php
/**
 * Template Name: MSH - Accueil
 */

get_header('front');

?>

<main class="flex-grow flex items-center justify-center p-4 font-sans">
    
    <div class="bg-white w-full max-w-5xl h-auto md:h-[600px] rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/2 relative overflow-hidden flex flex-col bg-primaire justify-center p-12 text-white">
            <div class="relative z-10">
                <img src="/wp-content/uploads/2026/01/logo-MSH2025-blanc.png" alt="MS Paris-Saclay" class="w-1/2 mb-8">
                <p class="text-lg">Gérez vos candidatures aux appels à projets de la MSH Paris-Saclay.</p>
            </div>
            </div>

        <div class="w-full md:w-1/2 bg-white p-10 md:p-14 flex flex-col justify-center">
            
            <div class="mb-6">
                <div class="inline-flex items-center bg-red-50 border border-red-100 rounded-full px-4 py-2 text-sm text-red-600">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    La campagne 2025 est close
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mt-8">Connexion</h1>
                <p class="text-gray-400 text-sm mt-1">Identifiez-vous pour accéder à votre espace.</p>
            </div>
            <?php the_content();?>

            <div class="mt-8 text-center">
                 <p class="text-sm text-gray-400">Besoin d'un compte ? <a href="/inscription/" class="text-blue-600 hover:underline">Inscrivez-vous</a>.</p>
            </div>
        </div>
    </div>

</main>

<?php
get_footer('front');
?>