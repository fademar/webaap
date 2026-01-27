<?php
/**
 * Template Name: MSH - Contact
 * Description: Page de contact avec formulaire WS Form
 */

get_header('front');

// ID du formulaire de contact WS Form (à ajuster selon votre configuration)
$contact_form_id = 12; // Changez cet ID selon votre formulaire
$shortcode = '[ws_form id="' . $contact_form_id . '"]';
?>

<main class="flex-grow flex items-center justify-center p-4 md:p-8 font-sans bg-gradient-to-br from-slate-50 to-slate-100">
    
    <div class="w-full max-w-4xl">
        
        <!-- Formulaire dans une card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-6 w-40 h-full mx-auto">
                    <img src="/wp-content/uploads/2026/01/logo-MSH2025.png" alt="MSH Paris-Saclay" class="w-full h-full object-contain">
                </div>
                <h1 class="text-xl md:text-3xl font-bold text-gray-900 mb-3">
                    Nous contacter
                </h1>
                <p class="text-lg text-gray-600">
                    Une question ? Notre équipe est là pour vous aider.
                </p>
            </div>

            <!-- Formulaire de contact -->
            <div class="border-t border-gray-100 pt-8">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Envoyez-nous un message</h2>
                <?php echo do_shortcode($shortcode); ?>
            </div>
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
