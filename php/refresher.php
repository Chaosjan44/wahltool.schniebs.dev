<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$poll_user = check_poll_user();
if ($poll_user == false) {
    print('<h1 class="text-center text-danger display-5">Seite neu laden!</h1>');
    print('<div style="display: none">unstop</div>');
    exit;
}

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
        if ($poll_user["answered_current"] === 1) {
            print("<script>location.href='/poll.php?uni=" . $poll['poll_unique'] . "'</script>");
        } else {
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
                $error_msg = '<h1 class="text-center text-danger display-5">Du hast zu viel Stimmen abgegeben!</h1>';
                $stmt = $pdo->prepare("UPDATE polls_users SET error_msg = ? WHERE poll_user_id = ?");
                $stmt->bindValue(1, $error_msg);
                $stmt->bindValue(2, $poll_user['poll_user_id']);
                $result = $stmt->execute();
                if (!$result) {
                    error('Datenbank Fehler!', pdo_debugStrParams($stmt));
                }
                print("<script>location.href='/poll.php?uni=" . $poll['poll_unique'] . "'</script>");
                exit;
            }
            print('<script type="text/javascript">dellocalstor("giveerror")</script>');
            foreach ($options as $option) {
                $stmt = $pdo->prepare("UPDATE options SET votes = votes + ? WHERE option_id = ?");
                $stmt->bindValue(1, (isset($_POST['vote_'.$option["option_id"]]) ? "1" : "0"), PDO::PARAM_INT);
                $stmt->bindValue(2, $_POST['option_'.$option["option_id"]]);
                $result = $stmt->execute();
                if (!$result) {
                    error('Datenbank Fehler!', pdo_debugStrParams($stmt));
                } 
                $stmt = $pdo->prepare("UPDATE questions SET votes_given = votes_given + 1 WHERE question_id = ?");
                $stmt->bindValue(1, $question["question_id"]);
                $result0 = $stmt->execute();
                if (!$result0) {
                    error('Datenbank Fehler!', pdo_debugStrParams($stmt));
                } 
                $stmt = $pdo->prepare("UPDATE polls_users SET error_msg = '' WHERE poll_user_id = ?");
                $stmt->bindValue(1, $poll_user['poll_user_id']);
                $result2 = $stmt->execute();
                if (!$result2) {
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
}
if ($poll_user['forcerefresh'] == 1) {
    echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die nächste Frage freigegeben wird.</h3>');
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
        echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die nächste Frage freigegeben wird.</h3>');
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
            echo('<form action="/php/refresher.php" method="POST"><div class="card-title row"><h2 class="col-8 text-kolping-orange text-start">' . $question["question"] . '</h2><h class="col-4 ctext text-end"><span id="cur_checked">0</span> / ' . $question["options_amount"] . '</h2></div>');
            echo($poll_user['error_msg']);
            foreach ($options as $option) {
            print('<div class="input-group justify-content-center my-2"><label for="'. $option["option_id"] . '" class="input-group-text">' . $option["option_name"] . '</label><div class="input-group-text"><input type="number" value="' . $option["option_id"] . '" name="option_' . $option["option_id"] . '" style="display: none;" required><input id="' . $option["option_id"] . '" type="checkbox" name="vote_' . $option["option_id"] . '" value="0" class="form-check-input checkbox-kolping mt-0" onchange="updatecur()"></div></div>');
            }
            print('<div class="d-grid gap-2 d-md-flex justify-content-md-center"><button type="submit" name="action" value="submit" class="btn btn-success my-2">Abstimmen</button></div></form><div style="display: none">stop</div>');
        } else {
            echo('<h3 class="display-6 text-center text-danger">Fehler!</h3>');
            print('<div style="display: none">unstop</div>');
        }
    }
} else {
    echo('<h3 class="display-6 text-center text-kolping-orange">Warte bis die nächste Frage freigegeben wird.</h3>');
    print('<div style="display: none">unstop</div>');
}
?>