<?php
// If the form was removed using the trashcan option, and there was some
// other validation error, don't render the deleted form the second time
if (isset($options['entry']) && $options['mode'] == 'edit'
    && $_POST
    && ($_POST['forms'] && !in_array($options['entry']->getId(), $_POST['forms']))
)
    return;

if (isset($options['entry']) && $options['mode'] == 'edit') { ?>
<tbody>
<?php }
// Keep up with the entry id in a hidden field to decide what to add and
// delete when the parent form is submitted
if (isset($options['entry']) && $options['mode'] == 'edit') { ?>
    <input type="hidden" name="forms[]" value="<?php
    echo $options['entry']->getId(); ?>" />
<?php }
/** @var DynamicForm $form */
foreach ($form->getFields() as $field) {
    try {
        if (!$field->isEnabled())
            continue;
        if ($options['mode'] == 'edit' && !$field->isEditableToStaff())
            continue;
        if ($field->get('name') != "priority")
            continue;
    }
    catch (Exception $e) {
        // Not connected to a DynamicFormField
    }
    ?>
    <tr>
        <td>
            <?php
            $field->render($options);
            if ($field->isStorable() && ($a = $field->getAnswer()) && $a->isDeleted()) {
                ?><a class="action-button float-right danger overlay" title="Delete this data"
                     href="#delete-answer"
                     onclick="javascript:if (confirm('<?php echo __('You sure?'); ?>'))
                             $.ajax({
                             url: 'ajax.php/form/answer/'
                             +$(this).data('entryId') + '/' + $(this).data('fieldId'),
                             type: 'delete',
                             success: $.proxy(function() {
                             $(this).closest('tr').fadeOut();
                             }, this)
                             });"
                     data-field-id="<?php echo $field->getAnswer()->get('field_id');
                     ?>" data-entry-id="<?php echo $field->getAnswer()->get('entry_id');
                ?>"> <i class="icon-trash"></i> </a><?php
            }
            if ($a && !$a->getValue() && $field->isRequiredForClose()) {
                ?>
                <i class="icon-warning-sign help-tip warning"
                   data-title="<?php echo __('Required to close ticket'); ?>"
                   data-content="<?php echo __('Data is required in this field in order to close the related ticket'); ?>">
                </i>
                <?php
            }
            foreach ($field->errors() as $e) { ?>
                <div class="error"><?php echo $e; ?></div>
            <?php } ?>

        </td>
    </tr>
<?php }
if (isset($options['entry']) && $options['mode'] == 'edit') { ?>
</tbody>
<?php } ?>
