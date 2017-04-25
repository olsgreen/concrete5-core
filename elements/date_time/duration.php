<?php
use Concrete\Core\Permission\Duration;

defined('C5_EXECUTE') or die("Access Denied.");

$r = \Concrete\Core\Http\ResponseAssetGroup::get();
$r->requireAsset('selectize');

if (!$timezone) {
    if (Config::get('concrete.misc.user_timezones')) {
        $user = new User();
        $userInfo = $user->getUserInfoObject();
        $timezone = $userInfo->getUserTimezone();
    } else {
        $site = \Core::make('site')->getSite();
        $timezone = $site->getConfigRepository()->get('timezone');
    }
}

$repeats = array(
    '' => t('** Options'),
    'daily' => t('Every Day'),
    'weekly' => t('Every Week'),
    'monthly' => t('Every Month'),
);
$repeatDays = array();
for ($i = 1; $i <= 30; ++$i) {
    $repeatDays[$i] = $i;
}
$repeatWeeks = array();
for ($i = 1; $i <= 30; ++$i) {
    $repeatWeeks[$i] = $i;
}
$repeatMonths = array();
for ($i = 1; $i <= 12; ++$i) {
    $repeatMonths[$i] = $i;
}

$service = Core::make('helper/date');
$now = $service->toDateTime('now', $timezone);

$pdStartDate = $now->format('Y-m-d');

$pdEndDate = false;
$pdRepeats = false;
$pdRepeatPeriod = false;
$pdRepeatPeriodWeekDays = array();
$pdRepeatPeriodDaysEvery = 1;
$pdRepeatPeriodWeeksEvery = 1;
$pdRepeatPeriodMonthsEvery = 1;
$pdRepeatPeriodMonthsRepeatBy = 'month';
$pdEndRepeatDateSpecific = false;
$pdEndRepeatDate = '';

$now = $service->toDateTime('now', $timezone);
$currentHour = $now->format('g');
$currentMinutes = $now->format('i');
$currentAM = $now->format('a');

$selectedStartTime = $currentHour . ':00' . $currentAM;
if ($currentMinutes > 29) {
    $selectedStartTime = $currentHour . ':30' . $currentAM;
}

$selectedEndTime = null;

if (is_object($pd)) {
    $pdStartDate = $pd->getStartDate();
    $pdEndDate = $pd->getEndDate();

    $selectedStartTime = $service->toDateTime($pdStartDate, $timezone)->format('g:ia');
    $selectedEndTime = $service->toDateTime($pdEndDate, $timezone)->format('g:ia');

    $pdStartDate = $service->toDateTime($pdStartDate, $timezone)->format('Y-m-d');
    $pdEndDate = $service->toDateTime($pdEndDate, $timezone)->format('Y-m-d');

    $pdRepeats = $pd->repeats();
    $pdStartDateAllDay = $pd->isStartDateAllDay();
    $pdEndDateAllDay = $pd->isEndDateAllDay();
    $pdRepeatPeriodInt = $pd->getRepeatPeriod();
    $pdRepeatPeriodWeekDays = $pd->getRepeatPeriodWeekDays();
    if ($pdRepeatPeriodInt === $pd::REPEAT_DAILY) {
        $pdRepeatPeriod = 'daily';
        $pdRepeatPeriodDaysEvery = $pd->getRepeatPeriodEveryNum();
    }
    if ($pdRepeatPeriodInt === $pd::REPEAT_WEEKLY) {
        $pdRepeatPeriod = 'weekly';
        $pdRepeatPeriodWeeksEvery = $pd->getRepeatPeriodEveryNum();
    }
    if ($pdRepeatPeriodInt === $pd::REPEAT_MONTHLY) {
        $pdRepeatPeriod = 'monthly';
        $pdRepeatPeriodMonthsEvery = $pd->getRepeatPeriodEveryNum();
    }
    $pdRepeatPeriodMonthsRepeatLastDay = $pd->getRepeatMonthLastWeekday();
    $rmb = $pd->getRepeatMonthBy();
    if ($rmb) {
        if ($rmb === Duration::MONTHLY_REPEAT_MONTHLY) {
            $pdRepeatPeriodMonthsRepeatBy = 'month';
        } elseif ($rmb === Duration::MONTHLY_REPEAT_WEEKLY) {
            $pdRepeatPeriodMonthsRepeatBy = 'week';
        } elseif ($rmb === Duration::MONTHLY_REPEAT_LAST_WEEKDAY) {
            $pdRepeatPeriodMonthsRepeatBy = 'lastweekday';
        }
    }
    $pdEndRepeatDateSpecific = $pd->getRepeatPeriodEnd();
    if ($pdEndRepeatDateSpecific) {
        $pdEndRepeatDate = 'date';
    }
}
$form = Loader::helper('form');
$dt = Loader::helper('form/date_time');

