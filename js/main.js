// LUETA.LV - Main JS

// SHARED STATE FOR IMAGE CYCLING
const currentVisibleImages = new Set();

function getUniqueImage(pool, currentSrc) {
    const absoluteCurrentSrc = new URL(currentSrc, window.location.href).href;
    
    for (let i = 0; i < pool.length; i++) {
        const candidate = pool[i];
        const absoluteCandidate = new URL(candidate, window.location.href).href;
        
        // The image is unique if it's not currently visible 
        // AND it's not the image we are already showing
        if (!currentVisibleImages.has(absoluteCandidate) && absoluteCandidate !== absoluteCurrentSrc) {
            return candidate;
        }
    }
    // Fallback: find any image that isn't the current one
    return pool.find(img => new URL(img, window.location.href).href !== absoluteCurrentSrc) || pool[0];
}

function updateImageWithUnique(container, imgElement, pool, transitionTime = 900) {
    const oldSrc = imgElement.src;
    const absoluteOldSrc = new URL(oldSrc, window.location.href).href;
    const newSrc = getUniqueImage(pool, oldSrc);
    const absoluteNewSrc = new URL(newSrc, window.location.href).href;

    // Reserve the new image immediately to prevent other containers from picking it
    currentVisibleImages.add(absoluteNewSrc);

    container.style.transition = `opacity ${transitionTime}ms ease`;
    container.style.opacity = '0';

    setTimeout(() => {
        // Remove the old image from visibility set and update src
        currentVisibleImages.delete(absoluteOldSrc);
        imgElement.src = newSrc;
        container.style.opacity = '1';
    }, transitionTime);
}

// SCROLL HEADER
let lastScroll = 0;
const header = document.querySelector('header');
window.addEventListener('scroll', () => {
    const y = window.scrollY;
    if (y <= 10) {
        header.classList.remove('sticky');
    } else {
        header.classList.add('sticky');
    }
    lastScroll = y;
}, { passive: true });

// HERO IMAGE CYCLING
const heroImg1Container = document.querySelector('.hero-img-1');
const heroImg2Container = document.querySelector('.hero-img-2');
const heroImg1 = heroImg1Container ? heroImg1Container.querySelector('img') : null;
const heroImg2 = heroImg2Container ? heroImg2Container.querySelector('img') : null;
const heroImages = [
    'media/lueta-1.jpg',
    'media/lueta-2.jpg',
    'media/lueta-3.jpg',
    'media/lueta-4.jpg',
    'media/lueta-img- (1).webp',
    'media/lueta-img- (2).webp',
    'media/lueta-img- (3).webp',
    'media/lueta-img- (4).webp',
    'media/lueta-img- (5).webp',
    'media/lueta-img- (6).webp',
    'media/lueta-img- (7).webp',
    'media/lueta-img- (8).webp'
];

function initHeroCycling() {
    if (!heroImg1 || !heroImg2) return;

    currentVisibleImages.add(new URL(heroImg1.src, window.location.href).href);
    currentVisibleImages.add(new URL(heroImg2.src, window.location.href).href);

    setInterval(() => {
        updateImageWithUnique(heroImg1Container, heroImg1, heroImages);
    }, 4500);

    setTimeout(() => {
        setInterval(() => {
            updateImageWithUnique(heroImg2Container, heroImg2, heroImages);
        }, 4500);
    }, 600);
}
initHeroCycling();

// MISSIS IMAGES CYCLING
function initMissisCycling() {
    const missisContainer = document.querySelector('.missis-cycling');
    if (!missisContainer) return;
    
    const images = [
        'media/lueta-img- (5).webp',
        'media/lueta-img- (6).webp',
        'media/lueta-img- (7).webp',
        'media/lueta-img- (8).webp'
    ];
    const imgElements = missisContainer.querySelectorAll('img');
    if (imgElements.length < 2) return;

    currentVisibleImages.add(new URL(imgElements[0].src, window.location.href).href);
    currentVisibleImages.add(new URL(imgElements[1].src, window.location.href).href);

    const updateMissisImg = (idx) => {
        const imgEl = imgElements[idx];
        const container = imgEl.parentElement;
        const oldSrc = imgEl.src;
        const absoluteOldSrc = new URL(oldSrc, window.location.href).href;
        const newSrc = getUniqueImage(images, oldSrc);
        const absoluteNewSrc = new URL(newSrc, window.location.href).href;

        if (absoluteNewSrc === absoluteOldSrc) return;

        currentVisibleImages.add(absoluteNewSrc);

        container.classList.add('fade-out');

        const preload = new Image();
        preload.onload = () => {
            setTimeout(() => {
                currentVisibleImages.delete(absoluteOldSrc);
                container.style.opacity = '';
                container.style.transition = 'none';
                imgEl.src = newSrc;
                void imgEl.offsetWidth;
                container.style.transition = '';
                container.classList.remove('fade-out');
            }, 700);
        };
        preload.src = newSrc;
    };

    // Start first image transition after 3-4 seconds
    setTimeout(() => {
        updateMissisImg(0);
        setInterval(() => updateMissisImg(0), 5000);
    }, 3500);

    // Start second image transition with stagger
    setTimeout(() => {
        updateMissisImg(1);
        setInterval(() => updateMissisImg(1), 5000);
    }, 5500);
}
initMissisCycling();

