<?php
require_once "connect.php";
session_start();

if (!isset($_SESSION['id']) || !isset($_POST['profil_id'])) {
    echo "Yetkisiz erişim.";
    exit;
}

$my_id = $_SESSION['id'];
$profil_id = $_POST['profil_id'];
$tur = $_POST['tur'];

$benim_profilim = ($my_id == $profil_id);

if ($tur == 'takip') {
    $sql = "SELECT u.id, u.username, u.pp 
            FROM followers f 
            JOIN users u ON f.following_id = u.id 
            WHERE f.follower_id = :pid";
} else {
    $sql = "SELECT u.id, u.username, u.pp 
            FROM followers f 
            JOIN users u ON f.follower_id = u.id 
            WHERE f.following_id = :pid";
}

$stmt = $vt->prepare($sql);
$stmt->execute([':pid' => $profil_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) == 0) {
    echo '<div class="text-center text-muted mt-3">Liste boş.</div>';
    exit;
}

echo '<ul class="list-group list-group-flush">';

foreach ($users as $user) {
    $pp = !empty($user['pp']) ? $user['pp'] : '1';
    $link = "profil.php?id=" . $user['id'];
    
    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
    echo '<div class="d-flex align-items-center">';
    echo '<a href="'.$link.'" class="text-decoration-none d-flex align-items-center">';
    echo '<img src="../pp/'.$pp.'.png" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">';
    echo '<span style="font-weight:bold; color:#333;">@'.$user['username'].'</span>';
    echo '</a>';
    echo '</div>';
    if ($benim_profilim && $tur == 'takip') {
        echo '<button class="btn btn-sm btn-outline-danger" onclick="takipEt('.$user['id'].', \'takibi_birak\')">Takibi Bırak</button>';
    }
    echo '</li>';
}

echo '</ul>';
?>