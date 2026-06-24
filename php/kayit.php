<?php require_once "connect.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $profil   = intval($_POST['img'] ?? 0);

    if ($username === '' || $email === '' || $password === '' || $profil === 0) {
        header("Location:kayit_ol.php?bos=1");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location:kayit_ol.php?mail=1");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location:kayit_ol.php?pw=1");
        exit();
    }


    $query = $vt->prepare("
        INSERT INTO users (username, email, password, pp, bio)
        VALUES (:username, :email, :password, :pp, :bio)
    ");

    $sqlResult = $query->execute([
        'username' => $username,
        'email'    => $email,
        'password' => $password,  
        'pp'       => $profil,
        'bio'      => $bio
    ]);

    if ($sqlResult) {
        header("Location:giris_kayit.php?kayit=1");
        exit();
    } else {
        header("Location:kayit_ol.php?kayit=0");
        exit();
    }
}
?>
