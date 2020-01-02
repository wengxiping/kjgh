<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	30 May 2012
 * @file name	:	views/message/view.html.php
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
class JblanceViewMessage extends JViewLegacy {
	
	protected $params;

	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'inbox', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		
		$this->params = $app->getParams('com_jblance');
		
		JblanceHelper::isAuthenticated($user->id, $layout);
		
		if($layout == 'inbox'){
			$return 	 = $model->getInbox();
			$msgs 		 = $return[0];
			//$out_msgs  = $return[1];
			//$newMsg 	 = $return[2];
			//$newOutMsg = $return[3];
			$pageNav 	 = $return[4];
			
			$this->msgs 		= $msgs;
			//$this->out_msgs 	= $out_msgs;
			//$this->newMsg 	= $newMsg;
			//$this->newOutMsg 	= $newOutMsg;
			$this->pageNav 		= $pageNav;
		}
		if($layout == 'read'){
			$return = $model->getMessageRead();
			$parent = $return[0];
			$rows = $return[1];
			
			$this->parent = $parent;
			$this->rows = $rows;
		}
		if($layout == 'compose'){
			/* $return = $model->getMessageRead();
			$parent = $return[0];
			$rows = $return[1];
			
			$this->parent = $parent;
			$this->rows = $rows; */
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
