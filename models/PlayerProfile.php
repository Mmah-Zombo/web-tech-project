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
    public static function read($id = null, $user_id = null, $email = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT pp.*, u.name, u.email, u.role FROM player_profiles pp INNER JOIN users u ON pp.user_id = u.id WHERE pp.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif ($user_id) {
            $stmt = $conn->prepare("SELECT pp.*, u.name, u.email, u.role FROM player_profiles pp INNER JOIN users u ON pp.user_id = u.id WHERE pp.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif ($email) {
            $stmt = $conn->prepare("SELECT pp.*, u.name, u.email, u.role FROM player_profiles pp INNER JOIN users u ON pp.user_id = u.id WHERE u.email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $conn->query("SELECT pp.*, u.name, u.email, u.role FROM player_profiles pp INNER JOIN users u ON pp.user_id = u.id");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update by ID
    public static function update($id, $position, $age, $height_cm, $weight_kg, $preferred_foot, $current_club, $nationality) {
        $conn = DB::getConnection();
        $where = "id";
        if (!is_numeric($id)) {
            // Treat as email
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if (!$user) {
                return false; // User not found
            }
            $id = $user['id'];
            $where = "user_id";
        }
        $stmt = $conn->prepare("UPDATE player_profiles SET position = ?, age = ?, height_cm = ?, weight_kg = ?, preferred_foot = ?, current_club = ?, nationality = ? WHERE $where = ?");
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