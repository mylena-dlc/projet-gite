import './styles/app.css';
import './js/dashboard.js';
import { initGiteDashboard } from './js/dashboardGite.js';

import Flashy from 'flashy-js';

import Splide from '@splidejs/splide';
import '@splidejs/splide/dist/css/splide.min.css';

import { Carousel, Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";
import "@fancyapps/ui/dist/carousel/carousel.css";


import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// -------------------- Menu burger
function toogleMenu() {
    const toggleElements = document.querySelectorAll("[data-collapse-toggle]");
    toggleElements.forEach(el => {
        el.addEventListener("click", () => {
            const target = document.querySelector(el.dataset.collapseToggle);
            if (target) target.classList.toggle("hidden");
        });
    });
}
toogleMenu();

// -------------------- Scroll to top button
document.addEventListener("DOMContentLoaded", () => {
    const backToTopButton = document.getElementById("backToTop");
    if (backToTopButton) {
        window.addEventListener("scroll", () => {
            backToTopButton.classList.toggle("opacity-100", window.scrollY > 200);
            backToTopButton.classList.toggle("opacity-0", window.scrollY <= 200);
        });

        backToTopButton.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    }
});

// -------------------- Flashy notifications 
document.addEventListener('DOMContentLoaded', () => {
    const flashContainer = document.getElementById('flash-messages');
    if (flashContainer) {
        const messages = JSON.parse(flashContainer.dataset.messages || '[]');
        messages.forEach(msg => {
            Flashy('#flash-messages', {
                type: msg.type || 'info',
                title: msg.title || 'Message',
                message: msg.message || '',
                globalClose: true,
                expiry: 5000,
            });
        });
    }
});

// -------------------- Noty exemple simple 
// export function notify(text, type = 'success') {
//     new Noty({
//         text,
//         type,
//         layout: 'topRight',
//         timeout: 3000
//     }).show();
// }

// -------------------- Splide
document.addEventListener("DOMContentLoaded", () => {
    const splideEl = document.querySelector("#splide");
    if (splideEl) {
        new Splide(splideEl, {
            type: 'slide',
            perPage: 3,
            gap: '1rem',
            breakpoints: {
                1024: { perPage: 2 },
                768: { perPage: 1 }
            }
        }).mount();
    }
});

// -------------------- Fancybox / Carousel
document.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector(".f-carousel")) {
        new Carousel(document.querySelector(".f-carousel"), {
            Dots: false,
        });
    }

    if (document.querySelector("[data-fancybox]")) {
        Fancybox.bind("[data-fancybox]", {
            // options fancybox ici
        });
    }
});

// -------------------- Leaflet (ex : carte de localisation)
document.addEventListener("DOMContentLoaded", () => {
    const mapElement = document.getElementById("map");
    if (mapElement) {
        const map = L.map("map").setView([48.1234, 7.1234], 13); // coordonnées à adapter
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([48.1234, 7.1234]).addTo(map).bindPopup("Gîte du Rain du Pair").openPopup();
    }
});


// -------------------- Ajuste dynamiquement la hauteur de la section #hero
function adjustHeroHeight() {
    const header = document.querySelector('header');
    const heroSection = document.getElementById('hero-section');
    if (header && heroSection) {
        const headerHeight = header.offsetHeight;
        heroSection.style.height = `calc(100vh - ${headerHeight}px)`;
    }
}

window.addEventListener('load', adjustHeroHeight);
window.addEventListener('resize', adjustHeroHeight);

// -------------------- Initialise le Carousel fancyapps s'il est présent
document.addEventListener('DOMContentLoaded', () => {
    const carouselEl = document.getElementById("myCarousel");
    if (carouselEl) {
        new Carousel(carouselEl, {
            infinite: true,
            Dots: false,
        });
    }
});
// -------------------- Initialise le slider Splide s'il est présent
document.addEventListener('DOMContentLoaded', () => {
    const splideElement = document.querySelector('#splide');
    if (splideElement) {
        // Supprime une éventuelle pagination déjà présente
        const existingPagination = splideElement.querySelector('.splide__pagination');
        if (existingPagination) {
            existingPagination.remove();
        }

        const splide = new Splide(splideElement, {
            type: 'slide',
            perPage: 3,
            arrows: true,
            pagination: true,
            speed: 600,
            gap: '1rem',
            rewind: false,
            breakpoints: {
                1024: { perPage: 2 },
                768: { perPage: 1 },
            },
            classes: {
                arrows: 'splide__arrows your-custom-arrows-class',
            },
        });

        splide.mount();

        // Optional si tu as des modifications dynamiques à faire ensuite
        splideElement.splide?.refresh?.();
    }
});

// Lazy-load jQuery + rateYo pour les avis page d'accueil
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.rating')) {
        const jqueryScript = document.createElement('script');
        jqueryScript.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        jqueryScript.onload = () => {
            const rateyoScript = document.createElement('script');
            rateyoScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js';
            rateyoScript.defer = true;
            document.head.appendChild(rateyoScript);
        };
        document.head.appendChild(jqueryScript);
    }
});

