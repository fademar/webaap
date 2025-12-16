<?php
/**
 * Template Name: MSH - Test3
 */

get_header('test'); 
?>

<div class="min-h-screen bg-slate-50">

  <!-- TOPBAR (globale) -->
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-screen-xl px-6 py-3 flex items-center justify-between">
      <!-- Left: logo + page title -->
      <div class="flex items-center gap-3 min-w-0">
        <div class="h-8 w-8 rounded-lg bg-slate-100"></div>
        <div class="min-w-0">
          <div class="text-sm font-semibold text-slate-900 truncate">Tableau de bord</div>
        </div>
      </div>

      <!-- Right: back to list + user menu -->
      <div class="flex items-center gap-2 shrink-0">
        <a href="#"
           class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
          ← Liste des projets
        </a>

        <!-- User menu -->
        <div class="relative" data-user-menu>
          <button type="button"
                  class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300"
                  data-user-menu-button aria-haspopup="menu" aria-expanded="false">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-700">
              FD
            </span>
            <span class="hidden sm:block text-sm font-semibold text-slate-700">Fabrice</span>
            <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.7a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
            </svg>
          </button>

          <div class="absolute right-0 mt-2 w-56 origin-top-right rounded-xl border bg-white shadow-lg p-1 hidden"
               data-user-menu-dropdown role="menu" aria-label="Menu utilisateur">
            <div class="px-3 py-2">
              <div class="text-sm font-semibold text-slate-900">Fabrice Demarthon</div>
              <div class="text-xs text-slate-500">candidat</div>
            </div>
            <div class="my-1 border-t"></div>

            <a href="/mon-profil/"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
               role="menuitem">
              <span class="text-slate-500">👤</span>
              Mon profil
            </a>

            <a href="/se-deconnecter/"
               class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50"
               role="menuitem">
              <span>⎋</span>
              Se déconnecter
            </a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- SUBHEADER PROJET + ACTIONS + TABS (contextuel au projet) -->
  <section class="sticky top-[56px] z-40 bg-white shadow-sm">
    <div class="mx-auto max-w-screen-xl px-6 py-4">

      <!-- Ligne badges + méta -->
      <div class="flex flex-wrap items-center gap-2 text-sm mb-2">
        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
        Réf. 26–1–EM–01
        </span>
        <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-1 font-semibold text-violet-800">
          En instruction
        </span>
        <span class="text-slate-500">Déposé le 12/12/2025</span>
      </div>

      <!-- Titre + actions projet -->
      <div class="flex items-start justify-between gap-6">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-xl font-bold text-slate-900 truncate">
            Algorithmes de la Mémoire : Cartographie sensible des quartiers populaires via l’IA
          </h1>
          <p class="mt-0.5 text-md text-slate-600 line-clamp-1">
          ALGO-MEMO
          </p>
        </div>

        <!-- Actions du projet (dans le subheader) -->
        <div class="flex items-center gap-2 shrink-0">

          <!-- Dropdown Actions -->
          <div class="relative" data-actions-menu>
            <button type="button"
                    class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 inline-flex items-center gap-2"
                    data-actions-menu-button aria-haspopup="menu" aria-expanded="false">
              Actions
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.7a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/>
              </svg>
            </button>

            <div class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl border bg-white shadow-lg p-1 hidden"
                 data-actions-menu-dropdown role="menu" aria-label="Actions projet">
              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                Changer le statut…
              </button>
              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                Exporter le dossier (PDF)
              </button>
              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                Télécharger les pièces jointes
              </button>

              <div class="my-1 border-t"></div>

              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                Notifier le porteur
              </button>
              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50" role="menuitem">
                Demander des compléments
              </button>

              <div class="my-1 border-t"></div>

              <button class="w-full text-left rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50" role="menuitem">
                Archiver le projet
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <nav class="mt-6">
        <div class="flex gap-1 overflow-x-auto">
          <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold bg-slate-900 text-white">
            Vue d’ensemble
          </a>
          <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">
            Communication
          </a>
          <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">
            Édition
          </a>
          <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">
            Plateformes
          </a>
          <a href="#" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">
            Évaluation
          </a>
        </div>
      </nav>

    </div>
  </section>

  <!-- CONTENU -->
  <main class="mx-auto max-w-screen-xl px-6 py-6">
    <div class="bg-white rounded-2xl p-6 sm:p-8 space-y-8 shadow-sm">
      <section>
        <h2 class="text-md font-bold text-slate-900 mb-2">Informations générales</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-4 text-sm">
          <div>
            <dt class="text-slate-500">Porteur</dt>
            <dd class="mt-1 font-medium text-slate-900">Prénom NOM</dd>
          </div>
          <div>
            <dt class="text-slate-500">Laboratoire</dt>
            <dd class="mt-1 font-medium text-slate-900">CHCSC</dd>
          </div>
        </dl>
      </section>

      <section>
        <h2 class="text-sm font-bold text-slate-900 mb-2">Résumé</h2>
        <p class="text-sm text-slate-700 leading-relaxed">(Texte…)</p>
      </section>
    </div>
  </main>
</div>

<?php
get_footer('test');
?>