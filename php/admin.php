<?php
session_start();
require_once 'connect.php'; 

$stmt = $vt->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

if ($currentUser['role'] == '0') {
    die("Yetkisiz Erişim.");
}

//---------------------------------------------------------------------------------------

if (isset($_GET['delete_book'])) {
    $id = $_GET['delete_book'];
    try {
        $vt->prepare("DELETE FROM book_tags WHERE book_id = ?")->execute([$id]);
        $vt->prepare("DELETE FROM user_book WHERE book_id = ?")->execute([$id]);
        $vt->prepare("DELETE FROM quotes WHERE book_id = ?")->execute([$id]);
        $vt->prepare("DELETE FROM likes WHERE book_id = ?")->execute([$id]);
        
        $vt->prepare("DELETE FROM book_comments WHERE book_id = ?")->execute([$id]); 
        
        
        $stmt = $vt->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: admin.php?msg=kitap_silindi");
        exit;
    } catch (PDOException $e) {
        echo "Hata: " . $e->getMessage();
    }
}

//---------------------------------------------------------------------------------------

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    if ($id != 1) { 
        $stmt = $vt->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: admin.php?msg=uye_silindi");
    exit;
}

//---------------------------------------------------------------------------------------

if (isset($_GET['delete_quote'])) {
    $id = $_GET['delete_quote'];
    $stmt = $vt->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php?msg=alinti_silindi");
    exit;
}

//---------------------------------------------------------------------------------------

if (isset($_GET['delete_comment'])) {
    $id = $_GET['delete_comment'];
    $stmt = $vt->prepare("DELETE FROM book_comments WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php?msg=yorum_silindi");
    exit;
}

//---------------------------------------------------------------------------------------

$editMode = false;
$editData = [];

