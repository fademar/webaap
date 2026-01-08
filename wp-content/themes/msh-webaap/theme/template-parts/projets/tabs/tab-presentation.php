<?php 
$status = get_post_status();
$statuses = function_exists('mshps_aap_projet_custom_statuses') ? mshps_aap_projet_custom_statuses() : [];
$status_label = $statuses[$status] ?? $status;

// Services sollicités  
$services = [];

// Communication (select)
$kit_com = get_field('cand_kit_com'); // ex: 'cible' ou 'complet'
if (in_array($kit_com, ['cible', 'complet'], true)) {
  $services[] = 'Communication';
}

// Édition (true/false ou boolean)
if ((int) get_field('cand_edition') === 1) {
  $services[] = 'Édition';
}

// Plateformes (true/false ou boolean)
if ((int) get_field('cand_plateformes') === 1) {
  $services[] = 'Plateformes';
}

/**
 * Team table
 * - proj_porteur : array (Group)
 * - proj_porteurs : array (Repeater rows)
 */

 $porteur  = get_field('proj_porteur');   // group
 $porteurs = get_field('proj_porteurs');  // repeater rows (array)
 
 $rows = [];
 
 // Ajout porteur principal (si présent)
 if (is_array($porteur) && !empty($porteur)) {
   $rows[] = [
     'nom'          => $porteur['nom'] ?? '',
     'prenom'       => $porteur['prenom'] ?? '',
     'email'        => $porteur['email'] ?? '',
     'laboratoire'  => $porteur['laboratoire'] ?? '',
     'etablissement'=> $porteur['etablissement'] ?? '',
     'cv_pdf'       => $porteur['cv'] ?? null, // file
   ];
 }
 
 // Ajout co-porteurs (repeater)
 if (is_array($porteurs)) {
   foreach ($porteurs as $p) {
     if (!is_array($p)) continue;
 
     $rows[] = [
       'nom'          => $p['nom'] ?? '',
       'prenom'       => $p['prenom'] ?? '',
       'email'        => $p['email'] ?? '',
       'laboratoire'  => $p['laboratoire'] ?? '',
       'etablissement'=> $p['etablissement'] ?? '',
       'cv_pdf'       => $p['cv'] ?? null,
     ];
   }
 }
 
 // Helper: normalise un champ "file" ACF (array ou URL ou ID)
 function msh_cv_url($file) {
   if (!$file) return '';
   if (is_array($file) && !empty($file['url'])) return $file['url'];
   if (is_string($file)) return $file; // déjà une URL
   if (is_int($file)) {
     $url = wp_get_attachment_url($file);
     return $url ? $url : '';
   }
   return '';
 }

?>


