<?php 
chdir ($_SERVER['DOCUMENT_ROOT']);
// bindet die PHP Funktionen ein
require_once("php/functions.php");

// Löscht den Security Token aus der Datenbank
$stmt = $pdo->prepare("DELETE FROM poll_securitytokens WHERE identifier = ?");
$stmt->bindValue(1, $_COOKIE['poll_identifier']);
$result = $stmt->execute();
// Fehler Seite anzeigen (wenn ein Fehler aufgetreten ist)
if (!$result) {
    error('Datenbank Fehler', pdo_debugStrParams($stmt));
}

// Entfernt Cookies
setcookie("poll_identifier","",time()-(3600*12),"/"); 
setcookie("poll_securitytoken","",time()-(3600*12),"/"); 

header("location: /index.php");
exit();
?>
