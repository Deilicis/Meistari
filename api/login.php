<?php
// api/login.php

// 1. CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    
    $email = htmlspecialchars(strip_tags($data->email));
    $password = $data->password;

    try {
        // 2. Find user by email
        // We select the ID, Username, Password, and Role
        $query = "SELECT id, username, password, role FROM users WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 3. Verify Password
            // Checks the plain text password against the hash in the DB
            if (password_verify($password, $user['password'])) {
                
                // Remove password from the array before sending it back (Security!)
                unset($user['password']);

                http_response_code(200); // OK
                echo json_encode([
                    "message" => "Veiksmīga pieslēgšanās.", // "Successful login"
                    "user" => $user // Send user data to Frontend
                ]);
            } else {
                http_response_code(401); // Unauthorized
                echo json_encode(["message" => "Nepareiza parole."]); // "Wrong password"
            }
        } else {
            http_response_code(404); // Not Found
            echo json_encode(["message" => "Lietotājs ar šādu e-pastu neeksistē."]); // "User not found"
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
    }

} else {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Lūdzu ievadiet e-pastu un paroli."]);
}
?>