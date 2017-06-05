<?php
if(!defined('OSTSTAFFINC') || !$faq || !$thisstaff) die('Access Denied');

$category=$faq->getCategory();

?>
<div class="col-sm-12">
<div class="has_bottom_border" style="padding-top:5px;">
<div class="pull-left"><h2><?php echo __('Knowledgebase Articles');?></h2></div>
<div class="pull-right flush-right">
<?php
$query = array();
parse_str($_SERVER['QUERY_STRING'], $query);
$query['a'] = 'print';
$query['id'] = $faq->getId();
$query = http_build_query($query); ?>
    <a href="faq.php?<?php echo $query; ?>" class="no-pjax action-button">
        <i class="fa fa-print"></i><?php echo __(' Print'); ?>
    </a>
<?php
if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) { ?>
    <a href="faq.php?id=<?php echo $faq->getId(); ?>&a=edit" class="action-button">
        <i class="fa fa-edit"></i><?php echo __(' Edit FAQ'); ?>
    </a>
<?php } ?>
</div><div class="clear"></div>

</div>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="kb.php"><?php echo __('All Categories');?></a></li>
        <li class="breadcrumb-item"><a href="kb.php?cid=<?php echo $category->getId(); ?>"><?php echo $category->getName(); ?></a></li>
        <li class="breadcrumb-item active"><?php echo $faq->getLocalQuestion() ?></li>
    </ol>

<div class="pull-right sidebar faq-meta">
    <div class="card">
        <div class="card-block">
            <h4 class="card-title has_bottom_border">Attachments</h4>
            <?php if ($attachments = $faq->getLocalAttachments()->all()) { ?>
<?php foreach ($attachments as $att) { ?>
            <i class="fa fa-paperclip pull-left"></i>
            <a target="_blank" href="<?php echo $att->file->getDownloadUrl(); ?>"
               class="attachment no-pjax">
                <?php echo Format::htmlchars($att->getFilename()); ?>
            </a>
<?php } ?>
            <?php } else { ?>
                <p class="card-text text-muted font-italic">No other languages</p>
            <?php } ?>
        </div>
    </div>

    <div class="card">
        <div class="card-block">
            <h4 class="card-title has_bottom_border">Service Templates</h4>
            <?php if ($faq->getHelpTopics()->count()) { ?>
            <?php foreach ($faq->getHelpTopics() as $T) { ?>
                <div><?php echo $T->topic->getFullName(); ?></div>
            <?php } ?>
            <?php } else { ?>
                <p class="card-text text-muted font-italic">No service templates</p>
            <?php } ?>
        </div>
    </div>

<?php
$displayLang = $faq->getDisplayLang();
$otherLangs = array();
if ($cfg->getPrimaryLanguage() != $displayLang)
    $otherLangs[] = $cfg->getPrimaryLanguage();
foreach ($faq->getAllTranslations() as $T) {
    if ($T->lang != $displayLang)
        $otherLangs[] = $T->lang;
} ?>
    <div class="card">
        <div class="card-block">
            <h4 class="card-title has_bottom_border"><?php echo __('Other Languages'); ?></h4>
<?php
if ($otherLangs) {
    foreach ($otherLangs as $lang) { ?>
    <div>
        <a href="faq.php?kblang=<?php echo $lang; ?>&id=<?php echo $faq->getId(); ?>">
        <?php echo Internationalization::getLanguageDescription($lang); ?>
        </a>
    </div>
    <?php } ?>
<?php } else { ?>
    <p class="card-text text-muted font-italic">No other languages</p>
<?php } ?>
        </div>
    </div>


    <div class="card">
        <div class="card-block">
            <h4 class="card-title has_bottom_border">Knowledge Article Access</h4>
            <p class="card-text"><?php echo $faq->isPublished()?__('Published'):__('Internal'); ?></p>
            <a data-dialog="ajax.php/kb/faq/<?php echo $faq->getId(); ?>/access" href="#" class="card-link btn-sm btn-primary"><?php echo __('Manage Access'); ?></a>
        </div>
    </div>

</div>

<div class="faq-content">


<div class="faq-title flush-left"><?php echo $faq->getLocalQuestion() ?>
</div>

<div class="faded font-italic"><?php echo __('Last Updated');?>
    <?php echo Format::relativeTime(Misc::db2gmtime($faq->getUpdateDate())); ?>
</div>
<br/>
<div class="thread-body bleed">
<?php echo $faq->getLocalAnswerWithImages(); ?>
</div>

</div>
<div class="clear"></div>
<hr>

<?php
if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) { ?>
<form action="faq.php?id=<?php echo  $faq->getId(); ?>" method="post">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="manage-faq">
    <input type="hidden" name="id" value="<?php echo  $faq->getId(); ?>">
    <button name="a" class="btn btn-sm btn-outline-danger" value="delete"><i class="fa fa-trash"></i><?php echo __(' Delete FAQ'); ?></button>
</form>
<?php }
?>
</div>