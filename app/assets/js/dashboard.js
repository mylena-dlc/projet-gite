// Fonction pour basculer entre les réservations et activer la bordure
// window.showReservations = function (type, element) {
//     const upcoming = document.getElementById('upcomingReservations');
//     const previous = document.getElementById('previousReservations');
//     const ongoing = document.getElementById('ongoingReservation');
//     const toConfirm = document.getElementById('reservationsToConfirms');
//     const refuse = document.getElementById('refuse');
//     const buttons = document.querySelectorAll('.my-6 span');

//     // Réinitialiser toutes les bordures des boutons
//     buttons.forEach(button => {
//         button.classList.remove('bg-terracota2', 'border-terracota2');
//         button.classList.add('bg-terracota1', 'border-transparent');
//     });

//     // Appliquer la bordure active au bouton cliqué
//     element.classList.remove('bg-terracota1', 'border-transparent');
//     element.classList.add('bg-terracota2', 'border-terracota2');

//     // Basculer l'affichage des sections
//     upcoming.classList.add('hidden');
//     previous.classList.add('hidden');
//     ongoing.classList.add('hidden');
//     refuse.classList.add('hidden');

//     if (type === 'upcoming') {
//         upcoming.classList.remove('hidden');
//     } else if (type === 'previous') {
//         previous.classList.remove('hidden');
//     } else if (type === 'ongoing') {
//         ongoing.classList.remove('hidden');
//     } else if (type === 'refuse') {
//         refuse.classList.remove('hidden');
//     }
// };

// // Gestion du bouton "voir toutes les réservations"
// window.showAllReservations = function (type) {
//     let allReservations;
    
//     // Détection du type de réservation (à venir ou passées)
//     if (type === 'upcoming') {
//         allReservations = document.getElementById('allUpcomingReservations');
//     } else if (type === 'previous') {
//         allReservations = document.getElementById('allPreviousReservations');
//     }

//     // Affiche toutes les réservations et cache le bouton
//     if (allReservations) {
//         allReservations.classList.remove('hidden');
//         event.target.style.display = 'none';
//     }
// };

// Fonction pour ouvrir la modale de suppression et mettre à jour l'action du formulaire
window.openDeleteModal = function (tokenId, csrfToken) {
    const deleteForm = document.getElementById('deleteTokenForm');
    const csrfTokenInput = document.getElementById('csrfToken');
    
    // Mise à jour de l'action du formulaire
    deleteForm.action = `/token/${tokenId}/delete`;
    
    // Utilisation du token passé depuis le template Twig
    csrfTokenInput.value = csrfToken;

    document.getElementById('deleteTokenModal').classList.remove('hidden');
};

// Fonction pour fermer la modale de suppression
window.closeDeleteModal = function () {
    document.getElementById('deleteTokenModal').classList.add('hidden');
};



// Fonction pour afficher toutes les transactions (page revenus)
window.showAllTransactions = function () {
    const allTransactions = document.getElementById('allTransactions');
    allTransactions.classList.remove('hidden');
    event.target.style.display = 'none'; // Cache le bouton après l'affichage
};


