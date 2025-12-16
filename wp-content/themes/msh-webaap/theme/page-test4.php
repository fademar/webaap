<?php
/**
 * Template Name: MSH - Test Design 4 (Topbar minimaliste)
 *
 * Page de test pour explorer un design plus fluide :
 * - pas de sidebar
 * - topbar minimaliste
 * - moins de "cards", plus de sections et séparateurs légers
 *
 * @package msh-webaap
 */

if ( ! is_user_logged_in() ) {
    auth_redirect();
}

get_header( 'app' );

$current_user = wp_get_current_user();
$roles        = is_array( $current_user->roles ?? null ) ? $current_user->roles : [];
$is_msh_team  = current_user_can( 'edit_others_posts' )
    || current_user_can( 'manage_options' )
    || in_array( 'msh_member', $roles, true )
    || in_array( 'administrator', $roles, true );

$logo_url = get_template_directory_uri() . '/assets/logo-msh.svg';

// CTA selon rôle (simple)
$primary = $is_msh_team
    ? [ 'label' => 'Tous les projets', 'url' => home_url( '/dashboard/' ) ]
    : [ 'label' => 'Nouveau projet', 'url' => home_url( '/nouveau-projet/' ) ];
?>

<main id="app-main-content" class="flex-1 px-4 sm:px-6 lg:px-8 py-4 lg:py-6 overflow-y-auto h-screen">

    <!-- Topbar minimaliste -->
    <div class="sticky top-0 z-40 -mx-4 sm:-mx-6 lg:-mx-8 mb-6 bg-gray-100/80 backdrop-blur">
        <div class="px-4 sm:px-6 lg:px-8 py-2">
            <div class="flex items-center justify-between gap-3">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3 min-w-0">
                    <img src="<?php echo esc_url( $logo_url ); ?>" class="h-7 w-auto" alt="MSH Paris-Saclay" />
                    <span class="text-sm font-semibold text-gray-900 truncate">
                        <?php echo esc_html( $is_msh_team ? 'Backoffice — Projets' : 'Mes projets' ); ?>
                    </span>
                </a>

                <div class="flex items-center gap-2">
                    <a href="<?php echo esc_url( $primary['url'] ); ?>"
                       class="hidden sm:inline-flex items-center px-3 py-2 rounded-lg bg-primaire text-white text-sm font-medium hover:opacity-90 transition">
                        <?php echo esc_html( $primary['label'] ); ?>
                    </a>

                    <div class="hidden md:flex items-center gap-2 text-sm text-gray-600">
                        <span class="font-medium text-gray-800"><?php echo esc_html( $current_user->display_name ); ?></span>
                    </div>

                    <a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:text-red-600 hover:bg-white transition"
                       title="Déconnexion" aria-label="Déconnexion">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-b border-gray-200"></div>
    </div>

    <!-- Header de page (fluide, sans “card”) -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            <?php echo esc_html( $is_msh_team ? 'Tous les projets' : 'Mes projets' ); ?>
        </h1>
        <p class="mt-1 text-sm text-gray-600 max-w-3xl">
            <?php if ( $is_msh_team ) : ?>
                Filtrez et ouvrez les dossiers. Design test : moins de boîtes, plus de largeur utile.
            <?php else : ?>
                Retrouvez vos brouillons et candidatures. Design test : navigation légère, lecture confortable.
            <?php endif; ?>
        </p>
    </div>

    <!-- Barre d’outils (sans card) -->
    <section class="mb-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 w-full">
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vague</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>26-1</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>Tous</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>Tous</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Communication</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>Tous</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Édition</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>Tous</option>
                    </select>
                </div>
                <div class="min-w-0">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Plateformes</label>
                    <select class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire">
                        <option>Tous</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 justify-end">
                <div class="relative w-full lg:w-72">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="search" placeholder="Rechercher…"
                           class="w-full rounded-lg border border-gray-200 bg-white pl-9 pr-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-primaire/20 focus:border-primaire" />
                </div>
            </div>
        </div>
    </section>

    <!-- Tableau (minimal chrome) -->
    <section class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/60">
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3">Statut</th>
                        <th class="px-5 py-3">Référence</th>
                        <th class="px-5 py-3">Titre</th>
                        <th class="px-5 py-3">Porteur</th>
                        <th class="px-5 py-3">Dépôt</th>
                        <th class="px-5 py-3 text-center">Services</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50/60">
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">En instruction</span>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-900 font-mono">26-1-EM-01</td>
                        <td class="px-5 py-4 text-sm text-gray-900 max-w-xl">
                            <div class="font-medium">Algorithmes de la Mémoire</div>
                            <div class="text-xs text-gray-500 mt-1 line-clamp-2">Cartographie sensible des quartiers populaires via l’IA</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-900">
                            <div class="font-medium">F. Demarthon</div>
                            <div class="text-xs text-gray-500">fdemarthon@gmail.com</div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700 whitespace-nowrap">12/12/2025<br><span class="text-xs text-gray-500">14:18</span></td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-2 text-gray-400">
                                <i class="fa-solid fa-bullhorn" title="Communication"></i>
                                <i class="fa-solid fa-book" title="Édition"></i>
                                <i class="fa-solid fa-server" title="Plateformes"></i>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="#" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition" title="Voir">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 text-xs text-gray-500 bg-gray-50/60 border-t border-gray-100">
            Design test : remplace ce tableau mock par DataTables une fois validé.
        </div>
    </section>

    <div class="mt-8 text-xs text-gray-400">
        &copy; <?php echo esc_html( date( 'Y' ) ); ?> MSH Paris-Saclay
    </div>

</main>

<?php get_footer( 'app' ); ?>


