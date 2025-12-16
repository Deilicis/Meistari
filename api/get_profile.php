<?php
// api/get_profile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$user_id = isset($_GET['id']) ? $_GET['id'] : die();

try {
    $query = "SELECT * FROM master_profiles WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        // Ja ir profils (Meistars/Uzņēmums)
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($profile);
    } else {
        echo json_encode(["type" => "individual"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>