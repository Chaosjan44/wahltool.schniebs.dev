<?php
$crdate = "2023";
?>
    </div>
</body>

<?php if (!isMobile()): ?>
    <footer class="container-fluid cbg2 footer py-3 sticky-bottom">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-2 text-start">
                    <ul class="px-0">
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/impressum.php" class="link ctext ps-2">Impressum</a>
                        </li>
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/disclaimer.php" class="link ctext ps-2">Disclaimer</a> 
                        </li>
                    </ul>
                </div>
                <div class="col-2 text-start">
                    <ul class="px-0">
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/datenschutz.php" class="link ctext ps-2">Datenschutz</a>
                        </li>
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/internal.php" class="link ctext ps-2">Intern</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row justify-content-end align-items-center">
            <div class="col-4 text-center ctext light"><a href="/internal.php" class="text-center ctext light">&copy; <?=$crdate?> Jan Schniebs</a></div>
            <div class="col-4 d-flex justify-content-end">
                <input onchange="toggleStyle()" class="styleswitcher" type="checkbox" name="switch" id="style_switch" <?php if (check_style() == "dark"): print("checked"); endif; ?> >
                <label class="styleswitcherlabel" for="style_switch"></label>
            </div>
        </div>
    </footer>
<?php else: ?>
    <footer class="container-fluid cbg2 footer py-3">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-6 text-start ps-2">
                    <ul class="px-0">
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/impressum.php" class="link ctext ps-2">Impressum</a>
                        </li>
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/disclaimer.php" class="link ctext ps-2">Disclaimer</a> 
                        </li>
                    </ul>
                </div>
                <div class="col-6 text-start ps-2">
                    <ul class="px-0">
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/datenschutz.php" class="link ctext ps-2">Datenschutz</a>
                        </li>
                        <li>
                            <i class="bi bi-chevron-right"></i>
                            <a href="/internal.php" class="link ctext ps-2">Intern</a>
                        </li>
                    </ul>
                </div>
                <div class="row justify-content-between align-items-center">
                    <div class="col-8 ctext text-start light ps-0"><a href="/internal.php" class="ctext light">&copy; <?=$crdate?> Jan Schniebs</a></div>
                    <div class="col-4 d-flex justify-content-end">
                        <input onchange="toggleStyle()" class="styleswitcher" type="checkbox" name="switch" id="style_switch" <?php if (check_style() == "dark"): print("checked"); endif; ?> >
                        <label class="styleswitcherlabel" for="style_switch"></label>
                    </div>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

<script src="/js/custom.js"></script>
</html>