$values = array();
foreach(array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) as $hour){
    $values[] = $hour . ':00am';
    $values[] = $hour . ':15am';
    $values[] = $hour . ':30am';
    $values[] = $hour . ':45am';
}
foreach(array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) as $hour){
    $values[] = $hour . ':00pm';
    $values[] = $hour . ':15pm';
    $values[] = $hour . ':30pm';
    $values[] = $hour . ':45pm';
}


/*
$times = array();
for ($i = 0; $i < count($values); $i++) {
    $value = $values[$i];
    $o = new stdClass;
    $o->id = $value;
    $o->text = $value;
    $times[] = $o;

}
*/

?>

<div id="ccm-permission-access-entity-time-settings-wrapper">

<div id="ccm-permissions-access-entity-dates">

    <div class="form-group">
        <div class="row">
            <div class="col-sm-6 ccm-date-time-date-group">
                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label"><?=t('From')?></label> <i class="fa fa-info-circle launch-tooltip" title="<?php echo t('Choose Repeat Event and choose a frequency to make this event recurring.')?>"></i>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6" data-column="start-date">
                        <?= $dt->date('pdStartDate', $pdStartDate); ?>
                    </div>
                    <div class="col-sm-6" id="pdStartDate_tw">
                        <select class="form-control" name="pdStartDateSelectTime" data-select="time">
                            <?php foreach($values as $value) { ?>
                                <option value="<?=$value?>" <?php if ($selectedStartTime == $value) { ?>selected<?php }?>><?=$value?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 ccm-date-time-date-group">
                <div class="row">
                    <div class="col-sm-12">
                        <label class="control-label"><?=t('To')?></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6" data-column="end-date">
                        <?= $dt->date('pdEndDate', $pdEndDate); ?>
                    </div>
                    <div class="col-sm-6"id="pdEndDate_tw">
                        <select class="form-control" name="pdEndDateSelectTime" data-select="time">
                            <?php foreach($values as $value) { ?>
                                <option value="<?=$value?>" <?php if ($selectedEndTime == $value) { ?>selected<?php }?>><?=$value?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
        </div>
    </div>

    <style type="text/css">
        div.form-inline-separator {
            font-size: 18px;
            color: #999;
            margin-left: 20px;
            margin-right: 20px;
            display: inline-block;
        }

    </style>

    <script type="text/javascript">
        $(function () {

            $('select[name=pdStartDateSelectTime]').selectize({
                create: true,
                copyClassesToDropdown: false,
                onChange: function(value) {
                    ccm_durationCalculateEndDate();
                }
            });

            $('select[name=pdEndDateSelectTime]').selectize({
                create: true,
                copyClassesToDropdown: false,
            });

        });
    </script>

</div>

<div class="form-group-highlight">

<div id="ccm-permissions-access-entity-repeat" style="display: none">

    <div class="row">
        <div class="col-sm-3">
            <label><?= $form->checkbox('pdStartDateAllDayActivate', 1,
                        $pdStartDateAllDay) ?> <?= t(
                        "All Day") ?></label>
        </div>
        <div class="col-sm-3">
            <label><?= $form->checkbox('pdRepeat', 1, $pdRepeats) ?> <?= t('Repeat Event') ?></label>
        </div>
        <div class="col-sm-6">
            <div class="pull-right text-muted">
                <?=$service->getTimeZoneDisplayName($timezone)?>
            </div>
        </div>
    </div>

</div>

    <hr/>

    <div id="ccm-permissions-access-entity-repeat-selector" style="display: none">


    <div class="form-group">
        <label for="pdRepeatPeriod" class="control-label"><?= t('Repeats') ?></label>
        <div class="">
            <?= $form->select('pdRepeatPeriod', $repeats, $pdRepeatPeriod) ?>
        </div>
    </div>

    <div id="ccm-permissions-access-entity-dates-repeat-daily" style="display: none">

        <div class="form-group">
            <label for="pdRepeatPeriodDaysEvery" class="control-label"><?= t('Repeat every') ?></label>
            <div class="">
                <div class="form-inline">
                    <?= $form->select(
                        'pdRepeatPeriodDaysEvery',
                        $repeatDays,
                        $pdRepeatPeriodDaysEvery,
                        array('style' => 'width: 60px')) ?>
                    <?= t('days') ?>
                </div>
            </div>
        </div>

    </div>

    <div id="ccm-permissions-access-entity-dates-repeat-monthly" style="display: none">


        <div class="form-group">
            <label for="pdRepeatPeriodMonthsRepeatBy" class="control-label"><?= t('Repeat By') ?></label>
            <div class="">
                <div class="radio"><label><?= $form->radio(
                            'pdRepeatPeriodMonthsRepeatBy',
                            'month',
                            $pdRepeatPeriodMonthsRepeatBy) ?> <?= t(
                            'Day of Month')
                        ?></label>
                </div>
                <div class="radio"><label><?= $form->radio(
                            'pdRepeatPeriodMonthsRepeatBy',
                            'week',
                            $pdRepeatPeriodMonthsRepeatBy) ?> <?= t('Day of Week') ?></label>
                </div>
                <div class="radio">
                    <label>
                        <?= $form->radio(
                            'pdRepeatPeriodMonthsRepeatBy',
                            'lastweekday',
                            $pdRepeatPeriodMonthsRepeatBy) ?> <?= t('The last ') ?>
                        <select name="pdRepeatPeriodMonthsRepeatLastDay">
                            <option
                                value="0" <?= $pdRepeatPeriodMonthsRepeatLastDay == 0 ? 'selected' : '' ?>><?= t('Sunday') ?></option>
                            <option
                                value="1" <?= $pdRepeatPeriodMonthsRepeatLastDay == 1 ? 'selected' : '' ?>><?= t('Monday') ?></option>
                            <option
                                value="2" <?= $pdRepeatPeriodMonthsRepeatLastDay == 2 ? 'selected' : '' ?>><?= t('Tuesday') ?></option>
                            <option
                                value="3" <?= $pdRepeatPeriodMonthsRepeatLastDay == 3 ? 'selected' : '' ?>><?= t('Wednesday') ?></option>
                            <option
                                value="4" <?= $pdRepeatPeriodMonthsRepeatLastDay == 4 ? 'selected' : '' ?>><?= t('Thursday') ?></option>
                            <option
                                value="5" <?= $pdRepeatPeriodMonthsRepeatLastDay == 5 ? 'selected' : '' ?>><?= t('Friday') ?></option>
                            <option
                                value="6" <?= $pdRepeatPeriodMonthsRepeatLastDay == 6 ? 'selected' : '' ?>><?= t('Saturday') ?></option>
                        </select>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="pdRepeatPeriodMonthsEvery" class="control-label"><?= t('Repeat every') ?></label>
            <div class="">
                <div class="form-inline">
                    <?= $form->select(
                        'pdRepeatPeriodMonthsEvery',
                        $repeatMonths,
                        $pdRepeatPeriodMonthsEvery,
                        array('style' => 'width: 60px')) ?>
                    <?= t('months') ?>
                </div>
            </div>
        </div>

    </div>


    <div id="ccm-permissions-access-entity-dates-repeat-weekly" style="display: none">


        <div id="ccm-permissions-access-entity-dates-repeat-weekly-dow" style="display: none">

            <div class="form-group">
                <label class="control-label"><?= tc('Date', 'On') ?></label>
                <div class="">
                    <?php
                    foreach (\Punic\Calendar::getSortedWeekdays('wide') as $weekDay) {
                        ?>
                        <div class="checkbox"><label><input
                                    <?php if (in_array($weekDay['id'], $pdRepeatPeriodWeekDays)) {
                                    ?>checked="checked" <?php
                                }
                                ?>
                                    type="checkbox" name="pdRepeatPeriodWeeksDays[]"
                                    value="<?= $weekDay['id'] ?>"/> <?= h(
                                    $weekDay['name']) ?></label></div>
                        <?php

                    } ?>
                </div>
            </div>

        </div>

        <div class="form-group">
            <label for="pdRepeatPeriodWeeksEvery" class="control-label"><?= t('Repeat every') ?></label>
            <div class="">
                <div class="form-inline">
                    <?= $form->select(
                        'pdRepeatPeriodWeeksEvery',
                        $repeatWeeks,
                        $pdRepeatPeriodWeeksEvery,
                        array('style' => 'width: 60px')) ?>
                    <?= t('weeks') ?>
                </div>
            </div>
        </div>
    </div>

    <div id="ccm-permissions-access-entity-dates-repeat-dates" style="display: none">


        <div class="form-group">
            <label class="control-label"><?= t('Starts On') ?></label>
            <div class="">
                <input type="text" class="form-control" disabled="disabled" value="" name="pdStartRepeatDate"/>
            </div>
        </div>

        <div class="form-group">
            <label for="pdEndRepeatDate" class="control-label"><?= t('Ends') ?></label>
            <div class="">
                <div class="radio"><label><?= $form->radio('pdEndRepeatDate', '', $pdEndRepeatDate) ?> <?= t(
                            'Never') ?></label></div>
                <div class="radio"><label><?= $form->radio('pdEndRepeatDate', 'date',
                            $pdEndRepeatDate) ?> <?= $dt->date(
                            'pdEndRepeatDateSpecific',
                            $pdEndRepeatDateSpecific) ?></label></div>
            </div>
        </div>

    </div>

</div>

</div>

</div>


<script type="text/javascript">

    ccm_getSelectedStartDate = function() {
        var sdf = ($("#pdStartDate_pub").datepicker('option', 'altFormat'));
        var sdfr = $.datepicker.parseDate(sdf, $("#pdStartDate").val());
        var startTime = $('select[name=pdStartDateSelectTime]').val();
        var sh = startTime.split(/:/gi)[0];
        var sm = startTime.split(/:/gi)[1].replace(/\D/g, '');
        if (startTime.match(/pm/i) && sh < 12) {
            sh = parseInt(sh) + 12;
        }
        return new Date(sdfr.getFullYear(), sdfr.getMonth(), sdfr.getDate(), sh, sm, 0);
    }

    ccm_getSelectedEndDate = function() {
        var edf = ($("#pdEndDate_pub").datepicker('option', 'altFormat'));
        var edfr = $.datepicker.parseDate(edf, $("#pdEndDate").val());
        var endTime = $('select[name=pdEndDateSelectTime]').val();
        if (endTime) {
            var eh = endTime.split(/:/gi)[0];
            var em = endTime.split(/:/gi)[1].replace(/\D/g, '');
            if (endTime.match('/pm/i') && eh < 12) {
                eh = parseInt(eh) + 12;
            }
            return new Date(edfr.getFullYear(), edfr.getMonth(), edfr.getDate(), eh, em, 0);
        }
    }

    ccm_accessEntityCalculateRepeatOptions = function () {

        var startDate = ccm_getSelectedStartDate();
        var endDate = ccm_getSelectedEndDate();

        var difference = ((endDate.getTime() / 1000) - (startDate.getTime() / 1000));

        if (difference >= 60 * 60 * 24) {
            $('select[name=pdRepeatPeriod] option[value=daily]').attr('disabled', true);
            $("#ccm-permissions-access-entity-dates-repeat-weekly-dow").hide();
        } else {
            $('select[name=pdRepeatPeriod] option[value=daily]').attr('disabled', false);
            $("#ccm-permissions-access-entity-dates-repeat-weekly-dow").show();
        }
        $('input[name=pdStartRepeatDate]').val($("#pdStartDate_pub").val());
        switch (startDate.getDay()) {
            case 0:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=0]").attr('checked', true);
                break;
            case 1:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=1]").attr('checked', true);
                break;
            case 2:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=2]").attr('checked', true);
                break;
            case 3:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=3]").attr('checked', true);
                break;
            case 4:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=4]").attr('checked', true);
                break;
            case 5:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=5]").attr('checked', true);
                break;
            case 6:
                $("#ccm-permissions-access-entity-dates-repeat-weekly-dow input[value=6]").attr('checked', true);
                break;
        }

    }

    ccm_accessEntityCheckRepeat = function () {
        if ($('input[name=pdRepeat]').is(':checked')) {
            $("#ccm-permissions-access-entity-repeat-selector").show();
        } else {
            $("#ccm-permissions-access-entity-repeat-selector").hide();
        }
    }

    ccm_accessEntityOnActivateDates = function () {
        ccm_accessEntityCalculateRepeatOptions();

        $("#ccm-permissions-access-entity-repeat").show();
        $('#pdStartDateAllDayActivate').attr('disabled', false);
        $('#pdEndDateAllDayActivate').attr('disabled', false);

        if ($("input[name=pdStartDateAllDayActivate]").is(':checked')) {
            $('div[data-column=start-date]').removeClass().addClass('col-sm-12');
            $('div[data-column=end-date]').removeClass().addClass('col-sm-12');
            $('#pdStartDate_tw').hide();
            $('#pdEndDate_tw').hide();
        } else {
            $('div[data-column=start-date]').removeClass().addClass('col-sm-6');
            $('div[data-column=end-date]').removeClass().addClass('col-sm-6');
            $('#pdStartDate_tw').show();
            $('#pdEndDate_tw').show();
        }

    }

    ccm_accessEntityOnRepeatPeriodChange = function () {
        $("#ccm-permissions-access-entity-dates-repeat-daily").hide();
        $("#ccm-permissions-access-entity-dates-repeat-weekly").hide();
        $("#ccm-permissions-access-entity-dates-repeat-monthly").hide();
        if ($('select[name=pdRepeatPeriod]').val() != '') {
            $("#ccm-permissions-access-entity-dates-repeat-" + $('select[name=pdRepeatPeriod]').val()).show();
            $("#ccm-permissions-access-entity-dates-repeat-dates").show();
        }
    }

    ccm_accessEntityCalculateRepeatEnd = function () {
        if ($('input[name=pdEndRepeatDate]:checked').val() == 'date') {
            $("#ccm-permissions-access-entity-dates-repeat-dates .ccm-input-date-wrapper input").attr('disabled', false);
        } else {
            $("#ccm-permissions-access-entity-dates-repeat-dates .ccm-input-date-wrapper input").attr('disabled', true);
        }
    }

    ccm_durationCalculateEndDate = function() {
        var startDate = ccm_getSelectedStartDate();
        var endDate = startDate;
        var format = $("#pdStartDate_pub").datepicker('option', 'dateFormat');
        endDate.setTime(startDate.getTime() + (1*60*60*1000)); // one hour
        var endDateFormatted = $.datepicker.formatDate(format, endDate);
        var hours = endDate.getHours();
        var pm = 'am';
        var minutes = endDate.getMinutes();
        if (hours == 0) {
            hours = 12;
        }
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        if (hours > 12) {
            hours = hours - 12;
            pm = 'pm';
        }
        var endTime = hours + ':' + minutes + pm;
        $('#pdEndDate_pub').datepicker('setDate', endDateFormatted);

        var $selectize = $('select[name=pdEndDateSelectTime]').selectize();
        $selectize[0].selectize.setValue(endTime);
    }

    $(function () {
        <?php if (!$selectedEndTime) { ?>
            ccm_durationCalculateEndDate();
        <?php } ?>
        $("#ccm-permissions-access-entity-repeat input[type=checkbox]").click(function () {
            ccm_accessEntityOnActivateDates();
        });
        $('#pdStartDate_pub').datepicker({
           onSelect: function() {
               $(this).trigger('change');
           }
        });
        $('#pdStartDate_pub').on('change', function() {
            $('#pdEndDate_pub').datepicker('setDate', $(this).val());
        });
        $("select[name=pdRepeatPeriod]").change(function () {
            ccm_accessEntityOnRepeatPeriodChange();
        });

        $("input[name=pdRepeat]").click(function () {
            ccm_accessEntityCheckRepeat();
        });

        $("#ccm-permissions-access-entity-dates span.ccm-input-date-wrapper input, #ccm-permissions-access-entity-dates span.ccm-input-time-wrapper select").change(function () {
            ccm_accessEntityCalculateRepeatOptions();
        });
        $("#ccm-permissions-access-entity-dates-repeat-dates input.ccm-input-date").attr('disabled', true);
        $('input[name=pdEndRepeatDate]').change(function () {
            ccm_accessEntityCalculateRepeatEnd();
        });
        ccm_accessEntityCalculateRepeatOptions();
        ccm_accessEntityOnActivateDates();
        ccm_accessEntityCheckRepeat();
        ccm_accessEntityOnRepeatPeriodChange();
        ccm_accessEntityCalculateRepeatEnd();
    });
</script>

<style type="text/css">
    #ccm-permissions-access-entity-dates .ccm-activate-date-time {
        margin-right: 8px;
    }
</style>
