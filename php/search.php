<?php
require_once "connect.php";
session_start();

$user_id = $_SESSION['id'];
$aranan = "";
$sonuc_kitap = [];
$sonuc_user = [];
$arama_yapildi = false;

if (isset($_GET['q'])) {
    $aranan = strip_tags(trim($_GET['q'])); 

    if (!empty($aranan)) {
        $arama_yapildi = true;

        $sqlKitap = $vt->prepare("SELECT * FROM books WHERE baslik LIKE :kelime OR yazar LIKE :kelime LIMIT 10");
        $sqlKitap->execute([':kelime' => "%" . $aranan . "%"]);
        $sonuc_kitap = $sqlKitap->fetchAll(PDO::FETCH_ASSOC);

        $sqlUser = $vt->prepare("SELECT * FROM users WHERE username LIKE :kelime AND id != :ben LIMIT 10");
        $sqlUser->execute([':kelime' => "%" . $aranan . "%", ':ben' => $user_id]);
        $sonuc_user = $sqlUser->fetchAll(PDO::FETCH_ASSOC);

    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - Ara</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/search.css">
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

<div class="container mt-5 mb-5">
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="search-container text-center">
                <h3 class="mb-4" style="color:#880e4f;">Ne aramak istiyorsun?</h3>
                <form method="GET" action="search.php" class="d-flex gap-2">
                    <input type="text" name="q" class="form-control search-input" placeholder="Kitap, yazar veya kullanıcı adı..." value="<?= htmlspecialchars($aranan) ?>" required>
                    <button type="submit" class="btn btn-search" style="background-color: #ec407a; color: white;">ARA</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($arama_yapildi): ?>
        
        <div class="mt-5">
            <div class="row">
                
                <div class="col-md-6 pe-md-5 mb-5" style="border-right: 1px solid #ddd;">
                    <h4 class="sonuc-baslik">
                        <i class="fa-solid fa-users me-2"></i> Kullanıcılar
                    </h4>

                    <?php if (count($sonuc_user) > 0): ?>
                        <div class="row">
                            <?php foreach ($sonuc_user as $u) { ?>
                                <div class="col-12 mb-3"> 
                                    <div class="card ozel-kart p-3">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                                $pp = "1";
                                                if($u['pp'] != "") { $pp = $u['pp']; }
                                            ?>
                                            <img src="../pp/<?php echo $pp; ?>.png" width="60" height="60" class="rounded-circle border border-2 border-white shadow-sm">
                                            
                                            <div class="ms-3">
                                                <h5 class="m-0" style="color: #ec407a; font-weight: bold;"><?php echo $u['username']; ?></h5>
                                                <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                                    <?php echo substr($u['bio'], 0, 40); ?>...
                                                </small>
                                                <a href="profil.php?id=<?php echo $u['id']; ?>" class="btn btn-light btn-sm rounded-pill border mt-2" style="font-size: 12px;">Profili Gör</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php else: ?>
                        <div class="bulunamadi-box">
                            <i class="fa-solid fa-user-slash fa-2x mb-3"></i>
                            <h5>Kullanıcı Bulunamadı</h5>
                            <p class="small text-muted mb-0">Bu isimde bir üyemiz yok.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 ps-md-4">
                    <h4 class="sonuc-baslik">
                        <i class="fa-solid fa-book me-2"></i> Kitaplar & Yazarlar
                    </h4>

                    <?php if (count($sonuc_kitap) > 0): ?>
                        <div class="row">
                            <?php foreach ($sonuc_kitap as $kitap) { ?>
                                <div class="col-sm-6 mb-4">
                                    <div class="card h-100 ozel-kart">
                                        <?php 
                                            if($kitap['kapak'] != "") {
                                                $resim = "../resimler/kitaplar/" . $kitap['kapak'];
                                            } else {
                                                $resim = "../resimler/kitaplar/0.png";
                                            }
                                        ?>
                                        <div class="p-2 text-center">
                                            <img src="<?php echo $resim; ?>" class="rounded-3 shadow-sm" style="height: 180px; width: auto; max-width: 100%; object-fit: cover;">
                                        </div>
                                        
                                        <div class="card-body text-center p-2">
                                            <h6 class="card-title text-truncate fw-bold text-dark">
                                                <?php echo $kitap['baslik']; ?>
                                            </h6>
                                            <p class="card-text text-secondary small mb-2"><?php echo $kitap['yazar']; ?></p>
                                            <a href="kitap_detay.php?id=<?php echo $kitap['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill w-100">İncele</a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php else: ?>
                        <div class="bulunamadi-box">
                            <i class="fa-solid fa-book-open-reader fa-2x mb-3"></i>
                            <h5>Kitap Bulunamadı</h5>
                            <p class="small text-muted mb-0">Aradığınız kitap veya yazar kütüphanemizde yok.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div> </div> <?php endif; ?>

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
</body>
</html>