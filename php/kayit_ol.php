<?php require_once "connect.php"; ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Kitap Durağı - Kayıt Ol</title>
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
	 <link rel="stylesheet" type="text/css" href="../css/menu_c.css">
	<link rel="stylesheet" type="text/css" href="../css/giris_kayit.css?vt=1">
	<script language="javascript" src="../js/kayit_ol.js"></script>
</head>
<body style="background-color:#FFCBDB ;">

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
<div class="div1" style="background-color:lavender">
	<form method="post" action="kayit.php">
	<div class="row mb-3 align-items-center">
			<div class="col-sm-6 anasayfa" style="align-items: center; padding: 12px 15px;">Kullanıcı Adı:</div>
			<div class="col-sm-6"><input type="text" name="username" class="textbox_css" style="width: 300px;"></div>
	</div>

	<div class="row mb-3 align-items-center">
			<div class="col-sm-6 anasayfa" style="align-items: center; padding: 12px 15px;">E-mail:</div>
			<div class="col-sm-6"><input type="mail" name="email" class="textbox_css" style="width: 300px;"></div>
	</div>
	<?php
		if (isset($_GET['mail']) && $_GET['mail']==1 ) {
			echo "<h6 class='anasayfa' style='color:red;'>Geçerli bir e-mail adresi giriniz.</h6>"; 
	}?>
	<div class="row mb-3 align-items-center">
			<div class="col-sm-6 anasayfa" style="align-items: center; padding: 12px 15px;">Şifre:</div>
			<div class="col-sm-6"><input type="password" name="password" class="textbox_css" style="width: 300px;"></div>
	</div>
	<?php
	if (isset($_GET['pw']) && $_GET['pw']==1 ) {
		echo "<h6 class='anasayfa' style='color:red;'>En az 6 karakterli bir şifre giriniz.</h6>"; 
	}
	?>
	
	<div class="row mb-3 align-items-center">
			<p class="anasayfa">Profil:</p>
	</div>

	<div class="container mt-4">
    <div class="row g-2 justify-content-start">
    	<input type="hidden" name="img" id="img">

        <?php for ($i = 1; $i <= 9; $i++) { ?>
            <div class="col-auto">
                <img src="../pp/<?php echo $i; ?>.png" 
                     class="pp-img" 
                     data-num="<?php echo $i; ?>" 
                     onerror="this.style.display='none';"
                     style="cursor:pointer; width:60px; height:60px;">
            </div>
        <?php } ?>
    </div>
    <div class="row g-2 mt-2 justify-content-start">
        <?php for ($i = 10; $i <= 18; $i++) { ?>
            <div class="col-auto">
                <img src="../pp/<?php echo $i; ?>.png" 
                     class="pp-img" 
                     data-num="<?php echo $i; ?>" 
                     onerror="this.style.display='none';"
                     style="cursor:pointer; width:60px; height:60px;">
            </div>
        <?php } ?>
    </div>
  </div>

  <div class="row mb-3 mt-2 align-items-center">
			<div class="col-sm-6 anasayfa" style="align-items: center; padding: 12px 15px;">Bio:</div>
			<div class="col-sm-6"><textarea name="bio" class="textbox_css" rows="5" cols="30" style="resize:none; width: 300px; padding:20px"></textarea></div>
	</div>
	<?php
		if (isset($_GET['bos']) && $_GET['bos']==1 ) {
			echo "<h6 class='anasayfa' style='color:red;'>Boş alanları lütfen doldurunuz.</h6>"; 
	}
		if (isset($_GET['kayit']) && $_GET['kayit']==0 ) 
		{
			echo "<h6 class='anasayfa' style='color:red;'>Bir hata oluştu. Tekrar deneyin.</h6>"; 

		}
	?>
	<div class="row mb-3 mt-2 align-items-center">
		<button class="btn btn_pembe anasayfa" style="margin-top:2px" onclick="window.location='giris_kayit.php'">Kayıt Ol</button>
	</div></form>
</div>

</center>
</body>
</html>