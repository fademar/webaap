<?php
/**
 * Sidebar de navigation de l'application.
 * Récupère l'argument 'active_page' passé lors de l'appel.
 */

$current_user = wp_get_current_user();
$is_admin     = current_user_can('edit_others_posts'); // Admin + équipe (msh_member)
$active_slug  = $args['active_page'] ?? 'dashboard'; // Défaut : dashboard

// Définition des menus
// $menu_items = [
//     [
//         'label' => 'Tableau de bord',
//         'url'   => home_url('/dashboard'),
//         'slug'  => 'dashboard',
//         'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>'
//     ]
// ];

// Menus CANDIDAT (si pas admin)
if ( ! $is_admin ) {
    $menu_items[] = [
        'label' => 'Mes projets',
        'url'   => home_url('/mes-projets'),
        'slug'  => 'mes-projets',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'
    ];
    $menu_items[] = [
        'label' => 'Nouveau projet',
        'url'   => home_url('/nouveau-projet'), // Assurez-vous d'avoir créé cette page
        'slug'  => 'nouveau-projet',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'
    ];
}

// Menus ADMIN
if ( $is_admin ) {
    $menu_items[] = [
        'label' => 'Tous les projets',
        'url'   => home_url('/dashboard/'),
        'slug'  => 'tous-les-projets',
        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>'
    ];
}
?>

<aside
    data-sidebar
    data-sidebar-expanded-width="16rem"
    data-sidebar-collapsed-width="5rem"
    class="sticky top-3 hidden md:flex h-[calc(100vh-1.5rem)] flex-col z-50
           w-20 lg:w-24 xl:w-64 m-3 xl:m-4 transition-[width] duration-200 overflow-visible"
>
    <!-- Handle flottant (toujours visible, joli sur laptop) -->
    <button
        type="button"
        data-sidebar-toggle
        class="absolute -right-3 top-1/2 -translate-y-1/2 inline-flex items-center justify-center w-10 h-10 rounded-2xl
               bg-white border border-gray-200 shadow-md text-gray-700 hover:text-gray-900 hover:shadow-lg hover:border-gray-300
               focus:outline-none focus:ring-2 focus:ring-primaire/30 focus:border-primaire transition"
        title="Réduire / agrandir la navigation"
        aria-label="Réduire / agrandir la navigation"
    >
        <i data-sidebar-toggle-icon class="fa-solid fa-chevron-left"></i>
    </button>

    <div class="h-full flex flex-col bg-white rounded-3xl shadow-sm overflow-hidden">
        <div class="px-4 py-5 xl:p-8 flex items-center justify-center border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primaire rounded-xl flex items-center justify-center text-white font-bold shadow-sm">M</div>
                <span data-sidebar-hide-on-collapse class="hidden xl:inline font-bold text-lg tracking-tight text-gray-800">MSH <span class="text-blue-600">Portal</span></span>
            </div>
        </div>


    <nav data-sidebar-nav class="flex-1 overflow-y-auto py-4 xl:py-6 px-2 xl:px-4 space-y-2">
        <?php foreach ($menu_items as $item): 
            $is_active = ($active_slug === $item['slug']);
            $class = $is_active ? 'bg-primaire text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 hover:text-primaire';
        ?>
            <a
                href="<?php echo esc_url($item['url']); ?>"
                title="<?php echo esc_attr($item['label']); ?>"
                data-sidebar-link
                class="flex items-center justify-center xl:justify-start gap-3 px-3 xl:px-4 py-3 rounded-2xl transition-all <?php echo $class; ?>"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?php echo $item['icon']; ?></svg>
                <span data-sidebar-hide-on-collapse class="hidden xl:inline font-medium"><?php echo esc_html($item['label']); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

     <div class="flex flex-col p-3 xl:p-4 mt-auto">
        <div class="flex items-center gap-3 p-2 border-y border-gray-100">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold overflow-hidden text-gray-600 text-sm">
                <?php echo strtoupper(substr($current_user->user_firstname, 0, 1) . substr($current_user->user_lastname, 0, 1)); ?>
            </div>
            <div data-sidebar-show-expanded class="hidden xl:block flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate"><?php echo esc_html($current_user->display_name); ?></p>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="text-xs text-red-500 hover:text-red-700 font-medium">Déconnexion</a>
            </div>
            <a data-sidebar-show-collapsed href="<?php echo wp_logout_url(home_url()); ?>" class="xl:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-red-600 hover:bg-red-50" title="Déconnexion" aria-label="Déconnexion">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
            </a>
        </div>

    <nav data-sidebar-hide-on-collapse class="hidden xl:block flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <?php
            if ( has_nav_menu( 'footer' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    // Classes Tailwind pour centrer et styliser les liens
                    'menu_class'     => 'flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-gray-500 font-medium dark:text-gray-400',
                    'depth'          => 1,
                    // Astuce : permet d'ajouter des classes aux <li> sans plugin
                    'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>', 
                ));
            } else {
                // FALLBACK (Si aucun menu n'est assigné)
                echo '<ul class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-xs text-gray-500 font-medium dark:text-gray-400">';
                echo '<li><a href="#" class="hover:text-blue-600 transition-colors">Mentions légales</a></li>';
                echo '<li><a href="#" class="hover:text-blue-600 transition-colors">Politique de confidentialité</a></li>';
                echo '<li><a href="#" class="hover:text-blue-600 transition-colors">Contact</a></li>';
                echo '</ul>';
            }
            ?>
        </nav>
            </div>
    </div>
</aside>