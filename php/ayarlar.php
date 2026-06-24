<?php
require_once "connect.php";
session_start();

if (!isset($_SESSION['id'])) { header("Location: giris_kayit.php"); exit; }

$user_id = $_SESSION['id'];
$mesaj = "";
$mesaj_tur = ""; 


$sql = $vt->prepare("SELECT * FROM users WHERE id = :id");
$sql->execute(['id' => $user_id]);
$uye = $sql->fetch(PDO::FETCH_ASSOC);

$mevcut_pp = $uye['pp'];

if (isset($_POST['profil_guncelle'])) {
    $yeni_username = trim($_POST['username']);
    $yeni_bio = trim($_POST['hakkimda']);
    $secilen_avatar = $_POST['img']; 

    if (!empty($yeni_username) && is_numeric($secilen_avatar)) {
        
        $updateSql = "UPDATE users SET username = :user, bio = :bio, pp = :pp WHERE id = :id";
        $stmt = $vt->prepare($updateSql);
        $sonuc = $stmt->execute([
            ':user' => $yeni_username,
            ':bio'  => $yeni_bio,
            ':pp'   => $secilen_avatar,
            ':id'   => $user_id
        ]);

        if ($sonuc) {
            $mesaj = "Profiliniz başarıyla güncellendi!";
            $mesaj_tur = "success";
            $_SESSION['username'] = $yeni_username;
            header("Refresh: 2; url=ayarlar.php");
        } else {
            $mesaj = "Güncelleme sırasında bir hata oluştu.";
            $mesaj_tur = "danger";
        }
    } else {
        $mesaj = "Lütfen geçerli bilgiler giriniz.";
        $mesaj_tur = "danger";
    }
}


if (isset($_POST['sifre_degistir'])) {
    $eski_sifre = $_POST['eski_sifre'];
    $yeni_sifre = $_POST['yeni_sifre'];
    $yeni_tekrar = $_POST['yeni_tekrar'];

    if ($eski_sifre == $uye['password']) {
        if ($yeni_sifre == $yeni_tekrar) {
            if (strlen($yeni_sifre) >= 6) {
                $sifreSql = $vt->prepare("UPDATE users SET password = :pass WHERE id = :id");
                $sifreSql->execute([':pass' => $yeni_sifre, ':id' => $user_id]);
                
                $mesaj = "Şifreniz başarıyla değiştirildi.";
                $mesaj_tur = "success";
            } else {
                $mesaj = "Yeni şifre en az 6 karakter olmalı.";
                $mesaj_tur = "danger";
            }
        } else {
            $mesaj = "Yeni şifreler uyuşmuyor.";
            $mesaj_tur = "danger";
        }
    } else {
        $mesaj = "Eski şifreniz hatalı.";
        $mesaj_tur = "danger";
    }
}


if (isset($_POST['hesap_sil'])) {
    $silSql = $vt->prepare("DELETE FROM users WHERE id = :id");
    $silIslemi = $silSql->execute([':id' => $user_id]);

    if ($silIslemi) {
        session_destroy();
        header("Location: giris_kayit.php?durum=hesapsilindi");
        exit;
    } else {
        $mesaj = "Hesap silinirken bir hata oluştu.";
        $mesaj_tur = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Kitap Durağı - Ayarlar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/menu_c.css">
    <link rel="stylesheet" href="../css/ayarlar.css">
    <script language="javascript" src="../js/ayarlar.js"></script>
</head>
<body>

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
    
    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $mesaj_tur ?> text-center shadow-sm">
            <?= $mesaj ?>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <div class="col-md-7">
            <div class="settings-card">
                <h4 class="settings-title">👤 Profil Bilgileri</h4>
                
                <form method="POST">
                    
                    <div class="mb-4 text-center">
                        <label class="form-label d-block mb-3">Profil Resmi</label>
                        
                        <input type="hidden" name="img" id="img" value="<?= $mevcut_pp ?>">

                        <div class="p-3 border rounded" style="background-color: #fff0f5;">
                            <div class="row g-2 justify-content-center mb-2">
                                <?php for ($i = 1; $i <= 9; $i++) { 
                                    $activeClass = ($i == $mevcut_pp) ? 'selected' : ''; 
                                ?>
                                    <div class="col-auto">
                                        <img src="../pp/<?php echo $i; ?>.png" 
                                             class="pp-img <?php echo $activeClass; ?>" 
                                             data-num="<?php echo $i; ?>" 
                                             onclick="selectAvatar(this)"
                                             onerror="this.style.display='none';"
                                             style="cursor:pointer; width:55px; height:55px;">
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="row g-2 justify-content-center">
                                <?php for ($i = 10; $i <= 18; $i++) { 
                                    $activeClass = ($i == $mevcut_pp) ? 'selected' : '';
                                ?>
                                    <div class="col-auto">
                                        <img src="../pp/<?php echo $i; ?>.png" 
                                             class="pp-img <?php echo $activeClass; ?>" 
                                             data-num="<?php echo $i; ?>" 
                                             onclick="selectAvatar(this)"
                                             onerror="this.style.display='none';"
                                             style="cursor:pointer; width:55px; height:55px;">
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kullanıcı Adı</label>
                        <input type="text" name="username" class="form-control" value="<?= $uye['username'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="hakkimda" class="form-control" rows="3" placeholder="Kendinizden bahsedin..."><?= isset($uye['bio']) ? $uye['bio'] : '' ?></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="profil_guncelle" class="btn btn-pembe">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-5">
            <div class="settings-card">
                <h4 class="settings-title">🔒 Şifre Değiştir</h4>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Mevcut Şifre</label>
                        <input type="password" name="eski_sifre" class="form-control" placeholder="******" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre</label>
                        <input type="password" name="yeni_sifre" class="form-control" placeholder="En az 6 karakter" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="yeni_tekrar" class="form-control" placeholder="******" required>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="sifre_degistir" class="btn btn-pembe" style="background-color: #ad1457;">Şifreyi Güncelle</button>
                    </div>
                </form>
            </div>

            <div class="settings-card danger-zone">
                <h4 class="settings-title text-danger" style="border-color: #f8d7da;">⚠️ Hesabı Sil</h4>
                <p class="text-muted small">
                    Hesabınızı silerseniz, kayıtlı tüm alıntılarınız ve yorumlarınız kalıcı olarak silinecektir.
                </p>
                <div class="d-grid">
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#silModal">
                        Hesabımı Kalıcı Olarak Sil
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>

<div class="modal fade" id="silModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Emin misiniz?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <p class="fw-bold">Bu işlem geri alınamaz!</p>
        <p>Hesabınızı silmek istediğinize emin misiniz? Bütün verileriniz kaybolacaktır.</p>
      </div>
      <div class="modal-footer">
        <form method="POST">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Vazgeç</button>
            <button type="submit" name="hesap_sil" class="btn btn-danger">Evet, Sil</button>
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

</body>
</html>