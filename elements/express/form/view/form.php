<?php
defined('C5_EXECUTE') or die("Access Denied.");
?>

<div class="ccm-dashboard-express-entry">
    <?php
    foreach ($form->getFieldSets() as $fieldSet) { ?>

        <fieldset>
            <?php if ($fieldSet->getTitle()) { ?>
                <legend><?= $fieldSet->getTitle() ?></legend>
            <?php } ?>

            <?php

            foreach($fieldSet->getControls() as $setControl) {
                $controlView = $setControl->getControlView(
                    new \Concrete\Core\Express\Form\Context\DashboardViewContext()
                );

                if (is_object($controlView)) {
                    $renderer = $controlView->getControlRenderer();
                    print $renderer->render();
                }
            }

            ?>
        </fieldset>
    <?php } ?>
</div>
