<?php
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/User.php';

class AgentProfile {
    // Create
    public static function create($user_id = null, $email = null, $license_number, $years_experience) {
        if ($user_id !== null && $email !== null) {
            return false; // Cannot provide both user_id and email
        }
        if ($user_id === null && $email === null) {
            return false; // Must provide either user_id or email
        }
        if ($email !== null) {
            $user = User::read(null, $email);
            if (!$user) {
                return false; // User not found
            }
            $user_id = $user['id'];
        }
        $conn = DB::getConnection();
        $stmt = $conn->prepare("INSERT INTO agent_profiles (user_id, license_number, years_experience) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $license_number, $years_experience);
        return $stmt->execute();
    }

    // Read
    public static function read($id = null, $user_id = null, $email = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT ap.*, u.name, u.email, u.role FROM agent_profiles ap INNER JOIN users u ON ap.user_id = u.id WHERE ap.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif ($user_id) {
            $stmt = $conn->prepare("SELECT ap.*, u.name, u.email, u.role FROM agent_profiles ap INNER JOIN users u ON ap.user_id = u.id WHERE ap.user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif ($email) {
            $stmt = $conn->prepare("SELECT ap.*, u.name, u.email, u.role FROM agent_profiles ap INNER JOIN users u ON ap.user_id = u.id WHERE u.email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $conn->query("SELECT ap.*, u.name, u.email, u.role FROM agent_profiles ap INNER JOIN users u ON ap.user_id = u.id");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update
    public static function update($id, $license_number, $years_experience) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("UPDATE agent_profiles SET license_number = ?, years_experience = ? WHERE id = ?");
        $stmt->bind_param("sii", $license_number, $years_experience, $id);
        return $stmt->execute();
    }

    // Delete
    public static function delete($id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("DELETE FROM agent_profiles WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>