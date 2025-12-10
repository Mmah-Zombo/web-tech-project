<?php
require_once __DIR__ . '/DB.php';

class Contract {
    // Create
    public static function create($player_user_id, $club_id, $agent_user_id, $start_date, $end_date, $salary, $status = 'Active') {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("INSERT INTO contracts (player_user_id, club_id, agent_user_id, start_date, end_date, salary, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissds", $player_user_id, $club_id, $agent_user_id, $start_date, $end_date, $salary, $status);
        return $stmt->execute();
    }

    // Read
    public static function read($id = null) {
        $conn = DB::getConnection();
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM contracts WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        } else {
            $result = $conn->query("SELECT * FROM contracts");
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Update
    public static function update($id, $start_date, $end_date, $salary, $status) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("UPDATE contracts SET start_date = ?, end_date = ?, salary = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $start_date, $end_date, $salary, $status, $id);
        return $stmt->execute();
    }

    // Delete
    public static function delete($id) {
        $conn = DB::getConnection();
        $stmt = $conn->prepare("DELETE FROM contracts WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>