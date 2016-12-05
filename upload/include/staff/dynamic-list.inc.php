<?php

$info=array();
if ($list) {
    $title = __('Update custom list');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $list->getInfo();
    $trans['name'] = $list->getTranslateTag('name');
    $trans['plural'] = $list->getTranslateTag('plural');
    $newcount=2;
} else {
    $title = __('Add New Custom List');
    $action = 'add';
    $submit_text = __('Add List');
    $newcount=4;
}

$info=Format::htmlchars(($errors && $_POST) ? array_merge($info,$_POST) : $info);

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
<ul class="nav nav-tabs" id="list-tabs" role="tablist">
    <li class="nav-item"><a <?php if (!$list) echo 'class="nav-link active"'; else echo 'class="nav-link"'?> data-toggle="tab" href="#definition" role="tab">
        <i class="icon-plus"></i> <?php echo __('Definition'); ?></a></li>
<?php if ($list) { ?>
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#items" role="tab">
        <i class="icon-list"></i> <?php echo sprintf(__('Items (%d)'), $list->getItems()->count()); ?></a></li>
<?php } ?>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#properties" role="tab">
        <i class="icon-asterisk"></i> <?php echo __('Properties'); ?></a></li>
</ul>
<div id="list-tabs_container" class="tab-content">
<div id="definition" class="tab-pane <?php if (!$list) echo 'active'; ?>" role="tabpanel">
    <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="2">
                <?php echo __(
                'Custom lists are used to provide drop-down lists for custom forms.'
                ); ?>&nbsp;<i class="help-tip icon-question-sign" href="#custom_lists"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="required"><?php echo __('Name'); ?>:</td>
            <td>
                <?php
                if ($list && !$list->isEditable())
                    echo $list->getName();
                else {
                    echo sprintf('<input size="50" type="text" name="name"
                            data-translate-tag="%s" autofocus
                            value="%s"/> <span
                            class="error">*<br/>%s</span>',
                            $trans['name'], $info['name'], $errors['name']);
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Plural Name'); ?>:</td>
            <td>
                <?php
                    if ($list && !$list->isEditable())
                        echo $list->getPluralName();
                    else
                        echo sprintf('<input size="50" type="text"
                                data-translate-tag="%s"
                                name="name_plural" value="%s"/>',
                                $trans['plural'], $info['name_plural']);
                ?>
            </td>
        </tr>
        <tr>
            <td><?php echo __('Sort Order'); ?>:</td>
            <td><select name="sort_mode" class="form-control-sm">
                <?php
                $sortModes = $list ? $list->getSortModes() : DynamicList::getSortModes();
                foreach ($sortModes as $key=>$desc) { ?>
                <option value="<?php echo $key; ?>" <?php
                    if ($key == $info['sort_mode']) echo 'selected="selected"';
                    ?>><?php echo $desc; ?></option>
                <?php } ?>
                </select></td>
        </tr>
    </tbody>
    <tbody>
        <tr class="table-heading">
            <th colspan="7">
                <strong><?php echo __('Internal Notes'); ?>:</strong>
                <?php echo __("Be liberal, they're internal"); ?>
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
<div id="properties" class="tab-pane" role="tabpanel">
    <table class="table table-condensed" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr class="table-heading">
            <th colspan="7">
                <strong><?php echo __('Item Properties'); ?></strong>
                <?php echo __('properties definable for each item'); ?>
            </th>
        </tr>
        <tr class="table-active">
            <th nowrap></th>
            <th nowrap><?php echo __('Label'); ?></th>
            <th nowrap><?php echo __('Type'); ?></th>
            <th nowrap><?php echo __('Visibility'); ?></th>
            <th nowrap><?php echo __('Variable'); ?></th>
            <th nowrap><?php echo __('Delete'); ?></th>
        </tr>
    </thead>
    <tbody class="sortable-rows" data-sort="prop-sort-">
    <?php if ($list && $form=$list->getForm()) foreach ($form->getDynamicFields() as $f) {
        $id = $f->get('id');
        $deletable = !$f->isDeletable() ? 'disabled="disabled"' : '';
        $force_name = $f->isNameForced() ? 'disabled="disabled"' : '';
        $fi = $f->getImpl();
        $ferrors = $f->errors(); ?>
        <tr>
            <td><i class="icon-sort"></i></td>
            <td><input type="text" class="form-control-sm" size="32" name="prop-label-<?php echo $id; ?>"
                data-translate-tag="<?php echo $f->getTranslateTag('label'); ?>"
                value="<?php echo Format::htmlchars($f->get('label')); ?>"/>
                <font class="error"><?php
                    if ($ferrors['label']) echo '<br/>'; echo $ferrors['label']; ?>
            </td>
            <td nowrap><select name="type-<?php echo $id; ?>" <?php
                if (!$fi->isChangeable() || !$f->isChangeable()) echo 'disabled="disabled"'; ?> class="form-control-sm">
                <?php foreach (FormField::allTypes() as $group=>$types) {
                        ?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
                        foreach ($types as $type=>$nfo) {
                            if ($f->get('type') != $type
                                    && isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>" <?php
                    if ($f->get('type') == $type) echo 'selected="selected"'; ?>>
                    <?php echo __($nfo[0]); ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>
            <?php if ($f->isConfigurable()) { ?>
                <button class="btn btn-sm btn-secondary"><a class="field-config"
                    style="overflow:inherit"
                    href="#form/field-config/<?php
                        echo $f->get('id'); ?>"><i
                        class="icon-cog"></i> <?php echo __('Config'); ?></a></button> <?php } ?></td>
            <td>
                <?php echo $f->getVisibilityDescription(); ?></td>
            <td>
                <input type="text" class="form-control-sm" size="20" name="name-<?php echo $id; ?>"
                    value="<?php echo Format::htmlchars($f->get('name'));
                    ?>" <?php echo $force_name ?>/>
                <font class="error"><?php
                    if ($ferrors['name']) echo '<br/>'; echo $ferrors['name'];
                ?></font>
                </td>
            <td>
                <?php
                if (!$f->isDeletable())
                    echo '<i class="icon-ban-circle"></i>';
                else
                    echo sprintf('<input type="checkbox" name="delete-prop-%s">', $id);
                ?>
                <input type="hidden" name="prop-sort-<?php echo $id; ?>"
                    value="<?php echo $f->get('sort'); ?>"/>
                </td>
        </tr>
    <?php
    }
    for ($i=0; $i<$newcount; $i++) { ?>
        <tr>
            <td>+
                <input type="hidden" name="prop-sort-new-<?php echo $i; ?>"
                    value="<?php echo $info["prop-sort-new-$i"]; ?>"/></td>
            <td><input type="text" class="form-control-sm" size="32" name="prop-label-new-<?php echo $i; ?>"
                value="<?php echo $info["prop-label-new-$i"]; ?>"/></td>
            <td><select name="type-new-<?php echo $i; ?>" class="form-control-sm">
                <?php foreach (FormField::allTypes() as $group=>$types) {
                    ?><optgroup label="<?php echo Format::htmlchars(__($group)); ?>"><?php
                    foreach ($types as $type=>$nfo) {
                        if (isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>"
                    <?php if ($info["type-new-$i"] == $type) echo 'selected="selected"'; ?>>
                    <?php echo __($nfo[0]); ?>
                </option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select></td>
            <td></td>
            <td><input type="text" class="form-control-sm" size="20" name="name-new-<?php echo $i; ?>"
                value="<?php echo $info["name-new-$i"]; ?>"/>
                <font class="error"><?php
                    if ($errors["new-$i"]['name']) echo '<br/>'; echo $errors["new-$i"]['name'];
                ?></font>
            <td></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>

<?php if ($list) { ?>
<div id="items" class="tab-pane active" role="tabpanel">
<?php
    $pjax_container = '#items';
    include STAFFINC_DIR . 'templates/list-items.tmpl.php'; ?>
</div>
<?php } ?>

<p style="float:left">
    <button type="submit" class="btn btn-sm btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>">Submit</button>
    <input type="reset" class="btn btn-sm btn-secondary" name="reset"  value="<?php echo __('Reset'); ?>">
    <input type="button" class="btn btn-sm btn-secondary" name="cancel" value="<?php echo __('Cancel'); ?>"
        onclick='window.location.href="?"'>
</p>
</form>
</div>
<script type="text/javascript">
$(function() {
    $('#properties, #items').on('click', 'a.field-config', function(e) {
        e.preventDefault();
        var $id = $(this).attr('id');
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.dialog(url, [201], function (xhr, resp) {
          var json = $.parseJSON(resp);
          if (json && json.success) {
            if (json.row) {
              if (json.id)
                $('#list-item-' + json.id).replaceWith(json.row);
              else
                $('#list-items').append(json.row);
            }
          }
        });
        return false;
    });
    $('#items').on('click', 'a.items-action', function(e) {
        e.preventDefault();
        var ids = [];
        $('form#save :checkbox.mass:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length && confirm(__('You sure?'))) {
            $.ajax({
              url: 'ajax.php/' + $(this).attr('href').substr(1),
              type: 'POST',
              data: {count:ids.length, ids:ids},
              dataType: 'json',
              success: function(json) {
                if (json.success) {
                  if (window.location.search.indexOf('a=items') != -1)
                    $.pjax.reload('#items');
                  else
                    $.pjax.reload('#pjax-container');
                }
              }
            });
        }
        return false;
    });
});
</script>
