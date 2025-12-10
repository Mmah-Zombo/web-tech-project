<?php
require_once 'models/User.php'; // Include other models as needed

// Demo Create
$newUserId = User::create('Test User', 'test@apexsports.com', 'testpass', 'Player');
echo "Created User ID: $newUserId\n";

// Demo Read
$users = User::read();
print_r($users);

// Demo Update
User::update($newUserId, 'Updated Test User', 'updatedtest@apexsports.com', 'newpass', 'Player');

// Demo Delete
User::delete($newUserId);
?>