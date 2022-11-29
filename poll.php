<?php 
require_once("php/functions.php");
if (!isset($_GET["uni"])) {
    header("location: index.php");
    exit;
}
$error_msg = "";
$stmt = $pdo->prepare("SELECT * FROM polls WHERE poll_unique = ?");
$stmt->bindValue(1, $_GET["uni"]);
$result = $stmt->execute();
if (!$result) {
    header("location: index.php");
    exit;
}
$poll = $stmt->fetch();

if (isset($_POST['action'])) {
    error_log("4");
    if ($_POST['action'] == 'login') {
        error_log("4");
        if(isset($_POST['passwort'])) {
            $passwort = $_POST['passwort'];
            $stmt = $pdo->prepare("SELECT * FROM polls_users WHERE poll_id = ? AND `password` = ?");
            $stmt->bindValue(1, $poll['poll_id']);
            $stmt->bindValue(2, $passwort);
            $result = $stmt->execute();
            if (!$result) {
                $error_msg = "<span class='text-danger'>Passwort ungültig!<br><br></span>";
            }
            $poll_user = $stmt->fetch();
            $_SESSION['userid'] = $poll_user['poll_user_id'];
            $identifier = md5(uniqid());
            $securitytoken = md5(uniqid());
            
            $stmt = $pdo->prepare("INSERT INTO poll_securitytokens (poll_user_id, identifier, securitytoken) VALUES (?, ?, ?)");
            $stmt->bindValue(1, $poll_user['poll_user_id'], PDO::PARAM_INT);
            $stmt->bindValue(2, $identifier);
            $stmt->bindValue(3, sha1($securitytoken));
            $result = $stmt->execute();
            if (!$result) {
                error_log("Fehler beim Anmelden");
                exit;
            }
            setcookie("poll_identifier",$identifier,time()+(3600*12)); //Valid for 12 hours
            setcookie("poll_securitytoken",$securitytoken,time()+(3600*12)); //Valid for 12 hours
            echo("<script>location.href='poll.php?uni=" . $_GET["uni"] . "'</script>");
            exit;
        }
    }
}

$poll_user = check_poll_user();
if ($poll_user == false) {
    require_once("templates/header.php"); ?>
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col">
                <div class="card cbg2">
                    <div class="card-body">
                        <h3 class="card-title display-3 text-center mb-4 text-kolping-orange">Anmelden</h3>
                        <div class="card-text">
                            <?=$error_msg?>
                            <form action="poll.php?uni=<?=$_GET["uni"]?>" method="POST">
                                <div class="form-floating mb-3">
                                    <input id="inputPassword" type="password" name="passwort" placeholder="Passwort" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                    <label for="inputPassword" class="text-dark fw-bold">Passwort</label>
                                </div>
                                <div class="<?php if (!isMobile()) {print('row row-cols-2 justify-content-between');} ?>">
                                    <div class="col <?php if (!isMobile()) {print('text-end');} else {print('text-center');} ?>">
                                        <button type="submit" name="action" value="login" class="btn btn-kolping btn-floating">Anmelden</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php require_once("templates/footer.php"); 
} else { require_once("templates/header.php"); ?>
    <div class="container py-3">
        works
    </div>
    <?php require_once("templates/footer.php"); } ?>