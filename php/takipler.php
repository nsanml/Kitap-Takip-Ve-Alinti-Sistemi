<?php 
require_once "connect.php"; 
session_start(); 
if (!isset($_SESSION['id'])) {
    header("Location:uyelere_ozel.php");
    exit; 
}

$uye_id = $_SESSION['id']; 
$limit = 5; 
$sayfa = isset($_GET['sayfa']) && is_numeric($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
if ($sayfa < 1) $sayfa = 1;
$offset = ($sayfa - 1) * $limit;

try {
    $countSql = "
        SELECT 
        (SELECT COUNT(*) FROM quotes q INNER JOIN followers f ON q.user_id = f.following_id WHERE f.follower_id = :uid) 
        + 
        (SELECT COUNT(*) FROM book_comments c INNER JOIN followers f ON c.user_id = f.following_id WHERE f.follower_id = :uid)
        as toplam";
    $countStmt = $vt->prepare($countSql);
    $countStmt->bindValue(':uid', $uye_id, PDO::PARAM_INT);
    $countStmt->execute();
    $toplamKayit = $countStmt->fetchColumn();
    $toplamSayfa = ceil($toplamKayit / $limit);
    if($toplamSayfa < 1) $toplamSayfa = 1;

    $sql = "
    (SELECT 
        'quote' as type,
        q.id as benzersiz_id, 
        q.content, 
        q.olusma_tarih, 
        u.id as user_id, u.username, u.pp, 
        b.id as book_id, b.baslik, b.yazar, b.kapak, 
        (SELECT COUNT(*) FROM likes WHERE quote_id = q.id) as like_count, 
        (SELECT COUNT(*) FROM likes WHERE quote_id = q.id AND user_id = :uid) as is_liked
    FROM quotes q 
    INNER JOIN followers f ON q.user_id = f.following_id 
    LEFT JOIN users u ON q.user_id = u.id 
    LEFT JOIN books b ON q.book_id = b.id 
    WHERE f.follower_id = :uid)

    UNION ALL

    (SELECT 
        'comment' as type,
        c.id as benzersiz_id, 
        c.content,               /* DÜZELTİLDİ: Senin tablondaki sütun adı */
        c.created_at as olusma_tarih, /* DÜZELTİLDİ: Senin tablondaki sütun adı */
        u.id as user_id, u.username, u.pp, 
        b.id as book_id, b.baslik, b.yazar, b.kapak, 
        0 as like_count,         /* Yorumlarda beğeni yok, 0 gönderiyoruz */
        0 as is_liked
    FROM book_comments c         /* DÜZELTİLDİ: Tablo adı book_comments */
    INNER JOIN followers f ON c.user_id = f.following_id 
    LEFT JOIN users u ON c.user_id = u.id 
    LEFT JOIN books b ON c.book_id = b.id 
    WHERE f.follower_id = :uid)

    ORDER BY olusma_tarih DESC LIMIT :limit OFFSET :offset";
            
    $stmt = $vt->prepare($sql);
    $stmt->bindValue(':uid', $uye_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $icerikler = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - Takipler</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/menu_alinti.css">
    <link rel="stylesheet" type="text/css" href="../css/oyun.css">
    <link rel="stylesheet" type="text/css" href="../css/alinti.css">
    <script src="../js/alinti.js"></script>


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

<div class="container mt-5 mb-5 feed-container">
    
    <?php if (count($icerikler) == 0): ?>
        <div class='alert alert-warning text-center'>Takip ettiğiniz kişilerin henüz alıntısı veya yorumu yok.</div>
    <?php endif; ?>

    <div class="d-flex flex-column align-items-center w-100">
        <?php foreach ($icerikler as $row): ?>
            <?php 
                $ppResim = !empty($row['pp']) ? $row['pp'] : '1.png'; 
                $kitapResim = !empty($row['kapak']) ? $row['kapak'] : ''; 
                $begenilmis = ($row['is_liked'] > 0) ? 'liked' : '';
                $kalpIkonu = ($row['is_liked'] > 0) ? '❤️' : '🤍'; 

                $isComment = ($row['type'] === 'comment');
                
                $cardStyle = $isComment ? 'background-color: #fff0f5; border: 1px solid #ffebf2;' : '';
                
                $turEtiketi = $isComment 
                    ? '<span class="badge mb-2" style="background-color:#ff9ccb; color:white;">📖 Kitap Yorumu</span>' 
                    : '';
            ?>
            
            <div class="modern-card" style="<?= $cardStyle ?>">
                
                <div class="m-header">
                    <a href="profil.php?id=<?= $row['user_id'] ?>">
                        <img src="../pp/<?= $ppResim ?>.png" class="m-pp">
                    </a>
                    <div class="m-user">
                        <a href="profil.php?id=<?= $row['user_id'] ?>" style="text-decoration:none; color: inherit;">
                            <h6><?= $row['username'] ?></h6>
                        </a>
                        <small class="text-muted"><?= date("d.m.Y H:i", strtotime($row['olusma_tarih'])) ?></small>
                    </div>
                </div>

                <div class="m-body">
                    <?= $turEtiketi ?>

                    <div class="m-quote" style="font-size: 1.1rem; line-height: 1.6; margin-top:10px;">
                        <?= $row['content'] ?>
                    </div>
                    
                    <a href="kitap_detay.php?id=<?= $row['book_id'] ?>" class="book-badge mt-3 w-100 text-decoration-none">
                        <img src="../resimler/kitaplar/<?= $kitapResim ?>" alt="Kapak" 
                             onerror="this.src='https://via.placeholder.com/60x90?text=Kapak';">
                        <div class="book-info">
                            <span class="book-title text-dark"><?= $row['baslik'] ?></span>
                            <span class="book-author text-muted"><?= $row['yazar'] ?></span>
                        </div>
                    </a>
                </div>

                <div class="m-footer">
                    <div class="m-actions">
                        <?php if (!$isComment): ?>
                            <span class="like-btn <?= $begenilmis ?>" onclick="begen(this, <?= $row['benzersiz_id'] ?>)">
                                <span class="icon"><?= $kalpIkonu ?></span> 
                                <span class="count" id="like-count-<?= $row['benzersiz_id'] ?>"><?= $row['like_count'] ?></span>
                            </span>
                        <?php else: ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>
    </div>

    <?php if ($toplamSayfa > 1): ?>
        <nav aria-label="Sayfalama" class="mt-4 mb-5">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($sayfa <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?sayfa=<?= $sayfa - 1 ?>">‹</a>
                </li>
                <?php for($s = 1; $s <= $toplamSayfa; $s++): ?>
                    <li class="page-item <?= ($s == $sayfa) ? 'active' : '' ?>">
                        <a class="page-link" href="?sayfa=<?= $s ?>"><?= $s ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($sayfa >= $toplamSayfa) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?sayfa=<?= $sayfa + 1 ?>">›</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>

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