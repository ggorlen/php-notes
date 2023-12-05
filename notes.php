<?php

function handle_get($db) {
    $query = $db->query('SELECT * FROM notes ORDER BY id DESC;');

    $rows = [];
    while ($row = $query->fetchArray(/*SQLITE3_ASSOC*/1)) {
         $rows[] = $row;
    }

    echo json_encode($rows);
}

function handle_post($db) {
    $stmt = $db->prepare('INSERT INTO notes (text) VALUES (:text);');
    $data = json_decode(file_get_contents('php://input'));

    if (!isset($data->note)) {
        http_response_code(400);
        json_encode(['message' => 'missing key `note`']);
        return;
    }

    $stmt->bindValue(':text', $data->note);
    $result = $stmt->execute();
    http_response_code(201);
    echo json_encode(['message' => 'record created']);
}

function handle_delete($path, $db) {
    if (count($path) < 3) {
        http_response_code(400);
        echo json_encode(['message' => 'missing key `id`']);
        return;
    }

    $stmt = $db->prepare('DELETE FROM notes WHERE id = :id;');
    $stmt->bindValue(':id', $path[2]);
    $result = $stmt->execute();

    if ($db->changes()) {
        http_response_code(200);
        echo json_encode(['message' => 'record deleted']);
        return;
    }

    http_response_code(404);
    echo json_encode(['message' => 'not found']);
}

function run_route($path, $db) {
    header('Content-Type: application/json; charset=utf-8');
    $methods = [
        'GET' => fn() => handle_get($db),
        'POST' => fn() => handle_post($db),
        'DELETE' => fn() => handle_delete($path, $db),
    ];

    if (array_key_exists($_SERVER['REQUEST_METHOD'], $methods)) {
        $methods[$_SERVER['REQUEST_METHOD']]();
    }
    else {
        http_response_code(405);
    }
}

?>
