<?php
require_once "connect.php";
session_start();

header('Content-Type: application/json');

if ($_POST && isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $book_id = $_POST['book_id'];
    $status  = $_POST['status']; 

    $kontrol = $vt->prepare("SELECT id FROM user_book WHERE user_id = :uid AND book_id = :bid");
    $kontrol->execute([':uid' => $user_id, ':bid' => $book_id]);
    $kayit = $kontrol->fetch(PDO::FETCH_ASSOC);

    if ($kayit) {
        $sql = "UPDATE user_book SET status = :status, eklenme_tarihi = NOW() WHERE id = :id";
        $stmt = $vt->prepare($sql);
        $sonuc = $stmt->execute([':status' => $status, ':id' => $kayit['id']]);
    } else {
        $sql = "INSERT INTO user_book (user_id, book_id, status, eklenme_tarihi) VALUES (:uid, :bid, :status, NOW())";
        $stmt = $vt->prepare($sql);
        $sonuc = $stmt->execute([':uid' => $user_id, ':bid' => $book_id, ':status' => $status]);
    }

    if ($sonuc) {
        echo json_encode(['status' => 'success', 'message' => 'Durum güncellendi!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'İşlem başarısız.']);
    }
}
?>