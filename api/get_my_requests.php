<?php
// api/get_my_requests.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$user_id = isset($_GET['id']) ? $_GET['id'] : die();

try {
    // Atlasām pieprasījumus un saskaitām pieteikumus (applicant_count)
    $query = "SELECT r.*, COUNT(a.id) as applicant_count 
              FROM requests r 
              LEFT JOIN applications a ON r.id = a.request_id 
              WHERE r.user_id = ? 
              GROUP BY r.id 
              ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($requests);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>