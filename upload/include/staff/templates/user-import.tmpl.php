<div id="the-lookup-form">
<h3 class="drag-handle"><?php echo $info['title']; ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<hr/>
<?php
if ($info['error']) {
    echo sprintf('<p id="msg_error">%s</p>', $info['error']);
} elseif ($info['warn']) {
    echo sprintf('<p id="msg_warning">%s</p>', $info['warn']);
} elseif ($info['msg']) {
    echo sprintf('<p id="msg_notice">%s</p>', $info['msg']);
} ?>
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#copy-paste" role="tab"
        ><i class="icon-edit"></i>&nbsp;<?php echo __('Copy Paste'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#upload" role="tab"
        ><i class="icon-fixed-width icon-cloud-upload"></i>&nbsp;<?php echo __('Upload'); ?></a></li>
</ul>
<form action="<?php echo $info['action']; ?>" method="post" enctype="multipart/form-data"
    onsubmit="javascript:
    if ($(this).find('[name=import]').val()) {
        $(this).attr('action', '<?php echo $info['upload_url']; ?>');
        $(document).unbind('submit.dialog');
    }">
<?php echo csrf_token();
if ($org_id) { ?>
    <input type="hidden" name="id" value="<?php echo $org_id; ?>"/>
<?php } ?>
<div class="tab-content">
<div id="copy-paste" class="tab-pane fade in active" style="margin:5px;" role="tabpanel">
<h2 style="margin-bottom:10px"><?php echo __('Name and Email'); ?></h2>
<p><?php echo __(
'Enter one name and email address per line.'); ?><br/><em><?php echo __(
'To import more other fields, use the Upload tab.'); ?></em>
</p>
<textarea name="pasted" style="display:block;width:100%;height:8em" class="form-control"
    placeholder="<?php echo __('e.g. John Smith, john.smith@cenovotechnologies.com'); ?>">
<?php echo $info['pasted']; ?>
</textarea>
</div>

<div id="upload" class="tab-pane fade" style="margin:5px;" role="tabpanel">
<h2 style="margin-bottom:10px"><?php echo __('Import a CSV File'); ?></h2>
<p>
<?php echo sprintf(__(
'Use the columns shown in the table below. To add more fields, visit the Admin Panel -&gt; Manage -&gt; Forms -&gt; %s page to edit the available fields.  Only fields with `variable` defined can be imported.'),
    UserForm::getUserForm()->get('title')
); ?>
</p>
<table class="table table-condensed table-bordered">
    <tr class="table-heading">
<?php
    $fields = array();
    $data = array(
        array('name' => __('John Smith'), 'email' => __('john.smith@cenovotechnologies.com'))
    );
    foreach (UserForm::getUserForm()->getFields() as $f)
        if ($f->get('name'))
            $fields[] = $f->get('name');
    foreach ($fields as $f) { ?>
            <th><?php echo mb_convert_case($f, MB_CASE_TITLE); ?></th>
<?php } ?>
    </tr>
<?php
    foreach ($data as $d) {
        foreach ($fields as $f) {
            ?><td><?php
            if (isset($d[$f])) echo $d[$f];
            ?></td><?php
        }
    } ?>
</tr></table>
<br/>
<input type="file" name="import"/>
</div>
</div>
    <hr>
    <p class="full-width">
        <span class="btn-group-sm pull-right">
            <button class="btn btn-default" type="reset" value="<?php echo __('Reset'); ?>">Reset</button>
            <button class="btn btn-outline-primary" type="submit" value="<?php echo __('Import Users'); ?>">Import Users</button>
        </span>
     </p>
</form>
