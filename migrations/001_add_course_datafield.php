<?php

/**
 * @author Till Gl�ggler <tgloeggl@uos.de>
 */
class AddCourseDatafield extends Migration
{

    public function description()
    {
        return 'add datafields for each sem_class for removing course tab-navigation and selecting style of overview-page';
    }

    public function up()
    {
        $db = DBManager::get();
        
        $stm = $db->prepare(
            "INSERT INTO `datafields` (`datafield_id`, `name`, `object_type`,
                `object_class`, `edit_perms`, `view_perms`, `priority`,
                `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `description`)
            VALUES (md5('Hide Course Navigation'), 'Hide Course Navigation', 1,
                NULL, 3, 3, '0', NULL, NULL, 1, '', '0', 'Veranstaltungsreiter Ausblenden')"
        );

        $stm->execute();

        
        $stm = $db->prepare(
            "INSERT INTO `datafields` (`datafield_id`, `name`, `object_type`,
                `object_class`, `edit_perms`, `view_perms`, `priority`,
                `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `description`)
            VALUES (md5('Overview style'), 'Overview style', 1,
                NULL, 3, 3, '0', NULL, NULL, 3, '', '0', 'Design f�r �bersichtsseite')"
        );
      
        $stm->execute();
           
    }

    public function down()
    {
        DBManager::get()->exec(

            "DELETE FROM datafields WHERE datafield_id "
                . "IN(md5('Hide Course Navigation'))"
        );
         DBManager::get()->exec(

            "DELETE FROM datafields WHERE datafield_id "
                . "IN(md5('Overview style'))"
        );
    }
}
