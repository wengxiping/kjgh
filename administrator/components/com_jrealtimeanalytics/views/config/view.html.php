<?php
// namespace administrator\components\com_jrealtimeanalytics\views\cpanel;
/**
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage config
 * @author Joomla! Extensions Store
 * @copyright (C) 2013 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Config view
 *
 * @package JREALTIMEANALYTICS::CONFIG::administrator::components::com_jrealtimeanalytics
 * @subpackage views
 * @subpackage config
 * @since 1.0
 */
class JRealtimeViewConfig extends JRealtimeView {
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		JToolBarHelper::title( JText::_( 'COM_JREALTIME_CONFIG' ), 'jrealtimeanalytics' );
		JToolBarHelper::save('config.saveentity', 'COM_JREALTIME_SAVECONFIG');
		JToolBarHelper::custom('cpanel.display', 'home', 'home', 'COM_JREALTIME_CPANEL', false);
	}
	
	/**
	 * Effettua il rendering dei tabs di configurazione del componente
	 * @access public
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = JFactory::getDocument();
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$this->loadValidation($doc);
		
		// Load specific JS App
		$doc->addScriptDeclaration("
					Joomla.submitbutton = function(pressbutton) {
						if(!jQuery.fn.validation) {
							jQuery.extend(jQuery.fn, jrealtimejQueryBackup.fn);
						}
						jQuery('#adminForm').validation();
				
						if (pressbutton == 'cpanel.display') {
							Joomla.submitform( pressbutton );
							return true;
						}
		
						if(jQuery('#adminForm').validate()) {
							Joomla.submitform( pressbutton );
							return true;
						}
						var parentId = jQuery('ul.errorlist').parents('div.tab-pane').attr('id');
						jQuery('#tab_configuration a[data-element=' + parentId + ']').tab('show');
						return false;
					};
				");
		
		$params = $this->get('Data');
		$form = $this->get('form');
		
		// Bind the form to the data.
		if ($form && $params) {
			$form->bind($params);
		}
		
		$this->params_form = $form;
		$this->params = $params;
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display();
	}
}
?>