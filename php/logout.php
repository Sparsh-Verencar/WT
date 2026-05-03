<?php
session_start();
// detect ajax
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
session_destroy();
if ($is_ajax) {
	header('Content-Type: application/json');
	echo json_encode(['success' => true, 'redirect' => '../index.php']);
	exit;
}
header("Location: ../index.php");
exit;
?>
