document.addEventListener('DOMContentLoaded', function () {
  // On cible le formulaire "portail"
  var form = document.querySelector('form.msh-aap-form');
  if (!form) return;

  // Bouton "soumettre définitivement"
  var submitBtn = form.querySelector('button[name="mshps_action"][value="submit_final"]');
  if (!submitBtn) return;

  // La checkbox ACF (true_false) — via data-name du champ
  var validationField = form.querySelector('[data-name="sys_validation_finale"] input[type="checkbox"]');
  if (!validationField) {
    // Par prudence, on ne permet pas la soumission finale si la case n’existe pas
    submitBtn.disabled = true;
    submitBtn.setAttribute('aria-disabled', 'true');
    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    return;
  }

  // Fonction d’état
  function refreshState() {
    var ok = validationField.checked;
    submitBtn.disabled = !ok;
    submitBtn.setAttribute('aria-disabled', (!ok).toString());
    submitBtn.classList.toggle('opacity-50', !ok);
    submitBtn.classList.toggle('cursor-not-allowed', !ok);
    submitBtn.title = ok ? '' : 'Cochez la case de validation pour soumettre définitivement';
  }

  // Initialisation + écoute
  refreshState();
  validationField.addEventListener('change', refreshState);

  // Sécurité UX : si désactivé, empêcher l’action explicite (clic)
  submitBtn.addEventListener('click', function (e) {
    if (submitBtn.disabled) {
      e.preventDefault();
      e.stopPropagation();
    }
  });
});