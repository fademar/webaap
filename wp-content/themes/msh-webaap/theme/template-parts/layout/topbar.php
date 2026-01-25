<?php
/**
 * Topbar : Mode Fil d'ariane (Breadcrumb) + Menu Utilisateur
 * @package msh-webaap
 */

$current_user = wp_get_current_user();
$is_msh = current_user_can('edit_others_posts') 
    || in_array('msh_member', (array) $current_user->roles, true) 
    || in_array('administrator', (array) $current_user->roles, true);

// 1. DÉFINITION DE LA RACINE
$root_url   = $is_msh ? home_url( '/dashboard/' ) : home_url( '/mes-projets/' );
$root_label = $is_msh ? 'Tous les projets' : 'Mes projets';

// 2. DÉFINITION DE L'ENFANT
$current_label = '';
$is_root       = false;

if ( is_page('dashboard') || is_page('mes-projets') ) {
    $is_root = true;
} elseif ( is_singular('projet') ) {
    $ref = get_post_meta( get_the_ID(), 'proj_ref', true );
    $current_label = $ref ? $ref : get_the_title(); 
} elseif ( is_page() ) {
    $current_label = get_the_title();
}

// 3. PRÉPARATION DES DONNÉES UTILISATEUR
// On récupère l'initiale du prénom (priorité) ou du display_name
$name_for_initial = !empty($current_user->first_name) ? $current_user->first_name : $current_user->display_name;
$initial = strtoupper(substr($name_for_initial, 0, 1));
?>

<nav class="w-full mx-auto sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b transition-all duration-200">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <div class="flex items-center gap-4 flex-1 min-w-0"> 
                <a data-unsaved-exit href="<?php echo esc_url($root_url); ?>" class="flex items-center gap-3 group pr-4 shrink-0">
                    <div class="h-10 w-auto flex-shrink-0 transition-transform group-hover:scale-105 opacity-90 hover:opacity-100">
                        <?php 
                        $logo_path = get_template_directory() . '/assets/logo-msh.svg';
                        if (file_exists($logo_path)) {
                            // On injecte le SVG directement pour pouvoir le styliser si besoin
                            echo str_replace('<svg', '<svg class="h-10 w-auto"', file_get_contents($logo_path));
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
                
                <div class="hidden md:block h-8 w-px bg-gray-200 mx-2"></div>

                <div class="relative" id="user-menu-container">
                    
                    <button type="button" 
                            id="user-menu-button"
                            class="flex items-center gap-3 focus:outline-none group p-1 rounded-full hover:bg-gray-50 transition-colors"
                            aria-expanded="false" 
                            aria-haspopup="true">
                        
                        <div class="hidden md:flex flex-col items-end mr-1">
                            <span class="text-sm font-medium text-gray-900 leading-none group-hover:text-black transition-colors">
                                <?php echo esc_html($current_user->display_name); ?>
                            </span>
                        </div>
                        
                        <div class="w-9 h-9 rounded-full bg-primaire flex items-center justify-center text-white text-sm font-bold shadow-sm ring-2 ring-white group-hover:ring-gray-200 transition-all">
                            <?php echo esc_html($initial); ?>
                        </div>

                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 group-hover:text-gray-600 transition-colors mr-1"></i>
                    </button>

                    <div id="user-dropdown-menu" 
                         class="hidden absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-black/5 focus:outline-none transform opacity-0 scale-95 transition-all duration-100 ease-out" 
                         role="menu" 
                         tabindex="-1">
                        
                        <div class="px-4 py-3 border-b border-gray-50">
                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Compte</p>
                            <p class="text-sm font-medium text-gray-900 truncate" title="<?php echo esc_attr($current_user->user_email); ?>">
                                <?php echo esc_html($current_user->user_email); ?>
                            </p>
                        </div>

                        <div class="py-1">
                            <a href="<?php echo home_url('/mon-profil/'); ?>" 
                               class="group flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors" 
                               role="menuitem">
                                <span class="flex items-center justify-center w-5 text-gray-400 group-hover:text-gray-600">
                                    <i class="fa-solid fa-user-gear"></i>
                                </span>
                                Modifier mon profil
                            </a>
                        </div>

                        <div class="py-1 border-t border-gray-50">
                            <a href="<?php echo wp_logout_url(home_url()); ?>" 
                               class="group flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors" 
                               role="menuitem">
                                <span class="flex items-center justify-center w-5 text-red-400 group-hover:text-red-500">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </span>
                                Se déconnecter
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('user-menu-container');
    const button    = document.getElementById('user-menu-button');
    const menu      = document.getElementById('user-dropdown-menu');
    const chevron   = button.querySelector('.fa-chevron-down');

    function toggleMenu() {
        const isClosed = menu.classList.contains('hidden');
        
        if (isClosed) {
            // Ouvrir
            menu.classList.remove('hidden');
            // Timeout pour permettre au navigateur de rendre la classe hidden avant d'animer
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
                if(chevron) chevron.classList.add('rotate-180');
            }, 10);
            button.setAttribute('aria-expanded', 'true');
        } else {
            // Fermer
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            if(chevron) chevron.classList.remove('rotate-180');
            
            // Attendre la fin de l'animation CSS (100ms) pour cacher
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 100);
            button.setAttribute('aria-expanded', 'false');
        }
    }

    // Clic bouton
    button.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu();
    });

    // Clic dehors
    document.addEventListener('click', (e) => {
        if (!container.contains(e.target) && !menu.classList.contains('hidden')) {
            // Forcer la fermeture
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            if(chevron) chevron.classList.remove('rotate-180');
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 100);
            button.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>