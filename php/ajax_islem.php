<?php
require_once "connect.php";
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Giriş yapmalısınız.']);
    exit;
}

$uye_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['islem']) && $_POST['islem'] == 'begen') {
    
    $quote_id = intval($_POST['quote_id']);

    $kontrol = $vt->prepare("SELECT id FROM likes WHERE user_id = :uid AND quote_id = :qid");
    $kontrol->execute(['uid' => $uye_id, 'qid' => $quote_id]);
    
    if ($kontrol->rowCount() > 0) {
        $sil = $vt->prepare("DELETE FROM likes WHERE user_id = :uid AND quote_id = :qid");
        $sil->execute(['uid' => $uye_id, 'qid' => $quote_id]);
        $durum = 'geri_alindi';
    } else {
        $ekle = $vt->prepare("INSERT INTO likes (user_id, quote_id) VALUES (:uid, :qid)");
        $ekle->execute(['uid' => $uye_id, 'qid' => $quote_id]);
        $durum = 'begendi';
    }

    $sayiSorgu = $vt->prepare("SELECT COUNT(*) FROM likes WHERE quote_id = :qid");
    $sayiSorgu->execute(['qid' => $quote_id]);
    $yeniSayi = $sayiSorgu->fetchColumn();

    echo json_encode(['status' => 'success', 'islem_turu' => $durum, 'yeni_sayi' => $yeniSayi]);
    exit;
}
?>