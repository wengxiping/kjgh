<?php
/**
 * @company        :    BriTech Solutions
 * @created by    :    JoomBri Team
 * @contact        :    www.joombri.in, support@joombri.in
 * @created on    :    28 March 2012
 * @file name    :    modules/mod_jblancecategory/helper.php
 * @copyright   :    Copyright (C) 2012 - 2019 BriTech Solutions. All rights reserved.
 * @license     :    GNU General Public License version 2 or later
 * @author      :    Faisel
 * @description    :    Entry point for the component (jblance)
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jblance/tables');
include_once(JPATH_ADMINISTRATOR . '/components/com_jblance/helpers/jblance.php');    //include this helper file to make the class accessible in all other PHP files

class ModJblanceCategoryHelperNew
{
    public static function getCategory($show_empty_count = 1)
    {
        $db = JFactory::getDbo();

        $query = "SELECT a.* FROM #__jblance_category a WHERE a.parent=0 AND a.published=1 ORDER BY a.ordering";//echo $query;
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        if ($show_empty_count == 1) {
            return $rows;
        } else {
            if (count($rows)) {
                $newRows = null;
                for ($n = 0; $n < count($rows); $n++) {
                    if (count(self::getSubCategories($rows[$n]->id, $show_empty_count, '', ''))) {
                        $newRows[] = $rows[$n];
                    }
                }
                return $newRows;
            } else {
                return $rows;
            }
        }
    }    //end function

    public static function getSubCategories($id_category, $show_empty_count, $init, $indent)
    {
        $db = JFactory::getDbo();
        $now = JFactory::getDate();

        if ($init)
            $result = $init;
        else
            $result = array();

        if ($show_empty_count == 1) {
            $query = "SELECT c.*, (SELECT count(p.id) FROM #__jblance_project p WHERE FIND_IN_SET(c.id, p.id_category) AND p.status='COM_JBLANCE_OPEN' AND p.approved=1 AND " . $db->quote($now) . " > p.start_date) AS thecount " .
                "FROM #__jblance_category c " .
                "WHERE c.parent =" . $db->quote($id_category) . " AND c.published=1 " .
                "ORDER BY c.ordering";
        } else {
            $query = "SELECT c.*, (SELECT count(p.id) FROM #__jblance_project p WHERE FIND_IN_SET(c.id, p.id_category) AND p.status='COM_JBLANCE_OPEN' AND p.approved=1 AND " . $db->quote($now) . " > p.start_date) AS thecount " .
                "FROM #__jblance_category c " .
                "WHERE c.parent =" . $db->quote($id_category) . " AND c.published=1 " .
                "HAVING thecount > 0 " .
                "ORDER BY c.ordering";
        }
        $db->setQuery($query);//echo $query;
        $rows = $db->loadObjectList();

        //get the sub elements of the sub categories
        foreach ($rows as $v) {
            $pre = ' ';
            $spacer = '&nbsp;&nbsp;&nbsp;';
            $v->category = $indent . $pre . $v->category;
            $result[] = $v;
           // $result = self::getSubCategories($v->id, $show_empty_count, $result, $indent . $spacer);
            //屏蔽这个位置，只显示二级分类
        }

        return $result;
    }

    public static function getCategorySelected($id)
    {
        $db = JFactory::getDbo();
        $query = "SELECT category FROM #__menu p WHERE p.id=" . $id . " and p.published = 1";
        $db->setQuery($query);//echo $query;
        $rowList = $db->loadResult();
        if ($rowList) {
            $query = "select c.id,c.parent,c.category from  #__jblance_category c where c.id in ($rowList)";
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            $arrStr = explode(",", $rowList);
            foreach ($rows as $row) {
                if ($row->parent != 0 && !in_array($row->parent, $arrStr)) {
                    $query = "select c.id,c.parent,c.category from  #__jblance_category c where c.id = " . $row->parent;
                    $db->setQuery($query);
                    $ret = $db->loadObjectList();
                    array_push($rows, $ret[0]);
                    array_push($arrStr, $row->parent);
                }
            }
            return $arrStr;
        } else {//如果结果为空则显示所有
            $query = "select c.id,c.parent,c.category from  #__jblance_category c";
            $db->setQuery($query);
            $rows = $db->loadObjectList();
            $arrStr = array();
            foreach ($rows as $row) {
                array_push($arrStr, $row->id);
            }
            return $arrStr;
        }
    }
}//end of class
