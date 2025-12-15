<?php
// api/admin_delete_user.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    try {
        // Dzēšam lietotāju. ON DELETE CASCADE datubāzē parūpēsies, 
        // lai izdzēstos arī viņa profils, pieteikumi un sludinājumi.
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $data->id);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Lietotājs veiksmīgi dzēsts."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Nevarēja izdzēst lietotāju."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Kļūda: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Nav norādīts ID."]);
}
?>