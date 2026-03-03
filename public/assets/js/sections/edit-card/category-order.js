/**
 * Sauvegarde dynamique (AJAX) de l'ordre d'affichage des catégories
 */
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('.category-order-input').forEach(function (input) {
        let saveTimeout = null;

        input.addEventListener('input', function () {
            // Accepter uniquement des entiers positifs
            let val = parseInt(this.value, 10);
            if (isNaN(val) || val < 1) return;

            const categoryId = this.dataset.categoryId;
            const statusEl = document.querySelector(`.category-order-status[data-category-id="${categoryId}"]`);

            clearTimeout(saveTimeout);
            setStatus(statusEl, 'saving');

            saveTimeout = setTimeout(() => {
                saveOrder(categoryId, val, statusEl);
            }, 600); // délai debounce
        });

        // Empêcher les valeurs négatives ou non entières
        input.addEventListener('keydown', function (e) {
            if (['-', 'e', 'E', '.', ','].includes(e.key)) {
                e.preventDefault();
            }
        });
    });

    function saveOrder(categoryId, order, statusEl) {
        const params = new URLSearchParams();
        params.append('category_id', categoryId);
        params.append('display_order', order);
        if (csrfToken) params.append('csrf_token', csrfToken);

        fetch('?page=edit-card&action=update-category-order', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Erreur réseau');
            return res.json();
        })
        .then(function (data) {
            if (data.success) {
                setStatus(statusEl, 'saved');

                // Si un swap a eu lieu, mettre à jour le champ et swapper les blocs DOM
                if (data.swapped) {
                    const otherInput = document.querySelector(
                        `.category-order-input[data-category-id="${data.swapped.id}"]`
                    );
                    if (otherInput) {
                        otherInput.value = data.swapped.order;
                        const otherStatus = document.querySelector(
                            `.category-order-status[data-category-id="${data.swapped.id}"]`
                        );
                        setStatus(otherStatus, 'saved');
                    }

                    // Swapper les category-block dans le DOM
                    const block1 = document.getElementById(`category-${categoryId}`);
                    const block2 = document.getElementById(`category-${data.swapped.id}`);
                    if (block1 && block2) {
                        swapDomElements(block1, block2);
                    }
                }
            } else {
                setStatus(statusEl, 'error');
            }
        })
        .catch(function () {
            setStatus(statusEl, 'error');
        });
    }

    function swapDomElements(el1, el2) {
        const parent1 = el1.parentNode;
        const next1   = el1.nextSibling;
        const parent2 = el2.parentNode;
        const next2   = el2.nextSibling;

        // Éviter l'insertion sur soi-même si adjacent
        if (next1 === el2) {
            parent1.insertBefore(el2, el1);
        } else if (next2 === el1) {
            parent2.insertBefore(el1, el2);
        } else {
            parent1.insertBefore(el2, next1);
            parent2.insertBefore(el1, next2);
        }
    }

    function setStatus(el, state) {
        if (!el) return;
        el.className = 'category-order-status';
        if (state === 'saving') {
            el.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            el.classList.add('saving');
        } else if (state === 'saved') {
            el.innerHTML = '<i class="fas fa-check"></i>';
            el.classList.add('saved');
            setTimeout(() => { el.innerHTML = ''; el.className = 'category-order-status'; }, 2000);
        } else if (state === 'error') {
            el.innerHTML = '<i class="fas fa-times"></i>';
            el.classList.add('error');
        }
    }
});
