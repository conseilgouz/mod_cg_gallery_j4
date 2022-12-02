<?php
/**
* CG Gallery - Joomla Module 
* Version			: 2.2.0
* Package			: Joomla 4.x
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::registerNamespace('ConseilGouz\Module\CGGallery\Site', JPATH_SITE . '/modules/mod_cg_gallery/src', false, false, 'psr4');


$document 		= Factory::getDocument();
$baseurl 		= JURI::base();
$modulefield	= ''.JURI::base(true).'/media/mod_cg_gallery/';

//Get this module id
$nummod_sf		= $module->id;
$num_sf		= 'mod'.$nummod_sf;

$ug_type	= $params->get('ug_type', '');
$ug_texte	= $params->get('ug_texte', '');
$ug_tiles_type = $params->get('ug_tiles_type', '' );
$ug_big_dir = $params->get('ug_big_dir','');
$ug_grid_num_rows = $params->get('ug_grid_num_rows');
$ug_space_between_rows = $params->get('ug_space_between_rows');
$ug_space_between_cols = $params->get('ug_space_between_cols');
$ug_min_columns = $params->get('ug_min_columns');
$ug_tile_height = $params->get('ug_tile_height');
$ug_tile_width = $params->get('ug_tile_width');
$ug_carousel_autoplay_timeout = $params->get('ug_carousel_autoplay_timeout');
$ug_carousel_scroll_duration = $params->get('ug_carousel_scroll_duration');
$ug_link = $params->get('ug_link');
$ug_lightbox = $params->get('ug_lightbox');
$ug_zoom = $params->get('ug_zoom','true');
$ug_grid_thumbs_pos = $params->get('ug_grid_thumbs_pos','right');
$ug_grid_show_icons = $params->get('ug_grid_show_icons','true');

// HTMLHelper::_('bootstrap.framework');		
HTMLHelper::_('jquery.framework'); 
				 
$document->addStyleSheet($modulefield.'unitegallery/css/unite-gallery.css');
$document->addScript($modulefield.'unitegallery/js/unitegallery.min.js');
$document->addScriptOptions('cg_gallery_'.$module->id, 
	array('ug_type' => $ug_type,'ug_texte' => $ug_texte,
		  'ug_tiles_type' => $ug_tiles_type,
		  'ug_grid_num_rows' => $ug_grid_num_rows,
	      'ug_space_between_rows' => $ug_space_between_rows,'ug_space_between_cols' => $ug_space_between_cols,
		  'ug_min_columns' => $ug_min_columns,
		  'ug_tile_height' => $ug_tile_height,
		  'ug_tile_width' => $ug_tile_width,
		  'ug_carousel_autoplay_timeout' => $ug_carousel_autoplay_timeout,
		  'ug_carousel_scroll_duration' => $ug_carousel_scroll_duration,
		  'ug_link'=> $ug_link,
		  'ug_lightbox'=>$ug_lightbox,'ug_zoom'=>$ug_zoom,
		  'ug_grid_thumbs_pos'=>$ug_grid_thumbs_pos, 'ug_grid_show_icons'=>$ug_grid_show_icons
));
if ($ug_type == "tiles") {
	if ($ug_tiles_type == "tilesgrid") {
	$document->addScript($modulefield.'unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js');
	} else {
	$document->addScript($modulefield.'unitegallery/themes/tiles/ug-theme-tiles.js');
	}
}
if ($ug_type == "grid") {
$document->addScript($modulefield.'unitegallery/themes/grid/ug-theme-grid.js');
}
if ($ug_type == "carousel") {
$document->addScript($modulefield.'unitegallery/themes/carousel/ug-theme-carousel.js');
}
if ($ug_type == "slider") {
$document->addScript($modulefield.'unitegallery/themes/slider/ug-theme-slider.js');
}
$document->addScript($modulefield.'js/init.js');
require ModuleHelper::getLayoutPath('mod_cg_gallery', $params->get('layout', 'default'));
?>