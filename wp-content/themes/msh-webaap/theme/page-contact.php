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

            <!-- Informations de contact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <!-- Email -->
                <div class="bg-slate-50 rounded-xl p-6 flex items-start">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fa-solid fa-envelope text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Email</h3>
                        <?php 
                        $email = 'aap@msh-paris-saclay.fr';
                        $encoded_email = antispambot($email);
                        ?>
                        <a href="mailto:<?php echo antispambot($email); ?>" class="text-sm text-primaire hover:underline">
                            <?php echo $encoded_email; ?>
                        </a>
                    </div>
                </div>

                <!-- Téléphone -->
                <div class="bg-slate-50 rounded-xl p-6 flex items-start">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fa-solid fa-phone text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">Téléphone</h3>
                        <a href="tel:+33000000000" class="text-sm text-primaire hover:underline">
                            01 00 00 00 00
                        </a>
                    </div>
                </div>

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
