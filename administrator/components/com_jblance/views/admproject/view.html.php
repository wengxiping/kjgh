<?php
/**
 * @company		:	BriTech Solutions
 * @created by	:	JoomBri Team
 * @contact		:	www.joombri.in, support@joombri.in
 * @created on	:	12 March 2012
 * @file name	:	views/admproject/view.html.php
 * @copyright   :	Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :	GNU General Public License version 2 or later
 * @author      :	Faisel
 * @description	: 	Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

$document = JFactory::getDocument();
$document->addStyleSheet (JURI::base().'components/com_jblance/assets/css/style.css');

class JblanceViewAdmproject extends JViewLegacy {
	/**
	 * display method of Jblance view
	 * @return void
	 **/
	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout =  $app->input->get('layout', 'dashboard', 'string');
		$model	= $this->getModel();
		$showSubMenu = false;
		
		if($layout == 'dashboard' or $layout == ''){
			$showSubMenu = true;
			$return  	 = $model->getDashboard();
			$users 	 	 = $return[0];
			$jbusers 	 = $return[1];
			$projects    = $return[2];
			$unappProjs  = $return[3];
			$unappUsers  = $return[4];
			$unappSubs   = $return[5];
			$unappDepo   = $return[6];
			$unappWdraws = $return[7];
			$unappMsgs   = $return[8];
			$xmlManifest = $return[9];
			$newVersion  = $return[10];
			$services    = $return[11];
			$unappSvcs   = $return[12];
			$tag 		 = $return[13];

			$this->users = $users;
			$this->jbusers = $jbusers;
			$this->projects = $projects;
			$this->unappProjs = $unappProjs;
			$this->unappUsers = $unappUsers;
			$this->unappSubs = $unappSubs;
			$this->unappDepo = $unappDepo;
			$this->unappWdraws = $unappWdraws;
			$this->unappMsgs = $unappMsgs;
			$this->xmlManifest = $xmlManifest;
			$this->newVersion = $newVersion;
			$this->services = $services;
			$this->unappSvcs = $unappSvcs;
			$this->tag = $tag;
		}
		elseif($layout == 'showproject'){
			$showSubMenu = true;
			$return  = $model->getShowProject();
			$rows 	 = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];

			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editproject'){
			$return 	= $model->getEditProject();
			$row 		= $return[0];
			$projfiles 	= $return[1];
			$bids 		= $return[2];
			$lists 		= $return[3];
			$fields 	= $return[4];
			$forums 	= $return[5];

			$this->row = $row;
			$this->projfiles = $projfiles;
			$this->bids = $bids;
			$this->lists = $lists;
			$this->fields = $fields;
			$this->forums = $forums;
		}
		elseif($layout == 'showservice'){
			$showSubMenu = true;
			$return  = $model->getShowService();
			$rows 	 = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];
			
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editservice'){
			$return 	= $model->getEditService();
			$row 		= $return[0];
			$lists 		= $return[1];
			$orders 	= $return[2];
			
			$this->row = $row;
			$this->lists = $lists;
			$this->orders = $orders;
		}
		elseif($layout == 'showuser'){
			$showSubMenu = true;
			$return  = $model->getShowUser();
			$rows 	 = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];

			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'edituser'){
			$return   = $model->getEditUser();
			$row 	  = $return[0];
			$lists 	  = $return[1];
			$grpLists = $return[2];
			$trans 	  = $return[3];
			$fields   = $return[4];

			$this->row = $row;
			$this->lists = $lists;
			$this->grpLists = $grpLists;
			$this->trans = $trans;
			$this->fields = $fields;
		}
		elseif($layout == 'showsubscr'){
			$showSubMenu = true;
			$return  = $model->getShowSubscr();
			$rows 	 = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'editsubscr'){
			$return = $model->getEditSubscr();
			$row = $return[0];
			$users = $return[1];
			$plans = $return[2];
			$lists = $return[3];
		
			$this->row = $row;
			$this->users = $users;
			$this->plans = $plans;
			$this->lists = $lists;
		}
		elseif($layout == 'showdeposit'){
			$showSubMenu = true;
			$return = $model->getShowDeposit();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'showwithdraw'){
			$showSubMenu = true;
			$return = $model->getShowWithdraw();
			$rows = $return[0];
			$pageNav = $return[1];
			$lists 	 = $return[2];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->lists = $lists;
		}
		elseif($layout == 'showescrow'){
			$showSubMenu = true;
			$return = $model->getShowsEscrow();
			$rows = $return[0];
			$pageNav = $return[1];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
		}
		elseif($layout == 'showsummary'){
			$showSubMenu = true;
			$return 	= $model->getShowSummary();
			$deposits 	= $return[0];
			$withdraws 	= $return[1];
			$project 	= $return[2];
			$subscrs 	= $return[3];
			$service 	= $return[4];
			$lists 	 	= $return[5];
			$jsonChart  = $return[6];
		
			$this->deposits = $deposits;
			$this->withdraws = $withdraws;
			$this->project = $project;
			$this->subscrs = $subscrs;
			$this->service = $service;
			$this->lists = $lists;
			$this->jsonChart = $jsonChart;
		}
		elseif($layout == 'showreporting'){
			$showSubMenu = true;
			$return = $model->getShowReporting();
			$rows = $return[0];
			$pageNav = $return[1];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
		}
		elseif($layout == 'detailreporting'){
			$return = $model->getDetailReporting();
			$rows = $return[0];
			$pageNav = $return[1];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
		}
		elseif($layout == 'managemessage'){
			$showSubMenu = true;
			$return = $model->getManageMessage();
			$rows = $return[0];
			$pageNav = $return[1];
			$threads = $return[2];
			$lists = $return[3];
		
			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->threads = $threads;
			$this->lists = $lists;
		}
		elseif($layout == 'about'){
			$showSubMenu = true;
			$filename = JPATH_COMPONENT.'/jblance.xml';
			$xml 	  = simplexml_load_file($filename);
			$this->version = $xml->{'version'};
		}
		elseif($layout == 'invoice'){
			$return = $model->getInvoice();
			$row = $return[0];
		
			$this->row = $row;
		}
		
		// Load the submenu.
		if($showSubMenu){
			JblanceHelper::addSubmenu($app->input->get('layout', 'dashboard', 'string'));
			$this->sidebar = JHtmlSidebar::render();
		}
		
		$this->addToolbar();
		parent::display($tpl); 
