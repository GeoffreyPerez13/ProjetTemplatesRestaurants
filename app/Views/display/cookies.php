<!-- Bannière de consentement aux cookies -->
<style>
#cookie-banner {
  display: none !important;
  position: fixed !important;
  bottom: 20px !important;
  left: 20px !important;
  right: 20px !important;
  max-width: 600px !important;
  margin: 0 auto !important;
  background: linear-gradient(135deg, #fffbeb 0%, #fafaf9 100%) !important;
  color: #1c1917 !important;
  padding: 28px !important;
  z-index: 999999 !important;
  border-radius: 16px !important;
  box-shadow: 0 10px 40px rgba(180, 83, 9, 0.15), 0 2px 10px rgba(180, 83, 9, 0.05) !important;
  border: 1px solid rgba(180, 83, 9, 0.1) !important;
  flex-direction: column !important;
  gap: 24px !important;
  backdrop-filter: blur(10px) !important;
  opacity: 0 !important;
  transform: translateY(100px) !important;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

#cookie-banner.show {
  display: flex !important;
  opacity: 1 !important;
  transform: translateY(0) !important;
}

#cookie-banner .cookie-text {
  display: flex !important;
  align-items: flex-start !important;
  gap: 12px !important;
  margin-bottom: 16px !important;
}

#cookie-banner .cookie-text::before {
  content: "🍪" !important;
  font-size: 1.2rem !important;
  flex-shrink: 0 !important;
  margin-top: 2px !important;
}

#cookie-banner .cookie-text p {
  margin: 0 !important;
  font-size: 0.9rem !important;
  line-height: 1.4 !important;
  color: #57534e !important;
}

#cookie-banner .cookie-buttons {
  display: flex !important;
  gap: 8px !important;
  flex-wrap: nowrap !important;
  margin-top: 16px !important;
}

#cookie-banner .btn-cookie {
  padding: 10px 12px !important;
  border: none !important;
  border-radius: 8px !important;
  cursor: pointer !important;
  font-size: 0.8rem !important;
  font-weight: 500 !important;
  transition: all 0.3s ease !important;
  flex: 1 !important;
  min-width: 130px !important;
  max-width: 180px !important;
  text-align: center !important;
  white-space: nowrap !important;
  line-height: 1.3 !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

#cookie-banner .btn-cookie.accept {
  background: linear-gradient(135deg, #b45309 0%, #92400e 100%) !important;
  color: white !important;
  box-shadow: 0 4px 12px rgba(180, 83, 9, 0.3) !important;
}

#cookie-banner .btn-cookie.accept:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(180, 83, 9, 0.4) !important;
}

#cookie-banner .btn-cookie.reject {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
  color: white !important;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
}

#cookie-banner .btn-cookie.reject:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4) !important;
}

#cookie-banner .btn-cookie.preferences {
  background: rgba(255, 255, 255, 0.8) !important;
  color: #57534e !important;
  border: 1px solid rgba(180, 83, 9, 0.2) !important;
  backdrop-filter: blur(10px) !important;
}

#cookie-banner .btn-cookie.preferences:hover {
  background: rgba(255, 255, 255, 0.95) !important;
  border-color: #b45309 !important;
  color: #b45309 !important;
  transform: translateY(-2px) !important;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
  #cookie-banner {
    left: 16px !important;
    right: 16px !important;
    bottom: 16px !important;
    max-width: none !important;
    padding: 20px !important;
  }

  #cookie-banner .cookie-buttons {
    flex-direction: column !important;
    gap: 10px !important;
  }

  #cookie-banner .btn-cookie {
    width: 100% !important;
    padding: 14px 16px !important;
    font-size: 0.85rem !important;
    min-width: auto !important;
    max-width: none !important;
  }
}

@media (max-width: 480px) {
  #cookie-banner {
    left: 12px !important;
    right: 12px !important;
    bottom: 12px !important;
    padding: 16px !important;
  }

  #cookie-banner .btn-cookie {
    padding: 12px 14px !important;
    font-size: 0.8rem !important;
    max-width: none !important;
  }
}

/* Style pour la modale de préférences */
#cookie-modal {
  display: none !important;
  position: fixed !important;
  inset: 0 !important;
  background: rgba(0, 0, 0, 0.6) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
  z-index: 1000000 !important;
  justify-content: center !important;
  align-items: center !important;
  padding: 20px !important;
}

#cookie-modal .modal-content {
  background: linear-gradient(135deg, #fffbeb 0%, #fafaf9 100%) !important;
  padding: 32px !important;
  border-radius: 20px !important;
  max-width: 480px !important;
  width: 100% !important;
  max-height: 80vh !important;
  overflow-y: auto !important;
  position: relative !important;
  box-shadow: 0 20px 60px rgba(180, 83, 9, 0.2), 0 8px 20px rgba(180, 83, 9, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  backdrop-filter: blur(20px) !important;
}

#cookie-modal .close-modal {
  position: absolute !important;
  top: 16px !important;
  right: 16px !important;
  font-size: 1.5rem !important;
  font-weight: bold !important;
  cursor: pointer !important;
  color: #57534e !important;
  width: 40px !important;
  height: 40px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
  line-height: 1 !important;
  background: rgba(255, 255, 255, 0.5) !important;
  backdrop-filter: blur(10px) !important;
}

