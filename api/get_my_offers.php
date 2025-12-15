<?php
// api/get_my_offers.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$user_id = isset($_GET['id']) ? $_GET['id'] : die();

try {
    $query = "SELECT * FROM offers WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);

    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($offers);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>