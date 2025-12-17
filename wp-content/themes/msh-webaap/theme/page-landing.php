<?php
/**
 * Template Name: MSH - Landing
 */

 get_header('test');
?>

<div class="min-h-screen bg-slate-50 flex flex-col">

  <!-- Topbar (globale, sobre) -->
  <header class="border-b bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-screen-xl px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3 min-w-0">
        <!-- Logo -->
        <div class="h-9 w-9 rounded-lg bg-slate-100"></div>

        <div class="min-w-0">
          <div class="text-sm font-semibold text-slate-900 truncate">
            MSH Paris-Saclay
          </div>
          <div class="text-xs text-slate-500 truncate">
            Appels à projets — Espace candidat
          </div>
        </div>
      </div>

      <!-- Liens simples -->
      <nav class="flex items-center gap-2">
        <a href="#" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
          Aide
        </a>
        <span class="text-slate-300">•</span>
        <a href="#" class="text-sm font-semibold text-slate-600 hover:text-slate-900">
          Contact
        </a>
      </nav>
    </div>
  </header>

  <!-- Contenu -->
  <main class="flex-1">
    <div class="mx-auto max-w-screen-xl px-6 py-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Colonne gauche : pitch + rassurance -->
        <section class="lg:col-span-6">
          <div class="bg-white border rounded-2xl p-8">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
              Connexion à l’espace candidat
            </h1>
            <p class="mt-3 text-slate-600 leading-relaxed">
              Déposez et suivez vos demandes de labellisation, complétez vos informations,
              et échangez avec les services de la MSH (communication, édition, plateformes).
            </p>

            <ul class="mt-6 space-y-3 text-sm text-slate-700">
              <li class="flex gap-3">
                <span class="mt-0.5 h-6 w-6 rounded-lg bg-slate-100 inline-flex items-center justify-center">✓</span>
                <span>
                  Accès à vos <strong>dossiers</strong> et à vos <strong>brouillons</strong>.
                </span>
              </li>
              <li class="flex gap-3">
                <span class="mt-0.5 h-6 w-6 rounded-lg bg-slate-100 inline-flex items-center justify-center">✓</span>
                <span>
                  Possibilité de <strong>reprendre</strong> une soumission et d’ajouter des pièces jointes.
                </span>
              </li>
              <li class="flex gap-3">
                <span class="mt-0.5 h-6 w-6 rounded-lg bg-slate-100 inline-flex items-center justify-center">✓</span>
                <span>
                  Suivi par onglets correspondant aux <strong>services de la MSH</strong>.
                </span>
              </li>
            </ul>

            <!-- Bloc info / rassurance -->
            <div class="mt-6 rounded-xl border bg-slate-50 p-4 text-sm text-slate-700">
              <p class="font-semibold text-slate-900 mb-1">Première connexion ?</p>
              <p class="text-slate-600">
                Créez un compte avec votre adresse institutionnelle. Votre inscription peut être soumise
                à validation par nos services.
              </p>
            </div>
          </div>

          <!-- Liens bas -->
          <div class="mt-4 text-xs text-slate-500">
            En vous connectant, vous acceptez la politique de confidentialité et les conditions d’utilisation.
          </div>
        </section>

        <!-- Colonne droite : formulaire -->
        <section class="lg:col-span-6">
          <div class="bg-white border rounded-2xl p-8">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Se connecter</h2>
                <p class="text-sm text-slate-600 mt-1">
                  Utilisez l’adresse e-mail associée à votre compte.
                </p>
              </div>

              <!-- Optionnel : lien inscription -->
              <a href="/inscription/"
                 class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                Créer un compte
              </a>
            </div>

            <!-- Zone WS Form -->
            <div class="mt-6">
              <!-- Remplace par ton shortcode WS Form -->
              <!-- Exemple : <?php echo do_shortcode('[ws_form id="XX"]'); ?> -->
              <div class="rounded-xl border border-dashed p-6 text-sm text-slate-500">
                Ici : shortcode WS Form (formulaire Login)
              </div>
            </div>

            <!-- Aides sous le formulaire -->
            <div class="mt-6 flex flex-wrap items-center justify-between gap-2 text-sm">
              <a href="/mot-de-passe-oublie/" class="font-semibold text-slate-600 hover:text-slate-900">
                Mot de passe oublié ?
              </a>
              <a href="/contact/" class="font-semibold text-slate-600 hover:text-slate-900">
                Problème de connexion ?
              </a>
            </div>
          </div>

          <!-- Note sécurité -->
          <div class="mt-4 rounded-xl border bg-white p-4 text-xs text-slate-600">
            <span class="font-semibold text-slate-900">Sécurité :</span>
            n’utilisez pas de mot de passe partagé et déconnectez-vous après usage sur un poste public.
          </div>
        </section>

      </div>
    </div>
  </main>

  <!-- Footer minimal -->
  <footer class="border-t bg-white">
    <div class="mx-auto max-w-screen-xl px-6 py-6 text-xs text-slate-500 flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
      <div>© <span class="font-semibold text-slate-700">MSH Paris-Saclay</span></div>
      <div class="flex gap-4">
        <a href="#" class="hover:text-slate-900">Mentions légales</a>
        <a href="#" class="hover:text-slate-900">Confidentialité</a>
        <a href="#" class="hover:text-slate-900">Contact</a>
      </div>
    </div>
  </footer>

</div>

<?php get_footer('test'); ?>