/**
 * Système de tour guidé pour les pages d'édition
 * Gère l'affichage des tooltips, la navigation et le highlight des éléments
 */

class Tour {
  constructor(steps) {
    this.steps = steps;
    this.currentStep = 0;
    this.isActive = false;
    this.elements = {
      overlay: null,
      highlight: null,
      tooltip: null
    };
  }

  /**
   * Démarre le tour
   */
  start() {
    if (this.isActive || !this.steps || this.steps.length === 0) return;
    
    // Appeler la fonction de préparation si elle existe
    if (typeof window.tourBeforeStart === 'function') {
      window.tourBeforeStart();
      // Attendre un peu que les animations d'accordéons se terminent
      setTimeout(() => {
        this.isActive = true;
        this.createElements();
        this.showStep(0);
      }, 400);
    } else {
      this.isActive = true;
      this.createElements();
      this.showStep(0);
    }
  }

  /**
   * Arrête le tour
   */
  stop() {
    if (!this.isActive) return;
    
    this.isActive = false;
    this.removeElements();
    
    // Fermer tous les accordéons à la fin du tour
    this.closeAllAccordions();
  }

  /**
   * Ferme tous les accordéons de la page
   */
  closeAllAccordions() {
    const collapseAllBtn = document.querySelector('#collapse-all-accordions');
    if (collapseAllBtn) {
      collapseAllBtn.click();
    }
  }

  /**
   * Crée les éléments DOM du tour
   */
  createElements() {
    // Overlay
    this.elements.overlay = document.createElement('div');
    this.elements.overlay.className = 'tour-overlay';
    document.body.appendChild(this.elements.overlay);

    // Highlight
    this.elements.highlight = document.createElement('div');
    this.elements.highlight.className = 'tour-highlight';
    document.body.appendChild(this.elements.highlight);

    // Tooltip
    this.elements.tooltip = document.createElement('div');
    this.elements.tooltip.className = 'tour-tooltip';
    document.body.appendChild(this.elements.tooltip);

    // Activer l'overlay avec un léger délai pour l'animation
    setTimeout(() => {
      this.elements.overlay.classList.add('active');
    }, 10);

    // Event listeners
    this.elements.overlay.addEventListener('click', (e) => {
      if (e.target === this.elements.overlay) {
        this.stop();
      }
    });
  }

  /**
   * Supprime les éléments DOM du tour
   */
  removeElements() {
    if (this.elements.overlay) {
      this.elements.overlay.classList.remove('active');
      setTimeout(() => {
        this.elements.overlay?.remove();
        this.elements.highlight?.remove();
        this.elements.tooltip?.remove();
      }, 300);
    }
  }

  /**
   * Affiche une étape spécifique
   */
  showStep(index) {
    if (index < 0 || index >= this.steps.length) return;

    this.currentStep = index;
    const step = this.steps[index];
    
    // Appeler beforeShow si défini pour cette étape
    if (typeof step.beforeShow === 'function') {
      // Masquer instantanément le tooltip et le highlight de l'étape précédente
      if (this.elements.tooltip) {
        this.elements.tooltip.classList.remove('active');
      }
      if (this.elements.highlight) {
        this.elements.highlight.style.opacity = '0';
      }
      
      step.beforeShow();
      // Attendre que les modifications (ex: ouverture d'accordéon) soient effectuées
      setTimeout(() => {
        // Réafficher le highlight
        if (this.elements.highlight) {
          this.elements.highlight.style.opacity = '1';
        }
        this.displayStep(step);
      }, 400);
    } else {
      this.displayStep(step);
    }
  }

