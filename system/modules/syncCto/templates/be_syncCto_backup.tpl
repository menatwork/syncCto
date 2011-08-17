<h1 id="tl_welcome"><?php echo $GLOBALS['TL_LANG']['syncCto']['welcome_backup']; ?></h1>

<div id="tl_soverview">

<?php if($this->message != null): ?>
<p class="tl_error"><?php echo $this->message; ?></p>
<?php endif; ?>

<div id="tl_moverview">
<h2><?php echo $GLOBALS['TL_LANG']['syncCto']['title_make_backup']; ?></h2>
<div class="tl_module_desc">
<h3><a style="background-image:url(system/modules/syncCto/html/iconBackupDB.png);" href="<?php echo $this->script; ?>?do=syncCto_backups&table=tl_syncCto_backup_db" class="navigation"><?php echo $GLOBALS['TL_LANG']['syncCto']['db_make_backup'][0]; ?></a></h3>
<?php echo $GLOBALS['TL_LANG']['syncCto']['db_make_backup'][1]; ?>
</div>
<div class="tl_module_desc">
<h3><a style="background-image:url(system/modules/syncCto/html/iconBackupFile.png);" href="<?php echo $this->script; ?>?do=syncCto_backups&table=tl_syncCto_backup_file" class="navigation"><?php echo $GLOBALS['TL_LANG']['syncCto']['file_make_backup'][0]; ?></a></h3>
<?php echo $GLOBALS['TL_LANG']['syncCto']['file_make_backup'][1]; ?>
</div>       
</div>
<div id="tl_moverview">
<h2><?php echo $GLOBALS['TL_LANG']['syncCto']['title_import_backup']; ?></h2>
<div class="tl_module_desc">
<h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreDB.png);" href="<?php echo $this->script; ?>?do=syncCto_backups&table=tl_syncCto_restore_db" class="navigation"><?php echo $GLOBALS['TL_LANG']['syncCto']['db_import_backup'][0]; ?></a></h3>
<?php echo $GLOBALS['TL_LANG']['syncCto']['db_import_backup'][1]; ?>
</div>
<div class="tl_module_desc">
<h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreFile.png);" href="<?php echo $this->script; ?>?do=syncCto_backups&table=tl_syncCto_restore_file" class="navigation"><?php echo $GLOBALS['TL_LANG']['syncCto']['file_import_backup'][0]; ?></a></h3>
<?php echo $GLOBALS['TL_LANG']['syncCto']['file_import_backup'][1]; ?>
</div>
</div>
</div>
