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

// -------------------- Scroll header transparent/fond-banner
window.addEventListener("DOMContentLoaded", () => {
	const header = document.getElementById("header");
	const logo = document.getElementById("logo");
	const logoContainer = document.getElementById("logo-container");
	const headerNav = document.getElementById('header-nav');
	const burger = document.getElementById('menu-burger');

	const handleHeaderScroll = () => {
		const scrolled = window.scrollY > 100;

		header.classList.toggle("scrolled-header", scrolled);
		header.classList.toggle("h-10", scrolled);
		logo.style.display = scrolled ? "none" : "block";
		logoContainer.style.width = scrolled ? "0" : "10rem";
		headerNav.classList.toggle("p-2", !scrolled);
		headerNav.classList.toggle("lg:justify-between", !scrolled);
		headerNav.classList.toggle("lg:justify-center", scrolled);
		burger.classList.toggle("mt-2", scrolled);
	};

	// Appelle la fonction au chargement (utile si on recharge en scroll)
	handleHeaderScroll();
	window.addEventListener("scroll", handleHeaderScroll);
});

// -------------------- Scroll to top button
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

// -------------------- Flashy notifications
window.addEventListener("DOMContentLoaded", () => {
    const flashContainer = document.getElementById("flash-messages");
    if (flashContainer) {
        import("flashy-js").then(Flashy => {
            const messages = JSON.parse(flashContainer.dataset.messages || '[]');
            messages.forEach(msg => {
                Flashy.default("#flash-messages", {
                    type: msg.type || 'info',
                    title: msg.title || 'Message',
                    message: msg.message || '',
                    globalClose: true,
                    expiry: 5000,
                });
            });
        });
    }
});

// Lazy-load Carousel Fancyapps
document.addEventListener("DOMContentLoaded", () => {
    const carouselEl = document.querySelector("#myCarousel");
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

// -------------------- Lazy Fancybox/Carousel
window.addEventListener("DOMContentLoaded", () => {
    if (document.querySelector(".f-carousel") || document.querySelector("[data-fancybox]")) {
        import("@fancyapps/ui/dist/fancybox/fancybox.css");
        import("@fancyapps/ui/dist/carousel/carousel.css");
        import("@fancyapps/ui").then(({ Fancybox, Carousel }) => {
            if (document.querySelector(".f-carousel")) {
                new Carousel(document.querySelector(".f-carousel"), { Dots: false });
            }
            if (document.querySelector("[data-fancybox]")) {
                Fancybox.bind("[data-fancybox]", {});
            }
        });
    }
});

// -------------------- Lazy Splide
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

// -------------------- Lazy Leaflet
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

// -------------------- Ajuste hauteur hero dynamiquement
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
