<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "connect.php"; 

if (isset($_POST['ajax_skor']) && isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $gelenSkor = intval($_POST['ajax_skor']);


    if ($gelenSkor > 0) {
        
        $kontrolSorgu = $vt->prepare("SELECT skor FROM skorlar WHERE user_id = :uid");
        $kontrolSorgu->execute(['uid' => $userId]);
        $mevcutKayit = $kontrolSorgu->fetch(PDO::FETCH_ASSOC);

        if ($mevcutKayit) {
            $eskiSkor = intval($mevcutKayit['skor']);

            if ($gelenSkor < $eskiSkor) {
                $guncelle = $vt->prepare("UPDATE skorlar SET skor = :yeniSkor WHERE user_id = :uid");
                $guncelle->execute(['yeniSkor' => $gelenSkor, 'uid' => $userId]);
                echo "Tebrikler. Rekorunuz kaydedildi.";
            } else {
                echo "Bu skor rekorunuzdan daha iyi değil, kaydedilmedi.";
            }

        } else {
            $ekle = $vt->prepare("INSERT INTO skorlar (user_id, skor) VALUES (:uid, :skor)");
            $ekle->execute(['uid' => $userId, 'skor' => $gelenSkor]);
            echo "İlk skorunuz başarıyla kaydedildi.";
        }
    }
    exit;
}

$stmt = $vt->query("SELECT * FROM books ORDER BY RAND() LIMIT 6");
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

$items = [];
foreach ($cards as $card) {
    $items[] = ['type' => 'yazar', 'value' => $card['yazar'], 'id' => $card['id']];
    $items[] = ['type' => 'kitap', 'value' => $card['baslik'], 'id' => $card['id']];
}
shuffle($items);


$liderSorgu = $vt->query("
    SELECT u.id as k_id, u.username, s.skor 
    FROM skorlar s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.skor ASC 
    LIMIT 5
");
$enIyiler = $liderSorgu->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - İMZA</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/oyun.css">
    <script language="javascript" src="../js/oyun.js"></script>
</head>
<body style="background: #f4f7f6;">

<div class="banner">
    <img src="../resimler/banner.png">
</div>
<!-- /////////////////////////////////////////////////////NAVBAR MENÜ//////////////////////////////////////////// -->
<center><nav class="navbar navbar-expand-lg navbar-light bg-white shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="../resimler/logo.png" alt="Logo" style="height:40px;"></a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100 justify-content-around">
            <li class="nav-item web_name">
                <a class="nav-link" href="index.php" style="color:pink !important;">Kitap Durağı</a>
            </li>

            <li class="nav-item anasayfa">
                <a class="nav-link" href="alinti.php">Alıntılar</a>
            </li>

            <li class="nav-item anasayfa">
                <a class="nav-link" href="kitaplar.php">Kitaplar</a>
            </li>

            <li class="nav-item anasayfa">
                <a class="nav-link" href="takipler.php">Takipler</a>
            </li>
            <li class="nav-item anasayfa">
                <a class="nav-link" href="search.php">Search</a>
            </li>
           <?php if (isset($_SESSION['id'])): 
                    $id = $_SESSION['id'];

                    $sqlQuery = $vt->prepare("SELECT pp FROM users WHERE id = :id");
                    $sqlQuery->execute(['id' => $id]);
                    $pp = $sqlQuery->fetch(PDO::FETCH_ASSOC);

                    $pp_name = !empty($pp['pp']) ? $pp['pp'] : '1';
                  ?>
                    <li class="nav-item d-flex align-items-center">
                        <div class="dropdown">
                            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" 
                               id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../pp/<?php echo $pp_name; ?>.png" 
                                     class="rounded-circle" height="40" width="40" alt="Profil Resmi">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li><a class="dropdown-item" href="profil.php">Profilim</a></li>
                                <li><a class="dropdown-item" href="ayarlar.php">Ayarlar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="cikis.php" onclick="return confirmExit()">Çıkış Yap</a></li>
                                <script>
                                                    function confirmExit() {
                                                        return confirm("Çıkış yapmak istediğinizden emin misiniz?");
                                                    }
                                            </script>
                            </ul>
                        </div>
                    </li>
                    <?php else: ?>
                    <li class="nav-item d-flex align-items-center">
                        <button class="btn btn_pembee anasayfa" onclick="window.location.href='giris_kayit.php'">
                            Giriş Yap / Kayıt Ol
                        </button>
                    </li>
                    <?php endif; ?>

    </ul>
    </div>
</div>
</nav></div></center>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<center>
<?php
if (!isset($_SESSION['id'])) {
    header("Location:uyelere_ozel.php");
    exit; 
}

?>
<div class="divoyun" data-logged-in="<?= isset($_SESSION['id']) ? 'true' : 'false' ?>">
    <h2 style="text-align:center; margin-bottom: 20px;" class="anasayfa"><img src="../resimler/imza.png" style="width:250px; mix-blend-mode: multiply;"></h2>
    
    <h3 id="timer" class="anasayfa" style="margin-bottom: 15px;">Süre: <span id="sayac">0</span> saniye</h3>
    
    <button class="btn btn_oyun anasayfa" id="baslatOyun">Oyuna Başla</button>
    
    <div id="result" style="font-size: 1.2rem; margin-bottom: 15px; height: 30px;"></div>

    <div class="grid">
        <?php foreach($items as $index => $item): ?>
            <div class="card anasayfa" data-id="<?= $item['id'] ?>" data-type="<?= $item['type'] ?>" id="card-<?= $index ?>">
                <?= htmlspecialchars($item['value']) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="container mt-5" style="max-width: 600px;">
        <h4 class="text-center mb-3 anasayfa">🏆 En Hızlı Kitap Kurtları (Top 5)</h4>
        <div class="table-responsive">
            <table class="table table-hover table-bordered text-center shadow-sm" style="background-color: white;">
                <thead class="table-dark">
                    <tr>
                        <th>Sıra</th>
                        <th>Kullanıcı</th>
                        <th>Süre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($enIyiler) > 0): ?>
                        <?php $sira = 1; foreach ($enIyiler as $skor): ?>
                        <tr>
                            <td>
                                <?php 
                                    if($sira == 1) echo '🥇'; 
                                    elseif($sira == 2) echo '🥈'; 
                                    elseif($sira == 3) echo '🥉'; 
                                    else echo $sira.'.'; 
                                ?>
                            </td>
                            <td>
                                <a href="profil.php?id=<?= $skor['k_id'] ?>" class="text-decoration-none fw-bold text-dark">
                                    <?= htmlspecialchars($skor['username']) ?>
                                </a>
                            </td>
                            <td><span class="badge bg-success"><?= $skor['skor'] ?> sn</span></td>
                        </tr>
                        <?php $sira++; endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">Henüz kaydedilmiş bir skor yok. İlk sen ol!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    </div>
</center>
</body>
</html>