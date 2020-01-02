<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	21 March 2012
 * @file name	:	views/user/view.html.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 jimport('joomla.application.component.view');

 $document 	= JFactory::getDocument();
 $direction = $document->getDirection();
 $config 	= JblanceHelper::getConfig();
 $app  	  	= JFactory::getApplication();
 $tmpl 	  	= $app->input->get('tmpl', '', 'string');
 
 if($config->loadBootstrap){
	JHtml::_('bootstrap.loadCss', true, $direction);
 }
 
 $document->addStyleSheet("components/com_jblance/css/style.css");
 if($direction === 'rtl')
 	$document->addStyleSheet("components/com_jblance/css/style-rtl.css");
?>
 <?php 
 $document = JFactory::getDocument();
 $renderer   = $document->loadRenderer('modules');
 $position   = 'joombri-menu';
 $options   = array('style' => 'raw');
 if($tmpl == '')
 	echo $renderer->render($position, $options, null); 
 ?>
 <!-- <div class="sp10">&nbsp;</div> -->
<?php
/**
 * HTML View class for the Jblance component
 */
class JblanceViewUser extends JViewLegacy {
	
	protected $params;

	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'dashboard', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		
		$this->params = $app->getParams('com_jblance');
		
		JblanceHelper::isAuthenticated($user->id, $layout);
		
		if($layout == 'dashboard'){
			$return = $model->getDashboard();
			$dbElements = $return[0];
			$userInfo = $return[1];
			$feeds = $return[2];
			$pendings = $return[3];
			
			$this->dbElements = $dbElements;
			$this->userInfo = $userInfo;
			$this->feeds = $feeds;
			$this->pendings = $pendings;
		}
		elseif($layout == 'editprofile'){
			$return = $model->getEditProfile();
			$userInfo = $return[0];
			$fields = $return[1];
		
			$this->userInfo = $userInfo;
			$this->fields = $fields;
		}
		elseif($layout == 'editpicture'){
			$return = $model->getEditPicture();
			$row = $return[0];
			
			$this->row = $row;
		}
		elseif($layout == 'editportfolio'){
			$return = $model->getEditPortfolio();
			$row = $return[0];
			$portfolios = $return[1];
			
			$this->row = $row;
			$this->portfolios = $portfolios;
		}
		elseif($layout == 'userlist'){
			$return = $model->getUserList();
			$rows = $return[0];
			$pageNav = $return[1];
			$params = $return[2];
		
			$this->pageNav = $pageNav;
			$this->rows = $rows;
			$this->params = $params;
		}
		elseif($layout == 'viewportfolio'){
		
			$return 	= $model->getViewPortfolio();
			$row 	= $return[0];
			$this->row = $row;
		}
		elseif($layout == 'viewprofile'){
			
			$return 	= $model->getViewProfile();
			$userInfo 	= $return[0];
			$fields 	= $return[1];
			$fprojects 	= $return[2];
			$frating 	= $return[3];
			$bprojects 	= $return[4];
			$brating 	= $return[5];
			$portfolios = $return[6];
		
			$this->userInfo = $userInfo;
			$this->fields = $fields;
			$this->fprojects = $fprojects;
			$this->frating = $frating;
			$this->bprojects = $bprojects;
			$this->brating = $brating;
			$this->portfolios = $portfolios;
		}
		elseif($layout == 'notify'){
			
			$return = $model->getNotify();
			$row = $return[0];
		
			$this->row = $row;
		}
		
		echo '<div class="jb-bs">';
		$this->prepareDocument();
        parent::display($tpl);
        echo '</div>';
	}
	
	/**
	 * Prepares the document
	 *
	 * @return  void
	 */
	protected function prepareDocument(){
	
		if($this->params->get('menu-meta_description')){
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
	
		if($this->params->get('menu-meta_keywords')){
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
	
		if($this->params->get('robots')){
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
