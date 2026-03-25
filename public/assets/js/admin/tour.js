/**
 * Système de tour guidé pour les pages d'édition
 * V2 — Transitions fluides, highlight animé, scroll centré, responsive
 */

class Tour {
  constructor(steps) {
    this.steps = steps;
    this.currentStep = 0;
    this.isActive = false;
    this.isTransitioning = false;
    this._currentElement = null;
    this._raf = null;

    // Refs DOM
    this.els = { overlay: null, svg: null, tooltip: null };

    // Bind pour pouvoir retirer les listeners
    this._onResize = this._onResizeHandler.bind(this);
    this._onScroll = this._onScrollHandler.bind(this);
    this._onKeyDown = this._handleKey.bind(this);
  }

  /* ========== LIFECYCLE ========== */

  start() {
    if (this.isActive || !this.steps || this.steps.length === 0) return;

    const go = () => {
      this.isActive = true;
      this._createDOM();
      this._addListeners();
      this.showStep(0);
    };

    if (typeof window.tourBeforeStart === 'function') {
      window.tourBeforeStart();
      setTimeout(go, 300);
    } else {
      go();
    }
  }

  stop() {
    if (!this.isActive) return;
    this.isActive = false;
    this._removeListeners();
    cancelAnimationFrame(this._raf);

    // Fade out
    if (this.els.overlay) this.els.overlay.classList.remove('active');
    if (this.els.tooltip) this.els.tooltip.classList.remove('active');

    setTimeout(() => {
      this.els.overlay?.remove();
      this.els.tooltip?.remove();
      this.els = { overlay: null, svg: null, tooltip: null };
      this._currentElement = null;
    }, 250);

    // Fermer les accordéons
    const btn = document.querySelector('#collapse-all-accordions');
    if (btn) btn.click();
  }

  /* ========== DOM ========== */

  _createDOM() {
    // Overlay container (contient le SVG cutout)
    this.els.overlay = document.createElement('div');
    this.els.overlay.className = 'tour-overlay';
    this.els.overlay.addEventListener('click', (e) => {
      if (e.target === this.els.overlay || e.target.tagName === 'svg' || e.target.tagName === 'path') {
        this.stop();
      }
    });

    // SVG pour le cutout (trou autour de l'élément ciblé)
    this.els.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    this.els.svg.classList.add('tour-svg-overlay');
    this.els.overlay.appendChild(this.els.svg);

    // Tooltip
    this.els.tooltip = document.createElement('div');
    this.els.tooltip.className = 'tour-tooltip';

    document.body.appendChild(this.els.overlay);
    document.body.appendChild(this.els.tooltip);

    // Activer avec micro-délai pour trigger la transition CSS
    requestAnimationFrame(() => {
      this.els.overlay.classList.add('active');
    });
  }

  /* ========== EVENT LISTENERS ========== */

  _addListeners() {
    window.addEventListener('resize', this._onResize);
    window.addEventListener('scroll', this._onScroll, { passive: true });
    document.addEventListener('keydown', this._onKeyDown);
  }

  _removeListeners() {
    window.removeEventListener('resize', this._onResize);
    window.removeEventListener('scroll', this._onScroll);
    document.removeEventListener('keydown', this._onKeyDown);
  }

  _handleKey(e) {
    if (!this.isActive) return;
    if (e.key === 'Escape') { this.stop(); return; }
    if (e.key === 'ArrowRight' || e.key === 'Enter') { e.preventDefault(); this.next(); return; }
    if (e.key === 'ArrowLeft') { e.preventDefault(); this.previous(); }
  }

  /* ========== STEP NAVIGATION ========== */

  showStep(index) {
    if (index < 0 || index >= this.steps.length || this.isTransitioning) return;
    this.isTransitioning = true;
    this.currentStep = index;
    const step = this.steps[index];

    // Masquer le tooltip pendant la transition
    this.els.tooltip.classList.remove('active');

    const proceed = () => {
      const el = document.querySelector(step.element);
      if (!el) {
        console.warn(`[Tour] Element not found: ${step.element}`);
        this.isTransitioning = false;
        return;
      }
      this._currentElement = el;

      // 1. Render + positionner le tooltip (invisible) pour mesurer sa vraie hauteur
      this._renderTooltip(step);
      this._positionTooltip(el);

      // 2. Scroller pour que élément + tooltip soient entièrement visibles
      this._scrollToElement(el, () => {
        // 3. Après scroll, recalculer les positions avec les coordonnées finales
        this._updateSVGCutout(el);
        this._positionTooltip(el);

        // 4. Afficher le tooltip avec animation
        requestAnimationFrame(() => {
          this.els.tooltip.classList.add('active');
          this.isTransitioning = false;
        });
      });
    };

    // Si l'étape a un beforeShow (ex: ouvrir un accordéon), l'appeler d'abord
    if (typeof step.beforeShow === 'function') {
      step.beforeShow();
      setTimeout(proceed, 250);
    } else {
      proceed();
    }
  }

