<?php
/**
* CG Gallery - Joomla Module
* Package			: Joomla 4.x/5x
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$document 		= Factory::getApplication()->getDocument();
$baseurl 		= URI::base();
$modulefield	= 'media/mod_cg_gallery/';

//Get this module id
$nummod_sf		= $module->id;
$num_sf		= 'mod'.$nummod_sf;

$base_dir = $params->get('base_dir', 'images');

$ug_type	= $params->get('ug_type', '');
$ug_texte	= $params->get('ug_texte', '');
$ug_tiles_type = $params->get('ug_tiles_type', '');
$ug_big_dir = $base_dir .'/'. $params->get('ug_big_dir', '');

$ug_grid_num_rows = $params->get('ug_grid_num_rows');
$ug_space_between_rows = $params->get('ug_space_between_rows');
$ug_space_between_cols = $params->get('ug_space_between_cols');
$ug_min_columns = $params->get('ug_min_columns');
$ug_tile_height = $params->get('ug_tile_height');
$ug_tile_width = $params->get('ug_tile_width', 200);
$ug_carousel_autoplay_timeout = $params->get('ug_carousel_autoplay_timeout');
$ug_carousel_scroll_duration = $params->get('ug_carousel_scroll_duration');
$ug_link = $params->get('ug_link');
$ug_lightbox = $params->get('ug_lightbox');
$ug_zoom = $params->get('ug_zoom', 'true');
$ug_grid_thumbs_pos = $params->get('ug_grid_thumbs_pos', 'right');
$ug_grid_show_icons = $params->get('ug_grid_show_icons', 'true');
$ug_skin	= $params->get('ug_skin', 'default');

// from https://digitaldisseny.com/en/blog/96-joomla-jfolder-filter-for-file-extensions
$filter = null;
$allowedExtensions = null;
if ($params->get('imgtypes', '')) {
    $allowedExtensions = $params->get('imgtypes');
}
if ($allowedExtensions) {
    $allowedExtensions = array_merge($allowedExtensions, array_map('strtoupper', $allowedExtensions));
    // Build the filter. Will return something like: "jpg|png|JPG|PNG|gif|GIF"
    $filter = implode('|', $allowedExtensions);
    $filter = "^.*\.(" . implode('|', $allowedExtensions) .")$";
} else {
    $filter = "^.*\.(jpg|jpeg|png|webp|gif|JPG|JPEG|PNG|WEBP|GIF)$";
}

// HTMLHelper::_('bootstrap.framework');
HTMLHelper::_('jquery.framework');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
if ($params->get('css_gen')) { // Custom CSS ?
    $wa->addInlineStyle($params->get('css_gen'));
}

$wa->registerAndUseStyle('cgunitecss', $modulefield.'unitegallery/css/unite-gallery.css');
$wa->registerAndUseScript('cgunitejs', $modulefield.'unitegallery/js/unitegallery.min.js');
if ($ug_skin != 'default') {
    $wa->registerAndUseStyle('cgugskin', $modulefield.'unitegallery/skins/'.$ug_skin.'/'.$ug_skin.'.css');
}
$document->addScriptOptions(
    'cg_gallery_'.$module->id,
    array('ug_type' => $ug_type,'ug_texte' => $ug_texte,
          'ug_tiles_type' => $ug_tiles_type,
          'ug_grid_num_rows' => $ug_grid_num_rows,
          'ug_space_between_rows' => $ug_space_between_rows,'ug_space_between_cols' => $ug_space_between_cols,
          'ug_min_columns' => $ug_min_columns,
          'ug_tile_height' => $ug_tile_height,
          'ug_tile_width' => $ug_tile_width,
          'ug_carousel_autoplay_timeout' => $ug_carousel_autoplay_timeout,
          'ug_carousel_scroll_duration' => $ug_carousel_scroll_duration,
          'ug_link' => $ug_link,
          'ug_lightbox' => $ug_lightbox,'ug_zoom' => $ug_zoom,
          'ug_grid_thumbs_pos' => $ug_grid_thumbs_pos, 'ug_grid_show_icons' => $ug_grid_show_icons,
          'ug_skin' => $ug_skin
)
);
if ($ug_type == "tiles") {
    if ($ug_tiles_type == "tilesgrid") {
        $wa->registerAndUseScript('cgunitetilesgrid', $modulefield.'unitegallery/themes/tilesgrid/ug-theme-tilesgrid.js');
    } else {
        $wa->registerAndUseScript('cgunitetiles', $modulefield.'unitegallery/themes/tiles/ug-theme-tiles.js');
    }
}
if ($ug_type == "grid") {
    $wa->registerAndUseScript('cgunitegrid', $modulefield.'unitegallery/themes/grid/ug-theme-grid.js');
}
if ($ug_type == "carousel") {
    $wa->registerAndUseScript('cgunitecarousel', $modulefield.'unitegallery/themes/carousel/ug-theme-carousel.js');
}
if ($ug_type == "slider") {
    $wa->registerAndUseScript('cguniteslider', $modulefield.'unitegallery/themes/slider/ug-theme-slider.js');
}
$wa->registerAndUseScript('cggallery', $modulefield.'js/init.js');
require ModuleHelper::getLayoutPath('mod_cg_gallery', $params->get('layout', 'default'));
