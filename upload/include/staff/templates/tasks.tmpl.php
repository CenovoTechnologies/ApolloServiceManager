<?php
/**
 * Created by PhpStorm.
 * User: melissaSusan
 * Date: 1/4/2017
 * Time: 9:11 PM
 */
global $thisstaff;

$role = $thisstaff->getRole($ticket->getDeptId());

$tasks = Task::objects()
    ->select_related('dept', 'staff', 'team')
    ->order_by('-created');

$tasks->filter(array(
    'object_id' => $ticket->getId(),
    'object_type' => 'T'));

$count = $tasks->count();
$pageNav = new Pagenate($count,1, 100000); //TODO: support ajax based pages
$showing = $pageNav->showing().' '._N('task', 'tasks', $count);

?>
<div id="tasks_content">
    <div class="pull-left">
        <?php
        if ($count) {
            echo '<strong>'.$showing.'</strong>';
        } else {
            echo sprintf(__('%s does not have any tasks'), $ticket? 'This ticket' :
                'System');
        }
        ?>
    </div>
    <div class="pull-right">
        <?php
        if ($role && $role->hasPerm(Task::PERM_CREATE)) { ?>
            <a
                class="green button action-button ticket-task-action"
                data-url="tickets.php?id=<?php echo $ticket->getId(); ?>#tasks"
                data-dialog-config='{"size":"large"}'
                href="#tickets/<?php
                echo $ticket->getId(); ?>/add-task">
                <i class="icon-plus-sign"></i> <?php
                print __('Add New Task'); ?></a>
            <?php
        }
        if ($count)
            Task::getAgentActions($thisstaff, array(
                'container' => '#tasks_content',
                'callback_url' => sprintf('ajax.php/tickets/%d/tasks',
                    $ticket->getId()),
                'morelabel' => __('Options')));
        ?>
    </div>
    <div class="clear"></div>
    <div>
        <?php
        if ($count) { ?>
            <form action="#tickets/<?php echo $ticket->getId(); ?>/tasks" method="POST"
                  name='tasks' id="tasks" style="padding-top:7px;">
                <?php csrf_token(); ?>
                <input type="hidden" name="a" value="mass_process" >
                <input type="hidden" name="do" id="action" value="" >
                <div class="container-fluid">
                    <?php
                    foreach($tasks as $task) {
                    $id = $task->getId();
                    $access = $task->checkStaffPerm($thisstaff);
                    $assigned='';
                    if ($task->staff)
                        $assigned=sprintf('<span class="Icon staffAssigned">%s</span>',
                            $task->staff->getName());

                    $status = $task->isOpen() ? '<strong>Open</strong>': 'Closed';

                    $title = $task->getTitle();
                    $threadcount = $task->getThread() ?
                        $task->getThread()->getNumEntries() : 0;

                    if ($access)
                        $viewhref = sprintf('tasks.php?id=%d', $id);
                    else
                        $viewhref = '#';

                    ?>
                    <div class="row-fluid" style="border-bottom: #555 1px;">
                        <div class="col-md-1">
                            <label>
                                <input class="ckb" type="checkbox" name="tids[]"
                                       value="<?php echo $id; ?>" <?php echo $sel?'checked="checked"':''; ?>/>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <a class="Icon no-pjax preview"
                               title="<?php echo __('Preview Task'); ?>"
                               href="<?php echo $viewhref; ?>"
                               data-preview="#tasks/<?php echo $id; ?>/preview"
                            >Task #<?php echo $task->getNumber(); ?></a>
                        </div>
                        <div class="col-md-4">
                            <?php
                            if ($access) { ?>
                                <a <?php if ($flag) { ?> class="no-pjax"
                                    title="<?php echo ucfirst($flag); ?> Task" <?php } ?>
                                        href="<?php echo $viewhref; ?>"><?php
                                    echo $title; ?></a>
                                <?php
                            } else {
                                echo $title;
                            }
                            if ($threadcount>1)
                                echo "<small>($threadcount)</small>&nbsp;".'<i
                            class="icon-fixed-width icon-comments-alt"></i>&nbsp;';
                            if ($row['collaborators'])
                                echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;';
                            if ($row['attachments'])
                                echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';
                            ?>
                        </div>
                        <div class="col-md-3">
                            <?php echo $status; ?>
                        </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </form>
            <?php
        } ?>
    </div>
</div>
<div id="task_content" style="display:none;">
</div>
<script type="text/javascript">
    $(function() {

        $(document).off('click.taskv');
        $(document).on('click.taskv', 'tbody.tasks a, a#reload-task', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if ($(this).attr('href').length > 1) {
                var url = 'ajax.php/'+$(this).attr('href').substr(1);
                var $container = $('div#task_content');
                var $stop = $('ul#ticket_tabs').offset().top;
                $.pjax({url: url, container: $container, push: false, scrollTo: $stop})
                    .done(
                        function() {
                            $container.show();
                            $('.tip_box').remove();
                            $('div#tasks_content').hide();
                        });
            } else {
                $(this).trigger('mouseenter');
            }

            return false;
        });
        // Ticket Tasks
        $(document).off('.ticket-task-action');
        $(document).on('click.ticket-task-action', 'a.ticket-task-action', function(e) {
            e.preventDefault();
            var url = 'ajax.php/'
                +$(this).attr('href').substr(1)
                +'?_uid='+new Date().getTime();
            var $redirect = $(this).data('href');
            var $options = $(this).data('dialogConfig');
            $.dialog(url, [201], function (xhr) {
                var tid = parseInt(xhr.responseText);
                if (tid) {
                    //var url = 'ajax.php/tickets/'+<?php echo $ticket->getId();?>+'/tasks';
                    var $container = $('div#task_content');
                    $container.load(url+'/'+tid+'/view', function () {
                        $('.tip_box').remove();
                        $('div#tasks_content').hide();
                        $.pjax({url: url, container: '#tasks_content', push: false});
                    }).show();
                } else {
                    window.location.href = $redirect ? $redirect : window.location.href;
                }
            }, $options);
            return false;
        });

        $('#ticket-tasks-count').html(<?php echo $count; ?>);
    });
</script>
