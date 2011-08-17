<h1 id="tl_welcome">syncCto <?php echo SYNCCTO_GET_VERSION; ?> <?php echo $GLOBALS['TL_LANG']['syncCto']['check']; ?></h1>
	
<div id="syncCto_check">
    <h2><?php echo $GLOBALS['TL_LANG']['syncCto']['configuration']; ?></h2>
    <?php echo $this->checkPhpConfiguration; ?> 
	<?php if ($this->safeModeHack): ?>
		<p class="warn"><?php echo $GLOBALS['TL_LANG']['syncCto']['safemodehack']; ?></p>
	<?php endif; ?>

	<h2><?php echo $GLOBALS['TL_LANG']['syncCto']['other_sync_issues']; ?></h2>
	<p><?php echo $GLOBALS['TL_LANG']['syncCto']['explanation_sync_issues']; ?><br /><?php echo $GLOBALS['TL_LANG']['syncCto']['known_issues']; ?></p>
	<ul>
		<li><a onclick="window.open(this.href); return false;" href="http://goo.gl/oj7rv"><?php echo $GLOBALS['TL_LANG']['syncCto']['suhosin']; ?></a></li>
		<li><a onclick="window.open(this.href); return false;" href="http://goo.gl/4T3qa"><?php echo $GLOBALS['TL_LANG']['syncCto']['max_request_len']; ?></a></li>
	</ul>
	
</div>