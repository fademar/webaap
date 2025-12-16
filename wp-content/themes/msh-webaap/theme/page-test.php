<?php
/**
 * Template Name: MSH - Test
 */

get_header('test'); 
?>

<div class="min-h-screen bg-slate-50">
  <!-- Topbar (globale, sobre) -->
  <header class="sticky top-0 z-40 border-b bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-screen-xl px-6 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="h-8 w-8 rounded-lg bg-slate-100"></div>
        <div class="text-sm text-slate-600">Tableau de bord</div>
      </div>

      <div class="flex items-center gap-2">
        <a href="#" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
          ← Retour à la liste
        </a>
        <div class="relative">
  <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Actions
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.7a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" />
        </svg>
    </button>

    <!-- Dropdown -->
    <div class="absolute right-0 mt-2 w-64 rounded-xl border bg-white shadow-lg">
        <ul class="py-2 text-sm">
        <li>
        <!-- Bouton dans le dropdown Actions -->
        <button data-open-status-modal>Changer le statut du projet</button>

        </li>
        <li>
            <button class="w-full text-left px-4 py-2 hover:bg-slate-50">
            Exporter le dossier (PDF)
            </button>
        </li>
        <li>
            <button class="w-full text-left px-4 py-2 hover:bg-slate-50">
            Télécharger les pièces jointes
            </button>
        </li>

        <li><hr class="my-2 border-slate-200" /></li>

        <li>
            <button class="w-full text-left px-4 py-2 hover:bg-slate-50">
            Notifier le porteur
            </button>
        </li>
        <li>
            <button class="w-full text-left px-4 py-2 hover:bg-slate-50">
            Demander des compléments
            </button>
        </li>

        <li><hr class="my-2 border-slate-200" /></li>

        <li>
            <button class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
            Archiver le projet
            </button>
        </li>
        <li>
            <button class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
            Supprimer le projet
            </button>
        </li>
        </ul>
    </div>
    </div>
      </div>
    </div>
  </header>

  <!-- Page "document" -->
  <main class="mx-auto max-w-screen-xl px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Colonne principale -->
      <article class="lg:col-span-8">
        <div class="bg-white border rounded-2xl p-6 sm:p-8">
          <!-- Header document -->
          <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="text-xs text-slate-500">• Déposé le 12/12/2025</span>
          </div>

          <h1 class="text-3xl font-bold text-slate-900 leading-tight">
            Algorithmes de la Mémoire : Cartographie sensible des quartiers populaires via l’IA
          </h1>

          <p class="mt-2 text-slate-600">
            Acronyme : <span class="font-semibold text-slate-900">ALGO-MEMO</span>
          </p>

          <!-- Propriétés (Notion-like) -->
          <section class="mt-6">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
              Propriétés
            </h2>

            <div class="rounded-xl border bg-slate-50 p-4">
              <dl class="divide-y divide-slate-200 text-sm">
              <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Référence</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">26-1-EM-01</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Vague</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">26-1</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Type de projet</dt>
                  <dd class="col-span-7 sm:col-span-8 font-medium text-slate-900">Émergence</dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">État</dt>
                  <dd class="col-span-7 sm:col-span-8">
                    <span class="inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-800">
                      En instruction
                    </span>
                  </dd>
                </div>
                <div class="py-2 grid grid-cols-12 gap-3">
                  <dt class="col-span-5 sm:col-span-4 text-slate-500">Services sollicités</dt>
                  <dd class="col-span-7 sm:col-span-8 text-slate-700 italic">Aucun</dd>
                </div>
              </dl>
            </div>
          </section>

          <!-- Contenu (blocs type Notion) -->
          <section class="mt-8 space-y-8">
            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Résumé court</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <p>
                  (Texte du résumé court ici. Dans l’interface finale, tu peux faire un bloc repliable.)
                </p>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Argumentaire scientifique</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <p class="mb-3">
                  (Bloc de texte long, avec possibilité d’insérer des images si besoin.)
                </p>
                <p>
                  (Deuxième paragraphe…)
                </p>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Pièces jointes</h3>
              <div class="rounded-xl border bg-white p-4">
                <ul class="space-y-2 text-sm">
                  <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-700">
                      <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">📎</span>
                      <span class="font-medium">Budget_previsionnel.pdf</span>
                    </div>
                    <a href="#" class="text-slate-600 hover:text-slate-900">Télécharger</a>
                  </li>
                  <li class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-slate-700">
                      <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100">📎</span>
                      <span class="font-medium">Annexe_methodo.docx</span>
                    </div>
                    <a href="#" class="text-slate-600 hover:text-slate-900">Télécharger</a>
                  </li>
                </ul>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-bold text-slate-900 mb-2">Historique</h3>
              <div class="rounded-xl border bg-white p-4 text-sm text-slate-700">
                <ul class="space-y-3">
                  <li class="flex gap-3">
                    <span class="text-slate-400">12/12/2025</span>
                    <span>Soumission déposée par le porteur.</span>
                  </li>
                  <li class="flex gap-3">
                    <span class="text-slate-400">13/12/2025</span>
                    <span>Passage au statut “En instruction”.</span>
                  </li>
                </ul>
              </div>
            </div>
          </section>
        </div>
      </article>

      <!-- Sidebar (Notion-like : actions + navigation) -->
      <aside class="lg:col-span-4 space-y-6">
        <section class="bg-white border rounded-2xl p-6">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
            Navigation
          </h2>
          <div class="space-y-2 text-sm">
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Projet
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Gestion
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Évaluation
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Budget
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Communication
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Édition
            </a>
            <a href="#" class="block rounded-lg px-3 py-2 hover:bg-slate-50 text-slate-700 font-medium">
              Plateformes
            </a>
          </div>
        </section>

        <section class="bg-white border rounded-2xl p-6">
          <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">
            Actions rapides
          </h2>
          <div class="flex flex-col gap-2">
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Changer le statut
            </button>
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Exporter PDF
            </button>
            <button class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
              Contacter le porteur
            </button>
          </div>
        </section>
      </aside>
    </div>
  </main>
</div>


<div id="status-modal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/30"></div>

  <div class="relative mx-auto mt-24 w-full max-w-lg">
    <div class="rounded-2xl bg-white shadow-xl border p-6">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Changer le statut</h3>
          <p class="text-sm text-slate-600 mt-1">
            Statut actuel : <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">En instruction</span>
          </p>
        </div>
        <button data-close-status-modal class="rounded-lg p-2 hover:bg-slate-100" aria-label="Fermer">✕</button>
      </div>

      <div class="mt-5 space-y-4">
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-1">Nouveau statut</label>
          <select class="w-full rounded-lg border px-3 py-2 text-sm">
            <option>En instruction</option>
            <option>En évaluation</option>
            <option>Labellisé</option>
            <option>Non retenu</option>
            <option>En exécution</option>
            <option>Clôturé</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-1">Note interne (optionnel)</label>
          <textarea class="w-full rounded-lg border px-3 py-2 text-sm" rows="3"
            placeholder="Contexte, décision, référence au comité, etc."></textarea>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-700">
          <input type="checkbox" class="rounded border" />
          Notifier le porteur par email
        </label>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button data-close-status-modal class="rounded-lg border px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
          Annuler
        </button>
        <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">
          Confirmer le changement
        </button>
      </div>
    </div>
  </div>
</div>



<?php
get_footer('test');
?>