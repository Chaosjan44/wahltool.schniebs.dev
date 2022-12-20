<?php
require_once("php/functions.php");
setlocale (LC_ALL, 'de_DE.UTF-8', 'de_DE@euro', 'de_DE', 'de', 'ge', 'de_DE.ISO_8859-1', 'German_Germany');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wahltool der Kolpingjugend DVRS">
    <meta name="author" content="Developed by Jan">
    <link rel="stylesheet" href="/npm/bootstrap/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="/npm/bootstrap/bootstrap.min.js"></script>
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <script defer data-domain="wahltool.schniebs.dev" src="https://plausible.schniebs.dev/js/script.js"></script>
    <link rel="stylesheet" href="/css/styles.css">          <!-- Link Stylesheet -->
    <link rel="stylesheet" href="/css/dark.css" disabled>   <!-- Link Dark Stylesheet and disable it -->
    <link rel="stylesheet" href="/css/light.css" disabled>  <!-- Link Light Stylesheet and disable it -->
    <script src="/js/custom.js"></script>
    <link rel="icon" type="image/png" href="/favicon<?php if (check_style() == "dark") print("_dark"); ?>.png" sizes="1024x1024" />
    <title>Wahlen</title>
</head>

<div class="modal fade" id="cookieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content cbg">
            <div class="modal-header cbg">
                <h4 class="modal-title ctext fw-bold" id="cookieModalLabel">Mhhh Lecker &#x1F36A;!</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ctext cbg fw-normal">
                <div class="px-2">
                    <p>Wir nutzen Cookies auf unserer Webseite.<br>
                    Alle Cookies welche auf dieser Webseite verwendet werden sind für die Funktion der Webseite nötig. <br>
                    Die Cookies werden nicht ausgewertet.
                    </p>
                </div>
            </div>
            <div class="modal-footer ctext cbg fw-bold">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" onclick='setCookie("acceptCookies", "false", 365)'>Ablehnen</button>
                <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick='setCookie("acceptCookies", "true", 365)'>Akzeptieren</button>
            </div>
        </div>
    </div>
</div>

<header class="header sticky-top">
    <nav class="navbar navbar-expand-lg cbg ctext">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <img src="/media/kj-logo_light.svg" class="navbar-icon_light" alt="Navbar Logo">
                <img src="/media/kj-logo_dark.svg" class="navbar-icon_dark" alt="Navbar Logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse cbg" tabindex="-1" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item text-size-x-large">
                        <a class="nav-link clink" href="/impressum.php">Impressum</a>
                    </li>
                    <li class="nav-item text-size-x-large">
                        <a class="nav-link clink" aria-current="page" href="/disclaimer.php">Disclaimer</a>
                    </li>
                    <li class="nav-item text-size-x-large">
                        <a class="nav-link clink clink" href="/datenschutz.php">Datenschutz</a>
                    </li>
                    <li class="nav-item text-size-x-large">
                        <a class="nav-link clink clink" href="/internal.php">Intern</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="header-line"></div>
</header>


<body class="body">
    <div class="body<?php if (isMobile()) print('_mobile');?>">


<?php 
if (!check_cookie()):
?>
<script type="text/javascript">
    const myModal = new bootstrap.Modal('#cookieModal');
    const modalToggle = document.getElementById('cookieModal');
    myModal.show(modalToggle);
</script>
<?php endif; ?>