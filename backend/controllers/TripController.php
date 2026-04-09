<?php
// Trip Controller - Handles trip related operations

class TripController {
    
    public function createTrip($user_id, $title, $description, $image_url, $youtube_link) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO trips (user_id, title, description, image_url, youtube_link) 
                              VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $title, $description, $image_url, $youtube_link]);
    }

    public function getAllTrips() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT t.*, u.username FROM trips t 
                              JOIN users u ON t.user_id = u.id 
                              ORDER BY t.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
