<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = $forms = array();
/** @var ServiceCat $serviceCat */
if($serviceCat && $_REQUEST['a']!='add') {
    $title=__('Update Service Category');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$serviceCat->getInfo();
    $info['id']=$serviceCat->getId();
    $info['pid']=$serviceCat->getPid();
    $trans['name'] = $serviceCat->getTranslateTag('name');
    $qs += array('id' => $serviceCat->getId());
    $serviceSubCats = $serviceCat->getServiceSubCats();
} else {
    $title=__('Add New Service Category');
    $action='create';
    $submit_text=__('Add Service Category');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $qs += array('a' => $_REQUEST['a']);
    $serviceSubCats = [];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="col-sm-12 col-md-12">
    <h2><?php echo $title; ?>
        <?php if (isset($info['service_cat'])) { ?><small>
            — <?php echo $info['service_cat']; ?></small>
        <?php } ?>
        <i class="help-tip icon-question-sign" href="#help_topic_information"></i></h2>

    <ul class="nav nav-tabs" id="topic-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab"><i class="icon-info-sign"></i> <?php echo __('Service Category'); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#sub-categories" role="tab"><i class="icon-file-alt"></i> <?php echo __('Sub Categories'); ?></a></li>
    </ul>

    <form action="servicecats.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="<?php echo $action; ?>">
        <input type="hidden" name="a" value="<?php echo $_REQUEST['a']; ?>">
        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

        <div id="service-cat-tabs" class="tab-content">
            <div class="tab-pane active" id="info" role="tabpanel">
                <div class="spacer"></div>
                <div class="col-sm-12 col-md-12">
                    <div class="row">
                        <div class="input-group">
                            <label style="width:100%">Service Category:
                                <input type="text" class="form-control required" size="30" name="service_cat" value="<?php echo $info['service_cat']; ?>" placeholder="Service Category"
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
                        <label style="width:100%">Service:
                            <select name="service_cat_pid" class="form-control required">
                                <option value=""> <?php echo __('Select Service...'); ?> </option><?php
                                $services = Service::getServiceCatalogue();
                                while (list($id,$service) = each($services)) {
                                    if ($id == $info['service_id'])
                                        continue; ?>
                                    <option value="<?php echo $id; ?>"<?php echo ($info['service_cat_pid']==$id)?'selected':''; ?>><?php echo $service; ?></option>
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
            <div class="tab-pane" id="sub-categories" role="tabpanel">
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
                    $current_sub_cats = array();
                    foreach ($serviceSubCats as $S) {
                        $current_sub_cats[] = $S->id;
                        $id = $S['id']; ?>
                        <div class="row" style="border-bottom: #555 1px;">
                            <div class="col-sm-3">
                                <a href="servicesubcats.php?id=<?php echo $id; ?>"><?php
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
                    <?php } ?>
                    <div class="spacer"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-12">
            <p style="text-align:left; padding-top: 0.5em">
                <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
                <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
                <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="servicecats.php"'>
            </p>
        </div>
    </form>
</div>

