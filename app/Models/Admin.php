<?php
class Admin {
    private $pdo;

    public $id;
    public $username;
    public $password;
    public $role;
    public $restaurant_name;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Trouver un admin par username
    public function findByUsername($username) {
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
    public function findById($id) {
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
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    // Login centralisé
    public function login($username, $password) {
        $admin = $this->findByUsername($username);
        if ($admin && $admin->verifyPassword($password)) {
            return $admin;
        }
        return null;
    }

    // Créer un nouvel admin (SUPER_ADMIN ou ADMIN)
    public function create($username, $password, $role, $restaurant_name) {
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

    private function fill($data) {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->role = $data['role'];
        $this->restaurant_name = $data['restaurant_name'];
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
?>
