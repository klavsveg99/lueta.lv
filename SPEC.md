# SPEC.md - lueta.lv Landing Page

## 1. Concept & Vision
A bilingual (Latvian/English) single-page portfolio site for Lueta Dzirniece - Brand Manager, Missis Latvia 2026 finalist, and marketing strategist. Editorial elegance meets corporate professionalism: minimal, intentional, every element earning its place.

## 2. Design Language
**Aesthetic:** Modern editorial luxury - Cormorant Garamond serif + DM Sans sans-serif.
**Colors:** bg `#FAFAF8`, accent `#C9A96E` (champagne gold), text `#1A1A1A`, muted `#8A8A8A`, alt `#F0EDE6`.
**Motion:** Staggered fade+slide on scroll, hover lifts, floating hero images. Respects `prefers-reduced-motion`.

## 3. Layout & Structure
`index.html` (LV) + `en.html` (EN):
Header (sticky on scroll past 10px, logo 2.5rem, nav gap 52px, font-size 1rem, CTA, lang switcher square black/white, hamburger mobile)
Hero (swirl SVG pattern bg very light opacity, content with dash-em-dash heading, 2 floating images cycling with smooth fade) → About (video bg, stats in right column vertically stacked) → Services (9 cards) → Missis Latvia (dark section, 2 slightly overlapping images) → Experience (video bg, 6 cards) → Testimonials (3 cards) → Contact → Footer (logo+desc+social | menu vertical with 25px margin, policies below).

## 4. Features
- Bilingual with full translations (index.html / en.html)
- Lang switcher in header - square 44x44px buttons, black active state, hover accent
- Header becomes sticky when scrolled past 10px from top
- Animated hamburger mobile menu (3-line → X, slide-in overlay, staggered links)
- Hero: swirl SVG pattern background (opacity 0.06), 2 overlapping floating images (340x450, 300x390) cycling through 4 media images with smooth 0.9s fade opacity transitions
- Missis section: 2 overlapping images in right column (slight overlap, missis-img-1 z-index 2, missis-img-2 z-index 1)
- Video backgrounds: About section (video 1, 15% opacity) + Experience section (video 2, 15% opacity), autoplay muted loop no controls
- Contact popup form (name, email, phone, brand name, description)
- Form validation + success/error messages + hides fields on success
- Cookie consent banner (Accept All / Customize) - NO Reject All button
- Cookie preferences modal with category toggles (Necessary disabled, Analytics, Marketing)
- Scroll-triggered fade-up animations
- Counter animation on stats
- Active nav highlighting
- Back to top button (fixed, appears at 300px scroll, accent hover)
- Privacy & Cookie Policy in both languages (linked in footer)
- Hostinger SMTP via PHPMailer (contact.php)

## 5. Technical Approach
- `index.html`, `en.html`, `css/style.css`, `js/main.js`
- `contact.php` - PHPMailer v7, Hostinger SMTP (smtp.hostinger.com:465)
- `composer.json` - requires phpmailer/phpmailer
- `.htaccess` - HTTPS redirect, security headers, cache, gzip
- Font Awesome 6 icons, Google Fonts (Cormorant Garamond, DM Sans)
- Responsive: large screens (1400px+) increased padding (120px+), footer logo 3rem
- Media: 4 images cycling in hero, 2 videos as section backgrounds
- Cache busting: v=4 on CSS and JS

## 6. Known Issues / Notes
- Missis images in Missis Latvia section: images are static (no cycling), slight overlap via CSS positioning. Videos in About/Experience sections are for bg only, not in hero.
- Hero images cycle with opacity fade + src swap (not crossfade with duplicate imgs) - 0.9s fade, 4.5s interval, staggered start
- Cookie banner: header at 18px, reject buttons removed, only Accept All + Customize shown