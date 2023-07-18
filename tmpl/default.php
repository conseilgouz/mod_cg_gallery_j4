<?php
/**
* CG Gallery - Joomla Module 
* Version			: 2.3.1
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use ConseilGouz\Module\CGGallery\Site\Helper\CGGalleryHelper;

$uri = Uri::getInstance();
$nummod_sf		= $module->id;

echo '<div id="cg_gallery_'.$module->id.'" data="'.$module->id.'" class="cg_gallery">';

if ($params->get('ug_dir_or_image') == "dir") { // images d'un répertoire
	$ug_full_dir = $params->get('ug_full_dir',''); // répertoire complet ou non
	$files = array();
	$ug_big_dir = CGGalleryHelper::getFolder($ug_big_dir); // gestion répertoire dynamique
	if ($ug_big_dir === false) {
		echo '</div>';  // on ferme la div ouverte
		return false;
	}
	$files = Folder::files($ug_big_dir,null,null ,null , array('desc.txt','index.html','.htaccess'));
	$desc = CGGalleryHelper::getDesc($ug_big_dir); // récupération fichier description s'il existe
	if (count($files) == 0) { ?>
		<img alt=""
		src ="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
		data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
						data-description=""
						style="display:none">
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
			<img alt="<?php echo $bigfile;?>" 
		     src="<?php echo $uri->root().$ug_thumb_dir.'/'.$file;?>"
			<?php if (File::exists($ug_big_dir.'/'.$bigfile)) {
			?>
				data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
			<?php } else {?>
				data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" <?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none">
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
				<img alt="<?php echo $bigfile;?>" 
					src="<?php echo $uri->root().$ug_thumb_dir.'/'.$files[$i];?>"
				<?php if (JFile::exists($ug_big_dir.'/'.$files[$i])) { ?>
					data-image="<?php echo $uri->root().$ug_big_dir; ?>/<?php echo $bigfile;?>"
				<?php } else {?>
					data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
				<?php } ?>
				data-description="<?php echo $description;?>"
				style="display:none">
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
				<img alt="<?php echo $imgcaption;?>" 
					src="<?php echo $uri->root().$imgthumb;?>"
					<?php if ($imgname)
					{
					?> data-image="<?php echo $uri->root().$imgname; ?>"
					<?php }
					else { ?> 
					data-image="<?php echo $modulefield;?>unitegallery/images/pasdimage.png" 
					<?php } ?>
				data-description="<?php echo $imgdesc;?>"
				style="display:none">
	<?php
				if (isset($link)) { echo '</a>'; }
		} 
}
?>	
	</div>

