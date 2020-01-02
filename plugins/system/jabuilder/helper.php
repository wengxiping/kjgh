<?php
jimport('joomla.filesystem.file');
require_once (__DIR__ . '/vendor/autoload.php');

class JUBHelper {
	var $app;
	var $params;
	// custom code
	var $cs_top_head = '';
	var $cs_bot_head = '';
	var $cs_top_body = '';
	var $cs_bot_body = '';

	public function __construct ($params) {
		$this->params = $params;
		$this->app = JFactory::getApplication();
	}

	public function isJUB () {
		return !$this->app->isAdmin() && ($this->isJUBPage() || $this->isJUBLayout());
	}

	public function isJUBPage () {
		return $this->app->input->get('option') == 'com_jabuilder';
	}

	public function isJUBLayout () {
		return $this->app->getTemplate() == 'ja_builder';
	}

	public function jubMode() {
		$input = $this->app->input;
		if (!empty($input->request)) $input = $input->request;
		return $input->getCmd('jub');
	}

	public function hasPermission () {
		$user = JFactory::getUser();
		return $user->authorise('core.edit', 'com_jabuilder');
	}

	public function isEditable() {
		return $this->hasPermission() || defined('JUB_SITE_KEY');
	}

	public function inEditMode() {
		return $this->isEditable() && $this->jubMode() == 'edit';
	}

	public function outputJson ($data) {
		header('Content-type: application/json');
		echo json_encode($data);
	}

    private function decodeData ($data) {
    	$_data = rawUrlDecode(gzinflate(base64_decode($data)));
    	return json_decode($_data, true);
    }

    private function getDataInput () {
		$input = $this->app->input;
		if (!empty($input->request)) $input = $input->request;
		$data = $input->getRaw('data');
		return is_string($data) ? $this->decodeData ($data) : $data;
    }	

	public function getSiteid() {
		if (defined ('JUB_SITE_KEY')) return JUB_SITE_KEY;

		jimport('joomla.application.component.helper');
		// Load the current component params.
		$params = JComponentHelper::getParams('com_jabuilder');
		// Set new value of param(s)
		$siteid = $params->get('siteid', '');
		return $siteid;
	}

	public function getKey($type, $get_object = false) {
		static $rows = array();
		if (isset($rows[$type])) {
			$row = $rows[$type];
			return $get_object ?  $row: $row->slug;
		}

		$id = 0;
		$itemtype = null;
		if ($type == 'page') {
			if ($this->isJUBPage()) {
				$id = $this->app->input->getInt('id');
			} else {
				// get JUB content for this joomla page
				$input = JFactory::getApplication()->input;
				$itemtype = $input->getCmd('Itemid') . ":" . $input->getCmd('option') . ":" . $input->getCmd('view') . ':' . $input->getInt('id');
			}	
		} else if ($type == 'layout' && $this->isJUBLayout()) {
			$tpl = $this->app->getTemplate(true);

			$id = (int) $tpl->params->get('jub-layout');
			if (!$id) {
				// create and attach a layout
				$db = JFactory::getDbo();
				// insert new
				$row = new stdClass();
				$row->type = $type;
				$row->slug = $this->createSlug();
				$row->modified_date = date('Y-m-d h:i:s');
				$db->insertObject('#__jabuilder_pages', $row, 'id');

				$id = $row->id;
				// attach $id into template style
				$tpl->params->set('jub-layout', $id);
				$style = new stdClass();
				$style->id = $tpl->id;
				$style->params = $tpl->params->toString();
				$db->updateObject('#__template_styles', $style, 'id');

				// reget this item after created
				// $row = $this->getItem($id, $itemtype);
				$rows[$type] = $row;
				return $get_object ?  $row: $row->slug;
			}
		}

		if ($id || $itemtype) {
			$row = $this->getItem($id, $itemtype);
			if ($row) {
				if (!$row->slug) {
					$row->slug = $this->createSlug();
					$db->updateObject('#__jabuilder_pages', $row, 'id');
				}
				$rows[$type] = $row;
				return $get_object ?  $row: $row->slug;
			} else {
				// create item for page
				$db = JFactory::getDbo();
				// insert new
				$row = new stdClass();
				$row->type = $itemtype ? $itemtype : $type;
				$row->slug = $this->createSlug();
				$row->modified_date = date('Y-m-d h:i:s');
				if ($id) $row->id = $id;

				$db->insertObject('#__jabuilder_pages', $row, 'id');

				// reget this item after created
				// $row = $this->getItem($id, $itemtype);
				$rows[$type] = $row;
				return $get_object ?  $row: $row->slug;
			} 
		}

		return '';
	}

	public function doSaveSitekey () {
		if (defined('JUB_SITE_KEY')) return;
		
		$input = JFactory::getApplication()->input;
		$data = $this->getDataInput();
		if (!$data || !is_array($data) || !isset($data['site'])) {
			return '';
		}
		$siteid = $data['site'];

		jimport('joomla.application.component.helper');
		// Load the current component params.
		$params = JComponentHelper::getParams('com_jabuilder');
		// Set new value of param(s)
		$params->set('siteid', $siteid);
		// Save the parameters
		$componentid = JComponentHelper::getComponent('com_jabuilder')->id;
		$table = JTable::getInstance('extension');
		$table->load($componentid);
		$table->bind(array('params' => $params->toString()));

		// check for error
		if (!$table->check()) {
		    echo $table->getError();
		    return false;
		}
		// Save to database
		if (!$table->store()) {
		    echo $table->getError();
		    return false;
		}		
	}

	protected function getGlobalSettings () {
		jimport('joomla.application.component.helper');
		// Load the current component params.
		$params = JComponentHelper::getParams('com_jabuilder');
		// Set new value of param(s)
		$siteid = $params->get('siteid');
		return @json_decode($params->get('settings-' . $siteid), true);
	}

