<?php
use Concrete\Core\Localization\Translation\PackageLocaleStatus;

defined('C5_EXECUTE') or die('Access Denied.');

/* @var Concrete\Core\Validation\CSRF\Token $token */
/* @var Concrete\Controller\SinglePage\Dashboard\System\Basics\Multilingual\Update $controller */
/* @var Concrete\Core\Localization\Translation\LocaleStatus[] $data */

$someUpdateAvailable = false;
?>
<div class="panel-group" id="ccm-packages" role="tablist" aria-multiselectable="true">
    <?php
    $class = ' in';
    foreach ($data as $details) {
        if ($details instanceof PackageLocaleStatus) {
            $handle = $details->getPackage()->getPackageHandle();
            $name = $details->getPackage()->getPackageName();
        } else {
            $handle = 'concrete5';
            $name = t('concrete5');
        }
        ?>
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="ccm-package-<?= $handle ?>-header">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#ccm-packages" href="#ccm-package-<?= $handle ?>-body" aria-expanded="true" aria-controls="ccm-package-<?= $handle ?>-body">
                        <?= $name ?>
                        <?php
                        if (!empty($details->getInstalledOutdated())) {
                            ?><span class="badge"><?= count($details->getInstalledOutdated()) ?></span><?php
                        }
                        ?>
                    </a>
                </h4>
            </div>
            <div id="ccm-package-<?= $handle ?>-body" class="panel-collapse collapse<?= $class ?>" role="tabpanel" aria-labelledby="ccm-package-<?= $handle ?>-header">
                <div class="panel-body">
                    <table class="table table-hover table-condensed">
                        <colgroup>
                            <col width="60" />
                            <col width="1" />
                            <col />
                            <col />
                            <col width="1" />
                        </colgroup>
                        <tbody>
                            <?php
                            if (!empty($details->getInstalledOutdated())) {
                                $someUpdateAvailable = true;
                                ?>
                                <tr><th colspan="5"><?= t('Updates to installed languages') ?></th></tr>
                                <?php
                                foreach ($details->getInstalledOutdated() as $localeID => $rl) {
                                    echo $controller->getLocaleRowHtml($localeID, $handle, $rl->getRemoteStats(), $rl->getLocalStats(), 'update');
                                }
                            }
                            if (!empty($details->getOnlyRemote())) {
                                ?>
                                <tr><th colspan="5"><?= t('Installable languages') ?></th></tr>
                                <?php
                                foreach ($details->getOnlyRemote() as $localeID => $remoteStats) {
                                    echo $controller->getLocaleRowHtml($localeID, $handle, $remoteStats, null, 'install');
                                }
                            }
                            if (!empty($details->getInstalledUpdated())) {
                                ?>
                                <tr><th colspan="5"><?= t('Up-to-date languages') ?></th></tr>
                                <?php
                                foreach ($details->getInstalledUpdated() as $localeID => $rl) {
                                    echo $controller->getLocaleRowHtml($localeID, $handle, $rl->getRemoteStats(), $rl->getLocalStats(), '');
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        $class = '';
    }
    ?>
</div>
<?php
if ($someUpdateAvailable) {
    ?>
    <form method="post" action="<?= h($view->action('update_all_outdated')) ?>">
        <?php $token->output('update-all-outdated') ?>
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <input type="submit" class="btn btn-primary pull-right" value="<?= h(t('Update all outdated languages')) ?>" />
            </div>
        </div>
    </form>
    <?php
}
?>
<script>
$(document).ready(function() {
    $('.ccm-install-package-locale').on('click', function() {
        var $btn = $(this);
        $.concreteAjax({
            url: $btn.data('action'),
            data: {ccm_token: $btn.data('token')},
            success: function(r) {
                $btn
                    .text($btn.data('is-update') ? <?= json_encode(t('Updated')) ?> : <?= json_encode(t('Installed')) ?>)
                    .attr('disabled', 'disabled')
                    .off('click')
                    ;
            }
        });
    });
});
</script>