<?php
/**
 * Template Name: MSH - Inscription
 */

get_header('front');
$shortcode = '[ws_form id="9"]';

?>

<main class="flex-grow flex items-center justify-center p-4 font-sans">
    
    <div class="bg-white w-full max-w-5xl h-auto md:min-h-[600px] rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/2 relative overflow-hidden flex flex-col bg-primaire justify-center p-12 text-white">
            <div class="relative z-10">
                <img src="/wp-content/uploads/2026/01/logo-MSH2025-blanc.png" alt="MSH Paris-Saclay" class="w-1/2 mb-8">
                <p class="text-lg">Gérez vos candidatures aux appels à projets de la MSH Paris-Saclay.</p>
            </div>
        </div>
        
        <div class="w-full md:w-1/2 bg-white p-6 md:p-8 flex flex-col justify-center">

            <?php echo do_shortcode($shortcode); ?>
        </div>
    </div>

</main>

<?php
get_footer('front');
?>