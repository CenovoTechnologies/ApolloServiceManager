<?php
if (!defined('OSTSCPINC') || !$thisstaff
    || !$thisstaff->hasPerm(FAQ::PERM_MANAGE)
)
    die('Access Denied');

$info = $qs = array();
if ($faq) {
    $title = __('Update Knowledgebase Article');
    $action = 'update';
    $submit_text = __('Save Changes');
    $info = $faq->getHashtable();
    $info['id'] = $faq->getId();
    $info['topics'] = $faq->getHelpTopicsIds();
    $info['answer'] = Format::viewableImages($faq->getAnswer());
    $info['notes'] = Format::viewableImages($faq->getNotes());
    $qs += array('id' => $faq->getId());
    $langs = $cfg->getSecondaryLanguages();
    $translations = $faq->getAllTranslations();
    foreach ($langs as $tag) {
        foreach ($translations as $t) {
            if (strcasecmp($t->lang, $tag) === 0) {
                $trans = $t->getComplex();
                $info['trans'][$tag] = array(
                    'question' => $trans['question'],
                    'answer' => Format::viewableImages($trans['answer']),
                );
                break;
            }
        }
    }
} else {
    $title = __('Add a New Knowledgebase Article');
    $action = 'create';
    $submit_text = __('Add Article');
    if ($category) {
        $qs += array('cid' => $category->getId());
        $info['category_id'] = $category->getId();
    }
}
//TODO: Add attachment support.
$info = Format::htmlchars(($errors && $_POST) ? $_POST : $info);
$qstr = Http::build_query($qs);
?>
<div class="col-sm-12">
    <form action="faq.php?<?php echo $qstr; ?>" method="post" id="save" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="do" value="<?php echo $action; ?>">
        <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
        <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
        <h2 class="has_bottom_border"><?php echo $title; ?></h2>
        <?php if ($info['question']) { ?>
            <div class="faq-title has_bottom_border" style="margin:5px 0 15px"><?php echo $info['question']; ?></div>
        <?php } ?>
        <div class="col-sm-12">
            <div class="row">
                <label style="width:100%;"><?php echo __('Knowledgebase Category'); ?>:
                    <select name="kb_category_id" class="form-control-sm required" id="kb_category_id" type="text" onchange="validateField(this);">
                        <option value=""
                                selected>&mdash; <?php echo __('Select Knowledgebase Category'); ?> &mdash;</option>
                        <?php foreach (Category::objects() as $C) { ?>
                            <option value="<?php echo $C->getId(); ?>" <?php
                            if ($C->getId() == $info['category_id']) echo 'selected="selected"';
                            ?>><?php echo sprintf('%s (%s)',
                                    $C->getName(),
                                    $C->isPublic() ? __('Public') : __('Private')
                                ); ?></option>
                        <?php } ?>
                    </select>
                </label>
            </div>
            <div class="row">
                <label style="width:100%;">Listing Type
                    <select name="ispublished" id="ispublished" class="form-control-sm required" type="text" onchange="validateField(this);">
                        <option value="2" <?php echo $info['ispublished'] == 2 ? 'selected="selected"' : ''; ?>>
                            <?php echo __('Featured (promote to front page)'); ?>
                        </option>
                        <option value="1" <?php echo $info['ispublished'] == 1 ? 'selected="selected"' : ''; ?>>
                            <?php echo __('Public') . ' ' . __('(publish)'); ?>
                        </option>
                        <option value="0" <?php echo !$info['ispublished'] ? 'selected="selected"' : ''; ?>>
                            <?php echo __('Internal') . ' ' . ('(private)'); ?>
                        </option>
                    </select>
                    <i class="help-tip fa fa-question-circle fa-lg" href="#listing_type"></i>
                </label>
            </div>
            <div class="row">
                <?php
                if ($topics = Topic::getAllHelpTopics()) {
                if (!is_array(@$info['topics']))
                    $info['topics'] = array();
                ?>
                <label style="width:100%;"><?php echo __('Service Templates'); ?>
                    <select multiple="multiple" name="topics[]" class="multiselect form-control-sm"
                            data-placeholder="<?php echo __('Service Templates'); ?>"
                            id="help-topic-selection" style="width:350px;">
                        <?php while (list($topicId, $topic) = each($topics)) { ?>
                            <option value="<?php echo $topicId; ?>" <?php
                            if (in_array($topicId, $info['topics'])) echo 'selected="selected"';
                            ?>><?php echo $topic; ?></option>
                        <?php } ?>
                    </select>
                    <script type="text/javascript">
                        $(function () {
                            $("#help-topic-selection").select2();
                        });
                    </script>
                    <?php } ?>
                </label>
            </div>
        </div>

        <div class="spacer"></div>
        <ul class="nav nav-tabs" id=kb_tabs" role="tablist">
            <li class="nav-item"><a id="kb-article-tab" class="nav-link active" data-toggle="tab"
                                    href="#article"><?php echo __('Article Content'); ?></a></li>
            <li class="nav-item"><a id="kb-attachment-tab" class="nav-link" data-toggle="tab"
                                    href="#attachments"><?php echo __('Attachments') . sprintf(' (%d)',
                            $faq ? count($faq->attachments->getSeparates('')) : 0); ?></a></li>
            <li class="nav-item"><a id="kb-notes-tab" class="nav-link" data-toggle="tab"
                                    href="#notes"><?php echo __('Internal Notes'); ?></a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="article" role="tabpanel">
                <div class="spacer"></div>
                <div class="font-weight-bold"><?php echo __('Knowledgebase Article Content'); ?></div>
                <p class="bullet-p"><?php echo __('Here you can manage the question and answer for the article. Multiple languages are available if enabled in the admin panel.'); ?></p>
                <div class="clear"></div>

                <?php
                $langs = Internationalization::getConfiguredSystemLanguages();
                if ($faq && count($langs) > 1) { ?>
                    <ul class="tabs alt clean" id="trans" style="margin-top:10px;">
                        <li class="empty"><i class="icon-globe" title="This content is translatable"></i></li>
                        <?php foreach ($langs as $tag => $i) {
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
                } ?>

                <div id="trans_container">
                    <?php foreach ($langs as $tag => $i) {
                        $code = $i['code'];
                        if ($tag == $cfg->getPrimaryLanguage()) {
                            $namespace = $faq ? $faq->getId() : false;
                            $answer = $info['answer'];
                            $question = $info['question'];
                            $qname = 'question';
                            $aname = 'answer';
                        } else {
                            $namespace = $faq ? $faq->getId() . $code : $code;
                            $answer = $info['trans'][$code]['answer'];
                            $question = $info['trans'][$code]['question'];
                            $qname = 'trans[' . $code . '][question]';
                            $aname = 'trans[' . $code . '][answer]';
                        }
                        ?>
                        <div class="tab_content <?php
                        if ($code != $cfg->getPrimaryLanguage()) echo "hidden";
                        ?>" id="lang-<?php echo $tag; ?>"
                            <?php if ($i['direction'] == 'rtl') echo 'dir="rtl" class="rtl"'; ?>
                        >


                            <div>
                                <label style="width:100%;"><?php echo __('Question'); ?>
                                    <div class="error"><?php echo $errors['question']; ?></div>
                                    <input type="text" size="70" name="<?php echo $qname; ?>"
                                           style="width:100%;box-sizing:border-box;"
                                    <select class="form-control-sm required" onchange="validateField(this);"
                                           value="<?php echo $question; ?>" id="kb-question-name" class="form-control required">
                                </label>
                            </div>
                            <div>
                                <label style="width:100%;"><?php echo __('Answer'); ?>
                                    <div class="error"><?php echo $errors['answer']; ?></div>
                                    <textarea name="<?php echo $aname; ?>" cols="21" rows="12"
                                              data-width="670px" onchange="validateField(this);"
                                              class="richtext draft" <?php
                                    list($draft, $attrs) = Draft::getDraftAndDataAttrs('faq', $namespace, $answer);
                                    echo $attrs; ?>><?php echo $draft ?: $answer;
                                        ?>
                                    </textarea>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="tab-pane" id="attachments" role="tabpanel">
                <div class="spacer"></div>
                <div>
                    <div class="font-weight-bold"><?php echo __('Common Attachments'); ?></div>
                    <p class="bullet-p"><?php echo __(
                            'These attachments are always available, regardless of the language in which the article is rendered'
                        ); ?></p>
                    <div class="error"><?php echo $errors['files']; ?></div>
                </div>
                <div class="col-sm-12">
                <?php
                print $faq_form->getField('attachments')->render(); ?>
                </div>

                <?php if (count($langs) > 1) { ?>
                    <div style="margin-top:15px"></div>
                    <strong><?php echo __('Language-Specific Attachments'); ?></strong>
                    <div><?php echo __(
                            'These attachments are only available when article is rendered in one of the following languages.'
                        ); ?></div>
                    <div class="error"><?php echo $errors['files']; ?></div>
                    <div style="margin-top:15px"></div>

                    <ul class="tabs alt clean">
                        <li class="empty"><i class="icon-globe" title="This content is translatable"></i></li>
                        <?php foreach ($langs as $lang => $i) { ?>
                            <li class="<?php if ($i['code'] == $cfg->getPrimaryLanguage()) echo 'active';
                            ?>"><a href="#attachments-<?php echo $i['code']; ?>">
                                    <span class="flag flag-<?php echo $i['flag']; ?>"></span>
                                </a></li>
                        <?php } ?>
                    </ul>
                    <?php foreach ($langs as $lang => $i) {
                        $code = $i['code']; ?>
                    <div class="tab_content"
                         id="attachments-<?php echo $i['code']; ?>" <?php if ($i['code'] != $cfg->getPrimaryLanguage()) echo 'style="display:none;"'; ?>>
                        <div style="padding:0 0 9px">
                            <strong><?php echo sprintf(__(
                                /* %s is the name of a language */
                                    'Attachments for %s'),
                                    Internationalization::getLanguageDescription($lang));
                                ?></strong>
                        </div>
                        <?php
                        print $faq_form->getField('attachments.' . $code)->render();
                        ?></div><?php
                    }
                } ?>
                <div class="clear"></div>
            </div>

            <div class="tab-pane" role="tabpanel" id="notes">
                <div class="spacer"></div>
                <div>
                    <div class="font-weight-bold"><?php echo __('Internal Notes'); ?></div>
                </div>
                <div class="spacer"></div>
                <label style="width: 100%;">
                    <textarea class="richtext no-bar" name="notes" cols="21"
                              rows="8" style="width: 80%;"><?php echo $info['notes']; ?>
                    </textarea>
                </label>
            </div>
        </div>
        <div class="spacer"></div>
        <p style="text-align:left;">
            <input type="submit" name="submit" class="btn btn-sm btn-outline-primary" value="<?php echo $submit_text; ?>">
            <input type="reset" name="reset" value="<?php echo __('Reset'); ?>" onclick="javascript:
        $(this.form).find('textarea.richtext')
            .redactor('deleteDraft');
        location.reload();"/>
            <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>"
                   onclick='window.location.href="faq.php?<?php echo $qstr; ?>"'>
        </p>
    </form>
</div>