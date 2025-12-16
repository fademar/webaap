<?php
/**
 * Template Name: MSH - Test2
 */

get_header('test'); 
?>

<div class="min-h-screen bg-slate-50">
  <!-- Topbar -->
  <header class="sticky top-0 z-50 border-b bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-screen-xl px-6 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="h-8 w-8 rounded-lg bg-slate-100"></div>
        <div class="text-sm text-slate-600">Backoffice / Projets</div>
      </div>

      <div class="flex items-center gap-2">
        <a href="#" class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">← Retour</a>
        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
          Actions
        </button>
      </div>
    </div>
  </header>

  <!-- Header projet compact -->
  <section class="bg-white border-b">
    <div class="mx-auto max-w-screen-xl px-6 py-5">
      <div class="flex items-start justify-between gap-6">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-2 mb-2">
            <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-800">Émergence</span>
            <button class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200">
              En instruction
            </button>
            <span class="text-xs text-slate-500">Réf. 26–1–EM–01</span>
            <span class="text-xs text-slate-500">• Déposé le 12/12/2025</span>
          </div>

          <h1 class="text-xl sm:text-2xl font-bold text-slate-900 truncate">
            ALGO-MEMO
          </h1>
          <p class="mt-1 text-sm text-slate-600 line-clamp-2">
            Algorithmes de la Mémoire : Cartographie sensible des quartiers populaires via l’IA
          </p>
        </div>

        <!-- Quick actions (Linear : quelques boutons visibles) -->
        <div class="flex items-center gap-2 shrink-0">
          <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Export PDF
          </button>
          <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Notifier
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Tabs sticky (niveau 1) -->
  <nav class="sticky top-[56px] z-40 border-b bg-white">
    <div class="mx-auto max-w-screen-xl px-6">
      <div class="flex items-center gap-2 overflow-x-auto py-3">
        <a href="#" class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">
          Projet
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Gestion
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Évaluation
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Budget
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Communication
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Édition
        </a>
        <a href="#" class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
          Plateformes
        </a>
      </div>
    </div>
  </nav>

  <!-- Contenu full-width (sections plates) -->
  <main class="mx-auto max-w-screen-xl px-6 py-6">
    <div class="bg-white border rounded-2xl">
      <!-- Section 1 -->
      <section class="px-6 py-6 border-b">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h2 class="text-sm font-bold text-slate-900">Détails</h2>
            <p class="text-xs text-slate-500 mt-1">Informations de base</p>
          </div>
          <button class="text-sm font-medium text-slate-600 hover:text-slate-900">Modifier</button>
        </div>

        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-4 text-sm">
          <div>
            <dt class="text-slate-500">Vague</dt>
            <dd class="mt-1 font-medium text-slate-900">26-1</dd>
          </div>
          <div>
            <dt class="text-slate-500">Type</dt>
            <dd class="mt-1 font-medium text-slate-900">Émergence</dd>
          </div>
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

      <!-- Section 2 -->
      <section class="px-6 py-6 border-b">
        <div class="flex items-start justify-between gap-4">
          <h2 class="text-sm font-bold text-slate-900">Résumé</h2>
          <button class="text-sm font-medium text-slate-600 hover:text-slate-900">Modifier</button>
        </div>
        <div class="mt-3 text-sm text-slate-700 leading-relaxed">
          <p>(Contenu long…)</p>
        </div>
      </section>

      <!-- Section 3 -->
      <section class="px-6 py-6">
        <div class="flex items-start justify-between gap-4">
          <h2 class="text-sm font-bold text-slate-900">Pièces jointes</h2>
          <button class="text-sm font-medium text-slate-600 hover:text-slate-900">Télécharger tout</button>
        </div>

        <ul class="mt-4 divide-y">
          <li class="py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100">📎</span>
              <div>
                <div class="text-sm font-medium text-slate-900">Budget_previsionnel.pdf</div>
                <div class="text-xs text-slate-500">Ajouté le 12/12/2025</div>
              </div>
            </div>
            <a class="text-sm text-slate-600 hover:text-slate-900" href="#">Télécharger</a>
          </li>
        </ul>
      </section>
    </div>
  </main>
</div>


<?php
get_footer('test');
?>