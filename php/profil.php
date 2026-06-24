<?php
require_once "connect.php";
session_start();

if (!isset($_SESSION['id'])) { header("Location: giris_kayit.php"); exit; }

$my_id = $_SESSION['id'];

//--------------------------------------------------------------------------------------------------

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $profil_id = $_GET['id'];
} else {
    $profil_id = $my_id;
}

$benim_profilim = ($profil_id == $my_id);

//--------------------------------------------------------------------------------------------------

if ($benim_profilim && isset($_POST['yorum_sil'])) {
    $silinecek_id = $_POST['yorum_id'];
    $stmt = $vt->prepare("DELETE FROM book_comments WHERE id = :id AND user_id = :uid");
    $sonuc = $stmt->execute([':id' => $silinecek_id, ':uid' => $my_id]);
    
    if ($sonuc) { header("Location: profil.php?id=$profil_id"); exit; }
}

//--------------------------------------------------------------------------------------------------

if ($benim_profilim && isset($_POST['yorum_guncelle'])) {
    $guncellenecek_id = $_POST['yorum_id'];
    $yeni_icerik = trim($_POST['yeni_icerik']);

    if (!empty($yeni_icerik)) {
        $stmt = $vt->prepare("UPDATE book_comments SET content = :content WHERE id = :id AND user_id = :uid");
        $sonuc = $stmt->execute([':content' => $yeni_icerik, ':id' => $guncellenecek_id, ':uid' => $my_id]);
        
        if ($sonuc) { header("Location: profil.php?id=$profil_id"); exit; }
    }
}

//--------------------------------------------------------------------------------------------------

$kullaniciSql = $vt->prepare("SELECT * FROM users WHERE id = :id");
$kullaniciSql->execute(['id' => $profil_id]);
$profil = $kullaniciSql->fetch(PDO::FETCH_ASSOC);

if (!$profil) { echo "Kullanıcı bulunamadı."; exit; }

//--------------------------------------------------------------------------------------------------

$takipEdiyorMuyum = false;
if (!$benim_profilim) {
    $takipSql = $vt->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = :ben AND following_id = :o");
    $takipSql->execute([':ben' => $my_id, ':o' => $profil_id]);
    $takipEdiyorMuyum = $takipSql->fetchColumn() > 0;
}

//--------------------------------------------------------------------------------------------------

$takipciSayisi = $vt->query("SELECT COUNT(*) FROM followers WHERE following_id = $profil_id")->fetchColumn();
$takipEdilenSayisi = $vt->query("SELECT COUNT(*) FROM followers WHERE follower_id = $profil_id")->fetchColumn();
$alintiSayisi = $vt->query("SELECT COUNT(*) FROM quotes WHERE user_id = $profil_id")->fetchColumn();
$kitapsayisi = $vt->query("SELECT COUNT(*) FROM user_book WHERE user_id = $profil_id AND status = 'okudum'")->fetchColumn();


//--------------------------------------------------------------------------------------------------

$kitapSql = "SELECT b.id, b.baslik, b.kapak, ub.status 
             FROM user_book ub 
             JOIN books b ON ub.book_id = b.id 
             WHERE ub.user_id = :uid ORDER BY ub.eklenme_tarihi DESC";
$stmt = $vt->prepare($kitapSql);
$stmt->execute([':uid' => $profil_id]);
$tumKitaplar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$okudum = []; $okuyorum = []; $okuyacagim = [];
foreach ($tumKitaplar as $k) {
    if ($k['status'] == 'okudum') $okudum[] = $k;
    elseif ($k['status'] == 'okuyorum') $okuyorum[] = $k;
    elseif ($k['status'] == 'okuyacagim') $okuyacagim[] = $k;
}

//--------------------------------------------------------------------------------------------------

$alintiSql = "SELECT q.id as alinti_id, q.content, q.olusma_tarih, 
              b.baslik, b.kapak, b.id as book_id,
              (SELECT COUNT(*) FROM likes WHERE quote_id = q.id) as like_count,
              (SELECT COUNT(*) FROM likes WHERE quote_id = q.id AND user_id = :uid) as is_liked
              FROM quotes q
              LEFT JOIN books b ON q.book_id = b.id
              WHERE q.user_id = :pid
              ORDER BY q.id DESC";
$stmt2 = $vt->prepare($alintiSql);
$stmt2->execute([':pid' => $profil_id, ':uid' => $my_id]);
$alintilar = $stmt2->fetchAll(PDO::FETCH_ASSOC);

//--------------------------------------------------------------------------------------------------

$yorumSql = "SELECT c.id as yorum_id, c.content, c.created_at, 
             b.baslik, b.kapak, b.id as book_id
             FROM book_comments c
             JOIN books b ON c.book_id = b.id
             WHERE c.user_id = :uid
             ORDER BY c.id DESC";
$stmt3 = $vt->prepare($yorumSql);
$stmt3->execute([':uid' => $profil_id]);
$yorumlar = $stmt3->fetchAll(PDO::FETCH_ASSOC);


