<?php
// Classe Restaurant : gère la récupération des restaurants
class Restaurant
{
    // Connexion PDO à la base de données
    private $pdo;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- Trouver un restaurant par son slug (URL friendly) ---
    public function findBySlug($slug)
    {
        // Prépare la requête SQL pour récupérer un restaurant correspondant au slug
        $stmt = $this->pdo->prepare("SELECT * FROM restaurants WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);

        // Retourne le résultat sous forme d'objet
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
}