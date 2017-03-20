<?php
/** @var Task $task */
/** @var Staff $thisstaff */
if (!defined('OSTSCPINC')
    || !$thisstaff || !$task
    || !($role = $thisstaff->getRole($task->getDeptId()))
)
    die('Invalid path');

global $cfg;

$id = $task->getId();
$dept = $task->getDept();
$thread = $task->getThread();

$iscloseable = $task->isCloseable();
$canClose = ($role->hasPerm(TaskModel::PERM_CLOSE) && $iscloseable === true);
$actions = array();

if ($task->isOpen() && $role->hasPerm(Task::PERM_ASSIGN)) {

    if ($task->getStaffId() != $thisstaff->getId()
        && (!$dept->assignMembersOnly()
            || $dept->isMember($thisstaff))
    ) {
        $actions += array(
            'claim' => array(
                'href' => sprintf('#tasks/%d/claim', $task->getId()),
                'icon' => 'icon-user',
                'label' => __('Claim'),
                'redirect' => 'tasks.php'
            ));
    }

    $actions += array(
        'assign/agents' => array(
            'href' => sprintf('#tasks/%d/assign/agents', $task->getId()),
            'icon' => 'icon-user',
            'label' => __('Assign to Agent'),
            'redirect' => 'tasks.php'
        ));

    $actions += array(
        'assign/teams' => array(
            'href' => sprintf('#tasks/%d/assign/teams', $task->getId()),
            'icon' => 'icon-user',
            'label' => __('Assign to Team'),
            'redirect' => 'tasks.php'
        ));
}

if ($role->hasPerm(Task::PERM_TRANSFER)) {
    $actions += array(
        'transfer' => array(
            'href' => sprintf('#tasks/%d/transfer', $task->getId()),
            'icon' => 'icon-share',
            'label' => __('Transfer'),
            'redirect' => 'tasks.php'
        ));
}

$actions += array(
    'print' => array(
        'href' => sprintf('tasks.php?id=%d&a=print', $task->getId()),
        'class' => 'no-pjax',
        'icon' => 'icon-print',
        'label' => __('Print')
    ));

if ($role->hasPerm(Task::PERM_EDIT)) {
    $actions += array(
        'edit' => array(
            'href' => sprintf('#tasks/%d/edit', $task->getId()),
            'icon' => 'icon-edit',
            'dialog' => '{"size":"large"}',
            'label' => __('Edit')
        ));
}

if ($role->hasPerm(Task::PERM_DELETE)) {
    $actions += array(
        'delete' => array(
            'href' => sprintf('#tasks/%d/delete', $task->getId()),
            'icon' => 'icon-trash',
            'class' => 'red button',
            'label' => __('Delete'),
            'redirect' => 'tasks.php'
        ));
}

$info = ($_POST && $errors) ? Format::input($_POST) : array();

if ($task->isOverdue())
    $warn .= '&nbsp;&nbsp;<span class="Icon overdueTicket">' . __('Marked overdue!') . '</span>';