<section class="flex flex-col gap-8 mt-6">
    <!-- propriétés -->
    <div class="flex flex-row items-left justify-between border border-primaire/10 rounded-xl bg-primaire/6 p-4">
        <div class="flex flex-col">
            <span class="text-slate-900 text-xs uppercase">Référence</span>
            <span class="font-medium text-slate-900"><?php echo get_field('proj_ref'); ?></span>
        </div>
        <div class="flex flex-col">
            <span class="text-slate-900 text-xs uppercase">Vague</span>
            <span class="font-medium text-slate-900">
            <?php 
                $terms = get_the_terms( get_the_ID(), 'projet_vague' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $vague = reset( $terms );
                    if ( isset( $vague->name ) ) {
                        echo esc_html( $vague->name );
                    }
                } 
            ?>
            </span>
        </div>
        <div class="flex flex-col">
            <span class="text-slate-900 text-xs uppercase">Type de projet</span>
            <span class="font-medium text-slate-900">
            <?php 
                $terms = get_the_terms( get_the_ID(), 'projet_type' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $ptype = reset( $terms );
                    if ( isset( $ptype->name ) ) {
                        echo esc_html( $ptype->name );
                    }
                }
            ?>
            </span>
        </div>
        <div class="flex flex-col">
            <span class="text-slate-900 text-xs uppercase">État</span>
            <span class="font-medium text-slate-900">
                <?php echo $status_label; ?>
            </span>
        </div>
        <div class="flex flex-col">
            <span class="text-slate-900 text-xs uppercase">Services sollicités</span>    
            <?php if (!empty($services)): ?>
                <span class="font-medium text-slate-900"><?php echo esc_html(implode(', ', $services)); ?></span>
            <?php else: ?>
                <span class="font-medium text-slate-900 italic">Aucun</span>
            <?php endif; ?>
        </div>
    </div>
    <!-- équipe -->
    <div>
        <h3 class="msh-label">Équipe</h3>
        <?php if (!empty($rows)): ?>
            <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Nom</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Prénom</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Email</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Laboratoire</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">Établissement</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-slate-900">CV</th>
                    </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $cv_url = msh_cv_url($r['cv_pdf']);
                        $full_name = trim(($r['prenom'] ?? '') . ' ' . ($r['nom'] ?? ''));
                        ?>
                        <tr class="hover:bg-slate-50">

                        <td class="whitespace-nowrap px-4 py-3 text-slate-900 uppercase">
                            <?php echo esc_html($r['nom']); ?>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-slate-900">
                            <?php echo esc_html($r['prenom']); ?>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3">
                            <?php if (!empty($r['email'])): ?>
                            <a class="text-slate-900 underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900"
                                href="<?php echo esc_attr('mailto:' . $r['email']); ?>">
                                <?php echo esc_html($r['email']); ?>
                            </a>
                            <?php else: ?>
                            <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-4 py-3 text-slate-900">
                            <?php echo !empty($r['laboratoire']) ? esc_html($r['laboratoire']) : '<span class="text-slate-400">—</span>'; ?>
                        </td>

                        <td class="px-4 py-3 text-slate-900">
                            <?php echo !empty($r['etablissement']) ? esc_html($r['etablissement']) : '<span class="text-slate-400">—</span>'; ?>
                        </td>

                        <td class="whitespace-nowrap px-4 py-3 text-center">
                        <?php if ($cv_url): ?>
                            <a
                            href="<?php echo esc_url($cv_url); ?>"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center text-slate-500 hover:text-slate-900"
                            title="<?php echo esc_attr('Télécharger le CV (PDF)'); ?>"
                            aria-label="<?php echo esc_attr('Télécharger le CV (PDF)'); ?>"
                            >
                            <i class="fa-solid fa-file-pdf text-lg"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-slate-300">—</span>
                        <?php endif; ?>
                        </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <!-- disciplines -->
    <div>
        <h3 class="msh-label">Disciplines</h3>
        <?php $disciplines = get_the_terms(get_the_ID(), 'projet_discipline'); ?>
        <?php if (!empty($disciplines)): ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($disciplines as $discipline): ?>
                    <span class="msh-value-pill"><?php echo $discipline->name; ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- mots-clés -->
    <div>
        <h3 class="msh-label">Mots-clés</h3>
        <?php $mots_cles = get_the_terms(get_the_ID(), 'projet_mot_cle'); ?>
        <?php if (!empty($mots_cles)): ?>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($mots_cles as $mot_cle): ?>
                    <span class="msh-value-pill"><?php echo $mot_cle->name; ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- résumé court -->
    <div>
        <h3 class="msh-label">Résumé court</h3>
        <div class="prose prose-slate">
            <?php echo get_field('proj_resume_court'); ?>
        </div>
    </div>



    <!-- argumentaire scientifique -->
    <?php
        // 1. Récupération du type de projet (Taxonomie)
        $terms = get_the_terms( get_the_ID(), 'projet_type' );
        $ptype = ( $terms && ! is_wp_error( $terms ) ) ? reset( $terms )->slug : '';

        // 2. Configuration des sections : [ 'Nom du champ ACF' => 'Titre à afficher' ]
        // On commence par les champs communs à tous les projets
        $sections = [
            'proj_objectifs' => 'Objectifs et hypothèses de recherche',
        ];

        // 3. Logique conditionnelle selon le type
        if ( in_array( $ptype, ['em', 'ma'], true ) ) {
            // Projets Émergence / Maturation
            $sections['proj_methodologie'] = 'Méthodologie';
            $sections['proj_etat_art']     = "État de l'art";

        } elseif ( in_array( $ptype, ['ws', 'se'], true ) ) {
            // Projets Workshop / Séminaire
            // "Public visé" remplace la méthodo. 
            // (Vérifie bien que le nom de ton champ ACF est 'proj_public' ou ajuste-le ici)
            $sections['proj_public_vise'] = 'Public visé'; 
        }

        // On ajoute les autres champs communs à la fin
        $sections['proj_interdisciplinarite'] = 'Dimension interdisciplinaire';
        $sections['proj_partenariat']         = 'Partenariat inter-institutionnel';


        // 4. Boucle d'affichage unique
        foreach ( $sections as $field_name => $label ) : 
            
            $content = get_field( $field_name );

            // Si le champ est vide, on passe au suivant sans rien afficher
            if ( ! $content ) continue; 
            ?>

            <div class="mb-8"> 
                <h3 class="text-sm font-bold text-slate-600 uppercase mb-2">
                    <?php echo esc_html( $label ); ?>
                </h3>
                
                <div class="prose prose-slate max-w-none">
                    <?php 
                    // Sécurisation et formatage
                    $content = wp_kses_post( $content );
                    echo apply_filters( 'the_content', $content ); 
                    ?>
                </div>
            </div>

        <?php endforeach; ?>
    
</section>

<?php msh_render_notes_module( get_the_ID(), 'presentation' ); ?>