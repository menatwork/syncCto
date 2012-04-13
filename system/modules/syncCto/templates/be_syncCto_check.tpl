<h1 id="tl_welcome">syncCto <?php echo $GLOBALS['SYC_VERSION']; ?> <?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['check']; ?></h1>

<div id="syncCto_check">
    <h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['configuration']; ?></h2>
    <?php echo $this->checkPhpConfiguration; ?> 
    <h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['functions']; ?></h2>
    <?php echo $this->checkPhpFunctions; ?> 
    <?php if ($this->safeModeHack): ?>
        <p class="warn"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['safemodehack']; ?></p>
    <?php endif; ?>

    <h2><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['other_sync_issues']; ?></h2>
    <p><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['explanation_sync_issues']; ?><br /><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['known_issues']; ?></p>
    <ul>
        <li><a <?php echo LINK_NEW_WINDOW; ?> href="http://goo.gl/oj7rv"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['suhosin_issue']; ?></a></li>
        <li><a <?php echo LINK_NEW_WINDOW; ?> href="http://goo.gl/4T3qa"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_check']['mrl_issue']; ?></a></li>
    </ul>

</div>
