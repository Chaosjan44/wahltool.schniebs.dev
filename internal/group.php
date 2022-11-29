<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$user = check_user();
if (!isset($user)) {
    print("<script>location.href='/login.php'</script>");
    exit;
}


if(isset($_POST['action'])) {
    if ($_POST['action'] == 'sel_group') {
        $stmt = $pdo->prepare('UPDATE users SET sel_group_id = ? WHERE user_id = ?');
        $stmt->bindValue(1, $_POST['group_sel'], PDO::PARAM_INT);
        $stmt->bindValue(2, $user["user_id"]);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        }
        print("<script>location.href='group.php'</script>");
    } if ($_POST['action'] == 'mod') {
        $pollid = $_POST['poll_id'];
        print("<script>location.href='poll.php?edit=" . $pollid . "'</script>");
        exit;
    } if ($_POST['action'] == 'deleteconfirm') {
        $stmt = $pdo->prepare('SELECT * FROM users_groups, groups where users_groups.group_id = groups.group_id AND users_groups.group_id = ?');
        $stmt->bindValue(1, $user["sel_group_id"]);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #1 beim löschen der Wahl!', pdo_debugStrParams($stmt));
        }
        $group = $stmt->fetch();
        if ($group['perm_group_admin'] != 1) {
            error('Unzureichende Berechtigungen!');
        }
        if(isset($_POST['poll_id']) and !empty($_POST['poll_id']) and isset($_POST['group_id']) and !empty($_POST['group_id'])) {
            // select all questions for poll
            $stmt = $pdo->prepare('SELECT question_id FROM questions where poll_id = ?');
            $stmt->bindValue(1, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler #2 beim löschen der Wahl!', pdo_debugStrParams($stmt));
            }
            $questions = $stmt->fetchAll();

            // delete all options for all questions
            foreach ($questions as $question) {
                $stmt = $pdo->prepare('DELETE FROM options WHERE option_id = ?');
                $stmt->bindValue(1, $question['question_id']);
                $result = $stmt->execute();
                if (!$result) {
                    error('Datenbank Fehler #3 beim löschen der Wahl!', pdo_debugStrParams($stmt));
                }
            }

            // delete all questions from poll
            $stmt = $pdo->prepare('DELETE FROM questions WHERE poll_id = ?');
            $stmt->bindValue(1, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler #4 beim löschen der Wahl!', pdo_debugStrParams($stmt));
            }

            // select all users for poll
            $stmt = $pdo->prepare('SELECT poll_user_id FROM polls_users where poll_id = ?');
            $stmt->bindValue(1, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler #5 beim löschen der Wahl!', pdo_debugStrParams($stmt));
            }

            // delete all securitytokens for poll users
            $poll_users = $stmt->fetchAll();
            foreach ($poll_users as $poll_user) {
                $stmt = $pdo->prepare('DELETE FROM poll_securitytokens WHERE poll_user_id = ?');
                $stmt->bindValue(1, $poll_user['poll_user_id']);
                $result = $stmt->execute();
                if (!$result) {
                    error('Datenbank Fehler #6 beim löschen der Wahl!', pdo_debugStrParams($stmt));
                }
            }

            // delete all users for poll
            $stmt = $pdo->prepare('DELETE FROM polls_users WHERE poll_id = ?');
            $stmt->bindValue(1, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler #7 beim löschen der Wahl!', pdo_debugStrParams($stmt));
            }

            // delete poll
            $stmt = $pdo->prepare('DELETE FROM polls WHERE poll_id = ?');
            $stmt->bindValue(1, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler #8 beim löschen der Wahl!', pdo_debugStrParams($stmt));
            }

            echo("<script>location.href='group.php'</script>");
            exit;
        }
    }
}

