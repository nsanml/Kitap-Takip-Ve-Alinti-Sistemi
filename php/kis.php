<?php require_once "connect.php"; 
session_start(); 
$sql_kis = "
    SELECT b.id as kitap_id, b.baslik, b.yazar, b.kapak 
    FROM books b
    INNER JOIN book_tags bt ON b.id = bt.book_id
    INNER JOIN tags t ON bt.tag_id = t.id
    WHERE t.name LIKE '%Kış%' OR t.name LIKE '%kis%' OR t.name LIKE '%Winter%'
    ORDER BY b.baslik ASC";

$res_kis = $vt->query($sql_kis);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kitap Durağı - Kış</title>
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	 <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
	<link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
	<link rel="stylesheet" type="text/css" href="../css/kis.css">
    <script src="../js/kis.js"></script>
</head>
<body >

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
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// --><div class="hero-section">
<div class="winter-header">
    <h2 class="main-title anasayfa">Kış Masalları ❄</h2>
    <span class="sub-title anasayfa">Battaniyeni al, kahveni yap ve kitabını seç...</span>
</div>

<div class="books-container">
    <?php if($res_kis->rowCount() > 0): ?>
        <?php while($row = $res_kis->fetch(PDO::FETCH_ASSOC)): ?>
            
            <a href="kitap_detay.php?id=<?php echo $row['kitap_id']; ?>" class="cozy-card">
                <img src="../resimler/kitaplar/<?php echo $row['kapak']; ?>" class="book-img" alt="Kapak">
                <div class="book-title"><?php echo $row['baslik']; ?></div>
                <div class="book-author"><?php echo $row['yazar']; ?></div>
                <span class="btn-read">Okumaya Başla</span>
            </a>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center anasayfa" style="margin-top: 50px;">
            <h3 style="color:#d63384; font-family:'Pacifico', cursive;">Henüz kar taneleri kadar eşsiz bir kitap eklenmedi... ❄️</h3>
            <p>(Veritabanında 'Kış' etiketli kitap yok)</p>
        </div>
    <?php endif; ?>
</div>

<br>
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