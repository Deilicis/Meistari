<?php
// api/create_offer.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->user_id) &&
    !empty($data->title) &&
    !empty($data->category) &&
    !empty($data->description) &&
    !empty($data->price) &&
    !empty($data->service_type)
) {
    try {
        $query = "INSERT INTO offers 
                 (user_id, title, category, description, price, service_type) 
                 VALUES 
                 (:user_id, :title, :category, :description, :price, :service_type)";

        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            ':user_id' => $data->user_id,
            ':title' => htmlspecialchars(strip_tags($data->title)),
            ':category' => htmlspecialchars(strip_tags($data->category)),
            ':description' => htmlspecialchars(strip_tags($data->description)),
            ':price' => htmlspecialchars(strip_tags($data->price)),
            ':service_type' => htmlspecialchars(strip_tags($data->service_type))
        ]);

        if ($result) {
            http_response_code(201);
            echo json_encode(["message" => "Pakalpojums veiksmīgi pievienots."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Nevarēja saglabāt pakalpojumu."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Lūdzu aizpildiet visus laukus."]);
}
?>