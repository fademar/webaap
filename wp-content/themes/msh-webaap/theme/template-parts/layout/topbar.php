<?php
/**
 * Topbar : Mode Fil d'ariane (Breadcrumb)
 * * @package msh-webaap
 */

$current_user = wp_get_current_user();
$is_msh = current_user_can('edit_others_posts') 
    || in_array('msh_member', (array) $current_user->roles, true) 
    || in_array('administrator', (array) $current_user->roles, true);

// 1. DÉFINITION DE LA RACINE (Le "Home" de l'app)
$root_url   = $is_msh ? home_url( '/dashboard/' ) : home_url( '/mes-projets/' );
$root_label = $is_msh ? 'Tous les projets' : 'Mes projets';

// 2. DÉFINITION DE L'ENFANT (La page actuelle)
$current_label = '';
$is_root       = false;

if ( is_page('dashboard') || is_page('mes-projets') ) {
    $is_root = true;
} elseif ( is_singular('projet') ) {
    // Si on est sur un projet, on affiche sa référence ou son titre
    $ref = get_post_meta( get_the_ID(), 'proj_ref', true );
    // On privilégie la référence courte, sinon le titre tronqué
    $current_label = $ref ? $ref : get_the_title(); 
} elseif ( is_page() ) {
    $current_label = get_the_title();
}
?>

<nav class="w-full mx-auto sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b transition-all duration-200">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <div class="flex items-center gap-4 flex-1 min-w-0"> <a data-unsaved-exit href="<?php echo esc_url($root_url); ?>" class="flex items-center gap-3 group pr-4 shrink-0">
                    <div class="h-10 w-auto flex-shrink-0 transition-transform group-hover:scale-105 opacity-90 hover:opacity-100">
                        <?php 
                        $logo_path = get_template_directory() . '/assets/logo-msh.svg';
                        if (file_exists($logo_path)) {
                            $svg_content = file_get_contents($logo_path);
                            $svg_content = str_replace('<svg', '<svg class="h-10 w-auto"', $svg_content);
                            echo $svg_content;
                        }
                        ?>
                    </div>
                </a>

                <div class="h-6 w-px bg-gray-200 shrink-0"></div>

                <nav class="flex items-center text-sm font-medium leading-none min-w-0 overflow-hidden" aria-label="Breadcrumb">
                    
                    <a href="<?php echo esc_url($root_url); ?>" 
                       class="<?php echo $is_root ? 'text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-900 transition-colors'; ?> whitespace-nowrap">
                        <?php echo esc_html($root_label); ?>
                    </a>

                    <?php if ( ! $is_root && $current_label ) : ?>
                        <i class="fa-solid fa-chevron-right text-[10px] text-gray-300 mx-3 shrink-0"></i>

                        <span class="text-gray-900 font-semibold truncate bg-gray-50 px-2 py-1 rounded-md border border-gray-200/50 max-w-[200px] md:max-w-[300px]">
                            <?php echo esc_html($current_label); ?>
                        </span>
                    <?php endif; ?>

                </nav>
            </div>

            <div class="flex items-center gap-3 shrink-0 pl-4">
                <div class="flex items-center gap-3 pl-3 border-l border-gray-200">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-sm font-medium text-gray-900 leading-none mb-1"><?php echo esc_html($current_user->display_name); ?></span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primaire flex items-center justify-center text-white text-sm font-bold shadow-sm ring-2 ring-white">
                        <?php echo strtoupper(substr($current_user->display_name, 0, 1)); ?>
                    </div>
                    <a data-unsaved-exit href="<?php echo wp_logout_url(home_url()); ?>" 
                       class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors ml-1"
                       title="Déconnexion">
                        <i class="fa-solid fa-power-off text-sm"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</nav>