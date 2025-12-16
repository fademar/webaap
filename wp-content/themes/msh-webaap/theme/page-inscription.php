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
                <h2 class="text-3xl font-bold mb-4">MSH</h2>
                <p class="text-lg">Gérez vos projets de recherche, vos équipes et vos financements en un seul endroit sécurisé.</p>
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