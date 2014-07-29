<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle; ?></title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?= $http_root_css; ?>styles.css" />
    <script type="text/javascript" src="<?= $http_root_js; ?>jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="<?= $http_root_js; ?>actions.js"></script>
</head>
<body>
    <header>
        <nav>
            <?= $menuNavigation; ?>
        </nav>
    </header>
    <section>
        <div id="container">
            <div id="quotes">
                <?= $this->fetch($GLOBALS['config']['root_tpl'] . $innerTemplate); ?>
            </div>
        </div>
    </section>
</body>
</html>