  /**
   * Affiche réellement l'étape (appelé par showStep)
   */
  displayStep(step) {
    const element = document.querySelector(step.element);

    if (!element) {
      console.warn(`Element not found: ${step.element}`);
      return;
    }

    // Positionner le highlight et le tooltip d'abord
    setTimeout(() => {
      this.positionHighlight(element);
      this.positionTooltip(element, step);
      
      // Pour le premier élément, scroller après l'affichage du tooltip
      if (this.currentStep === 0) {
        setTimeout(() => {
          this.scrollToElementAfterTooltip(element);
        }, 100);
      } else {
        // Pour les autres éléments, scroll normal avant affichage
        this.scrollToElement(element);
      }
    }, 100);
    
    // Pour les éléments autres que le premier, scroll immédiat
    if (this.currentStep !== 0) {
      this.scrollToElement(element);
    }
  }

  /**
   * Positionne le highlight autour de l'élément
   */
  positionHighlight(element) {
    const rect = element.getBoundingClientRect();
    const padding = 8;

    this.elements.highlight.style.top = `${rect.top + window.scrollY - padding}px`;
    this.elements.highlight.style.left = `${rect.left + window.scrollX - padding}px`;
    this.elements.highlight.style.width = `${rect.width + padding * 2}px`;
    this.elements.highlight.style.height = `${rect.height + padding * 2}px`;
  }

  /**
   * Positionne le tooltip par rapport à l'élément
   */
  positionTooltip(element, step) {
    const rect = element.getBoundingClientRect();
    const tooltipWidth = 400;
    const tooltipPadding = 20;
    
    // Construire le contenu du tooltip
    this.elements.tooltip.innerHTML = `
      <div class="tour-tooltip-header">
        <div class="tour-tooltip-title">
          <i class="fas fa-lightbulb"></i>
          ${step.title}
        </div>
        <button class="tour-tooltip-close" onclick="tour.stop()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="tour-tooltip-content">
        ${step.content}
      </div>
      <div class="tour-tooltip-footer">
        <div class="tour-tooltip-progress">
          ${this.currentStep + 1} / ${this.steps.length}
        </div>
        <div class="tour-tooltip-actions">
          <button class="tour-btn" onclick="tour.previous()" ${this.currentStep === 0 ? 'disabled' : ''}>
            <i class="fas fa-chevron-left"></i> Précédent
          </button>
          <button class="tour-btn tour-btn-primary" onclick="tour.next()">
            ${this.currentStep === this.steps.length - 1 ? 'Terminer' : 'Suivant'} 
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    `;

    // Déterminer la position optimale
    const position = this.calculateTooltipPosition(rect, tooltipWidth);
    
    this.elements.tooltip.className = `tour-tooltip position-${position.placement}`;
    this.elements.tooltip.style.top = `${position.top}px`;
    this.elements.tooltip.style.left = `${position.left}px`;

    // Activer le tooltip avec animation
    setTimeout(() => {
      this.elements.tooltip.classList.add('active');
    }, 50);
  }

  /**
   * Calcule la meilleure position pour le tooltip
   */
  calculateTooltipPosition(elementRect, tooltipWidth) {
    const tooltipHeight = 250; // Estimation
    const padding = 20;
    const minTopMargin = 100; // Marge minimale en haut (pour le header + espace)
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;

    let placement = 'bottom';
    let top = elementRect.bottom + window.scrollY + padding;
    let left = elementRect.left + window.scrollX + (elementRect.width / 2) - (tooltipWidth / 2);

    // Position absolue de l'élément dans la page
    const elementAbsoluteTop = elementRect.top + window.scrollY;
    
    // Vérifier si le tooltip dépasse en bas
    if (elementRect.bottom + tooltipHeight + padding > viewportHeight) {
      // Essayer en haut
      const topPosition = elementAbsoluteTop - tooltipHeight - padding;
      
      // Vérifier que le tooltip ne dépasse pas en haut de la page (position absolue)
      // ET qu'il sera visible dans le viewport actuel
      if (topPosition >= minTopMargin && elementRect.top > tooltipHeight + padding) {
        placement = 'top';
        top = topPosition;
      } else {
        // Si pas assez de place en haut, forcer en bas
        placement = 'bottom';
        top = elementRect.bottom + window.scrollY + padding;
      }
    }

    // Vérifier si le tooltip dépasse à droite
    if (left + tooltipWidth > viewportWidth - padding) {
      left = viewportWidth - tooltipWidth - padding;
    }

    // Vérifier si le tooltip dépasse à gauche
    if (left < padding) {
      left = padding;
    }

    // Sur mobile, centrer horizontalement
    if (viewportWidth < 768) {
      left = padding;
      placement = 'bottom';
    }

    return { placement, top, left };
  }

