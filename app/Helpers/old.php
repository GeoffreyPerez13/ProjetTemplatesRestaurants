<?php

/**
 * Récupère une ancienne valeur de formulaire (après validation échouée)
 */
function old($field, $default = '')
{
    return $_SESSION['old_input'][$field] ?? $default;
}