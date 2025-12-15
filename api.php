<?php
// api.php (updated with validations, auth endpoints, and JWT)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/DB.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/PlayerProfile.php';
require_once __DIR__ . '/models/AgentProfile.php';
require_once __DIR__ . '/models/Club.php';
require_once __DIR__ . '/models/Contract.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

if ($uri[0] !== 'api') {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
    exit;
}

$resource = $uri[1] ?? null;
// $subresource = isset($uri[2]) ? $uri[2] : null;
$id = isset($uri[2]) ? (int)$uri[2] : null;

if (!$resource) {
    http_response_code(404);
    echo json_encode(['error' => 'Resource not specified']);
    exit;
}

// JWT Functions
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

function generateJWT($user) {
    $secret = JWT_SECRET;
    $header = base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64UrlEncode(json_encode([
        'id' => $user['id'],
        'role' => $user['role'],
        'exp' => time() + 3600 // 1 hour
    ]));
    $signature = base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));
    return $header . '.' . $payload . '.' . $signature;
}

function verifyJWT($token) {
    $secret = JWT_SECRET;
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    list($header, $payload, $signature) = $parts;
    $calc_signature = base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $secret, true));
    if ($signature !== $calc_signature) return false;
    $data = json_decode(base64UrlDecode($payload), true);
    if (time() > $data['exp']) return false;
    return $data;
}

// Auth check for protected methods
function checkAuth() {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (strpos($auth, 'Bearer ') === 0) {
        $token = substr($auth, 7);
        $user = verifyJWT($token);
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        return $user;
    }
    http_response_code(401);
    echo json_encode(['error' => 'Authorization required']);
    exit;
}

switch ($resource) {
    case 'users':
        if ($method !== 'GET') checkAuth();
        handleUser($method, $id);
        break;
    case 'player_profiles':
        if ($method !== 'GET') checkAuth();
        $id = isset($uri[2]) ? $uri[2] : null;
        handlePlayerProfile($method, $id);
        break;
    case 'agent_profiles':
        if ($method !== 'GET') checkAuth();
        $id = isset($uri[2]) ? $uri[2] : null;
        handleAgentProfile($method, $id);
        break;
    case 'clubs':
        if ($method !== 'GET') checkAuth();
        $id = isset($uri[2]) ? $uri[2] : null;
        handleClub($method, $id);
        break;
    case 'contracts':
        if ($method !== 'GET') checkAuth();
        handleContract($method, $id);
        break;
    case 'auth':
        $subresource = isset($uri[2]) ? $uri[2] : null;
        handleAuth($method, $subresource);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Resource not found']);
        exit;
}

