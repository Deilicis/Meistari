<?php
// api/get_requests.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

try {
    // Mēs izmantojam JOIN, lai dabūtu arī lietotājvārdu no 'users' tabulas
    // Atlasām tikai 'active' statusa pieprasījumus
    $query = "SELECT 
                r.id, 
                r.title, 
                r.category, 
                r.description, 
                r.budget, 
                r.location, 
                r.deadline, 
                r.created_at,
                u.username as author
              FROM requests r
              LEFT JOIN users u ON r.user_id = u.id
              WHERE r.status = 'active'
              ORDER BY r.created_at DESC"; // Jaunākie augšā

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($requests);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
}
?>