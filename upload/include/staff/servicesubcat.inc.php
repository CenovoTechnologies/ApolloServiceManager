<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info = $qs = $forms = array();
/** @var ServiceSubCat $serviceSubCat */
if($serviceSubCat && $_REQUEST['a']!='add') {
    $title=__('Update Sub Category');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$serviceSubCat->getInfo();
    $info['id']=$serviceSubCat->getId();
    $info['pid']=$serviceSubCat->getPid();
    $trans['name'] = $serviceSubCat->getTranslateTag('name');
    $qs += array('id' => $serviceSubCat->getId());
} else {
    $title=__('Add New Sub Category');
    $action='create';
    $submit_text=__('Add Sub Category');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['ispublic']=isset($info['ispublic'])?$info['ispublic']:1;
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="col-sm-12 col-md-12">
    <h2><?php echo $title; ?>
        <?php if (isset($info['service_sub_cat'])) { ?><small>
            — <?php echo $info['service_sub_cat']; ?></small>
        <?php } ?>
    </h2>

    <ul class="nav nav-tabs" id="topic-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab"><i class="icon-info-sign"></i> <?php echo __('Service Sub-Category'); ?></a></li>
    </ul>

    <form action="servicesubcats.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
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
                            <label style="width:100%">Sub Category:
                                <input type="text" class="form-control required" size="30" name="service_sub_cat" value="<?php echo $info['service_sub_cat']; ?>" placeholder="Service Category"
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
                        <label style="width:100%">Service Category:
                            <select name="service_sub_cat_pid" class="form-control required">
                                <option value=""> <?php echo __('Select Service Category...'); ?> </option><?php
                                $categories = ServiceCat::getServiceCategories();
                                while (list($id,$cat) = each($categories)) {
                                    if ($id == $info['service_cat_id'])
                                        continue; ?>
                                    <option value="<?php echo $id; ?>"<?php echo ($info['service_sub_cat_pid']==$id)?'selected':''; ?>><?php echo $cat; ?></option>
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
        </div>
        <div class="col-sm-12 col-md-12">
            <p style="text-align:left; padding-top: 0.5em">
                <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
                <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
                <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="servicesubcats.php"'>
            </p>
        </div>
    </form>
</div>

