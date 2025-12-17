<?php
/**
 * The footer for the App.
 *
 * @package msh-webaap
 */
?>

<footer class="w-full pb-6 mt-auto text-center z-0">
    <div class="max-w-screen-xl mx-auto px-4">
        <nav class="mb-2">
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
        <div class="text-xs text-gray-400">
            <p>
                &copy; <?php echo date('Y'); ?> 
                <span class="font-semibold text-gray-500"><?php bloginfo('name'); ?></span>. 
                Tous droits réservés.
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>

	