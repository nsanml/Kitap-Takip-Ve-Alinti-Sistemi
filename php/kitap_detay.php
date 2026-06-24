<?php
require_once "connect.php";
session_start();

if (!isset($_SESSION['id'])){
    header("Location:uyelere_ozel.php");
    exit;
}
$uye_id = $_SESSION['id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kitaplar.php"); 
    exit;
}
$kitap_id = $_GET['id'];
$durum = $vt->prepare("SELECT status FROM user_book WHERE user_id=:uid AND book_id=:bid");
$durum->execute(['uid'=>$uye_id,'bid'=>$kitap_id]);
$mevcutDurum = $durum->fetchColumn();

//-------------------------------------------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['yorum_yap'])) {
    $yorum_icerik = htmlspecialchars(trim($_POST['yorum_icerik']));
    if (!empty($yorum_icerik)) {
        $stmt = $vt->prepare("INSERT INTO book_comments (user_id, book_id, content) VALUES (:uid, :bid, :content)");
        $sonuc = $stmt->execute([':uid' => $uye_id, ':bid' => $kitap_id, ':content' => $yorum_icerik]);
        if($sonuc){ 
            header("Location: ?id=$kitap_id&tab=yorumlar"); 
            exit; 
        }
    }
}
//-------------------------------------------------------------------------------------------------------------

if (isset($_POST['yorum_sil'])) {
    $silinecek_id = $_POST['yorum_id'];
    $stmt = $vt->prepare("DELETE FROM book_comments WHERE id = :id AND user_id = :uid");
    $sonuc = $stmt->execute([':id' => $silinecek_id, ':uid' => $uye_id]);
    
    if ($sonuc) { header("Location: ?id=$kitap_id&tab=yorumlar"); exit; }
}
//-------------------------------------------------------------------------------------------------------------

if (isset($_POST['yorum_guncelle'])) {
    $guncellenecek_id = $_POST['yorum_id'];
    $yeni_icerik = htmlspecialchars(trim($_POST['yeni_icerik']));
    
    if (!empty($yeni_icerik)) {
        $stmt = $vt->prepare("UPDATE book_comments SET content = :content WHERE id = :id AND user_id = :uid");
        $sonuc = $stmt->execute([':content' => $yeni_icerik, ':id' => $guncellenecek_id, ':uid' => $uye_id]);
        
        if ($sonuc) { header("Location: ?id=$kitap_id&tab=yorumlar"); exit; }
    }
}
//-------------------------------------------------------------------------------------------------------------

$sqlQuery = $vt->prepare("SELECT * FROM books WHERE id=:id");
$sqlQuery->execute(['id'=> $kitap_id]);
$kitap = $sqlQuery->fetch(PDO::FETCH_ASSOC);

if (!$kitap) { die("Kitap bulunamadı."); }

$sayfa = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
$limit = 5; 
$offset = ($sayfa - 1) * $limit;

$alintiSql = "SELECT 
                q.id as alinti_id, q.content, q.olusma_tarih, 
                u.id as user_id, u.username, u.pp, 
                b.id as book_id, b.baslik, b.yazar, b.kapak, 
                (SELECT COUNT(*) FROM likes WHERE quote_id = q.id) as like_count, 
                (SELECT COUNT(*) FROM likes WHERE quote_id = q.id AND user_id = :uid) as is_liked
            FROM quotes q 
            LEFT JOIN users u ON q.user_id = u.id 
            LEFT JOIN books b ON q.book_id = b.id 
            WHERE book_id=:book_id
            ORDER BY q.id DESC LIMIT :limit OFFSET :offset";

