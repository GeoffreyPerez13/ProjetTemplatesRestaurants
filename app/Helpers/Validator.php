<?php

class Validator
{
    private $data;
    private $errors = [];
    private $rules = [];
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    /**
     * Définir les règles de validation
     */
    public function rules($rules)
    {
        $this->rules = $rules;
        return $this;
    }
    
    /**
     * Valider les données (retourne seulement true/false)
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
     * Ajouter une erreur (sans message)
     */
    private function addError($field)
    {
        $this->errors[$field] = true;
    }
    
    /**
     * Récupérer toutes les erreurs
     */
    public function errors()
    {
        return $this->errors;
    }
    
    /**
     * Vérifier si un champ a une erreur
     */
    public function hasError($field)
    {
        return isset($this->errors[$field]);
    }
}