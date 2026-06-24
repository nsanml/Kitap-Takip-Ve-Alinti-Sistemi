<?php
require_once "connect.php";
session_start();
if (!isset($_SESSION['id'])){
	header("Location:uyelere_ozel.php");
}

$kitap_limit=12;
$sayfa = isset($_GET['sayfa']) && is_numeric($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
if ($sayfa < 1) $sayfa = 1;
$offset = ($sayfa - 1) * $limit;

try{
	$sqlQuery=$vt->prepare("SELECT COUNT(*) FROM books");
	$kitapSayisi=$sqlQuery->fetchColumn();
	$sayfaSayisi=$kitapSayisi/$kitap_limit;

	$sql = $vt->prepare("SELECT * FROM books ORDER BY id DESC");
	$sql->execute();
	$kitaplar = $sql->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    die("Hata: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kitap Durağı - Kütüphane</title>
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	 <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
	<link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
	<link rel="stylesheet" type="text/css" href="../css/kitaplar.css">
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
</nav></div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<center>
<div class="container mt-5 mb-5 kutuphane-alani">
    <div class="row mb-4">
        <div class="col-sm-12 text-center">
            <h2 class="raf-basligi">📚 Kitaplar</h2>
            <p class="text-muted">Raflarımızdaki tüm kitapları keşfet.</p>
        </div>
    </div>

    <?php 
    if (count($kitaplar) == 0) { ?>
        <div class="alert alert-warning text-center" style="background:pink !important">
            Henüz kitap kayıtlı değil.
        </div>
    <?php 
    } else { 
        echo '<div class="row">';
        
        $sayac = 0;
        foreach ($kitaplar as $kitap) {
            $kapak = $kitap['kapak'];
            ?>
            
            <div class="col-sm-3">
                <a href="kitap_detay.php?id=<?php echo $kitap['id']; ?>" class="kitap-link">
                    <div class="kitap-kutusu">
                        <img class="kitap-gorsel" src="../resimler/kitaplar/<?= $kapak ?>">
                        <span class="kitap-adi"><?=$kitap['baslik'] ?></span>
                        <span class="yazar-adi"><?=$kitap['yazar'] ?></span>
                    </div>
                </a>
            </div>

            <?php
            $sayac++;
         
            if ($sayac % 4 == 0) {
                echo '</div>'; 
                echo '<div class="col-12"><div class="raf-tahtasi"></div></div>'; 
                
               
                if ($sayac != count($kitaplar)) {
                    echo '<div class="row">';
                }
            }
        }
        
        if ($sayac % 4 != 0) {
            echo '</div>'; 
            echo '<div class="col-12"><div class="raf-tahtasi"></div></div>'; 
        }
        ?>

        <?php if ($toplamSayfa > 1): ?>
            <nav aria-label="Sayfalama" class="mt-5 mb-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($sayfa <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?sayfa=<?= $sayfa - 1 ?>">‹ Önceki Raf</a>
                    </li>
                    <?php for($s = 1; $s <= $toplamSayfa; $s++): ?>
                        <li class="page-item <?= ($s == $sayfa) ? 'active' : '' ?>">
                            <a class="page-link" href="?sayfa=<?= $s ?>"><?= $s ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($sayfa >= $toplamSayfa) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?sayfa=<?= $sayfa + 1 ?>">Sonraki Raf ›</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

    <?php
    }
    ?>
</div>
</center>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<footer class="text-white pt-5 pb-4" style="background: linear-gradient(135deg, #333 0%, #444 100%);">
    <div class="container">
        <div class="row">
            
            <div class="col-md-4 mb-4">
                <h5 class=" mb-3" style="color: #ff69b4; letter-spacing: 2px;">KİTAP DURAĞI</h5>
                <p class="small text-white-50">
                    Her kitap gidilecek yeni bir kıta, çevrilen her sayfa keşfedilecek başka bir hayattır. Hayatın karmaşasına kısa bir mola verip, kelimelerin sonsuz yolculuğuna çıktığımız bu durakta bizimlesiniz.
                </p>
                <div class="mt-3">
                    <img src="../resimler/footer.png" style="width: 100px; mix-blend-mode: multiply;">
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <h6 class=" mb-3 fw-bold" style="color: #ffc3a0;">HIZLI ERİŞİM</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none hover-link">Ana Sayfa</a></li>
                    <li class="mb-2"><a href="alinti.php" class="text-white-50 text-decoration-none hover-link">Alıntılar</a></li>
                    <li class="mb-2"><a href="kitaplar.php" class="text-white-50 text-decoration-none hover-link">Kitaplar</a></li>
                    <li class="mb-2"><a href="oyun.php" class="text-white-50 text-decoration-none hover-link">İmza Oyunu</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-4">
                <h6 class=" mb-3 fw-bold" style="color: #ffc3a0;">İLETİŞİM</h6>
                <p class="small text-white-50 mb-1">📍 Konya, Türkiye</p>
                <p class="small text-white-50 mb-1">📧 iletisim@kitapduragi.com</p>
                <p class="small text-white-50">📞 +90 555 000 00 00</p>
            </div>
        </div>

        <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start small text-white-50">
                &copy; 2025 <strong>Kitap Durağı</strong>. Tüm hakları saklıdır.
            </div>
            <div class="col-md-6 text-center text-md-end small text-white-50">
                Tasarlayan: <span style="color: #ff69b4;">Nisa-Şamil</span>
            </div>
        </div>
    </div>
</footer>

<style>
    .hover-link:hover{
        color: #fff !important;
        padding-left: 5px;
        transition: all 0.3s ease;
    }
</style>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
</center>
</body>
</html>