function handleAuth($method, $sub) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if ($sub === 'register') {
        if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        // Validations
        if (strlen($data['name']) < 2) {
            http_response_code(400);
            echo json_encode(['error' => 'Name too short']);
            exit;
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email']);
            exit;
        }
        if (strlen($data['password']) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password too short']);
            exit;
        }
        if (!in_array(strtolower($data['role']), ['player', 'agent', 'clubmanager'])) { // Exclude Admin for register
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role']);
            exit;
        }
        $userId = User::create($data['name'], $data['email'], $data['password'], $data['role']);
        if ($userId) {
            // Optionally create profile based on role
            // For simplicity, assume separate calls
            echo json_encode(['id' => $userId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register']);
        }
    } elseif ($sub === 'login') {
        if (!isset($data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        $conn = DB::getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }
        $token = generateJWT($user);
        echo json_encode(['token' => $token, 'role' => $user['role']]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Subresource not found']);
    }
}

function handleUser($method, $id) {
    switch ($method) {
        case 'GET':
            // No validation needed
            if ($id) {
                $result = User::read(id:$id);
            } else {
                $result = User::read();
            }
            echo json_encode($result ?: ['error' => 'Not found']);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['name']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Name too short']);
                exit;
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email']);
                exit;
            }
            if (strlen($data['password']) < 8) {
                http_response_code(400);
                echo json_encode(['error' => 'Password too short']);
                exit;
            }
            if (!in_array($data['role'], ['Admin', 'Player', 'Agent', 'ClubManager'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid role']);
                exit;
            }
            $result = User::create($data['name'], $data['email'], $data['password'], $data['role']);
            if ($result) {
                echo json_encode(['id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create user']);
            }
            break;
        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name'], $data['email'], $data['role'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['name']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Name too short']);
                exit;
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid email']);
                exit;
            }
            if (!in_array($data['role'], ['Admin', 'Player', 'Agent', 'ClubManager'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid role']);
                exit;
            }
            if (isset($data['password']) && strlen($data['password']) < 8) {
                http_response_code(400);
                echo json_encode(['error' => 'Password too short']);
                exit;
            }
            $password = isset($data['password']) ? $data['password'] : null;
            $result = User::update($id, $data['name'], $data['email'], $password, $data['role']);
            echo json_encode(['success' => $result]);
            break;
        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit;
            }
            $result = User::delete($id);
            echo json_encode(['success' => $result]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handlePlayerProfile($method, $id) {
    switch ($method) {
        case 'GET':
            if (is_numeric($id)) {
                $result = PlayerProfile::read(id:$id);
            } elseif (is_string($id)) {
                $result = PlayerProfile::read(email:$id);
            } else {
                $result = PlayerProfile::read();
            }
            echo json_encode($result ?: ['error' => 'Not found']);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['user_id'], $data['position'], $data['age'], $data['height_cm'], $data['weight_kg'], $data['preferred_foot'], $data['current_club'], $data['nationality'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (isset($data['user_id'])) {
                if (!is_int($data['user_id']) || $data['user_id'] <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid user_id']);
                    exit;
                }
            }
            if (strlen($data['position']) < 3) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid position']);
                exit;
            }
            if (!is_int($data['age']) || $data['age'] < 15 || $data['age'] > 50) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid age']);
                exit;
            }
            if (!is_int($data['height_cm']) || $data['height_cm'] < 150 || $data['height_cm'] > 220) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid height']);
                exit;
            }
            if (!is_int($data['weight_kg']) || $data['weight_kg'] < 50 || $data['weight_kg'] > 120) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid weight']);
                exit;
            }
            if (!in_array($data['preferred_foot'], ['Left', 'Right', 'Both'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid preferred_foot']);
                exit;
            }
            if (strlen($data['current_club']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid current_club']);
                exit;
            }
            if (strlen($data['nationality']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid nationality']);
                exit;
            }
            $result = PlayerProfile::create($data['user_id'], $data['position'], $data['age'], $data['height_cm'], $data['weight_kg'], $data['preferred_foot'], $data['current_club'], $data['nationality']);
            echo json_encode(['success' => $result]);
            break;
        case 'PUT':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['position'], $data['age'], $data['height_cm'], $data['weight_kg'], $data['preferred_foot'], $data['current_club'], $data['nationality'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['position']) < 3) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid position']);
                exit;
            }
            if (!is_int($data['age']) || $data['age'] < 15 || $data['age'] > 50) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid age']);
                exit;
            }
            if (!is_int($data['height_cm']) || $data['height_cm'] < 150 || $data['height_cm'] > 220) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid height']);
                exit;
            }
            if (!is_int($data['weight_kg']) || $data['weight_kg'] < 50 || $data['weight_kg'] > 120) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid weight']);
                exit;
            }
            if (!in_array($data['preferred_foot'], ['Left', 'Right', 'Both'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid preferred_foot']);
                exit;
            }
            if (strlen($data['current_club']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid current_club']);
                exit;
            }
            if (strlen($data['nationality']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid nationality']);
                exit;
            }
            $result = PlayerProfile::update($id, $data['position'], $data['age'], $data['height_cm'], $data['weight_kg'], $data['preferred_foot'], $data['current_club'], $data['nationality']);
            echo json_encode(['success' => $result]);
            break;
        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $result = PlayerProfile::delete($id);
            echo json_encode(['success' => $result]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleAgentProfile($method, $id) {
    switch ($method) {
        case 'GET':
            if (is_numeric($id)) {
                $result = AgentProfile::read(id:$id);
            } elseif (is_string($id)) {
                $result = AgentProfile::read(email:$id);
            } else {
                $result = AgentProfile::read();
            }
            echo json_encode($result ?: ['error' => 'Not found']);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['license_number'], $data['years_experience']) || (!isset($data['user_id']) && !isset($data['email']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields. Must provide license_number, years_experience, and either user_id or email']);
                exit;
            }
            // Validations
            if (isset($data['user_id'])) {
                if (!is_int($data['user_id']) || $data['user_id'] <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid user_id']);
                    exit;
                }
            }
            if (isset($data['email'])) {
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid email']);
                    exit;
                }
            }
            if (strlen($data['license_number']) < 5) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid license_number']);
                exit;
            }
            if (!is_int($data['years_experience']) || $data['years_experience'] < 0 || $data['years_experience'] > 50) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid years_experience']);
                exit;
            }
            $result = AgentProfile::create($data['user_id'] ?? null, $data['email'] ?? null, $data['license_number'], $data['years_experience']);
            echo json_encode(['success' => $result]);
            break;
        case 'PUT':
            if (!$id || (is_numeric($id) && $id <= 0)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['license_number'], $data['years_experience'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['license_number']) < 5) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid license_number']);
                exit;
            }
            if (!is_int($data['years_experience']) || $data['years_experience'] < 0 || $data['years_experience'] > 50) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid years_experience']);
                exit;
            }
            $result = AgentProfile::update($id, $data['license_number'], $data['years_experience']);
            echo json_encode(['success' => $result]);
            break;
        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $result = AgentProfile::delete($id);
            echo json_encode(['success' => $result]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleClub($method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $result = Club::read($id);
            } else {
                $result = Club::read();
            }
            echo json_encode($result ?: ['error' => 'Not found']);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name'], $data['location'], $data['league']) || (!isset($data['manager_user_id']) && !isset($data['manager_email']))) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['name']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid name']);
                exit;
            }
            if (strlen($data['location']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid location']);
                exit;
            }
            if (strlen($data['league']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid league']);
                exit;
            }
            if (isset($data['manager_user_id'])) {
                if (!is_int($data['manager_user_id']) || $data['manager_user_id'] <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid manager_user_id']);
                    exit;
                }
            }
            $result = Club::create($data['name'], $data['location'], $data['league'], $data['manager_user_id'] ?? null, $data['manager_email'] ?? null);
            if ($result) {
                echo json_encode(['id' => $result]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create club']);
            }
            break;
        case 'PUT':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name'], $data['location'], $data['league'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (strlen($data['name']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid name']);
                exit;
            }
            if (strlen($data['location']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid location']);
                exit;
            }
            if (strlen($data['league']) < 2) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid league']);
                exit;
            }
            // if (isset($data['manager_user_id'])) {
            //     if (!is_int($data['manager_user_id']) || $data['manager_user_id'] <= 0) {
            //         http_response_code(400);
            //         echo json_encode(['error' => 'Invalid manager_user_id']);
            //         exit;
            //     }
            // }

            $result = Club::update($id, $data['name'], $data['location'], $data['league']);

            echo json_encode(['success' => $result]);
            break;
        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $result = Club::delete($id);
            echo json_encode(['success' => $result]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleContract($method, $id) {
    switch ($method) {
        case 'GET':
            if ($id) {
                $result = Contract::read($id);
            } else {
                $result = Contract::read();
            }
            echo json_encode($result ?: ['error' => 'Not found']);
            break;
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['player_user_id'], $data['club_id'], $data['agent_user_id'], $data['start_date'], $data['end_date'], $data['salary'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            if (!is_int($data['player_user_id']) || $data['player_user_id'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid player_user_id']);
                exit;
            }
            if (!is_int($data['club_id']) || $data['club_id'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid club_id']);
                exit;
            }
            if (!is_int($data['agent_user_id']) || $data['agent_user_id'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid agent_user_id']);
                exit;
            }
            $start = DateTime::createFromFormat('Y-m-d', $data['start_date']);
            if (!$start || $start->format('Y-m-d') !== $data['start_date']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid start_date format (YYYY-MM-DD)']);
                exit;
            }
            $end = DateTime::createFromFormat('Y-m-d', $data['end_date']);
            if (!$end || $end->format('Y-m-d') !== $data['end_date']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid end_date format (YYYY-MM-DD)']);
                exit;
            }
            if ($start >= $end) {
                http_response_code(400);
                echo json_encode(['error' => 'start_date must be before end_date']);
                exit;
            }
            if (!is_numeric($data['salary']) || $data['salary'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid salary']);
                exit;
            }
            $status = isset($data['status']) ? $data['status'] : 'Active';
            if (!in_array($status, ['Active', 'Expired', 'Terminated'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid status']);
                exit;
            }
            $result = Contract::create($data['player_user_id'], $data['club_id'], $data['agent_user_id'], $data['start_date'], $data['end_date'], $data['salary'], $status);
            echo json_encode(['success' => $result]);
            break;
        case 'PUT':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['start_date'], $data['end_date'], $data['salary'], $data['status'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            // Validations
            $start = DateTime::createFromFormat('Y-m-d', $data['start_date']);
            if (!$start || $start->format('Y-m-d') !== $data['start_date']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid start_date format (YYYY-MM-DD)']);
                exit;
            }
            $end = DateTime::createFromFormat('Y-m-d', $data['end_date']);
            if (!$end || $end->format('Y-m-d') !== $data['end_date']) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid end_date format (YYYY-MM-DD)']);
                exit;
            }
            if ($start >= $end) {
                http_response_code(400);
                echo json_encode(['error' => 'start_date must be before end_date']);
                exit;
            }
            if (!is_numeric($data['salary']) || $data['salary'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid salary']);
                exit;
            }
            if (!in_array($data['status'], ['Active', 'Expired', 'Terminated'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid status']);
                exit;
            }
            $result = Contract::update($id, $data['start_date'], $data['end_date'], $data['salary'], $data['status']);
            echo json_encode(['success' => $result]);
            break;
        case 'DELETE':
            if (!$id || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid ID required']);
                exit;
            }
            $result = Contract::delete($id);
            echo json_encode(['success' => $result]);
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

?>