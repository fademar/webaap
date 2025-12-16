/**
 * Front-end JavaScript
 *
 * The JavaScript code you place here will be processed by esbuild. The output
 * file will be created at `../theme/js/script.min.js` and enqueued in
 * `../theme/functions.php`.
 *
 * For esbuild documentation, please see:
 * https://esbuild.github.io/
 */

/**
 * Gestion de la suppression des brouillons WS Form
 */
document.addEventListener('DOMContentLoaded', function() {
    // Délégation d'événements pour gérer les boutons de suppression
    document.body.addEventListener('click', function(e) {
        // Vérifier si le click provient d'un bouton de suppression
        const deleteBtn = e.target.closest('.mshps-delete-draft');
        if (!deleteBtn) return;
        
        e.preventDefault();
        
        // Récupérer l'ID de la soumission
        const submitId = deleteBtn.getAttribute('data-submit-id');
        if (!submitId) return;
        
        // Confirmation avant suppression
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce brouillon ?\n\nCette action est irréversible.')) {
            return;
        }
        
        // Désactiver le bouton pendant la requête
        deleteBtn.disabled = true;
        deleteBtn.style.opacity = '0.5';
        deleteBtn.style.cursor = 'wait';
        
        // Appel à l'API REST
        fetch(`/wp-json/mshps/v1/draft/${submitId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': window.wpApiSettings?.nonce || ''
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Erreur lors de la suppression');
                });
            }
            return response.json();
        })
        .then(data => {
            // Supprimer visuellement la ligne du tableau
            const row = deleteBtn.closest('tr');
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    
                    // Vérifier s'il reste des lignes dans le tableau
                    const tbody = document.querySelector('[data-dataview="mes-brouillons"] tbody');
                    if (tbody && tbody.querySelectorAll('tr').length === 0) {
                        // Recharger la page pour afficher le message "Aucun projet"
                        window.location.reload();
                    }
                }, 300);
            } else {
                // Si on ne trouve pas la ligne, recharger la page
                window.location.reload();
            }
        })
        .catch(error => {
            alert('Erreur lors de la suppression du brouillon.\n\n' + error.message);
            
            // Réactiver le bouton en cas d'erreur
            deleteBtn.disabled = false;
            deleteBtn.style.opacity = '1';
            deleteBtn.style.cursor = 'pointer';
        });
    });
});