  next() {
    if (this.currentStep < this.steps.length - 1) {
      this.showStep(this.currentStep + 1);
    } else {
      this.stop();
    }
  }

  previous() {
    if (this.currentStep > 0) {
      this.showStep(this.currentStep - 1);
    }
  }

  /* ========== SVG CUTOUT ========== */

  _updateSVGCutout(el) {
    if (!this.els.svg || !el) return;

    const rect = el.getBoundingClientRect();
    const pad = 8;
    const r = 8; // border-radius du trou
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // Coordonnées du trou (en viewport, car le SVG est fixed)
    const x = Math.max(0, rect.left - pad);
    const y = Math.max(0, rect.top - pad);
    const w = rect.width + pad * 2;
    const h = rect.height + pad * 2;

    this.els.svg.setAttribute('viewBox', `0 0 ${vw} ${vh}`);
    this.els.svg.innerHTML = `
      <defs>
        <mask id="tour-mask">
          <rect x="0" y="0" width="${vw}" height="${vh}" fill="white"/>
          <rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" ry="${r}" fill="black"/>
        </mask>
      </defs>
      <rect x="0" y="0" width="${vw}" height="${vh}" fill="rgba(0,0,0,0.65)" mask="url(#tour-mask)"/>
      <rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" ry="${r}"
            fill="none" stroke="var(--color-primary, #b45309)" stroke-width="2.5"
            class="tour-highlight-rect"/>
    `;
  }

  /* ========== TOOLTIP ========== */

  _renderTooltip(step) {
    const total = this.steps.length;
    const i = this.currentStep;

    // Barre de progression visuelle
    const pct = ((i + 1) / total) * 100;

    this.els.tooltip.innerHTML = `
      <div class="tour-tooltip-header">
        <div class="tour-tooltip-title">
          <i class="fas fa-lightbulb"></i>
          ${step.title}
        </div>
        <button class="tour-tooltip-close" aria-label="Fermer">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="tour-tooltip-content">
        ${step.content}
      </div>
      <div class="tour-tooltip-footer">
        <div class="tour-tooltip-progress">
          <div class="tour-progress-bar">
            <div class="tour-progress-fill" style="width:${pct}%"></div>
          </div>
          <span class="tour-progress-text">${i + 1} / ${total}</span>
        </div>
        <div class="tour-tooltip-actions">
          <button class="tour-btn tour-btn-prev" ${i === 0 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> <span>Précédent</span>
          </button>
          <button class="tour-btn tour-btn-primary tour-btn-next">
            <span>${i === total - 1 ? 'Terminer' : 'Suivant'}</span>
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    `;

    // Attacher les événements
    this.els.tooltip.querySelector('.tour-tooltip-close').addEventListener('click', () => this.stop());
    this.els.tooltip.querySelector('.tour-btn-next').addEventListener('click', () => this.next());
    const prevBtn = this.els.tooltip.querySelector('.tour-btn-prev');
    if (prevBtn && !prevBtn.disabled) {
      prevBtn.addEventListener('click', () => this.previous());
    }
  }

