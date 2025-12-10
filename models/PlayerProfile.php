<?php
require_once __DIR__ . '/DB.php';

class PlayerProfile {
    // Create a new player profile
    public static function create($user_id, $position, $age, $height_cm, $weight_kg, $preferred_foot, $current_club, $nationality) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("INSERT INTO player_profiles (user_id, position, age, height_cm, weight_kg, preferred_foot, current_club, nationality) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiisss", $user_id, $position, $age, $height_cm, $weight_kg, $preferred_foot, $current_club, $nationality);
        return $stmt->execute();
    }

    // Read all or by ID
    public static function read($id = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM player_profiles WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $conn->query("SELECT * FROM player_profiles");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update by ID
    public static function update($id, $position, $age, $height_cm, $weight_kg, $preferred_foot, $current_club, $nationality) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("UPDATE player_profiles SET position = ?, age = ?, height_cm = ?, weight_kg = ?, preferred_foot = ?, current_club = ?, nationality = ? WHERE id = ?");
        $stmt->bind_param("siiisssi", $position, $age, $height_cm, $weight_kg, $preferred_foot, $current_club, $nationality, $id);
        return $stmt->execute();
    }

    // Delete by ID
    public static function delete($id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("DELETE FROM player_profiles WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>