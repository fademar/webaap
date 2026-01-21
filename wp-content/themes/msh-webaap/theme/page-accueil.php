<?php
/**
 * Template Name: MSH - Accueil
 */

get_header('front');

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

<main class="flex-grow flex items-center justify-center p-4 font-sans">
    
    <div class="bg-white w-full max-w-5xl h-auto md:h-[600px] rounded-[2.5rem] shadow-sm overflow-hidden flex flex-col md:flex-row">
        
        <div class="w-full md:w-1/2 relative overflow-hidden flex flex-col bg-primaire justify-center p-12 text-white">
            <div class="relative z-10">
                <img src="/wp-content/uploads/2026/01/logo-MSH2025-blanc.png" alt="MSH Paris-Saclay" class="w-1/2 mb-8">
                <p class="text-lg">Gérez vos candidatures aux appels à projets de la MSH Paris-Saclay.</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 bg-white p-10 md:p-14 flex flex-col justify-center">
            
            <div class="mb-6">
                
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