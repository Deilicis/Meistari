<?php
// api/register.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->role)
) {
    // Validācija
    if (!in_array($data->role, ['mekletajs', 'meistars'])) {
        http_response_code(400);
        echo json_encode(["message" => "Nederīga loma."]);
        exit();
    }

    try {
        // 1. Pārbaudām, vai e-pasts jau eksistē
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$data->email]);
        if ($check->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(["message" => "Šis e-pasts jau ir reģistrēts."]);
            exit();
        }

        $pdo->beginTransaction();

        // 2. Ievietojam lietotāju (users tabulā)
        $query = "INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)";
        $stmt = $pdo->prepare($query);
        
        $password_hash = password_hash($data->password, PASSWORD_BCRYPT);
        
        $stmt->execute([
            ':username' => htmlspecialchars(strip_tags($data->username)),
            ':email' => htmlspecialchars(strip_tags($data->email)),
            ':password' => $password_hash,
            ':role' => $data->role
        ]);

        $user_id = $pdo->lastInsertId();

        // 3. Ja tas ir MEISTARS, uzreiz izveidojam profila sagatavi
        if ($data->role === 'meistars') {
            // Saņemam tipu no frontenda (individual vai company), pēc noklusējuma individual
            $type = isset($data->master_type) && in_array($data->master_type, ['company', 'individual']) 
                    ? $data->master_type 
                    : 'individual';

            $profileQuery = "INSERT INTO master_profiles (user_id, type) VALUES (?, ?)";
            $profileStmt = $pdo->prepare($profileQuery);
            $profileStmt->execute([$user_id, $type]);
        }

        $pdo->commit();

        http_response_code(201);
        echo json_encode(["message" => "Lietotājs veiksmīgi izveidots."]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Nepilnīgi dati."]);
}
?>