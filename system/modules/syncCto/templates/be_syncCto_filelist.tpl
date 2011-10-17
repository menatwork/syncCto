<h1 class="file"><strong><?php echo $GLOBALS['TL_LANG']['MSC']['totalsize']; ?></strong> ~ <?php echo $this->getReadableSize($this->totalsize); ?></h1>

<form id="syncCto_filelist_form" action="<?php echo $this->Environment->base; ?>contao/main.php?do=synccto_clients&amp;table=tl_syncCto_clients_sync<?php echo $this->direction; ?>&amp;act=start&amp;step=<?php echo $this->step; ?>&amp;id=<?php echo $this->id; ?>" method="post">

    <div class="submit_container">
        <input class="tl_submit" name="transfer" type="submit" value="<?php echo $GLOBALS['TL_LANG']['MSC']['submit_files']; ?>" />
        <input class="tl_submit" name="delete" type="submit" value="<?php echo $GLOBALS['TL_LANG']['MSC']['delete_files']; ?>" />
    </div>

    <table id="syncCto_filelist">
        <colgroup>
            <col width="12%" />
            <col width="15%" />
            <col width="35" />
            <col width="*" />
        </colgroup>
        <thead>
           
            <tr class="head">
                <th class="state">Status</th>
                <th class="filesize">Dateigröße</th>
                <th class="checkbox">&nbsp;</th>
                <th class="last">Datei</th>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="checkbox"><input class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this, 'syncCto_filelist')" type="checkbox" /></td>
                <td class="last"><?php echo $GLOBALS['TL_LANG']['MSC']['select_all_files']; ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->filelist as $key => $file): ?>
                <?php if ($i == 0 && $this->compare_complex == true): $i = 1; ?>
                    <tr>
                        <td colspan="4" class="headline">
                            Da fehlt noch was!
                        </td>
                    </tr>
                <?php elseif (($file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_DELETE || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_MISSING || $file["state"] >= SyncCtoEnum::FILESTATE_TOO_BIG_NEED) && $i == 1 && $this->compare_complex == true): $i = 2; ?>
                    <tr>
                        <td colspan="4" class="headline">
                            <?php echo $GLOBALS['TL_LANG']['MSC']['skipped_files']; ?>
                        </td>
                    </tr>
                <?php elseif (($file["state"] >= SyncCtoEnum::FILESTATE_BOMBASTIC_BIG) && $i == 2 && $this->compare_complex == true): $i = 3; ?>            
                    <tr>
                        <td colspan="4" class="headline">
                            <?php echo $GLOBALS['TL_LANG']['MSC']['ignored_files']; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="state <?php echo $file["css"]; ?>"><?php echo $GLOBALS['TL_LANG']['MSC'][$file["css"] . '_file']; ?></td>
                    <td class="filesize"><?php echo $this->getReadableSize($file["size"]); ?></td>
                    <td class="checkbox"><?php if ($i == 1 || $this->compare_complex == false): ?><input class="tl_checkbox" type="checkbox" name="del-file-<?php echo $key; ?>" value="<?php echo $key; ?>" /><?php else: ?> X <?php endif; ?></td>  
                    <td class="last" title="<?php echo htmlentities($file["path"]); ?>"><?php echo (strlen($file["path"]) >= 60) ? htmlentities(substr($file["path"], 0, 30) . "[...]" . substr($file["path"], strlen($file["path"]) - 30, strlen($file["path"]) - 1)) : htmlentities($file["path"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</form>
