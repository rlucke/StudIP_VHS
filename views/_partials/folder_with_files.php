 <? foreach ($folderwithfiles as $folder => $files): ?>
 <? if (!$allfolderwithfiles) { $allfolderwithfiles = $folderwithfiles; } ?>
    <? if ($parentfolder[$folder] == $parent) : ?>
        <section class="contentbox folder" <?= $parent ? 'style="display:none"' : '' ?>>
        <a class='folder_open' href=''><?= Icon::create('folder-full', 'clickable')?> <?= ( Folder::find($folder)->name ) ? : 'Allgemeiner Dateiordner' ?></a>
        <? if(array_keys($parentfolder, $folder)) : ?>
            <? foreach (array_keys($parentfolder, $folder) as $subfolder): ?>
                <?= $this->render_partial('_partials/folder_with_files', 
                    array('folderwithfiles' => [$subfolder => $allfolderwithfiles[$subfolder]],
                        'allfolderwithfiles' => $allfolderwithfiles,
                        'parentfolder' => $parentfolder,
                        'parent' => $folder)) ?>
            <? endforeach ?>
        <? endif ?>
        <? foreach ($files as $file): ?>
            <li class='file_download' style="display: <?=$display? : 'none' ?> "> <a href='<?=$GLOBALS['ABSOLUTE_URI_STUDIP']?>sendfile.php?force_download=1&type=0&file_id=<?= $file['id']?>&file_name=<?= $file['name'] ?>'><?= $file['name'] ?></a></li>
        <? endforeach ?>
        </section>
    <? endif ?>
<? endforeach ?>
