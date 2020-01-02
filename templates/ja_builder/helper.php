<?php
class Tpl_UberX_Helper {
	var $tpl = null;
	var $layout = null;
	var $mainbody = null;
	var $data = null;
	var $pageContent = array();
	var $assets = array();

	public function init($tpl) {	
		$this->tpl = $tpl;
		$layout_id = $tpl->params->get('jub-layout', 0);
		$layout = $this->getItem($layout_id);

		if ($layout) {
			// $layout->content = @json_decode($layout->content, true);
			// $layout->data = @json_decode($layout->data, true);
			$layout->content = @json_decode($layout->content, true);			
			if (!$layout->content) $layout->content = array();
			$this->mainbody = isset($layout->content['content']) ? $layout->content['content'] : null;	
			// assets
			$this->data = json_decode($layout->data, true);
			if (isset($this->data['assets'])) $this->assets = $this->data['assets'];

			$this->layout = $layout;
		}

		if (!$this->isJUBPage()) {
			$input = JFactory::getApplication()->input;
			$itemtype = $input->getCmd('Itemid') . ":" . $input->getCmd('option') . ":" . $input->getCmd('view') . ':' . $input->getInt('id');
			$item = $this->getItem(null, $itemtype);
			
			if ($item) {
				$this->pageContent = @json_decode($item->content, true);

				// get page assets
				$data = json_decode($item->data, true);
			
				if (isset($data['assets']) && is_array($data['assets'])) {
					$this->assets = array_replace_recursive($this->assets, $data['assets']);
				}
			}
		}
	
	}

	private function addCss ($path) {
		if (is_file(JPATH_ROOT . $path)) {
			$this->tpl->addStyleSheet (JUri::root(true) . $path . '?_=' . filemtime(JPATH_ROOT . $path));
		}
	}

	public function getItem($id, $type = null) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query	->clear()
				->select('*')
				->from($db->quoteName('#__jabuilder_pages'));
		if ($id) {
			$query->where($db->quoteName('id').'='.(int) $id);
		}
		if ($type) {
			$query->where($db->quoteName('type').'='.$db->quote($type));
		}
		
		$db->setQuery($query);

		$item = $db->loadObject();
		if ($item) {
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onJubLoadItem', array (&$item));
		}

		return $item;
	}

	public function isLayoutEditMode() {
		$input = JFactory::getApplication()->input;
		return $input->get('option') == 'com_jabuilder' && $input->get('layout') == 'edit_layout';
	}


	public function _layout($part) {
		if ($this->layout && $this->layout->content && $this->layout->content[$part]) {
			echo $this->layout->content[$part];
		}
	}

	public function mainbody($name) {
		if ($this->mainbody && isset($this->mainbody[$name])) {
			$result = $this->mainbody[$name];
			// ajust width for cols 
			if ($name == 'cols' && is_array($result)) {
				$contentIdx = -1;
				$colWidth = 0;
				foreach ($result as $i => $col) {
					if ($col['type'] == 'content') {
						$contentIdx = $i;
					} else {
						if ($this->tpl->countModules($col['name'])) {
							$colWidth += $col['width'];
						}
					}
				}
				// update main width
				if ($colWidth < 12) $result[$contentIdx]['width'] = 12 - $colWidth;
			}
			return $result;
		}	
		return null;	
	}

	public function _mainbody($name) {
		echo $this->mainbody($name);
	}

	public function isJUBPage () {
		return JFactory::getApplication()->input->get('option') == 'com_jabuilder';
	}

	public function pageContent ($name) {
		return (isset($this->pageContent[$name])) ? $this->pageContent[$name] : null;
	}

	public function _pageContent ($name) {
		echo $this->pageContent($name);
	}

	public function param ($name, $default = null) {
		return $this->tpl->params->get($name, $default);
	}

	public function _param ($name, $default = '') {
		echo $this->param($name, $default);
	}
}

return new Tpl_UberX_Helper();