<?php
// api/apply_for_job.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->request_id) && !empty($data->master_id) && !empty($data->price)) {
    try {
        // 1. Pārbaudām, vai meistars jau nav pieteicies šim darbam (lai novērstu dublikātus)
        $check = $pdo->prepare("SELECT id FROM applications WHERE request_id = ? AND master_id = ?");
        $check->execute([$data->request_id, $data->master_id]);

        if ($check->rowCount() > 0) {
            http_response_code(409); // Conflict
            echo json_encode(["message" => "Jūs jau esat pieteicies šim darbam."]);
            exit();
        }

        // 2. Saglabājam pieteikumu
        $query = "INSERT INTO applications (request_id, master_id, price, comment) VALUES (:req_id, :master_id, :price, :comment)";
        $stmt = $pdo->prepare($query);

        $result = $stmt->execute([
            ':req_id' => $data->request_id,
            ':master_id' => $data->master_id,
            ':price' => $data->price,
            ':comment' => htmlspecialchars(strip_tags($data->comment ?? ''))
        ]);

        if ($result) {
            http_response_code(201);
            echo json_encode(["message" => "Pieteikums veiksmīgi nosūtīts!"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Nevarēja nosūtīt pieteikumu."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Nepieciešama cena un ID."]);
}
?>