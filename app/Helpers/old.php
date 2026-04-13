<?php

/**
 * Récupère une ancienne valeur de formulaire (après validation échouée)
 * Les valeurs sont stockées dans $_SESSION['old_input'] par les contrôleurs
 *
 * @param string $field   Nom du champ
 * @param string $default Valeur par défaut si absente
 * @return string
 */
function old($field, $default = '')
{
    return $_SESSION['old_input'][$field] ?? $default;
}