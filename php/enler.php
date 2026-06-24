<?php 
require_once "connect.php"; 
session_start();

$sql_begenilen = "
    SELECT q.content AS alinti_icerik, b.baslik AS kitap_adi, b.kapak, u.username AS paylasan, COUNT(l.id) as begeni_sayisi 
    FROM quotes q
    LEFT JOIN likes l ON q.id = l.quote_id
    INNER JOIN books b ON q.book_id = b.id
    INNER JOIN users u ON q.user_id = u.id
    GROUP BY q.id
    ORDER BY begeni_sayisi DESC
    LIMIT 3";
$res_begenilen = $vt->query($sql_begenilen);

$sql_okunan = "
    SELECT b.id AS kitap_id, b.baslik, b.yazar, b.kapak, COUNT(ub.id) as okunma_sayisi 
    FROM books b
    INNER JOIN user_book ub ON b.id = ub.book_id
    WHERE ub.status = 'okudum'
    GROUP BY b.id
    ORDER BY okunma_sayisi DESC
    LIMIT 3";
$res_okunan = $vt->query($sql_okunan);

$sql_populer_user = "
    SELECT u.id AS kullanici_id, u.username, u.pp, COUNT(f.id) as takipci_sayisi 
    FROM users u
    INNER JOIN followers f ON u.id = f.following_id
    GROUP BY u.id
    ORDER BY takipci_sayisi DESC
    LIMIT 3";
$res_populer_user = $vt->query($sql_populer_user);

$sql_kitapkurdu = "
    SELECT u.id AS kullanici_id, u.username, u.pp, COUNT(ub.id) as okudugu_sayi 
    FROM users u
    INNER JOIN user_book ub ON u.id = ub.user_id
    WHERE ub.status = 'okudum'
    GROUP BY u.id
    ORDER BY okudugu_sayi DESC
    LIMIT 3";
$res_kitapkurdu = $vt->query($sql_kitapkurdu);
?> 

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - En'ler</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/enler.css">
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

<h1 class="anasayfa">✨ Kitap Durağı En'ler ✨</h1><br>

<div class="dashboard-grid">

   <div class="stat-card anasayfa">
        <h3>❤️ En Çok Beğenilen Alıntılar</h3>
        <?php $sira=1; if($res_begenilen->rowCount() > 0): ?>
            <?php while($row = $res_begenilen->fetch(PDO::FETCH_ASSOC)): ?>
            <a href="alinti.php" class="list-link">
                <div class="list-item">
                    <span class="rank"><?php echo $sira++; ?></span>
                    
                    <img src="../resimler/kitaplar/<?php echo $row['kapak']; ?>" class="thumb book-cover">
                    
                    <div class="info">
                        <span class="name" style="font-style: italic; font-size: 0.95rem;">
                            "<?php echo mb_substr($row['alinti_icerik'], 0, 40); ?>..."
                        </span>
                        
                        <span class="sub-text">
                            <?php echo $row['kitap_adi']; ?> | <span style="color:#d63384;">@<?php echo $row['paylasan']; ?></span>
                        </span>
                    </div>
                    
                    <span class="custom-badge"><?php echo $row['begeni_sayisi']; ?> Beğeni</span>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Henüz beğenilen alıntı yok.</p>
        <?php endif; ?>
    </div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
    <div class="stat-card anasayfa">
        <h3>📖 En Çok Okunanlar</h3>
        <?php $sira=1; if($res_okunan->rowCount() > 0): ?>
            <?php while($row = $res_okunan->fetch(PDO::FETCH_ASSOC)): ?>
            <a href="kitap_detay.php?id=<?php echo $row['kitap_id']; ?>" class="list-link">
                <div class="list-item">
                    <span class="rank"><?php echo $sira++; ?></span>
                    <img src="../resimler/kitaplar/<?php echo $row['kapak']; ?>" class="thumb book-cover">
                    <div class="info">
                        <span class="name"><?php echo $row['baslik']; ?></span>
                        <span class="sub-text"><?php echo $row['yazar']; ?></span>
                    </div>
                    <span class="custom-badge"><?php echo $row['okunma_sayisi']; ?> Okunma</span>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Henüz veri yok.</p>
        <?php endif; ?>
    </div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
    <div class="stat-card anasayfa">
        <h3>👥 Popüler Kullanıcılar</h3>
        <?php $sira=1; if($res_populer_user->rowCount() > 0): ?>
            <?php while($row = $res_populer_user->fetch(PDO::FETCH_ASSOC)): ?>
            <a href="profil.php?id=<?php echo $row['kullanici_id']; ?>" class="list-link">
                <div class="list-item">
                    <span class="rank"><?php echo $sira++; ?></span>
                    <img src="../pp/<?php echo $row['pp']; ?>.png" class="thumb">
                    <div class="info">
                        <span class="name">@<?php echo $row['username']; ?></span>
                        <span class="sub-text">Kitap Sever</span>
                    </div>
                    <span class="custom-badge"><?php echo $row['takipci_sayisi']; ?> Takipçi</span>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Henüz veri yok.</p>
        <?php endif; ?>
    </div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
    <div class="stat-card anasayfa">
        <h3>🤓 Kitap Kurtları</h3>
        <?php $sira=1; if($res_kitapkurdu->rowCount() > 0): ?>
            <?php while($row = $res_kitapkurdu->fetch(PDO::FETCH_ASSOC)): ?>
            <a href="profil.php?id=<?php echo $row['kullanici_id']; ?>" class="list-link">
                <div class="list-item">
                    <span class="rank"><?php echo $sira++; ?></span>
                    <img src="../pp/<?php echo $row['pp']; ?>.png" class="thumb">
                    <div class="info">
                        <span class="name">@<?php echo $row['username']; ?></span>
                        <span class="sub-text">Okur</span>
                    </div>
                    <span class="custom-badge"><?php echo $row['okudugu_sayi']; ?> Kitap</span>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">Henüz veri yok.</p>
        <?php endif; ?>
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
    .hover-link:hover {
        color: #fff !important;
        padding-left: 5px;
        transition: all 0.3s ease;
    }
</style>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
</center></body></html>