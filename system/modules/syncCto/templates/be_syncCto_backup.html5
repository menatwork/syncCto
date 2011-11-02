<h1 id="tl_welcome"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['welcome']; ?></h1>

<div id="tl_soverview">

    <?php if (count($_SESSION["TL_ERROR"]) != 0 && $_SESSION["TL_ERROR"] != ""): ?>
        <?php foreach ($_SESSION["TL_ERROR"] as $key => $value): ?>
            <p class="tl_error"><?php echo $value; ?></p>
        <?php endforeach; ?>    
    <?php endif; ?>

    <?php if (count($_SESSION["TL_INFO"]) != 0 && $_SESSION["TL_INFO"] != ""): ?>
        <?php foreach ($_SESSION["TL_INFO"] as $key => $value): ?>
            <p class="tl_info"><?php echo $value; ?></p>
        <?php endforeach; ?>    
    <?php endif; ?>

    <?php if (count($_SESSION["TL_CONFIRM"]) != 0 && $_SESSION["TL_CONFIRM"] != ""): ?>
        <?php foreach ($_SESSION["TL_CONFIRM"] as $key => $value): ?>
            <p class="tl_confirm"><?php echo $value; ?></p>
        <?php endforeach; ?>    
    <?php endif; ?>

    <div id="tl_moverview">
        <h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['title_backup']; ?></h2>
        <div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconBackupDB.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_backup_db" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_backup'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_backup'][1]; ?>
        </div>
        <div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconBackupFile.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_backup_file" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_backup'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_backup'][1]; ?>
        </div>       
    </div>

    <div id="tl_moverview">
        <h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['title_restore']; ?></h2>
        <div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreDB.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_restore_db" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_restore'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['db_restore'][1]; ?>
        </div>
        <div class="tl_module_desc">
            <h3><a style="background-image:url(system/modules/syncCto/html/iconRestoreFile.png);" href="<?php echo $this->Environment->base . $this->script; ?>?do=syncCto_backups&amp;table=tl_syncCto_restore_file" class="navigation"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_restore'][0]; ?></a></h3>
            <?php echo $GLOBALS['TL_LANG']['tl_syncCto_backup']['file_restore'][1]; ?>
        </div>
    </div>

</div>
