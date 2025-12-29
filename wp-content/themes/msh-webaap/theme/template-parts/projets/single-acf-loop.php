<?php
/**
 * Boucle d'affichage dynamique.
 * Attend la variable $fields_to_display (array de slugs) et $all_fields (array d'objets ACF)
 * provenant du template parent.
 */

if ( empty( $fields_to_display ) || ! is_array( $fields_to_display ) ) {
    if ( empty( $all_fields ) ) {
        echo '<div class="p-6 text-sm text-gray-500 italic">Aucune donnée disponible.</div>';
    }
    return;
}

foreach ( $fields_to_display as $field_key ) :

    // On récupère l'objet field complet depuis notre gros tableau chargé au début
    $field = $all_fields[ $field_key ] ?? null;

    // Si le champ n'existe pas ou est vide (optionnel, selon si vous voulez afficher les champs vides)
    if ( ! $field ) continue;

    // --- LOGIQUE D'AFFICHAGE ---

    // 1. CAS SPÉCIAL : ÉQUIPE
    if ( $field_key === 'proj_porteur' ) {
        // On récupère aussi les co-porteurs pour les fusionner
        $porteur_data = $field['value'] ?? [];
        $coporteurs_field = $all_fields['proj_porteurs'] ?? null; // On va le chercher directement
        $coporteurs_data = $coporteurs_field['value'] ?? []; 

        // ... (VOTRE CODE DE TABLEAU D'ÉQUIPE EXISTANT ICI) ...
        // Je remets le début pour contexte :
        $team = [];
        if ( ! empty( $porteur_data ) ) {
             $team[] = [ 'role' => 'Porteur', 'nom' => $porteur_data['nom'] ?? '', 'prenom' => $porteur_data['prenom'] ?? '', 'email' => $porteur_data['email'] ?? '', 'laboratoire' => $porteur_data['laboratoire'] ?? '', 'etablissement' => $porteur_data['etablissement'] ?? '', 'cv' => $porteur_data['cv'] ?? '' ];
        }
        if ( is_array( $coporteurs_data ) ) {
            foreach ( $coporteurs_data as $co ) {
                $team[] = [ 'role' => 'Co-porteur', 'nom' => $co['nom'] ?? '', 'prenom' => $co['prenom'] ?? '', 'email' => $co['email'] ?? '', 'laboratoire' => $co['laboratoire'] ?? '', 'etablissement' => $co['etablissement'] ?? '', 'cv' => $co['cv'] ?? '' ];
            }
        }
        ?>
        <div class="p-5 border-b border-gray-100 last:border-0">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Équipe du projet</h3>
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Identité</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Laboratoire / Établissement</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">CV</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <?php foreach($team as $m): ?>
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-500"><?php echo esc_html($m['role']); ?></td>
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                <?php echo esc_html($m['nom'] . ' ' . $m['prenom']); ?>
                                <div class="text-xs text-gray-400"><?php echo esc_html($m['email']); ?></div>
                            </td>
                            <td class="px-3 py-2 text-sm text-gray-500">
                                <div><?php echo esc_html($m['laboratoire']); ?></div>
                                <div class="text-xs"><?php echo esc_html($m['etablissement']); ?></div>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <?php // (Logique CV) ... ?>
                                <i class="fa-solid fa-file-pdf text-gray-300"></i>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        continue;
    }

    // 2. AFFICHAGE STANDARD (Stacked Label)
    $label = $field['label'];
    
    // Petite astuce : Si c'est un champ de suivi (commence par 'suiv_'), on le met en valeur
    $is_suivi = str_starts_with($field_key, 'suiv_');
    $bg_class = $is_suivi ? 'bg-amber-50/50 hover:bg-amber-50' : 'hover:bg-gray-50/50';
    $label_class = $is_suivi ? 'text-amber-700' : 'text-gray-500';
    ?>
    
    <div class="p-5 border-b border-gray-100 last:border-0 transition-colors group <?php echo $bg_class; ?>">
        <div class="text-xs font-semibold <?php echo $label_class; ?> mb-2 flex items-center gap-2">
            <?php echo esc_html( $label ); ?>
            <?php if($is_suivi): ?>
                <span class="inline-flex items-center rounded-md bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20 ml-auto">Réservé MSH</span>
            <?php endif; ?>
        </div>
        <div class="text-sm text-gray-900 leading-relaxed">
            <?php echo mshps_render_acf_field_value( $field ); ?>
        </div>
    </div>

<?php endforeach; ?>