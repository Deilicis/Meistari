<?php
// api/confirm_application.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

include_once 'db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->application_id) && !empty($data->request_id)) {
    try {
        $pdo->beginTransaction();

        // 1. Atzīmējam uzvarētāju (pieteikumu) kā 'accepted'
        $stmt1 = $pdo->prepare("UPDATE applications SET status = 'accepted' WHERE id = ?");
        $stmt1->execute([$data->application_id]);

        // 2. Atzīmējam darbu kā 'completed' (lai tas vairs neparādās meklēšanā)
        // Piezīme: Vēlāk šeit varētu būt 'assigned', ja vēlaties atšķirt "procesā" no "pabeigts"
        $stmt2 = $pdo->prepare("UPDATE requests SET status = 'completed' WHERE id = ?");
        $stmt2->execute([$data->request_id]);

        // 3. (Neobligāti) Noraidām visus pārējos pieteikumus šim darbam
        $stmt3 = $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE request_id = ? AND id != ?");
        $stmt3->execute([$data->request_id, $data->application_id]);

        $pdo->commit();

        http_response_code(200);
        echo json_encode(["message" => "Meistars veiksmīgi apstiprināts! Darbs noslēgts."]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["message" => "Kļūda saglabājot datus: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Trūkst ID datu."]);
}
?>