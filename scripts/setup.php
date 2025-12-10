<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/DB.php'; // For connection
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PlayerProfile.php';
require_once __DIR__ . '/../models/AgentProfile.php';
require_once __DIR__ . '/../models/Club.php';
require_once __DIR__ . '/../models/Contract.php';

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
$conn->query($sql);

// Select DB
$conn->select_db(DB_NAME);

// Create tables (same as before)
$sqlUsers = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Player', 'Agent', 'ClubManager') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sqlUsers);

$sqlPlayerProfiles = "CREATE TABLE IF NOT EXISTS player_profiles (
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
$conn->query($sqlPlayerProfiles);

$sqlAgentProfiles = "CREATE TABLE IF NOT EXISTS agent_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    license_number VARCHAR(50),
    years_experience INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->query($sqlAgentProfiles);

$sqlClubs = "CREATE TABLE IF NOT EXISTS clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    league VARCHAR(255),
    manager_user_id INT,
    FOREIGN KEY (manager_user_id) REFERENCES users(id) ON DELETE SET NULL
)";
$conn->query($sqlClubs);

$sqlContracts = "CREATE TABLE IF NOT EXISTS contracts (
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
$conn->query($sqlContracts);

// Insert sample data using model methods
// Admins
User::create('Admin One', 'admin1@apexsports.com', 'adminpass1', 'Admin');
User::create('Admin Two', 'admin2@apexsports.com', 'adminpass2', 'Admin');

// Players
$player1Id = User::create('Player One', 'player1@apexsports.com', 'playerpass1', 'Player');
PlayerProfile::create($player1Id, 'Forward', 25, 185, 80, 'Right', 'Club A', 'Sierra Leone');

$player2Id = User::create('Player Two', 'player2@apexsports.com', 'playerpass2', 'Player');
PlayerProfile::create($player2Id, 'Midfielder', 22, 178, 72, 'Left', 'Club B', 'Sierra Leone');

// Agents
$agent1Id = User::create('Agent One', 'agent1@apexsports.com', 'agentpass1', 'Agent');
AgentProfile::create($agent1Id, 'LIC001', 10);

$agent2Id = User::create('Agent Two', 'agent2@apexsports.com', 'agentpass2', 'Agent');
AgentProfile::create($agent2Id, 'LIC002', 8);

// Club Managers and Clubs
$manager1Id = User::create('Manager One', 'manager1@apexsports.com', 'managerpass1', 'ClubManager');
$club1Id = Club::create('Club A', 'Freetown', 'Sierra Leone Premier', $manager1Id);

$manager2Id = User::create('Manager Two', 'manager2@apexsports.com', 'managerpass2', 'ClubManager');
$club2Id = Club::create('Club B', 'Bo', 'Sierra Leone Premier', $manager2Id);

// Contracts
Contract::create($player1Id, $club1Id, $agent1Id, '2025-01-01', '2027-12-31', 100000.00);
Contract::create($player2Id, $club2Id, $agent2Id, '2025-06-01', '2026-05-31', 80000.00);

echo "Setup completed successfully.\n";

$conn->close();
?>