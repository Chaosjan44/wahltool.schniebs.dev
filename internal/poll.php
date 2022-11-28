<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$user = check_user();
if (!isset($user)) {
    print("<script>location.href='/login.php'</script>");
    exit;
}
// Add check if user is allowed to create and edit polls!!!

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'create_poll') {
        $md5string = md5($_POST['group_id'] . $_POST['poll_name'] . time());
        $stmt = $pdo->prepare("INSERT INTO polls SET group_id = ?, poll_unique = ?, poll_name = ?");
        $stmt->bindValue(1, $_POST['group_id']);
        $stmt->bindValue(2, $md5string);
        $stmt->bindValue(3, $_POST['poll_name']);
        $result = $stmt->execute();
        if (!$result) {
            error('Fehler 1 beim erstellen der Wahl', pdo_debugStrParams($stmt));
        }
        $stmt = $pdo->prepare('SELECT * FROM polls where poll_unique = ?');
        $stmt->bindValue(1, $md5string);
        $result = $stmt->execute();
        if (!$result) {
            error('Fehler 2 beim erstellen der Wahl', pdo_debugStrParams($stmt));
        }
        $poll = $stmt->fetch();
        print("<script>location.href='poll.php?edit=" . $poll['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_add_question') {



    } else if ($_POST['action'] == 'poll_delete_question') {
        // delete all options for question
        $stmt = $pdo->prepare('DELETE FROM options WHERE question_id = ?');
        $stmt->bindValue(1, $_POST['question_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #1 beim Löschen der Frage', pdo_debugStrParams($stmt));
        }
        // delete question from poll
        $stmt = $pdo->prepare('DELETE FROM questions WHERE question_id = ?');
        $stmt->bindValue(1, $_POST['question_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #2 beim Löschen der Frage', pdo_debugStrParams($stmt));
        }
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_question_setlive') {
        // set all questions of poll to not current
        $stmt = $pdo->prepare("UPDATE questions SET current = 0 WHERE poll_id = ?");
        $stmt->bindValue(1, $_POST['poll_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        // set selected question of poll to current
        $stmt = $pdo->prepare("UPDATE questions SET current = 1 WHERE question_id = ?");
        $stmt->bindValue(1, $_POST['question_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_question_setnotlive') {
        // set selected question of poll to current
        $stmt = $pdo->prepare("UPDATE questions SET current = 0 WHERE question_id = ?");
        $stmt->bindValue(1, $_POST['question_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_question_save_option') { 
        $stmt = $pdo->prepare("UPDATE options SET option_name = ? WHERE option_id = ?");
        $stmt->bindValue(1, $_POST['option_name']);
        $stmt->bindValue(2, $_POST['option_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_question_add_option') { 

    } else if ($_POST['action'] == 'poll_question_delete_option') { 
        // delete option
        $stmt = $pdo->prepare('DELETE FROM options WHERE option_id = ?');
        $stmt->bindValue(1, $_POST['option_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #1 beim löschen der Option', pdo_debugStrParams($stmt));
        }
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;


    } else if ($_POST['action'] == 'poll_question_edit_option') { 
        // select * from option
        $stmt = $pdo->prepare('SELECT * FROM options where option_id  = ?');
        $stmt->bindValue(1, $_POST['option_id']);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            error('Datenbank Fehler #1 beim editieren der Option', pdo_debugStrParams($stmt));
        }
        $option = $stmt->fetch();
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Wahl erstellen</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-10">
                            <div class="form-floating">
                                <input id="inputName" type="text" name="option_name" placeholder="Name der Option" value="<?=$option['option_name']?>" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Name der Option</label>
                            </div>
                        </div>
                        <div class="col-2">
                            <input type="number" value="<?=$option['option_id']?>" name="option_id" style="display: none;" required>
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_question_save_option" class="btn btn-success">Option speichern</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");
    }




} if (isset($_GET["create"])) {
    $stmt = $pdo->prepare('SELECT * FROM users_groups, groups where users_groups.group_id = groups.group_id AND users_groups.group_id = ? AND users_groups.user_id = ?');
    $stmt->bindValue(1, $user["sel_group_id"]);
    $stmt->bindValue(2, $user["user_id"]);
    $result = $stmt->execute();
    if (!$result) {
        error('Du bist in keiner Gruppe, wende dich bitte an die Administrierende Person', pdo_debugStrParams($stmt));
    }
    $group = $stmt->fetch();
    require("templates/header.php");
    ?>
    <div class="container-xxl py-3">
        <div class="row">
            <h1 class="display-4 text-center mb-3 text-kolping-orange">Wahl erstellen</h1>
        </div>
        <div class="">
            <div class="col mb-3">
                <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                    <div class="col-10">
                        <div class="form-floating">
                            <input id="inputName" type="text" name="poll_name" placeholder="Name der Wahl" class="form-control border-0 ps-4 text-dark fw-bold" required>
                            <label for="inputName" class="text-dark fw-bold">Name der Wahl</label>
                        </div>
                    </div>
                    <div class="col-2">
                        <input type="number" value="<?=$group['group_id']?>" name="group_id" style="display: none;" required>
                        <button type="submit" name="action" value="create_poll" class="btn btn-success">Wahl erstellen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php
    include_once("templates/footer.php");
} if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare('SELECT * FROM polls where poll_id  = ? and group_id = ?');
    $stmt->bindValue(1, $_GET["edit"], PDO::PARAM_INT);
    $stmt->bindValue(2, $user["sel_group_id"]);
    $stmt->execute();
    if ($stmt->rowCount() != 1) {
        error_log($stmt->rowCount());
        header("location: group.php");
        exit;
    }
    $poll = $stmt->fetch();
    $error_msg = "";
    $stmt = $pdo->prepare('SELECT * FROM questions where poll_id  = ?');
    $stmt->bindValue(1, $_GET["edit"], PDO::PARAM_INT);
    $result = $stmt->execute();
    if (!$result) {
        $error_msg = '<h3 class="display-6 text-center text-danger">Keine Fragen vorhanden</h3>';
    }
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    require("templates/header.php");
    ?>
        <div class="container-xxl py-3">
            <div class="row align-items-center">
                <div class="col-8">
                    <h1 class="display-4 text-start text-kolping-orange"><?=$poll["poll_name"]?></h1>
                </div>
                <div class="col-4">
                    <form action="poll.php" method="POST" class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                        <button class="btn btn-kolping" type="submit" name="action" value="poll_add_question">Frage Hinzufügen</button>
                        <button class="btn btn-danger" type="button" onclick="window.location.href = 'group.php';">Abbrechen</button>
                    </form>
                </div>
                
            </div>
            <div class="col">
                <?=$error_msg?>
                <?php foreach ($questions as $question):
                    $error_msg2 = "";
                    $stmt = $pdo->prepare('SELECT * FROM options where question_id  = ?');
                    $stmt->bindValue(1, $question["question_id"], PDO::PARAM_INT);
                    $stmt->execute();
                    if ($stmt->rowCount() < 1) {
                        $error_msg2 = '<h3 class="display-6 text-center text-danger">Keine Optionen vorhanden</h3>';
                    } else {
                        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                ?>
                    <div class="card cbg2 my-3 p-1">
                        <div class="card-body row">
                            <div class="col-8">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <?php if ($question['current'] == 1) 
                                        print('<button class="btn btn-success">Aktiv</button>'); 
                                        else print('<button class="btn btn-secondary" disabled>Inaktiv</button>')?>
                                    <h3 class="card-title text-start mb-0"><?=$question['question']?></h3>
                                </div>
                            </div>
                            <div class="col-4">
                                <form action="poll.php" method="POST" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                    <input type="number" value="<?=$question['question_id']?>" name="question_id" style="display: none;" required>
                                    <button class="btn btn-success" type="submit" name="action" value="poll_question_setlive">Aktiv setzten</button>
                                    <button class="btn btn-secondary" type="submit" name="action" value="poll_question_setnotlive">Inaktiv setzten</button>
                                </form>
                            </div>
                            <p class="card-text">
                                <?=$error_msg2?>
                                <?php if ($error_msg2 == ""): ?>
                                <table class="table align-middle table-borderless table-hover">
                                    <thead>
                                        <tr>
                                            <div class="cbg ctext rounded">
                                                <th scope="col" class="border-0 text-center">
                                                    <div class="p-2 px-3 ctext">Option</div>
                                                </th>
                                                <th scope="col" class="border-0 text-center">
                                                    <div class="p-2 px-3 ctext">Stimmen</div>
                                                </th>
                                                <th scope="col" class="border-0" style="width: 15%"></th>
                                            </div>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($options as $option): ?>
                                            <!-- editor for option -->
                                            
                                            <tr>
                                                <td class="border-0 text-center">
                                                    <div><?=$option['option_name']?></div>
                                                </td>
                                                <td class="border-0 text-center">
                                                    <div><?=$option['votes']?></div>
                                                </td>
                                                <td class="border-0 actions text-center">
                                                    <form action="poll.php" method="post" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                        <div class="">
                                                            <input type="number" value="<?=$option['option_id']?>" name="option_id" style="display: none;" required>
                                                            <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                            <button type="submit" name="action" value="poll_question_edit_option" class="btn btn-kolping">Editieren</button>
                                                        </div>
                                                        <div class="">
                                                            <input type="number" value="<?=$option['option_id']?>" name="option_id" style="display: none;" required>
                                                            <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas<?=$option['option_id']?>" aria-controls="offcanvas<?=$option['option_id']?>">Löschen</button>
                                                            <div class="offcanvas offcanvas-end cbg" data-bs-scroll="true" tabindex="-1" id="offcanvas<?=$option['option_id']?>" aria-labelledby="offcanvas<?=$option['option_id']?>Label">
                                                                <div class="offcanvas-header">
                                                                    <h2 class="offcanvas-title ctext" id="offcanvas<?=$option['option_id']?>Label">Wirklich Löschen?</h2>
                                                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                                                </div>
                                                                <div class="offcanvas-body">
                                                                    <span class="pb-3">Bist du dir sicher das du diese Wahl löschen möchtest?<br></span>
                                                                    <input type="number" value="<?=$option['option_id']?>" name="option_id" style="display: none;" required>
                                                                    <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                                    <button class="btn btn-success mx-2" type="submit" name="action" value="poll_question_delete_option">Ja</button>
                                                                    <button class="btn btn-danger mx-2" type="button" data-bs-dismiss="offcanvas" aria-label="Close">Nein</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php endif;?>
                            </p>
                            <form action="poll.php" method="POST" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button class="btn btn-kolping" type="submit" name="action" value="poll_question_add_option">Option Hinzufügen</button>
                                <div class="">
                                    <input type="number" value="<?=$question['question_id']?>" name="question_id" style="display: none;" required>
                                    <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas<?=$question['question_id']?>" aria-controls="offcanvas<?=$question['question_id']?>">Löschen</button>
                                    <div class="offcanvas offcanvas-end cbg" data-bs-scroll="true" tabindex="-1" id="offcanvas<?=$question['question_id']?>" aria-labelledby="offcanvas<?=$question['question_id']?>Label">
                                        <div class="offcanvas-header">
                                            <h2 class="offcanvas-title ctext" id="offcanvas<?=$question['question_id']?>Label">Wirklich Löschen?</h2>
                                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                        </div>
                                        <div class="offcanvas-body">
                                            <span class="my-3 text-center">Bist du dir sicher das du diese Frage löschen möchtest?</span>
                                            <div class="my-3 d-grid gap-2 d-md-flex justify-content-md-center">
                                                <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                <input type="number" value="<?=$question['question_id']?>" name="question_id" style="display: none;" required>
                                                <button class="btn btn-success" type="submit" name="action" value="poll_delete_question">Ja</button>
                                                <button class="btn btn-danger" type="button" data-bs-dismiss="offcanvas" aria-label="Close">Nein</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    <?php
    require("templates/footer.php");
} else {


}
?>
