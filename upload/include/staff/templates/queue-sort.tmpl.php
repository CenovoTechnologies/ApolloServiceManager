<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle action-button" data-toggle="dropdown" title="<?php echo $sort_options[$sort_cols]; ?>" aria-haspopup="true" aria-expanded="false">
        <i class="icon-sort-by-attributes-alt <?php if ($sort_dir) echo 'icon-flip-vertical'; ?>"></i> <?php echo __('Sort');?>
    </button>
  <ul id="sort-dropdown" class="bleed-left dropdown-menu"
      onclick="javascript:
var query = addSearchParam({'sort': $(event.target).data('mode'), 'dir': $(event.target).data('dir')});
$.pjax({
    url: '?' + query,
    timeout: 2000,
    container: '#pjax-container'});">
    <?php foreach ($queue_sort_options as $mode) {
    $desc = $sort_options[$mode];
    $icon = '';
    $dir = '0';
    $selected = $sort_cols == $mode; ?>
    <li <?php
    if ($selected) {
    echo 'class="active"';
    $dir = ($sort_dir == '1') ? '0' : '1'; // Flip the direction
    $icon = ($sort_dir == '1') ? 'icon-hand-up' : 'icon-hand-down';
    }
    ?>>
        <a href="#" data-mode="<?php echo $mode; ?>" data-dir="<?php echo $dir; ?>">
          <i class="icon-fixed-width <?php echo $icon; ?>"
          ></i> <?php echo Format::htmlchars($desc); ?></a>
      </li>
    <?php } ?>
 </ul>
</div>

