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

            if (status) {
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
                        status.textContent =
                            "Votre demande a bien été reçue. Notre équipe vous répondra rapidement.";
                        status.style.color = "";
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
                    status.textContent = error.message ||
                        "Une erreur est survenue. Merci de réessayer ou de nous contacter directement.";
                    status.style.color = "#d33";
                }
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            });

        });

    }

});