?>
<div class="col-sm-12">
    <div class="sticky bar">
        <div class="content">
            <nav class="navbar navbar-dark nav-no-padding">
                <?php
                if ($ticket) { ?>
                <a id="task-view"
                   target="_blank"
                   class="btn btn-default action-button"
                   href="tasks.php?id=<?php
                   echo $task->getId(); ?>">
                    <i class="icon-share"></i> <?php
                    echo __('View Task'); ?>
                </a>
                <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle action-button" title="<?php echo __('Actions'); ?>"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-reorder"></i> <?php echo __('Actions'); ?>
                    </button>
                    <ul class="dropdown-menu">
                        <?php
                        if ($task->isOpen()) { ?>
                            <li>
                                <a class="no-pjax task-action"
                                   href="#tasks/<?php echo $task->getId(); ?>/reopen"><i
                                            class="icon-fixed-width icon-undo"></i> <?php
                                    echo __('Reopen'); ?> </a>
                            </li>
                            <?php
                        } else {
                            ?>
                            <li>
                                <a class="no-pjax task-action"
                                   href="#tasks/<?php echo $task->getId(); ?>/close"><i
                                            class="icon-fixed-width icon-ok-circle"></i> <?php
                                    echo __('Close'); ?> </a>
                            </li>
                            <?php
                        } ?>
                        <?php
                        foreach ($actions as $a => $action) { ?>
                            <li <?php if ($action['class']) echo sprintf("class='%s'", $action['class']); ?> >
                                <a class="no-pjax task-action" <?php
                                if ($action['dialog'])
                                    echo sprintf("data-dialog-config='%s'", $action['dialog']);
                                if ($action['redirect'])
                                    echo sprintf("data-redirect='%s'", $action['redirect']);
                                ?>
                                   href="<?php echo $action['href']; ?>"
                                    <?php
                                    if (isset($action['href']) &&
                                        $action['href'][0] != '#'
                                    ) {
                                        echo 'target="blank"';
                                    } ?>
                                ><i class="<?php
                                    echo $action['icon'] ?: 'icon-tag'; ?>"></i> <?php
                                    echo $action['label']; ?></a>
                            </li>
                            <?php
                        } ?>
                    </ul>
                </div>
                    <?php
                } else { ?>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown"
                                title="<?php echo __('Change Status'); ?>" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-flag"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <?php
                            if ($task->isClosed()) { ?>
                                <li>
                                    <a class="no-pjax task-action"
                                       href="#tasks/<?php echo $task->getId(); ?>/reopen"><i
                                                class="icon-fixed-width icon-undo"></i> <?php
                                        echo __('Reopen'); ?> </a>
                                </li>
                                <?php
                            } else {
                                ?>
                                <li>
                                    <a class="no-pjax task-action"
                                       href="#tasks/<?php echo $task->getId(); ?>/close"><i
                                                class="icon-fixed-width icon-ok-circle"></i> <?php
                                        echo __('Close'); ?> </a>
                                </li>
                                <?php
                            } ?>
                        </ul>
                    </div>
                    <div class="btn-group">
                        <?php
                        // Assign
                        unset($actions['claim'], $actions['assign/agents'], $actions['assign/teams']);
                        if ($task->isOpen() && $role->hasPerm(Task::PERM_ASSIGN)) { ?>
                            <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown"
                                  title=" <?php echo $task->isAssigned() ? __('Reassign') : __('Assign'); ?>"
                                    aria-haspopup="true" aria-expanded="false">
                                <i class="icon-user"></i>
                            </button>
                                <ul class="dropdown-menu">
                                    <?php
                                    // Agent can claim team assigned ticket
                                    if ($task->getStaffId() != $thisstaff->getId()
                                    && (!$dept->assignMembersOnly()
                                        || $dept->isMember($thisstaff))
                                    ) { ?>
                                    <li><a class="no-pjax task-action"
                                           data-redirect="tasks.php"
                                           href="#tasks/<?php echo $task->getId(); ?>/claim"><i
                                                    class="icon-chevron-sign-down"></i> <?php echo __('Claim'); ?></a>
                                        <?php
                                        } ?>
                                    <li><a class="no-pjax task-action"
                                           data-redirect="tasks.php"
                                           href="#tasks/<?php echo $task->getId(); ?>/assign/agents"><i
                                                    class="icon-user"></i> <?php echo __('Agent'); ?></a>
                                    <li><a class="no-pjax task-action"
                                           data-redirect="tasks.php"
                                           href="#tasks/<?php echo $task->getId(); ?>/assign/teams"><i
                                                    class="icon-group"></i> <?php echo __('Team'); ?></a>
                                </ul>
                            <?php
                        } ?>
                        <?php
                        foreach ($actions as $action) { ?>
                            <span class="action-button <?php echo $action['class'] ?: ''; ?>">
                                <a class="task-action"
                                    <?php
                                    if ($action['dialog'])
                                        echo sprintf("data-dialog-config='%s'", $action['dialog']);
                                    if ($action['redirect'])
                                        echo sprintf("data-redirect='%s'", $action['redirect']);
                                    ?>
                                   href="<?php echo $action['href']; ?>"
                                   data-placement="bottom"
                                   data-toggle="tooltip"
                                   title="<?php echo $action['label']; ?>">
                                    <i class="<?php
                                    echo $action['icon'] ?: 'icon-tag'; ?>"></i>
                                </a>
                            </span>
                            <?php
                        } ?>
                    </div>
                    <?php
                } ?>
            </nav>
        </div>
    </div>
    <div class="col-sm-7 col-md-8">
    <div id="task_thread_container">
        <div id="task_thread_content" class="tab_content">
            <?php
            $task->getThread()->render(array('M', 'R', 'N'),
                array(
                    'mode' => Thread::MODE_STAFF,
                    'container' => 'taskThread',
                    'sort' => $thisstaff->thread_view_order
                )
            );
            ?>
        </div>
    </div>
    <div class="clear"></div>
    <?php if ($errors['err']) { ?>
        <div id="msg_error"><?php echo $errors['err']; ?></div>
    <?php } elseif ($msg) { ?>
        <div id="msg_notice"><?php echo $msg; ?></div>
    <?php } elseif ($warn) { ?>
        <div id="msg_warning"><?php echo $warn; ?></div>
    <?php }

    if ($ticket)
        $action = sprintf('#tickets/%d/tasks/%d',
            $ticket->getId(), $task->getId());
    else
        $action = 'tasks.php?id=' . $task->getId();
    ?>
        <div class="has_bottom_border"></div>
        <div id="task-reply" class="tab-pane card-block" role="tabpanel">
            <div class="col-sm-12 col-md-12">
                <div class="btn-group">
                    <label>
                        <input type="radio" name="convooptions" id="opt1" checked>&nbsp;Reply to Conversation
                    </label>
                    <label>
                        <input type="radio" name="convooptions" id="opt2">&nbsp;Add an Internal Note
                    </label>
                </div>
                <div id="task-reply-div">
                    <?php
                    if ($role->hasPerm(TaskModel::PERM_REPLY)) { ?>
                        <form id="task_reply" class="tab_content spellcheck"
                              action="<?php echo $action; ?>"
                              name="task_reply" method="post" enctype="multipart/form-data">
                            <?php csrf_token(); ?>
                            <input type="hidden" name="id" value="<?php echo $task->getId(); ?>">
                            <input type="hidden" name="a" value="postreply">
                            <input type="hidden" name="lockCode" value="<?php echo ($mylock) ? $mylock->getCode() : ''; ?>">
                            <span class="error"></span>
                            <div class="row">
                                <label>
                                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))
                                            ? 'checked="checked"' : ''; ?>
                                           style="display:<?php echo $thread->getNumCollaborators() ? 'inline-block' : 'none'; ?>;"
                                    >
                                    <?php
                                    $recipients = __('Add Participants');
                                    /** @var Thread $thread */
                                    if ($thread->getNumCollaborators())
                                        $recipients = sprintf(__('Recipients (%d of %d)'),
                                            $thread->getNumActiveCollaborators(),
                                            $thread->getNumCollaborators());

                                    echo sprintf('<span><a class="collaborators preview"
                            href="#thread/%d/collaborators"><span id="t%d-recipients">%s</span></a></span>',
                                        $thread->getId(),
                                        $thread->getId(),
                                        $recipients);
                                    ?>
                                </label>
                            </div>
                            <div class="row">
                                <div class="error"><?php echo $errors['response']; ?></div>
                                <input type="hidden" name="draft_id" value=""/>
                                <textarea name="response" id="task-response" cols="50"
                                          data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                                          data-signature="<?php
                                          echo Format::viewableImages($signature); ?>"
                                          placeholder="<?php echo __('Start writing your update here.'); ?>"
                                          rows="9" wrap="soft"
                                          class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                                          ?> draft draft-delete" <?php
                                list($draft, $attrs) = Draft::getDraftAndDataAttrs('task.response', $task->getId(),
                                    $info['task.response']);
                                echo $attrs; ?>><?php echo $draft ?: $info['task.response'];
                                    ?>
                                </textarea>
                                <div id="task_response_form_attachments" style="margin:10px 12px;" class="attachments">
                                    <?php
                                    if ($reply_attachments_form)
                                        print $reply_attachments_form->getField('attachments')->render();
                                    ?>
                                </div>
                            </div>
                            <div class="row">
                                <label>Status:
                                    <select name="task:status" class="form-control-sm">
                                        <option value="open" <?php
                                        echo $task->isOpen() ?
                                            'selected="selected"' : ''; ?>> <?php
                                            echo __('Open'); ?></option>
                                        <?php
                                        if ($task->isClosed() || $canClose) {
                                            ?>
                                            <option value="closed" <?php
                                            echo $task->isClosed() ?
                                                'selected="selected"' : ''; ?>> <?php
                                                echo __('Closed'); ?></option>
                                            <?php
                                        } ?>
                                    </select>
                                </label>
                                &nbsp;<span class='error'><?php echo
                                    $errors['task:status']; ?></span>
                            </div>
                            <p style="text-align:right;">
                                <input class="btn btn-sm btn-outline-primary" type="submit" value="<?php echo __('Post Update'); ?>">
                                <input class="btn btn-sm btn-outline-secondary" type="reset" value="<?php echo __('Reset'); ?>">
                            </p>
                        </form>
                        <?php
                    } ?>
                </div>
                <div id="task-note-div" style="display:none;">
                    <form id="task_note"
                          action="<?php echo $action; ?>"
                          class="tab_content spellcheck <?php
                          echo $role->hasPerm(TaskModel::PERM_REPLY) ? '' : 'hidden'; ?>"
                          name="task_note"
                          method="post" enctype="multipart/form-data">
                        <?php csrf_token(); ?>
                        <input type="hidden" name="id" value="<?php echo $task->getId(); ?>">
                        <input type="hidden" name="a" value="postnote">
                        <div class="row">
                            <div><span class='error'><?php echo $errors['note']; ?></span></div>
                            <textarea name="note" id="task-note" cols="80"
                                      placeholder="<?php echo __('Internal Note details'); ?>"
                                      rows="9" wrap="soft" data-draft-namespace="task.note"
                                      data-draft-object-id="<?php echo $task->getId(); ?>"
                                      class="richtext ifhtml draft draft-delete"><?php
                                echo $info['note']; ?>
                                    </textarea>
                            <div class="attachments" style="margin:10px 12px;">
                                <?php
                                if ($note_attachments_form)
                                    print $note_attachments_form->getField('attachments')->render();
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <span class="faded"> - </span>
                            <label>
                                <select name="task:status">
                                    <option value="open" <?php
                                    echo $task->isOpen() ?
                                        'selected="selected"' : ''; ?>> <?php
                                        echo __('Open'); ?></option>
                                    <?php
                                    if ($task->isClosed() || $canClose) {
                                        ?>
                                        <option value="closed" <?php
                                        echo $task->isClosed() ?
                                            'selected="selected"' : ''; ?>> <?php
                                            echo __('Closed'); ?></option>
                                        <?php
                                    } ?>
                                </select>
                                <span class='error'><?php echo
                                    $errors['task:status']; ?></span>
                            </label>
                        </div>
                        <p style="text-align:right;">
                            <input class="btn btn-sm btn-outline-primary" type="submit" value="<?php echo __('Post Note'); ?>">
                            <input class="btn btn-sm btn-secondary" type="reset" value="<?php echo __('Reset'); ?>">
                        </p>
                    </form>
                </div>
            </div>
        </div>
    <?php
    echo $reply_attachments_form->getMedia();
    ?>
    </div>
    <?php
    if (!$ticket) { ?>
        <div class="col-sm-5 col-md-4">
            <div class="spacer"></div>
            <div class="d-inline">
                <div class="pull-right flush-right">
                    <h5><?php $title = TaskForm::getInstance()->getField('title');
                        echo $title->display($task->getTitle()); ?></h5>
                </div>
                <div class="flush-left">
                    <h5><a id="reload-task"
                           href="tasks.php?id=<?php echo $task->getId(); ?>">
                            &nbsp;<?php echo sprintf(__('Task #%s'), $task->getNumber()); ?></a>
                    </h5>
                </div>
            </div>
            <h6 class="sidebar-heading">General Information</h6>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Status</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $task->getStatus(); ?></dd>
            </dl>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Date Created</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $task->getCreateDate(); ?></dd>
            </dl>
            <?php
            if ($task->isOpen()) { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle"><?php echo __('Due Date'); ?>:</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo $task->duedate ? $task->duedate : '<span
                        class="faded">&mdash; ' . __('None') . ' &mdash;</span>'; ?></dd>
                </dl>
                <?php
            } else { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle"><?php echo __('Closed'); ?>:</dt>
                    <dd class="col-sm-8 sidebar-detail"><?php echo $task->getCloseDate(); ?></dd>
                </dl>
                <?php
            }
            ?>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Department</dt>
                <dd class="col-sm-8 sidebar-detail"><?php echo $task->dept->getName(); ?></dd>
            </dl>
            <?php
            if ($task->isOpen()) { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle"><?php echo __('Assigned To'); ?>:</dt>
                    <dd class="col-sm-8 sidebar-detail">
                        <?php
                        if ($assigned = $task->getAssigned())
                            echo $assigned;
                        else
                            echo '<span class="faded">&mdash; ' . __('Unassigned') . ' &mdash;</span>';
                        ?>
                    </dd>
                </dl>
                <?php
            } else { ?>
                <dl class="row">
                    <dt class="col-sm-4 sidebar-label align-middle"><?php echo __('Closed By'); ?>:</dt>
                    <dd class="col-sm-8 sidebar-detail">
                        <?php
                        if (($staff = $task->getStaff()))
                            echo $staff->getName();
                        else
                            echo '<span class="faded">&mdash; ' . __('Unknown') . ' &mdash;</span>';
                        ?>
                    </dd>
                </dl>
                <?php
            } ?>
            <dl class="row">
                <dt class="col-sm-4 sidebar-label align-middle">Watchers</dt>
                <dd class="col-sm-8 sidebar-detail"><?php
                    $collaborators = __('Add Watchers');
                    if ($task->getThread()->getNumCollaborators())
                        $collaborators = sprintf(__('Participants (%d)'),
                            $task->getThread()->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                                    href="#thread/%d/collaborators"><span
                                    id="t%d-collaborators">%s</span></a></span>',
                        $task->getThreadId(),
                        $task->getThreadId(),
                        $collaborators);
                    ?>
                </dd>
            </dl>
            <?php
            $idx = 0;
            foreach (DynamicFormEntry::forObject($task->getId(),
                ObjectModel::OBJECT_TYPE_TASK) as $form) {
                $answers = $form->getAnswers()->exclude(Q::any(array(
                    'field__flags__hasbit' => DynamicFormField::FLAG_EXT_STORED,
                    'field__name__in' => array('title')
                )));
                if (!$answers || count($answers) == 0)
                    continue;

                ?>
                <?php foreach ($answers as $a) {
                    if (!($v = $a->display())) continue; ?>
                    <dl class="row">
                        <dt class="col-sm-4 sidebar-label align-middle"><?php
                            echo $a->getField()->get('label');
                            ?>:
                        </dt>
                        <dd class="col-sm-8 sidebar-detail"><?php
                            echo $v;
                            ?>
                        </dd>
                    </dl>
                    <?php
                } ?>
                <?php
                $idx++;
            } ?>
        </div>
        <?php
    } ?>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $('#opt1').click(function() {
        $('#task-reply-div').show();
        $('#task-note-div').hide();
    });
    $('#opt2').click(function() {
        $('#task-reply-div').hide();
        $('#task-note-div').show();
    });
    $(function () {
        $(document).off('.tasks-content');
        $(document).on('click.tasks-content', '#all-ticket-tasks', function (e) {
            e.preventDefault();
            $('div#task_content').hide().empty();
            $('div#tasks_content').show();
            return false;
        });

        $(document).off('.task-action');
        $(document).on('click.task-action', 'a.task-action', function (e) {
            e.preventDefault();
            var url = 'ajax.php/'
                + $(this).attr('href').substr(1)
                + '?_uid=' + new Date().getTime();
            var $options = $(this).data('dialogConfig');
            var $redirect = $(this).data('redirect');
            $.dialog(url, [201], function (xhr) {
                if (!!$redirect)
                    window.location.href = $redirect;
                else
                    $.pjax.reload('#pjax-container');
            }, $options);

            return false;
        });

        $(document).off('.tf');
        $(document).on('submit.tf', '.ticket_task_actions form', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $container = $('div#task_content');
            $.ajax({
                type: $form.attr('method'),
                url: 'ajax.php/' + $form.attr('action').substr(1),
                data: $form.serialize(),
                cache: false,
                success: function (resp, status, xhr) {
                    $container.html(resp);
                    $('#msg_notice, #msg_error', $container)
                        .delay(5000)
                        .slideUp();
                }
            })
                .done(function () {
                })
                .fail(function () {
                });
        });
        <?php
        if ($ticket) { ?>
        $('#ticket-tasks-count').html(<?php echo $ticket->getNumTasks(); ?>);
        <?php
        } ?>
    });
</script>
