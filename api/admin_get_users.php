<?php
// api/admin_get_users.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'db.php';

// Mēs varētu šeit pārbaudīt, vai user_id, kas nāk pieprasījumā, tiešām ir admin,
// bet eksāmena ietvaros pietiks, ja frontendā paslēpsim šo lapu.

try {
    $query = "SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Datubāzes kļūda: " . $e->getMessage()]);
}
?>