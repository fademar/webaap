<?php
/**
 * Template Name: MSH - Inscription
 */

get_header('front');
$shortcode = '[ws_form id="10"]';

// --- LOGIQUE MÉTIER : Récupérer la vague active ---
$active_wave = null;
$closing_date_display = '';

// On cherche le terme qui a le champ ACF 'is_current' à true
$terms = get_terms([
    'taxonomy'   => 'projet_vague',
    'hide_empty' => false, // Important : même s'il n'y a pas encore de projets, la vague existe
    'meta_query' => [
        [
            'key'     => 'is_current',
            'value'   => '1', // ou true, selon comment ACF le stocke (souvent '1' en DB)
            'compare' => '=' // ou 'LIKE' si doute
        ]
    ],
    'number' => 1
]);

if ( ! empty($terms) && ! is_wp_error($terms) ) {
    $active_wave = $terms[0];
    
    // On récupère la date de clôture pour l'affichage (Bonus UX)
    $raw_date = get_field('vague_date_cloture', 'projet_vague_' . $active_wave->term_id);
    if ($raw_date) {
        // Supposons que le format ACF soit Ymd, on le passe en d/m/Y
        $date_obj = DateTime::createFromFormat('Ymd', $raw_date);
        if ($date_obj) {
            $closing_date_display = 'Jusqu\'au ' . $date_obj->format('d/m/Y');
        }
    }
}
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
                Plateforme des appels à projets
            </h1>
            <p class="text-lg text-gray-600">
                Connectez-vous et gérez vos candidatures aux appels à projets de la MSH Paris-Saclay ou <a href="/inscription/" class="text-blue-600 hover:underline">inscrivez-vous</a> pour créer un compte.
            </p>
        </div>
        <div class="mb-6 text-center">   
            <?php if ( $active_wave ) : ?>
                <div class="inline-flex flex-col items-start gap-1">
                    <div class="inline-flex items-center bg-green-50 border border-green-100 rounded-full px-4 py-2 text-sm text-green-700">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="font-medium">Appel à projets en cours : <?php echo esc_html( $active_wave->name ); ?></span>
                    </div>
                    <?php if($closing_date_display): ?>
                        <span class="text-xs text-gray-400 ml-4 pl-2 border-l-2 border-gray-100">
                            <?php echo esc_html($closing_date_display); ?>
                        </span>
                    <?php endif; ?>
                </div>

            <?php else : ?>
                <div class="inline-flex items-center bg-red-50 border border-red-100 rounded-full px-4 py-2 text-sm text-red-600">
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                    Aucun appel à projet en cours
                </div>
            <?php endif; ?>
        </div>
        <?php echo do_shortcode($shortcode); ?>
        </div>

    </div>

</main>

<?php
get_footer('front');
?>