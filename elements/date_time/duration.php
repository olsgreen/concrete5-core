<?php
defined('C5_EXECUTE') or die("Access Denied.");

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

$values = array();
foreach (array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) as $hour) {
    $values[] = $hour . ':00am';
    $values[] = $hour . ':15am';
    $values[] = $hour . ':30am';
    $values[] = $hour . ':45am';
}
foreach (array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) as $hour) {
    $values[] = $hour . ':00pm';
    $values[] = $hour . ':15pm';
    $values[] = $hour . ':30pm';
    $values[] = $hour . ':45pm';
}

$repeats = array(
    '' => t('** Options'),
    'daily' => t('Every Day'),
    'weekly' => t('Every Week'),
    'monthly' => t('Every Month'),
);

$weekDays = \Punic\Calendar::getSortedWeekdays('wide');

?>

<script type="text/template" data-template="duration-wrapper">

    <div class="ccm-date-time-duration-wrapper">

        <a href="javascript:void(0)" data-delete="duration" class="ccm-date-time-duration-delete icon-link"><i class="fa fa-minus-circle"></i></a>

        <input type="hidden" name="repetitionSetID[]" value="<%=setID%>">
        <input type="hidden" name="repetitionID_<%=setID%>" value="<%=repetitionID%>">

        <div class="form-group">
            <div class="row">
                <div class="col-sm-6 ccm-date-time-date-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label"><?= t('From') ?></label> <i
                                class="fa fa-info-circle launch-tooltip"
                                title="<?php echo t('Choose Repeat Event and choose a frequency to make this event recurring.') ?>"></i>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6" data-column="date">
                            <input type="text" class="form-control" name="pdStartDate_pub_<%=setID%>" value="<%=pdStartDate%>">
                            <input type="hidden" class="form-control" name="pdStartDate_<%=setID%>" value="<%=pdStartDate%>">
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" name="pdStartDateSelectTime_<%=setID%>" data-select="start-time">
                                <?php foreach ($values as $value) { ?>
                                    <option value="<?= $value ?>" <% if (pdStartDateSelectTime == '<?=$value?>') { %>selected<% } %>><?= $value ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 ccm-date-time-date-group">
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="control-label"><?= t('To') ?></label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6" data-column="date">
                            <input type="text" class="form-control" name="pdEndDate_pub_<%=setID%>" value="<%=pdEndDate%>">
                            <input type="hidden" class="form-control" name="pdEndDate_<%=setID%>" value="<%=pdEndDate%>">
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control" name="pdEndDateSelectTime_<%=setID%>" data-select="end-time">
                                <?php foreach ($values as $value) { ?>
                                    <option value="<?= $value ?>" <% if (pdEndDateSelectTime == '<?=$value?>') { %>selected<% } %>><?= $value ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
            </div>
        </div>

    </div>

    <div class="form-group-highlight">

        <div data-wrapper="duration-repeat" style="display: none">

            <div class="row">
                <div class="col-sm-3">
                    <label><input name="pdStartDateAllDayActivate_<%=setID%>" <% if (pdStartDateAllDay) { %>checked<% } %> value="1" type="checkbox"> <?= t("All Day") ?></label>
                </div>
                <div class="col-sm-3">
                    <label><input name="pdRepeat_<%=setID%>" value="1" <% if (pdRepeats) { %>checked<% } %> type="checkbox"> <?= t("Repeat Event") ?></label>
                </div>
                <div class="col-sm-6">
                    <div class="pull-right text-muted">
                        <%=timezone.timezone%>
                    </div>
                </div>
            </div>

        </div>

        <div data-wrapper="duration-repeat-selector" style="display: none">

            <br/>

            <div class="form-group">
                <label for="pdRepeatPeriod" class="control-label"><?= t('Repeats') ?></label>
                <div class="">
                    <select class="form-control" name="pdRepeatPeriod_<%=setID%>">
                        <?php foreach ($repeats as $key => $value) { ?>
                            <option value="<?= $key ?>" <% if (pdRepeatPeriod == '<?=$key?>') { %>selected<% } %>><?= $value ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div data-wrapper="duration-dates-repeat-daily" style="display: none">

                <div class="form-group">
                    <label for="pdRepeatPeriodDaysEvery_<%=setID%>" class="control-label"><?= t('Repeat every') ?></label>
                    <div class="">
                        <div class="form-inline">
                            <select class="form-control" style="width: 60px" name="pdRepeatPeriodDaysEvery_<%=setID%>">
                                <?php foreach ($repeatDays as $key => $value) { ?>
                                    <option value="<?= $key ?>" <% if (pdRepeatPeriodDaysEvery == '<?=$key?>') { %>selected<% } %>><?= $value ?></option>
                                <?php } ?>
                            </select>
                            <?= t('days') ?>
                        </div>
                    </div>
                </div>

            </div>

            <div data-wrapper="duration-dates-repeat-monthly" style="display: none">


                <div class="form-group">
                    <label for="pdRepeatPeriodMonthsRepeatBy_<%=setID%>" class="control-label"><?= t('Repeat By') ?></label>
                    <div class="">
                        <div class="radio">
                            <label>
                                <input type="radio" name="pdRepeatPeriodMonthsRepeatBy_<%=setID%>" <% if (pdRepeatPeriodDaysEvery == 'month') { %>checked<% } %> value="month">
                                <?= t('Day of Month')?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="pdRepeatPeriodMonthsRepeatBy_<%setID%>" <% if (pdRepeatPeriodDaysEvery == 'week') { %>checked<% } %> value="week">
                                <?= t('Day of Week')?>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                <input type="radio" name="pdRepeatPeriodMonthsRepeatBy_<%=setID%>" <% if (pdRepeatPeriodDaysEvery == 'lastweekday') { %>checked<% } %> value="lastweekday">
                                <?= t('The last ') ?>
                                <select name="pdRepeatPeriodMonthsRepeatLastDay_<%=setID%>" class="form-control">
                                    <?php foreach($weekDays as $weekDay) { ?>
                                        <option value="<?=$weekDay['id']?>" <% if (pdRepeatPeriodMonthsRepeatLastDay == '<?=$weekDay['id']?>') { %>selected<% } %>><?=h($weekDay['name'])?></option>
                                    <?php } ?>
                                </select>

                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pdRepeatPeriodMonthsEvery_<%=setID%>" class="control-label"><?= t('Repeat every') ?></label>
                    <div class="">
                        <div class="form-inline">
                            <select class="form-control" style="width: 60px" name="pdRepeatPeriodMonthsEvery_<%=setID%>">
                                <?php foreach ($repeatMonths as $key => $value) { ?>
                                    <option value="<?= $key ?>" <% if (pdRepeatPeriodMonthsEvery == '<?=$key?>') { %>selected<% } %>><?= $value ?></option>
                                <?php } ?>
                            </select>
                            <?= t('months') ?>
                        </div>
                    </div>
                </div>

            </div>


            <div data-wrapper="duration-dates-repeat-weekly" style="display: none">


                <div data-wrapper="duration-repeat-weekly-dow" style="display: none">

                    <div class="form-group">
                        <label class="control-label"><?= tc('Date', 'On') ?></label>
                        <div class="">
                            <?php foreach ($weekDays as $weekDay) { ?>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="pdRepeatPeriodWeeksDays_<%=setID%>[]" value="<?=$weekDay['id']?>" <% if (_.contains(pdRepeatPeriodWeekDays, '<?=$weekDay['id']?>')) { %> checked <% } %>> <?=h($weekDay['name'])?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                </div>

                <div class="form-group">
                    <label for="pdRepeatPeriodWeeksEvery_<%=setID%>" class="control-label"><?= t('Repeat every') ?></label>
                    <div class="">
                        <div class="form-inline">
                            <select class="form-control" style="width: 60px" name="pdRepeatPeriodWeeksEvery_<%=setID%>">
                                <?php foreach ($repeatWeeks as $key => $value) { ?>
                                    <option value="<?= $key ?>" <% if (pdRepeatPeriodWeeksEvery == '<?=$key?>') { %>selected<% } %>><?= $value ?></option>
                                <?php } ?>
                            </select>
                            <?= t('weeks') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div data-wrapper="duration-repeat-dates" style="display: none">


                <div class="form-group">
                    <label class="control-label"><?= t('Starts On') ?></label>
                    <div class="">
                        <input type="text" class="form-control" disabled="disabled" value="" name="pdStartRepeatDate_<%=setID%>"/>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pdEndRepeatDate" class="control-label"><?= t('Ends') ?></label>
                    <div class="">
                        <div class="radio">
                            <label>
                                <input type="radio" name="pdEndRepeatDate_<%=setID%>" value="" <% if (!pdEndRepeatDate) { %>checked <% } %>> <?=t('Never') ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="pdEndRepeatDate_<%=setID%>" value="date" <% if (pdEndRepeatDate == 'date') { %>checked <% } %>>
                                <input type="text" class="form-control" name="pdEndRepeatDateSpecific_pub_<%=setID%>" value="<%=pdEndRepeatDateSpecific%>">
                                <input type="hidden" class="form-control" name="pdEndRepeatDateSpecific_<%=setID%>" value="<%=pdEndRepeatDateSpecific%>">
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <hr/>

    </div>

</script>