  /**
   * Positionne le tooltip — appelé UNE SEULE FOIS par étape (pas au scroll)
   * Le tooltip est position:absolute → il ne bouge pas quand on scrolle.
   */
  _positionTooltip(el) {
    const tooltip = this.els.tooltip;
    const pad = 16;
    const rect = el.getBoundingClientRect();
    const vw = window.innerWidth;

    // Largeur du tooltip
    const isMobile = vw < 600;
    const tooltipW = isMobile ? vw - pad * 2 : Math.min(400, vw - pad * 2);
    tooltip.style.maxWidth = tooltipW + 'px';

    // Forcer position-bottom pour mesurer la hauteur réelle
    tooltip.style.top = '0';
    tooltip.style.left = '0';
    tooltip.className = 'tour-tooltip position-bottom';

    // Mesurer la hauteur réelle après rendu
    const tooltipH = tooltip.offsetHeight;

    // Toujours placer en dessous de l'élément (position-bottom)
    const placement = 'bottom';
    const top = rect.bottom + pad;

    // Position horizontale : centrer par rapport à l'élément
    let left;
    if (isMobile) {
      left = pad;
    } else {
      left = rect.left + rect.width / 2 - tooltipW / 2;
      left = Math.max(pad, Math.min(left, vw - tooltipW - pad));
    }

    // Position absolute (viewport → document)
    tooltip.style.top = (top + window.scrollY) + 'px';
    tooltip.style.left = left + 'px';
    tooltip.className = `tour-tooltip position-${placement}`;

    // Stocker la hauteur mesurée pour le scroll
    this._lastTooltipH = tooltipH;
  }

  /* ========== SCROLL ========== */

  /**
   * Scroll pour que l'élément ET le tooltip en dessous soient entièrement visibles.
   * Le tooltip sera toujours en position-bottom.
   */
  _scrollToElement(el, callback) {
    const rect = el.getBoundingClientRect();
    const vh = window.innerHeight;
    const headerH = 80;       // hauteur du header fixe
    const gap = 16;           // espace entre élément et tooltip
    const tooltipH = this._lastTooltipH || 220; // hauteur réelle mesurée, ou estimation
    const bottomPad = 24;     // marge sous le tooltip

    // Position idéale : l'élément commence juste sous le header avec un peu de marge
    const idealTop = headerH + 20;

    // Position du bas du tooltip
    const tooltipBottom = rect.bottom + gap + tooltipH;

    // Vérifier si le bas du tooltip est déjà visible
    if (tooltipBottom <= vh - bottomPad && rect.top >= idealTop) {
      // Tout est visible, pas besoin de scroll
      requestAnimationFrame(() => callback());
      return;
    }

    // Calculer le scroll pour que le bas du tooltip soit visible
    let targetScrollY;
    
    if (tooltipBottom > vh - bottomPad) {
      // Le tooltip déborde en bas : scroller pour que le bas du tooltip soit visible
      targetScrollY = window.scrollY + (tooltipBottom - (vh - bottomPad));
    } else if (rect.top < idealTop) {
      // L'élément est trop haut : scroller pour le positionner sous le header
      targetScrollY = window.scrollY + (rect.top - idealTop);
    } else {
      // Pas besoin de scroll
      targetScrollY = window.scrollY;
    }

    window.scrollTo({
      top: Math.max(0, targetScrollY),
      behavior: 'smooth'
    });

    // Attendre la fin du scroll
    setTimeout(callback, 300);
  }

  /* ========== LIVE UPDATE (scroll/resize) ========== */

  /**
   * Scroll : met à jour UNIQUEMENT le SVG cutout (position:fixed → suit le viewport).
   * Le tooltip est position:absolute → il reste en place, pas de recalcul nécessaire.
   * Cela évite le tremblement d'écran (boucle scroll → reposition → scroll).
   */
  _onScrollHandler() {
    if (!this.isActive || !this._currentElement || this.isTransitioning) return;

    cancelAnimationFrame(this._raf);
    this._raf = requestAnimationFrame(() => {
      this._updateSVGCutout(this._currentElement);
    });
  }

  /**
   * Resize : met à jour le SVG cutout ET repositionne le tooltip
   * (les dimensions changent, il faut tout recalculer).
   */
  _onResizeHandler() {
    if (!this.isActive || !this._currentElement || this.isTransitioning) return;

    cancelAnimationFrame(this._raf);
    this._raf = requestAnimationFrame(() => {
      this._updateSVGCutout(this._currentElement);
      this._positionTooltip(this._currentElement);
    });
  }
}

// Instance globale du tour (sera initialisée par chaque page)
let tour = null;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
  const tourButton = document.getElementById('tour-toggle');

  if (tourButton && typeof tourSteps !== 'undefined') {
    tourButton.addEventListener('click', () => {
      if (!tour) {
        tour = new Tour(tourSteps);
      }
      tour.start();
    });
  }
});
