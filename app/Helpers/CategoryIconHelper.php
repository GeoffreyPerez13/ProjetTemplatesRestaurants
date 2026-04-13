<?php
/**
 * Helper pour déterminer l'icône Font Awesome appropriée selon le nom de la catégorie
 */
class CategoryIconHelper
{
    /**
     * Mapping des mots-clés vers les icônes Font Awesome
     */
    private static $iconMap = [
        // Entrées
        'entrée' => 'fa-seedling',
        'entree' => 'fa-seedling',
        'starter' => 'fa-seedling',
        'apéritif' => 'fa-wine-glass',
        'aperitif' => 'fa-wine-glass',
        'apéro' => 'fa-wine-glass',
        'apero' => 'fa-wine-glass',
        'salade' => 'fa-leaf',
        'soupe' => 'fa-bowl-food',
        
        // Plats principaux
        'plat' => 'fa-utensils',
        'principal' => 'fa-utensils',
        'main' => 'fa-utensils',
        'viande' => 'fa-drumstick-bite',
        'poisson' => 'fa-fish',
        'poulet' => 'fa-drumstick-bite',
        'boeuf' => 'fa-drumstick-bite',
        'porc' => 'fa-drumstick-bite',
        'agneau' => 'fa-drumstick-bite',
        'burger' => 'fa-burger',
        'pizza' => 'fa-pizza-slice',
        'pasta' => 'fa-bowl-rice',
        'pâte' => 'fa-bowl-rice',
        'pate' => 'fa-bowl-rice',
        'riz' => 'fa-bowl-rice',
        'wok' => 'fa-fire-burner',
        
        // Desserts
        'dessert' => 'fa-ice-cream',
        'gâteau' => 'fa-cake-candles',
        'gateau' => 'fa-cake-candles',
        'tarte' => 'fa-cake-candles',
        'glace' => 'fa-ice-cream',
        'crêpe' => 'fa-cookie',
        'crepe' => 'fa-cookie',
        'pâtisserie' => 'fa-cookie',
        'patisserie' => 'fa-cookie',
        'sucré' => 'fa-candy-cane',
        'sucre' => 'fa-candy-cane',
        
        // Boissons
        'boisson' => 'fa-glass-water',
        'drink' => 'fa-glass-water',
        'café' => 'fa-mug-hot',
        'cafe' => 'fa-mug-hot',
        'thé' => 'fa-mug-hot',
        'the' => 'fa-mug-hot',
        'jus' => 'fa-glass-water',
        'soda' => 'fa-glass-water',
        'cocktail' => 'fa-martini-glass-citrus',
        'alcool' => 'fa-wine-bottle',
        'vin' => 'fa-wine-bottle',
        'bière' => 'fa-beer-mug-empty',
        'biere' => 'fa-beer-mug-empty',
        'champagne' => 'fa-champagne-glasses',
        
        // Spécialités
        'végétarien' => 'fa-carrot',
        'vegetarien' => 'fa-carrot',
        'vegan' => 'fa-leaf',
        'bio' => 'fa-leaf',
        'enfant' => 'fa-child',
        'menu' => 'fa-list',
        'formule' => 'fa-list',
        'petit-déjeuner' => 'fa-mug-saucer',
        'petit-dejeuner' => 'fa-mug-saucer',
        'breakfast' => 'fa-mug-saucer',
        'brunch' => 'fa-mug-saucer',
        'tapas' => 'fa-cheese',
        'fromage' => 'fa-cheese',
        'snack' => 'fa-cookie-bite',
        'sandwich' => 'fa-bread-slice',
        'wrap' => 'fa-bread-slice',
    ];

    /**
     * Détermine l'icône appropriée pour une catégorie donnée
     * 
     * @param string $categoryName Le nom de la catégorie
     * @return string La classe Font Awesome de l'icône
     */
    public static function getIcon($categoryName)
    {
        if (empty($categoryName)) {
            return 'fa-folder';
        }

        // Normaliser le nom : minuscules, sans accents pour la comparaison
        $normalized = mb_strtolower(trim($categoryName), 'UTF-8');
        
        // Recherche exacte d'abord
        if (isset(self::$iconMap[$normalized])) {
            return self::$iconMap[$normalized];
        }
        
        // Recherche par mot-clé contenu dans le nom
        foreach (self::$iconMap as $keyword => $icon) {
            if (strpos($normalized, $keyword) !== false) {
                return $icon;
            }
        }
        
        // Icône par défaut si aucune correspondance
        return 'fa-folder';
    }

    /**
     * Retourne toutes les icônes disponibles (pour debug/admin)
     * 
     * @return array
     */
    public static function getAllIcons()
    {
        return array_unique(array_values(self::$iconMap));
    }
}
