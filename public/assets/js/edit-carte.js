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
                const type = deleteCategory ? "cette catégorie" : "ce plat";
                // Définition du type pour le message SweetAlert

                Swal.fire({
                    title: "Confirmer la suppression",
                    text: `Voulez-vous vraiment supprimer ${type} ?`,
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