	protected function saveGlobalSettings ($settings) {

		jimport('joomla.application.component.helper');
		// Load the current component params.
		$params = JComponentHelper::getParams('com_jabuilder');
		// Set new value of param(s)
		// $siteid = $params->get('siteid');
		$siteid = $this->getSiteid();
		$current_settings_json = $params->get('settings-' . $siteid);
		$current_settings = json_decode($current_settings_json, true);

		// merge form
		$forms = isset($current_settings['forms']) && is_array($current_settings['forms']) ? $current_settings['forms'] : array();
		if (isset($settings['forms']) && is_array($settings['forms'])) $forms = array_merge($forms, $settings['forms']);
		$settings['forms'] = $forms;

		$settings_json = json_encode($settings);

		// settings not change
		if ($current_settings_json == $settings_json) return false;

		$params->set('settings-' . $siteid, $settings_json);
		// Save the parameters
		$componentid = JComponentHelper::getComponent('com_jabuilder')->id;
		$table = JTable::getInstance('extension');
		$table->load($componentid);
		$table->bind(array('params' => $params->toString()));

		// check for error
		if (!$table->check()) {
		    echo $table->getError();
		    return false;
		}
		// Save to database
		if (!$table->store()) {
		    echo $table->getError();
		    return false;
		}

		// check if less change
		$current_less_json = isset($current_settings['less']) ? json_encode($current_settings['less']) : '';
		$less_json = isset($settings['less']) ? json_encode($settings['less']) : '';

		return ($current_less_json != $less_json);	
	}

	public function doLoadConfig() {
		// load menus
		$menu = $this->app->getMenu();
		$items = $menu->getMenu();
		
		$config = array();
		$config['menu'] = array();

		foreach ($items as $item) {
			$menutype = $item->menutype;
			if (!isset($config['menu'][$menutype])) $config['menu'][$menutype] = array();
			$mitem = new stdClass();
			$mitem->id = $item->id;

			// get item link
			$link  = $item->link;
			// Reverted back for CMS version 2.5.6
			switch ($item->type)
			{
				case 'separator':
				case 'heading':
					// No further action needed.
					continue;

				case 'url':
					if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
					{
						// If this is an internal Joomla link, ensure the Itemid is set.
						$link = $item->link . '&Itemid=' . $item->id;
					}
					break;

				case 'alias':
					$link = 'index.php?Itemid=' . $item->params->get('aliasoptions');
					break;

				default:
					$link = 'index.php?Itemid=' . $item->id;
					break;
			}
			if (strcasecmp(substr($link, 0, 4), 'http') && (strpos($link, 'index.php?') !== false))
			{
				$link = JRoute::_($link, true, $item->params->get('secure'));
			}
			else
			{
				$link = JRoute::_($link);
			}
			$mitem->link = $link;

			$mitem->title = $item->title;
			$mitem->alias = $item->alias;
			$mitem->level = $item->level;
			$mitem->spacer = str_repeat('- ', $item->level);
			$mitem->parent = $item->parent_id;
			$config['menu'][$menutype][] = $mitem;

			// default page
			if ($item->home) {
				$config['home'] = $mitem;
			}
		}

		// get all module positions
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT position')
			->from($db->quoteName('#__modules'))
			->where ('client_id=0')
			->order('position');
		$config['positions'] = $db->setQuery($query)->loadColumn();
		// get all modules
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, module, title')
			->from($db->quoteName('#__modules'))
			->where ('client_id=0')
			->where ('published=1')
			->order('title');
		$config['modules'] = $db->setQuery($query)->loadObjectList();

		// load last revision
		$config['rev'] = array();
		$page = $this->getKey('page', true);

		$config['rev']['page'] = $page ? $db->setQuery ('Select max(rev) from #__jabuilder_revisions where itemid=' . (int)$page->id)->loadResult() : 0;

		$layout = $this->getKey('layout', true);
		$config['rev']['layout'] = $layout ? $db->setQuery ('Select max(rev) from #__jabuilder_revisions where itemid=' . (int)$layout->id)->loadResult() : 0;

		return $config;
		//$this->outputJson ($config);
	}

	public function doCreateRevision ($data = null) {
		if ($data === null) $data = $this->getDataInput();

		if (!$data || !is_array($data)) {
			return '';
		}

		$db = JFactory::getDbo();

		// build data
		$itemdata = null;
		if (isset($data['page']) && isset($data['layout'])) {
			$type = 'all';
			$itemdata = array('blocks' => array());
			// merge page data
			if (isset($data['page-data']['settings'])) $itemdata['settings'] = $data['page-data']['settings'];
			if (isset($data['page-data']['blocks'])) {
				foreach ($data['page-data']['blocks'] as $section => $blocks) {
					$itemdata['blocks'][$section] = $blocks;
				}
			}
			// merge layout data
			if (isset($data['layout-data']['blocks'])) {
				if (isset($data['layout-data']['blocks']['header'])) $itemdata['blocks']['header'] = $data['layout-data']['blocks']['header'];
				if (isset($data['layout-data']['blocks']['footer'])) $itemdata['blocks']['footer'] = $data['layout-data']['blocks']['footer'];
			}
			$type = 'all';
			$note = isset($data['page-note']) ? $data['page-note'] : '';
			$itemrev = 0;
		} else if (isset($data['page'])) {
			$itemdata = $data['page-data'];
			$type = 'page';
			$note = isset($data['page-note']) ? $data['page-note'] : '';
			$itemrev = $data['page'];
		} else if (isset($data['layout'])) {
			$itemdata = $data['layout-data'];
			$type = 'layout';
			$note = isset($data['layout-note']) ? $data['layout-note'] : '';
			$itemrev = $data['layout'];
		}

		if ($type == 'layout') {
			$layout = $this->getKey('layout', true);
			$itemid = $layout->id;
		} else {
			$page = $this->getKey('page', true);
			$itemid = $page->id;
		}

		$rev = new stdClass();
		$rev->rev = $itemrev;
		$rev->data = json_encode($itemdata);
		$rev->created = date('Y-m-d H:i:s');
		$rev->itemid = $itemid;
		$rev->note = $note;
		$rev->itemtype = $type;

		$db->insertObject('#__jabuilder_revisions', $rev);
		/*
		if (isset($data['page'])) {
			// create revision for page
			$page = $this->getKey('page', true);

			$rev = new stdClass();
			$rev->rev = $data['page'];
			$rev->data = json_encode($data['page-data']);
			$rev->created = date('Y-m-d H:i:s');
			$rev->itemid = $page->id;
			$rev->note = isset($data['page-note']) ? $data['page-note'] : '';
			$rev->itemtype = 'page';

			$db->insertObject('#__jabuilder_revisions', $rev);
		}

		if (isset($data['layout'])) {
			// create revision for page
			$layout = $this->getKey('layout', true);

			$rev = new stdClass();
			$rev->rev = $data['layout'];
			$rev->data = json_encode($data['layout-data']);
			$rev->created = date('Y-m-d H:i:s');
			$rev->itemid = $layout->id;
			$rev->note = isset($data['layout-note']) ? $data['layout-note'] : '';
			$rev->itemtype = 'layout';
			
			$db->insertObject('#__jabuilder_revisions', $rev);
		}
		*/
	}

