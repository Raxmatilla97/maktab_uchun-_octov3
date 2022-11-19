<!DOCTYPE html>
<html lang="<?= App::getLocale() ?>">
    <head>
        <title><?= Lang::get('system::lang.page.maintenance.label') ?></title>
        <link href="<?= Url::asset('/modules/system/assets/css/styles.css') ?>" rel="stylesheet" />
        <script src="<?= Url::asset('modules/system/assets/js/framework-bundle.min.js') ?>"></script>
        <meta name="turbo-visit-control" content="disable" />
        <meta charset="utf-8" />
    </head>
    <body>
        <div class="container">
            <h1><i class="icon-wrench warning"></i> <?= Lang::get('system::lang.page.maintenance.label') ?></h1>
            <p class="lead"><?= Lang::get('system::lang.page.maintenance.help') ?></p>
            <p class="lead">
                <?php if ($message): ?>
                    <strong><?= Lang::get('system::lang.page.maintenance.message') ?></strong> <?= $message ?><br />
                <?php endif ?>
                <?php if ($willBeAvailableAt): ?>
                    <strong><?= Lang::get('system::lang.page.maintenance.available_at') ?></strong> <?= $willBeAvailableAt ?><br />
                <?php endif ?>
            </p>
        </div>
    </body>
</html>
