document.addEventListener("DOMContentLoaded", () => {

    // =========================
    // MENU BURGER
    // =========================

    const burger = document.getElementById("burger");
    const mobileMenu = document.getElementById("mobileMenu");

    if (burger && mobileMenu) {

        function closeMenu() {
            mobileMenu.classList.remove("open");
            burger.setAttribute("aria-expanded", "false");
            document.body.classList.remove("menu-open");
        }

        burger.addEventListener("click", () => {
            const open = mobileMenu.classList.toggle("open");

            burger.setAttribute("aria-expanded", String(open));
            document.body.classList.toggle("menu-open", open);
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                closeMenu();
            }
        });

        mobileMenu.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", closeMenu);
        });

        document.addEventListener("click", (e) => {
            if (
                mobileMenu.classList.contains("open") &&
                !mobileMenu.contains(e.target) &&
                !burger.contains(e.target)
            ) {
                closeMenu();
            }
        });

        // Ferme automatiquement le menu lorsqu'on repasse en mode desktop
        window.addEventListener("resize", () => {
            if (window.innerWidth > 900) {
                closeMenu();
            }
        });

    }

    // =========================
    // FILTRE PRODUITS
    // =========================

    const filterButtons = document.querySelectorAll(".filter-chip");
    const panels = document.querySelectorAll(".product-panel");

    if (filterButtons.length && panels.length) {

        filterButtons.forEach((button) => {

            button.addEventListener("click", () => {

                const filter = button.dataset.filter;

                filterButtons.forEach((chip) => {
                    chip.classList.remove("active");
                });

                button.classList.add("active");

                panels.forEach((panel) => {

                    const matches =
                        filter === "all" ||
                        panel.dataset.category === filter;

                    panel.classList.toggle("active", matches);

                });

            });

        });

    }
// =========================
// FORMULAIRE CONTACT
// =========================

const contactForm = document.querySelector(".contact-form form");

if (contactForm) {

    contactForm.addEventListener("submit", (event) => {

        event.preventDefault();

        const status = document.querySelector(".form-status");
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const formData = new FormData(contactForm);

        // Ajout dynamique de l'heure locale française (ex: 17h00)
        const now = new Date();
        const localTimeStr = now.toLocaleDateString("fr-FR") + " à " + now.toLocaleTimeString("fr-FR", { hour: '2-digit', minute: '2-digit' });
        formData.append("Heure d'envoi", localTimeStr);

        if (status) {
            status.className = "form-status";
            status.style.display = "block";
            status.textContent = "Envoi en cours...";
            status.style.color = "";
        }
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        fetch(contactForm.action, {
            method: "POST",
            body: formData,
            headers: {
                "Accept": "application/json"
            }
        })
        .then((response) => {
            if (response.ok) {
                if (status) {
                    status.className = "form-status success"; // Active le style vert CSS et rend le bloc visible
                    status.textContent = "Merci ! Votre message a bien été envoyé. Notre équipe vous répondra dans les plus brefs délais.";
                }
                contactForm.reset();
            } else {
                return response.json().then((data) => {
                    const errorMsg = (data && data.errors && data.errors.length)
                        ? data.errors.map((e) => e.message).join(", ")
                        : "Une erreur est survenue. Merci de réessayer ou de nous contacter directement.";
                    throw new Error(errorMsg);
                });
            }
        })
        .catch((error) => {
            if (status) {
                status.className = "form-status error"; // Active le style rouge CSS
                status.textContent = error.message ||
                    "Une erreur est survenue. Merci de réessayer ou de nous contacter directement.";
            }
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        });

    });
}});