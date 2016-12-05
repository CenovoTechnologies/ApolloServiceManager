<?php

$info=array();
if ($role) {
    $title = __('Update Role');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $role->getInfo();
    $trans['name'] = $role->getTranslateTag('name');
    $newcount=2;
} else {
    $title = __('Add New Role');
    $action = 'add';
    $submit_text = __('Add Role');
    $newcount=4;
}

$info = Format::htmlchars(($errors && $_POST) ? array_merge($info, $_POST) : $info);

?>
<div class="col-sm-12 col-md-12">
<form action="" method="post" id="save">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="<?php echo $action; ?>">
    <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
    <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
    <h2><?php echo $title; ?>
    <?php if (isset($info['name'])) { ?><small>
    — <?php echo $info['name']; ?></small>
        <?php } ?>
    </h2>
    <ul class="nav nav-tabs">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#definition" role="tab"><i class="icon-file"></i> <?php echo __('Definition'); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#permissions" role="tab"><i class="icon-lock"></i> <?php echo __('Permissions'); ?></a></li>
    </ul>
    <div class="tab-content">
    <div id="definition" class="tab-pane active" role="tabpanel">
        <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
            <thead>
                <tr class="table-heading">
                    <th colspan="2">
                        <?php echo __(
                        'Roles are used to define agents\' permissions'
                        ); ?>&nbsp;<i class="help-tip icon-question-sign"
                        href="#roles"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="required"><?php echo __('Name'); ?>:</td>
                    <td>
                        <input size="50" class="form-control-sm" type="text" name="name" value="<?php echo
                        $info['name']; ?>" data-translate-tag="<?php echo $trans['name']; ?>"
                        autofocus/>
                        <span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr class="table-heading">
                    <th colspan="7">
                        <?php echo __('Internal Notes'); ?>
                    </th>
                </tr>
                <tr>
                    <td colspan="7"><textarea name="notes" class="form-control-sm richtext no-bar"
                        rows="6" cols="80"><?php
                        echo $info['notes']; ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="permissions" class="tab-pane" role="tabpanel">
        <?php
            $setting = $role ? $role->getPermissionInfo() : array();
            // Eliminate groups without any department-specific permissions
            $buckets = array();
            foreach (RolePermission::allPermissions() as $g => $perms) {
                foreach ($perms as $k => $v) {
                if ($v['primary'])
                    continue;
                    $buckets[$g][$k] = $v;
            }
        } ?>
        <ul class="nav nav-tabs">
            <?php
                $first = true;
                foreach ($buckets as $g => $perms) { ?>
                    <li class="nav-item">
                        <a <?php if ($first) { echo 'class="nav-link active"'; $first=false; }else{ echo 'class="nav-link"';} ?> data-toggle="tab" href="#<?php echo Format::slugify($g); ?>" role="tab"><?php echo Format::htmlchars(__($g));?></a>
                    </li>
            <?php } ?>
        </ul>
        <div class="tab-content">
        <?php
        $first = true;
        foreach ($buckets as $g => $perms) { ?>
        <div class="tab-pane <?php if ($first) { echo 'active'; } else { $first = false; }
            ?>" role="tabpanel" id="<?php echo Format::slugify($g); ?>">
            <table class="table table-condensed">
                <?php foreach ($perms as $k => $v) { ?>
                <tr>
                    <td>
                        <label>
                            <?php
                            echo sprintf('<input type="checkbox" name="perms[]" value="%s" %s />',
                            $k, (isset($setting[$k]) && $setting[$k]) ?  'checked="checked"' : ''); ?>
                            &nbsp;
                            <?php echo Format::htmlchars(__($v['title'])); ?>
                            —
                            <?php echo Format::htmlchars(__($v['desc']));
                            ?>
                        </label>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
        <?php } ?>
        </div>
    </div>
    </div>
    <p style="float:left">
        <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>"><?php echo $submit_text; ?></button>
        <input type="reset"  name="reset"  value="<?php echo __('Reset'); ?>">
        <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>"
            onclick='window.location.href="?"'>
    </p>
</form>
</div>