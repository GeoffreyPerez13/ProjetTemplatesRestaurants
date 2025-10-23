<?php
class Admin
{
    private $pdo;

    public $id;
    public $username;
    public $password;
    public $role;
    public $restaurant_name;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createInvitation($email, $restaurantName, $token)
    {
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        try {
            $sql = "INSERT INTO invitations (email, restaurant_name, token, expiry) VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$email, $restaurantName, $token, $expiry]);

            if ($result) {
                $inviteLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=register&token=" . $token;
                $to = $email;
                $subject = "Invitation à créer votre compte restaurant";
                $message = "Bonjour,\n\n";
                $message .= "Vous avez été invité à créer un compte pour votre restaurant.\n";
                $message .= "Cliquez sur ce lien : " . $inviteLink;
                $headers = "From: no-reply@votrerestaurant.com";

                return mail($to, $subject, $message, $headers);
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getInvitation($token)
    {
        $sql = "SELECT * FROM invitations WHERE token = ? AND used = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result;
    }

    public function createAccount($invitation, $username, $password)
    {
        try {
            $this->pdo->beginTransaction();

            // Vérification si le username existe déjà
            $stmt = $this->pdo->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $this->pdo->rollBack();
                error_log("Erreur: Username déjà existant");
                return false;
            }

            // Hashage du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insertion du nouvel admin
            $sql = "INSERT INTO admins (username, email, password, restaurant_name, role) VALUES (?, ?, ?, ?, 'ADMIN')";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                $username,
                $invitation->email,
                $hashedPassword,
                $invitation->restaurant_name
            ]);

            if (!$success) {
                throw new Exception("Erreur lors de l'insertion dans la table admins");
            }

            // Mise à jour de l'invitation
            $sql = "UPDATE invitations SET used = 1 WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([$invitation->id]);

            if (!$success) {
                throw new Exception("Erreur lors de la mise à jour de l'invitation");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erreur création compte: " . $e->getMessage());
            error_log("Trace: " . $e->getTraceAsString());
            return false;
        }
    }

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

    // Vérifier le mot de passe
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    // Login centralisé
    public function login($username, $password)
    {
        $admin = $this->findByUsername($username);
        if ($admin && $admin->verifyPassword($password)) {
            return $admin;
        }
        return null;
    }

    // Créer un nouvel admin (SUPER_ADMIN ou ADMIN)
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

    private function fill($data)
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->restaurant_name = $data['restaurant_name'];
    }

    public function requestPasswordReset($email)
    {
        error_log("[DEBUG] Tentative de réinitialisation pour email: " . $email);

        $sql = "SELECT id FROM admins WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            error_log("[DEBUG] Email non trouvé: " . $email);
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([$token, $expiry, $email]);

        if ($ok) {
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "?page=reset-password&token=" . $token;
            error_log("[DEBUG] Lien de réinitialisation généré: " . $resetLink);

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

    public function resetPassword($token, $newPassword)
    {
        $sql = "SELECT id, reset_token_expiry FROM admins WHERE reset_token = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return false;
        }

        $expiry = $row['reset_token_expiry'] ?? null;
        if (empty($expiry) || strtotime($expiry) < time()) {
            return false;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

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
}
