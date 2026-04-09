<?php
// User Controller

class UserController {
    
    public function getUserById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>
