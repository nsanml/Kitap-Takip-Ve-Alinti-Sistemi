<?php
require_once "connect.php";
session_start();

if ($_POST && isset($_SESSION['id'])) {
    $follower_id = $_SESSION['id']; 
    $following_id = $_POST['profil_id']; 
    $islem = $_POST['islem'];

    if ($follower_id == $following_id) {
        echo json_encode(['status' => 'error', 'message' => 'Kendini takip edemezsin.']);
        exit;
    }

    if ($islem == 'takip_et') {
        $sql = "INSERT INTO followers (follower_id, following_id, created_at) VALUES (:fid, :target, NOW())";
    } else {
        $sql = "DELETE FROM followers WHERE follower_id = :fid AND following_id = :target";
    }

    $stmt = $vt->prepare($sql);
    $sonuc = $stmt->execute([':fid' => $follower_id, ':target' => $following_id]);

    if ($sonuc) {
        $sayiSql = $vt->prepare("SELECT COUNT(*) FROM followers WHERE following_id = :target");
        $sayiSql->execute([':target' => $following_id]);
        $yeniSayi = $sayiSql->fetchColumn();

        echo json_encode(['status' => 'success', 'yeni_sayi' => $yeniSayi]);
    } else {
        echo json_encode(['status' => 'error']);
    }
}
?>