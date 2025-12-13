<?php
require_once __DIR__ . '/DB.php';

class User {
    // Create a new user
    public static function create($name, $email, $password, $role) {
        $conn = DB::getConnection();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Secure hashing
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
        if ($stmt->execute()) {
            return $conn->insert_id; // Return new user ID
        }
        return false;
    }

    // Read all users or a single user by ID
    public static function read($id = null, $email = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif($email) {
            $stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
            $stmt->bind_param("i", $email);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        else {
            $result = $conn->query("SELECT id, name, email, role FROM users");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update user by ID
    public static function update($id, $name, $email, $role, $password = null) {
        $conn = DB::getConnection();
        $hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        if ($hashedPassword) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $hashedPassword, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $role, $id);
        }
        return $stmt->execute();
    }

    // Delete user by ID
    public static function delete($id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>