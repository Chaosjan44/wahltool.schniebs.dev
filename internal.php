<?php 
require_once("php/functions.php");
$user = check_user();
if ($user == false) {
    print("<script>location.href='login.php'</script>");
    exit;
}
// error_log(print_r($user,true));
require_once("templates/header.php"); ?>

<div class="container py-3">
    <div>
        <div class="card cbg2 my-3 py-3 px-3">
            <div class="card-body text-center">
                <h1 class="card-title display-3 text-center mb-4 text-kolping-orange">Interner Bereich</h1>
                <?php if (!isMobile()): ?>
                    <div class="card-text">
                        <?php if ($user['perm_admin'] == "1"): ?>
                            <button class="btn btn-kolping mx-1" type="button" onclick="window.location.href = '/internal/user.php';">Nutzer*innen</button>
                            <button class="btn btn-kolping mx-1" type="button" onclick="window.location.href = '/internal/groups.php';">Gruppen</button>
                        <?php endif; ?>
                        <button class="btn btn-kolping mx-1 my-2" type="button" onclick="window.location.href = '/internal/group.php';">Gruppe</button>
                        <button class="btn btn-kolping mx-1 my-2" type="button" onclick="window.location.href = '/internal/settings.php';">Einstellungen</button>
                        <button class="btn btn-kolping mx-1 my-2" type="button" onclick="window.location.href = '/internal/logout.php';">Abmelden</button>
                    </div>
                <?php else: ?>
                    <div class="card-text my-2">
                        <?php if ($user['perm_admin'] == "1"): ?>
                            <button class="btn btn-kolping mx-1" type="button" onclick="window.location.href = '/internal/user.php';">Nutzer*innen</button>
                            <button class="btn btn-kolping mx-1" type="button" onclick="window.location.href = '/internal/groups.php';">Gruppen</button>
                        <?php endif; ?>
                        <button class="btn btn-kolping mx-1" type="button" onclick="window.location.href = '/internal/group.php';">Gruppe</button>
                    </div>
                    <div class="card-text">
                        <button class="btn btn-kolping mx-1 my-2" type="button" onclick="window.location.href = '/internal/settings.php';">Einstellungen</button>
                        <button class="btn btn-kolping mx-1 my-2" type="button" onclick="window.location.href = '/internal/logout.php';">Abmelden</button>
                    </div>
                <?php endif;?>
            </div>
        </div>
    </div>
</div>





<?php require_once("templates/footer.php"); ?>