<div id="tl_buttons">
<a onclick="Backend.getScrollOffset();" accesskey="b" title="<?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['back']; ?>" class="header_back" href="<?php echo $this->script; ?>?do=syncCto_backups"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['back']; ?></a>
</div>

<h2 class="sub_headline"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['edit']; ?></h2>

<div class="tl_formbody_edit">
<div class="tl_tbox block">
<h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1']; ?></h1>
<p class="tl_help"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step1_help']; ?> <?php if ($this->condition['1'] == OK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['ok']; elseif ($this->condition['1'] == SKIPPED) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['skipped']; elseif ($this->condition['1'] == WORK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['progress']; else echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['error']; ?></p>
</div>

<?php if ($this->step > 1) : ?>
<div class="tl_box block">
<h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step2']; ?></h1>
<p class="tl_help"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step2_help']; ?> <?php if ($this->condition['2'] == OK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['ok']; elseif ($this->condition['2'] == SKIPPED) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['skipped']; elseif ($this->condition['2'] == WORK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['progress']; else echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['error']; ?>
</p>
</div>
<?php endif; ?>

<?php if ($this->step > 2) : ?>
<div class="tl_box block">
<h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step3']; ?></h1>
<p class="tl_help"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['step3_help']; ?> <?php if ($this->condition['3'] == OK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['ok']; elseif ($this->condition['3'] == SKIPPED) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['skipped']; elseif ($this->condition['3'] == WORK) echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['progress']; else echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['error']; ?></p>
</div>
<?php endif; ?>

<?php if ($this->step > 3) : ?>
<div class="tl_box block">
<h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete']; ?></h1>
<p class="tl_help"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['complete_help']; ?> <?php echo $this->file; ?><br />
<a href="contao/popup.php?src=tl_files/syncCto_backups/files/<?php echo $this->file; ?>&amp;download=1"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['download_backup']; ?></a></p>
</div>
<?php endif; ?>

<?php if ($this->refresh): ?>
<meta http-equiv="refresh" content="3; URL=<?php echo $this->Environment->base; ?>contao/main.php?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=start&amp;step=<?php echo $this->step + 1 ?>" />
<img style="margin-bottom:20px;" src="system/modules/syncCto/html/ajax-loader.gif" alt="" />
<?php endif; ?>

<?php if ($this->error) : ?>
<div class="tl_box block">
<h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup_file']['error']; ?></h1>
<p class="tl_help"><?php echo $this->error; ?></p>
</div>
<?php endif; ?>

</div>
