<?php
chdir ($_SERVER['DOCUMENT_ROOT']);
require_once("php/functions.php");
$user = check_user();
if (!isset($user)) {
    print("<script>location.href='/login.php'</script>");
    exit;
}
if (!isset($_GET["id"]) && !isset($_GET["create"])) {
    header("location: group.php");
    exit;
}

if (isset($_GET["create"])):
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
    <div class="container-xxl py-3" style="min-height: 80vh;">
        <div class="row">
            <h1 class="display-4 text-center mb-3 text-kolping-orange"><?=$poll["poll_name"]?></h1>
        </div>
        <div class="<?php if (!isMobile()) print("row");?>">
            <div class="col<?php if (!isMobile()) print("-6");?> mb-3">
                <?php foreach ($questions as $question):
                    $stmt = $pdo->prepare('SELECT * FROM options where question_id  = ?');
                    // bindValue will allow us to use integer in the SQL statement, we need to use for LIMIT
                    $stmt->bindValue(1, $questions[$i]["question_id"], PDO::PARAM_INT);
                    $stmt->execute();
                    if (!$result) {} else {
                        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                ?>
                    <div class="card cbg2 py-3 px-3">
                        <div class="row g-0">
                            <div class="col">
                                <div class="card cbg text-size-larger py-3 px-3 align-items-center text-center">
                                </div>
                            </div>
                            <div class="col-md-10 d-flex justify-content-start align-items-center">
                                <div class="card-body ctext align-items-center">
                                    <h3 class="card-title align-center"><?=$event[0]['title']?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?=$question?>
                <?php endforeach;?>
            </div>
        </div>
    </div>

    <?php
    include_once("templates/footer.php");







else:



$stmt = $pdo->prepare('SELECT * FROM polls where poll_id  = ? and group_id = ?');
// bindValue will allow us to use integer in the SQL statement, we need to use for LIMIT
$stmt->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$stmt->bindValue(2, $user["sel_group_id"]);
$stmt->execute();
if ($stmt->rowCount() != 1) {
    error_log($stmt->rowCount());
    header("location: group.php");
    exit;
}
$poll = $stmt->fetch();

$stmt = $pdo->prepare('SELECT * FROM questions where poll_id  = ?');
// bindValue will allow us to use integer in the SQL statement, we need to use for LIMIT
$stmt->bindValue(1, $_GET["id"], PDO::PARAM_INT);
$stmt->execute();
if (!$result) {
    error_log($stmt->rowCount());
    header("location: group.php");
    exit;
}
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require("templates/header.php");
?>
<div class="container-xxl py-3" style="min-height: 80vh;">
    <div class="row">
        <h1 class="display-4 text-center mb-3 text-kolping-orange"><?=$poll["poll_name"]?></h1>
    </div>
    <div class="<?php if (!isMobile()) print("row");?>">
        <div class="col<?php if (!isMobile()) print("-6");?> mb-3">
            <?php foreach ($questions as $question):
                $stmt = $pdo->prepare('SELECT * FROM options where question_id  = ?');
                // bindValue will allow us to use integer in the SQL statement, we need to use for LIMIT
                $stmt->bindValue(1, $questions[$i]["question_id"], PDO::PARAM_INT);
                $stmt->execute();
                if (!$result) {} else {
                    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            ?>
                <div class="card cbg2 py-3 px-3">
                    <div class="row g-0">
                        <div class="col">
                            <div class="card cbg text-size-larger py-3 px-3 align-items-center text-center">
                            </div>
                        </div>
                        <div class="col-md-10 d-flex justify-content-start align-items-center">
                            <div class="card-body ctext align-items-center">
                                <h3 class="card-title align-center"><?=$event[0]['title']?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?=$question?>
            <?php endforeach;?>
        </div>
    </div>
</div>

<?php
include_once("templates/footer.php");
endif;
?>
