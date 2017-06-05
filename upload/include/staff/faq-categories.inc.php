<?php
if(!defined('OSTSTAFFINC') || !$thisstaff) die('Access Denied');

?>
<div class="col-sm-12">
<form id="kbSearch" action="kb.php" method="get">
    <input type="hidden" name="a" value="search">
    <input type="hidden" name="cid" value="<?php echo Format::htmlchars($_REQUEST['cid']); ?>"/>
    <input type="hidden" name="topicId" value="<?php echo Format::htmlchars($_REQUEST['topicId']); ?>"/>

    <div class="has_bottom_border" style="margin-bottom:5px; padding-top:5px;">
        <div class="pull-left">
            <h2><?php echo __('Knowledgebase Articles');?></h2>
        </div>
        <div class="pull-right">
            <span class="action-button muted" data-dropdown="#category-dropdown">
                <i class="icon-caret-down pull-right"></i>
                <span>
                    <i class="fa fa-filter"></i>
                    <?php echo __('Category'); ?>
                </span>
            </span>
            <span class="action-button muted" data-dropdown="#topic-dropdown">
                <i class="icon-caret-down pull-right"></i>
                <span>
                    <i class="fa fa-filter"></i>
                    <?php echo __('Service Template'); ?>
                </span>
            </span>
        </div>
        <div class="clear"></div>
    </div>

        <div id="category-dropdown" class="action-dropdown anchor-right"
            onclick="javascript:
                var form = $(this).closest('form');
                form.find('[name=cid]').val($(event.target).data('cid'));
                form.submit();">
            <ul class="bleed-left">
<?php
$total = FAQ::objects()->count();

$categories = Category::objects()
    ->annotate(array('faq_count' => SqlAggregate::COUNT('faqs')))
    ->filter(array('faq_count__gt' => 0))
    ->order_by('name')
    ->all();
array_unshift($categories, new Category(array('id' => 0, 'name' => __('All Categories'), 'faq_count' => $total)));
foreach ($categories as $C) {
        $active = $_REQUEST['cid'] == $C->getId(); ?>
        <li <?php if ($active) echo 'class="active"'; ?>>
            <a href="#" data-cid="<?php echo $C->getId(); ?>">
                <i class="icon-fixed-width <?php
                if ($active) echo 'icon-hand-right'; ?>"></i>
                <?php echo sprintf('%s (%d)',
                    Format::htmlchars($C->getLocalName()),
                    $C->faq_count); ?>
            </a>
        </li> <?php
} ?></ul>
        </div>

        <div id="topic-dropdown" class="action-dropdown anchor-right"
            onclick="javascript:
                var form = $(this).closest('form');
                form.find('[name=topicId]').val($(event.target).data('topicId'));
                form.submit();">
            <ul class="bleed-left">
<?php
$topics = Topic::objects()
    ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
    ->filter(array('faq_count__gt'=>0))
    ->all();
usort($topics, function($a, $b) {
    return strcmp($a->getFullName(), $b->getFullName());
});
array_unshift($topics, new Topic(array('id' => 0, 'topic' => __('All Topics'), 'faq_count' => $total)));
foreach ($topics as $T) {
        $active = $_REQUEST['topicId'] == $T->getId(); ?>
        <li <?php if ($active) echo 'class="active"'; ?>>
            <a href="#" data-topic-id="<?php echo $T->getId(); ?>">
                <i class="icon-fixed-width <?php
                if ($active) echo 'icon-hand-right'; ?>"></i>
                <?php echo sprintf('%s (%d)',
                    Format::htmlchars($T->getFullName()),
                    $T->faq_count); ?></a>
        </li> <?php
} ?>
            </ul>
        </div>

    <div id="basic_search">
        <label>Search for a knowledgebase article.</label>
        <div class="attached input">
            <input id="query" type="text" size="20" name="q" autofocus
                   value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
            <button class="attached button" id="searchSubmit" type="submit">
                <i class="fa fa-search"></i>
            </button>
        </div>
    </div>

</form>
</div>
<div class="col-sm-12">
<?php
if($_REQUEST['q'] || $_REQUEST['cid'] || $_REQUEST['topicId']) { //Search.
    $faqs = FAQ::objects()
        ->annotate(array(
            'attachment_count'=>SqlAggregate::COUNT('attachments'),
            'topic_count'=>SqlAggregate::COUNT('topics')
        ))
        ->order_by('question');

    if ($_REQUEST['cid'])
        $faqs->filter(array('category_id'=>$_REQUEST['cid']));

    if ($_REQUEST['topicId'])
        $faqs->filter(array('topics__topic_id'=>$_REQUEST['topicId']));

    if ($_REQUEST['q'])
        $faqs->filter(Q::ANY(array(
            'question__contains'=>$_REQUEST['q'],
            'answer__contains'=>$_REQUEST['q'],
            'keywords__contains'=>$_REQUEST['q'],
            'category__name__contains'=>$_REQUEST['q'],
            'category__description__contains'=>$_REQUEST['q'],
        )));

    echo "<div><strong>".__('Search Results')."</strong></div><div class='clear'></div>";
    if ($faqs->exists(true)) {
        echo '<div id="faq">
                <ol>';
        foreach ($faqs as $F) {
            echo sprintf(
                '<li><a href="faq.php?id=%d" class="previewfaq">%s</a> - <span>%s</span></li>',
                $F->getId(), $F->getLocalQuestion(), $F->getVisibilityDescription());
        }
        echo '  </ol>
             </div>';
    } else {
        echo '<strong class="faded">'.__('The search did not match any FAQs.').'</strong>';
    }
} else { //Category Listing.
    $categories = Category::objects()
        ->annotate(array('faq_count'=>SqlAggregate::COUNT('faqs')))
        ->all();

    if (count($categories)) {
        echo '<ul id="kb">';
        foreach ($categories as $C) {
            echo sprintf('
                <li>
                    <a class="btn btn-block text-md-left" href="kb.php?cid=%d">
                        <h3><i class="fa fa-folder-open"></i> %s (%d) - <span class="faded">%s</span></h3>
                        %s
                    </a>
                </li>',$C->getId(),$C->getLocalName(),$C->faq_count,
                $C->getVisibilityDescription(),
                Format::safe_html($C->getLocalDescriptionWithImages())
            );
        }
        echo '</ul>';
    } else {
        echo __('No Article found');
    }
}
?>
</div>
