<?php

/* Génère des champs de formulaire HTML */
class FormHelper
{
    /**
     * Génère un champ input avec gestion d'erreurs et de valeurs préservées
     */
    public static function input($name, $label = '', $attributes = [], $errors = [])
    {
        $html = '';
        
        // Ajouter le label si fourni
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label) . '</label>';
        }
        
        // Récupérer la valeur (depuis POST ou valeur par défaut)
        $value = $_POST[$name] ?? ($attributes['value'] ?? '');
        
        // Construire les attributs
        $attrString = '';
        $class = $attributes['class'] ?? '';
        
        // Ajouter la classe d'erreur si nécessaire
        if (isset($errors[$name])) {
            $class .= ' error-field';
        }
        
        // Construire tous les attributs
        foreach ($attributes as $key => $val) {
            if ($key === 'class') continue; // Géré séparément
            
            if ($key === 'value') {
                $val = htmlspecialchars($value);
            }
            
            $attrString .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        // Ajouter la classe
        if ($class) {
            $attrString .= ' class="' . trim($class) . '"';
        }
        
        // Générer l'input
        $html .= '<input name="' . htmlspecialchars($name) . '"' . $attrString . '>';
        
        // Ajouter le message d'erreur si nécessaire
        if (isset($errors[$name])) {
            $html .= '<div class="field-error-message">' . htmlspecialchars($errors[$name]) . '</div>';
        }
        
        return $html;
    }
    
    /**
     * Génère un champ textarea avec gestion d'erreurs
     */
    public static function textarea($name, $label = '', $attributes = [], $errors = [])
    {
        $html = '';
        
        // Ajouter le label si fourni
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label) . '</label>';
        }
        
        // Récupérer la valeur
        $value = $_POST[$name] ?? ($attributes['value'] ?? '');
        
        // Construire les attributs
        $attrString = '';
        $class = $attributes['class'] ?? '';
        
        // Ajouter la classe d'erreur si nécessaire
        if (isset($errors[$name])) {
            $class .= ' error-field';
        }
        
        // Construire tous les attributs sauf value
        foreach ($attributes as $key => $val) {
            if ($key === 'class' || $key === 'value') continue;
            
            $attrString .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        // Ajouter la classe
        if ($class) {
            $attrString .= ' class="' . trim($class) . '"';
        }
        
        // Générer le textarea
        $html .= '<textarea name="' . htmlspecialchars($name) . '"' . $attrString . '>' 
                . htmlspecialchars($value) . '</textarea>';
        
        // Ajouter le message d'erreur si nécessaire
        if (isset($errors[$name])) {
            $html .= '<div class="field-error-message">' . htmlspecialchars($errors[$name]) . '</div>';
        }
        
        return $html;
    }
    
    /**
     * Génère un champ select
     */
    public static function select($name, $label = '', $options = [], $attributes = [], $errors = [])
    {
        $html = '';
        
        // Ajouter le label si fourni
        if ($label) {
            $html .= '<label for="' . htmlspecialchars($name) . '">' . htmlspecialchars($label) . '</label>';
        }
        
        // Récupérer la valeur sélectionnée
        $selected = $_POST[$name] ?? ($attributes['selected'] ?? '');
        
        // Construire les attributs
        $attrString = '';
        $class = $attributes['class'] ?? '';
        
        // Ajouter la classe d'erreur si nécessaire
        if (isset($errors[$name])) {
            $class .= ' error-field';
        }
        
        // Construire tous les attributs sauf selected et class
        foreach ($attributes as $key => $val) {
            if ($key === 'class' || $key === 'selected') continue;
            
            $attrString .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        // Ajouter la classe
        if ($class) {
            $attrString .= ' class="' . trim($class) . '"';
        }
        
        // Générer le select
        $html .= '<select name="' . htmlspecialchars($name) . '"' . $attrString . '>';
        
        foreach ($options as $value => $text) {
            $isSelected = ($value == $selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $isSelected . '>'
                   . htmlspecialchars($text) . '</option>';
        }
        
        $html .= '</select>';
        
        // Ajouter le message d'erreur si nécessaire
        if (isset($errors[$name])) {
            $html .= '<div class="field-error-message">' . htmlspecialchars($errors[$name]) . '</div>';
        }
        
        return $html;
    }
    
    /**
     * Vérifie si un champ a une erreur
     */
    public static function hasError($name, $errors)
    {
        return isset($errors[$name]);
    }
    
    /**
     * Obtient le message d'erreur d'un champ
     */
    public static function getError($name, $errors)
    {
        return $errors[$name] ?? '';
    }
}