	public function doLoadRevisions() {
		$db = JFactory::getDbo();
		$ids = array();
		$page = $this->getKey('page', true);
		if ($page) $ids[] = $page->id;
		$layout = $this->getKey('layout', true);
		if ($layout) $ids[] = $layout->id;

		$query = $db->getQuery(true);
		$query->select('*')->from('#__jabuilder_revisions')
			->where('itemid in (' . implode(', ', $ids) . ')')
			->order('id desc')
			->limit((int) $this->params->get('max_revisions_load', 100));
		$revisions = $db->setQuery($query)->loadObjectList();

		if ($revisions) {
			for ($i=0; $i<count($revisions); $i++) {
				$revisions[$i]->data = json_decode($revisions[$i]->data, true);
			}
		}

		return $revisions;
	}

	public function doGetContent () {
		$input = JFactory::getApplication()->input;
		$data = $this->getDataInput();

		if (!$data || !is_array($data) || !isset($data['type'])) {
			return '';
		}

		$doc = JFactory::getDocument();
		$oldStyleSheets = $doc->_styleSheets;
		$content = null;
		switch ($data['type']) {

			case 'module': 
				if (!isset($data['modid']) || !isset($data['modname']) || !isset($data['modtitle']) || !$data['modid']) {
					echo "Empty";
					return;
				}
				$data['title'] = $data['modtitle'];
				$data['id'] = $data['modid'];
				
				$content = $doc->getBuffer($data['type'], $data['modname'], $data);
				break;
			case 'position':
				if (!isset($data['position']) || !$data['position']) {
					echo "Empty";
					return;
				}
				$position = $data['position'];
				$content = $doc->getBuffer('modules', $position, $data);
				break;
		}

		// detect new css files
		$newStyleSheets = array_slice($doc->_styleSheets, count($oldStyleSheets));
		// preprocess to ignore some loaded
		$urls = array();
		foreach($newStyleSheets as $url => $css) {
			if (preg_match('/(font-awesome|bootstrap)/', $url)) continue;
			$urls[] = $url;
		}

		$result = array();
		$result['content'] = $content;
		$result['styleSheets'] = $urls;
		$this->outputJson($result);
	}

