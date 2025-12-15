<?php
require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/User.php';

class Club {
    // Create
    public static function create($name, $location, $league, $manager_user_id = null, $manager_email = null) {
        if ($manager_user_id !== null && $manager_email !== null) {
            return false; // Cannot provide both manager_user_id and manager_email
        }
        if ($manager_user_id === null && $manager_email === null) {
            return false; // Must provide either manager_user_id or manager_email
        }
        if ($manager_email !== null) {
            $user = User::read(null, $manager_email);
            if (!$user) {
                return false; // User not found
            }
            $manager_user_id = $user['id'];
        }
        $conn = DB::getConnection();
        $stmt = $conn->prepare("INSERT INTO clubs (name, location, league, manager_user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $location, $league, $manager_user_id);
        if ($stmt->execute()) {
            return $conn->insert_id;
        }
        return false;
    }

    // Read
    public static function read($id = null, $manager_user_id = null, $manager_email = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id WHERE c.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } elseif ($manager_user_id) {
            $stmt = $conn->prepare("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id WHERE c.manager_user_id = ?");
            $stmt->bind_param("i", $manager_user_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } elseif ($manager_email) {
            $stmt = $conn->prepare("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id WHERE u.email = ?");
            $stmt->bind_param("s", $manager_email);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            $result = $conn->query("SELECT c.*, u.name as manager_name FROM clubs c LEFT JOIN users u ON c.manager_user_id = u.id");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update
    public static function update($id, $name, $location, $league) {
        // if ($manager_user_id !== null && $manager_email !== null) {
        //     return false; // Cannot provide both manager_user_id and manager_email
        // }
        // if ($manager_user_id === null && $manager_email === null) {
        //     return false; // Must provide either manager_user_id or manager_email
        // }
        // if ($manager_email !== null) {
        //     $user = User::read(null, $manager_email);
        //     if (!$user) {
        //         return false; // User not found
        //     }
        //     $manager_user_id = $user['id'];
        // }
        $conn = DB::getConnection();
        $stmt = $conn->prepare("UPDATE clubs SET name = ?, location = ?, league = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $location, $league, $id);
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