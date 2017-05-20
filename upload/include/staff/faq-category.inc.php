<?php
if (!defined('OSTSTAFFINC') || !$category || !$thisstaff) die('Access Denied');

?>
<div class="has_bottom_border" style="margin-bottom:5px; padding-top:5px;">
    <div class="pull-left">
        <h2><?php echo __('Frequently Asked Questions'); ?></h2>
    </div>
    <?php if ($thisstaff->hasPerm(FAQ::PERM_MANAGE)) {
        echo sprintf('<div class="pull-right flush-right">
    <a class="green action-button" href="faq.php?cid=%d&a=add">' . __('Add New FAQ') . '</a>
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown" title="More">
            <i class="icon-cog"></i> More
        </button>
        <ul class="dropdown-menu" id="action-dropdown-cat-More">
            <li><a class="user-action" href="categories.php?id=%d">
                <i class="icon-pencil icon-fixed-width"></i>'
            . __('Edit Category') . '</a>
            </li>
            <li class="danger">
                <a class="user-action" href="categories.php">
                    <i class="icon-trash icon-fixed-width"></i>'
            . __('Delete Category') . '</a>
            </li>
        </ul>
    </div>
</div>', $category->getId(), $category->getId());
    } else {
        ?><?php
    } ?>
    <div class="clear"></div>

</div>
<div class="faq-category">
    <div style="margin-bottom:5px;">
        <div class="faq-title pull-left"><?php echo $category->getName() ?></div>
        <div class="faq-status inline">(<?php echo $category->isPublic() ? __('Public') : __('Internal'); ?>)</div>
        <div class="clear">
            <time class="faq font-italic"> <?php echo __('Last Updated') . ' ' . Format::daydatetime($category->getUpdateDate()); ?></time>
        </div>
    </div>
    <div class="cat-desc has_bottom_border">
        <?php echo Format::display($category->getDescription()); ?>
    </div>
</div>
<?php


$faqs = $category->faqs
    ->constrain(array('attachments__inline' => 0))
    ->annotate(array('attachments' => SqlAggregate::COUNT('attachments')));
if ($faqs->exists(true)) {
    echo '<div id="faq">
            <ol>';
    foreach ($faqs as $faq) {
        echo sprintf('
            <li><strong><a href="faq.php?id=%d" class="previewfaq">%s <span>- %s</span></a> %s</strong></li>',
            $faq->getId(), $faq->getQuestion(), $faq->isPublished() ? __('Published') : __('Internal'),
            $faq->attachments ? '<i class="icon-paperclip"></i>' : ''
        );
    }
    echo '  </ol>
         </div>';
} else {
    echo '<div class="font-weight-bold font-italic">' . __('Category does not have any knowledgebase articles at this time') . '</div>';
}
?>
</div>
