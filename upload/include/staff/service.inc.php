<?php
/** @var Staff $thisstaff */
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
    $info = $qs = $forms = array();
/** @var Service $service */
    if($service && $_REQUEST['a']!='add') {
        $title=__('Update Service');
        $action='update';
        $submit_text=__('Save Changes');
        $info=$service->getInfo();
        $info['id']=$service->getId();
        $info['pid']=$service->getPid();
        $trans['name'] = $service->getTranslateTag('name');
        $qs += array('id' => $service->getId());
        $serviceCats = $service->getServiceCats();
    } else {
        $title=__('Add New Service');
        $action='create';
        $submit_text=__('Add Service');
        $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
        $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
        $qs += array('a' => $_REQUEST['a']);
        $serviceCats = ServiceCat::getAllServiceCategories();
    }
    $info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="col-sm-12 col-md-12">
    <h2><?php echo $title; ?>
        <?php if (isset($info['service'])) { ?><small>
            — <?php echo $info['service']; ?></small>
        <?php } ?>
        <i class="help-tip icon-question-sign" href="#help_topic_information"></i></h2>

    <ul class="nav nav-tabs" id="topic-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab"><i class="icon-info-sign"></i> <?php echo __('Service Information'); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#routing" role="tab"><i class="icon-wrench"></i> <?php echo __('Configuration'); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#categories" role="tab"><i class="icon-file-alt"></i> <?php echo __('Service Categories'); ?></a></li>
    </ul>

    <form action="services.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="<?php echo $action; ?>">
        <input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

        <div id="service-tabs" class="tab-content">
            <div class="tab-pane active" id="info" role="tabpanel">
                <div class="spacer"></div>
                <div class="col-sm-12 col-md-12">
                    <div class="row">
                        <div class="input-group">
                            <label style="width:100%">Service Name:
                                <input type="text" class="form-control required" size="30" name="service" value="<?php echo $info['service']; ?>" placeholder="Service Name"
                                       autofocus data-translate-tag="<?php echo $trans['name']; ?>"/>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label class="form-check-label required" style="padding-left: 0;">Status:
                                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>> <?php echo __('Active'); ?>
                                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>> <?php echo __('Disabled'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label class="form-check-label required" style="padding-left: 0;"> Type:
                                <input type="radio" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>> <?php echo __('Public'); ?>
                                <input type="radio" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>> <?php echo __('Private/Internal');?>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <label style="width:100%">Service Type:
                            <select name="service_pid" class="form-control required">
                                <option value=""> <?php echo __('Select Service Type...'); ?> </option><?php
                                $servicetypes = ServiceType::getAllServiceTypes();
                                while (list($id,$servicetype) = each($servicetypes)) {
                                    if ($id == $info['service_type_id'])
                                        continue; ?>
                                    <option value="<?php echo $id; ?>"<?php echo ($info['service_pid']==$id)?'selected':''; ?>><?php echo $servicetype; ?></option>
                                    <?php
                                } ?>
                            </select>
                        </label>
                    </div>
                    <div class="row">
                        <label style="width:100%">Service Owner:
                            <select name="service_owner" class="form-control required">
                                <option value="0" data-quick-add data-id-prefix="t"> <?php echo __('Select Service Owner...'); ?> </option>
                               <?php $teams = Team::getTeams();
                                   while (list($id,$team) = each($teams)) {
                                        $k="t$id";
                                        $selected = ($info['assign']==$k || $info['team_id']==$id) ? 'selected="selected"' : '';
                                        ?>
                                        <option value="<?php echo $k; ?>"<?php echo $selected; ?>><?php echo $team; ?></option>
                                        <?php
                                    } ?>
                            </select>
                        </label>
                    </div>
                    <div class="d-inline">
                        <h6 class="sidebar-heading" >Additional Notes</h6>
                    </div>
                    <label>
                        <textarea class="form-control richtext no-bar" name="notes" cols="21" rows="8">
                            <?php echo $info['notes']; ?>
                        </textarea>
                    </label>
                </div>
            </div>
            <div class="tab-pane" id="routing" role="tabpanel">
                <div class="spacer"></div>
                <div class="col-sm-12 col-md-12">
                    <div class="d-inline">
                        <h6 class="sidebar-heading" style="margin-left:-15px; margin-right:-15px;">New Ticket Options</h6>
                    </div>
                    <div class="row">
                        <div class="form-check-label" style="padding-left:0;">
                            <label>Ticket Number Format:
                                <input type="radio" name="custom-numbers" value="0" <?php echo !$info['custom-numbers']?'checked="checked"':''; ?>
                                       onchange="$('#custom-numbers').hide();"> <?php echo __('System Default'); ?>
                                <input type="radio" name="custom-numbers" value="1" <?php echo $info['custom-numbers']?'checked="checked"':''; ?>
                                       onchange="$('#custom-numbers').show(200);"> <?php echo __('Custom'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div id="custom-numbers" style="<?php if (!$info['custom-numbers']) echo 'display:none'; ?>">
                            <label style="width:100%;">Number Format:
                                <input type="text" class="form-control"  placeholder="Number Format" name="number_format" value="<?php echo $info['number_format']; ?>"/>
                                <span class="faded"><?php echo __('e.g.'); ?>
                                    <span id="format-example"><?php
                                        if ($info['custom-numbers']) {
                                            if ($info['sequence_id'])
                                                /** @var Sequence $seq */
                                                $seq = Sequence::lookup($info['sequence_id']);
                                            if (!isset($seq))
                                                /** @var RandomSequence $seq */
                                                $seq = new RandomSequence();
                                            echo $seq->current($info['number_format']);
                                        } ?>
                                    </span>
                                </span>
                                <div class="error"><?php echo $errors['number_format']; ?></div>
                            </label>
                            <label style="width:100%">Sequence:
                                <select name="sequence_id" class="form-control-sm">
                                    <option value="0" <?php if ($info['sequence_id'] == 0) echo $selected;
                                    ?>> <?php echo __('Random'); ?> </option>
                                    <?php foreach (Sequence::objects() as $s) { ?>
                                        <option value="<?php echo $s->id; ?>" <?php
                                        if ($info['sequence_id'] == $s->id) echo $selected;
                                        ?>><?php echo $s->name; ?></option>
                                    <?php } ?>
                                </select>
                                <button class="action-button pull-right"
                                        onclick="$.dialog('ajax.php/sequence/manage', 205);
                                                return false;">
                                    <i class="icon-gear"></i> <?php echo __('Manage'); ?>
                                </button>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <label style="width:100%;">Status:
                            <select name="status_id" class="form-control">
                                <option value=""> <?php echo __('System Default'); ?> </option>
                                <?php
                                foreach (TicketStatusList::getStatuses(array('states'=>array('open'))) as $status) {
                                    $name = $status->getName();
                                    if (!($isenabled = $status->isEnabled()))
                                        $name.=' '.__('(disabled)');

                                    echo sprintf('<option value="%d" %s %s>%s</option>',
                                        $status->getId(),
                                        ($info['status_id'] == $status->getId())
                                            ? 'selected="selected"' : '',
                                        $isenabled ? '' : 'disabled="disabled"',
                                        $name
                                    );
                                }
                                ?>
                            </select>
                            <span class="error"><?php echo $errors['status_id']; ?></span>
                        </label>
                    </div>
                    <div class="row">
                        <label style="width:100%;">Priority:
                            <select name="priority_id" class="form-control">
                                <option value=""> <?php echo __('System Default'); ?> </option>
                                <?php
                                if (($priorities=Priority::getPriorities())) {
                                    foreach ($priorities as $id => $name) {
                                        $selected=($info['priority_id'] && $id==$info['priority_id'])?'selected="selected"':'';
                                        echo sprintf('<option value="%d" %s>%s</option>', $id, $selected, $name);
                                    }
                                }
                                ?>
                            </select>
                            &nbsp;<span class="error">&nbsp;<?php echo $errors['priority_id']; ?></span>
                        </label>
                    </div>
                    <div class="row">
                        <label style="width:100%;">SLA Plan:
                            <select name="sla_id" class="form-control">
                                <option value="0"> <?php echo __("Department's Default");?> </option>
                                <?php
                                if($slas=SLA::getSLAs()) {
                                    foreach($slas as $id =>$name) {
                                        echo sprintf('<option value="%d" %s>%s</option>',
                                            $id, ($info['sla_id']==$id)?'selected="selected"':'',$name);
                                    }
                                }
                                ?>
                            </select>
                            &nbsp;<span class="error">&nbsp;<?php echo $errors['sla_id']; ?></span>
                        </label>
                    </div>
                    <!--<div class="row">-->
                    <!--    <label style="width:100%;">Auto-Assignment:
                            <select name="assign" class="form-control" data-quick-add>
                                <option value="0"> <?php /*echo __('Unassigned'); */?> </option>
                                <?php
/*                                if (($users=Staff::getStaffMembers())) {
                                    echo sprintf('<OPTGROUP label="%s">',
                                        sprintf(__('Agents (%d)'), count($users)));
                                    foreach ($users as $id => $name) {
                                        $k="s$id";
                                        $selected = ($info['assign']==$k || $info['staff_id']==$id)?'selected="selected"':'';
                                        */?>
                                        <option value="<?php /*echo $k; */?>"<?php /*echo $selected; */?>><?php /*echo $name; */?></option>

                                        <?php
/*                                    }
                                    echo '</OPTGROUP>';
                                }
                                if ($teams = Team::getTeams()) { */?>
                                    <optgroup data-quick-add="team" label="<?php
/*                                    echo sprintf(__('Teams (%d)'), count($teams)); */?>"><?php
/*                                        foreach ($teams as $id => $name) {
                                            $k="t$id";
                                            $selected = ($info['assign']==$k || $info['team_id']==$id) ? 'selected="selected"' : '';
                                            */?>
                                            <option value="<?php /*echo $k; */?>"<?php /*echo $selected; */?>><?php /*echo $name; */?></option>
                                            <?php
/*                                        } */?>
                                        <option value="0" data-quick-add data-id-prefix="t">— <?php /*echo __('Add New Team'); */?> —</option>
                                    </optgroup>
                                    <?php
/*                                } */?>
                            </select>
                            &nbsp;<span class="error">&nbsp;<?php /*echo $errors['assign']; */?></span>
                        </label>
                    </div>-->
                </div>
            </div>
            <div class="tab-pane" id="categories" role="tabpanel">
                <div class="col-sm-12 col-md-12">
                    <div class="spacer"></div>
                    <div class="row sidebar-heading">
                        <div class="col-sm-3 required">
                            Name
                        </div>
                        <div class="col-sm-1 required">
                            Status
                        </div>
                        <div class="col-sm-1 required">
                            Type
                        </div>
                        <div class="col-sm-7 required">
                            Notes
                        </div>
                    </div>
                    <?php
                    $current_cats = array();
                    /** @var ServiceCat $serviceCats */
                    foreach ($serviceCats as $S => $val) {
                        if ($_REQUEST['a']!='add') {
                            $S = $val;
                            $current_cats[] = $S->id;
                            $id = $S['id']; ?>
                            <div class="row" style="border-bottom: #555 1px;">
                                <div class="col-sm-3">
                                    <a href="servicecats.php?id=<?php echo $id; ?>"><?php
                                        echo $S['category']; ?></a>
                                </div>
                                <div class="col-sm-1">
                                    <?php echo $S['active'] ? __('Active') : __('Disabled'); ?>
                                </div>
                                <div class="col-sm-1">
                                    <?php echo $S['public'] ? __('Public') : __('Private');?>
                                </div>
                                <div class="col-sm-7">
                                    <?php echo $S['notes']; ?>
                                </div>
                            </div>
                    <?php }
                        } ?>
                    <div class="spacer"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-12">
            <p style="text-align:left; padding-top: 0.5em">
                <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
                <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
                <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="services.php"'>
            </p>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function() {
        var request = null,
            update_example = function() {
                request && request.abort();
                request = $.get('ajax.php/sequence/'
                    + $('[name=sequence_id] :selected').val(),
                    {'format': $('[name=number_format]').val()},
                    function(data) { $('#format-example').text(data); }
                );
            };
        $('[name=sequence_id]').on('change', update_example);
        $('[name=number_format]').on('keyup', update_example);

        $('form select#newform').change(function() {
            var $this = $(this),
                val = $this.val();
            if (!val) return;
            $.ajax({
                url: 'ajax.php/form/' + val + '/fields/view',
                dataType: 'json',
                success: function(json) {
                    if (json.success) {
                        $(json.html).appendTo('#topic-forms').effect('highlight');
                        $this.find(':selected').prop('disabled', true);
                    }
                }
            });
        });
    });
</script>
