<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
if(!($maxfileuploads=ini_get('max_file_uploads')))
    $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;
?>
<div class="col-sm-12 col-md-12">
<h2><?php echo __('Ticket Settings and Options');?></h2>
<form action="settings.php?t=tickets" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="tickets" >

<ul class="nav nav-tabs">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#items" role="tab"><i class="icon-asterisk"></i>
        <?php echo __('Settings'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#autoresp" role="tab"><i class="icon-mail-reply-all"></i>
        <?php echo __('Autoresponder'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#alerts" role="tab"><i class="icon-bell-alt"></i>
        <?php echo __('Alerts and Notices'); ?></a></li>
</ul>
    <div class="tab-content">
<div class="tab-pane active" id="items" role="tabpanel">
<table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('System-wide default ticket settings and options.'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="width:25%">
                <?php echo __('Default Ticket Number Format'); ?>:
            </td>
            <td>
                <input type="text" class="form-control-sm" name="ticket_number_format" value="<?php
                echo $config['ticket_number_format']; ?>"/>
                <span class="faded"><?php echo __('e.g.'); ?> <span id="format-example"><?php
                    if ($config['ticket_sequence_id'])
                        $seq = Sequence::lookup($config['ticket_sequence_id']);
                    if (!isset($seq))
                        $seq = new RandomSequence();
                    echo $seq->current($config['ticket_number_format']);
                    ?></span></span>
                <i class="help-tip icon-question-sign" href="#number_format"></i>
                <div class="error"><?php echo $errors['ticket_number_format']; ?></div>
            </td>
        </tr>
        <tr><td><?php echo __('Default Ticket Number Sequence'); ?>:</td>
<?php $selected = 'selected="selected"'; ?>
            <td>
                <select name="ticket_sequence_id" class="form-control-sm">
                <option value="0" <?php if ($config['ticket_sequence_id'] == 0) echo $selected;
                    ?>>&mdash; <?php echo __('Random'); ?> &mdash;</option>
<?php foreach (Sequence::objects() as $s) { ?>
                <option value="<?php echo $s->id; ?>" <?php
                    if ($config['ticket_sequence_id'] == $s->id) echo $selected;
                    ?>><?php echo $s->name; ?></option>
<?php } ?>
                </select>
                <button class="pull-right btn btn-sm btn-secondary" onclick="javascript:
                $.dialog('ajax.php/sequence/manage', 205);
                return false;
                "><i class="icon-gear"></i> <?php echo __('Manage'); ?></button>
                <i class="help-tip icon-question-sign" href="#sequence_id"></i>
            </td>
        </tr>
        <tr>
            <td class="required">
                <?php echo __('Default Status'); ?>:
            </td>
            <td>
                <span>
                <select name="default_ticket_status_id" class="form-control-sm">
                <?php
                $criteria = array('states' => array('open'));
                foreach (TicketStatusList::getStatuses($criteria) as $status) {
                    $name = $status->getName();
                    if (!($isenabled = $status->isEnabled()))
                        $name.=' '.__('(disabled)');

                    echo sprintf('<option value="%d" %s %s>%s</option>',
                            $status->getId(),
                            ($config['default_ticket_status_id'] ==
                             $status->getId() && $isenabled)
                             ? 'selected="selected"' : '',
                             $isenabled ? '' : 'disabled="disabled"',
                             $name
                            );
                }
                ?>
                </select>
                &nbsp;
                <span class="error">*&nbsp;<?php echo $errors['default_ticket_status_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#default_ticket_status"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td class="required"><?php echo __('Default Priority');?>:</td>
            <td>
                <select name="default_priority_id" class="form-control-sm">
                    <?php
                    $priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
                    while (list($id,$tag) = db_fetch_row($priorities)){ ?>
                        <option value="<?php echo $id; ?>"<?php echo ($config['default_priority_id']==$id)?'selected':''; ?>><?php echo $tag; ?></option>
                    <?php
                    } ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_priority_id']; ?></span> <i class="help-tip icon-question-sign" href="#default_priority"></i>
             </td>
        </tr>
        <tr>
            <td class="required">
                <?php echo __('Default SLA');?>:
            </td>
            <td>
                <span>
                <select name="default_sla_id" class="form-control-sm">
                    <option value="0">&mdash; <?php echo __('None');?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id => $name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id,
                                    ($config['default_sla_id'] && $id==$config['default_sla_id'])?'selected="selected"':'',
                                    $name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_sla_id']; ?></span>  <i class="help-tip icon-question-sign" href="#default_sla"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td class="required">
                <?php echo __('Default Auto Close Plan');?>:
            </td>
            <td>
                <span>
                <select name="default_auto_closure_id" class="form-control-sm">
                    <option value="0">&mdash; <?php echo __('None');?> &mdash;</option>
                    <?php
                    $acs=AutoClosure::objects();
                    foreach ($acs as $ac) {
                        if ($ac->isActive()) { ?>
                            <option value="<?php echo $ac->getId(); ?>" <?php echo ($config['default_auto_closure_id'] == $ac->getId()) ? 'selected' : ''; ?>>
                                <?php echo $ac->getName()." (".$ac->getTimePeriod()." hours)"; ?></option>
                            <?php
                        }
                    }?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_auto_closure_id']; ?></span>  <i class="help-tip icon-question-sign" href="#default_auto_close_plan"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Default Service Template'); ?>:</td>
            <td>
                <select name="default_help_topic" class="form-control-sm">
                    <option value="0">&mdash; <?php echo __('None'); ?> &mdash;</option><?php
                    $topics = Topic::getHelpTopics(false, Topic::DISPLAY_DISABLED);
                    while (list($id,$topic) = each($topics)) { ?>
                        <option value="<?php echo $id; ?>"<?php echo ($config['default_help_topic']==$id)?'selected':''; ?>><?php echo $topic; ?></option>
                    <?php
                    } ?>
                </select><br/>
                <span class="error"><?php echo $errors['default_help_topic']; ?></span>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Lock Semantics'); ?>:</td>
            <td>
                <select name="ticket_lock" class="form-control-sm" <?php if ($cfg->getLockTime() == 0) echo 'disabled="disabled"'; ?>>
<?php foreach (array(
    Lock::MODE_DISABLED => __('Disabled'),
    Lock::MODE_ON_VIEW => __('Lock on view'),
    Lock::MODE_ON_ACTIVITY => __('Lock on activity'),
) as $v => $desc) { ?>
                <option value="<?php echo $v; ?>" <?php
                    if ($config['ticket_lock'] == $v) echo 'selected="selected"';
                    ?>><?php echo $desc; ?></option>
<?php } ?>
                </select>
                <div class="error"><?php echo $errors['ticket_lock']; ?></div>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Maximum <b>Open</b> Tickets');?>:</td>
            <td>
                <input type="text" class="form-control-sm" name="max_open_tickets" size=4 value="<?php echo $config['max_open_tickets']; ?>">
                <?php echo __('per end user'); ?>
                <span class="error">*&nbsp;<?php echo $errors['max_open_tickets']; ?></span>
                <i class="help-tip icon-question-sign" href="#maximum_open_tickets"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Human Verification');?>:</td>
            <td>
                <input type="checkbox" name="enable_captcha" <?php echo $config['enable_captcha']?'checked="checked"':''; ?>>
                <?php echo __('Enable CAPTCHA on new web tickets.');?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_captcha']; ?></font>
                &nbsp;<i class="help-tip icon-question-sign" href="#human_verification"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Claim on Response'); ?>:</td>
            <td>
                <input type="checkbox" name="auto_claim_tickets" <?php echo $config['auto_claim_tickets']?'checked="checked"':''; ?>>
                <?php echo __('Enable'); ?>&nbsp;<i class="help-tip icon-question-sign" href="#claim_tickets"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Assigned Tickets');?>:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php
                echo !$config['show_assigned_tickets']?'checked="checked"':''; ?>>
                <?php echo __('Exclude assigned tickets from open queue.'); ?>
                <i class="help-tip icon-question-sign" href="#assigned_tickets"></i>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Answered Tickets');?>:</td>
            <td>
                <input type="checkbox" name="show_answered_tickets" <?php
                echo !$config['show_answered_tickets']?'checked="checked"':''; ?>>
                <?php echo __('Exclude answered tickets from open queue.'); ?>
                <i class="help-tip icon-question-sign" href="#answered_tickets"></i>
            </td>
        </tr>
        <tr class="table-heading">
            <th colspan="2">
                <b><?php echo __('Attachments');?></b>:  <?php echo __('Size and maximum uploads setting mainly apply to web tickets.');?>
            </th>
        </tr>
        <tr>
            <td><?php echo __('Ticket Attachment Settings');?>:</td>
            <td>
<?php
                $tform = TicketForm::objects()->one()->getForm();
                $f = $tform->getField('message');
?>
                <a class="btn btn-sm btn-secondary field-config" style="overflow:inherit"
                    href="#ajax.php/form/field-config/<?php
                        echo $f->get('id'); ?>"
                    onclick="javascript:
                        $.dialog($(this).attr('href').substr(1), [201]);
                        return false;
                    "><i class="icon-edit"></i> <?php echo __('Config'); ?></a>
                <i class="help-tip icon-question-sign" href="#ticket_attachment_settings"></i>
            </td>
        </tr>
    </tbody>
</table>
</div>
<div class="tab-pane" id="autoresp" role="tabpanel" data-tip-namespace="settings.autoresponder">
    <?php include STAFFINC_DIR . 'settings-autoresp.inc.php'; ?>
</div>
<div class="tab-pane" id="alerts" role="tabpanel" data-tip-namespace="settings.alerts">
    <?php include STAFFINC_DIR . 'settings-alerts.inc.php'; ?>
</div>
    </div>
<p style="text-align:left;">
    <button class="btn btn-sm btn-outline-primary" type="submit" name="submit" value="<?php echo __('Save Changes');?>">Save Changes</button>
    <button class="btn btn-sm btn-secondary" type="reset" name="reset" value="<?php echo __('Reset Changes');?>">Reset</button>
</p>
</form>
    </div>
<script type="text/javascript">
$(function() {
    var request = null,
      update_example = function() {
      request && request.abort();
      request = $.get('ajax.php/sequence/'
        + $('[name=ticket_sequence_id] :selected').val(),
        {'format': $('[name=ticket_number_format]').val()},
        function(data) { $('#format-example').text(data); }
      );
    };
    $('[name=ticket_sequence_id]').on('change', update_example);
    $('[name=ticket_number_format]').on('keyup', update_example);
});
</script>