?>
<div class="row-fluid">
	<div class="span12">
		<?php
		$app = JFactory::getApplication();
		$print = $app->input->get('print', 0, 'int');
		if(!$print)
			include_once('components/com_jblance/views/joombricredit.php');
		?>		
	</div>
</div>
<?php	}
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar(){
		$app  	= JFactory::getApplication();
		$layout =  $app->input->get('layout', 'dashboard', 'string');
		jbimport('toolbar');
		switch ($layout){
			//Dashboard
			case 'dashboard':
				JbToolbarHelper::_DASHBOARD();
				break;
			
			//Projects
			case 'showproject':
				JbToolbarHelper::_SHOW_PROJECT();
				break;
			
			case 'editproject' :
				JbToolbarHelper::_EDIT_PROJECT();
				break;
			
			//Services
			case 'showservice':
				JbToolbarHelper::_SHOW_SERVICE();
				break;
			
			case 'editservice' :
				JbToolbarHelper::_EDIT_SERVICE();
				break;
			
			//user
			case 'showuser':
				JbToolbarHelper::_SHOW_USER();
				break;
			
			case 'edituser' :
				JbToolbarHelper::_EDIT_USER();
				break;
			
			//Subscription
			case 'showsubscr':
				JbToolbarHelper::_SHOW_SUBSCR();
				break;
			
			case 'editsubscr':
				JbToolbarHelper::_EDIT_SUBSCR();
				break;
			
			//Deposit
			case 'showdeposit':
				JbToolbarHelper::_SHOW_DEPOSIT();
				break;
			
			//Withdrawals
			case 'showwithdraw':
				JbToolbarHelper::_SHOW_WITHDRAW();
				break;
			
			//Escrows
			case 'showescrow':
				JbToolbarHelper::_SHOW_ESCROW();
				break;
			
			//Reporting
			case 'showreporting':
				JbToolbarHelper::_SHOW_REPORTING();
				break;
				
			case 'detailreporting':
				JbToolbarHelper::_DETAIL_REPORTING();
				break;
			
			//Messages
			case 'managemessage':
				JbToolbarHelper::_SHOW_MESSAGE();
				break;
			
			default:
				JbToolbarHelper::_DEFAULT();
			break;
		}
	}
}
