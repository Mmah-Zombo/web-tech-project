<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/DB.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/PlayerProfile.php';
require_once __DIR__ . '/../models/AgentProfile.php';
require_once __DIR__ . '/../models/Club.php';
require_once __DIR__ . '/../models/Contract.php';

// Arrays for realistic data

// Admins (2, but users table will have more)
$admins = [
    ['name' => 'Abdul Kamara', 'email' => 'abdul.kamara@apexsports.sl', 'password' => 'adminpass123', 'role' => 'Admin'],
    ['name' => 'Ibrahim Sesay', 'email' => 'ibrahim.sesay@apexsports.sl', 'password' => 'adminpass123', 'role' => 'Admin'],
];

// Club Managers (7)
$clubManagers = [
    ['name' => 'Joseph Conteh', 'email' => 'joseph.conteh@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'Samuel Bangura', 'email' => 'samuel.bangura@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'James Turay', 'email' => 'james.turay@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'David Koroma', 'email' => 'david.koroma@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'Emmanuel Mansaray', 'email' => 'emmanuel.mansaray@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'Francis Lahai', 'email' => 'francis.lahai@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
    ['name' => 'Umaru Fofana', 'email' => 'umaru.fofana@apexsports.sl', 'password' => 'managerpass123', 'role' => 'ClubManager'],
];

// Clubs (7, linked to managers)
$clubs = [
    ['name' => 'Bo Rangers', 'location' => 'Bo', 'league' => 'Sierra Leone Premier'],
    ['name' => 'East End Lions', 'location' => 'Freetown', 'league' => 'Sierra Leone Premier'],
    ['name' => 'FC Kallon', 'location' => 'Freetown', 'league' => 'Sierra Leone Premier'],
    ['name' => 'Ports Authority', 'location' => 'Freetown', 'league' => 'Sierra Leone Premier'],
    ['name' => 'Central Parade', 'location' => 'Freetown', 'league' => 'Sierra Leone Premier'],
    ['name' => 'Diamond Stars', 'location' => 'Koidu', 'league' => 'Sierra Leone Premier'],
    ['name' => 'Bai Bureh Warriors', 'location' => 'Port Loko', 'league' => 'Sierra Leone Premier'],
];

// Players (7)
$players = [
    ['name' => 'Kei Kamara', 'email' => 'kei.kamara@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Mohamed Buya Turay', 'email' => 'mohamed.turay@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Alhassan Koroma', 'email' => 'alhassan.koroma@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Augustus Kargbo', 'email' => 'augustus.kargbo@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Mustapha Bundu', 'email' => 'mustapha.bundu@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Alex Bangura', 'email' => 'alex.bangura@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
    ['name' => 'Josh Koroma', 'email' => 'josh.koroma@apexsports.sl', 'password' => 'playerpass123', 'role' => 'Player'],
];

// Player Profiles (7, linked to players)
$playerProfiles = [
    ['position' => 'Forward', 'age' => 35, 'height_cm' => 188, 'weight_kg' => 82, 'preferred_foot' => 'Right', 'current_club' => 'Bo Rangers', 'nationality' => 'Sierra Leone'],
    ['position' => 'Forward', 'age' => 28, 'height_cm' => 178, 'weight_kg' => 70, 'preferred_foot' => 'Right', 'current_club' => 'East End Lions', 'nationality' => 'Sierra Leone'],
    ['position' => 'Midfielder', 'age' => 24, 'height_cm' => 175, 'weight_kg' => 68, 'preferred_foot' => 'Left', 'current_club' => 'FC Kallon', 'nationality' => 'Sierra Leone'],
    ['position' => 'Forward', 'age' => 26, 'height_cm' => 180, 'weight_kg' => 75, 'preferred_foot' => 'Right', 'current_club' => 'Ports Authority', 'nationality' => 'Sierra Leone'],
    ['position' => 'Forward', 'age' => 27, 'height_cm' => 183, 'weight_kg' => 78, 'preferred_foot' => 'Right', 'current_club' => 'Central Parade', 'nationality' => 'Sierra Leone'],
    ['position' => 'Defender', 'age' => 23, 'height_cm' => 185, 'weight_kg' => 80, 'preferred_foot' => 'Left', 'current_club' => 'Diamond Stars', 'nationality' => 'Sierra Leone'],
    ['position' => 'Forward', 'age' => 25, 'height_cm' => 182, 'weight_kg' => 76, 'preferred_foot' => 'Right', 'current_club' => 'Bai Bureh Warriors', 'nationality' => 'Sierra Leone'],
];

// Agents (7)
$agents = [
    ['name' => 'Ralph Nkomo', 'email' => 'ralph.nkomo@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'Karabo Mathang', 'email' => 'karabo.mathang@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'Lance Davids', 'email' => 'lance.davids@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'George Deda', 'email' => 'george.deda@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'Lindela Tshuma', 'email' => 'lindela.tshuma@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'Adonis Sithole', 'email' => 'adonis.sithole@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
    ['name' => 'Gibson Mahachi', 'email' => 'gibson.mahachi@apexsports.sl', 'password' => 'agentpass123', 'role' => 'Agent'],
];

// Agent Profiles (7, linked to agents)
$agentProfiles = [
    ['license_number' => 'LIC-SL-001', 'years_experience' => 15],
    ['license_number' => 'LIC-SL-002', 'years_experience' => 12],
    ['license_number' => 'LIC-SL-003', 'years_experience' => 10],
    ['license_number' => 'LIC-SL-004', 'years_experience' => 8],
    ['license_number' => 'LIC-SL-005', 'years_experience' => 7],
    ['license_number' => 'LIC-SL-006', 'years_experience' => 5],
    ['license_number' => 'LIC-SL-007', 'years_experience' => 9],
];

// Contracts (7, will link after inserting)
$contracts = [
    ['start_date' => '2024-01-01', 'end_date' => '2026-12-31', 'salary' => 150000.00, 'status' => 'Active'],
    ['start_date' => '2023-07-01', 'end_date' => '2025-06-30', 'salary' => 80000.00, 'status' => 'Active'],
    ['start_date' => '2025-01-01', 'end_date' => '2027-12-31', 'salary' => 120000.00, 'status' => 'Active'],
    ['start_date' => '2024-06-01', 'end_date' => '2026-05-31', 'salary' => 90000.00, 'status' => 'Active'],
    ['start_date' => '2023-01-01', 'end_date' => '2025-12-31', 'salary' => 100000.00, 'status' => 'Active'],
    ['start_date' => '2025-07-01', 'end_date' => '2027-06-30', 'salary' => 110000.00, 'status' => 'Active'],
    ['start_date' => '2024-01-01', 'end_date' => '2026-12-31', 'salary' => 130000.00, 'status' => 'Active'],
];

// Insert Admins (contributes to users table)
foreach ($admins as $admin) {
    User::create($admin['name'], $admin['email'], $admin['password'], $admin['role']);
}

// Insert Club Managers and Clubs
$clubManagerIds = [];
$clubIds = [];
foreach ($clubManagers as $index => $manager) {
    $managerId = User::create($manager['name'], $manager['email'], $manager['password'], $manager['role']);
    $clubManagerIds[] = $managerId;
    $clubId = Club::create($clubs[$index]['name'], $clubs[$index]['location'], $clubs[$index]['league'], $managerId);
    $clubIds[] = $clubId;
}

// Insert Players and Profiles
$playerIds = [];
foreach ($players as $index => $player) {
    $playerId = User::create($player['name'], $player['email'], $player['password'], $player['role']);
    $playerIds[] = $playerId;
    PlayerProfile::create($playerId, $playerProfiles[$index]['position'], $playerProfiles[$index]['age'], $playerProfiles[$index]['height_cm'], $playerProfiles[$index]['weight_kg'], $playerProfiles[$index]['preferred_foot'], $playerProfiles[$index]['current_club'], $playerProfiles[$index]['nationality']);
}

// Insert Agents and Profiles
$agentIds = [];
foreach ($agents as $index => $agent) {
    $agentId = User::create($agent['name'], $agent['email'], $agent['password'], $agent['role']);
    $agentIds[] = $agentId;
    AgentProfile::create($agentId, $agentProfiles[$index]['license_number'], $agentProfiles[$index]['years_experience']);
}

// Insert Contracts (link sequentially for simplicity)
foreach ($contracts as $index => $contract) {
    $playerIndex = $index % 7; // Cycle through 0-6
    $clubIndex = $index % 7;
    $agentIndex = $index % 7;
    Contract::create($playerIds[$playerIndex], $clubIds[$clubIndex], $agentIds[$agentIndex], $contract['start_date'], $contract['end_date'], $contract['salary'], $contract['status']);
}

echo "Realistic data inserted successfully into all tables.\n";
?>