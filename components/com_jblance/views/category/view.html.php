<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    16 March 2012
 * @file name    :    views/guest/view.html.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Entry point for the component (jblance)
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

$document = JFactory::getDocument();
$direction = $document->getDirection();
$config = JblanceHelper::getConfig();
$app  	  	= JFactory::getApplication();
$tmpl 	  	= $app->input->get('tmpl', '', 'string');


if ($config->loadBootstrap) {
    JHtml::_('bootstrap.loadCss', true, $direction);
}

$document->addStyleSheet("components/com_jblance/css/style.css");
if ($direction === 'rtl')
    $document->addStyleSheet("components/com_jblance/css/style-rtl.css");
?>
<?php
$document = JFactory::getDocument();
$renderer = $document->loadRenderer('modules');
$position = 'joombri-menu';
$options = array('style' => 'raw');
if ($tmpl == '')
    echo $renderer->render($position, $options, null);
?>

<?php

/**
 * HTML View class for the Jblance component
 */
class JblanceViewCategory extends JViewLegacy
{

    protected $params;

    function display($tpl = null)
    {
        //die;
        require_once(dirname(__FILE__) . '/helper.php');
        $app = JFactory::getApplication();
        $layout = $app->input->get('layout', 'editproject', 'string');
        $model = $this->getModel();
        $user = JFactory::getUser();
        $this->state = $this->get('state');
       // $this->params = $app->getParams('com_jblance');
        $app = JFactory::getApplication();
        $this->params= $app->getParams("com_menus");

        $this->total_column = intval($this->params->get('num_columns', 1));//布局项目
        $this->show_empty_count = 1;
        $this->show_count = 1;
        $this->setItemId = $app->input->get('Itemid', 0);


        $rows = ModJblanceCategoryHelperNew::getCategory($this->show_empty_count);
        $this->selectChooseIdArray = ModJblanceCategoryHelperNew::getCategorySelected($this->setItemId);
        $this->rows = $rows;
        echo '<div class="new-jb-bs">';
        $this->prepareDocument();
        parent::display($tpl);
        echo '</div>';
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }
}
