<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info=$qs= array();
if($rule && $_REQUEST['a']!='add'){
    $title=__('Update Ban Rule');
    $action='update';
    $submit_text=__('Update');
    $info=$rule->getInfo();
    $info['id']=$rule->getId();
    $qs += array('id' => $rule->getId());
}else {
    $title=__('Add New Email Address to Ban List');
    $action='add';
    $submit_text=__('Add');
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qs += array('a' => $_REQUEST['a']);
}

$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<div class="col-sm-12 col-md-12">
<form action="banlist.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">

    <h2><?php echo $title; ?>
    <i class="help-tip icon-question-sign" href="#ban_list"></i>
    </h2>
 <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('Valid email address is required');?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="required">
                <?php echo __('Ban Status'); ?>:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong><?php echo __('Active');?></strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><?php echo __('Disabled');?>
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td class="required">
                <?php echo __('Email Address');?>:
            </td>
            <td>
                <input name="val" class="form-control-sm" type="text" size="24" value="<?php echo $info['val']; ?>">
                 &nbsp;<span class="error">*&nbsp;<?php echo $errors['val']; ?></span>
            </td>
        </tr>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __('Internal Notes');?>: <?php echo __('Admin Notes');?>&nbsp;
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="form-control-sm richtext no-bar" name="notes" cols="21"
                    rows="8"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:left;">
    <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>">Add</button>
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="banlist.php"'>
</p>
</form>
</div>