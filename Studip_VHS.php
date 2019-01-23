<?php
require_once 'lib/bootstrap.php';
require_once __DIR__ . '/models/SeminarTab.class.php';

/**
 * Uebersicht_VHS.class.php
 *
 * ...
 *
 * @author  Annelene Sudau <asudau@uos.de>
 * @version 0.1a
 */

class Studip_VHS extends StudIPPlugin implements StandardPlugin, SystemPlugin
{

    public function __construct()
    {
        parent::__construct();
        global $perm;
        
        $this->sidebar_images = array(
            'admin' => 'admin-sidebar.png',
            'forum2' => 'forum-sidebar.png',
            'members' => 'person-sidebar.png',
            'files' => 'files-sidebar.png',
            'schedule' => 'schedule-sidebar.png',
            'wiki' => 'wiki-sidebar.png',
            'calendar' => 'date-sidebar.png',
            'mooc_courseware' => 'group-sidebar.png',
            'members' => 'person-sidebar.png',
            'vipsplugin' => 'checkbox-sidebar.png',
            'modules' => 'plugin-sidebar.png',
            'literature' => 'literature-sidebar.png',
            'generic' => 'generic-sidebar.png',
        );
        
        $this->templates = array(
            'index_ammerland',
            'index_el4',
            'kacheln',
            'mitarbeiter',
            'index_wesermarsch',
            'index_allgemein');
           
        //Falls wir schon in einem Kurs sind, checke ob dieser schon gestartet und konfiguriere die Navigation
        $referer = $_SERVER['REQUEST_URI'];
        $this->course = Course::findCurrent();
	 	$this->course_id = $this->course->id;
		
		if ($this->course)
		{
            if(!$this->course_available($this->course_id) && $referer==str_replace("seminar_main.php","",$referer) && $referer==str_replace("/studip_vhs/seminar","",$referer) ){
                PageLayout::postMessage(MessageBox::error(_('Dieser Kurs ist noch nicht gestartet.')));
                header('Location: '. $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/my_courses?cid=' , false, 303);
                exit();
            }
            $this->setupStudIPNavigation($this->course_id);	
        }
        

        //Adminbereich f�r Intranetverwaltung
        if($perm->have_perm('root')){
            $navigation = new Navigation('Intranetverwaltung', PluginEngine::getURL($this, array(), 'intranetverwaltung/index'));
            $navigation->addSubNavigation('index', new Navigation('�bersicht', PluginEngine::getURL($this, array(), 'intranetverwaltung/index')));
            $navigation->addSubNavigation('settings', new Navigation('Einstellungen', PluginEngine::getURL($this, array(), 'settings')));
            Navigation::addItem('/admin/intranetverwaltung', $navigation);
        } 
   
        //setup intranet navigation and forward if just logged in
        //TODO: auslagern
        $intranets = $this->getIntranetIDsForUser();
        
        if (Navigation::hasItem('/start') && $intranets){
            Navigation::getItem('/start')->setURL(PluginEngine::getLink($this, array(), 'intranet_start/index/' . $intranets[0]) );
        }
        //Intranetnutzer werden statt auf die allgemeine Startseite auf ihre individuelle Startseite weitergeleitet
        if ( $referer!=str_replace("dispatch.php/start","",$referer) &&  $intranets){
            //$result = $this->getSemStmt($GLOBALS['user']->id);
            header('Location: '. PluginEngine::getLink($this, array(), 'intranet_start/index/' . $intranets[0]) , false, 303);
            exit();	
        //Nicht-Intranetnutzer werden, wenn sie die Intranet URL verwenden, auf die allgemeine Startseite weitergeleitet
        } 
    }

    public function initialize ()
    {
        PageLayout::addStylesheet($this->getPluginURL().'/assets/settings_sortable.css');
        PageLayout::addScript('http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js');
        PageLayout::addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js');
        PageLayout::addScript($this->getPluginURL().'/assets/scripts/settings_sortable.js');
        //PageLayout::addScript($this->getPluginURL().'/assets/scripts/replace_tab_navigation.js');
        
    }

    public function getTabNavigation($course_id)
    {
        $localEntries = DataFieldEntry::getDataFieldEntries($course_id);
        
        $datafield_begin =  DataField::findOneBySQL('name = \'course begin\'');
        $this->datafield_id_begin = $datafield_begin->datafield_id;
        
        //Kurs hat noch nicht begonnen
        //TODO Navigation deaktivieren und Fehler werfen in den anderen Actions
        if (!$this->course_available($course_id)){
            $navigation = new Navigation(_('�bersicht'));
            $navigation->setImage(Icon::create('seminar', 'info_alt'));
            $navigation->setActiveImage(Icon::create('seminar', 'info'));
            $navigation->setURL(PluginEngine::getURL($this, [], 'seminar/not_started'));
        
            return array(
                'overview_vhs' => $navigation
            );
        }
        
        $datafield =  DataField::findOneBySQL('name = \'Overview style\'');
        $this->datafield_id_overview = $datafield->datafield_id;
        
        
        $this->style = $localEntries[$this->datafield_id_overview]->value;

        if($this->style == 'standard'){ //todo: oder keine angabe
            $core_overview = CoreOverview::getTabNavigation($course_id);
            if($GLOBALS["perm"]->have_studip_perm('tutor', $course_id)){
                $item = new Navigation(_('Kurs gestalten'), PluginEngine::getLink($this, array(), 'seminar/settings'));
                $core_overview['main']->addSubNavigation('switchback', $item);
            }
            return $core_overview;
        }
       
        $navigation = new Navigation(_('�bersicht'));
        $navigation->setImage(Icon::create('seminar', 'info_alt'));
        $navigation->setActiveImage(Icon::create('seminar', 'info'));
        $navigation->setURL(PluginEngine::getURL($this, array('style' => $this->style), 'seminar'));
        
        return array(
            'overview_vhs' => $navigation
        );
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return array();
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        // ...
    }

    public function getInfoTemplate($course_id)
    {
        // ...
    }

    public function perform($unconsumed_path)
    {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
        
        //for current user check prerequisites
        //if all existing excercises done, ajax call of zertifikats-action
        
    }

    private function setupAutoload()
    {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }
	
    public function getIntranetIDsForUser(){
        $user = User::findCurrent();
        $datafield_id_inst = md5('Eigener Intranetbereich');
        $intranets = array();
        foreach($user->institute_memberships as $membership){
            $entries = DataFieldEntry::getDataFieldEntries($membership->institut_id);
            if ($entries[$datafield_id_inst]->value){
                $intranets[] = $membership->institut_id;
            }
        }
        return $intranets;
    }
    
    private function setupStudIPNavigation($course_id){
		
        //falls individuelle Einstellungen zur Reihenfogle vorliegen
		$block = SeminarTab::findOneBySQL('seminar_id = ? ORDER BY position ASC',
                                 array($course_id) );
        if (!$this->course_available($course_id)){
            $this->clearCourseNavigation($course_id);
        }
		else if($block){
			$this->sortCourseNavigation($course_id);
		}
    }
	
    private function sortCourseNavigation($course_id){
        global $perm;
        $restNavigation = array();
        $newNavigation = Navigation::getItem('/course');
        foreach(Navigation::getItem('/course') as $key => $tab){
            $block = SeminarTab::findOneBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                     array($this->course_id,$key) );
            if($block){
                $tab->setTitle($block->getValue('title'));
                if($block->getValue('tn_visible') == true || $perm->have_studip_perm('dozent', Request::get('cid')) ){
                    $subNavigations[$block->getValue('position')][$key] = $tab;
                }
            } else { 
               //keine Info bez�glich Reihenfolge also hinten dran
               //greift bei neu aktivierten Navigationselementen
               $restNavigation[$key] = $tab;
            }
            $newNavigation->removeSubNavigation($key);
        }	

        ksort($subNavigations);

        foreach($subNavigations as $subNavs){
            foreach($subNavs as $key => $subNav){
                $newNavigation->addSubNavigation($key, $subNav);
            }
        }
        if(count($restNavigation)>0){
            foreach($restNavigation as $key => $restNav){
                $newNavigation->addSubNavigation($key, $restNav);  
            }
        }
        Navigation::addItem('/course', $newNavigation);
    }
    