// -------------------- Page Galerie
document.addEventListener("DOMContentLoaded", () => {
    const galleryItems = document.querySelector("[data-fancybox='gallery']");
    if (galleryItems) {
        Fancybox.bind("[data-fancybox='gallery']", {
            Thumbs: {
                autoStart: true, 
            },
            Toolbar: {
                display: ["zoom", "close"], 
            },
            animated: true, 
        });
    }

    // Scroll vers ancre 
    const hash = window.location.hash;
    if (hash) {
        const target = document.querySelector(hash);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth' });
        }
    }
});

// -------------------- Page FAQ
document.addEventListener('DOMContentLoaded', () => {
    const faqButtons = document.querySelectorAll('.faq-question');

    if (faqButtons.length > 0) {
        faqButtons.forEach(button => {
            button.addEventListener('click', () => {
                const answer = button.nextElementSibling;
                const icon = button.querySelector('span');

                const isHidden = answer.classList.contains('hidden');

                // Fermer tous les autres si tu veux un effet accordéon unique
                faqButtons.forEach(btn => {
                    const ans = btn.nextElementSibling;
                    const icn = btn.querySelector('span');
                    ans.classList.add('hidden');
                    icn.textContent = '+';
                });

                // Ouvrir celui cliqué si fermé
                if (isHidden) {
                    answer.classList.remove('hidden');
                    icon.textContent = '-';
                }
            });
        });
    }
});


// -------------------- Page aux alentours
document.addEventListener("DOMContentLoaded", function () {
    const mapEl = document.getElementById("map-activity");

    if (!mapEl) return;

    // Si Leaflet a déjà monté une carte (cas rare), on sort
    if (mapEl._leaflet_id) {
        console.warn("Carte déjà initialisée !");
        return;
    }

    // 🗺 Initialisation de la carte
    const map = L.map('map-activity').setView([48.116933, 7.140431], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Icône personnalisée pour le gîte
    const giteIcon = L.icon({
        iconUrl: '/assets/img/marker-terracota.png',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
    });

    const giteMarker = L.marker([48.116933, 7.140431], { icon: giteIcon }).addTo(map);
    giteMarker.bindPopup("<b>Le Gîte</b>").openPopup();

    // Bouton "Voir le gîte"
    const giteBtn = document.getElementById("gite");
    if (giteBtn) {
        giteBtn.addEventListener("click", () => {
            map.setView([48.116933, 7.140431], 14);
            giteMarker.openPopup();
        });
    }

    // Icône pour les activités
    const activityIcon = L.icon({
        iconUrl: '/assets/img/marker-gris.png',
        iconSize: [32, 32],
        iconAnchor: [15, 45],
        popupAnchor: [0, -45]
    });

    const markers = [];

    // Boucle sur les cartes activité
    document.querySelectorAll(".activity-card").forEach(card => {
        const lat = parseFloat(card.dataset.lat);
        const lng = parseFloat(card.dataset.lng);
        const imageUrl = card.dataset.image;
        const activityName = card.dataset.name;

        if (!isNaN(lat) && !isNaN(lng)) {
            const marker = L.marker([lat, lng], { icon: activityIcon }).addTo(map);
            marker.bindPopup(`
                <img src="${imageUrl}" alt="Photo de ${activityName}" width="150" />
                <br><b>${activityName}</b>
            `);

            markers.push(marker);

            card.addEventListener("click", () => {
                map.setView([lat, lng], 14);
                marker.openPopup();
            });
        } else {
            console.warn("Coordonnées invalides pour :", activityName);
        }
    });

    // Fix taille carte
    setTimeout(() => map.invalidateSize(), 500);

    // Filtrage par catégorie
    const categoryFilters = document.querySelectorAll(".category-filter");
    const activityGroups = document.querySelectorAll(".activity-group");

    function showCategory(category) {
        categoryFilters.forEach(filter =>
            filter.classList.remove("border-gray", "bg-terracota2")
        );

        const activeBtn = document.querySelector(`.category-filter[data-category="${category}"]`);
        activeBtn?.classList.add("border-gray1", "bg-terracota2");

        activityGroups.forEach(group => {
            if (group.dataset.category === category) {
                group.classList.remove("hidden");
            } else {
                group.classList.add("hidden");
            }
        });
    }

    categoryFilters.forEach(filter => {
        filter.addEventListener("click", () => {
            const category = filter.dataset.category;
            showCategory(category);
        });
    });

    // Afficher la première catégorie automatiquement
    if (categoryFilters.length > 0) {
        showCategory(categoryFilters[0].dataset.category);
    }
});


// -------------------- Page Admin / Gite
document.addEventListener('DOMContentLoaded', () => {
    initGiteDashboard();
});

// -------------------- Page FAQ
// -------------------- Page FAQ
// -------------------- Page FAQ
// -------------------- Page FAQ
