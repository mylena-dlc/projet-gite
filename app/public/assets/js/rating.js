$(document).ready(function () {
    $("#rating").rateYo({
        rating: 0, // la valeur initiale
        starWidth: "20px",
        precision: 0, // Désactive les demi-étoiles

        onChange: function (rating, rateYoInstance) {
            // Mettre à jour la valeur du champ caché avec la note sélectionnée
            $("input[name='review[rating]']").val(rating);
        }
    });
});

console.log("fichier rating ok");