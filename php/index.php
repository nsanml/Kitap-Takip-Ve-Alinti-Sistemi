<?php require_once "connect.php"; 
session_start(); ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kitap Durağı</title>
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	 <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
	<link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
	<link rel="stylesheet" type="text/css" href="../css/oyun.css">
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

<!-- ///////////////////////////////////////////////CAROUSEL/////////////////////////////////////////////////// -->
<div class="container mt-3">
	<div class="row">
		<div class="col-sm-5">
			<div id="demo" class="carousel slide" data-bs-ride="carousel">
				<div class="carousel-indicators">
					<button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
					<button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
					<button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
				</div>

				<div class="carousel-inner">
					<div class="carousel-item active">
						<a href="enler.php"><img src="../resimler/1.png" alt="Kitap Durağı Enleri" class="d-block w-100"></a>
					</div>
					<div class="carousel-item">
						<a href="kis.php"><img src="../resimler/2.png" alt="Yılbaşında Ne Okunur?" class="d-block w-100"></a>
					</div>
					<div class="carousel-item">
						<a href="kitap_detay.php?id=<?= 1 ?>"><img src="../resimler/3.png" alt="Hüseyin Rahmi Gürpınar, Cadı" class="d-block w-100"></a>
					</div>
				</div>

				<button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
					<span class="carousel-control-prev-icon"></span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
					<span class="carousel-control-next-icon"></span>
				</button>
			</div>
		</div>
		<div class="col-sm-7">
	    <div class="alinti">
	        <h3>
	            ✨ Günün Sözü ✨
	        </h3>
	        
	        <p>
	        <?php 
                
                $gunun_sozu = "Kitaplar, soğuk ama güvenilir dostlardır.";
                $gunun_yazari = "Victor Hugo";

                $sqlQuery = "SELECT quotes.content, books.yazar 
                             FROM quotes 
                             INNER JOIN books ON quotes.book_id = books.id 
                             ORDER BY RAND() LIMIT 1";
    
                $stmt = $vt->query($sqlQuery);

                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($row) {
                        $gunun_sozu = $row['content'];
                        $gunun_yazari = $row['yazar'];
                    }
                }

                echo $gunun_sozu 
            ?>
	        </p>
	        <div>
	            -<a href="#"><?php echo $gunun_yazari ?></a>
	        </div>
	    </div>
		</div>
	</div>
</div>
<!-- ////////////////////////////////////////////////////OYUN TANITIM///////////////////////////////////////////////// -->
<div class="container">
    <div class="oyun-bolumu-bg">
        <div class="row align-items-center">
            
            <div class="col-md-5 mb-3 mb-md-0">
                 <div class="oyun-kart">
                    <h3 class="kart-baslik">🏆 Şampiyonlar Listesi</h3>
                    
                    <?php
                    $liderSorgu = $vt->query("
                        SELECT u.id as k_id, u.username, s.skor 
                        FROM skorlar s 
                        JOIN users u ON s.user_id = u.id 
                        ORDER BY s.skor ASC 
                        LIMIT 5
                    ");
                    $enIyiler = $liderSorgu->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="skor-table-wrapper">
                        <table class="table table-hover table-borderless text-center" style="margin-bottom: 0;">
                            <thead style="color: #888; border-bottom: 1px solid #eee;">
                                <tr>
                                    <th>#</th>
                                    <th>Oyuncu</th>
                                    <th>Süre</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($enIyiler) > 0): ?>
                                    <?php $sira = 1; foreach ($enIyiler as $skor): ?>
                                    <tr>
                                        <td style="font-size:1.2rem;">
                                            <?= ($sira == 1) ? '🥇' : (($sira == 2) ? '🥈' : (($sira == 3) ? '🥉' : $sira)) ?>
                                        </td>
                                        <td>
                                            <a href="profil.php?id=<?= $skor['k_id'] ?>" class="text-decoration-none fw-bold" style="color:#555 !important;">
                                                <?= htmlspecialchars($skor['username']) ?>
                                            </a>
                                        </td>
                                        <td><span class="badge rounded-pill bg-success"><?= $skor['skor'] ?> sn</span></td>
                                    </tr>
                                    <?php $sira++; endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-muted py-4">Henüz bir şampiyon yok.<br>İlk sen ol!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="oyun-kart">
                   
                       <img src="../resimler/imza.png" style="width: 200px;">
                    
                    
                    <p class="text-muted text-center" style="font-size: 1.1rem; line-height: 1.6;">
                        "Hafızana güveniyor musun?"<br>
                        Yazarları eserleriyle en hızlı sürede buluştur,<br>
                        adını <b>Şampiyonlar Listesi</b>'ne yazdır!
                    </p>

                    <a href="oyun.php" class="btn-imza">
                        OYUNA BAŞLA ►
                    </a>
                    
                    <small class="text-muted mt-3" style="font-size: 0.8rem;">
                        * En iyi skorunuz otomatik kaydedilir.
                    </small>
                </div>
            </div>
            
        </div>
    </div>
