<?php
class Contact {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByAdmin($admin_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM contact WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        return $stmt->fetch();
    }

    public function update($admin_id, $telephone, $email, $adresse, $horaires) {
        $stmt = $this->pdo->prepare(
            "UPDATE contact SET telephone = ?, email = ?, adresse = ?, horaires = ? WHERE admin_id = ?"
        );
        return $stmt->execute([$telephone, $email, $adresse, $horaires, $admin_id]);
    }

    public function createIfNotExist($admin_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM contact WHERE admin_id = ?");
        $stmt->execute([$admin_id]);
        if (!$stmt->fetch()) {
            $stmt = $this->pdo->prepare(
                "INSERT INTO contact (admin_id, telephone, email, adresse, horaires) VALUES (?, '', '', '', '')"
            );
            $stmt->execute([$admin_id]);
        }
    }
}
?>
