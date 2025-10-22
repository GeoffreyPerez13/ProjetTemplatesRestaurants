<?php
class Dish {
    private $pdo;

    public $id;
    public $category_id;
    public $name;
    public $price;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($category_id, $name, $price) {
        $price = floatval($price);
        $stmt = $this->pdo->prepare(
            "INSERT INTO plats (category_id, name, price) VALUES (?, ?, ?)"
        );
        $stmt->execute([$category_id, $name, $price]);
        $this->id = $this->pdo->lastInsertId();
        $this->category_id = $category_id;
        $this->name = $name;
        $this->price = $price;
        return $this;
    }

    public function update($id, $name, $price) {
        $price = floatval($price);
        $stmt = $this->pdo->prepare("UPDATE plats SET name = ?, price = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM plats WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllByCategory($category_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM plats WHERE category_id = ?");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    }

    // Formater le prix pour affichage
    public function formatPrice($price) {
        return number_format($price, 2, ',', '') . '€';
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
     * Get the value of category_id
     */ 
    public function getCategory_id()
    {
        return $this->category_id;
    }

    /**
     * Set the value of category_id
     *
     * @return  self
     */ 
    public function setCategory_id($category_id)
    {
        $this->category_id = $category_id;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
}
?>