  /**
   * Scroll vers l'élément après l'affichage du tooltip (pour le premier élément)
   */
  scrollToElementAfterTooltip(element) {
    // Scroller au maximum vers le bas pour voir tout le tooltip
    const documentHeight = Math.max(
      document.body.scrollHeight,
      document.documentElement.scrollHeight,
      document.body.offsetHeight,
      document.documentElement.offsetHeight,
      document.body.clientHeight,
      document.documentElement.clientHeight
    );
    const viewportHeight = window.innerHeight;
    // Ajouter une petite marge supplémentaire pour être sûr que tout est visible
    const maxScroll = documentHeight - viewportHeight + 50;
    
    window.scrollTo({
      top: maxScroll,
      behavior: 'smooth'
    });
  }

  /**
   * Scroll vers l'élément de manière fluide
   */
  scrollToElement(element) {
    const rect = element.getBoundingClientRect();
    const tooltipHeight = 280; // Estimation de la hauteur du tooltip (augmentée)
    const padding = 20;
    const minTopMargin = 100; // Marge pour le header
    const bottomMargin = 30; // Marge en bas pour éviter que le tooltip soit collé au bord
    const viewportHeight = window.innerHeight;
    
    // Déterminer si le tooltip sera en haut ou en bas
    const elementAbsoluteTop = rect.top + window.scrollY;
    const willBeOnTop = (rect.bottom + tooltipHeight + padding > viewportHeight) && 
                        (elementAbsoluteTop >= minTopMargin + tooltipHeight + padding) && 
                        (rect.top > tooltipHeight + padding);
    
    let scrollTop;
    
    if (willBeOnTop) {
      // Tooltip en haut : centrer l'élément avec le tooltip au-dessus
      const totalHeight = tooltipHeight + padding + rect.height;
      const targetTop = (viewportHeight - totalHeight) / 2;
      scrollTop = window.scrollY + rect.top - targetTop - tooltipHeight - padding;
    } else {
      // Tooltip en bas : positionner l'élément pour que le tooltip soit visible en entier
      // Pour le premier élément, scroller au maximum vers le bas
      if (this.currentStep === 0) {
        // Premier élément : scroller le plus bas possible pour voir tout le tooltip
        // On calcule la position pour que le bas du tooltip soit visible en bas de l'écran
        const elementBottom = rect.bottom + window.scrollY;
        const tooltipBottom = elementBottom + padding + tooltipHeight;
        scrollTop = tooltipBottom - viewportHeight + bottomMargin;
      } else {
        // Autres éléments : positionner pour que le tooltip soit visible
        const requiredSpace = rect.height + padding + tooltipHeight + bottomMargin;
        const targetTop = viewportHeight - requiredSpace;
        scrollTop = window.scrollY + rect.top - targetTop;
      }
    }

    window.scrollTo({
      top: Math.max(0, scrollTop), // Ne pas scroller en négatif
      behavior: 'smooth'
    });
  }

  /**
   * Passe à l'étape suivante
   */
  next() {
    if (this.currentStep < this.steps.length - 1) {
      this.showStep(this.currentStep + 1);
    } else {
      this.stop();
    }
  }

  /**
   * Revient à l'étape précédente
   */
  previous() {
    if (this.currentStep > 0) {
      this.showStep(this.currentStep - 1);
    }
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
