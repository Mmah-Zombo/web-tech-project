<?php
// Database configuration
$servername = "localhost";  // Change if needed
$username = "root";         // Your MySQL username
$password = "";             // Your MySQL password
$dbname = "football_agent"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists.\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

// Select the database
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Player', 'Agent', 'ClubManager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully.\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

// Create player_profiles table
$sql = "CREATE TABLE IF NOT EXISTS player_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    position VARCHAR(50),
    age INT,
    height_cm INT,
    weight_kg INT,
    preferred_foot ENUM('Left', 'Right', 'Both'),
    current_club VARCHAR(255),
    nationality VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Player profiles table created successfully.\n";
} else {
    echo "Error creating player_profiles table: " . $conn->error . "\n";
}

// Create agent_profiles table
$sql = "CREATE TABLE IF NOT EXISTS agent_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(50),
    years_experience INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Agent profiles table created successfully.\n";
} else {
    echo "Error creating agent_profiles table: " . $conn->error . "\n";
}

// Create clubs table
$sql = "CREATE TABLE IF NOT EXISTS clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    league VARCHAR(255),
    manager_user_id INT,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
)";
if ($conn->query($sql) === TRUE) {
    echo "Clubs table created successfully.\n";
} else {
    echo "Error creating clubs table: " . $conn->error . "\n";
}

// Create contracts table
$sql = "CREATE TABLE IF NOT EXISTS contracts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_user_id INT NOT NULL,
    club_id INT NOT NULL,
    agent_user_id INT NOT NULL,
    start_date DATE,
    end_date DATE,
    salary DECIMAL(10,2),
    status ENUM('Active', 'Expired', 'Terminated') DEFAULT 'Active',
    FOREIGN KEY (player_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
    echo "Contracts table created successfully.\n";
} else {
    echo "Error creating contracts table: " . $conn->error . "\n";
}

// Insert sample data
// Note: Passwords are plain text for demo; in production, use hashing like password_hash()

// Admins
$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Admin One', 'admin1@apexsports.com', 'adminpass1', 'Admin'),
    ('Admin Two', 'admin2@apexsports.com', 'adminpass2', 'Admin')";
$conn->query($sql);

// Players
$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Player One', 'player1@apexsports.com', 'playerpass1', 'Player')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_profile = "INSERT INTO player_profiles (user_id, position, age, height_cm, weight_kg, preferred_foot, current_club, nationality) VALUES 
        ($user_id, 'Forward', 25, 185, 80, 'Right', 'Club A', 'Sierra Leone')";
    $conn->query($sql_profile);
}

$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Player Two', 'player2@apexsports.com', 'playerpass2', 'Player')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_profile = "INSERT INTO player_profiles (user_id, position, age, height_cm, weight_kg, preferred_foot, current_club, nationality) VALUES 
        ($user_id, 'Midfielder', 22, 178, 72, 'Left', 'Club B', 'Sierra Leone')";
    $conn->query($sql_profile);
}

// Agents
$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Agent One', 'agent1@apexsports.com', 'agentpass1', 'Agent')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_profile = "INSERT INTO agent_profiles (user_id, license_number, years_experience) VALUES 
        ($user_id, 'LIC001', 10)";
    $conn->query($sql_profile);
}

$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Agent Two', 'agent2@apexsports.com', 'agentpass2', 'Agent')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_profile = "INSERT INTO agent_profiles (user_id, license_number, years_experience) VALUES 
        ($user_id, 'LIC002', 8)";
    $conn->query($sql_profile);
}

// Club Managers and Clubs
$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Manager One', 'manager1@apexsports.com', 'managerpass1', 'ClubManager')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_club = "INSERT INTO clubs (name, location, league, manager_user_id) VALUES 
        ('Club A', 'Freetown', 'Sierra Leone Premier', $user_id)";
    $conn->query($sql_club);
}

$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES 
    ('Manager Two', 'manager2@apexsports.com', 'managerpass2', 'ClubManager')";
if ($conn->query($sql) === TRUE && $conn->affected_rows > 0) {
    $user_id = $conn->insert_id;
    $sql_club = "INSERT INTO clubs (name, location, league, manager_user_id) VALUES 
        ('Club B', 'Bo', 'Sierra Leone Premier', $user_id)";
    $conn->query($sql_club);
}

// Sample Contracts (assuming IDs: players 3-4, agents 5-6, clubs 1-2)
$sql = "INSERT IGNORE INTO contracts (player_user_id, club_id, agent_user_id, start_date, end_date, salary, status) VALUES 
    (3, 1, 5, '2025-01-01', '2027-12-31', 100000.00, 'Active'),
    (4, 2, 6, '2025-06-01', '2026-05-31', 80000.00, 'Active')";
$conn->query($sql);

echo "Sample data inserted successfully.\n";

// Close connection
$conn->close();
?>