/**
 * Hero Title Text Reveal Animation
 *
 * Inspired by Animmaster Text Animation #17.
 * Splits the hero title into visual lines using GSAP SplitText,
 * wraps each line in an inner element for overflow masking,
 * and reveals each line upward with a staggered GSAP animation.
 *
 * - Uses GSAP SplitText for line detection.
 * - Wraps line contents in .text-reveal-line-inner masked by .text-reveal-line.
 * - Respects prefers-reduced-motion: skips animation and displays text immediately.
 * - Preserves accessibility: adds aria-label so screen readers read the full heading.
 * - Safe fallback: if JS fails, title remains visible normal HTML.
 */
import { gsap } from 'gsap';
import { SplitText } from 'gsap/SplitText';

gsap.registerPlugin(SplitText);

/**
 * Initialize the hero title reveal animation.
 */
export function initHeroTextReveal() {
    const titleEl = document.querySelector('.landing-hero-title[data-text-reveal]');

    if (!titleEl) {
        return;
    }

    // Preserve original text for screen readers if not already set
    const originalText = titleEl.textContent.trim();
    if (!titleEl.getAttribute('aria-label')) {
        titleEl.setAttribute('aria-label', originalText);
    }

    // Respect prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    try {
        // 1. Split the title into visual lines
        const split = new SplitText(titleEl, {
            type: 'lines',
            linesClass: 'text-reveal-line',
        });

        if (!split.lines || split.lines.length === 0) {
            return;
        }

        // 2. Wrap each line's content in an inner div for overflow clipping (mask)
        split.lines.forEach((line) => {
            const inner = document.createElement('div');
            inner.className = 'text-reveal-line-inner';

            while (line.firstChild) {
                inner.appendChild(line.firstChild);
            }
            line.appendChild(inner);
        });

        // 3. Target the inner divs for the upward reveal
        const inners = titleEl.querySelectorAll('.text-reveal-line-inner');

        gsap.from(inners, {
            yPercent: 110,
            opacity: 0,
            duration: 1,
            stagger: 0.1,
            ease: 'power4.out',
            delay: 0.1,
            clearProps: 'willChange',
        });
    } catch (error) {
        console.warn('Hero text reveal initialization error:', error);
    }
}
