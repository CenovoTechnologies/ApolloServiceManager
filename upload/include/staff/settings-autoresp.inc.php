<table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('Global setting - can be disabled at department or email level.'); ?>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td style="width:15%"><?php echo __('New Ticket'); ?>:</td>
            <td>
                <input type="checkbox" name="ticket_autoresponder" <?php
echo $config['ticket_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('Ticket Owner'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('New Ticket by Agent'); ?>:</td>
            <td>
                <input type="checkbox" name="ticket_notice_active" <?php
echo $config['ticket_notice_active'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('Ticket Owner'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket_by_staff"></i>
            </td>
        </tr>
        <tr>
            <td rowspan="2"><?php echo __('New Message'); ?>:</td>
            <td>
                <input type="checkbox" name="message_autoresponder" <?php
echo $config['message_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('Submitter: Send receipt confirmation'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_submitter"></i>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="message_autoresponder_collabs" <?php
echo $config['message_autoresponder_collabs'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('Participants: Send new activity notice'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_participants"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Overlimit Notice'); ?>:</td>
            <td>
                <input type="checkbox" name="overlimit_notice_active" <?php
echo $config['overlimit_notice_active'] ? 'checked="checked"' : ''; ?>/>
                <?php echo __('Ticket Submitter'); ?>&nbsp;
                <i class="help-tip icon-question-sign" href="#overlimit_notice"></i>
            </td>
        </tr>
    </tbody>
</table>
