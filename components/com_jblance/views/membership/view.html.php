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
class JblanceViewMembership extends JViewLegacy {

	protected $params;

	function display($tpl = null){
		$app  	= JFactory::getApplication();
		$layout = $app->input->get('layout', 'planadd', 'string');
		$model	= $this->getModel();
		$user	= JFactory::getUser();
		$userid = $user->id;

		$this->params = $app->getParams('com_jblance');

		JblanceHelper::isAuthenticated($userid, $layout);

		if($layout == 'planadd'){
			$return = $model->getPlanAdd();

			$rows = $return[0];
			$plans = $return[1];

			$this->rows = $rows;
			$this->plans = $plans;
		}
		elseif($layout == 'planhistory'){
			$return = $model->getPlanHistory();
			$rows = $return[0];
			$finish = $return[1];
			$pageNav = $return[2];

			$this->rows = $rows;
			$this->finish = $finish;
			$this->pageNav = $pageNav;
		}
		elseif($layout == 'check_out'){

			$type = $app->input->get('type', 'plan', 'string'); //get the type of purchase to call different functions

			if($type == 'plan'){
				$return = $model->getPlanCheckout();
				$subscr = $return[0];
				$plan = $return[1];

				$this->plan = $plan;
				$this->subscr = $subscr;
			}
			elseif($type == 'deposit'){
				$return = $model->getDepositCheckout();
				$deposit = $return[0];

				$this->deposit = $deposit;
			}
		}
		elseif($layout == 'transaction'){
			$return = $model->getTransaction();

			$rows = $return[0];
			$pageNav = $return[1];
			$last_trans = $return[2];

			$this->rows = $rows;
			$this->pageNav = $pageNav;
			$this->last_trans = $last_trans;
		}
		elseif($layout == 'withdrawfund'){
			$return = $model->getWithdrawFund();
			$paymodes = $return[0];
			$form = $return[1];
			$this->paymodes = $paymodes;
			$this->form = $form;
		}
		elseif($layout == 'escrow'){
			$return = $model->getEscrow();
			$lists = $return[0];
			$this->lists = $lists;
		}
		elseif($layout == 'managepay'){
			$return = $model->getManagepay();
			$escrow_out = $return[0];
			$escrow_in = $return[1];
			$withdraws = $return[2];
			$deposits = $return[3];
			$pageNavWithdraw = $return[4];
			$pageNavDeposit = $return[5];

			$this->escrow_out = $escrow_out;
			$this->escrow_in = $escrow_in;
			$this->withdraws = $withdraws;
			$this->deposits = $deposits;
			$this->pageNavWithdraw = $pageNavWithdraw;
			$this->pageNavDeposit = $pageNavDeposit;
		}
		elseif($layout == 'invoice'){
			$return = $model->getInvoice();
			$row = $return[0];
			$escrows = $return[1];

			$this->row = $row;
			$this->escrows = $escrows;
		}
		elseif($layout == 'plandetail'){
			$return = $model->getPlanDetail();
			$row = $return[0];
			$this->row = $row;
		}
		elseif($layout == 'thankpayment'){
			$return = $model->getThankPayment();
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
