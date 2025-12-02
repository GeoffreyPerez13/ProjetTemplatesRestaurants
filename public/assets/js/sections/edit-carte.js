document.addEventListener("DOMContentLoaded", function () {
    // On attend que tout le DOM soit chargé avant d'attacher les événements

    document.querySelectorAll("form.inline-form").forEach(form => {
        // On cible tous les formulaires en ligne (suppression catégorie/plat)
        form.addEventListener("submit", function (e) {
            // On intercepte l'événement submit
            const deleteCategory = form.querySelector("input[name='delete_category']");
            const deleteDish = form.querySelector("input[name='delete_dish']");
            // On vérifie si c'est une suppression de catégorie ou de plat

            if (deleteCategory || deleteDish) {
                e.preventDefault(); // on bloque temporairement l'envoi du formulaire
                
                let itemName = "";
                let type = "";
                let warningMessage = "";

                if (deleteCategory) {
                    // Pour les catégories, on récupère le nom depuis le strong parent
                    const categoryBlock = form.closest('.category-block');
                    const categoryNameElement = categoryBlock.querySelector('strong');
                    itemName = categoryNameElement ? categoryNameElement.textContent.trim() : "cette catégorie";
                    type = "la catégorie";
                    
                    // Vérifier s'il y a des plats dans cette catégorie
                    const dishList = categoryBlock.querySelector('.dish-list');
                    const hasPlats = dishList && dishList.querySelector('li');
                    
                    if (hasPlats) {
                        warningMessage = "\n\n⚠️ Attention, tous les plats associés seront également supprimés !";
                    }
                } else if (deleteDish) {
                    // Pour les plats, on récupère le nom depuis le champ d'édition
                    const dishEditContainer = form.closest('.dish-edit-container');
                    const dishNameInput = dishEditContainer.querySelector('input[name="dish_name"]');
                    itemName = dishNameInput ? dishNameInput.value.trim() : "cet élément";
                    type = "le plat";
                }

                Swal.fire({
                    title: "Confirmer la suppression",
                    text: `Voulez-vous vraiment supprimer ${type} "${itemName}" ?${warningMessage}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    // Callback lorsque l'utilisateur ferme l'alerte
                    if (result.isConfirmed) {
                        form.submit(); // On soumet le formulaire si confirmé
                    }
                });
            }
        });
    });

    // Validation des prix pour les formulaires de plat
    const priceForms = document.querySelectorAll("form[name='edit_dish_form'], form[name='new_dish_form']");
    priceForms.forEach(form => {
        form.addEventListener("submit", function (e) {
            const priceInput = form.querySelector("input[name='dish_price']");
            let price = parseFloat(priceInput.value);
            // Conversion du prix en nombre

            if (isNaN(price) || price < 0) {
                // Vérifie que le prix est un nombre positif
                e.preventDefault();
                Swal.fire("Erreur", "Veuillez saisir un prix valide pour le plat.", "error");
                return; // Arrête l'exécution si invalide
            }

            priceInput.value = price.toFixed(2); 
            // Formate le prix à 2 décimales pour envoi en base
        });
    });
});