     private function clearCourseNavigation(){
        $navigation = Navigation::getItem('/course');
        $overview = '';
        
        //diese unterscheidung muss anders gemacht werden
        foreach(Navigation::getItem('/course') as $key => $tab){
            $navigation->removeSubNavigation($key);
        }    
        $new_navigation = new Navigation(_('�bersicht'));
        $new_navigation->setImage(Icon::create('seminar', 'info_alt'));
        $new_navigation->setActiveImage(Icon::create('seminar', 'info'));
        $new_navigation->setURL(PluginEngine::getURL($this, [], 'seminar/not_started'));
        $navigation->addSubNavigation('overview_vhs', $new_navigation);
        Navigation::addItem('/course', $navigation);
     }
     
     private function course_available($course_id){
        $localEntries = DataFieldEntry::getDataFieldEntries($course_id);
        
        $datafield_begin =  DataField::findOneBySQL('name = \'course begin\'');
        $this->datafield_id_begin = $datafield_begin->datafield_id;
        
        //falls Kurs noch nciht gestartet und Nutzer autor -> noch kein Zugriff
        if ((!$GLOBALS['perm']->have_studip_perm('tutor', $course_id) || $localEntries[$this->datafield_id_begin]->value > time())){
            return false;
        }else return true;
     }
    
}
