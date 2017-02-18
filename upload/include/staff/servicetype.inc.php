<?php
/** @var Staff $thisstaff */
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = $forms = array();
/** @var ServiceType $servicetype */
if($servicetype && $_REQUEST['a']!='add') {
    $title=__('Update Service Type');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$servicetype->getInfo();
    $info['id']=$servicetype->getId();
    $trans['name'] = $servicetype->getTranslateTag('name');
    $qs += array('id' => $servicetype->getId());
    $services = $servicetype->getServices();
} else {
    $title=__('Add New Service Type');
    $action='create';
    $submit_text=__('Add Service Type');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $qs += array('a' => $_REQUEST['a']);
    $services = Service::objects();
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="col-sm-12 col-md-12">
    <h2><?php echo $title; ?>
        <?php if (isset($info['service_type'])) { ?><small>
            — <?php echo $info['service_type']; ?></small>
        <?php } ?>
    </h2>

    <ul class="nav nav-tabs" id="service-type-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab"><i class="icon-info-sign"></i> <?php echo __('Service Type Information'); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#services" role="tab"><i class="icon-file-alt"></i> <?php echo __('Services Offered'); ?></a></li>
    </ul>

    <form action="servicetypes.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="<?php echo $action; ?>">
        <input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

        <div class="tab-content">
            <div class="tab-pane active" id="info" role="tabpanel">
                <div class="spacer"></div>
                <div class="col-sm-12 col-md-12">
                    <div class="row">
                        <div class="input-group">
                            <label style="width:100%">Name:
                                <input type="text" class="form-control required" size="30" name="service_type" value="<?php echo $info['service_type']; ?>" placeholder="Service Type"
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
                        <label style="width:100%">Department:
                            <select name="dept_id" class="form-control required" data-quick-add="department">
                                <option value="0"> <?php echo __('Select Department...'); ?> </option>
                                <?php
                                foreach (Dept::getDepartments() as $id=>$name) {
                                    $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                                    echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                                } ?>
                                <option value="0" data-quick-add>&mdash; <?php echo __('Add New');?> &mdash;</option>
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
            <div class="tab-pane" id="services" role="tabpanel">
                <div class="spacer"></div>
                <div class="col-sm-12 col-md-12">
                    <div class="row sidebar-heading">
                        <div class="col-sm-4 required">
                            Name
                        </div>
                        <div class="col-sm-4 required">
                            Team
                        </div>
                        <div class="col-sm-2 required">
                            Status
                        </div>
                        <div class="col-sm-2 required">
                            Type
                        </div>
                    </div>
                    <?php
                    $current_services = array();
                    foreach ($services as $S) {
                        $current_services[] = $S->id;
                        $id = $S['id']; ?>
                    <div class="row" style="border-bottom: #555 1px;">
                        <div class="col-sm-4">
                            <a href="services.php?id=<?php echo $id; ?>"><?php
                                echo $S['service']; ?></a>
                        </div>
                        <div class="col-sm-4">
                            <?php echo Team::getNameById($S['team']); ?>
                        </div>
                        <div class="col-sm-2">
                            <?php echo $S['active'] ? __('Active') : __('Disabled'); ?>
                        </div>
                        <div class="col-sm-2">
                            <?php echo $S['public'] ? __('Public') : __('Private');?>
                        </div>
                    </div>
                    <?php } ?>
                    <div class="spacer"></div>
                    <!--<div class="row">
                        <h6 class="sidebar-heading">Add a Service to this Service Type</h6>
                        <label class="d-inline">
                            <select name="service_id" id="addServices" class="form-control">
                                <option value=""><?php /*echo __('Add Service...'); */?></option>
                                <?php /*foreach (Service::objects() as $S) { */?>
                                    <option value="<?php /*echo $S->get('id'); */?>"
                                        <?php /*if (in_array($S->id, $current_services))
                                            echo 'disabled="disabled"'; */?>
                                        <?php /*if ($S->get('id') == $info['service_id'])
                                            echo 'selected="selected"'; */?>>
                                        <?php /*echo $S->getLocal('service'); */?>
                                    </option>
                                <?php /*} */?>
                            </select>
                        </label>
                    </div>-->
                </div>
                <div class="spacer"></div>
            </div>
        </div>
        <div class="col-sm-12 col-md-12">
            <p style="text-align:left; padding-top: 0.5em">
                <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
                <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
                <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="servicetypes.php"'>
            </p>
        </div>
    </form>
</div>