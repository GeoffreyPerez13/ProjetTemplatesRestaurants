document.addEventListener("DOMContentLoaded", function () {
    // ✅ Confirmation avant suppression (SweetAlert2)
    document.querySelectorAll("form.inline-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            const deleteCategory = form.querySelector("input[name='delete_category']");
            const deleteDish = form.querySelector("input[name='delete_dish']");

            if (deleteCategory || deleteDish) {
                e.preventDefault(); // on bloque l'envoi temporairement
                const type = deleteCategory ? "cette catégorie" : "ce plat";

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
                    if (result.isConfirmed) {
                        form.submit(); // on envoie le formulaire si confirmé
                    }
                });
            }
        });
    });

    // ✅ Validation du prix (inchangée)
    const priceForms = document.querySelectorAll("form[name='edit_dish_form'], form[name='new_dish_form']");
    priceForms.forEach(form => {
        form.addEventListener("submit", function (e) {
            const priceInput = form.querySelector("input[name='dish_price']");
            let price = parseFloat(priceInput.value);

            if (isNaN(price) || price < 0) {
                e.preventDefault();
                Swal.fire("Erreur", "Veuillez saisir un prix valide pour le plat.", "error");
                return;
            }

            priceInput.value = price.toFixed(2);
        });
    });
});
