<?php 
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$poll_user = check_poll_user();
if ($poll_user == false) {
    print('<h1 class="text-center text-danger display-5">Seite neu laden!</h1>');
    print('<div style="display: none">unstop</div>');
    exit;
}
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
                $stmt = $pdo->prepare("UPDATE polls_users SET error_msg = '' WHERE poll_user_id = ?");
                $stmt->bindValue(1, $poll_user['poll_user_id']);
                $result2 = $stmt->execute();
                if (!$result2) {
                    error('Datenbank Fehler!', pdo_debugStrParams($stmt));
                }
            }
            $stmt = $pdo->prepare("UPDATE questions SET votes_given = votes_given + 1 WHERE question_id = ?");
            $stmt->bindValue(1, $question["question_id"]);
            $result0 = $stmt->execute();
            if (!$result0) {
                error('Datenbank Fehler!', pdo_debugStrParams($stmt));
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

?>