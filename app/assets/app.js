import './styles/app.css';

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

// -------------------- Scroll header page d'accueil
window.addEventListener("DOMContentLoaded", () => {
	const header = document.getElementById("header");
	const headerNav = document.getElementById("header-nav");
	const burger = document.getElementById("menu-burger");

	// Vérifie qu'on est bien sur la page avec le bon header
	if (!header || !headerNav || !burger) return;

	const handleHeaderScroll = () => {
		const scrolled = window.scrollY > 100;
		header.classList.toggle("scrolled-header", scrolled);
		header.classList.toggle("h-10", scrolled);
		headerNav.classList.toggle("p-2", !scrolled);
		headerNav.classList.toggle("lg:justify-center", scrolled);
		headerNav.classList.toggle("lg:pt-0", scrolled);
		headerNav.classList.toggle("lg:pt-14", !scrolled);
		burger.classList.toggle("mt-2", scrolled);
	};

	handleHeaderScroll();
	window.addEventListener("scroll", handleHeaderScroll);
});



// -------------------- Bouton Scroll 
window.addEventListener("DOMContentLoaded", () => {
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



// -------------------- Ajuste hauteur hero dynamiquement page d'accueil
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


// -------------------- Carousel d'images page d'accueil
document.addEventListener("DOMContentLoaded", () => {
    const carouselEl = document.querySelector("#home-carousel");
    if (carouselEl) {
        import("@fancyapps/ui/dist/carousel/carousel.css");
        import("@fancyapps/ui").then(({ Carousel }) => {
            new Carousel(carouselEl, {
                Dots: false,
                infinite: true,
                transition: "slide",
            });
        });
    }
});


// -------------------- Carousel d'avis page d'accueil
window.addEventListener("DOMContentLoaded", () => {
    const splideElement = document.querySelector("#splide");
    if (splideElement) {
        import("@splidejs/splide/dist/css/splide.min.css");
        import("@splidejs/splide").then(({ default: Splide }) => {
            new Splide(splideElement, {
                type: 'slide',
                perPage: 3,
                gap: '1rem',
                breakpoints: {
                    1024: { perPage: 2 },
                    768: { perPage: 1 },
                }
            }).mount();
        });
    }
});

// -------------------- Leaflet
window.addEventListener("DOMContentLoaded", () => {
    const mapEl = document.getElementById("map") || document.getElementById("map-activity");
    if (mapEl) {
        import("leaflet/dist/leaflet.css");
        import("leaflet").then(L => {
            const map = L.map(mapEl).setView([48.116933, 7.140431], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const marker = L.marker([48.116933, 7.140431]).addTo(map);
            marker.bindPopup("Gîte du Rain du Pair").openPopup();
        });
    }
});




// -------------------- Page FAQ toggle
window.addEventListener("DOMContentLoaded", () => {
    const faqButtons = document.querySelectorAll('.faq-question');
    faqButtons.forEach(button => {
        button.addEventListener('click', () => {
            const answer = button.nextElementSibling;
            const icon = button.querySelector('span');

            const isHidden = answer.classList.contains('hidden');

            faqButtons.forEach(btn => {
                btn.nextElementSibling.classList.add('hidden');
                btn.querySelector('span').textContent = '+';
            });

            if (isHidden) {
                answer.classList.remove('hidden');
                icon.textContent = '-';
            }
        });
    });
});



