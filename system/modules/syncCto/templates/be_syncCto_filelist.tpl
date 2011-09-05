<h1 class="file"><strong><?php echo $GLOBALS['TL_LANG']['syncCto']['size']; ?></strong>~ <?php echo $this->getReadableSize($this->totalsize); ?></h1>

<form id="syncCto_filelist_form" action="<?php echo $this->Environment->base . $this->script; ?>?do=synccto_clients&amp;table=tl_syncCto_clients_sync<?php echo $this->direction; ?>&amp;act=start&amp;step=<?php echo $this->step; ?>&amp;id=<?php echo $this->id; ?>" method="post">

<div class="submit_container">
	<input class="syncCto_filelist_submit" name="transfer" type="submit" value="<?php echo $GLOBALS['TL_LANG']['syncCto']['submit_files']; ?>" />
	<input class="syncCto_filelist_submit" name="delete" type="submit" value="<?php echo $GLOBALS['TL_LANG']['syncCto']['delete_files']; ?>" />
</div>

<table id="syncCto_filelist">
	<colgroup>
		<col width="12%" />
		<col width="12%" />
		<col width="35" />
		<col width="*" />
	</colgroup>
	<tbody>
		<tr>
			<td colspan="2">&nbsp;</td>
			<td class="checkbox"><input class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, 'syncCto_filelist')" type="checkbox" /></td>
			<td class="last"><?php echo $GLOBALS['TL_LANG']['syncCto']['select_all_files']; ?></td>
		</tr>
	<?php foreach ($this->filelist as $key => $file): ?>
        
		<tr>
	<?php if($i == 0 && $this->compare_complex == true): $i = 1; ?>
			<td colspan="4" class="headline">
				Da fehlt noch was!
			</td>
	<?php elseif(($file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_DELETE || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_MISSING || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_NEED)	&& $i == 1 && $this->compare_complex == true): $i = 2; ?>
			<td colspan="4" class="headline">
				<?php echo $GLOBALS['TL_LANG']['syncCto']['skipped_files']; ?>
			</td>
	<?php elseif(($file["state"] >= SyncCtoEnum::FILESTATE_BOMBASTIC_BIG) && $i == 2 && $this->compare_complex == true): $i = 3; ?>            
			<td colspan="4" class="headline">
				<?php echo $GLOBALS['TL_LANG']['syncCto']['ignored_files']; ?>
			</td>
	<?php endif; ?>
		</tr>
		<tr>
			<td class="state <?php echo $file["css"]; ?>"><?php echo $GLOBALS['TL_LANG']['syncCto'][$file["css"] . '_file']; ?></td>
			<td class="filesize"><?php echo $this->getReadableSize($file["size"]); ?></td>
			<td class="checkbox"><?php if($i == 1 || $this->compare_complex == false): ?><input class="tl_checkbox" type="checkbox" name="del-file-<?php echo $key; ?>" value="<?php echo $key; ?>" /><?php else: ?> X <?php endif; ?></td>  
			<td class="last" title="<?php echo htmlentities($file["path"]); ?>"><?php echo (strlen($file["path"]) >= 60) ? htmlentities(substr($file["path"], 0, 30) . "[...]" . substr($file["path"], strlen($file["path"]) - 30, strlen($file["path"]) - 1)) : htmlentities($file["path"]); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
    
</form>