$stmt = $vt->prepare($alintiSql);
$stmt->bindValue(':uid', $uye_id, PDO::PARAM_INT);
$stmt->bindValue(':book_id', $kitap_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$alintilar = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalSql = $vt->prepare("SELECT COUNT(*) FROM quotes WHERE book_id = :book_id");
$totalSql->execute(['book_id' => $kitap_id]);
$totalQuotes = $totalSql->fetchColumn();
$toplamSayfa = ceil($totalQuotes / $limit);


$yorumSql = "SELECT c.*, u.username, u.pp 
             FROM book_comments c 
             JOIN users u ON c.user_id = u.id 
             WHERE c.book_id = :bid 
             ORDER BY c.created_at DESC";
$yorumStmt = $vt->prepare($yorumSql);
$yorumStmt->execute([':bid' => $kitap_id]);
$yorumlar = $yorumStmt->fetchAll(PDO::FETCH_ASSOC);

$aktifTab = isset($_GET['tab']) && $_GET['tab'] == 'yorumlar' ? 'yorumlar' : 'alintilar';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - <?php echo $kitap['baslik']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/kitaplar.css">
    <link rel="stylesheet" type="text/css" href="../css/alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/kitap_detay.css">

    <script src="../js/kitap_detay.js"></script>
 
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
<div class="container mt-4">
    <div class="detay-karti bg-white p-4 rounded shadow-sm"> 
        <div class="row">
            <div class="col-sm-4 text-center">
                <img src="../resimler/kitaplar/<?php echo $kitap['kapak']; ?>" class="img-fluid rounded shadow" style="max-height:350px;">
                
                <div class="d-grid gap-2 mt-3">
                    <button onclick="durumGuncelle('okudum')" class="btn btn-durum <?= ($mevcutDurum == 'okudum') ? 'active' : '' ?>">📚 Okudum</button>
                    <button onclick="durumGuncelle('okuyorum')" class="btn btn-durum <?= ($mevcutDurum == 'okuyorum') ? 'active' : '' ?>">📖 Okuyorum</button>
                    <button onclick="durumGuncelle('okuyacagim')" class="btn btn-durum <?= ($mevcutDurum == 'okuyacagim') ? 'active' : '' ?>">🔖 Okuyacağım</button>
                </div>

            </div>
            <div class="col-sm-8">
                <h2 class="detay-baslik text-dark"><?php echo $kitap['baslik'];?></h2>
                <h4 class="detay-yazar text-secondary"><?php echo $kitap['yazar'];?></h4>
                <div class="mt-3">
                    <p><span class="fw-bold">Kategori:</span> <?php echo $kitap['kategori'];?></p>
                    <p><span class="fw-bold">Basım Yılı:</span> <?php echo $kitap['yil'];?></p>
                    <p><span class="fw-bold">Sayfa Sayısı:</span> <?php echo $kitap['sayfa_sayisi'];?></p>
                </div>
                <div class="detay-ozet mt-4 p-3 bg-light rounded">
                    <strong>Konusu:</strong><br>
                    <?php echo nl2br($kitap['aciklama']);?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<div class="container mt-4 mb-5">
    
    <ul class="nav nav-tabs mb-4" id="bookTabs" role="tablist">
        <li class="nav-item"><button class="nav-link <?= ($aktifTab == 'alintilar') ? 'active' : '' ?>" id="alintilar-tab" data-bs-toggle="tab" data-bs-target="#alintilar">💬 Alıntılar (<?= $totalQuotes ?>)</button></li>
        <li class="nav-item"><button class="nav-link <?= ($aktifTab == 'yorumlar') ? 'active' : '' ?>" id="yorumlar-tab" data-bs-toggle="tab" data-bs-target="#yorumlar">📝 Yorumlar (<?= count($yorumlar) ?>)</button></li>
    </ul>

    <div class="tab-content" id="bookTabsContent">
        
        <div class="tab-pane fade <?= ($aktifTab == 'alintilar') ? 'show active' : '' ?>" id="alintilar">
            <?php if (count($alintilar) == 0): ?>
                <div class='alert alert-light text-center border'>Bu kitap için henüz alıntı paylaşılmamış.</div>
            <?php endif; ?>
            <div class="d-flex flex-column align-items-center w-100">
                <?php foreach ($alintilar as $row): 
                    $ppResim = !empty($row['pp']) ? $row['pp'] : '1'; 
                    $begenilmis = ($row['is_liked'] > 0) ? 'liked' : '';
                    $kalpIkonu = ($row['is_liked'] > 0) ? '❤️' : '🤍'; 
                ?>
                    <div class="modern-card w-100" style="max-width: 800px;">
                        <div class="m-header">
                            <a href="profil.php?id=<?= $row['user_id'] ?>"><img src="../pp/<?= $ppResim ?>.png" class="m-pp"></a>
                            <div class="m-user">
                                <a href="profil.php?id=<?= $row['user_id'] ?>" class="text-dark text-decoration-none"><h6><?= $row['username'] ?></h6></a>
                                <span class="text-muted small"><?= date("d.m.Y", strtotime($row['olusma_tarih'])) ?></span>
                            </div>
                        </div>
                        <div class="m-body p-3"><div class="m-quote fst-italic">"<?= $row['content'] ?>"</div></div>
                        <div class="m-footer p-2 border-top">
                            <span class="like-btn <?= $begenilmis ?>" onclick="begen(this, <?= $row['alinti_id'] ?>)" style="cursor:pointer;">
                                <span class="icon"><?= $kalpIkonu ?></span> <span class="count" id="like-count-<?= $row['alinti_id'] ?>"><?= $row['like_count'] ?></span>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($toplamSayfa > 1): ?>
                <nav aria-label="Sayfalama" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($sayfa <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?id=<?= $kitap_id ?>&sayfa=<?= $sayfa - 1 ?>&tab=alintilar">‹</a></li>
                        <?php for($s = 1; $s <= $toplamSayfa; $s++): ?>
                            <li class="page-item <?= ($s == $sayfa) ? 'active' : '' ?>"><a class="page-link" href="?id=<?= $kitap_id ?>&sayfa=<?= $s ?>&tab=alintilar"><?= $s ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($sayfa >= $toplamSayfa) ? 'disabled' : '' ?>"><a class="page-link" href="?id=<?= $kitap_id ?>&sayfa=<?= $sayfa + 1 ?>&tab=alintilar">›</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade <?= ($aktifTab == 'yorumlar') ? 'show active' : '' ?>" id="yorumlar">
            
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3 anasayfa">Bu kitap hakkında ne düşünüyorsun? 🤔</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <textarea name="yorum_icerik" class="form-control anasayfa" rows="3" placeholder="Kitap hakkında ne düşünüyorsun?" required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="yorum_yap" class="btn btn-yorum anasayfa"> 📝 Yorumu Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="yorum-listesi">
                <?php if(count($yorumlar) > 0): ?>
                    <?php foreach($yorumlar as $yorum): 
                        $y_pp = !empty($yorum['pp']) ? $yorum['pp'] : '1';
                    ?>
                        <div class="d-flex comment-card">
                            
                            <?php if ($yorum['user_id'] == $uye_id): ?>
                            <div class="comment-options dropdown">
                                <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v text-secondary"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item anasayfa" href="#" onclick="openEditModal(<?= $yorum['id'] ?>, '<?= addslashes(htmlspecialchars($yorum['content'])) ?>')">
                                            ✏️ Düzenle
                                        </a>
                                    </li>
                                    <li>
                                        <form method="POST" action="" onsubmit="return confirm('Bu yorumu silmek istediğine emin misin?');">
                                            <input type="hidden" name="yorum_id" value="<?= $yorum['id'] ?>">
                                            <button type="submit" name="yorum_sil" class="dropdown-item text-danger">🗑️ Sil</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <div class="flex-shrink-0">
                                <img src="../pp/<?= $y_pp ?>.png" class="rounded-circle" width="50" height="50">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1 fw-bold">
                                        <a href="profil.php?id=<?= $yorum['user_id'] ?>" class="text-decoration-none text-reset">
                                            <?= $yorum['username'] ?>
                                        </a>
                                        
                                        <small class="text-muted fw-normal ms-2" style="font-size:0.8rem">
                                            <?= date("d.m.Y H:i", strtotime($yorum['created_at'])) ?>
                                        </small>
                                    </h6>
                                <p class="mb-0 text-dark"><?= nl2br($yorum['content']) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">Bu kitaba henüz yorum yapılmamış. İlk yorumu sen yap!</div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////////////////////// -->

<div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title">Yorumu Düzenle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST" action="">
          <div class="modal-body">
            <input type="hidden" name="yorum_id" id="edit_yorum_id">
            <div class="mb-3">
                <textarea name="yeni_icerik" id="edit_yorum_icerik" class="form-control" rows="4" required></textarea>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
            <button type="submit" name="yorum_guncelle" class="btn btn-yorum">Kaydet</button>
          </div>
      </form>
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
</body>
</html>