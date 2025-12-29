<?php
/**
 * Grille de sélection du type de projet.
 * Utilisé dans la page "Nouveau Projet".
 */
?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-6">

    <a href="<?php echo home_url('/nouveau-projet/?ptype=em'); ?>" class="group relative flex flex-col p-8 rounded-[2rem] border border-green-200/40 bg-green-50/40 ring-1 ring-transparent hover:bg-green-100/60 hover:border-green-200/70 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-200/80">
        <div class="w-14 h-14 mb-6 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl shadow-sm ring-1 ring-green-200/60 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-seedling"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-600">Émergence</h3>
        <p class="text-gray-500 text-sm mb-6 flex-1">Soutien aux projets exploratoires et interdisciplinaires en phase de démarrage.</p>
        <div class="mt-auto flex items-center text-green-600 font-bold text-sm">Commencer <i class="px-2 fa-solid fa-arrow-right"></i></div>
    </a>

    <a href="<?php echo home_url('/nouveau-projet/?ptype=ma'); ?>" class="group relative flex flex-col p-8 rounded-[2rem] border border-blue-200/40 bg-blue-50/40 ring-1 ring-transparent hover:bg-blue-100/60 hover:border-blue-200/70 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-200/80">
        <div class="w-14 h-14 mb-6 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl shadow-sm ring-1 ring-blue-200/60 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-rocket"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-600">Maturation</h3>
        <p class="text-gray-500 text-sm mb-6 flex-1">Pour les projets de recherche avancés nécessitant une phase de consolidation avant un dépôt ANR ou ERC.</p>
        <div class="mt-auto flex items-center text-blue-600 font-bold text-sm">Commencer <i class="px-2 fa-solid fa-arrow-right"></i></div>
    </a>

    <a href="<?php echo home_url('/nouveau-projet/?ptype=ws'); ?>" class="group relative flex flex-col p-8 rounded-[2rem] border border-amber-200/40 bg-amber-50/40 ring-1 ring-transparent hover:bg-amber-100/60 hover:border-amber-200/70 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-200/80">
        <div class="w-14 h-14 mb-6 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl shadow-sm ring-1 ring-amber-200/60 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-people-group"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-amber-600">Colloque / Workshop / Journée d'étude</h3>
        <p class="text-gray-500 text-sm mb-6 flex-1">Financement d'événements scientifiques ponctuels (1 à 3 jours)</p>
        <div class="mt-auto flex items-center text-amber-600 font-bold text-sm">Commencer <i class="px-2 fa-solid fa-arrow-right"></i></div>
    </a>

    <a href="<?php echo home_url('/nouveau-projet/?ptype=se'); ?>" class="group relative flex flex-col p-8 rounded-[2rem] border border-pink-200/40 bg-pink-50/40 ring-1 ring-transparent hover:bg-pink-100/60 hover:border-pink-200/70 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-pink-200/80">
        <div class="w-14 h-14 mb-6 rounded-2xl bg-pink-100 text-pink-700 flex items-center justify-center text-2xl shadow-sm ring-1 ring-pink-200/60 group-hover:scale-105 transition-transform">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-pink-600">Séminaire</h3>
        <p class="text-gray-500 text-sm mb-6 flex-1">Aide à la mise en place de cycles de rencontres régulières.</p>
        <div class="mt-auto flex items-center text-pink-600 font-bold text-sm">Commencer <i class="px-2 fa-solid fa-arrow-right"></i></div>
    </a>
    
    </div>