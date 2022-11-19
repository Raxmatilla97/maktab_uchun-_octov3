<?php Block::put('breadcrumb') ?>
    <ul>
        <li><a href="<?= Backend::url('system/updates') ?>"><?= e(__('System Updates')) ?></a></li>
        <li><a href="<?= Backend::url('system/market') ?>"><?= e(__('Marketplace')) ?></a></li>
        <li><?= e(trans($this->pageTitle)) ?></li>
    </ul>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>

    <div class="mw-950">
        <?php if ($warnings = $this->updaterWidget->renderWarnings()): ?>
            <?= $warnings ?>
        <?php endif ?>

        <div class="row">
            <div class="col-sm-9">
                <?= $this->makePartial('details_scoreboard') ?>
            </div>
            <div class="col-sm-3">
                <?= $this->makePartial('details_toolbar') ?>
            </div>
        </div>

        <div class="control-tabs primary-tabs" data-control="tab">
            <ul class="nav nav-tabs">
                <li class="<?= $activeTab == 'readme' ? 'active' : '' ?>">
                    <a
                        href="#readme"
                        data-tab-url="<?= Backend::url('system/market/plugin/'.$urlCode.'/readme') ?>">
                        <?= e(__('Documentation')) ?>
                    </a>
                </li>
                <li class="<?= $activeTab == 'upgrades' ? 'active' : '' ?>">
                    <a
                        href="#upgrades"
                        data-tab-url="<?= Backend::url('system/market/plugin/'.$urlCode.'/upgrades') ?>">
                        <?= e(__('Upgrade Guide')) ?>
                    </a>
                </li>
                <li class="<?= $activeTab == 'license' ? 'active' : '' ?>">
                    <a
                        href="#license"
                        data-tab-url="<?= Backend::url('system/market/plugin/'.$urlCode.'/license') ?>">
                        <?= e(__('Licence')) ?>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane <?= $activeTab == 'readme' ? 'active' : '' ?>">
                    <div class="plugin-details-content">
                        <?php if ($product->contentHtml): ?>
                            <?= $product->contentHtml ?>
                        <?php else: ?>
                            <p><?= e(__('There is no documentation provided.')) ?></p>
                        <?php endif ?>
                    </div>
                </div>
                <div class="tab-pane <?= $activeTab == 'upgrades' ? 'active' : '' ?>">
                    <div class="plugin-details-content">
                        <?php if ($product->upgradeHtml): ?>
                            <?= $product->upgradeHtml ?>
                        <?php else: ?>
                            <p><?= e(__('There are no upgrade instructions provided.')) ?></p>
                        <?php endif ?>
                    </div>
                </div>
                <div class="tab-pane <?= $activeTab == 'license' ? 'active' : '' ?>">
                    <div class="plugin-details-content">
                        <?php if ($product->licenseHtml): ?>
                            <?= $product->licenseHtml ?>
                        <?php else: ?>
                            <p><?= e(__('There is no licence provided.')) ?></p>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

<?php else: ?>

    <p class="flash-message static error"><?= e($this->fatalError) ?></p>
    <p><a href="<?= Backend::url('system/updates') ?>" class="btn btn-default"><?= __('Return to System Settings') ?></a></p>

<?php endif ?>
