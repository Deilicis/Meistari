<?php
// api/get_offers.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

try {
    // Atlasām pakalpojumus un pievienojam autora vārdu un pilsētu no profila
    $query = "SELECT 
                o.id, o.title, o.category, o.description, o.price, o.service_type, o.created_at,
                u.username,
                mp.city, mp.type, mp.company_name, mp.first_name, mp.last_name
              FROM offers o
              JOIN users u ON o.user_id = u.id
              LEFT JOIN master_profiles mp ON u.id = mp.user_id
              ORDER BY o.created_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($offers);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
}
?>