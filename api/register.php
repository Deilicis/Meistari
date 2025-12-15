<?php
// api/register.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests (Browser security check)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Include Database Connection
include_once 'db.php';

// 3. Get the posted data
$data = json_decode(file_get_contents("php://input"));

// Check if data is not empty
if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->role) // 'seeker' or 'master'
) {
    // 4. Input Validation & Cleaning
    $username = htmlspecialchars(strip_tags($data->username));
    $email = htmlspecialchars(strip_tags($data->email));
    $role = htmlspecialchars(strip_tags($data->role));
    $password = $data->password;

    // Validate role to ensure it's only what we expect
    if(!in_array($role, ['mekletajs', 'meistars'])) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid role selected."]);
        exit();
    }

    try {
        // 5. Check if User already exists (Email or Username)
        $checkQuery = "SELECT id FROM users WHERE email = :email OR username = :username";
        $stmt = $pdo->prepare($checkQuery);
        $stmt->execute(['email' => $email, 'username' => $username]);

        if ($stmt->rowCount() > 0) {
            // User exists
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Username or Email already exists."]);
        } else {
            // 6. Create new User
            
            // SECURITY: Hash the password! Never store plain text passwords.
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
            $stmt = $pdo->prepare($query);

            if ($stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password_hash,
                ':role' => $role
            ])) {
                http_response_code(201); // Created
                echo json_encode(["message" => "User registered successfully."]);
            } else {
                http_response_code(503); // Service Unavailable
                echo json_encode(["message" => "Unable to register user."]);
            }
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage()]);
    }

} else {
    // Data is incomplete
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Incomplete data. Please provide username, email, password and role."]);
}
?>