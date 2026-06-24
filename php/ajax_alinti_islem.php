<?php
require_once "connect.php";
session_start();

header('Content-Type: application/json');

if ($_POST && isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $islem   = $_POST['islem'];

    try {
        if ($islem == 'ekle') {
            $book_id = $_POST['book_id'];
            $content = $_POST['content'];

            if (empty($content) || empty($book_id)) {
                echo json_encode(['status' => 'error', 'message' => 'Lütfen kitap seçin ve alıntı yazın.']);
                exit;
            }

            $sql = "INSERT INTO quotes (user_id, book_id, content, olusma_tarih) VALUES (:uid, :bid, :content, NOW())";
            $stmt = $vt->prepare($sql);
            $sonuc = $stmt->execute([':uid' => $user_id, ':bid' => $book_id, ':content' => $content]);
            
            echo json_encode(['status' => 'success', 'message' => 'Alıntı başarıyla eklendi!']);
        }

        elseif ($islem == 'sil') {
            $alinti_id = $_POST['alinti_id'];

            $sql = "DELETE FROM quotes WHERE id = :id AND user_id = :uid";
            $stmt = $vt->prepare($sql);
            $sonuc = $stmt->execute([':id' => $alinti_id, ':uid' => $user_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Alıntı silindi.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Silme yetkiniz yok veya alıntı bulunamadı.']);
            }
        }


        elseif ($islem == 'guncelle') {
            $alinti_id = $_POST['alinti_id'];
            $book_id   = $_POST['book_id'];
            $content   = $_POST['content'];

            $sql = "UPDATE quotes SET book_id = :bid, content = :content WHERE id = :id AND user_id = :uid";
            $stmt = $vt->prepare($sql);
            $sonuc = $stmt->execute([
                ':bid' => $book_id, 
                ':content' => $content, 
                ':id' => $alinti_id, 
                ':uid' => $user_id
            ]);

            echo json_encode(['status' => 'success', 'message' => 'Alıntı güncellendi.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}
?>