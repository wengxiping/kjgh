<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    28 March 2012
 * @file name    :    modules/mod_jblancecategory/tmpl/default.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Entry point for the component (jblance)
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


$document = JFactory::getDocument();
$direction = $document->getDirection();
$document->addStyleSheet("components/com_jblance/css/style.css");
$document->addStyleSheet("modules/mod_jblancecategory/css/style.css");

if ($direction === 'rtl')
    $document->addStyleSheet("modules/mod_jblancecategory/css/style-rtl.css");

$config = JblanceHelper::getConfig();

if ($config->loadBootstrap) {
    JHtml::_('bootstrap.loadCss', true, $direction);
}

//dump(ModJblanceCategoryHelper::showHtml($this->Itemid));

echo ModJblanceCategoryHelper::showHtml($this->Itemid);

?>

