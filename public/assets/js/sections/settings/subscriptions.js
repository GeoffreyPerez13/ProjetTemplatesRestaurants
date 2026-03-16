document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la sélection multiple des abonnements
    const selectAllCheckbox = document.getElementById('select-all-subs');
    const subCheckboxes = document.querySelectorAll('.sub-checkbox');
    const bulkCancelButton = document.getElementById('bulk-cancel-subs');
    const cancelForms = document.querySelectorAll('.cancel-form');

    // Gérer la sélection "Tout sélectionner"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            subCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const card = checkbox.closest('.subscription-row');
                if (card) card.classList.toggle('selected', isChecked);
            });
            updateBulkActionButton();
        });
    }

    // Gérer les checkboxes individuelles
    subCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Toggle .selected sur la card parente
            const card = this.closest('.subscription-row');
            if (card) card.classList.toggle('selected', this.checked);
            updateSelectAllCheckbox();
            updateBulkActionButton();
        });
    });

    // Mettre à jour la checkbox "Tout sélectionner"
    function updateSelectAllCheckbox() {
        const checkedCount = document.querySelectorAll('.sub-checkbox:checked').length;
        const totalCount = subCheckboxes.length;
        
        if (selectAllCheckbox) {
            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === totalCount) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    // Mettre à jour le bouton d'action groupée
    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.sub-checkbox:checked').length;
        
        if (bulkCancelButton) {
            bulkCancelButton.disabled = checkedCount === 0;
            
            if (checkedCount === 0) {
                bulkCancelButton.innerHTML = '<i class="fas fa-trash-alt"></i> Résilier la sélection';
            } else {
                bulkCancelButton.innerHTML = `<i class="fas fa-trash-alt"></i> Résilier (${checkedCount})`;
            }
        }
    }

    // Gérer le clic sur le bouton de résiliation groupée
    if (bulkCancelButton) {
        bulkCancelButton.addEventListener('click', function() {
            const selectedSubs = document.querySelectorAll('.sub-checkbox:checked');
            const selectedData = Array.from(selectedSubs).map(checkbox => ({
                type: checkbox.dataset.type,
                name: checkbox.dataset.name
            }));

            if (selectedData.length === 0) return;

            // Vérifier si l'abonnement basique est sélectionné
            const hasBasique = selectedData.some(sub => sub.type === 'basique');
            const premiumCount = selectedData.filter(sub => sub.type === 'premium').length;

            let message = `Êtes-vous sûr de vouloir résilier :<br><br>`;
            message += '<ul style="text-align: center; margin: 15px 0; padding-left: 0; list-style: none;">';
            selectedData.forEach(sub => {
                message += `<li style="margin: 8px 0;"><strong>${sub.name}</strong></li>`;
            });
            message += '</ul>';

            if (hasBasique && premiumCount > 0) {
                message += '<br><strong style="color: #e53e3e;">⚠️ Attention : La résiliation de l\'abonnement Basique résiliera également toutes vos options premium.</strong>';
            }

            Swal.fire({
                title: 'Confirmation de résiliation',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6b7280',
                cancelButtonColor: '#e53e3e',
                confirmButtonText: 'Annuler',
                cancelButtonText: 'Résilier',
                reverseButtons: true
            }).then((result) => {
                if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                    cancelBulkSubscriptions(selectedData);
                }
            });
        });
    }

    // Résilier les abonnements sélectionnés en groupe
    function cancelBulkSubscriptions(subscriptions) {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;
        
        // Créer un formulaire pour la résiliation groupée
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('bulk_cancel', '1');
        
        subscriptions.forEach((sub) => {
            if (sub.type === 'basique') {
                formData.append('basique_cancel', '1');
            } else {
                formData.append(`premium_cancel_${sub.name}`, sub.name);
            }
        });

        // Envoyer la requête
        fetch('?page=cancel-subscription', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Stocker le message de succès dans sessionStorage pour l'afficher après le rechargement
                sessionStorage.setItem('subscriptionSuccessMessage', data.message || 'Vos abonnements ont été résiliés avec succès.');
                window.location.hash = 'subscription-total';
                window.location.reload();
            } else {
                // Stocker le message d'erreur dans sessionStorage pour l'afficher après le rechargement
                sessionStorage.setItem('subscriptionErrorMessage', data.message || 'Une erreur est survenue lors de la résiliation.');
                window.location.hash = 'subscription-total';
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Stocker le message d'erreur technique dans sessionStorage pour l'afficher après le rechargement
            sessionStorage.setItem('subscriptionErrorMessage', 'Une erreur technique est survenue. Veuillez réessayer.');
            window.location.hash = 'subscription-total';
            window.location.reload();
        });
    }

    // Gérer les formulaires de résiliation individuels
    cancelForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const subscriptionType = this.dataset.subscriptionType;
            const featureName = this.dataset.featureName;
            const isBasique = subscriptionType === 'basique';
            
            let message = `Êtes-vous sûr de vouloir résilier <strong>${featureName}</strong> ?`;
            
            if (isBasique) {
                message += '<br><br><strong style="color: #e53e3e;">⚠️ Attention : La résiliation de l\'abonnement Basique résiliera également toutes vos options premium actives.</strong>';
            }

            Swal.fire({
                title: 'Confirmation de résiliation',
                html: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6b7280',
                cancelButtonColor: '#e53e3e',
                confirmButtonText: 'Annuler',
                cancelButtonText: 'Résilier',
                reverseButtons: true
            }).then((result) => {
                if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                    this.submit();
                }
            });
        });
    });

    // Initialisation
    updateSelectAllCheckbox();
    updateBulkActionButton();
});
