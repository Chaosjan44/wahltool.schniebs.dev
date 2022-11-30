<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$user = check_user();
if ($user == false) {
    print("<script>location.href='/login.php'</script>");
}
if(isset($_POST['action'])) {
    // Wenn action "save" ist
    if($_POST['action'] == 'save') {
        if(isset($_POST['vorname']) and isset($_POST['nachname']) and isset($_POST['passwortNeu']) and isset($_POST['passwortNeu2']) and !empty($_POST['vorname']) and !empty($_POST['nachname'])) {
            $stmt = $pdo->prepare("UPDATE users SET vorname = ?, nachname = ? WHERE user_id = ?");
            $stmt->bindValue(1, $_POST['vorname']);
            $stmt->bindValue(2, $_POST['nachname']);
            $stmt->bindValue(3, $user['user_id'], PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            }
            // Überprüfe ob die eingegebenen Passwörter übereinstimmen
            if($_POST['passwortNeu'] == $_POST['passwortNeu2']) {
                // überprüft das die Passwörter nicht leer sind
                if (!empty($_POST['passwortNeu']) and !empty($_POST['passwortNeu2'])) {
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $stmt->bindValue(1, password_hash($_POST['passwortNeu'], PASSWORD_DEFAULT));
                    $stmt->bindValue(2, $user['user_id'], PDO::PARAM_INT);
                    $result = $stmt->execute();
                    if (!$result) {
                        error('Datenbank Fehler!', pdo_debugStrParams($stmt));
                    }                    
                }
            } else {
                error('Passwörter stimmen nicht überein!');
            }
            $stmt = $pdo->prepare('UPDATE users SET sel_group_id = ? WHERE user_id = ?');
            $stmt->bindValue(1, $_POST['group_sel'], PDO::PARAM_INT);
            $stmt->bindValue(2, $user["user_id"]);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            }
            echo("<script>location.href='/internal.php'</script>");
            exit;
        }
    }
    if ($_POST['action'] == 'cancel') {
        echo("<script>location.href='/internal.php'</script>");
        exit;
    }
}
$stmt = $pdo->prepare('SELECT * FROM users_groups, groups where users_groups.group_id = groups.group_id AND users_groups.user_id  = ?');
$stmt->bindValue(1, $user["user_id"]);
$result = $stmt->execute();
if ($stmt->rowCount() < 1) {
    error('Du bist in keiner Gruppe, wende dich bitte an die Administrierende Person', pdo_debugStrParams($stmt));
}
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
require_once("templates/header.php"); 
?>
<div class="px-3 py-3">
    <h1>Einstellungen</h1>
    <div>
        <form action="settings.php" method="post">
            <div class="row d-flex justify-content-between">
                <div class="<?php if (!isMobile()) print("col-6"); else print("col-12");?>">
                    <div class="input-group py-2">
                        <span class="input-group-text" for="inputVorname" style="min-width: 150px;">Vorname</span>
                        <input class="form-control" id="inputVorname" name="vorname" type="text" value="<?=$user['vorname']?>" required>
                    </div>
                    <div class="input-group py-2">
                        <span class="input-group-text" for="inputNachname" style="min-width: 150px;">Nachname</span>
                        <input class="form-control" id="inputNachname" name="nachname" type="text" value="<?=$user['nachname']?>" required>
                    </div>
                    <div class="input-group py-2">
                        <span class="input-group-text" for="selectGroup" style="min-width: 150px;">Gruppe</span>
                        <select class="form-select" aria-label="Default select example" name="group_sel" id="selectGroup">
                            <?php $i = 0; foreach ($groups as $group): ?>
                                <option class="text-dark" <?php if($group['group_id'] == $user['sel_group_id']) print("selected");?> value="<?=$group['group_id']?>"><?=$group['group_name']?></option>
                            <?php $i++; endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="<?php if (!isMobile()) print("col-6"); else print("col-12");?>">
                    <div class="input-group py-2">
                        <input class="form-control" id="inputPasswortNeu" name="passwortNeu" type="password" placeholder="Neues Passwort">
                    </div>
                    <div class="input-group py-2">
                        <input class="form-control" id="inputPasswortNeu2" name="passwortNeu2" type="password" placeholder="Neues Passwort wiederholen">
                    </div>
                    <div class="input-group py-2 justify-content-end">
                        <button type="submit" name="action" value="save" class="btn btn-success">Speichern</button>
                        <button type="submit" name="action" value="cancel" class="btn btn-danger">Abrechen</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<?php 
require_once("templates/footer.php"); 
?>