if (!isset($user["sel_group_id"])) {
    $stmt = $pdo->prepare('SELECT * FROM users_groups, groups where users_groups.group_id = groups.group_id AND users_groups.user_id  = ?');
    $stmt->bindValue(1, $user["user_id"]);
    $result = $stmt->execute();
    if ($stmt->rowCount() < 1) {
        error('Du bist in keiner Gruppe, wende dich bitte an die Administrierende Person', pdo_debugStrParams($stmt));
    }
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    require("templates/header.php");
    ?>
    <div class="container p-3">
        <h1 class="text-kolping-orange text-center">Wähle deine Gruppe</h1>
        <div class="">
            <form class="d-grid gap-2 col<?php if(!isMobile()) print("-6"); ?> mx-auto" action="group.php" method="post">
                <select class="form-select" aria-label="Default select example" name="group_sel">
                    <?php $i = 0; foreach ($groups as $group): ?>
                        <option <?php ($i = 0 ? "selected" : "")?> value="<?=$group['group_id']?>"><?=$group['group_name']?></option>
                    <?php $i++; endforeach; ?>
                </select>
                <button type="submit" name="action" value="sel_group" class="btn btn-kolping mx-auto">Wählen</button>
            </form>
        </div>
    </div>
    <?php
    require("templates/footer.php");
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users_groups, groups where users_groups.group_id = groups.group_id AND users_groups.group_id = ?');
$stmt->bindValue(1, $user["sel_group_id"]);
$result = $stmt->execute();
if (!$result) {
    error('Du bist in keiner Gruppe, wende dich bitte an die Administrierende Person', pdo_debugStrParams($stmt));
}
$groups = $stmt->fetchAll();
$users;
$i = 0;
foreach ($groups as $group) {
    $stmt = $pdo->prepare('SELECT user_id, login, nachname, vorname FROM users where user_id = ?');
    $stmt->bindValue(1, $group["user_id"]);
    $result = $stmt->execute();
    if (!$result) {
        error('Datenbank Fehler!', pdo_debugStrParams($stmt));
    }
    $users[$i] = $stmt->fetch();
    $i++;
}
$stmt = $pdo->prepare('SELECT * FROM polls where group_id = ?');
$stmt->bindValue(1, $groups[0]["group_id"]);
$result = $stmt->execute();
if (!$result) {
}
$polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
require("templates/header.php");
?>
<div class="container-xxl py-3">
    <div class="row">
        <h1 class="display-4 text-center mb-3 text-kolping-orange"><?=$groups[0]["group_name"]?></h1>
    </div>
    <div class="<?php if (!isMobile()) print("row");?>">
        <div class="col<?php if (!isMobile()) print("-12");?> mb-3">
            <div class="card cbg2 p-2">
                <div class="d-flex justify-content-between">
                    <div class="col-4"></div>
                    <div class="col-4">
                        <h3 class="display-6 text-center text-kolping-orange">Wahlen</h3>
                    </div>
                    <div class="col-4 d-flex justify-content-end">
                        <div>
                            <button class="btn btn-kolping mx-1 mx-auto" type="button" onclick="window.location.href = 'poll.php?create';">Wahl erstellen</button>
                        </div>
                    </div>
                </div>
                
                <?php 
                $stmt = $pdo->prepare('SELECT * FROM polls where group_id = ?');
                $stmt->bindValue(1, $groups[0]["group_id"]);
                $result = $stmt->execute();
                if ($stmt->rowCount() < 1) {
                    ?>
                        <h3 class="display-6 text-center text-danger">Keine Wahlen vorhanden</h3>
                    <?php
                } else {
                    $polls = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    ?>
                    <table class="table align-middle table-borderless table-hover">
                        <thead>
                            <tr>
                                <div class="cbg ctext rounded">
                                    <th scope="col" class="border-0 text-center">
                                        <div class="p-2 px-3 ctext">Name</div>
                                    </th>
                                    <th scope="col" class="border-0 text-center">
                                        <div class="p-2 px-3 ctext">Link</div>
                                    </th>
                                    <th scope="col" class="border-0" style="width: 15%"></th>
                                </div>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($polls as $poll): ?>
                                <tr>
                                    <td class="border-0 text-center">
                                        <div><?=$poll['poll_name']?></div>
                                    </td>
                                    <td class="border-0 text-center">
                                        <div>https://wahltool.schniebs.dev/poll.php?uni=<?=$poll['poll_unique']?></div>
                                    </td>
                                    <td class="border-0 actions text-center">
                                        <?php if ($poll['poll_id'] != 0):?>
                                        <form action="group.php" method="post" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <div class="">
                                                <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                <button type="submit" name="action" value="mod" class="btn btn-kolping">Editieren</button>
                                            </div>
                                            <div class="">
                                                <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas<?=$poll['poll_id']?>" aria-controls="offcanvas<?=$poll['poll_id']?>">Löschen</button>
                                                <div class="offcanvas offcanvas-end cbg" data-bs-scroll="true" tabindex="-1" id="offcanvas<?=$poll['poll_id']?>" aria-labelledby="offcanvas<?=$poll['poll_id']?>Label">
                                                    <div class="offcanvas-header">
                                                        <h2 class="offcanvas-title ctext" id="offcanvas<?=$poll['poll_id']?>Label">Wirklich Löschen?</h2>
                                                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                                    </div>
                                                    <div class="offcanvas-body">
                                                        <span class="pb-3">Bist du dir sicher das du diese Wahl löschen möchtest?<br></span>
                                                        <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                        <input type="number" value="<?=$groups[0]['group_id']?>" name="group_id" style="display: none;" required>
                                                        <button class="btn btn-success mx-2" type="submit" name="action" value="deleteconfirm">Ja</button>
                                                        <button class="btn btn-danger mx-2" type="button" data-bs-dismiss="offcanvas" aria-label="Close">Nein</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>
        <div class="col<?php if (!isMobile()) print("-6");?> mb-3">
            <div class="card cbg2 p-2">
                <h3 class="display-6 text-center text-kolping-orange">Personen in der Gruppe</h3>
                <table class="table align-middle table-borderless table-hover">
                    <thead>
                        <tr>
                            <div class="cbg ctext rounded">
                                <th scope="col" class="border-0 text-center">
                                    <div class="p-2 px-3 ctext">Login</div>
                                </th>
                                <th scope="col" class="border-0 text-center">
                                    <div class="p-2 px-3 ctext">Vorname</div>
                                </th>
                                <th scope="col" class="border-0 text-center">
                                    <div class="p-2 px-3 ctext">Nachname</div>
                                </th>
                            </div>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="border-0 text-center">
                                    <div><?=$user['login']?></div>
                                </td>
                                <td class="border-0 text-center">
                                    <div><?=$user['vorname']?></div>
                                </td>
                                <td class="border-0 text-center">
                                    <div><?=$user['nachname']?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include_once("templates/footer.php")
?>
