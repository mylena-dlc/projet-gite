import { Carousel } from "@fancyapps/ui";
import Chart from 'chart.js/auto';

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

// -------------------- Page Statistiques
document.addEventListener('DOMContentLoaded', () => {
    const dashboardCarousel = document.getElementById("myCarousel");

    // Si on est sur la page admin dashboard
    if (dashboardCarousel) {
        // Initialisation du carousel
        new Carousel(dashboardCarousel, { infinite: false });

        // Initialisation des graphes Chart.js
        const charts = document.querySelectorAll("canvas[id^='chart']");
        charts.forEach((canvas) => {
            const ctx = canvas.getContext("2d");
            const reserved = parseInt(canvas.dataset.reserved);
            const total = parseInt(canvas.dataset.total);

            new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: ["Nuits réservées", "Nuits disponibles"],
                    datasets: [{
                        data: [reserved, total - reserved],
                        backgroundColor: ["#b58869", "#a9b4a4"],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '70%',
                }
            });
        });
    }
});


// -------------------- Page d'accueil
document.addEventListener("DOMContentLoaded", () => {
    // Si on est sur le tableau de bord admin
    if (document.querySelector(".my-6 span") && document.getElementById("upcomingReservations")) {

        window.showReservations = function (type, element) {
            const sections = {
                upcoming: document.getElementById('upcomingReservations'),
                previous: document.getElementById('previousReservations'),
                ongoing: document.getElementById('ongoingReservation'),
                refuse: document.getElementById('refuse'),
                confirm: document.getElementById('confirm'),
                cancel: document.getElementById('cancel')
            };

            // Masquer toutes les sections
            Object.values(sections).forEach(section => section && section.classList.add('hidden'));

            // Afficher la section sélectionnée
            if (sections[type]) {
                sections[type].classList.remove('hidden');
            }

            // Activer le bouton cliqué
            document.querySelectorAll(".my-6 span").forEach(btn => btn.classList.remove("bg-terracota2", "border-terracota2"));
            if (element) {
                element.classList.add("bg-terracota2", "border-terracota2");
            }
        };

        window.showAllReservations = function (type) {
            let allReservations = null;

            if (type === 'upcoming') {
                allReservations = document.getElementById('allUpcomingReservations');
            } else if (type === 'previous') {
                allReservations = document.getElementById('allPreviousReservations');
            }

            if (allReservations) {
                allReservations.classList.remove('hidden');
                event.target.style.display = 'none';
            }
        };
    }
});

// -------------------- Page de revenus
document.addEventListener("DOMContentLoaded", () => {
    const incomeChartEl = document.getElementById('incomeChart');

    // Graphique des revenus
    if (incomeChartEl && window.graphData) {
        const graphData = JSON.parse(incomeChartEl.dataset.graph);

        const labels = Object.keys(graphData);
        const incomes = Object.values(graphData);

        const backgroundColors = [
            'rgba(169, 180, 164)', // Vert pastel
            'rgba(199, 169, 148)', // Beige pastel
            'rgba(108, 133, 97)',  // Vert foncé
            'rgba(233, 196, 203)'  // Rose pastel
        ];

        new Chart(incomeChartEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenus mensuels',
                    data: incomes,
                    backgroundColor: incomes.map((_, i) => backgroundColors[i % backgroundColors.length]),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Bouton "voir toutes les transactions"
    const showBtn = document.querySelector("p[onclick='showAllTransactions()']");
    const hiddenTransactions = document.getElementById("allTransactions");

    if (showBtn && hiddenTransactions) {
        showBtn.addEventListener("click", () => {
            hiddenTransactions.classList.remove("hidden");
            showBtn.style.display = "none";
        });
    }
});



// -------------------- Page Gite

// -------------------- Page d'accueil
// -------------------- Page d'accueil