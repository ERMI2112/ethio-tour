import './bootstrap';
import 'bootstrap';
import { initHeroTextReveal } from './animations/text-reveal';

// Theme Toggle (Dark / Light Mode)
function initThemeToggle() {
    const savedTheme = localStorage.getItem('ethio_tour_theme') === 'dark' ? 'dark' : 'light';
    applyTheme(savedTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach((toggleBtn) => {
        toggleBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            localStorage.setItem('ethio_tour_theme', newTheme);
        });
    });
}

function applyTheme(theme) {
    const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
    const isDark = normalizedTheme === 'dark';
    const nextAction = isDark ? 'Switch to light mode' : 'Switch to dark mode';

    document.documentElement.setAttribute('data-bs-theme', normalizedTheme);
    document.querySelectorAll('[data-theme-toggle]').forEach((toggleBtn) => {
        toggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        toggleBtn.setAttribute('aria-label', nextAction);
        toggleBtn.setAttribute('title', nextAction);
        toggleBtn.querySelector('[data-theme-icon="light"]')?.classList.toggle('d-none', !isDark);
        toggleBtn.querySelector('[data-theme-icon="dark"]')?.classList.toggle('d-none', isDark);
        const label = toggleBtn.querySelector('[data-theme-label]');
        if (label) label.textContent = isDark ? 'Light mode' : 'Dark mode';
    });
}

// Currency Selector
window.setAppCurrency = function (currency) {
    localStorage.setItem('ethio_tour_currency', currency);
    const label = document.getElementById('current-currency-label');
    if (label) {
        label.textContent = currency;
    }
};

// Language Selector
window.setAppLanguage = function (lang) {
    localStorage.setItem('ethio_tour_lang', lang);
    const label = document.getElementById('current-lang-label');
    if (label) {
        label.textContent = lang === 'am' ? 'አማርኛ' : 'English';
    }
};

async function initEnhancements() {
    const animatedElements = document.querySelectorAll('[data-aos]');
    if (animatedElements.length > 0) {
        const [{ default: AOS }] = await Promise.all([
            import('aos'),
            import('aos/dist/aos.css'),
        ]);

        AOS.init({ duration: 550, easing: 'ease-out-cubic', once: true, offset: 24 });
    }

    const confirmForms = document.querySelectorAll('form[data-confirm]');
    if (confirmForms.length > 0) {
        const [{ default: Swal }] = await Promise.all([
            import('sweetalert2'),
            import('sweetalert2/dist/sweetalert2.min.css'),
        ]);

        window.Swal = Swal;

        confirmForms.forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const message = form.dataset.confirm || 'Are you sure you want to continue?';
                Swal.fire({
                    title: 'Please confirm',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel',
                    buttonsStyling: false,
                    customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-outline-secondary' },
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {

    initThemeToggle();

    const savedCurrency = localStorage.getItem('ethio_tour_currency');
    if (savedCurrency) {
        window.setAppCurrency(savedCurrency);
    }

    const savedLang = localStorage.getItem('ethio_tour_lang');
    if (savedLang) {
        window.setAppLanguage(savedLang);
    }

    initHeroTextReveal();
    void initEnhancements();
});
