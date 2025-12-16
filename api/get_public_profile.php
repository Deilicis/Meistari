<?php
// api/get_public_profile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$id = isset($_GET['id']) ? $_GET['id'] : die();

try {
    // Atlasām tikai publiski rādāmos datus
    $query = "SELECT 
                u.id, u.email, u.username,
                mp.type, mp.first_name, mp.last_name, mp.company_name, 
                mp.specialty, mp.city, mp.phone_number, mp.description, mp.experience, mp.reg_number
              FROM users u
              JOIN master_profiles mp ON u.id = mp.user_id
              WHERE u.id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($profile);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Profils nav atrasts."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>