//--------------------------------------------------------------------------------------------------

$sistemdekiTumKitaplar = [];
if ($benim_profilim) {
    $uye_id = $_SESSION['id']; 
    
    $sql = "SELECT b.id, b.baslik 
            FROM books b 
            INNER JOIN user_book ub ON b.id = ub.book_id 
            WHERE ub.user_id = ? AND ub.status = 'okudum' 
            ORDER BY b.baslik ASC";
            
    $bkSql = $vt->prepare($sql);
    $bkSql->execute([$uye_id]);
    
    $sistemdekiTumKitaplar = $bkSql->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - Profil</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/profil.css">
    <script language="javascript" src="../js/profil.js"></script>
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
<div class="container mb-5">
    
    <div class="profil-header">
        <?php $pp_img = !empty($profil['pp']) ? $profil['pp'] : '1'; ?>
        <img src="../pp/<?= $pp_img ?>.png" class="profil-img">
        
        <h2 class="user-name">@<?= $profil['username'] ?></h2>

        <?php if (!empty($profil['bio'])): ?>
            <div class="user-bio"><?= nl2br(htmlspecialchars($profil['bio'])) ?></div>
        <?php endif; ?>
        
        <div class="user-stats">
            <div class="stat-box"><span class="stat-num" id="takipci-sayisi"><?= $takipciSayisi ?></span><span class="stat-label">Takipçi</span></div>
            <div class="stat-box"><span class="stat-num"><?= $takipEdilenSayisi ?></span><span class="stat-label">Takip</span></div>
            <div class="stat-box"><span class="stat-num"><?= $alintiSayisi ?></span><span class="stat-label">Alıntı</span></div>
            <div class="stat-box"><span class="stat-num"><?= $kitapsayisi ?></span><span class="stat-label">Okuduğu Kitap Sayısı</span></div>
        </div>

        <div class="mt-4">
            <?php if ($benim_profilim): ?>
                <a href="ayarlar.php" class="btn btn-edit-profile">Profili Düzenle</a>
            <?php else: ?>
                <button onclick="takipEt(<?= $profil_id ?>, '<?= $takipEdiyorMuyum ? 'takibi_birak' : 'takip_et' ?>')" id="btn-takip" class="btn btn-follow <?= $takipEdiyorMuyum ? 'btn-unfollow' : '' ?>">
                    <?= $takipEdiyorMuyum ? 'Takibi Bırak' : 'Takip Et' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#alintilar">📝 Alıntılar</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#yorumlar">💬 Yorumlar</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#kutuphane">📚 Kütüphane</button></li>
    </ul>

    <div class="tab-content mt-4" id="myTabContent">
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

        <div class="tab-pane fade show active" id="alintilar">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <?php if ($benim_profilim): ?>
                        <button class="btn-new-quote" data-bs-toggle="modal" data-bs-target="#alintiModal" onclick="modalSifirla()">
                            + Yeni Alıntı Paylaş
                        </button>
                    <?php endif; ?>

                    <?php if (count($alintilar) == 0): ?>
                        <div class="alert alert-light text-center" style="color:#ec407a">Henüz alıntı yok.</div>
                    <?php endif; ?>

                    <?php foreach ($alintilar as $row): ?>
                        <div class="alinti-karti shadow-sm">
                            
                            <?php if ($benim_profilim): ?>
                            <div class="dropdown alinti-actions">
                                <button class="btn-icon" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="alintiDuzenle(<?= $row['alinti_id'] ?>, '<?= addslashes($row['content']) ?>', <?= $row['book_id'] ?>)">✏️ Düzenle</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="alintiSil(<?= $row['alinti_id'] ?>)">🗑️ Sil</a></li>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex align-items-center mb-2 border-bottom pb-2">
                                <img src="../resimler/kitaplar/<?= $row['kapak'] ?>" style="width:30px; height:45px; object-fit:cover; border-radius:2px; margin-right:10px;">
                                <a href="kitap_detay.php?id=<?= $row['book_id'] ?>" class="text-decoration-none">
                                    <span class="q-book-title"><?= $row['baslik'] ?></span>
                                </a>
                                <small class="ms-auto text-muted"><?= date("d.m.Y", strtotime($row['olusma_tarih'])) ?></small>
                            </div>
                            
                            <p style="font-size:1.1rem; font-style:italic; color:#444;">"<?= nl2br($row['content']) ?>"</p>
                            
                            <div class="text-end">
                                <span style="color:#e91e63;">❤️ <?= $row['like_count'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

        <div class="tab-pane fade" id="yorumlar">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <?php if (count($yorumlar) == 0): ?>
                        <div class="alert alert-light text-center anasayfa" style="color:pink">Henüz kitap yorumu yapılmamış.</div>
                    <?php endif; ?>

                    <?php foreach ($yorumlar as $y): ?>
                        <div class="alinti-karti shadow-sm">
                            
                            <?php if ($benim_profilim): ?>
                            <div class="dropdown alinti-actions">
                                <button class="btn-icon" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item anasayfa" href="#" onclick="yorumDuzenleAc(<?= $y['yorum_id'] ?>, '<?= addslashes(htmlspecialchars($y['content'])) ?>')">
                                            ✏️ Düzenle
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="" onsubmit="return confirm('Bu yorumu silmek istediğine emin misin?');">
                                            <input type="hidden" name="yorum_id" value="<?= $y['yorum_id'] ?>">
                                            <button type="submit" name="yorum_sil" class="dropdown-item text-danger">🗑️ Sil</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex align-items-center mb-2 border-bottom pb-2">
                                <img src="../resimler/kitaplar/<?= $y['kapak'] ?>" style="width:30px; height:45px; object-fit:cover; border-radius:2px; margin-right:10px;">
                                <a href="kitap_detay.php?id=<?= $y['book_id'] ?>" class="text-decoration-none">
                                    <span class="q-book-title"><?= $y['baslik'] ?></span>
                                </a>
                                <small class="ms-auto text-muted"><?= date("d.m.Y", strtotime($y['created_at'])) ?></small>
                            </div>
                            
                            <p style="font-size:1.1rem; font-style:italic; color:#444;line-height: 1.5;">
                                <?= nl2br(htmlspecialchars($y['content'])) ?>
                            </p>
                            
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

        <div class="tab-pane fade" id="kutuphane">
            <div class="row">
                <div class="col-md-4 mb-4"><h5 class="status-header">✅ Okudum (<?= count($okudum) ?>)</h5><div class="book-grid"><?php foreach ($okudum as $k) echo "<a href='kitap_detay.php?id={$k['id']}'><img src='../resimler/kitaplar/{$k['kapak']}' class='mini-book'></a>"; ?></div></div>
                <div class="col-md-4 mb-4"><h5 class="status-header">📖 Okuyorum (<?= count($okuyorum) ?>)</h5><div class="book-grid"><?php foreach ($okuyorum as $k) echo "<a href='kitap_detay.php?id={$k['id']}'><img src='../resimler/kitaplar/{$k['kapak']}' class='mini-book'></a>"; ?></div></div>
                <div class="col-md-4 mb-4"><h5 class="status-header">🔖 Okuyacağım (<?= count($okuyacagim) ?>)</h5><div class="book-grid"><?php foreach ($okuyacagim as $k) echo "<a href='kitap_detay.php?id={$k['id']}'><img src='../resimler/kitaplar/{$k['kapak']}' class='mini-book'></a>"; ?></div></div>
            </div>
        </div>

    </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<div class="modal fade" id="alintiModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#fce4ec;">
        <h5 class="modal-title" style="color:#880e4f;" id="modalBaslik">Yeni Alıntı Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="alintiForm">
            <input type="hidden" id="modal_islem" value="ekle">
            <input type="hidden" id="modal_alinti_id" value="">

            <div class="mb-3">
                <label class="form-label">Hangi Kitap?</label>
                <select class="form-select" id="modal_book_id" required>
                    <option value="">Kitap Seçiniz...</option>
                    <?php foreach ($sistemdekiTumKitaplar as $bk): ?>
                        <option value="<?= $bk['id'] ?>"><?= $bk['baslik'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Alıntı Metni</label>
                <textarea class="form-control" id="modal_content" rows="4" placeholder="Buraya yazın..." required></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="button" class="btn btn-follow" onclick="alintiKaydet()">Kaydet</button>
      </div>
    </div>
  </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<div class="modal fade" id="alintiModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#fce4ec;">
        <h5 class="modal-title" style="color:#880e4f;" id="modalBaslik">Yeni Alıntı Ekle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="alintiForm">
            <input type="hidden" id="modal_islem" value="ekle">
            <input type="hidden" id="modal_alinti_id" value="">

            <div class="mb-3">
                <label class="form-label">Hangi Kitap?</label>
                <select class="form-select" id="modal_book_id" required>
                    <option value="">Kitap Seçiniz...</option>
                    <?php foreach ($sistemdekiTumKitaplar as $bk): ?>
                        <option value="<?= $bk['id'] ?>"><?= $bk['baslik'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Alıntı Metni</label>
                <textarea class="form-control" id="modal_content" rows="4" placeholder="Buraya yazın..." required></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
        <button type="button" class="btn btn-follow" onclick="alintiKaydet()">Kaydet</button>
      </div>
    </div>
  </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<div class="modal fade" id="yorumDuzenleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:#fce4ec;">
        <h5 class="modal-title anasayfa" style="color:#880e4f;">Yorumu Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="">
            <input type="hidden" name="yorum_id" id="duzenle_yorum_id">
            <div class="mb-3">
                <label class="form-label anasayfa">Yorumunuz:</label>
                <textarea class="form-control" name="yeni_icerik" id="duzenle_yorum_icerik" rows="5" required></textarea>
            </div>
            <div class="text-end">
                <button type="submit" name="yorum_guncelle" class="btn btn-follow anasayfa">Güncelle</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
</body>
</html>