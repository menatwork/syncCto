<div id="tl_buttons">
    <a onclick="Backend.getScrollOffset();" accesskey="b" title="<?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['back']; ?>" class="header_back" href="<?php echo $this->script; ?>?do=synccto_clients"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['back']; ?></a>
</div>

<h2 class="sub_headline"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['edit']; ?> ID <?php echo $this->id; ?></h2>

<div class="tl_formbody_edit">

<p class="info tl_info"><?php echo $GLOBALS['TL_LANG']['syncCto']['sync_info']; ?></p>

<?php foreach ($this->content as $key => $value) : ?>

<div class="<?php echo ($value["step"] == 1) ? "tl_tbox" : "tl_box"; ?> block">
    <h1 id="step<?php echo $value["step"]; ?>"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['step']; ?> <?php echo $value["step"]; ?></h1>
    <p class="tl_help">
        <?php echo $value["desc"]; ?>
        <?php switch ($value["state"]):
            case WORK:
                echo $GLOBALS['TL_LANG']['syncCto']['progress'];
                break;
            case ERROR:
                echo $GLOBALS['TL_LANG']['syncCto']['error'];
                break;
            case SKIPPED:
                echo $GLOBALS['TL_LANG']['syncCto']['skipped'];
                break;
            case OK:
                echo $GLOBALS['TL_LANG']['syncCto']['ok'];
                break;
        endswitch; ?>
    </p>

    <?php if (strlen($value["msg"])) : ?>
    <p class="tl_help"><?php echo $value["msg"]; ?></p>
    <?php endif; ?>
	
    <?php if (strlen($value["compare"])) : ?>
    <?php echo $value["compare"]; ?>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<?php $arrLast = array_pop($this->content) ?>

<?php if ($arrLast["refresh"]): ?>
    <meta http-equiv="refresh" content="1; URL=<?php echo $this->Environment->base; ?>contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_syncTo&amp;act=start&amp;step=<?php echo $this->step + 1 ?>&amp;id=<?php echo $this->id ?>" />
    <img style="margin-bottom:20px;" src="system/modules/syncCto/html/ajax-loader.gif" alt="" />
<?php endif; ?>

<?php if ($arrLast["error"]) : ?>
    <div class="tl_box block">
        <h1><?php echo $GLOBALS['TL_LANG']['syncCto']['error']; ?></h1>
        <p class="tl_help"><?php echo $arrLast["error"]; ?></p>
    </div>
<?php endif; ?>
    
<?php if ($arrLast["finished"]) : ?>
    <div class="tl_box block">
        <h1><?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['complete']; ?></h1>
        <p class="tl_help"><?php echo $GLOBALS['TL_LANG']['tl_syncCto_clients_syncTo']['complete_help']; ?></p>
    </div>
<?php endif; ?>    

<?php if($GLOBALS['TL_CONFIG']['syncCto_debug_filelist'] == true): ?>
<p class="debug tl_help"><?php echo vsprintf($GLOBALS['TL_LANG']['syncCto']['run_time'], array(number_format(microtime(true) - $this->Session->get("syncCto_Start"), 5))); ?><br />
Auslastung: <?php echo round(memory_get_peak_usage(true) / 1048576 , 4); ?> MBytes</p>
<?php endif; ?>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.scrollTo(null, ($('step<?php echo $this->step; ?>').getPosition().y - 20));
//--><!]]>
</script>

</div>