// SCROLL ANIMATIONS

const revealElements = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
revealElements.forEach(el => observer.observe(el));

// COUNTER ANIMATION
const counters = document.querySelectorAll('.stat-number');
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const target = parseInt(el.dataset.count);
            let current = 0;
            const step = target / 40;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    el.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    el.textContent = Math.floor(current) + '+';
                }
            }, 30);
            counterObserver.unobserve(el);
        }
    });
}, { threshold: 0.5 });
counters.forEach(c => counterObserver.observe(c));

// ACTIVE NAV
const navLinks = document.querySelectorAll('.nav-items-grid a');
const sections = document.querySelectorAll('section[id]');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const top = section.offsetTop - 140;
        if (window.scrollY >= top) current = section.getAttribute('id');
    });
    navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === `#${current}`);
    });
}, { passive: true });

// MOBILE MENU
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const mainNav = document.getElementById('mainNav');
const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
const backToTop = document.getElementById('backToTop');

function openMobileMenu() {
    mainNav.classList.add('active');
    mobileMenuToggle.classList.add('active');
    mobileMenuOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    if (backToTop) backToTop.style.display = 'none';
}
function closeMobileMenu() {
    mainNav.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    mobileMenuOverlay.classList.remove('active');
    document.body.style.overflow = '';
    if (backToTop) backToTop.style.display = '';
}

if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', () => {
        if (mainNav.classList.contains('active')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });
}
if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMobileMenu);
if (mainNav) mainNav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));

// CONTACT POPUP
const contactPopup = document.getElementById('contactPopup');
const contactOverlay = document.getElementById('contactOverlay');
const popupClose = document.getElementById('popupClose');
const ctaButtons = document.querySelectorAll('.contact-cta-btn');

function openPopup() {
    contactPopup.classList.add('active');
    contactOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closePopup() {
    contactPopup.classList.remove('active');
    contactOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

if (ctaButtons) {
    ctaButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openPopup();
        });
    });
}
if (popupClose) popupClose.addEventListener('click', closePopup);
if (contactOverlay) contactOverlay.addEventListener('click', closePopup);

// ESCAPE KEY
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (contactPopup && contactPopup.classList.contains('active')) closePopup();
        if (mainNav && mainNav.classList.contains('active')) closeMobileMenu();
    }
});

// FORM HANDLING
const isEnglish = document.documentElement.lang === 'en';

const contactForm = document.getElementById('contactForm');
const formMessage = document.getElementById('formMessage');

const errorMessages = {
    network: isEnglish ? 'Error sending message. Please try again or contact us by email.' : 'Kļūda nosūtot ziņojumu. Lūdzu, mēģiniet vēlreiz vai sazinieties ar mani pa e-pastu.',
    required: isEnglish ? 'Please fill in all required fields.' : 'Lūdzu, aizpildiet visus obligātos laukus.',
    invalidEmail: isEnglish ? 'Please enter a valid email address.' : 'Lūdzu, ievadiet derīgu e-pasta adresi.',
    invalidPhone: isEnglish ? 'Phone number can only contain numbers and + sign.' : 'Tālruņa numurs var saturēt tikai ciparus un + zīmi.'
};

if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(contactForm);

        const phoneInput = document.getElementById('phone');
        if (phoneInput && phoneInput.value) {
            const phoneRegex = /^[0-9+]+$/;
            if (!phoneRegex.test(phoneInput.value)) {
                formMessage.textContent = errorMessages.invalidPhone;
                formMessage.className = 'form-message error';
                return;
            }
        }

        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = isEnglish ? 'Sending...' : 'Nosūta...';
        submitBtn.disabled = true;
        console.log('Submitting contact form', Object.fromEntries(formData.entries()));

        try {
            console.log('[Form Fetch] Starting fetch to contact.php');
            const response = await fetch('contact.php', { method: 'POST', body: formData });
            console.log('[Form Fetch] Response status:', response.status, 'OK?', response.ok);
            const result = await response.json();
            console.log('[Form Fetch] Parsed JSON:', result);
            formMessage.textContent = result.message;
            formMessage.className = 'form-message ' + (result.success ? 'success' : 'error');
            if (result.success) {
                const formFields = contactForm.querySelector('.form-fields');
                if (formFields) formFields.style.display = 'none';
            }
        } catch (error) {
            console.error('[Form Fetch] Caught error:', error);
                formMessage.textContent = errorMessages.network;
            formMessage.className = 'form-message error';
        } finally {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        }
    });
}

