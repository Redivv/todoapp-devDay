<?php

use PhpAmqpLib\Message\AMQPMessage;

$json = file_get_contents('php://input');
if (empty($json)) {
    header("Location: /");
    exit;
}

$rabbit->queue_declare('task_created');
$msg = new AMQPMessage('Hello World!');
$rabbit->basic_publish($msg, '', 'task_created');

$data = json_decode($json, true);

$stmt = $db->prepare("SELECT id FROM tables WHERE id = ? AND user_id = ?");
$stmt->execute([
    $data['tableId'],
    $_SESSION['user_id'],
]);
$validatedTableId = (int)$stmt->fetchColumn();

$stmt = $db->prepare("INSERT INTO tasks (title, table_id) VALUES (?, ?)");
$stmt->execute([
    $data['taskName'],
    $validatedTableId,
]);

$stmt = $db->prepare("SELECT id, title, level FROM tasks WHERE title = ? AND table_id = ? ORDER BY id DESC");
$stmt->execute([
    $data['taskName'],
    $data['tableId'],
]);

http_response_code(201);
header('Content-type: application/json');
echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
exit;
