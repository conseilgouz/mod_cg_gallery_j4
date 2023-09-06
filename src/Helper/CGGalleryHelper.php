<?php
/**
* CG Gallery - Joomla Module 
* Version			: 2.3.5
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
namespace ConseilGouz\Module\CGGallery\Site\Helper;
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Content\Site\Model\ArticleModel;
use Joomla\Component\Content\Site\Helper\RouteHelper as ContentHelperRoute;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Version;

class CGGalleryHelper
{

	public static function getFolder($dir)
	{
		$input = Factory::getApplication()->input;
		$catid = 0;
		$articleid = 0;
		$artalias = '';
		$catalias = '';
		$articlealias = '';
		if ($input->getString('view') == 'category') {
			$catid = $input->getInt('id');
			$catalias = self::getCatAlias($catid)->alias;
		}
		if ($input->getString('view') == 'article') { // on est sur l'affichage d'un article
			$catid = $input->getInt('catid');
			$articleid = $input->getInt('id');
			$res = self::getArticleInfos($articleid);
			$articlealias = $res->alias;
			$catid = $res->catid;
			$catalias = self::getCatAlias($catid)->alias;
		}
		$root = $dir;
		$pattern = array('/\$catid/','/\$catalias/', '/\$articleid/', '/\$articlealias/');
		$replace = array($catid, $catalias, $articleid,$articlealias);
		$root = preg_replace($pattern, $replace, $root);
		if (strpos($root,'$') !== false) { // répertoire incorrect: il reste des zones 
			return false;
		}
		$root = Path::clean($root,'/');
		if(!is_dir($root) ) { // le répertoire n'existe pas : on crée
			Folder::create($root,755);
		}
		return $root; 
	}
	static function getArticleInfos($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('alias,catid')
			->from('#__content')
			->where(' id = ' .$id);
		$db->setQuery($query);
		return $db->loadObject();
	}
	static function getCatAlias($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('alias')
			->from('#__categories')
			->where(' id = ' .$id);
		$db->setQuery($query);
		return $db->loadObject();
	}
	static function getArticle(&$item, $params) {
		
		$j = new Version();
		$version=substr($j->getShortVersion(), 0,1); 
		$model     = new ArticleModel(array('ignore_request' => true));
		if (!$model) {return false;}
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$params= $appParams;
		$model->setState('params', $appParams);
		
		$model->setState('list.start', 0);
		$model->setState('list.limit', 1);
		// $model->setState('filter.published', 1);
		$model->setState('filter.featured', 'show');
		$model->setState('filter.category_id', array());
		
		// Access filter
		$access =ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$model->setState('filter.access', $access);
		
		// Filter by language
		// $model->setState('filter.language', $app->getLanguageFilter());
		
		try{
		  $onearticle = $model->getItem($item->slidearticleid);
		} catch (Exception $e) {
		    return false;
		}
		$item->article = $onearticle;
		$item->article->text = $onearticle->introtext;
		$item->article->text = self::truncate($item->article->text, $params->get('ug_text_lgth', '100'), true, false);
		// $item->article->text = JHTML::_('string.truncate',$item->article->introtext,'150');
		// set the item link to the article depending on the user rights
		if ($access || in_array($item->article->access, $authorised)) {
			// We know that user has the privilege to view the article
			$item->slug = $item->article->id . ':' . $item->article->alias;
			$item->catslug = $item->article->catid ? $item->article->catid . ':' . $item->article->category_alias : $item->article->catid;
			$link = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
		} else {
			$app = Factory::getApplication();
			$menu = $app->getMenu();
			$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');
			if (isset($menuitems[0])) {
				$Itemid = $menuitems[0]->id;
			} elseif (Factory::getApplication()->input::getInt('Itemid') > 0) {
				$Itemid = Factory::getApplication()->input::getInt('Itemid');
			}
			$link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
		}
		return $link;
	}
	static function getArticleK2(&$item, $params) {
		require_once(JPATH_SITE.'/components/com_k2/helpers/route.php');
	// Access filter
        $app = Factory::getApplication();
        $db = Factory::getDbo();
		$query = "SELECT i.* from #__k2_items AS i WHERE i.id = {$item->file_id}";
        $db->setQuery($query);
        $item2 = $db->loadObject();
        $link = urldecode(Route::_(K2HelperRoute::getItemRoute($item2->id.':'.urlencode($item2->alias), $item2->catid.':'.urlencode($item2->categoryalias))));
		return $link;
	}
	
	/**
	 * Truncates text blocks over the specified character limit and closes
	 */
    public static function truncate($html, $maxLength = 0, $noSplit = true, $allowHtml = true)
    {
	        $baseLength = strlen($html);
	        $ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
	        for ($maxLength; $maxLength < $baseLength;)
	        {
	            $htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit, $allowHtml);
	            $htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit, $allowHtml);
	            if ($ptString == $htmlStringToPtString)
	            {
	                return $htmlString;
	            }
	            $diffLength = strlen($ptString) - strlen($htmlStringToPtString);
	            $maxLength += $diffLength;
	            if ($baseLength <= $maxLength || $diffLength <= 0)
	            {
	                return $htmlString;
	            }
	        }
	        return $html;
    }
    
	/* 
		fichier desc.txt
		<nom image>|<description>|<url>
		si nom image = * => description/url par d�faut
	*/
	static public function getDesc($dir) {
		$filename = 'desc';
		$contents = self::getLabelsFileContents($dir,$filename);
		if ($contents === false) {
			return array();
		}
		if (!strcmp("\xEF\xBB\xBF", substr($contents,0,3))) {  // file starts with UTF-8 BOM
			$contents = substr($contents, 3);  // remove UTF-8 BOM
		}
		$contents = str_replace("\r", "\n", $contents);  // normalize line endings
		$contents = strtr($contents, '���������������������������', 'aaaaaaceeeeiiiinooooouuuuyy');
		// split into lines
		$matches = array();
		preg_match_all('/^([^|\n]+)(?:[|]([^|\n]*)(?:[|]([^\n]*))?)?$/mu', $contents, $matches, PREG_SET_ORDER);

		// parse individual entries
		$labels = array();
		foreach ($matches as $match) {
			$imagefile = $match[1];
			$description = count($match) > 2 ? $match[2] : false;
			$link = count($match) > 3 ? $match[3] : false;
			$arr = array();
			$arr['imagefile'] = $imagefile;
			$arr['description'] = $description;
			$arr['link'] = $link;
			$labels[$imagefile] = $arr; 
		}
		return $labels;		
	}
