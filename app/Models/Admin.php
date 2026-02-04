<?php
// Classe Admin : gère la logique des administrateurs et des invitations
class Admin
{
    // Connexion PDO à la base de données
    private $pdo;

    // Propriétés publiques représentant un administrateur
    public $id;
    public $username;
    public $password;
    public $role;
    public $restaurant_name;
    public $restaurant_id;

    // Constructeur : initialise la connexion PDO
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // --- INVITATIONS ---

    // Crée une invitation pour un nouvel administrateur
    public function createInvitation($email, $restaurantName, $token)
    {
        // Date d'expiration du lien = +24 heures
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            // Insertion de l'invitation en base (SANS restaurant_id)
            $sql = "INSERT INTO invitations (email, restaurant_name, token, expiry) 
                VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$email, $restaurantName, $token, $expiry]);

            if ($result) {
                // Génération du lien d'inscription
                $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "/admin/?page=register&token=" . $token;

                // Préparation du mail
                $to = $email;
                $subject = "Invitation à créer votre compte restaurant";

                // Dans la méthode createInvitation(), modifiez la construction du lien :
                $message = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Invitation à créer votre compte restaurant</title>
                    </head>
                    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                        <h2>Invitation Menumiam</h2>
                        <p>Bonjour,</p>
                        <p>Vous avez été invité à créer un compte pour gérer la carte en ligne de votre restaurant <strong>{$restaurantName}</strong> sur Menumiam.</p>
                        <p>Cliquez sur le lien ci-dessous pour créer votre compte :</p>
                        <p><code style='background-color: #f4f4f4; padding: 5px; border-radius: 3px;'>" . htmlspecialchars($inviteLink) . "</code></p>
                        <p><strong>Attention :</strong> Ce lien expirera dans 24 heures.</p>
                        <br>
                        <p>Cordialement,<br>L'équipe Menumiam</p>
                    </body>
                    </html>
                    ";

                // En-têtes pour l'email HTML
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Menumiam <no-reply@menumiam.com>" . "\r\n";
                $headers .= "Reply-To: no-reply@menumiam.com" . "\r\n";

                // Envoi du mail
                $mailResult = mail($to, $subject, $message, $headers);

                // Log pour déboguer
                error_log("[DEBUG] Envoi d'invitation:");
                error_log("  - Email: $email");
                error_log("  - Restaurant: $restaurantName");
                error_log("  - Lien: $inviteLink");
                error_log("  - Mail envoyé: " . ($mailResult ? "OUI" : "NON"));

                return $mailResult;
            }

            return false;
        } catch (PDOException $e) {
            error_log("[ERREUR] createInvitation: " . $e->getMessage());
            return false;
        }
    }

    // Récupère une invitation valide non utilisée par token
    public function getInvitation($token)
    {
        $sql = "SELECT * FROM invitations WHERE token = ? AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    // --- GESTION DES COMPTES ---

    // Crée un compte administrateur à partir d'une invitation
    public function createAccount($invitation, $username, $password)
    {
        try {
            $this->pdo->beginTransaction();

            // Vérifie si le username existe déjà
            $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $this->pdo->rollBack();
                error_log("Erreur: Username déjà existant");
                return false;
            }

            // 1. CRÉER LE RESTAURANT
            $slug = $this->generateSlug($invitation->restaurant_name);

            $stmt = $this->pdo->prepare("
            INSERT INTO restaurants (name, slug, created_at, updated_at) 
            VALUES (?, ?, NOW(), NOW())
        ");
            $stmt->execute([$invitation->restaurant_name, $slug]);
            $restaurantId = $this->pdo->lastInsertId();

            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 2. CRÉER L'ADMIN avec l'ID du restaurant
            $sql = "INSERT INTO admins (username, email, password, restaurant_name, restaurant_id, role) 
                VALUES (?, ?, ?, ?, ?, 'ADMIN')";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $username,
                $invitation->email,
                $hashedPassword,
                $invitation->restaurant_name,
                $restaurantId  // ID du restaurant créé
            ]);

            if (!$success) {
                throw new Exception("Erreur lors de l'insertion dans la table admins");
            }

            $adminId = $this->pdo->lastInsertId();

            // 3. Marquer l'invitation comme utilisée
            $sql = "UPDATE invitations SET used = 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$invitation->id]);

            if (!$success) {
                throw new Exception("Erreur lors de la mise à jour de l'invitation");
            }

            $this->pdo->commit();

            // Log de succès
            error_log("[DEBUG] Compte et restaurant créés avec succès:");
            error_log("  - Admin ID: $adminId");
            error_log("  - Username: $username");
            error_log("  - Restaurant: " . $invitation->restaurant_name);
            error_log("  - Restaurant ID créé: $restaurantId");
            error_log("  - Slug: $slug");

            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur création compte: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    // Ajoutez cette méthode pour générer un slug
    private function generateSlug($name)
    {
        // Remplace les caractères spéciaux
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/\s+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Vérifier si le slug existe déjà
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Vérifie si un slug existe déjà
    private function slugExists($slug)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM restaurants WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch() !== false;
    }

    public function getCarteMode($adminId)
    {
        $stmt = $this->pdo->prepare("SELECT carte_mode FROM admins WHERE id = ?");
        $stmt->execute([$adminId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['carte_mode'] ?? 'editable';
    }

    public function updateCarteMode($adminId, $mode)
    {
        $stmt = $this->pdo->prepare("UPDATE admins SET carte_mode = ? WHERE id = ?");
        return $stmt->execute([$mode, $adminId]);
    }

    // --- RECHERCHES / LOGIN ---

    // Trouver un admin par username
    public function findByUsername($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch();
        if ($data) {
            $this->fill($data);
            return $this;
        }
        return null;
    }

    // Trouver un admin par ID
    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            $this->fill($data);
            return $this;
        }
        return null;
    }

    // Vérifie si le mot de passe fourni correspond au hash stocké
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    // Login : retourne l'admin si les identifiants sont corrects
    public function login($username, $password)
    {
        $admin = $this->findByUsername($username);
        if ($admin && $admin->verifyPassword($password)) {
            return $admin;
        }
        return null;
    }

    // Création d’un nouvel admin (SUPER_ADMIN ou ADMIN)
    public function create($username, $password, $role, $restaurant_name)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO admins (username, password, role, restaurant_name) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$username, $hash, $role, $restaurant_name]);
        $this->id = $this->pdo->lastInsertId();
        $this->username = $username;
        $this->password = $hash;
        $this->role = $role;
        $this->restaurant_name = $restaurant_name;
        return $this;
    }

    // Remplit les propriétés de l'objet à partir d'un tableau de données
    private function fill($data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->restaurant_name = $data['restaurant_name'];
        $this->restaurant_id = $data['restaurant_id'] ?? null; // Ajoutez cette ligne
    }

    // --- RÉINITIALISATION DE MOT DE PASSE ---

    // Demande de réinitialisation : génère un token et envoie le lien par mail
    public function requestPasswordReset($email)
    {
        error_log("[DEBUG] Tentative de réinitialisation pour email: " . $email);

        // Vérifie que l'email existe
        $sql = "SELECT id FROM admins WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            error_log("[DEBUG] Email non trouvé: " . $email);
            return false;
        }

        // Génération du token et date d'expiration
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Mise à jour en base du token et de l'expiration
        $sql = "UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([$token, $expiry, $email]);

        if ($ok) {
            // Génération du lien de réinitialisation
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=reset-password&token=" . $token;
            error_log("[DEBUG] Lien de réinitialisation généré: " . $resetLink);

            // Envoi du mail
            $subject = "Réinitialisation de votre mot de passe";
            $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $resetLink;
            $headers = "From: no-reply@votrerestaurant.com";

            $mailSent = mail($email, $subject, $message, $headers);
            error_log("[DEBUG] Envoi mail: " . ($mailSent ? "OK" : "ÉCHEC"));

            return true;
        }

        error_log("[DEBUG] Échec de la mise à jour en base");
        return false;
    }

    // Réinitialisation du mot de passe avec le token
    public function resetPassword($token, $newPassword)
    {
        $sql = "SELECT id, reset_token_expiry FROM admins WHERE reset_token = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie que le token est valide et non expiré
        if (!$row) return false;
        $expiry = $row['reset_token_expiry'] ?? null;
        if (empty($expiry) || strtotime($expiry) < time()) return false;

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        // Met à jour le mot de passe et supprime le token
        $this->pdo->beginTransaction();
        try {
            $sql = "UPDATE admins SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hashed, $row['id']]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of restaurant_name
     */
    public function getRestaurant_name()
    {
        return $this->restaurant_name;
    }

    /**
     * Set the value of restaurant_name
     *
     * @return  self
     */
    public function setRestaurant_name($restaurant_name)
    {
        $this->restaurant_name = $restaurant_name;

        return $this;
    }

    /**
     * Get the value of restaurant_id
     */
    public function getRestaurant_id()
    {
        return $this->restaurant_id;
    }

    /**
     * Set the value of restaurant_id
     *
     * @return  self
     */
    public function setRestaurant_id($restaurant_id)
    {
        $this->restaurant_id = $restaurant_id;

        return $this;
    }
}
