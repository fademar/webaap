<?php
/**
 * Topbar moderne pour l'application
 * 
 * Arguments acceptés via $args:
 * - 'active_page' (string) : slug de la page active pour déterminer le contexte
 * - 'title' (string) : titre personnalisé (optionnel)
 * 
 * @package msh-webaap
 */

$current_user = wp_get_current_user();
$is_msh = current_user_can('edit_others_posts') 
    || in_array('msh_member', (array) $current_user->roles, true) 
    || in_array('administrator', (array) $current_user->roles, true);

$args = $args ?? [];
$active_page = $args['active_page'] ?? '';
$custom_title = $args['title'] ?? '';

$menu_items = [];
if ( $is_msh ) {
    $menu_items[] = [
        'label' => 'Tous les projets',
        'url'   => home_url( '/dashboard/' ),
        'slug'  => 'tous-les-projets',
    ];
} else {
    $menu_items[] = [
        'label'   => 'Nouveau projet',
        'url'     => home_url( '/nouveau-projet/' ),
        'slug'    => 'nouveau-projet',
        'primary' => true,
        'icon'    => 'fa-regular fa-file',
    ];
    $menu_items[] = [
        'label' => 'Mes projets',
        'url'   => home_url( '/mes-projets/' ),
        'slug'  => 'mes-projets',
        'icon'  => 'fa-regular fa-folder-open',
    ];
}
?>

<!-- Topbar moderne minimaliste -->
<nav class="max-w-[1400px] mx-auto sticky top-2 z-50 bg-white/80 backdrop-blur-xl border border-gray-200/60 rounded-2xl">
    <div class="max-w-[1400px] mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo + Titre -->
            <div class="flex items-center gap-6">
                <a data-unsaved-exit href="<?php echo home_url('/'); ?>" class="flex items-center gap-3 group border-r border-gray-200 pr-6">
                    <div class="h-12 w-auto flex-shrink-0 transition-transform group-hover:scale-105">
                        <?php 
                        $logo_path = get_template_directory() . '/assets/logo-msh.svg';
                        if (file_exists($logo_path)) {
                            $svg_content = file_get_contents($logo_path);
                            // Ajouter des classes pour que le SVG prenne toute la taille et soit responsive
                            $svg_content = str_replace('<svg', '<svg class="h-12 w-auto"', $svg_content);
                            echo $svg_content;
                        }
                        ?>
                    </div>
                    <span class="text-lg font-semibold text-gray-900 hidden md:inline">web<span class="text-primaire">aap</span></span>
                </a>

                <!-- Menu -->
                <div class="hidden lg:flex items-center gap-2">
                    <?php foreach ( $menu_items as $item ) : ?>
                        <?php
                        $is_active  = ( $active_page !== '' && $active_page === $item['slug'] );

                        // Navigation sobre : gris léger par défaut, gris un peu plus fort pour l'actif.
                        $base  = 'inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors border';
                        $class = $is_active
                            ? $base . ' bg-gray-200/70 text-gray-900 border-gray-400/70 shadow-sm'
                            : $base . ' bg-gray-50 text-gray-700 border-gray-200/60 hover:bg-gray-100';

                        $icon_class = isset( $item['icon'] ) ? (string) $item['icon'] : '';
                        ?>
                        <a data-unsaved-exit href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo esc_attr( $class ); ?>">
                            <?php if ( $icon_class !== '' ) : ?>
                                <i class="<?php echo esc_attr( $icon_class ); ?> mr-2 text-gray-500"></i>
                            <?php endif; ?>
                            <?php echo esc_html( $item['label'] ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actions + User -->
            <div class="flex items-center gap-3">
                <!-- User menu -->
                <div class="flex items-center gap-3 pl-3 border-l border-gray-200">
                    <div class="hidden md:flex flex-col items-end">
                        <span class="text-sm font-medium text-gray-900"><?php echo esc_html($current_user->display_name); ?></span>
                        <span class="text-xs text-gray-500"><?php echo $is_msh ? 'Équipe MSH' : 'Candidat'; ?></span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-primaire flex items-center justify-center text-white text-sm font-bold shadow-sm">
                        <?php echo strtoupper(substr($current_user->display_name, 0, 1)); ?>
                    </div>
                    <a data-unsaved-exit href="<?php echo wp_logout_url(home_url()); ?>" 
                       class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                       title="Déconnexion">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
