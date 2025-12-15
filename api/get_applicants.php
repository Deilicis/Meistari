<?php
// api/get_applicants.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

$request_id = isset($_GET['request_id']) ? $_GET['request_id'] : die();

try {
    // Atlasām pieteikumu datus un pievienojam Meistara vārdu/uzvārdu/lietotājvārdu no 'users' un 'master_profiles'
    // Izmantojam LEFT JOIN master_profiles, jo varbūt meistars vēl nav aizpildījis profilu, bet lietotājs eksistē
    $query = "SELECT 
                a.id, a.price, a.comment, a.created_at, a.status,
                u.username,
                mp.first_name, mp.last_name, mp.company_name, mp.type
              FROM applications a
              JOIN users u ON a.master_id = u.id
              LEFT JOIN master_profiles mp ON u.id = mp.user_id
              WHERE a.request_id = ?
              ORDER BY a.price ASC"; // Kārtojam pēc zemākās cenas

    $stmt = $pdo->prepare($query);
    $stmt->execute([$request_id]);

    $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($applicants);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
}
?>