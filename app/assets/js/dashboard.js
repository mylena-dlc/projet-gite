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


