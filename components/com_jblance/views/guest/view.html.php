<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	16 March 2012
 * @file name	:	views/guest/view.html.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
 defined('_JEXEC') or die('Restricted access');
 jimport('joomla.application.component.view');

 $document = JFactory::getDocument();
 $direction = $document->getDirection();
 $config = JblanceHelper::getConfig();

 if($config->loadBootstrap){
	JHtml::_('bootstrap.loadCss', true, $direction);
 }

 $document->addStyleSheet("components/com_jblance/css/style.css");
 if($direction === 'rtl')
 	$document->addStyleSheet("components/com_jblance/css/style-rtl.css");

/**
 * HTML View class for the Jblance component
 */
class JblanceViewGuest extends JViewLegacy {

	protected $params;

	function display($tpl = null){
		$app  	= JFactory::getApplication();

		$layout = $app->input->get('layout', 'showfront', 'string');
		$model	= $this->getModel();

		$this->params = $app->getParams('com_jblance');

		if($layout == 'showfront'){
			$return = $model->getShowFront();
			$userGroups = $return[0];
			$this->userGroups = $userGroups;
		}
		elseif($layout == 'usergroupfield'){
			$fields = $model->getUserGroupField();

			$this->fields = $fields;
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
