<?php
if (!defined('OSTSCPINC') || !$thisstaff) die('Access Denied');
$info = $qs = array();
if ($canned && $_REQUEST['a'] != 'add') {
    $title = __('Update Canned Response');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $canned->getInfo();
    $info['id'] = $canned->getId();
    $qs += array('id' => $canned->getId());
    // Replace cid: scheme with downloadable URL for inline images
    $info['response'] = $canned->getResponseWithImages();
    $info['notes'] = Format::viewableImages($info['notes']);
} else {
    $title = __('Add New Canned Response');
    $action = 'create';
    $submit_text = __('Add Response');
    $info['isenabled'] = isset($info['isenabled']) ? $info['isenabled'] : 1;
    $qs += array('a' => $_REQUEST['a']);
}
$info = Format::htmlchars(($errors && $_POST) ? $_POST : $info);

?>
<div class="col-sm-12">
    <form action="canned.php?<?php echo Http::build_query($qs); ?>" method="post" id="save"
          enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="<?php echo $action; ?>">
        <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
        <h2 style="margin-left: -15px;"><?php echo $title; ?>
            <?php if (isset($info['title'])) { ?>
                <small>
                — <?php echo $info['title']; ?></small>
            <?php } ?><i class="help-tip icon-question-sign" href="#canned_response"></i>
        </h2>
        <div class="row">
            <div class="form-group">
                <div>Status:</div>
                <label class="form-check-inline required"><input type="radio" class="form-check-input required"
                                                                 name="isenabled" value="1" <?php
                    echo $info['isenabled'] ? 'checked="checked"' : ''; ?>>&nbsp;<?php echo __('Active'); ?>&nbsp;
                </label>
                <label class="form-check-inline required"><input type="radio" class="form-check-input required"
                                                                 name="isenabled" value="0" <?php
                    echo !$info['isenabled'] ? 'checked="checked"' : ''; ?>>&nbsp;<?php echo __('Disabled'); ?>&nbsp;
                </label>
            </div>
        </div>
        <div class="row">
            <label style="width:100%;"> Department:
                <select name="dept_id" class="form-control-sm required" id="dept_id" style="width:100%;"
                        onchange="validateField(this);">
                    <option value="">&mdash; <?php echo __('All Departments'); ?> &mdash;</option>
                    <?php
                    if (($depts = Dept::getDepartments())) {
                        foreach ($depts as $id => $name) {
                            $selected = ($info['dept_id'] && $id == $info['dept_id']) ? 'selected="selected"' : '';
                            echo sprintf('<option value="%d" %s>%s</option>', $id, $selected, $name);
                        }
                    }
                    ?>
                </select>
            </label>
        </div>
        <div class="row">
            <label style="width:100%;"><?php echo __('Title'); ?> <span
                        class="error"><?php echo $errors['title']; ?></span>
                <input type="text" size="70" id="canRespTitle" style="width:100%;" class="form-control-sm required"
                       onchange="validateField(this);" name="title" value="<?php echo $info['title']; ?>">
            </label>
        </div>
        <div class="row">
            <label style="width:100%;">Canned Response:
                <textarea name="response" cols="21" rows="12" style="width:100%;"
                          data-root-context="cannedresponse"
                          class="form-control richtext draft draft-delete required" <?php
                list($draft, $attrs) = Draft::getDraftAndDataAttrs('canned',
                    is_object($canned) ? $canned->getId() : false, $info['response']);
                echo $attrs; ?>><?php echo $draft ?: $info['response']; ?>
        </textarea>
            </label>
            <div style="padding-top: 0.5em"><h5><?php echo __('Attachments'); ?> <?php echo __('(optional)'); ?>
                    &nbsp;<i class="help-tip icon-question-sign" href="#canned_attachments"></i></h5>
                <div class="error"><?php echo $errors['files']; ?></div>
            </div>
            <div class="col-sm-12">
                <?php
                $attachments = $canned_form->getField('attachments');
                if ($canned && $attachments) {
                    $attachments->setAttachments($canned->attachments);
                }
                print $attachments->render(); ?>
            </div>
        </div>
        <div class="row">
            <label style="width:100%;">Additional Notes:
                <textarea class="form-control richtext no-bar" name="notes" cols="21"
                          rows="8" style="width: 100%;"><?php echo $info['notes']; ?>
            </textarea>
            </label>
        </div>
        <?php if ($canned && $canned->getFilters()) { ?>
            <br/>
            <div id="msg_warning"><?php echo __('Canned response is in use by email filter(s)'); ?>: <?php
                echo implode(', ', $canned->getFilters()); ?></div>
        <?php } ?>
        <p style="text-align:left; margin-left:-15px;" class="btn-group-sm">
            <button type="submit" class="btn btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>">
                Submit Response
            </button>
            <button type="reset" class="btn btn-secondary" name="reset" value="<?php echo __('Reset'); ?>" onclick="javascript:
        $(this.form).find('textarea.richtext')
            .redactor('deleteDraft');
        location.reload();">Reset
            </button>
            <button type="button" class="btn btn-secondary" name="cancel" value="<?php echo __('Cancel'); ?>"
                    onclick='window.location.href="canned.php"'>Cancel
            </button>
        </p>
    </form>
</div>
