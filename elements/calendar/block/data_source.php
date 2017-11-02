<?php

defined('C5_EXECUTE') or die("Access Denied.");
$form = Core::make("helper/form");

$calendars = array_filter(\Concrete\Core\Calendar\Calendar::getList(), function ($calendar) {
    $p = new \Permissions($calendar);

    return $p->canViewCalendarInEditInterface();
});
if (isset($multiple) && $multiple) {
    $calendarSelect = array();
} else {
    $calendarSelect = array('' => t('** Select a Calendar'));
}

foreach ($calendars as $calendar) {
    $calendarSelect[$calendar->getID()] = $calendar->getName();
}

$chooseCalendar = 'all';
$calendarAttributeKeys = [];
if (compat_is_version_8()) {
    $keys = \Concrete\Core\Attribute\Key\SiteKey::getList();
    foreach ($keys as $ak) {
        if ($ak->getAttributeTypeHandle() == 'calendar') {
            $calendarAttributeKeys[] = $ak;
        }
    }
}

$calendarAttributeKeySelect = array('' => t('** Select Attribute'));
foreach ($calendarAttributeKeys as $ak) {
    $calendarAttributeKeySelect[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyDisplayName();
}

if ($calendarAttributeKeyHandle) {
    $chooseCalendar = 'site';
} else {
    $chooseCalendar = 'specific';
}
?>

<div class="form-group">
    <label class="control-label"><?= t('Calendar') ?></label>
    <?php /* ?>
    <div class="radio">
        <label>
            <input type="radio" name="chooseCalendar" value="all" <?php if ($chooseCalendar == 'all') { ?> checked <?php } ?>>
            All Events
        </label>
    </div>*/ ?>
    <div class="radio">
        <label>
            <input type="radio" name="chooseCalendar" value="specific" <?php if ($chooseCalendar == 'specific') {
    ?> checked <?php 
} ?>>
            Specific Calendar
        </label>
    </div>
    <?php if (count($calendarAttributeKeys)) {
    ?>
    <div class="radio">
        <label>
            <input type="radio" name="chooseCalendar" value="site" <?php if ($chooseCalendar == 'site') {
    ?> checked <?php 
}
    ?>>
            Site-wide Calendar
        </label>
    </div>
        <div data-row="calendar-attribute">
            <div class="form-group">
                <?= $form->label('calendarAttributeKeyHandle', t('Calendar Site Attribute')) ?>
                <?php echo $form->select('calendarAttributeKeyHandle', $calendarAttributeKeySelect, $calendarAttributeKeyHandle);
    ?>
            </div>
        </div>
    <?php 
} ?>
    <div data-row="specific-calendar">
        <div class="form-group">
            <?php
            $method = isset($multiple) && $multiple ? 'selectMultiple' : 'select';
            $calendarField = isset($multiple) && $multiple ? 'caID[]' : 'caID';
            ?>
            <?= $form->label($calendarField, t('Calendar')) ?>
            <?php echo $form->$method('caID', $calendarSelect, $caID); ?>
        </div>
    </div>
</div>


<script type="text/javascript">
   $(function() {
       $('input[name=chooseCalendar]').on('change', function() {
           var selected = $('input[name=chooseCalendar]:checked').val();
           if (selected == 'site') {
               $('div[data-row=calendar-attribute]').show();
               $('div[data-row=specific-calendar]').hide();
           } else if (selected == 'specific') {
               $('div[data-row=specific-calendar]').show();
               $('div[data-row=calendar-attribute]').hide();
           } else {
               $('div[data-row=specific-calendar]').hide();
               $('div[data-row=calendar-attribute]').hide();
           }
       }).trigger('change');
   });
</script>