// COOKIE CONSENT
const COOKIE_KEY = 'lueta_cookie_consent';
const COOKIE_PREFS_KEY = 'lueta_cookie_prefs';
const COOKIE_DAYS = 365;

class CookieConsent {
    constructor() {
        this.banner = document.getElementById('cookieConsent');
        this.modal = document.getElementById('cookieModal');
        this.modalOverlay = document.getElementById('cookieModalOverlay');
        this.init();
    }
    init() {
        document.getElementById('cookieAcceptAll')?.addEventListener('click', () => this.acceptAll());
        document.getElementById('cookieCustomize')?.addEventListener('click', () => this.openModal());
        document.getElementById('cookieModalClose')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cookieModalOverlay')?.addEventListener('click', () => this.closeModal());
        document.getElementById('cookieModalAccept')?.addEventListener('click', () => this.acceptAll());
        document.getElementById('cookieModalSave')?.addEventListener('click', () => this.savePrefs());
        document.getElementById('cookieSettings')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.openModal();
        });
        this.check();
    }
    check() {
        const c = this.get();
        if (!c) this.showBanner();
    }
    get() {
        const cookie = document.cookie.split('; ').find(r => r.startsWith(COOKIE_KEY + '='));
        if (!cookie) return null;
        try { return JSON.parse(decodeURIComponent(cookie.split('=')[1])); }
        catch { return null; }
    }
    getPrefs() {
        const cookie = document.cookie.split('; ').find(r => r.startsWith(COOKIE_PREFS_KEY + '='));
        if (!cookie) return { analytics: false, marketing: false };
        try { return JSON.parse(decodeURIComponent(cookie.split('=')[1])); }
        catch { return { analytics: false, marketing: false }; }
    }
    showBanner() { this.banner?.classList.add('show'); }
    hideBanner() { this.banner?.classList.remove('show'); }
    openModal() {
        this.modal?.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.loadPrefs();
    }
    closeModal() {
        this.modal?.classList.remove('show');
        document.body.style.overflow = '';
    }
    loadPrefs() {
        const prefs = this.getPrefs();
        document.querySelectorAll('.category-checkbox').forEach(cb => {
            const cat = cb.dataset.category;
            if (cat === 'necessary') cb.checked = true;
            else cb.checked = prefs[cat] || false;
        });
    }
    savePrefs() {
        const prefs = {};
        document.querySelectorAll('.category-checkbox:not(:disabled)').forEach(cb => {
            prefs[cb.dataset.category] = cb.checked;
        });
        this.set(prefs);
        this.closeModal();
        this.hideBanner();
    }
    acceptAll() { this.set({ analytics: true, marketing: true }); this.closeModal(); this.hideBanner(); }
    set(prefs) {
        const consent = {
            necessary: true,
            analytics: prefs.analytics || false,
            marketing: prefs.marketing || false,
            ts: new Date().getTime()
        };
        const exp = new Date();
        exp.setDate(exp.getDate() + COOKIE_DAYS);
        const opts = '; expires=' + exp.toUTCString() + '; path=/; SameSite=Lax';
        document.cookie = COOKIE_KEY + '=' + encodeURIComponent(JSON.stringify(consent)) + opts;
        document.cookie = COOKIE_PREFS_KEY + '=' + encodeURIComponent(JSON.stringify(prefs)) + opts;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new CookieConsent();
    const logoLink = document.querySelector('.logo');
    if (logoLink) logoLink.addEventListener('click', (e) => { e.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
});

// BACK TO TOP
const backToTopBtn = document.getElementById('backToTop');
function updateBackToTop() {
    if (!backToTopBtn) return;
    const popupActive = contactPopup?.classList.contains('active');
    const modalActive = mainNav?.classList.contains('active');
    const cookieModalActive = document.getElementById('cookieModal')?.classList.contains('show');
    if (window.scrollY > 300 && !popupActive && !modalActive && !cookieModalActive) {
        backToTopBtn.style.display = 'flex';
    } else {
        backToTopBtn.style.display = 'none';
    }
}
window.addEventListener('scroll', updateBackToTop, { passive: true });
if (backToTopBtn) backToTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
updateBackToTop();
