<?defined('C5_EXECUTE') or die("Access Denied.")?>

<?$valt = Loader::helper('validation/token')?>

<?php
if ($this->controller->getTask() == 'translate_po') {

//	Loader::packageElement('simplepo/edit', 'multilingual_plus', array('catalogID' => $catalogID,'lang'=>$lang));

}  else {

	if (!is_dir(DIR_LANGUAGES_SITE_INTERFACE) || !is_writable(DIR_LANGUAGES_SITE_INTERFACE)) { ?>
		<div class="alert alert-warning"><?=t('You must create the directory %s and make it writable before you may run this tool. Additionally, all files within this directory must be writable.', DIR_LANGUAGES_SITE_INTERFACE)?></div>
	<? } ?>

	<?php
	$nav = Loader::helper('navigation');
	Loader::model('section', 'multilingual');
	$pages = \Concrete\Core\Multilingual\Page\Section::getList();
	$defaultLanguage = Config::get('concrete.multilingual.default_locale');

	$ch = Core::make('multilingual/interface/flag');
	if (count($pages) > 0) { ?>

<div class="ccm-dashboard-content-full">
    <div class="table-responsive">
        <table class="ccm-search-results-table">
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th><span><?=t("Name")?></span></th>
                <th><span><?=t('Locale')?></span></th>
                <th colspan="2"><span><?=t('Completion')?></span></th>
                <th><span><?=t('Last Updated')?></span></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <? foreach($pages as $pc) {
                $pcl = \Concrete\Core\Multilingual\Page\Section::getByID($pc->getCollectionID());?>
                <tr>
                    <td><?=$ch->getSectionFlagIcon($pc)?></td>
                    <td>
                        <a href="<?=$nav->getLinkToCollection($pc)?>">
                            <?=$pc->getCollectionName()?>
                        </a>
                    </td>
                    <td style="white-space: nowrap">
                        <?php echo $pc->getLocale(); ?>
                        <? if ($pc->getLocale() != $defaultLanguage) { ?>
                            <a href="#" class="icon-link launch-tooltip" title="<?=REL_DIR_LANGUAGES_SITE_INTERFACE?>/<?=$pc->getLocale()?>.mo"><i class="fa fa-question-circle"></i></a>
                        <? } ?>
                    </td>
                    <td style="width: 40%">
                        <? if ($pc->getLocale() != $defaultLanguage) { ?>
                            <? //$spl = SimplePoInterface::getCatalogDataByName($pc->getLocale());?>
                            <div class="progress">
                                <div class="bar" style="width: <?=$spl['percent_complete']?>%">&nbsp;</div>
                            </div>
                        <? } ?>
                    </td>
                    <td style="white-space: nowrap">
                        <span class="percent"><?=$spl['percent_complete']?>%</span> - <span class="translated"><?=$spl['translated_count']?></span> <?=t('of')?> <span class="total"><?=$spl['message_count']?></span>
                    </td>
                    <td>
                        <? if ($pc->getLocale() != $defaultLanguage) {
                            if (file_exists(DIR_LANGUAGES_SITE_INTERFACE . '/' . $pc->getLocale() . '.mo'))
                                print date('F d, Y g:i:s A', filemtime(DIR_LANGUAGES_SITE_INTERFACE . '/' . $pc->getLocale() . '.mo'));
                            else
                                print t('File not found.');
                        }
                        else
                            echo t('N/A'); ?>
                    </td>
                    <? if ($pc->getLocale() == $defaultLanguage) { ?>
                        <td></td>
                    <? } else { ?>
                        <td><a href="<?=$this->action('translate_po', $spl['id'])?>" class="icon-link"><i class="fa fa-pencil"></i></a></td>
                    <? } ?>
                </tr>
            <? } ?>
            </tbody>
        </table>
    </div>
</div>

        <?
        if (is_dir(DIR_LANGUAGES_SITE_INTERFACE) && is_writable(DIR_LANGUAGES_SITE_INTERFACE)) { ?>

        <div class="ccm-dashboard-header-buttons">
            <form method="post" action="<?=$controller->action('reload')?>">
                <?=$valt->output('reload')?>
                <button class="btn btn-default" type="submit"><?=t('Reload Strings')?></button>
            </form>
        </div>

        <? } ?>

        <style type="text/css">
            table.ccm-search-results-table div.progress {
                margin-bottom: 0px;
            }
        </style>


	<? } else { ?>
		<p><?=t('You have not created any multilingual content sections yet.')?></p>
	<? } ?>
<? } ?>
</div>