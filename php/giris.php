<?php
session_start();
require_once "connect.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $kadi  = $_POST['giris_k_adi'];
    $sifre = $_POST['giris_sifre'];

    $sql = $vt->prepare("SELECT * FROM users WHERE username = :kadi");
    $sql->execute(['kadi' => $kadi]);

    $kullanici = $sql->fetch(PDO::FETCH_ASSOC);

    if ($kullanici && $sifre === $kullanici['password']) {

        $_SESSION['id']   = $kullanici['id'];
        $_SESSION['kadi'] = $kullanici['username'];
        if (isset($_POST['cbremember'])) {
                setcookie("user", $kullanici['username'], time() + 86400); 
                setcookie("password", $kullanici['password'], time() +86400 );
            } 
            else {
                setcookie("user", "", time() - 3600);
                setcookie("password", "", time() - 3600);
            }
        if ($kullanici['id']==1 &&  $kullanici['username']=='admin')
        {
            header("Location: admin.php");
            exit;
        }
        header("Location: index.php");
        exit;
    }
    header("Location: giris_kayit.php?h=1");
    exit;
}
