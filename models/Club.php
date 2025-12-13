<?php
require_once __DIR__ . '/DB.php';

class Club {
    // Create
    public static function create($name, $location, $league, $manager_user_id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("INSERT INTO clubs (name, location, league, manager_user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $location, $league, $manager_user_id);
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        return false;
    }

    // Read
    public static function read($id = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $conn->query("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update
    public static function update($id, $name, $location, $league, $manager_user_id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("UPDATE clubs SET name = ?, location = ?, league = ?, manager_user_id = ? WHERE id = ?");
        $stmt->bind_param("sssii", $name, $location, $league, $manager_user_id, $id);
        return $stmt->execute();
    }

    // Delete
    public static function delete($id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("DELETE FROM clubs WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>