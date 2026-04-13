<?php

/**
 * Validateur de formulaires avec règles : required, min, max, numeric, email, min_value, max_value
 * Inclut aussi une méthode statique validatePassword() pour la validation de mot de passe
 */
class Validator
{
    /** @var array Données à valider (généralement $_POST) */
    private $data;
    /** @var array Champs en erreur [field => true] */
    private $errors = [];
    /** @var array Règles de validation [field => ['required', 'min:3', ...]] */
    private $rules = [];

    /**
     * @param array $data Données à valider
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    /**
     * Définit les règles de validation
     *
     * @param array $rules Tableau [champ => ['required', 'min:3', 'max:100', ...]]
     * @return self
     */
    public function rules($rules)
    {
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Exécute la validation selon les règles définies
     *
     * @return bool true si toutes les règles passent
     */
    public function validate()
    {
        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? '';
            
            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && empty(trim($value))) {
                    $this->addError($field);
                    break;
                }
                
                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($value) < $min) {
                        $this->addError($field);
                        break;
                    }
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($value) > $max) {
                        $this->addError($field);
                        break;
                    }
                }
                
                if ($rule === 'numeric' && !is_numeric($value)) {
                    $this->addError($field);
                    break;
                }
                
                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field);
                    break;
                }
                
                if (strpos($rule, 'min_value:') === 0) {
                    $min = (float) substr($rule, 10);
                    if ((float)$value < $min) {
                        $this->addError($field);
                        break;
                    }
                }
                
                if (strpos($rule, 'max_value:') === 0) {
                    $max = (float) substr($rule, 10);
                    if ((float)$value > $max) {
                        $this->addError($field);
                        break;
                    }
                }
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Marque un champ comme invalide
     *
     * @param string $field Nom du champ
     */
    private function addError($field)
    {
        $this->errors[$field] = true;
    }
    
    /**
     * Récupère tous les champs en erreur
     *
     * @return array [field => true, ...]
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Vérifie si un champ spécifique est en erreur
     *
     * @param string $field Nom du champ
     * @return bool
     */
    public function hasError($field)
    {
        return isset($this->errors[$field]);
    }

    /**
     * Valide un mot de passe selon les règles de sécurité du site
     * Méthode statique réutilisable partout (register, reset, settings)
     * 
     * @param string $password Le mot de passe à valider
     * @param string|null $confirmPassword Le mot de passe de confirmation (optionnel)
     * @return array Tableau des messages d'erreur (vide si valide)
     */
    public static function validatePassword($password, $confirmPassword = null)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins une minuscule.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Le mot de passe doit contenir au moins un caractère spécial.";
        }
        if ($confirmPassword !== null && $password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        return $errors;
    }
}