<?php
// api/get_my_applications.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$master_id = isset($_GET['id']) ? $_GET['id'] : die();

try {
    // Atlasām pieteikumu + saistītā darba nosaukumu
    $query = "SELECT 
                a.id, a.price, a.comment, a.status, a.created_at,
                r.title as request_title, r.status as request_status
              FROM applications a
              JOIN requests r ON a.request_id = r.id
              WHERE a.master_id = ?
              ORDER BY a.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$master_id]);

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($applications);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>