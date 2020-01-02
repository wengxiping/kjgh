<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	14 March 2012
 * @file name	:	views/admconfig/view.html.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.component.view' );

$document = JFactory::getDocument();
$document->addStyleSheet (JURI::base().'components/com_jblance/assets/css/style.css');

class JblanceViewAdmconfig extends JViewLegacy {
	/**
	 * display method of Jblance view
	 * @return void
	 **/
	function display($tpl = null) {
		$app  	= JFactory::getApplication();
		$layout =  $app->input->get('layout', '', 'string');
		$model	= $this->getModel();
		$showSubMenu = false;
		
		if($layout == 'config'){
			$return = $model->getConfig();
			$row = $return[0];
			$params = $return[1];
			$this->row = $row;
			$this->params = $params;
		}
		elseif($layout == 'showusergroup'){
			$return = $model->getShowUserGroup();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];
			
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editusergroup'){
			$return = $model->getEditUserGroup();
			
			$row = $return[0];
			$fields = $return[1];
			$params = $return[2];
			
			$this->row = $row;
			$this->fields = $fields;
			$this->params = $params;
		}
		elseif($layout == 'showplan'){
			$return = $model->getShowPlan();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editplan'){
			$return = $model->getEditPlan();
			$row = $return[0];
			$params = $return[1];
		
			$this->row = $row;
			$this->params = $params;
		}
		elseif($layout == 'showpaymode'){
			$return = $model->getShowPaymode();
			$rows 	= $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editpaymode'){
			$return = $model->getEditPaymode();
			$paymode = $return[0];
			$params = $return[1];
			$form = $return[2];
			$this->paymode = $paymode;
			$this->params = $params;
			$this->form = $form;
		}
		elseif($layout == 'showcustomfield'){
			$return = $model->getShowCustomField();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
			$fieldfor = $return[3];
			
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
			$this->fieldfor = $fieldfor;
		}
		elseif($layout == 'editcustomfield'){
			$return = $model->getEditCustomField();
			$row = $return[0];
			$groups = $return[1];
			$lists = $return[2];
			
			$this->row = $row;
			$this->groups = $groups;
			$this->lists = $lists;
		}
		elseif($layout == 'emailtemplate'){
			$template = $model->getEmailTemplate();
			$this->template = $template;
		}
		elseif($layout == 'showcategory'){
			$return = $model->getShowCategory();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editcategory'){
			$row = $model->getEditCategory();
			$this->row = $row;
		}
		elseif($layout == 'showbudget'){
			$return = $model->getShowBudget();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editbudget'){
			$row = $model->getEditBudget();
			$this->row = $row;
		}
		elseif($layout == 'showduration'){
			$return = $model->getShowDuration();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editduration'){
			$row = $model->getEditDuration();
			$this->row = $row;
		}
		elseif($layout == 'showlocation'){
			$return = $model->getShowLocation();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists = $return[2];
			$ordering = $return[3];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
			$this->ordering = $ordering;
		}
		elseif($layout == 'editlocation'){
			$row = $model->getEditLocation();
			$this->row = $row;
		}
		elseif($layout == 'optimise'){
			$return = $model->getOptimise();
			$results = $return[0];
			$userIds = $return[1];
			$projectIds = $return[2];
		
			$this->results = $results;
			$this->userIds = $userIds;
			$this->projectIds = $projectIds;
		}
		elseif($layout == 'filemanager'){
		    $return = $model->getFileManager();
		    $list = $return[0];
		    $tree = $return[1];
		    
		    $this->state      = $this->get('state');
		    $this->folders    = $list['folders'];
		    $this->images     = $list['images'];
		    $this->documents  = $list['docs'];
		    $this->tree       = $tree;
		}
		
		// Load the submenu.
		if($showSubMenu)
			JblanceHelper::addSubmenu($app->input->get('layout', 'config', 'string'));
		
		$this->addToolbar();
		parent::display($tpl); 
		?>
		<div class="row-fluid">
			<div class="span12">
				<?php
				include_once('components/com_jblance/views/joombricredit.php');
				?>
			</div>
		</div>
<?php	
	} // end of display function
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar(){
		$app  = JFactory::getApplication();
		$layout =  $app->input->get('layout', '', 'string');
		jbimport('toolbar');
		switch ($layout){
	
			//Configuration : config panel
			case 'configpanel':
				JbToolbarHelper::_CONFIG_PANEL();
				break;
	
			//Configuration : All
			case 'config':
				JbToolbarHelper::_CONFIG();
				break;
	
			//Configuration : User Group
			case 'showusergroup':
				JbToolbarHelper::_SHOW_USERGROUP();
				break;
	
			case 'editusergroup' :
				JbToolbarHelper::_EDIT_USERGROUP();
				break;
	
			//Configuration : Subscription Plans for Users
			case 'showplan':
				JbToolbarHelper::_SHOW_PLAN();
				break;
	
			case 'editplan':
				JbToolbarHelper::_EDIT_PLAN();
				break;
	
			//Configuration : Payment Modes
			case 'showpaymode':
				JbToolbarHelper::_SHOW_PAYMODE();
				break;
	
			case 'editpaymode':
				JbToolbarHelper::_EDIT_PAYMODE();
				break;
	
			//custom fields
			case 'showcustomfield':
				JbToolbarHelper::_SHOW_CUSTOM_FIELD();
				break;
	
			case 'editcustomfield':
				JbToolbarHelper::_EDIT_CUSTOM_FIELD();
				break;
	
			//Configuration : Email Templates
			case 'emailtemplate':
				JbToolbarHelper::_EMAIL_TEMPLATE();
				break;
	
			//Configuration : Category
			case 'showcategory':
				JbToolbarHelper::_SHOW_CATEGORY();
				break;
	
			case 'editcategory' :
				JbToolbarHelper::_EDIT_CATEGORY();
				break;
	
			//Configuration : Budget Range
			case 'showbudget':
				JbToolbarHelper::_SHOW_BUDGET();
				break;
	
			case 'editbudget' :
				JbToolbarHelper::_EDIT_BUDGET();
				break;
				
			//Configuration : Project Duration
			case 'showduration':
				JbToolbarHelper::_SHOW_DURATION();
				break;
	
			case 'editduration' :
				JbToolbarHelper::_EDIT_DURATION();
				break;
				
			//Configuration : Location
			case 'showlocation':
				JbToolbarHelper::_SHOW_LOCATION();
				break;
			
			case 'editlocation' :
				JbToolbarHelper::_EDIT_LOCATION();
				break;
				
			case 'optimise' :
				JbToolbarHelper::_OPTIMSE();
				break;
				
			case 'filemanager' :
			    JbToolbarHelper::_FILEMANAGER();
				break;
	
			default:
				JbToolbarHelper::_DEFAULT();
			break;
		}
	}
} // end of class
