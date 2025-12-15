<?php
// api/update_profile.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id)) {
    try {
        // 1. Check if profile exists for this user
        $check = $pdo->prepare("SELECT id FROM master_profiles WHERE user_id = ?");
        $check->execute([$data->user_id]);
        
        if ($check->rowCount() > 0) {
            // UPDATE existing profile
            $query = "UPDATE master_profiles SET 
                first_name = :first_name,
                last_name = :last_name,
                specialty = :specialty,
                city = :city,
                phone_number = :phone_number,
                description = :description,
                experience = :experience
                WHERE user_id = :user_id";
        } else {
            // INSERT new profile
            $query = "INSERT INTO master_profiles 
                (user_id, first_name, last_name, specialty, city, phone_number, description, experience)
                VALUES (:user_id, :first_name, :last_name, :specialty, :city, :phone_number, :description, :experience)";
        }

        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ':user_id' => $data->user_id,
            ':first_name' => $data->first_name ?? '',
            ':last_name' => $data->last_name ?? '',
            ':specialty' => $data->specialty ?? '',
            ':city' => $data->city ?? '',
            ':phone_number' => $data->phone_number ?? '',
            ':description' => $data->description ?? '',
            ':experience' => $data->experience ?? ''
        ]);

        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "Profils veiksmīgi atjaunots."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Nevarēja saglabāt datus."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "SQL Error: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Nav norādīts lietotāja ID."]);
}
?>