	public function doPublishData() {
		$input = JFactory::getApplication()->input;
		$data = $this->getDataInput();
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';
		$pagekey = '';
		// save main data
		if ($this->isJUBPage()) {
			$id = $this->app->input->getInt('id');
			$item = $this->getItem($id);
			$row = new stdClass();
			$row->id = $id;
			// update content
			if (isset($data['html']) && isset($data['html']['content'])) {
				$row->content = $data['html']['content'];
				unset($data['html']['content']);
			} else {
				$row->content = '';
			}

			// page data
			$_data = array();
			// assets
			if (isset($data['assets']['page'])) $_data['assets'] = $data['assets']['page'];
			// setting
			if (isset($data['page-settings'])) {
				$_data['settings'] = $data['page-settings'];
			}

			$row->data = json_encode($_data);

			$this->saveItem($row);

			$pagekey = $item->slug;
		} else {
			// get JUB content for this joomla page
			$input = JFactory::getApplication()->input;
			$itemtype = $input->getCmd('Itemid') . ":" . $input->getCmd('option') . ":" . $input->getCmd('view') . ':' . $input->getInt('id');
			$item = $this->getItem(null, $itemtype);

			// save page data for joomla page
			if ($item) {
				$row = new stdClass();
				$row->id = $item->id;
				$contents = array();
				if (isset($data['html']) && isset($data['html']['top'])) {
					$contents['top'] = $data['html']['top'];
					unset($data['html']['top']);
				}
				if (isset($data['html']) && isset($data['html']['bottom'])) {
					$contents['bottom'] = $data['html']['bottom'];
					unset($data['html']['bottom']);
				}
				$row->content = json_encode($contents);

				// page data
				$_data = array();
				// assets
				if (isset($data['assets']['page'])) $_data['assets'] = $data['assets']['page'];
				// setting
				if (isset($data['page-settings'])) {
					$_data['settings'] = $data['page-settings'];
				}
		
				$row->data = json_encode($_data);

				$this->saveItem($row);

				$pagekey = $item->slug;
			}
		}		
		
		// save layout data
		$layoutkey = null;
		if ($this->isJUBLayout()) {
			$tpl = $this->app->getTemplate(true);
			$lid = (int) $tpl->params->get('jub-layout');
			$item = $this->getItem($lid);
			// update content
			$content = json_decode($item->content, true);
			$content['header'] = isset($data['html']) && isset($data['html']['header']) ? $data['html']['header'] : '';
			$content['footer'] = isset($data['html']) && isset($data['html']['footer']) ? $data['html']['footer'] : '';
			if (!$this->isJUBPage()) {
				$content['content'] = isset($data['html']) && isset($data['html']['content']) ? $data['html']['content'] : '';
			}

			$row = new stdClass();
			$row->id = $item->id;
			$row->content = json_encode($content);

			// page data
			$_data = array();
			// assets
			if (isset($data['assets']['layout'])) $_data['assets'] = $data['assets']['layout'];

			$row->data = json_encode($_data);

			$this->saveItem($row);

			$layoutkey = $item->slug;
		}

		// save global settings
		// setting
		$globalLessChange = false;
		if (isset($data['global-settings'])) {
			$globalLessChange = $this->saveGlobalSettings ($data['global-settings']);
		}


		// sync assets
		$static = $data['assets']['static'];
		if (is_array($static)) {
			foreach ($static as $path => $url) {
				$path = JPATH_ROOT . '/' . $path;
				if (is_file($path)) continue;
				if (!preg_match('/^(https?:)?\/\//', $url)) {
					$url = JUri::root() . $url;
				}
				if (preg_match('/^\/\//', $url)) $url = 'http:' . $url;
				// get asset content
				$content = $this->getRemoteFile ($url);
				
				if ($content) {
					JFile::write(JPath::check($path), $content);
				}
				// $this->syncRemoteFile ($url, $path);
			}			
		}
		

		// save css
		if ($pagekey || $layoutkey) {
			$css = $data['css'];
			try {
				// minify
				$minifier = new MatthiasMullie\Minify\CSS();
				$minifier->add($css);
				$css = $minifier->minify();
				// write to file
				if ($pagekey) {
					$devcss = JPATH_ROOT . '/media/jub/dev/' . $site . '/css/' . $pagekey . '.css';
					$livecss = JPATH_ROOT . '/media/jub/' . $site . '/css/' . $pagekey . '.css';
					JFile::write(JPath::check($livecss), $css);
					JFile::write(JPath::check($devcss), $css);						
				}
				if ($layoutkey) {
					$devcss = JPATH_ROOT . '/media/jub/dev/' . $site . '/css/' . $layoutkey . '.css';
					$livecss = JPATH_ROOT . '/media/jub/' . $site . '/css/' . $layoutkey . '.css';
					JFile::write(JPath::check($livecss), $css);
					JFile::write(JPath::check($devcss), $css);						
				}
			} catch (Exception $e) {}

			// update other css if less change
			if ($globalLessChange) {
				// get global css
				$regex = '/(\.jub\-block\.[0-9a-z_\-]+\-\d)/i';
				$arr = preg_split($regex, $css, 2);
				$global_css = $arr[0];
				// update css in all pages
				$path = JPATH_ROOT . '/media/jub/' . $site . '/css/';
				$files = glob($path . '*.css');
				foreach ($files as $file) {
					$name = basename($file, '.css');
					if ($name == $pagekey || $name == $layoutkey) continue;
					$_css = file_get_contents($file);
					$_arr = preg_split($regex, $_css, 2, PREG_SPLIT_DELIM_CAPTURE);
					$_css = $global_css . (count($_arr) > 2 ? $_arr[1] . $_arr[2] : '');
					JFile::write(JPath::check($file), $_css);
				}
			}
		}

		// create revision if need
		if ($data['rev']) {
			$this->doCreateRevision ($data['rev']);
		}
/*
		// create revision for publish
		$page = $this->getKey('page', true);

		$rev = new stdClass();
		$rev->rev = 0;
		$rev->data = json_encode($this->decodeData($data['raw']));
		$rev->created = date('Y-m-d H:i:s');
		$rev->itemid = $page->id;
		$rev->note = 'Publish Page';
		$rev->itemtype = 'all';

		$db = JFactory::getDbo();
		$db->insertObject('#__jabuilder_revisions', $rev);
*/

		return 'ok';
	}

	public function doSaveCss() {
		
		$data = $this->getDataInput();
		$input = $this->app->input;
		if (!empty($input->request)) $input = $input->request;
		$published = (boolean)$input->get('published');
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';

		foreach ($data as $key => $css) {
			// save css 
			$cssfile = JPATH_ROOT . '/media/jub/dev/' . $site . '/css/' . $key . '.css';
			JFile::write(JPath::check($cssfile), $css);
			if ($published) {
				// minify
				$minifier = new MatthiasMullie\Minify\CSS();
				$minifier->add($css);
				$css = $minifier->minify();
				// publish css file
				$livecss = JPATH_ROOT . '/media/jub/' . $site . '/css/' . $key . '.css';
				JFile::write(JPath::check($livecss), $css);
			}
		}

		return 'ok';
	}

	private function remotefilemtime($url){
	    $ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_FILETIME, TRUE);

		$data = curl_exec($ch);
		$filetime = curl_getinfo($ch, CURLINFO_FILETIME);

