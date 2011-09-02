<h1 id="tl_welcome"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['welcome']; ?></h1>

<div id="tl_soverview">

<?php if($this->message != null): ?>
<p class="tl_error"><?php echo $this->message; ?></p>
<?php endif; ?>

<div id="tl_moverview">
	<h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['title_backup']; ?></h2>
        <div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconBackupDB.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_backup_db&amp;act=edit" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_backup'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_backup'][1]; ?>
	</div>
	<div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconBackupFile.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_backup_file&amp;act=edit" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_backup'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_backup'][1]; ?>
	</div>       
</div>

<div id="tl_moverview">
	<h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['title_restore']; ?></h2>
	<div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreDB.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_restore_db&amp;act=edit" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_restore'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_restore'][1]; ?>
	</div>
	<div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreFile.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_restore_file&amp;act=edit" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_restore'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_restore'][1]; ?>
	</div>
</div>

</div>
