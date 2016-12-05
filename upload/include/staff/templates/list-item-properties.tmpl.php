<?php
    $properties_form = $item ? $item->getConfigurationForm($_POST ?: null)
        : $list->getConfigurationForm($_POST ?: null);
    $hasProperties = count($properties_form->getFields()) > 0;
?>
<h3 class="drag-handle"><?php echo $list->getName(); ?> &mdash; <?php
    echo $item ? $item->getValue() : __('Add New List Item'); ?></h3>
<a class="close" href=""><i class="icon-remove-circle"></i></a>
<hr/>

<?php if ($hasProperties) { ?>
<ul class="nav nav-tabs" id="item_tabs">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#value" role="tab"><i class="icon-reorder"></i>
        <?php echo __('Value'); ?></a>
    </li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#item-properties" role="tab"><i class="icon-asterisk"></i>
        <?php echo __('Item Properties'); ?></a>
    </li>
</ul>
<?php } ?>

<form method="post" id="item_tabs_container" action="<?php echo $action; ?>">
    <?php
    echo csrf_token();
    $internal = $item ? $item->isInternal() : false;
?>
<div class="tab-content">
<div class="tab-pane active" role="tabpanel" id="value">
<?php
    $form = $item_form;
    include 'dynamic-form-simple.tmpl.php';
?>
</div>

<div class="tab-pane" role="tabpanel" id="item-properties">
<?php
    if ($hasProperties) {
        $form = $properties_form;
        include 'dynamic-form-simple.tmpl.php';
    }
?>
</div>
</div>
<hr>
<p class="full-width">
    <span class="btn-group-sm pull-right">
        <input type="reset" value="<?php echo __('Reset'); ?>">
        <button type="submit" class="btn btn-outline-primary" value="<?php echo __('Save'); ?>">Save</button>
    </span>
 </p>
</form>

<script type="text/javascript">
   // Make translatable fields translatable
   $('input[data-translate-tag], textarea[data-translate-tag]').translatable();
</script>
