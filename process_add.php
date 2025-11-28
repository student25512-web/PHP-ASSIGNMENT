<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php'); exit;
}

$title  = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$genre  = trim($_POST['genre'] ?? '');
$price  = floatval($_POST['price'] ?? 0);
$special_offer = isset($_POST['special_offer']);

if ($title === '' || $author === '' || $genre === '' || $price <= 0) {
    die('All fields required!');
}

$line = "$title,$author,$genre,$price," . ($special_offer ? '1' : '0') . PHP_EOL;
file_put_contents('books.txt', $line, FILE_APPEND | LOCK_EX);

header('Location: admin.php?msg=added');
exit;