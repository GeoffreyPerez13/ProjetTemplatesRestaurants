/**
 * Gestion des fermetures exceptionnelles
 */
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si les éléments du calendrier existent avant de continuer
    const calendar = document.getElementById('closure-calendar');
    const monthYearElement = document.getElementById('current-month-year');
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');
    const selectedDatesList = document.getElementById('selected-dates-list');
    const selectedCountElement = document.getElementById('selected-count');
    const clearAllBtn = document.getElementById('clear-all-closure-dates');
    const saveBtn = document.getElementById('save-closure-dates');
    
    // Si les éléments n'existent pas, ne pas initialiser le calendrier
    if (!calendar || !monthYearElement || !prevMonthBtn || !nextMonthBtn || !selectedDatesList || !selectedCountElement || !clearAllBtn || !saveBtn) {
        console.log('Éléments du calendrier non trouvés, initialisation annulée');
        return;
    }
    
    // État du calendrier
    let currentDate = new Date();
    let selectedDates = new Set();
    
    // Noms des mois et jours
    const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                       'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    
    // Charger les dates existantes
    loadExistingDates();
    
    // Initialiser le calendrier
    renderCalendar();
    
    // Écouteurs d'événements
    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    
    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
    
    clearAllBtn.addEventListener('click', clearAllDates);
    saveBtn.addEventListener('click', saveDates);
    
    /**
     * Charge les dates de fermeture existantes depuis le serveur
     */
    function loadExistingDates() {
        fetch('?page=settings&action=get-closure-dates', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.dates) {
                selectedDates = new Set(data.dates);
                updateSelectedDatesList();
                renderCalendar();
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des dates:', error);
        });
    }
    
    /**
     * Affiche le calendrier
     */
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Mettre à jour le titre
        monthYearElement.textContent = `${monthNames[month]} ${year}`;
        
        // Vider le calendrier
        calendar.innerHTML = '';
        
        // Ajouter les en-têtes des jours
        dayNames.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'calendar-day-header';
            dayHeader.textContent = day;
            calendar.appendChild(dayHeader);
        });
        
        // Obtenir le premier jour du mois
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const prevLastDay = new Date(year, month, 0);
        
        const firstDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();
        const daysInPrevMonth = prevLastDay.getDate();
        
        // Ajouter les jours du mois précédent
        for (let i = firstDayOfWeek - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const dayElement = createDayElement(day, true, new Date(year, month - 1, day));
            calendar.appendChild(dayElement);
        }
        
        // Ajouter les jours du mois actuel
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayElement = createDayElement(day, false, date);
            calendar.appendChild(dayElement);
        }
        
        // Ajouter les jours du mois suivant
        const totalCells = calendar.children.length - 7; // -7 pour les en-têtes
        const remainingCells = 42 - totalCells; // 6 semaines * 7 jours
        
        for (let day = 1; day <= remainingCells; day++) {
            const dayElement = createDayElement(day, true, new Date(year, month + 1, day));
            calendar.appendChild(dayElement);
        }
    }
    
    /**
     * Crée un élément de jour pour le calendrier
     */
    function createDayElement(day, isOtherMonth, date) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        dayElement.textContent = day;
        
        if (isOtherMonth) {
            dayElement.classList.add('other-month');
        } else {
            const dateString = formatDateForStorage(date);
            const today = new Date();
            const isToday = date.toDateString() === today.toDateString();
            
            if (isToday) {
                dayElement.classList.add('today');
            }
            
            if (selectedDates.has(dateString)) {
                dayElement.classList.add('selected');
            }
            
            dayElement.addEventListener('click', () => toggleDate(dateString, dayElement));
        }
        
        return dayElement;
    }
    
    /**
     * Ajoute/supprime une date de la sélection
     */
    function toggleDate(dateString, element) {
        if (selectedDates.has(dateString)) {
            selectedDates.delete(dateString);
            element.classList.remove('selected');
        } else {
            selectedDates.add(dateString);
            element.classList.add('selected');
        }
        
        updateSelectedDatesList();
    }
    
    /**
     * Met à jour la liste des dates sélectionnées
     */
    function updateSelectedDatesList() {
        if (!selectedCountElement || !selectedDatesList) {
            console.log('Éléments de la liste non trouvés');
            return;
        }
        
        selectedCountElement.textContent = selectedDates.size;
        
        if (selectedDates.size === 0) {
            selectedDatesList.innerHTML = '<p class="no-dates">Aucune date de fermeture programmée</p>';
            return;
        }
        
        // Trier les dates
        const sortedDates = Array.from(selectedDates).sort();
        
        selectedDatesList.innerHTML = '';
        sortedDates.forEach(dateString => {
            const date = parseDateFromStorage(dateString);
            const dateItem = document.createElement('div');
            dateItem.className = 'selected-date-item';
            
            dateItem.innerHTML = `
                <div class="selected-date-text">
                    <i class="fas fa-calendar-times"></i>
                    <span>${formatDateForDisplay(date)}</span>
                </div>
                <button type="button" class="remove-date-btn" data-date="${dateString}">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            selectedDatesList.appendChild(dateItem);
        });
        
        // Ajouter les écouteurs pour les boutons de suppression
        document.querySelectorAll('.remove-date-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const dateString = e.target.closest('.remove-date-btn').dataset.date;
                removeDate(dateString);
            });
        });
    }
    
    /**
     * Supprime une date spécifique
     */
    function removeDate(dateString) {
        selectedDates.delete(dateString);
        updateSelectedDatesList();
        renderCalendar();
    }
    
    /**
     * Efface toutes les dates
     */
    function clearAllDates() {
        if (selectedDates.size === 0) return;
        
        if (confirm('Êtes-vous sûr de vouloir effacer toutes les dates de fermeture ?')) {
            selectedDates.clear();
            updateSelectedDatesList();
            renderCalendar();
        }
    }
    
    /**
     * Sauvegarde les dates sur le serveur
     */
    function saveDates() {
        const datesArray = Array.from(selectedDates);
        
        // Créer un formulaire pour soumettre les données (comme saveAllOptions)
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "?page=settings&action=save-closure-dates";

        // Ajouter les données
        const datesInput = document.createElement("input");
        datesInput.type = "hidden";
        datesInput.name = "dates";
        datesInput.value = JSON.stringify(datesArray);
        form.appendChild(datesInput);

        const csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = document.querySelector('.settings-container').dataset.csrfToken;
        form.appendChild(csrfInput);

        // Ajouter au DOM et soumettre
        document.body.appendChild(form);
        form.submit();
    }
    
    /**
     * Formate une date pour le stockage (YYYY-MM-DD)
     */
    function formatDateForStorage(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    /**
     * Parse une date depuis le format de stockage
     */
    function parseDateFromStorage(dateString) {
        const [year, month, day] = dateString.split('-').map(Number);
        return new Date(year, month - 1, day);
    }
    
    /**
     * Formate une date pour l'affichage (DD/MM/YYYY)
     */
    function formatDateForDisplay(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
});
