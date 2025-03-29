<?php
/**
* CG Gallery - Joomla Module 
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use ConseilGouz\Module\CGGallery\Site\Helper\CGGalleryHelper;

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


$uri = Uri::getInstance();

echo '<div id="cg_gallery_'.$module->id.'" data="'.$module->id.'" class="cg_gallery">';

if ($params->get('ug_dir_or_image') == "dir") { // images d'un répertoire
	$ug_full_dir = $params->get('ug_full_dir',''); // répertoire complet ou non
	$files = array();
	$ug_big_dir = CGGalleryHelper::getFolder($ug_big_dir); // gestion répertoire dynamique
	if ($ug_big_dir === false) {
		echo '</div>';  // on ferme la div ouverte
		return false;
	}
	$files = Folder::files($ug_big_dir,$filter,null ,null , array('desc.txt','index.html','.htaccess'));
	$desc = CGGalleryHelper::getDesc($ug_big_dir); // récupération fichier description s'il existe
	if (count($files) == 0) { ?>
		<img src ="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
		data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
						data-description=""
						style="display:none" alt="">
	<?php 
	} else {
	$ug_thumb_dir = $ug_big_dir; // répertoire des miniatures
	if ($ug_full_dir == "true") { // répertoire complet
		foreach ($files as $file) { 
			$bigfile = $file;
			$description = $bigfile;
			$link = false;
			$item = new \stdClass();
			$index = isset($desc[$bigfile]) ? $bigfile : '*';
			if ($index == '*') {if (!isset($desc[$index])) {$index = false;}}
			if ($index) {
				$description = $desc[$index]['description'];
				$link = $desc[$index]['link'];
				$target = '';
				if (is_numeric($link)) { // lien sur un article
					$item->slidearticleid = $link;
					$link = CGGalleryHelper::getArticle($item, $params);
					$description = $item->article->text;
				} else {
				$target = ' target="_blank" rel="noopener noreferrer" '; // lien sur adresse web: nouvelle fenêtre
				}
				
			}
			if ($link) {
				echo '<a href="'.$link.'"'.$target.'>';
			}
			?>
			<img src="<?php echo $uri->root().$ug_thumb_dir.'/'.$file;?>"
			<?php if (is_file($ug_big_dir.'/'.$bigfile)) {
			?>
				data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
			<?php } else {?>
				data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" <?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none" alt="<?php echo $bigfile;?>" >
		<?php
			if ($link) { echo '</a>'; }
			} 
	}  else { // on prend juste les premières images du répertoire
			$ug_file_nb = $params->get('ug_file_nb','5');
			if ($ug_file_nb > count($files) ) {$ug_file_nb = count($files); } // dépassement capacité
			for ($i = 0; $i < $ug_file_nb; $i++) { 
				$bigfile = $files[$i];
				$description = $bigfile;
				$item = new stdClass();
				$link = false;
				$target = '';
				$index = $desc[$bigfile] ? $bigfile : '*';
				if ($index == '*') {if (!isset($desc[$index])) {$index = false;}}
				if ($index) {
					$description = $desc[$index]['description'];
					$link = $desc[$index]['link'];
					if (is_numeric($link)) { // lien sur un article
						$item->slidearticleid = $link;
						$link = CGGalleryHelper::getArticle($item, $params);
						$description = $item->article->text;
					}
					else {
					$target = ' target="_blank" rel="noopener noreferrer" '; // lien sur adresse web: nouvelle fen�tre
					}
				}
				if ($link) {
					echo '<a href="'.$link.'"'.$target.'>'; // lien externe : nouvelle fen�tre
				}
				?>
				<img src="<?php echo $uri->root().$ug_thumb_dir.'/'.$files[$i];?>"
				<?php if (is_file($ug_big_dir.'/'.$files[$i])) { ?>
					data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
				<?php } else {?>
					data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
				<?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none" alt="<?php echo $bigfile;?>" >
			<?php 
				if ($link) { echo '</a>'; }
			}
	    }
	}
}  else { // images sélectionnées individuellement
	$ug_articles = $params->get('ug_articles','articles');
    if ($ug_articles == "articles") {
		$slideslist = $params->get('slideslist');
	} else {
		$slideslist = $params->get('slideslist_k2');
	}
	foreach ($slideslist as $item) {
		$imgcaption =  $item->file_desc;
		$image40 = explode('#',$item->file_name);
		$imgname = $image40[0]; // Joomla 4.0 : nom du fichier en 2 parties
		$imgthumb = $imgname;
		$pos = strrpos($imgthumb,'/');
		$len = strlen($imgthumb);
		$imgtitle = $item->file_name;
		$item->slidearticleid = $item->file_id;
		$link = null;
		$imgdesc = $imgcaption;
		if (isset($item->slidearticleid) && $item->slidearticleid) {
			if ($ug_articles == 'articles') {
				$link = CGGalleryHelper::getArticle($item, $params);	
			} else {
				$link = CGGalleryHelper::getArticleK2($item, $params);	
			}
			if ($imgdesc == '') $imgdesc = $item->article->text; 
		}
		if (isset($link)) {
			echo '<a href="'.$link.'">';
		}
		?>
				<img src="<?php echo $uri->root().$imgthumb;?>"
					<?php if ($imgname)
					{
					?> data-image="<?php echo $uri->root().$imgname; ?>"
					<?php }
					else { ?> 
					data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
					<?php } ?>
				data-description="<?php echo $imgdesc;?>"
				style="display:none" alt="<?php echo $imgcaption;?>" >
	<?php
				if (isset($link)) { echo '</a>'; }
		} 
}
?>	
	</div>