#cookie-modal .close-modal:hover {
  color: #ef4444 !important;
  background: rgba(255, 255, 255, 0.8) !important;
  transform: rotate(90deg) !important;
}

#cookie-modal .modal-content h3 {
  margin-top: 0 !important;
  font-size: 1.15rem !important;
  font-weight: 700 !important;
  color: #1c1917 !important;
  border-bottom: 1px solid rgba(180, 83, 9, 0.1) !important;
  padding-bottom: 16px !important;
  margin-bottom: 8px !important;
}

#cookie-modal .modal-content > p {
  color: #1c1917 !important;
  margin-bottom: 20px !important;
  font-size: 0.95rem !important;
}

#cookie-modal .cookie-option {
  margin: 16px 0 !important;
  padding: 20px !important;
  border: 1px solid rgba(100, 116, 139, 0.1) !important;
  border-radius: 12px !important;
  transition: all 0.3s ease !important;
  background: rgba(255, 255, 255, 0.3) !important;
  backdrop-filter: blur(5px) !important;
}

#cookie-modal .cookie-option:hover {
  border-color: rgba(180, 83, 9, 0.3) !important;
  background: rgba(255, 255, 255, 0.5) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}

#cookie-modal .cookie-option label {
  font-weight: 600 !important;
  font-size: 0.925rem !important;
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  cursor: pointer !important;
  color: #1c1917 !important;
}

#cookie-modal .cookie-option input[type="checkbox"] {
  width: 18px !important;
  height: 18px !important;
  accent-color: #b45309 !important;
}

#cookie-modal .option-desc {
  margin: 8px 0 0 26px !important;
  font-size: 0.825rem !important;
  color: #1c1917 !important;
  line-height: 1.5 !important;
}

#cookie-modal .modal-buttons {
  display: flex !important;
  gap: 12px !important;
  justify-content: flex-end !important;
  margin-top: 24px !important;
}

#cookie-modal .btn-cookie.save {
  background: linear-gradient(135deg, #b45309 0%, #92400e 100%) !important;
  color: white !important;
  padding: 12px 20px !important;
  border-radius: 8px !important;
  border: none !important;
  cursor: pointer !important;
  font-weight: 500 !important;
  transition: all 0.3s ease !important;
}

#cookie-modal .btn-cookie.save:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(180, 83, 9, 0.4) !important;
}

#cookie-modal .btn-cookie.accept {
  background: linear-gradient(135deg, #b45309 0%, #92400e 100%) !important;
  color: white !important;
  padding: 12px 20px !important;
  border-radius: 8px !important;
  border: none !important;
  cursor: pointer !important;
  font-weight: 500 !important;
  transition: all 0.3s ease !important;
}

#cookie-modal .btn-cookie.accept:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(180, 83, 9, 0.4) !important;
}
</style>
<div id="cookie-banner" class="cookie-banner">
    <div class="cookie-text">
        <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site. En poursuivant votre navigation, vous acceptez notre politique de cookies.</p>
    </div>
    <div class="cookie-buttons">
        <button id="accept-all-cookies" class="btn-cookie accept">Accepter tous</button>
        <button id="reject-all-cookies" class="btn-cookie reject">Refuser tous</button>
        <button id="open-cookie-preferences" class="btn-cookie preferences">Choisir mes préférences</button>
    </div>
</div>

<!-- Modale de préférences des cookies -->
<div id="cookie-modal" class="cookie-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Préférences des cookies</h3>
        <p>Personnalisez votre consentement.</p>
        <div class="cookie-option">
            <label>
                <input type="checkbox" id="cookies-necessary" checked disabled>
                Cookies nécessaires (obligatoires)
            </label>
            <p class="option-desc">Ces cookies sont indispensables au fonctionnement du site.</p>
        </div>
        <div class="cookie-option">
            <label>
                <input type="checkbox" id="cookies-analytics">
                Cookies analytiques
            </label>
            <p class="option-desc">Nous aident à améliorer notre site en collectant des informations anonymes.</p>
        </div>
        <div class="cookie-option">
            <label>
                <input type="checkbox" id="cookies-marketing">
                Cookies marketing
            </label>
            <p class="option-desc">Utilisés pour vous proposer des publicités adaptées.</p>
        </div>
        <div class="modal-buttons">
            <button id="save-cookie-preferences" class="btn-cookie save">Enregistrer mes choix</button>
            <button id="accept-all-from-modal" class="btn-cookie accept">Tout accepter</button>
        </div>
    </div>
</div>