		curl_close($ch);
		return $filetime;
	}

	private function getRemoteFile($url)
	{
		$config = JFactory::getConfig();
		// Capture PHP errors
		$track_errors = ini_get('track_errors');
		ini_set('track_errors', true);

		// Set user agent
		$version = new JVersion;
		ini_set('user_agent', $version->getUserAgent('Installer'));

		$headers = array();

		try
		{
			$response = JHttpFactory::getHttp()->get($url, $headers);
		}
		catch (RuntimeException $exception)
		{
			$error = $exception->getMessage();
			if (preg_match('/ssl/i', $error)) {
				// ssl error, try with non-ssl
				$url = preg_replace ('/^https/', 'http', $url);
				return $this->getRemoteFile($url);
			}
			return false;
		}

		if (302 == $response->code && isset($response->headers['Location']))
		{
			return $this->getRemoteFile($response->headers['Location']);
		}
		elseif (200 != $response->code)
		{
			return false;
		}

		return $response->body;
	}	

	public function doSyncAssets() {
		jimport('joomla.filesystem.path');

		$input = $this->app->input;
		$data = $this->getDataInput();
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';
		$assetPath = JPATH_ROOT . '/media/jub/assets/';
		
		$assets = isset($data['css']) ? $data['css'] : array();
		$assets = isset($data['js']) ? array_merge($assets, $data['js']) : $assets;
		$assets = isset($data['file']) ? array_merge($assets, $data['file']) : $assets;

		$syncFails = array();
		foreach ($assets as $asset) {
			if (!preg_match('/^(https?:)?\/\//', $asset)) {
				$path = $assetPath . $asset;
				$url = JUB_BUILDER_URL . '/' . $asset;
				if ($this->syncRemoteFile ($url, $path) === false) {
					$syncFails[$asset] = is_file ($path) ? filemtime($path) : 0;
				}
			}
		}

		if (count($syncFails)) {
			return json_encode($syncFails);
		}

		return 'ok';
	}

	public function doSyncAssets2() {
		$input = $this->app->input;
		$data = $this->getDataInput();
		$assetPath = JPATH_ROOT . '/media/jub/assets/';
		$errors = array();

		if (is_array($data) && isset($data['assets']) && is_array($data['assets'])) {
			jimport('joomla.filesystem.file');
			foreach ($data['assets'] as $asset => $content) {
				// check if need decode
				if (!preg_match('/\.(css|js)$/', $asset)) {
					$content = base64_decode ($content);
				}
				$path = JPath::check($assetPath . $asset, '/');
				// accept only static file types
				if (!$this->isSafefile($path)) continue;
				try {
					JFile::write ($path, $content);
				} catch (Exception $e) {
					// cannot write
					$errors[] = $e->getMessage();
				}
			}
		}

		return json_encode($errors);
	}

	protected function isSafefile ($file) {
		$regex = '/\.(css|js|jpg|jpeg|gif|ico|png|bmp|pict|csv|pdf|pls|ppt|tif|tiff|eps|ejs|swf|midi|mid|ttf|eot|woff|otf|svg|svgz|webp|woff2)$/i';
		if (preg_match($regex, $file)) return true;
		return false;
	}

	protected function syncRemoteFile ($url, $path) {
		jimport('joomla.http');

		$path = JPath::check($path);
		// accept only static file types
		if (!$this->isSafefile($path)) return true;
		
		static $http = null;
		if (!$http) {
			$http = new JHttp();
		}
		
		$modifiedtime = $this->remotefilemtime($url);		
		if ($modifiedtime < 0) {
			return false;
		}

		if (!is_file($path) || filemtime($path) < $modifiedtime) {
			try {
				$content = $http->get($url)->body;
				JFile::write ($path, $content);
			} catch (Exception $e) {
				return false;
			}
			return true;
		}

		return true;
	}

	public function doUploadImage () {
		jimport('joomla.filesystem.file');
		$imgTypes = array('png', 'jpg', 'jpeg', 'gif', 'ico', 'bmp', 'svg', 'pict');

		$input = $this->app->input;
		$data = $this->getDataInput();
		
		// validate content		
		if (!preg_match('/data:image\/([^;]*);base64,(.*)$/', $data['content'], $match)) {
			// not valid image data
			return 'not-valid-image-data';
		}
		$type = strtolower($match[1]);
		$content = base64_decode($match[2]);
		if (!in_array($type, $imgTypes)) {
			// not support image type
			return 'not-support-image-type [' . $type . ']';
		}
		// make file name safe
		$ext = JFile::getExt($data['name']);
		if (!in_array(strtolower($ext), $imgTypes)) $ext = $type;
		$name = preg_replace('/[\.\s]/', '-', JFile::stripExt($data['name'])) . '.' . $ext;

		// image max size
		$max_size = $this->getUploadSize();
		if (strlen($content) > 1024*1024*$max_size) {
			// image oversize
			return 'oversize: ' . strlen($content);
		}
		// save image
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';
		$path = 'media/jub/images/' . $site . '/' . $name;
		$fullpath = JPATH_ROOT . '/' . $path;

		JFile::write(JPath::check($fullpath), $content);
		/*
		if (!is_dir(dirname($fullpath))) {
			@mkdir(dirname($fullpath), 0755, true);
		}
		file_put_contents($fullpath, $content);
		*/
		return $path;
	}

	public function getUploadSize () {
		$max_size = (float) $this->params->get('upload_max_size', 1);
		if (!$max_size) $max_size = 1;
		return $max_size;
	}

	private function check_duplicated_alias($alias, $id, $table)
	{
		$db = JFactory::getDbo();
		$check_alias = $alias;
		$i = 1;
		while (1) {
			$query = $db->getQuery(true);
			$query->select( $db->quoteName('alias') )
					->from( $db->quoteName($table) )
					->where( $db->quoteName('alias').'='.$db->quote($check_alias) )
					->where( $db->quoteName('id').'!='.$id );
			$db->setQuery($query);
			$db->execute();
			if (!$db->getNumRows()) return $check_alias;
			$check_alias = $alias . '-' . $i++;
		}
	}

	
	protected function createSlug () {
		$slug = null;
		while (!$slug) {
			$slug = substr(md5(uniqid(rand(10000,99999), true)), 0, 13);
			if ($this->slugExisted ($slug)) $slug = null;
		}
		return $slug;
	}

	protected function slugExisted ($slug) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select ('id')
				->from($db->quoteName('#__jabuilder_pages'))
				->where($db->quoteName('slug').'='.$db->quote($slug));
		$db->setQuery($query);
		return $db->loadResult();
	}


	public function doCreatePage() {
		require_once (JPATH_ROOT . '/administrator/components/com_jabuilder/helpers/jabuilder.php');
		if (!$this->hasPermission()) {
			die ('Only Super User can create new page!');
		}

		$input = JFactory::getApplication()->input;	
		$data = $this->getDataInput();

		// create new page
		// create new JUB Page
		$db = JFactory::getDbo();
		// insert new
		$row = new stdClass();
		$row->title = $data['title'];
		$row->alias = $this->check_duplicated_alias (JabuilderHeper::stringUrlsafe($row->title), 0, '#__jabuilder_pages');

		$row->type = 'page';
		$row->state = 1;
 		$row->slug = isset($data['item_key']) ? $data['item_key'] : $this->createSlug();
		$row->modified_date = date('Y-m-d h:i:s');
		$db->insertObject('#__jabuilder_pages', $row, 'id');

		// create new menu item
		// get parent menu item
		// default parent to root
		if (!isset($data['parent']) || !$data['parent']) $data['parent'] = 1; // root
		if (!isset($data['ordering'])) $data['ordering'] = -2; // last
		$menu = JFactory::getApplication()->getMenu();

		$parent = $menu->getItem($data['parent']);
		$order = ($data['ordering'] > 0) ? $menu->getItem($data['ordering']) : $data['ordering'];

		$mitem = array();
		$mitem['menutype'] = $data['menutype'];
		$mitem['title'] = $data['title'];
		$mitem['alias'] = $this->check_duplicated_alias ($row->alias, 0, '#__menu');
		// $mitem['alias'] = preg_replace('/[^a-z0-9]/i', '-', strtolower($data['title']));
		$mitem['link'] = "index.php?option=com_jabuilder&view=page&id={$row->id}";
		$mitem['type'] = 'component';
		$mitem['parent_id'] = $data['parent'];
		$mitem['level'] = $parent ? $parent->level + 1 : 1;
		//$mitem['menuordering'] = $data['ordering'];
		$mitem['component_id'] = JComponentHelper::getComponent('com_jabuilder')->id;
		$mitem['access'] = 1;
		$mitem['published'] = 1;
		$mitem['client_id'] = 0;
		$mitem['language'] = '*';

		// Get a row instance.
		$table = new JTableMenu ($db);

		if ($data['ordering'] == -1)
		{
			$table->setLocation($data['parent'], 'first-child');
		}
		elseif ($data['ordering'] > 0 && $table->id != $data['ordering'])
		{
			$table->setLocation($data['ordering'], 'after');
		}
		// Just leave it where it is if no change is made.
		else
		{
			$table->setLocation($data['parent'], 'last-child');			
		}

		// Bind the data.
		if (!$table->bind($mitem))
		{
			echo $table->getError();
			return false;
		}

		if (!$table->store()) {
			echo $table->getError();
		}

		// reload 
		$table->load($table->id);

		return JUri::root(true) . '/index.php?option=com_jabuilder&view=page&id=' . $row->id . '&Itemid=' . $table->id;
	}

	public function getItem($id, $type = null) {
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		
		$query	->clear()
				->select('*')
				->from($db->quoteName('#__jabuilder_pages'));
		if ($id) {
			$query->where($db->quoteName('id').'='.(int) $id);
		} else if ($type) {
			$query->where($db->quoteName('type').'='.$db->quote($type));
		}
		
		$db->setQuery($query);

		$item = $db->loadObject();
		if ($item) {
			$this->trigger('onJubLoadItem', $item);			
		}

		return $item;
	}

	public function saveItem($item) {
		$this->trigger('onJubSaveItem', $item);
		if (isset($item->modified_date)) $item->modified_date = date('Y-m-d H:i:s');		
		$db = JFactory::getDbo();
		$db->updateObject('#__jabuilder_pages', $item, 'id');
	}

	public function trigger($event, &$item) {
		$dispatcher = JEventDispatcher::getInstance();
		// JPluginHelper::importPlugin('system');
		$dispatcher->trigger($event, array (&$item));
	}

	protected function addJUBAssets($item) {
		if (!$item) return ;
		$doc = JFactory::getDocument();
		$assetUrl = JUri::root(true) . '/media/jub/assets/';
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';

		$key = $item->slug;
		$data = json_decode($item->data, true);
		
		// add assets
		if ($data && isset($data['assets'])) {
			// css		
			if (isset($data['assets']['css'])) {
				foreach ($data['assets']['css'] as $url) {
					$url = preg_match('/^https?:/', $url) ? $url : $assetUrl . $url;
					$doc->addStyleSheet ($url);
				}
			}
			// js
			if (isset($data['assets']['js'])) {
				foreach ($data['assets']['js'] as $url) {
					if ($this->params->get('nobootstrap') && preg_match('/\/bootstrap(\.min)?\.js/', $url)) continue;
					$url = preg_match('/^https?:/', $url) ? $url : $assetUrl . $url;
					$doc->addScript ($url);
				}
			}
		}

		if (isset($data['settings'])) $this->addJUBAssetsFromSettings ($data['settings']);

		$pagecss = '/media/jub/' . $site . '/css/' . $key . '.css';
		return (is_file(JPATH_ROOT . $pagecss)) ? $pagecss : null;
	}


	protected function addJUBAssetsFromSettings($fromsettings) {
		if (!is_array($fromsettings)) return;
		// add custom code
		if (isset($fromsettings['custom-code'])) {
			$settings = $fromsettings['custom-code'];
			$regex = '/^__@__/';
			if (isset($settings['page-top-head']) && !preg_match ($regex, $settings['page-top-head'])) $this->cs_top_head .= $settings['page-top-head'] . "\n";
			if (isset($settings['page-bot-head']) && !preg_match ($regex, $settings['page-bot-head'])) $this->cs_bot_head .= $settings['page-bot-head'] . "\n";
			if (isset($settings['page-top-body']) && !preg_match ($regex, $settings['page-top-body'])) $this->cs_top_body .= $settings['page-top-body'] . "\n";
			if (isset($settings['page-bot-body']) && !preg_match ($regex, $settings['page-bot-body'])) $this->cs_bot_body .= $settings['page-bot-body'] . "\n";
			if (isset($settings['site-top-head']) && !preg_match ($regex, $settings['site-top-head'])) $this->cs_top_head .= $settings['site-top-head'] . "\n";
			if (isset($settings['site-bot-head']) && !preg_match ($regex, $settings['site-bot-head'])) $this->cs_bot_head .= $settings['site-bot-head'] . "\n";
			if (isset($settings['site-top-body']) && !preg_match ($regex, $settings['site-top-body'])) $this->cs_top_body .= $settings['site-top-body'] . "\n";
			if (isset($settings['site-bot-body']) && !preg_match ($regex, $settings['site-bot-body'])) $this->cs_bot_body .= $settings['site-bot-body'] . "\n";
			// append custom style
			if (isset($settings['page-custom-css']) && !preg_match ($regex, $settings['page-custom-css'])) $this->cs_bot_head .= '<style type="text/css">' . $settings['page-custom-css'] . "</style>\n";
			if (isset($settings['site-custom-css']) && !preg_match ($regex, $settings['site-custom-css'])) $this->cs_bot_head .= '<style type="text/css">' . $settings['site-custom-css'] . "</style>\n";
		}

		// add web fonts
		if (isset($fromsettings['webfonts']) && count($fromsettings['webfonts'])) {
			$webfonts = '';
			$webfontCls = '';
			$fonts = isset($fromsettings['webfonts']['fonts']) ? $fromsettings['webfonts']['fonts'] : $fromsettings['webfonts'];
			$subsets = isset($fromsettings['webfonts']['subsets']) ? $fromsettings['webfonts']['subsets'] : null;
			
			foreach ($fonts as $name => $font) {
				$webfonts .= str_replace(' ', '+', $name);
				if (!isset($font['styles'])) $font['styles'] = array();
				$selectedStyles = isset($font['selectedStyles']) ? $font['selectedStyles'] : $font['styles'];
				if (is_array($font['styles']) && count($font['styles']) > 1
					&& is_array($selectedStyles) && count($selectedStyles)) {
					$webfonts .= ':' . implode(',', $selectedStyles);
				}
				$webfonts .= '|';
				$webfontCls .= '.f-' . str_replace(' ', '-', strtolower($name)) . '{font-family:\'' . $name . '\';}';
			}
			$webfonts = substr($webfonts, 0, -1);
			// add subsets
			if ($subsets && count($subsets)) {
				$webfonts .= '&amp;subset=' . implode(',', $subsets);
			}

			$this->cs_bot_head .= '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=' . $webfonts . "\" />\n";
			$this->cs_bot_head .= '<style type="text/css">' . $webfontCls . "</style>\n";
		}
	}

	public function addJUBPageAssets() {
		// add jub assets
		JHtml::_('jquery.framework');

		$doc = JFactory::getDocument();
		$assetUrl = JUri::root(true) . '/media/jub/assets/';
		$site = defined('JUB_SITE_KEY') ? JUB_SITE_KEY : 'default';
		// core bootstrap
		if (!$this->params->get('nobootstrap')) {
			$url = $assetUrl . '/css/bootstrap-core.min.css';
			$doc->addStyleSheet ($url);
		}

		// add setting from global assets
		$this->addJUBAssetsFromSettings ($this->getGlobalSettings());

		$pagecss = null; // decide which key will be used to add css file
		if ($this->isJUBLayout()) {
			$tpl = $this->app->getTemplate(true);
			$lid = (int) $tpl->params->get('jub-layout');
			$item = $this->getItem($lid);
			$content = @json_decode($item->content, true);
			if (is_array($content) && ((isset($content['header']) && trim($content['header']) != '') || (isset($content['footer']) && trim($content['footer']) != ''))) {
				$_pagecss = $this->addJUBAssets($item);
				if ($_pagecss) $pagecss = $_pagecss;
			}

			// 
			if (!$this->isJUBPage()) {
				$input = $this->app->input;
				$itemtype = $input->getCmd('Itemid') . ":" . $input->getCmd('option') . ":" . $input->getCmd('view') . ':' . $input->getInt('id');
				$item = $this->getItem(null, $itemtype);
				$content = @json_decode($item->content, true);

				if (is_array($content) && ((isset($content['top']) && trim($content['top']) != '') || (isset($content['bottom']) && trim($content['bottom']) != ''))) {
					$_pagecss = $this->addJUBAssets($item);
					if ($_pagecss) $pagecss = $_pagecss;
				}
			}
		}

		if ($this->isJUBPage()) {
			if (isset($doc->jubitem) && $doc->jubitem) {
				$item = $doc->jubitem;
				$content = trim($item->content);
				if ($content) {
					$_pagecss = $this->addJUBAssets($item);
					if ($_pagecss) $pagecss = $_pagecss;
				}
			}
		}

		// compiled css
		if ($pagecss) {
			$doc->addStyleSheet (JUri::root(true). $pagecss);
		}		
	}


	public function addCustomCode () {
		$places   = array();
		$contents = array();

		if ($this->cs_top_head) {
			$places[] = '/<head>/i';	//not sure that any attritube can be place in head open tag, profile is not support in html5
			$contents[] = "<head>\n" . $this->cs_top_head;
		}
		if ($this->cs_bot_head) {
			$places[] = '/<\/head>/i';
			$contents[] = $this->cs_bot_head . "\n</head>";
		}
		if ($this->cs_top_body) {
			$places[] = '/<body([^>]*)>/i';
			$contents[] = "<body$1>\n" . $this->cs_top_body;
		}
		if ($this->cs_bot_body) {
			$places[] = '/<\/body>/i';
			$contents[] = $this->cs_bot_body . "\n</body>";
		}

		if (count($places)) {			
			$body = JResponse::getBody();
			$body = preg_replace($places, $contents, $body);

			JResponse::setBody($body);
		}			
	}


	public function parseJUBPage () {
		// get JUB Page content
		$doc = JFactory::getDocument();
		$content = $doc->getBuffer('component');
		if (preg_match_all('#<jdoc:include\ type="([^"]+)"(.*)\/>#iU', $content, $matches))
		{
			$template_tags = array();
			// Step through the jdocs in reverse order.
			for ($i = count($matches[0]) - 1; $i >= 0; $i--)
			{
				$type = $matches[1][$i];
				$attribs = empty($matches[2][$i]) ? array() : JUtility::parseAttributes($matches[2][$i]);

				// update name, title for module type if id available
				if ($type == 'id' && isset($attribs['id'])) {
					$db = JFactory::getDbo();
					$modid = (int) $attribs['id'];
					$query = $db->getQuery(true)
						->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params')
						->from('#__modules AS m')
						->where('m.id = ' . $modid)
						->where('m.published = 1');
					$module = $db->setQuery($query)->loadObject();
					if ($module) {
						$attribs['name'] = $module->module;
						$attribs['title'] = $module->title;
					}
				}

				$name = isset($attribs['name']) ? $attribs['name'] : null;

				$template_tags[$matches[0][$i]] = array('type' => $type, 'name' => $name, 'attribs' => $attribs);

			}

			// render 
			$replace = array();
			$with = array();

			foreach ($template_tags as $jdoc => $args)
			{
				$replace[] = $jdoc;
				$with[] = $doc->getBuffer($args['type'], $args['name'], $args['attribs']);
			}

			$content = str_replace($replace, $with, $content);
		}
		$doc->setBuffer($content, 'component');
	}

	public function getVersion () {
		if (defined('JUB_DEV')) return 'dev';
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('manifest_cache')
			->from('#__extensions')
			->where('element=' . $db->quote('pkg_ja_builder'))
			->where('type=' . $db->quote('package'));
		$manifest = $db->setQuery($query)->loadResult();

		if ($manifest) {
			$info = json_decode($manifest, true);
			if (is_array($info) && isset($info['version'])) return $info['version'];
		}

		return '';
	}


	public function doUserForm () {
		$settings = $this->getGlobalSettings();
		$forms = isset($settings['forms']) && is_array($settings['forms']) ? $settings['forms'] : array();

		// form submitted
		$post = JFactory::getApplication()->input->post;

		$formid = $post->get('formid');
		if (!$formid || !isset($forms[$formid])) return array('error' => 'Form not found!');;

		$form = $forms[$formid];

		// verify captcha
		if (isset($form['captcha_sitekey']) && $form['captcha_sitekey']) {

			$gresponse = $post->getRaw('g-recaptcha-response');
			$post_data = http_build_query(
			    array(
			        'secret' => $form['captcha_secret'],
			        'response' => $gresponse,
			        'remoteip' => $_SERVER['REMOTE_ADDR']
			    )
			);
			$opts = array('http' =>
			    array(
			        'method'  => 'POST',
			        'header'  => 'Content-type: application/x-www-form-urlencoded',
			        'content' => $post_data
			    )
			);
			$context  = stream_context_create($opts);
			$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
			$result = json_decode($response);
			if (!$result->success) {
			    return array('error' => 'Cannot verify captcha!');
			}
			
		}

		// verify consent
		if ($form['consent'] && !$post->get('consent')) {
			return array('error' => 'Cannot verify consent!');
		}

		// get data
		$items = array();
		foreach ($form['fields'] as $f) {
			$f['value'] = $post->getRaw($f['name']);
			$items[] = $f;
		}

		$content = JLayoutHelper::render('jabuilder.mail', array('items' => $items, 'title' => $form['title']), __DIR__ . '/html');

		$subject = $form['title'];

		$output = array ('ok' => 1);
		
		// get mail config
		$config = JFactory::getConfig();
		$mailfrom = $config->get('mailfrom');
		$fromname = $config->get('fromname');
		$sitename = $config->get('sitename');

		// get receivers
		$mailto = isset($form['receivers']) && $form['receivers'] ? $form['receivers'] : $this->params->get('receivers');
		
		if ($mailto) $mailto = explode(',', $mailto);
		else $mailto = $mailfrom;

		// send mail
		$mail = JFactory::getMailer();
		$mail->addRecipient($mailto);
		//$mail->addReplyTo($email, $name);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': ' . $subject);
		$mail->isHtml (true);
		$mail->setBody($content);
		$sent = $mail->Send();

		if ($sent !== true) {
			return array('error' => 'Cannot send email!');
		}

		return $output;
	}

	public function checkRevisionsTableExisted () {
		$db = JFactory::getDbo();
		$existed = FALSE;
		try {
			$existed = $db->setQuery('select 1 from #__jabuilder_revisions')->loadResult();
		} catch (Exception $e) {}

		if ($existed === FALSE) {
			$query = '
				CREATE TABLE IF NOT EXISTS `#__jabuilder_revisions` (
				  `id` int(20) NOT NULL AUTO_INCREMENT,
				  `itemid` int(20) NOT NULL DEFAULT \'0\',
				  `itemtype` varchar(10) NOT NULL,
				  `data` mediumtext NOT NULL,
				  `rev` int(11) NOT NULL DEFAULT \'0\',
				  `created` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
				  `note` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				)';
			$db->setQuery($query)->execute();
		}
	}

}