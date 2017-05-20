<?php
if (!defined('OSTSCPINC') || !$thisstaff
        || !$thisstaff->hasPerm(FAQ::PERM_MANAGE))
    die('Access Denied');

$info=array();
$qs = array();
if($category && $_REQUEST['a']!='add'){
    $title=__('Update Category');
    $action='update';
    $submit_text=__('Save Changes');
    $info=$category->getHashtable();
    $info['id']=$category->getId();
    $info['notes'] = Format::viewableImages($category->getNotes());
    $qs += array('id' => $category->getId());
    $langs = $cfg->getSecondaryLanguages();
    $translations = $category->getAllTranslations();
    foreach ($langs as $tag) {
        foreach ($translations as $t) {
            if (strcasecmp($t->lang, $tag) === 0) {
                $trans = $t->getComplex();
                $info['trans'][$tag] = array(
                    'name' => $trans['name'],
                    'description' => Format::viewableImages($trans['description']),
                );
                break;
            }
        }
    }
}else {
    $title=__('Add New Category');
    $action='create';
    $submit_text=__('Add');
    $qs += array('a' => $_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="categories.php?<?php echo Http::build_query($qs); ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2><?php echo $title; ?>
     <?php if (isset($info['name'])) { ?><small>
    — <?php echo $info['name']; ?></small>
     <?php } ?>
    </h2>


    <div style="margin:8px 0"><?php echo __('Category Type');?>:
    <div class="btn-group" style="margin-left:20px">
        <label class="form-check-inline">
    <input type="radio" class="form-check-input" name="ispublic" value="2" <?php echo $info['ispublic']?'checked="checked"':''; ?>><b><?php echo __('Featured');?></b> <?php echo __('(on front-page sidebar)');?>
        </label>
        <label class="form-check-inline">
    <input type="radio" class="form-check-input" name="ispublic" value="1" <?php echo $info['ispublic']?'checked="checked"':''; ?>><b><?php echo __('Public');?></b> <?php echo __('(publish)');?>
        </label>
        <label class="form-check-inline">
    <input type="radio" class="form-check-input" name="ispublic" value="0" <?php echo !$info['ispublic']?'checked="checked"':''; ?>><b><?php echo __('Private');?></b> <?php echo __('(internal)');?>
        </label>
    <div class="error"><?php echo $errors['ispublic']; ?></div>
    </div>

<div style="margin-top:20px"></div>

<ul class="nav nav-tabs" style="margin-top:9px;" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#info" role="tab"><?php echo __('Category Information'); ?></a></li>
    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#notes" role="tab"><?php echo __('Internal Notes'); ?></a></li>
</ul>

<div class="tab-content">

<?php
$langs = Internationalization::getConfiguredSystemLanguages();
if (count($langs) > 1) { ?>
    <ul class="alt tabs clean" id="trans">
        <li class="empty"><i class="icon-globe" title="This content is translatable"></i></li>
<?php foreach ($langs as $tag=>$i) {
    list($lang, $locale) = explode('_', $tag);
 ?>
    <li class="<?php if ($tag == $cfg->getPrimaryLanguage()) echo "active";
        ?>"><a href="#lang-<?php echo $tag; ?>" title="<?php
        echo Internationalization::getLanguageDescription($tag);
    ?>"><span class="flag flag-<?php echo strtolower($i['flag'] ?: $locale ?: $lang); ?>"></span>
    </a></li>
<?php } ?>
    </ul>
<?php
} foreach ($langs as $tag=>$i) {
    $code = $i['code'];
    $cname = 'name';
    $dname = 'description';
    if ($tag == $cfg->getPrimaryLanguage()) {
        $category = $info[$cname];
        $desc = $info[$dname];
    }
    else {
        $category = $info['trans'][$code][$cname];
        $desc = $info['trans'][$code][$dname];
        $cname = "trans[$code][$cname]";
        $dname = "trans[$code][$dname]";
    } ?>
    <div class="tab-pane card-block active" id="info" role="tabpanel">
        <div class="row">
        <label style="width:100%;"><?php echo __('Category Name');?>:
            <span class="faded font-italic"><?php echo __('Short descriptive name.');?></span>
        <input type="text" class="form-control-sm required" size="70" style="width:100%;" id="catName" onchange="validateField(this);"
            name="<?php echo $cname; ?>" value="<?php echo $category; ?>">
        </label>
        <div class="error"><?php echo $errors['name']; ?></div>
        </div>
        <div class="row">
            <label style="width:100%;"><?php echo __('Category Description');?>:
                <span class="faded font-italic"><?php echo __('Summary of the category.');?></span>
                <textarea class="richtext no-bar form-control required" name="<?php echo $dname; ?>" cols="21" rows="12"
                          style="width:100%;"><?php
                    echo $desc; ?>
                </textarea>
            </label>
            <div class="error"><?php echo $errors['description']; ?></div>
        </div>
    </div>
<?php } ?>

    <div class="tab-pane card-block" id="notes" role="tabpanel" style="padding-top:12px;">
        <b><?php echo __('Internal Notes');?></b>:
        <span class="faded"><em><?php echo __("Be liberal, they're internal");?></em></span>
        <textarea class="richtext no-bar form-control" name="notes" cols="21"
            rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
    </div>
</div>


<p class="btn-group-sm" style="text-align:left; padding-top:12px;">
    <button type="submit" class="btn btn-outline-primary" name="submit" value="<?php echo $submit_text; ?>">Save Changes</button>
    <button type="reset"  class="btn btn-outline-secondary" name="reset"  value="<?php echo __('Reset');?>">Reset</button>
    <button type="button" class="btn btn-outline-secondary" name="cancel" value="<?php echo __('Cancel');?>" onclick='window.location.href="categories.php"'>Cancel</button>
</p>
</form>
