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
        // Pārbaudām, vai profils eksistē
        $check = $pdo->prepare("SELECT id FROM master_profiles WHERE user_id = ?");
        $check->execute([$data->user_id]);
        
        // Sagatavojam datus (tukšus stringus, ja nav norādīts)
        $params = [
            ':user_id' => $data->user_id,
            ':first_name' => $data->first_name ?? '',
            ':last_name' => $data->last_name ?? '',
            ':company_name' => $data->company_name ?? '',
            ':reg_number' => $data->reg_number ?? '',
            ':specialty' => $data->specialty ?? '',
            ':city' => $data->city ?? '',
            ':phone_number' => $data->phone_number ?? '',
            ':description' => $data->description ?? '',
            ':experience' => $data->experience ?? ''
        ];

        if ($check->rowCount() > 0) {
            // UPDATE
            $query = "UPDATE master_profiles SET 
                first_name = :first_name,
                last_name = :last_name,
                company_name = :company_name,
                reg_number = :reg_number,
                specialty = :specialty,
                city = :city,
                phone_number = :phone_number,
                description = :description,
                experience = :experience
                WHERE user_id = :user_id";
        } else {
            // INSERT (Ja nu gadījumā profila nav)
            // Piezīme: Šeit vajadzētu arī 'type', bet pieņemsim, ka tas nāk no reģistrācijas vai frontenda
            $params[':type'] = $data->type ?? 'individual';
            $query = "INSERT INTO master_profiles 
                (user_id, type, first_name, last_name, company_name, reg_number, specialty, city, phone_number, description, experience)
                VALUES (:user_id, :type, :first_name, :last_name, :company_name, :reg_number, :specialty, :city, :phone_number, :description, :experience)";
        }

        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute($params)) {
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