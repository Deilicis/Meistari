<?php
// api/create_request.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

// Pārbaudām obligātos laukus (saskaņā ar 3. tabulu dokumentācijā)
if (
    !empty($data->user_id) &&
    !empty($data->title) &&
    !empty($data->category) &&
    !empty($data->description) &&
    !empty($data->budget) &&
    !empty($data->location)
) {
    try {
        $query = "INSERT INTO requests 
                 (user_id, title, category, description, budget, location, deadline) 
                 VALUES 
                 (:user_id, :title, :category, :description, :budget, :location, :deadline)";

        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            ':user_id' => $data->user_id,
            ':title' => htmlspecialchars(strip_tags($data->title)),
            ':category' => htmlspecialchars(strip_tags($data->category)),
            ':description' => htmlspecialchars(strip_tags($data->description)),
            ':budget' => htmlspecialchars(strip_tags($data->budget)),
            ':location' => htmlspecialchars(strip_tags($data->location)),
            ':deadline' => !empty($data->deadline) ? $data->deadline : null // Termiņš nav obligāts
        ]);

        if ($result) {
            http_response_code(201);
            echo json_encode(["message" => "Pieprasījums veiksmīgi izveidots."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Nevarēja saglabāt pieprasījumu."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Lūdzu aizpildiet visus obligātos laukus."]);
}
?>