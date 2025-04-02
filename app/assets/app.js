import './bootstrap.js';
import './styles/app.css';
import './js/dashboard.js';


function toogleMenu() {
    const toggleElements = document.querySelectorAll("[data-collapse-toggle]");

    for(let toggleElement of toggleElements){
        toggleElement.addEventListener("click", function(){
            const elementId = this.dataset.collapseToggle;
            const menu = document.querySelector(elementId);
            menu.classList.toggle("hidden");
        });
    }
}
toogleMenu();

document.addEventListener("DOMContentLoaded", function () {
    let backToTopButton = document.getElementById("backToTop");

    // Afficher le bouton quand on scrolle vers le bas
    window.addEventListener("scroll", function () {
        if (window.scrollY > 200) {
            backToTopButton.classList.remove("opacity-0");
            backToTopButton.classList.add("opacity-100");
        } else {
            backToTopButton.classList.remove("opacity-100");
            backToTopButton.classList.add("opacity-0");
        }
    });

    // Remonter en haut de la page au clic
    backToTopButton.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
});