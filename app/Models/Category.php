<?php
class Category
{
    private $pdo;

    public $id;
    public $admin_id;
    public $name;
    public $image;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($admin_id, $name, $image = null)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO categories (admin_id, name, image) VALUES (?, ?, ?)"
        );
        $stmt->execute([$admin_id, $name, $image]);
        $this->id = $this->pdo->lastInsertId();
        $this->admin_id = $admin_id;
        $this->name = $name;
        $this->image = $image;
        return $this;
    }

    public function update($id, $name, $image = null)
    {
        $sql = "UPDATE categories SET name = ?";
        $params = [$name];

        if ($image !== null) {
            $sql .= ", image = ?";
            $params[] = $image;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getAllByAdmin($admin_id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM categories WHERE admin_id = ? ORDER BY id ASC"
        );
        $stmt->execute([$admin_id]);
        return $stmt->fetchAll();
    }

    public function delete($id, $admin_id)
    {
        // Supprimer les plats liés
        $stmt = $this->pdo->prepare("DELETE FROM plats WHERE category_id = ?");
        $stmt->execute([$id]);

        $stmt = $this->pdo->prepare(
            "DELETE FROM categories WHERE id = ? AND admin_id = ?"
        );
        return $stmt->execute([$id, $admin_id]);
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll();
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
     * Get the value of admin_id
     */
    public function getAdmin_id()
    {
        return $this->admin_id;
    }

    /**
     * Set the value of admin_id
     *
     * @return  self
     */
    public function setAdmin_id($admin_id)
    {
        $this->admin_id = $admin_id;

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
     * Get the value of image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
