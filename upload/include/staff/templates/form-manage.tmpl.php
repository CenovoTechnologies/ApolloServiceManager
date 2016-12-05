<h3 class="drag-handle"><i class="icon-paste"></i> <?php echo __('Manage Forms'); ?></i></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<hr/><?php echo __(
'Sort the forms on this ticket by click and dragging on them. Use the box below the forms list to add new forms to the ticket.'
); ?>
<br/>
<br/>
<form method="post" action="<?php echo $info['action']; ?>">
<div id="ticket-entries">
<?php
$current_list = array();
foreach ($forms as $e) { ?>
<div class="sortable row-item" data-id="<?php echo $e->get('id'); ?>">
    <input type="hidden" name="forms[]" value="<?php echo $e->get('form_id'); ?>" />
    <i class="icon-reorder"></i> <?php echo $e->getTitle();
    $current_list[] = $e->get('form_id');
    if ($e->getDynamicForm()->get('type') == 'G') { ?>
    <div class="button-group">
    <div class="delete"><a href="#"><i class="icon-trash"></i></a></div>
    </div>
    <?php } ?>
</div>
<?php } ?>
</div>
<hr/>
<div class="input-group">
<select name="new-form" class="form-control" onchange="javascript:
    $(this).parent().find('button').trigger('click');">
<option selected="selected" disabled="disabled"><?php
    echo __('Add a form'); ?></option>
<?php foreach (DynamicForm::objects()
    ->filter(array('type'=>'G'))
    ->exclude(array('flags__hasbit' => DynamicForm::FLAG_DELETED))
    as $f) {
    if (in_array($f->get('id'), $current_list))
        continue;
    ?><option value="<?php echo $f->get('id'); ?>"><?php
    echo $f->getTitle(); ?></option><?php
} ?>
</select>
<button type="button" class="btn btn-outline-success inline" onclick="javascript:
    var select = $(this).parent().find('select'),
        $sel = select.find('option:selected'),
        id = $sel.val();
    if (!id || !parseInt(id))
        return;
    if ($sel.prop('disabled'))
        return;
    $('#ticket-entries').append($('<div></div>').addClass('sortable row-item')
        .text(' '+$sel.text())
        .data('id', id)
        .prepend($('<i>').addClass('icon-reorder'))
        .append($('<input/>').attr({name:'forms[]', type:'hidden'}).val(id))
        .append($('<div></div>').addClass('button-group')
          .append($('<div></div>').addClass('delete')
            .append($('<a href=\'#\'>')
              .append($('<i>').addClass('icon-trash'))
              .click(function() {
                $sel.prop('disabled',false);
                $(this).closest('div.row-item').remove();
                $('#delete-warning').show();
                return false;
              })
            )
        ))
    );
    $sel.prop('disabled',true);"><i class="icon-plus-sign"></i>
<?php echo __('Add'); ?></button>
</div>

<div id="delete-warning" style="display:none">
<hr>
    <div id="msg_warning"><?php echo __(
    'Clicking <strong>Save Changes</strong> will permanently delete data associated with the deleted forms'
    ); ?>
    </div>
</div>
    <hr>
    <p class="full-width">
        <span class="btn-group-sm pull-right">
            <button type="reset" class="btn btn-default" value="<?php echo __('Reset'); ?>">Reset</button>
            <button type="submit" class="btn btn-outline-primary" value="<?php echo __('Save Changes'); ?>">Save Changes</button>
        </span>
     </p>

<script type="text/javascript">
$(function() {
    $('#ticket-entries').sortable({containment:'parent',tolerance:'pointer'});
});
</script>
