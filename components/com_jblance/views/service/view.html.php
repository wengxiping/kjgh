<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	04 November 2014
 * @file name	:	views/service/view.html.php
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
 class JblanceViewService extends JViewLegacy {

 	protected $params;

 	function display($tpl = null){
 		$app  	= JFactory::getApplication();
 		$layout = $app->input->get('layout', 'myservice', 'string');
 		$model	= $this->getModel();
 		$user	= JFactory::getUser();

 		$this->params = $app->getParams('com_jblance');



 		JblanceHelper::isAuthenticated($user->id, $layout);

 		if($layout == 'myservice'){
 			$return  = $model->getMyService();

 			$rows 	 = $return[0];
			$pageNav = $return[1];

			$this->rows = $rows;
			$this->pageNav = $pageNav;



        }
 		elseif($layout == 'listservice'){
 			$return  = $model->getListService();
 			$rows 	 = $return[0];
			$pageNav = $return[1];

			$this->rows = $rows;
			$this->pageNav = $pageNav;

			$application = JFactory::getApplication();
            $params = $application->getParams("com_menus");
            // dump($params->get("id"));

 		}
 		elseif($layout == 'servicebought'){
 			$return  = $model->getServiceBought();
 			$rows 	 = $return[0];
			$pageNav = $return[1];

			$this->rows = $rows;
			$this->pageNav = $pageNav;
 		}
 		elseif($layout == 'serviceprogress'){
 			$return   = $model->getServiceProgress();
 			$row 	  = $return[0];
 			$messages = $return[1];

			$this->row = $row;
			$this->messages = $messages;
 		}
 		elseif($layout == 'servicesold'){
 			$return  = $model->getServiceSold();
 			$rows 	 = $return[0];
			$pageNav = $return[1];

			$this->rows = $rows;
			$this->pageNav = $pageNav;
 		}
 		elseif($layout == 'editservice'){
 			$return = $model->getEditService();
 			$row 	= $return[0];

 			$this->row = $row;
 		}
 		elseif($layout == 'viewservice'){
 			$return  = $model->getViewService();
 			$row 	 = $return[0];
 			$ratings = $return[1];

 			$this->row = $row;
 			$this->ratings = $ratings;
 		}
 		elseif($layout == 'rateservice'){
 			$return = $model->getRateService();
 			$row 	= $return[0];

 			$this->row = $row;
 		}
        $state = 1;
        $this->assignRef('categorylist',$state);
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