if (isset($_GET['edit_book'])) {
    $editMode = true;
    $id = $_GET['edit_book'];
    $stmt = $vt->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

//---------------------------------------------------------------------------------------

if (isset($_POST['save_book'])) { 
    $baslik = $_POST['baslik'];
    $yazar = $_POST['yazar'];
    $yil = $_POST['yil'];
    $sayfa_sayisi = $_POST['sayfa_sayisi'];
    $kategori = $_POST['kategori'];
    $kapak = $_POST['kapak']; 
    $aciklama = $_POST['aciklama'];
    
    $book_id = $_POST['book_id_hidden']; 

    if (!empty($book_id)) {
        $sql = "UPDATE books SET baslik=?, yazar=?, yil=?, sayfa_sayisi=?, aciklama=?, kategori=?, kapak=? WHERE id=?";
        $stmt = $vt->prepare($sql);
        $stmt->execute([$baslik, $yazar, $yil, $sayfa_sayisi, $aciklama, $kategori, $kapak, $book_id]);
        $msg = "kitap_guncellendi";
    } else {
        $sql = "INSERT INTO books (baslik, yazar, yil, sayfa_sayisi, aciklama, kategori, kapak) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $vt->prepare($sql);
        $stmt->execute([$baslik, $yazar, $yil, $sayfa_sayisi, $aciklama, $kategori, $kapak]);
        $msg = "kitap_eklendi";
    }
    
    header("Location: admin.php?msg=" . $msg);
    exit;
}


$books = $vt->query("SELECT * FROM books ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$users = $vt->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);


$quotes = $vt->query("
    SELECT quotes.*, books.baslik as book_name, users.username as user_name 
    FROM quotes 
    LEFT JOIN books ON quotes.book_id = books.id 
    LEFT JOIN users ON quotes.user_id = users.id 
    ORDER BY quotes.id DESC
")->fetchAll(PDO::FETCH_ASSOC);


$comments = $vt->query("
    SELECT book_comments.*, books.baslik as book_name, users.username as user_name 
    FROM book_comments 
    LEFT JOIN books ON book_comments.book_id = books.id 
    LEFT JOIN users ON book_comments.user_id = users.id 
    ORDER BY book_comments.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Durağı - Yönetici</title>
    <script src="../js/admin.js"></script>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
     <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
    <link rel="stylesheet" type="text/css" href="../css/admin.css">
</head>
<body style="background: #f4f7f6;">

<div class="banner">
    <img src="../resimler/banner.png" style="width:100%;">
</div>

<center><nav class="navbar navbar-expand-lg navbar-light bg-white shadow">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="../resimler/logo.png" alt="Logo" style="height:40px;"></a>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 w-100 justify-content-around">
            <li class="nav-item web_name">
                <a class="nav-link" href="index.php" style="color:pink !important;">Kitap Durağı</a>
            </li>
    </ul>
    </div>
</div>
</nav></center>

<div class="container mb-5 mt-4">
    
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="background-color: #d1e7dd; color: #0f5132;">
            <?php 
                if($_GET['msg'] == 'kitap_guncellendi') echo "✅ Kitap bilgileri başarıyla güncellendi!";
                elseif($_GET['msg'] == 'kitap_eklendi') echo "✅ Yeni kitap başarıyla eklendi!";
                elseif($_GET['msg'] == 'yorum_silindi') echo "✅ Yorum başarıyla silindi!";
                else echo "✅ İşlem başarıyla gerçekleşti!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="admin-card p-4 bg-white shadow rounded">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="admin-title">🛠️ Yönetici Kontrol Merkezi</h2>
            <?php if($editMode): ?>
                <a href="admin.php" class="btn btn-secondary btn-sm">Düzenlemeyi İptal Et / Yeni Ekle</a>
            <?php endif; ?>
        </div>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" id="books-tab" data-bs-toggle="tab" data-bs-target="#books">📚 Kitap İşlemleri</button></li>
            <li class="nav-item"><button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users">👥 Üyeler</button></li>
            <li class="nav-item"><button class="nav-link" id="quotes-tab" data-bs-toggle="tab" data-bs-target="#quotes">💬 Alıntılar</button></li>
            <li class="nav-item"><button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments">📝 Yorumlar</button></li>
        </ul>

        <div class="tab-content">
            
            <div class="tab-pane fade show active" id="books">
                
                <div class="card bg-light border-0 mb-5 p-3" style="border-radius: 15px;">
                    <h5 class="mb-3" style="color:<?= $editMode ? '#ffc107' : '#d63384' ?>;">
                        <?= $editMode ? '✏️ Kitap Bilgilerini Düzenle' : '✨ Yeni Kitap Ekle' ?>
                    </h5>
                    
                    <form method="POST" class="row g-3" action="admin.php">
                        <input type="hidden" name="book_id_hidden" value="<?= $editMode ? $editData['id'] : '' ?>">

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Kitap Başlığı</label>
                            <input type="text" name="baslik" class="form-control rounded-pill" 
                                   value="<?= $editMode ? htmlspecialchars($editData['baslik']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Yazar</label>
                            <input type="text" name="yazar" class="form-control rounded-pill" 
                                   value="<?= $editMode ? htmlspecialchars($editData['yazar']) : '' ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Basım Yılı</label>
                            <input type="number" name="yil" class="form-control rounded-pill" placeholder="Örn: 2023"
                                   value="<?= $editMode ? htmlspecialchars($editData['yil']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Kategori</label>
                            <input type="text" name="kategori" class="form-control rounded-pill" placeholder="Örn: Roman"
                                   value="<?= $editMode ? htmlspecialchars($editData['kategori']) : '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Sayfa Sayısı</label>
                            <input type="number" name="sayfa_sayisi" class="form-control rounded-pill"
                                   value="<?= $editMode ? htmlspecialchars($editData['sayfa_sayisi']) : '' ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label small text-muted">Kapak Resim Dosyası (örn: 1.png)</label>
                            <input type="text" name="kapak" class="form-control rounded-pill"
                                   value="<?= $editMode ? htmlspecialchars($editData['kapak']) : '' ?>">
                        </div>

                        <div class="col-12">
                            <textarea name="aciklama" class="form-control" placeholder="Kitap Açıklaması..." rows="3" style="border-radius: 15px;"><?= $editMode ? htmlspecialchars($editData['aciklama']) : '' ?></textarea>
                        </div>
                        
                        <div class="col-12 text-end">
                            <?php if($editMode): ?>
                                <button type="submit" name="save_book" class="btn btn-warning text-white">Değişiklikleri Güncelle</button>
                            <?php else: ?>
                                <button type="submit" name="save_book" class="btn btn-pembe">Kitabı Kaydet</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>ID</th><th>Kapak</th><th>Başlık</th><th>Yazar</th><th>Yıl</th><th>Kategori</th><th>İşlemler</th></tr></thead>
                        <tbody>
                            <?php foreach($books as $book): ?>
                            <tr class="<?= ($editMode && $book['id'] == $id) ? 'table-warning' : '' ?>">
                                <td><?= $book['id'] ?></td>
                                <td>
                                    <img src="../resimler/kitaplar/<?= !empty($book['kapak']) ? $book['kapak'] : 'default.png' ?>" width="40" style="border-radius: 5px;">
                                </td>
                                <td class="fw-bold text-secondary"><?= htmlspecialchars($book['baslik']) ?></td>
                                <td><?= htmlspecialchars($book['yazar']) ?></td>
                                <td><?= htmlspecialchars($book['yil']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($book['kategori']) ?></span></td>
                                <td>
                                    <a href="admin.php?edit_book=<?= $book['id'] ?>#books" class="btn btn-sm btn-outline-warning rounded-pill">Düzenle</a>
                                    
                                    <a href="?delete_book=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Silmek istiyor musun?')">Sil</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="users">
                <table class="table table-hover">
                    <thead><tr><th>ID</th><th>Kullanıcı Adı</th><th>Email</th><th>Durum</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?= ($user['id'] == 1) ? '<span class="badge bg-danger rounded-pill">Yönetici</span>' : '<span class="badge bg-secondary rounded-pill">Üye</span>' ?>
                            </td>
                            <td>
                                <?php if($user['id'] != 1): ?>
                                    <a href="?delete_user=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Sil?')">Sil</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="quotes">
                <table class="table table-bordered border-light">
                    <thead><tr><th>Kullanıcı</th><th>Kitap</th><th>Alıntı</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach($quotes as $quote): ?>
                        <tr>
                            <td><?= htmlspecialchars($quote['user_name'] ?? 'Bilinmeyen') ?></td>
                            <td><?= htmlspecialchars($quote['book_name'] ?? '-') ?></td>
                            <td><small class="text-muted">"<?= mb_substr(htmlspecialchars($quote['content'] ?? ''), 0, 100) ?>..."</small></td>
                            <td>
                                <a href="?delete_quote=<?= $quote['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Sil?')">Kaldır</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="comments">
                <table class="table table-bordered border-light">
                    <thead><tr><th>Kullanıcı</th><th>Kitap</th><th>Yorum</th><th>Tarih</th><th>İşlem</th></tr></thead>
                    <tbody>
                        <?php foreach($comments as $comment): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($comment['user_name'] ?? 'Bilinmeyen') ?></td>
                            <td><?= htmlspecialchars($comment['book_name'] ?? '-') ?></td>
                            <td style="max-width: 400px;">
                                <small class="text-muted"><?= nl2br(htmlspecialchars($comment['content'] ?? '')) ?></small>
                            </td>
                            <td>
                                <small class="text-secondary"><?= isset($comment['created_at']) ? date("d.m.Y", strtotime($comment['created_at'])) : '-' ?></small>
                            </td>
                            <td>
                                <a href="?delete_comment=<?= $comment['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Bu yorumu silmek istediğine emin misin?')">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php if($editMode): ?>

<?php endif; ?>

</body>
</html>
