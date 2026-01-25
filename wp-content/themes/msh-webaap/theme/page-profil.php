<?php
/**
 * Template Name: MSH - Profil Utilisateur
 */

// Sécurité : Redirection si non connecté
if ( ! is_user_logged_in() ) {
    wp_redirect( home_url('/connexion') );
    exit;
}

$current_user = wp_get_current_user();
$is_msh = current_user_can('edit_others_posts') 
    || in_array('msh_member', (array) $current_user->roles, true) 
    || in_array('administrator', (array) $current_user->roles, true);

// 1. DÉFINITION DE LA RACINE
$root_url   = $is_msh ? home_url( '/dashboard/' ) : home_url( '/mes-projets/' );

// 1. Définition des options de statut
$statuts_disponibles = [
    'enseignant-chercheur' => 'Enseignant·e-chercheur·se',
    'chercheur'            => 'Chercheur·se',
    'ingenieur'            => 'Ingénieur·e',
    'post-doctorant'       => 'Post-doctorant·e',
    'doctorant'            => 'Doctorant·e'
];

// 2. Récupération de la valeur actuelle en base
// J'utilise 'msh_statut' comme clé pour être cohérent avec 'msh_laboratoire'
$current_statut = get_user_meta( $current_user->ID, 'statut', true );

get_header('app');

?>

<div class="container mx-auto px-4 py-12 max-w-4xl">

    <?php
    get_template_part( 'template-parts/layout/page-header', null, [
        'title'    => 'Mon Profil',
        'subtitle' => 'Gérez vos informations personnelles et votre sécurité.',
        'actions'  => [
            [ 'label' => 'Retour au tableau de bord', 'url' => $root_url, 'class' => 'text-sm text-gray-500 hover:text-gray-900 underline' ],
        ],
    ] );
    ?>

    <?php if ( isset($_GET['msg']) ) : ?>
        <div class="mb-8 rounded-lg p-4 text-sm font-medium border
            <?php echo strpos($_GET['msg'], 'success') !== false ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'; ?>">
            <?php
            switch ($_GET['msg']) {
                case 'info_success': echo '✅ Vos informations ont été mises à jour.'; break;
                case 'pwd_success':  echo '🔒 Votre mot de passe a été modifié avec succès.'; break;
                case 'email_exists': echo '⚠️ Cet email est déjà utilisé par un autre compte.'; break;
                case 'email_invalid':echo '⚠️ Adresse email invalide.'; break;
                case 'pwd_wrong':    echo '⛔ L\'ancien mot de passe est incorrect.'; break;
                case 'pwd_mismatch': echo '⚠️ Les nouveaux mots de passe ne correspondent pas.'; break;
                case 'pwd_short':    echo '⚠️ Le mot de passe est trop court (6 caractères min).'; break;
                default:             echo 'Une erreur est survenue.';
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Mes informations</h2>
            </div>
            
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="msh_update_profile_info">
                <?php wp_nonce_field( 'msh_update_profile_info_action', 'msh_profile_nonce' ); ?>

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Prénom</label>
                    <input type="text" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" required
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nom</label>
                    <input type="text" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" required
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                    </div>

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Adresse Email</label>
                    <input type="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Laboratoire</label>
                    <input type="text" name="laboratoire" value="<?php echo esc_attr( $current_user->laboratoire ); ?>" required
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>
                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Établissement</label>
                    <input type="text" name="etablissement" value="<?php echo esc_attr( $current_user->etablissement ); ?>" required
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>
                <div>
                    <label for="statut" class="block text-sm font-medium text-gray-900">Statut</label>
                    <div class="mt-2 grid grid-cols-1">
                        <select id="statut" name="statut" required
                                class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                            
                            <option value="">Sélectionnez votre statut...</option>
                            
                            <?php foreach ( $statuts_disponibles as $value => $label ) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected( $current_statut, $value ); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                        
                        <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>


                <div class="pt-4">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition-colors font-medium">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden h-fit">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Changer le mot de passe</h2>
            </div>

            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="msh_update_profile_password">
                <?php wp_nonce_field( 'msh_update_profile_password_action', 'msh_pwd_nonce' ); ?>

                <input type="text" style="display:none">
                <input type="password" style="display:none">

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Mot de passe actuel</label>
                    <input type="password" name="current_pass" required
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>

                <hr class="border-gray-100 my-2">

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Nouveau mot de passe</label>
                    <input type="password" name="new_pass" required minlength="6"
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>

                <div>
                    <label class="block text-sm/6 font-medium text-gray-900 dark:text-white">Confirmer le nouveau</label>
                    <input type="password" name="confirm_pass" required minlength="6"
                           class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6 dark:bg-white/5 dark:text-white dark:outline-white/10 dark:placeholder:text-gray-500 dark:focus:outline-indigo-500">
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php get_footer('app'); ?>