</div>
<!--////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<div class="container mb-5 mt-4">
    <div class="card border-0 shadow-sm" style="background: linear-gradient(to right, #fff, #fff0f6); border-radius: 20px; overflow: hidden;">
        <div class="row g-0 align-items-center">
            
            <?php

            $kitapSorgu = $vt->query("SELECT * FROM books ORDER BY RAND() LIMIT 1");
            $vitrinKitap = $kitapSorgu->fetch(PDO::FETCH_ASSOC);
            

            $kitapResmi = (!empty($vitrinKitap['kapak'])) ? $vitrinKitap['kapak'] : '';
            ?>

            <div class="col-md-3 text-center p-3">
                <div class="kitap-golge">
                    <img src="../resimler/kitaplar/<?= $kitapResmi ?>" 
                         alt="<?= htmlspecialchars($vitrinKitap['baslik']) ?>" 
                         class="img-fluid rounded-3" 
                         style="max-height: 250px; object-fit: cover;">
                </div>
            </div>

            <div class="col-md-9">
                <div class="card-body p-4">
                    <span class="badge bg-warning text-dark mb-2 anasayfa">⭐ Haftanın Önerisi</span>
                    
                    <h2 class="card-title" style="color: #d63384; font-family: 'Georgia', serif; font-weight: bold;">
                        <?= htmlspecialchars($vitrinKitap['baslik']) ?>
                    </h2>
                    
                    <h5 class="text-muted mb-3">
                        ✍️ <?= htmlspecialchars($vitrinKitap['yazar']) ?>
                    </h5>
                    
                    <p class="card-text text-secondary anasayfa">
                        Bu kitabı henüz okumadınız mı? Kitaplığınızda mutlaka bulunması gereken, 
                        okurken sizi başka diyarlara götürecek eşsiz bir eser.
                    </p>

                    <div class="mt-4">
                        <a href="kitap_detay.php?id=<?= $vitrinKitap['id'] ?>" class="btn btn-outline-danger rounded-pill px-4 anasayfa">
                            İncele
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .kitap-golge img{
        box-shadow: 10px 10px 20px rgba(0,0,0,0.2);
        transform: perspective(800px) rotateY(-10deg); 
        transition: transform 0.3s ease;
    }
    
    .kitap-golge img:hover{
        transform: perspective(800px) rotateY(0deg) scale(1.05); 
    }
</style>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<div class="container-fluid py-5" style="background-color: #fff0f6; margin-top: 80px;">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-md-8">
                <h3 style="color: #d63384; font-family: 'Georgia', serif;">💌 Edebiyat Postası</h3>
                <p class="text-muted mb-4">
                    Haftanın en sevilen alıntılarını ve kitap önerilerini kaçırma.<br>
                    Söz veriyoruz, seni sıkmadan sadece ilham göndereceğiz.
                </p>
                
                <form action="" method="POST" class="d-flex justify-content-center">
                    <div class="input-group mb-3" style="max-width: 500px;">
                        <input type="email" class="form-control form-control-lg border-0 shadow-sm" placeholder="E-posta adresin..." aria-label="E-posta" style="border-radius: 30px 0 0 30px; padding-left: 20px;">
                        <button class="btn btn-dark px-4" type="button" style="background-color: #d63384; border: none; border-radius: 0 30px 30px 0;">Abone Ol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
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