<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    23 March 2012
 * @file name    :    views/project/view.html.php
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
$app = JFactory::getApplication();
$tmpl = $app->input->get('tmpl', '', 'string');

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
    <!-- <div class="sp10">&nbsp;</div> -->
<?php

/**
 * HTML View class for the Jblance component
 */
class JblanceViewProject extends JViewLegacy
{

    protected $params;

    function display($tpl = null)
    {

        $app = JFactory::getApplication();
        $layout = $app->input->get('layout', 'editproject', 'string');
        $model = $this->getModel();
        $user = JFactory::getUser();
        $this->state = $this->get('state');
        $this->params = $app->getParams('com_jblance');


        if ($layout == 'editproject') {

            $return = $model->getEditProject();
            $row = $return[0];
            $projfiles = $return[1];
            $fields = $return[2];

            $this->row = $row;
            $this->projfiles = $projfiles;
            $this->fields = $fields;
        } elseif ($layout == 'showmyproject') {
            $return = $model->getShowMyProject();
            $rows = $return[0];
            $pageNav = $return[1];
            $this->rows = $rows;
            $this->pageNav = $pageNav;
        } elseif ($layout == 'listproject') {
            $return = $model->getListProject();
            $rows = $return[0];
            $pageNav = $return[1];
            $params = $return[2];
            $this->rows = $rows;
            $this->pageNav = $pageNav;
            $this->params = $params;

        } elseif ($layout == 'detailproject') {
            $return = $model->getDetailProject();
            $row = $return[0];
            $projfiles = $return[1];
            $bids = $return[2];
            $fields = $return[3];
            $forums = $return[4];

            $this->row = $row;
            $this->projfiles = $projfiles;
            $this->bids = $bids;
            $this->fields = $fields;
            $this->forums = $forums;

            //set page title and meta data
            $this->document->setTitle($row->project_title);
            if ($row->metadesc) {
                $this->document->setMetaData('description', $row->metadesc);
            }
            if ($row->metakey) {
                $this->document->setMetaData('keywords', $row->metakey);
            }
        } elseif ($layout == 'placebid') {
            $return = $model->getPlaceBid();
            $project = $return[0];
            $bid = $return[1];
            $this->project = $project;
            $this->bid = $bid;
        } elseif ($layout == 'showmybid') {
            $return = $model->getShowMyBid();
            $rows = $return[0];
            $pageNav = $return[1];
            $this->rows = $rows;
            $this->pageNav = $pageNav;
        } elseif ($layout == 'pickuser') {
            $return = $model->getPickUser();
            $rows = $return[0];
            $project = $return[1];
            $pageNav = $return[2];
            $this->rows = $rows;
            $this->pageNav = $pageNav;
            $this->project = $project;
        } elseif ($layout == 'rateuser') {
            $return = $model->getRateUser();
            $project = $return[1];
            $this->project = $project;
        } elseif ($layout == 'searchproject') {
            $return = $model->getSearchProject();
            $rows = $return[0];
            $pageNav = $return[1];

            $this->rows = $rows;
            $this->pageNav = $pageNav;
        } elseif ($layout == 'inviteuser') {
            $return = $model->getInviteUser();
            $rows = $return[0];
            $project = $return[1];
            $pageNav = $return[2];

            $this->rows = $rows;
            $this->project = $project;
            $this->pageNav = $pageNav;
        } elseif ($layout == 'invitetoproject') {
            $return = $model->getInviteToProject();
            $projects = $return[0];
            $this->projects = $projects;
        } elseif ($layout == 'projectprogress') {
            $return = $model->getProjectProgress();
            $row = $return[0];
            $messages = $return[1];

            $this->row = $row;
            $this->messages = $messages;
        }
//        $db = JFactory::getDbo();
//        $query = "SELECT categorylist FROM #__menu p WHERE categorylist is not null and p.published = 1";
//        $db->setQuery($query);//echo $query;
//        dump( $db->loadResult());
        echo '<div class="jb-bs">';
        if ($layout != 'detailproject') {
            $this->prepareDocument();
        }
        //die($layout);
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
