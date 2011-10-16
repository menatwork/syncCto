<h1 id="tl_welcome">syncCto <?php echo $GLOBALS['SYC_VERSION']; ?> <?php echo $GLOBALS['TL_LANG']['syncCto_check']['check']; ?></h1>

<div id="syncCto_check">
    <h2><?php echo $GLOBALS['TL_LANG']['syncCto_check']['configuration']; ?></h2>
    <?php echo $this->checkPhpConfiguration; ?> 
    <?php if ($this->safeModeHack): ?>
        <p class="warn"><?php echo $GLOBALS['TL_LANG']['syncCto_check']['safemodehack']; ?></p>
    <?php endif; ?>

    <h2><?php echo $GLOBALS['TL_LANG']['syncCto_check']['other_sync_issues']; ?></h2>
    <p><?php echo $GLOBALS['TL_LANG']['syncCto_check']['explanation_sync_issues']; ?><br /><?php echo $GLOBALS['TL_LANG']['syncCto_check']['known_issues']; ?></p>
    <ul>
        <li><a <?php echo LINK_NEW_WINDOW; ?> href="http://goo.gl/oj7rv"><?php echo $GLOBALS['TL_LANG']['syncCto_check']['suhosin']; ?></a></li>
        <li><a <?php echo LINK_NEW_WINDOW; ?> href="http://goo.gl/4T3qa"><?php echo $GLOBALS['TL_LANG']['syncCto_check']['max_request_len']; ?></a></li>
    </ul>

</div>