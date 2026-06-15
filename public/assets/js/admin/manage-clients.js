/**
 * JavaScript pour la gestion des clients Premium
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('[manage-clients] JS loaded, attaching events...');
    const csrfToken = document.querySelector('.manage-clients-container')?.dataset?.csrfToken || 
                      document.querySelector('meta[name="csrf-token"]')?.content || '';

    // ==================== FILTRES ====================
    const filterTabs = document.querySelectorAll('.filter-tab');
    const clientRows = document.querySelectorAll('.client-row');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            clientRows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.plan === filter ? '' : 'none';
                }
            });
        });
    });

    // ==================== DROPDOWNS ====================
    document.querySelectorAll('.actions-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const clientId = this.dataset.client;
            const dropdown = document.getElementById(`dropdown-${clientId}`);
            // Reset all dropdowns and z-index
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('show');
                    menu.closest('.actions-dropdown').style.zIndex = '';
                }
            });
            if (dropdown) {
                dropdown.classList.toggle('show');
                // Raise z-index of parent when open
                dropdown.closest('.actions-dropdown').style.zIndex = dropdown.classList.contains('show') ? '9999' : '';
            }
        });
    });

    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
            menu.closest('.actions-dropdown').style.zIndex = '';
        });
    });

    // ==================== HELPER: AJAX POST ====================
    function postAction(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: csrfToken, ...body })
        }).then(r => r.json());
    }

    function getClientName(clientId) {
        // Chercher par data-sub-id d'abord, puis par data-client-id
        let row = document.querySelector(`.client-row[data-sub-id="${clientId}"]`);
        if (!row) row = document.querySelector(`.client-row[data-client-id="${clientId}"]`);
        return row?.querySelector('.client-name')?.textContent?.trim() || 'ce client';
    }

    // ==================== VOIR DETAILS ====================
    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', function() {
            const clientId = this.dataset.client;
            fetch(`?page=manage-clients&action=get-client-details&client_id=${clientId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message);
                const c = data.data;
                const features = JSON.parse(c.features_enabled || '[]');
                Swal.fire({
                    title: c.username,
                    html: `
                        <div style="text-align:left;font-size:0.9rem;line-height:1.8">
                            <p><strong>Restaurant :</strong> ${c.restaurant_name || 'Non défini'}</p>
                            <p><strong>Email :</strong> ${c.email}</p>
                            <p><strong>Plan :</strong> ${(c.plan_type || 'beta').toUpperCase()}</p>
                            <p><strong>Statut :</strong> ${c.status}</p>
                            <p><strong>Fonctionnalités :</strong> ${features.join(', ') || 'Aucune'}</p>
                            <p><strong>Début :</strong> ${c.started_at || '-'}</p>
                            <p><strong>Expiration :</strong> ${c.expires_at || '-'}</p>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Fermer'
                });
            })
            .catch(err => Swal.fire('Erreur', err.message, 'error'));
        });
    });

    // ==================== PROLONGER ====================
    document.querySelectorAll('.extend-subscription').forEach(btn => {
        btn.addEventListener('click', async function() {
            const clientId = this.dataset.client;
            const name = getClientName(clientId);
            const { value: months } = await Swal.fire({
                title: `Prolonger l'abonnement`,
                text: `Combien de mois ajouter pour ${name} ?`,
                input: 'select',
                inputOptions: { 1: '1 mois', 2: '2 mois', 3: '3 mois', 6: '6 mois', 12: '12 mois' },
                inputValue: 1,
                showCancelButton: true,
                cancelButtonText: 'Annuler',
                confirmButtonText: 'Prolonger'
            });
            if (!months) return;
            try {
                const data = await postAction('?page=manage-clients&action=extend-subscription', { client_id: clientId, months });
                if (!data.success) throw new Error(data.message);
                await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                location.reload();
            } catch(err) { Swal.fire('Erreur', err.message, 'error'); }
        });
    });

    // ==================== SUSPENDRE ====================
    document.querySelectorAll('.suspend-subscription').forEach(btn => {
        btn.addEventListener('click', async function() {
            const clientId = this.dataset.client;
            const name = getClientName(clientId);
            const result = await Swal.fire({
                title: 'Suspendre l\'abonnement ?',
                html: `L'accès de <strong>${name}</strong> sera temporairement désactivé.<br>Vous pourrez le réactiver à tout moment.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Suspendre',
                cancelButtonText: 'Annuler'
            });
            if (!result.isConfirmed) return;
            try {
                const data = await postAction('?page=manage-clients&action=suspend-subscription', { client_id: clientId });
                if (!data.success) throw new Error(data.message);
                await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                location.reload();
            } catch(err) { Swal.fire('Erreur', err.message, 'error'); }
        });
    });

    // ==================== DESACTIVER (CANCEL) ====================
    document.querySelectorAll('.cancel-subscription').forEach(btn => {
        btn.addEventListener('click', async function() {
            const clientId = this.dataset.client;
            const name = getClientName(clientId);
            const result = await Swal.fire({
                title: 'Désactiver l\'abonnement ?',
                html: `L'abonnement de <strong>${name}</strong> sera annulé.<br>Les fonctionnalités premium seront désactivées.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Désactiver',
                cancelButtonText: 'Annuler'
            });
            if (!result.isConfirmed) return;
            try {
                const data = await postAction('?page=manage-clients&action=cancel-subscription', { client_id: clientId });
                if (!data.success) throw new Error(data.message);
                await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                location.reload();
            } catch(err) { Swal.fire('Erreur', err.message, 'error'); }
        });
    });

    // ==================== REACTIVER ====================
    document.querySelectorAll('.reactivate-subscription').forEach(btn => {
        btn.addEventListener('click', async function() {
            const clientId = this.dataset.client;
            const name = getClientName(clientId);
            const result = await Swal.fire({
                title: 'Réactiver l\'abonnement ?',
                html: `L'abonnement de <strong>${name}</strong> sera réactivé.<br>Si l'expiration est passée, 1 mois supplémentaire sera ajouté.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                confirmButtonText: 'Réactiver',
                cancelButtonText: 'Annuler'
            });
            if (!result.isConfirmed) return;
            try {
                const data = await postAction('?page=manage-clients&action=reactivate-subscription', { client_id: clientId });
                if (!data.success) throw new Error(data.message);
                await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                location.reload();
            } catch(err) { Swal.fire('Erreur', err.message, 'error'); }
        });
    });

    // ==================== SUPPRIMER ====================
    document.querySelectorAll('.delete-client').forEach(btn => {
        btn.addEventListener('click', async function() {
            const clientId = this.dataset.client;
            const name = getClientName(clientId);
            const result = await Swal.fire({
                title: 'Supprimer ce client ?',
                html: `<strong>${name}</strong> sera définitivement supprimé.<br><br><em>Cette action est irréversible.</em>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Supprimer définitivement',
                cancelButtonText: 'Annuler'
            });
            if (!result.isConfirmed) return;
            // Double confirmation
            const confirm2 = await Swal.fire({
                title: 'Confirmation finale',
                text: `Êtes-vous vraiment sûr de vouloir supprimer ${name} ? Toutes ses données seront perdues.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Non, annuler'
            });
            if (!confirm2.isConfirmed) return;
            try {
                const data = await postAction('?page=manage-clients&action=delete-client', { client_id: clientId });
                if (!data.success) throw new Error(data.message);
                await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                location.reload();
            } catch(err) { Swal.fire('Erreur', err.message, 'error'); }
        });
    });

    // ==================== MODAL ACTIVATION (si existe) ====================
    const modal = document.getElementById('activateModal');
    if (modal) {
        const modalClose = document.querySelectorAll('.modal-close');
        const activateButtons = document.querySelectorAll('.activate-premium, .activate-pro');
        const durationSelect = document.getElementById('duration');
        
        activateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const clientId = this.dataset.client;
                const planType = this.dataset.plan;
                const clientRow = document.querySelector(`.client-row[data-client-id="${clientId}"]`);
                
                document.getElementById('modal-client-name').textContent = clientRow?.querySelector('.client-name')?.textContent || '';
                document.getElementById('modal-client-restaurant').textContent = clientRow?.querySelector('.client-email')?.textContent || '';
                
                document.querySelectorAll('.plan-option').forEach(opt => opt.classList.remove('selected'));
                document.querySelector(`.plan-option[data-plan="${planType}"]`)?.classList.add('selected');
                
                modal.dataset.clientId = clientId;
                modal.dataset.planType = planType;
                if (durationSelect) updatePrice();
                modal.classList.add('show');
            });
        });
        
        modalClose.forEach(button => {
            button.addEventListener('click', () => modal.classList.remove('show'));
        });
        
        modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.classList.remove('show');
        });

        document.querySelectorAll('.plan-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.plan-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                modal.dataset.planType = this.dataset.plan;
                if (durationSelect) updatePrice();
            });
        });

        if (durationSelect) {
            durationSelect.addEventListener('change', updatePrice);

            function updatePrice() {
                const planType = modal.dataset.planType || 'premium';
                const duration = parseInt(durationSelect.value);
                const basePrices = { premium: 19, pro: 39 };
                const discounts = { 1: 0, 3: 10, 6: 15, 12: 20 };
                const basePrice = basePrices[planType] || 19;
                const discountPercent = discounts[duration] || 0;
                const discount = Math.round(basePrice * duration * discountPercent / 100);
                const total = basePrice * duration - discount;
                const bp = document.getElementById('base-price');
                const dp = document.getElementById('discount');
                const tp = document.getElementById('total-price');
                if (bp) bp.textContent = `${basePrice * duration}\u20AC`;
                if (dp) dp.textContent = `-${discount}\u20AC`;
                if (tp) tp.textContent = `${total}\u20AC`;
            }
        }

        const confirmButton = document.getElementById('confirm-activation');
        if (confirmButton) {
            confirmButton.addEventListener('click', async function() {
                const clientId = modal.dataset.clientId;
                const planType = modal.dataset.planType;
                const duration = durationSelect ? parseInt(durationSelect.value) : 1;
                if (!clientId || !planType) return;
                
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                try {
                    const data = await postAction('?page=manage-clients&action=activate-subscription', {
                        client_id: clientId, plan_type: planType, duration: duration
                    });
                    if (!data.success) throw new Error(data.message);
                    modal.classList.remove('show');
                    await Swal.fire({ icon: 'success', title: data.message, timer: 1500, showConfirmButton: false });
                    location.reload();
                } catch(err) {
                    Swal.fire('Erreur', err.message, 'error');
                } finally {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-crown"></i> Activer l\'abonnement';
                }
            });
        }
    }
});
