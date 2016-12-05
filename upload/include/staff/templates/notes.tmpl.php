<div id="quick-notes">
<?php
$show_options = true;
foreach ($notes as $note) {
    include STAFFINC_DIR."templates/note.tmpl.php";
} ?>
</div>
<div id="new-note-box">
<div class="quicknote" id="new-note" data-url="<?php echo $create_note_url; ?>">
    <button class="btn btn-outline-primary btn-block">
    <a href="#"><i class="icon-plus icon-large"></i> &nbsp;
    <?php echo __('Click to create a new note'); ?></a>
    </button>
</div>
</div>
