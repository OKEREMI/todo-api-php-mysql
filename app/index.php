<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
require 'db.php';

$pdo = getDbConnection();

set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode(['error' => 'Something went wrong']);
    exit;
});

$method = $_SERVER['REQUEST_METHOD'];
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($method === 'GET' && $parts[0] === 'todos' && !isset($parts[1])) {
    $stmt = $pdo->query('SELECT * FROM todos ORDER BY id');
    $todos = $stmt->fetchAll();
    echo json_encode($todos);
    exit;
}

if ($method === 'POST' && $parts[0] === 'todos' && !isset($parts[1])) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['title']) || trim($input['title']) === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title is required']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO todos (title, completed) VALUES (:title, :completed)');
    $stmt->execute([
        ':title' => $input['title'],
        ':completed' => isset($input['completed']) ? (int)$input['completed'] : 0,
    ]);

    $newId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute([':id' => $newId]);

    http_response_code(201);
    echo json_encode($stmt->fetch());
    exit;
}

if ($method === 'GET' && $parts[0] === 'todos' && isset($parts[1])) {
    $id = $parts[1];
    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $todo = $stmt->fetch();

    if (!$todo) {
        http_response_code(404);
        echo json_encode(['error' => 'Todo not found']);
        exit;
    }

    echo json_encode($todo);
    exit;
}

if ($method === 'PUT' && $parts[0] === 'todos' && isset($parts[1])) {
    $id = $parts[1];
    $input = json_decode(file_get_contents('php://input'), true);

    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Todo not found']);
        exit;
    }

    if (isset($input['title']) && trim($input['title']) === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title cannot be empty']);
        exit;
    }

    $title = isset($input['title']) ? $input['title'] : $existing['title'];
    $completed = isset($input['completed']) ? (int)$input['completed'] : $existing['completed'];

    $stmt = $pdo->prepare('UPDATE todos SET title = :title, completed = :completed WHERE id = :id');
    $stmt->execute([
        ':title' => $title,
        ':completed' => $completed,
        ':id' => $id,
    ]);

    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    echo json_encode($stmt->fetch());
    exit;
}

if ($method === 'DELETE' && $parts[0] === 'todos' && isset($parts[1])) {
    $id = $parts[1];

    $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Todo not found']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id');
    $stmt->execute([':id' => $id]);

    http_response_code(204);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Route not found']);
