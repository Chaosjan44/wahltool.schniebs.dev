<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$poll_user = check_poll_user();

// if (isset($_GET['stop'])) {
//     print('<div style="display: none">stop</div>');
// }

$errormsg = "";
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'submit') {
        $stmt = $pdo->prepare('SELECT * FROM polls where poll_id  = ?');
        $stmt->bindValue(1, $poll_user["poll_id"], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            error('Fehler beim Abgeben der Ergebnisse');
        }
        $poll = $stmt->fetch();
        $stmt = $pdo->prepare('SELECT * FROM questions where poll_id  = ? and current = 1');
        $stmt->bindValue(1, $poll_user["poll_id"], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            error('Fehler beim Abgeben der Ergebnisse');
        }
        $question = $stmt->fetch();

        $stmt = $pdo->prepare('SELECT * FROM options where question_id  = ?');
        $stmt->bindValue(1, $question["question_id"], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            error('Fehler!');
        } 
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counter = 0;
        foreach  ($options as $option) {
            if (isset($_POST['vote_'.$option["option_id"]])) {
                $counter++;
            }
        }
        if ($counter > $question['options_amount']) {
            print("<script>location.href='/poll.php?uni=" . $poll['poll_unique'] . "'</script>");
            exit;
        }
        foreach ($options as $option) {
            $stmt = $pdo->prepare("UPDATE options SET votes = votes + ? WHERE option_id = ?");
            $stmt->bindValue(1, (isset($_POST['vote_'.$option["option_id"]]) ? "1" : "0"), PDO::PARAM_INT);
            $stmt->bindValue(2, $_POST['option_'.$option["option_id"]]);
            $result = $stmt->execute();
            if (!$result) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
            } 
        }
        $stmt = $pdo->prepare("UPDATE polls_users SET answered_current = 1 WHERE poll_user_id = ?");
        $stmt->bindValue(1, $poll_user['poll_user_id']);
        $result = $stmt->execute();
        if (!$result) {
            error('Datenbank Fehler!', pdo_debugStrParams($stmt));
        } 
        print('<div style="display: none">unstop</div>');
        print("<script>location.href='/poll.php?uni=" . $poll['poll_unique'] . "'</script>");
        exit;
    }
}
if ($poll_user['forcerefresh'] == 1) {
    echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die Wahl freigegeben wird.</h3>');
    print('<div style="display: none">unstop</div>');
    $stmt = $pdo->prepare("UPDATE polls_users SET forcerefresh = 0 WHERE poll_user_id = ?");
    $stmt->bindValue(1, $poll_user['poll_user_id']);
    $result = $stmt->execute();
    if (!$result) {
        error('Datenbank Fehler!', pdo_debugStrParams($stmt));
    } 
    exit;
}
if ($poll_user['refresh'] == 1) {
    $stmt = $pdo->prepare('SELECT * FROM questions where poll_id  = ? and current = 1');
    $stmt->bindValue(1, $poll_user["poll_id"], PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() < 1) {
        echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die Wahl freigegeben wird.</h3>');
        print('<div style="display: none">unstop</div>');
        exit;
    } else {
        if ($poll_user['answered_current'] == 1) {
            echo('<h3 class="display-6 text-center text-success">Du hast deine Stimme abgegeben</h3>');
            print('<div style="display: none">unstop</div>');
            exit;
        }
        if ($poll_user['refresh'] == 1) {
            $question = $stmt->fetch();

            $stmt = $pdo->prepare('SELECT * FROM options where question_id  = ?');
            $stmt->bindValue(1, $question["question_id"], PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() < 1) {
                error('Fehler!');
            } 
            $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare('SELECT * FROM polls where poll_id  = ?');
            $stmt->bindValue(1, $poll_user["poll_id"], PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() != 1) {
                error_log($stmt->rowCount());
                header("location: group.php");
                exit;
            }
            $poll = $stmt->fetch();
            echo($errormsg);
            echo('<form action="/php/refresher.php" method="POST"><div class="card-title row"><h2 class="col-8 text-kolping-orange text-start">' . $question["question"] . '</h2><h class="col-4 ctext text-end">Stimmen: ' . $question["options_amount"] . '</h2></div>');
            foreach ($options as $option) {
            print('<div class="input-group justify-content-center my-2"><label for="'. $option["option_id"] . '" class="input-group-text">' . $option["option_name"] . '</label><div class="input-group-text"><input type="number" value="' . $option["option_id"] . '" name="option_' . $option["option_id"] . '" style="display: none;" required><input id="' . $option["option_id"] . '" type="checkbox" name="vote_' . $option["option_id"] . '" value="0" class="form-check-input checkbox-kolping mt-0"></div></div>');
            }
            print('<div class="d-grid gap-2 d-md-flex justify-content-md-center"><button type="submit" name="action" value="submit" class="btn btn-success my-2">Stimme Abgeben</button></div></form><div style="display: none">stop</div>');
        } else {
            echo('<h3 class="display-6 text-center text-danger">Fehler!</h3>');
            print('<div style="display: none">unstop</div>');
        }
    }
} else {
    echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die Wahl freigegeben wird.</h3>');
    print('<div style="display: none">unstop</div>');
}
?>