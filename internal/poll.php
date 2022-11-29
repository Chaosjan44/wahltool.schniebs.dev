<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
// check if user is logged in, if not redirect to login
$user = check_user();
if (!isset($user)) {
    print("<script>location.href='/login.php'</script>");
    exit;
}

// check if user has permission in their selected group to create/edit/delete polls, if not redirect to error with explanation
$stmt = $pdo->prepare('SELECT * FROM users_groups WHERE user_id = ? AND group_id = ?');
$stmt->bindValue(1, $user["user_id"]);
$stmt->bindValue(2, $user["sel_group_id"]);
$result = $stmt->execute();
if (!$result) {
    error('Du bist in keiner Gruppe, wende dich bitte an die Administrierende Person', pdo_debugStrParams($stmt));
}
$user_group = $stmt->fetch();
if ($user_group['perm_poll'] != 1) {
    error('Du hast nicht die nötigen Berechtigungen um dies zu tun');
}

// POST action stuff
if (isset($_POST['action'])) {
    // action to create a poll
    if ($_POST['action'] == 'create_poll') {
        $uni = $_POST['group_id'] . generateRandomString(10);
        $stmt = $pdo->prepare("INSERT INTO polls SET group_id = ?, poll_unique = ?, poll_name = ?");
        $stmt->bindValue(1, $_POST['group_id']);
        $stmt->bindValue(2, $uni);
        $stmt->bindValue(3, $_POST['poll_name']);
        $result = $stmt->execute();
        if (!$result) {
            error('Fehler 1 beim erstellen der Wahl', pdo_debugStrParams($stmt));
        }
        $stmt = $pdo->prepare('SELECT * FROM polls where poll_unique = ?');
        $stmt->bindValue(1, $uni);
        $result = $stmt->execute();
        if (!$result) {
            error('Fehler 2 beim erstellen der Wahl', pdo_debugStrParams($stmt));
        }
        $poll = $stmt->fetch();
        print("<script>location.href='poll.php?edit=" . $poll['poll_id'] . "'</script>");
        exit;

    // action to save poll name
    } else if ($_POST['action'] == 'poll_save') { 
        $stmt = $pdo->prepare("UPDATE polls SET poll_name = ? WHERE poll_id = ?");
        $stmt->bindValue(1, $_POST['poll_name']);
        $stmt->bindValue(2, $_POST['poll_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;
        
    // action to edit poll name
    } else if ($_POST['action'] == 'poll_edit') {
        // select * from question
        $stmt = $pdo->prepare('SELECT * FROM polls where poll_id  = ?');
        $stmt->bindValue(1, $_POST['poll_id']);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            error('Datenbank Fehler #1 beim editieren der Frage', pdo_debugStrParams($stmt));
        }
        $poll = $stmt->fetch();
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Wahl bearbeiten</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating">
                                <input id="inputName" type="text" name="poll_name" placeholder="Wahl Name" value="<?=$poll['poll_name']?>" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Wahl Name</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_save" class="btn btn-success">Wahl speichern</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to save a question from a poll
    } else if ($_POST['action'] == 'poll_save_question') { 
        if (isset($_POST['question_id'])) {
            $stmt = $pdo->prepare("UPDATE questions SET question = ?, options_amount = ? WHERE question_id = ?");
            $stmt->bindValue(1, $_POST['question']);
            $stmt->bindValue(2, $_POST['options_amount']);
            $stmt->bindValue(3, $_POST['question_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        } else {
            $stmt = $pdo->prepare("INSERT INTO questions SET question = ?, poll_id = ?, options_amount = ?");
            $stmt->bindValue(1, $_POST['question']);
            $stmt->bindValue(2, $_POST['poll_id']);
            $stmt->bindValue(3, $_POST['options_amount']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        }
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;

    // action to add a question to a poll
    } else if ($_POST['action'] == 'poll_add_question') {
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Frage hinzufügen</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating my-2">
                                <input id="inputName" type="text" name="question" placeholder="Frage" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Frage</label>
                            </div>
                            <div class="form-floating my-2">
                                <input id="inputValue" type="number" name="options_amount" placeholder="Anzahl an Stimmen" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputValue" class="text-dark fw-bold">Anzahl an Stimmen</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_save_question" class="btn btn-success">Frage hinzufügen</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to delete a question from a poll
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

    // action to edit a question from a poll
    } else if ($_POST['action'] == 'poll_edit_question') { 
        // select * from question
        $stmt = $pdo->prepare('SELECT * FROM questions where question_id  = ?');
        $stmt->bindValue(1, $_POST['question_id']);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            error('Datenbank Fehler #1 beim editieren der Frage', pdo_debugStrParams($stmt));
        }
        $question = $stmt->fetch();
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Frage bearbeiten</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating my-2">
                                <input id="inputName" type="text" name="question" placeholder="Frage" value="<?=$question['question']?>" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Frage</label>
                            </div>
                            <div class="form-floating my-2">
                                <input id="inputValue" type="number" name="options_amount" placeholder="Anzahl an Stimmen" value="<?=$question['options_amount']?>" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputValue" class="text-dark fw-bold">Anzahl an Stimmen</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$question['question_id']?>" name="question_id" style="display: none;" required>
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_save_question" class="btn btn-success">Frage speichern</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to set a question live
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

    // action to set a question not live
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

    // action to save an option of a question
    } else if ($_POST['action'] == 'poll_question_save_option') { 
        if (isset($_POST['option_id'])) {
            $stmt = $pdo->prepare("UPDATE options SET option_name = ? WHERE option_id = ?");
            $stmt->bindValue(1, $_POST['option_name']);
            $stmt->bindValue(2, $_POST['option_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        } else {
            $stmt = $pdo->prepare("INSERT INTO options SET option_name = ?, question_id = ?, votes = 0");
            $stmt->bindValue(1, $_POST['option_name']);
            $stmt->bindValue(2, $_POST['question_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        }
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;

    // action to add an option to a question
    } else if ($_POST['action'] == 'poll_question_add_option') { 
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Option erstellen</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating">
                                <input id="inputName" type="text" name="option_name" placeholder="Name der Option" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Name der Option</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$_POST['question_id']?>" name="question_id" style="display: none;" required>
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_question_save_option" class="btn btn-success">Option hinzufügen</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to delete an option of a question
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

    // action to edit an option of a question
    } else if ($_POST['action'] == 'poll_question_edit_option') { 
        // select * from option
        $stmt = $pdo->prepare('SELECT * FROM options where option_id  = ?');
        $stmt->bindValue(1, $_POST['option_id']);
        $stmt->execute();
        if ($stmt->rowCount() != 1) {
            error('Datenbank Fehler #1 beim editieren der Option', pdo_debugStrParams($stmt));
        }
        $option = $stmt->fetch();
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Option Bearbeiten</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating">
                                <input id="inputName" type="text" name="option_name" placeholder="Name der Option" value="<?=$option['option_name']?>" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Name der Option</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$option['option_id']?>" name="option_id" style="display: none;" required>
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_question_save_option" class="btn btn-success">Option speichern</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to save poll users
    } else if ($_POST['action'] == 'poll_users_save') { 
        for ($i = 0; $i < $_POST['poll_user_amount']; $i++) {
            $stmt = $pdo->prepare("INSERT INTO polls_users SET password = ?, poll_id = ?");
            $stmt->bindValue(1, generateRandomString(6));
            $stmt->bindValue(2, $_POST['poll_id']);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        }      
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;

    // action to create poll users
    } else if ($_POST['action'] == 'poll_users_create') { 
        $pollid = $_POST["poll_id"];
        require("templates/header.php");
        ?>
        <div class="container-xxl py-3">
            <div class="row">
                <h1 class="display-4 text-center mb-3 text-kolping-orange">Nutzer*innen erstellen</h1>
            </div>
            <div class="">
                <div class="col mb-3">
                    <form action="poll.php" method="post" class="<?php if (!isMobile()) print('row');?> align-items-center">
                        <div class="col-8">
                            <div class="form-floating">
                                <input id="inputName" type="number" name="poll_user_amount" placeholder="Anzahl an Nutzer*innen" class="form-control border-0 ps-4 text-dark fw-bold" required>
                                <label for="inputName" class="text-dark fw-bold">Anzahl an Nutzer*innen</label>
                            </div>
                        </div>
                        <div class="col-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <input type="number" value="<?=$_POST['poll_id']?>" name="poll_id" style="display: none;" required>
                            <button type="submit" name="action" value="poll_users_save" class="btn btn-success">Nutzer*innen erstellen</button>
                            <button class="btn btn-danger" type="button" onclick='window.location.href = "poll.php?edit=<?=$pollid?>";'>Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once("templates/footer.php");

    // action to delete all poll users of poll
    } else if ($_POST['action'] == 'poll_users_deleteall') { 
        $stmt = $pdo->prepare('SELECT poll_user_id FROM polls_users where poll_id = ?');
        $stmt->bindValue(1, $_POST['poll_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #5 beim löschen der Wahl!', pdo_debugStrParams($stmt));
        }
        $poll_users = $stmt->fetchAll();

        // delete all securitytokens for poll users
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
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;

    // action to delete poll user
    } else if ($_POST['action'] == 'poll_users_delete') { 
        $stmt = $pdo->prepare('DELETE FROM poll_securitytokens WHERE poll_user_id = ?');
        $stmt->bindValue(1, $_POST['poll_user_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #6 beim löschen der Wahl!', pdo_debugStrParams($stmt));
        }
        $stmt = $pdo->prepare('DELETE FROM polls_users WHERE poll_user_id = ?');
        $stmt->bindValue(1, $_POST['poll_user_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler #7 beim löschen der Wahl!', pdo_debugStrParams($stmt));
        }
        print("<script>location.href='poll.php?edit=" . $_POST['poll_id'] . "'</script>");
        exit;
    } 



// this comes up if user is directed to url https://<domain>/internal/poll?create
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
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        $error_msg = '<h3 class="display-6 text-center text-danger">Keine Fragen vorhanden</h3>';
    }
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $error_msg3 = "";
    $stmt = $pdo->prepare('SELECT * FROM polls_users where poll_id  = ?');
    $stmt->bindValue(1, $_GET["edit"], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        $error_msg3 = '<h3 class="display-6 text-center text-danger">Keine Nutzer*innen vorhanden</h3>';
    }
    $polls_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                        <button class="btn btn-kolping" type="submit" name="action" value="poll_edit">Wahl anpassen</button>
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
                                    <h3 class="card-title text-start mb-0 ms-1"><?=$question['question']?></h3>
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
                            <h3 class="card-title text-start mb-0 mt-1">Optionen zur Auswahl: <?=$question['options_amount']?></h3>
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
                                <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                <input type="number" value="<?=$question['question_id']?>" name="question_id" style="display: none;" required>
                                <button class="btn btn-kolping" type="submit" name="action" value="poll_question_add_option">Option Hinzufügen</button>
                                <button class="btn btn-kolping" type="submit" name="action" value="poll_edit_question">Editieren</button>
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
            <!-- Users for poll -->
            <div>
                <div class="card cbg2 my-3 p-1">
                    <div class="card-body row">
                        <div class="col-6">
                            <h3 class="text-start mb-0 ms-1">Nutzer*innen für die Wahl</h3>
                        </div>
                        <div class="col-6">
                            <form action="poll.php" method="POST" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <div>
                                    <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                    <button class="btn btn-success" type="submit" name="action" value="poll_users_create">Nutzer*innen erstellen</button>
                                </div>
                                <?php if ($error_msg3 == ""): ?>
                                <div class="">
                                    <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                    <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas<?=$poll['poll_id']?>1" aria-controls="offcanvas<?=$poll['poll_id']?>1">Alle Nutzer*innen löschen</button>
                                    <div class="offcanvas offcanvas-end cbg" data-bs-scroll="true" tabindex="-1" id="offcanvas<?=$poll['poll_id']?>1" aria-labelledby="offcanvas<?=$poll['poll_id']?>1Label">
                                        <div class="offcanvas-header">
                                            <h2 class="offcanvas-title ctext" id="offcanvas<?=$poll['poll_id']?>1Label">Wirklich Löschen?</h2>
                                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                        </div>
                                        <div class="offcanvas-body">
                                            <span class="pb-3">Bist du dir sicher das du diese Wahl löschen möchtest?<br></span>
                                            <div class="my-3 d-grid gap-2 d-md-flex justify-content-md-center">
                                                <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                <button class="btn btn-success" type="submit" name="action" value="poll_users_deleteall">Ja</button>
                                                <button class="btn btn-danger" type="button" data-bs-dismiss="offcanvas" aria-label="Close">Nein</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                        <?=$error_msg3?>
                        <?php if ($error_msg3 == ""): ?>
                        <div class="card-text">
                            <table class="table align-middle table-borderless table-hover">
                                <thead>
                                    <tr>
                                        <div class="cbg ctext rounded">
                                            <th scope="col" class="border-0 text-center">
                                                <div class="p-2 px-3 ctext">Passwort</div>
                                            </th>
                                            <th scope="col" class="border-0 text-center">
                                                <div class="p-2 px-3 ctext">Angemeldet</div>
                                            </th>
                                            <th scope="col" class="border-0 text-center">
                                                <div class="p-2 px-3 ctext">Aktuelle Frage abgestimmt</div>
                                            </th>
                                            <th scope="col" class="border-0" style="width: 15%"></th>
                                        </div>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($polls_users as $polls_user): 
                                        $stmt = $pdo->prepare('SELECT poll_securitytoken_id FROM poll_securitytokens where poll_user_id = ?');
                                        $stmt->bindValue(1, $polls_user["poll_user_id"]);
                                        $stmt->execute();
                                        if ($stmt->rowCount() < 1) {
                                            $polls_user_logged = false;
                                        } else {
                                            $polls_user_logged = true;
                                        }
                                        ?>
                                        <!-- editor for option -->
                                        
                                        <tr>
                                            <td class="border-0 text-center">
                                                <div><?=$polls_user['password']?></div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <div>
                                                    <?php 
                                                        if ($polls_user_logged == true) print("Ja");
                                                        else print("Nein");
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="border-0 text-center">
                                                <div><?=$polls_user['answered_current']?></div>
                                            </td>
                                            <td class="border-0 actions text-center">
                                                <form action="poll.php" method="post" class="d-grid gap-2 d-md-flex justify-content-md-end">
                                                    <div class="">
                                                        <input type="number" value="<?=$polls_user['poll_user_id']?>" name="poll_user_id" style="display: none;" required>
                                                        <button class="btn btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas<?=$polls_user['poll_user_id']?>" aria-controls="offcanvas<?=$polls_user['poll_user_id']?>">Löschen</button>
                                                        <div class="offcanvas offcanvas-end cbg" data-bs-scroll="true" tabindex="-1" id="offcanvas<?=$polls_user['poll_user_id']?>" aria-labelledby="offcanvas<?=$polls_user['poll_user_id']?>Label">
                                                            <div class="offcanvas-header">
                                                                <h2 class="offcanvas-title ctext" id="offcanvas<?=$polls_user['poll_user_id']?>Label">Wirklich Löschen?</h2>
                                                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                                            </div>
                                                            <div class="offcanvas-body">
                                                                <span class="pb-3">Bist du dir sicher das du diese Wahl löschen möchtest?<br></span>
                                                                <div class="my-3 d-grid gap-2 d-md-flex justify-content-md-center">
                                                                    <input type="number" value="<?=$poll['poll_id']?>" name="poll_id" style="display: none;" required>
                                                                    <input type="number" value="<?=$polls_user['poll_user_id']?>" name="poll_user_id" style="display: none;" required>
                                                                    <button class="btn btn-success" type="submit" name="action" value="poll_users_delete">Ja</button>
                                                                    <button class="btn btn-danger" type="button" data-bs-dismiss="offcanvas" aria-label="Close">Nein</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php
    require("templates/footer.php");
} else {
    header("location: group.php");
    exit;
}
?>