private static function getLabelsFileContents($imagedirectory, $labelsfilename) {
		$file = self::getLabelsFilePath($imagedirectory, $labelsfilename);
		return $file ? file_get_contents($file) : false;
	}

private static function getLabelsFilePath($imagedirectory, $labelsfilename) {

		if (is_file($imagedirectory)) {  // a file, not a directory
			return false;
		}

		// default to language-neutral labels file
		$file = $imagedirectory.DIRECTORY_SEPARATOR.$labelsfilename.'.txt';  // filesystem path to labels file
		if (is_file($file)) {
			return $file;
		}
		return false;
	}
// AJAX Request 	
	public static function getAjax() {
        $input = Factory::getApplication()->input;
		$id = $input->get('id');
		$module = self::getModuleById($id);
		$params = new JRegistry($module->params);  		
        $output = '';
		if ($input->get('data') == "param") {
			return self::getParams($id,$params);
		}
		return false;
	}
// Get Module per ID
	private static function getModuleById($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params')
			->from('#__modules AS m')
			->where('m.id = '.(int)$id);
		$db->setQuery($query);
		return $db->loadObject();
	}
	private static function getParams($id,$params) {
		$ret = '{"id":"'.$id.'","ug_type":"'.$params->get("ug_type").'","ug_texte":"'.$params->get("ug_texte").'"';
		$ret .= ',"ug_tiles_type":"'.$params->get("ug_tiles_type").'","ug_grid_num_rows":"'.$params->get("ug_grid_num_rows").'"';
	    $ret .= ',"ug_space_between_rows":"'.$params->get("ug_space_between_rows").'","ug_space_between_cols":"'.$params->get("ug_space_between_cols").'"';
		$ret .= ',"ug_min_columns":"'.$params->get("$ug_min_columns").'","ug_tile_height":"'.$params->get("ug_tile_height").'"';
		$ret .= ',"ug_tile_width":"'.$params->get("$ug_tile_width").'","ug_carousel_autoplay_timeout":"'.$params->get("ug_carousel_autoplay_timeout").'"';
		$ret .= ',"ug_carousel_scroll_duration":"'.$params->get("ug_carousel_scroll_duration").'","ug_link":"'.$params->get("ug_link").'"';
		$ret .= ',"ug_grid_thumbs_pos":"'.$params->get("ug_grid_thumbs_pos").'","ug_grid_show_icons":"'.$params->get("ug_grid_show_icons").'"';
		$ret .= ',"ug_lightbox":"'.$params->get("ug_lightbox").'"}';
		return $ret;
	}	
}
