<?php
/**
* CG Gallery Module  - Joomla 4.x/5.x Module
* Package			: CG Gallery
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class mod_cg_galleryInstallerScript
{
    private $min_joomla_version      = '4.0.0';
    private $min_php_version         = '7.4';
    private $name                    = 'CG Gallery';
    private $exttype                 = 'module';
    private $extname                 = 'cg_gallery';
    private $previous_version        = '';
    private $dir           = null;
    private $installerName = 'cg_galleryinstaller';
    private $lang;
    public function __construct()
    {
        $this->dir = __DIR__;
        $this->lang = Factory::getLanguage();
        $this->lang->load($this->extname);
    }

    public function preflight($type, $parent)
    {
        if (! $this->passMinimumJoomlaVersion()) {
            $this->uninstallInstaller();
            return false;
        }

        if (! $this->passMinimumPHPVersion()) {
            $this->uninstallInstaller();
            return false;
        }
        // To prevent installer from running twice if installing multiple extensions
        if (! file_exists($this->dir . '/' . $this->installerName . '.xml')) {
            return true;
        }
        $xml = simplexml_load_file(JPATH_BASE . '/modules/mod_'.$this->extname.'/mod_'.$this->extname.'.xml');
        $this->previous_version = $xml->version;
    }

    public function postflight($type, $parent)
    {
        if (($type == 'install') || ($type == 'update')) { // remove obsolete dir/files
            $this->postinstall_cleanup();
        }

        switch ($type) {
            case 'install': $message = Text::_('ISO_POSTFLIGHT_INSTALLED');
                break;
            case 'uninstall': $message = Text::_('ISO_POSTFLIGHT_UNINSTALLED');
                break;
            case 'update': $message = Text::_('ISO_POSTFLIGHT_UPDATED');
                break;
            case 'discover_install': $message = Text::_('ISO_POSTFLIGHT_DISC_INSTALLED');
                break;
        }
        return true;
    }
    private function postinstall_cleanup()
    {
        $obsloteFolders = ['unitegallery', 'assets', 'models','elements'];
        // Remove plugins' files which load outside of the component. If any is not fully updated your site won't crash.
        foreach ($obsloteFolders as $folder) {
            $f = JPATH_SITE . '/modules/mod_'.$this->extname.'/' . $folder;

            if (!@file_exists($f) || !is_dir($f) || is_link($f)) {
                continue;
            }

            Folder::delete($f);
        }
        $obsloteFiles = [sprintf("%s/modules/mod_%s/helper.php", JPATH_SITE, $this->extname),
						 sprintf("%s/modules/mod_%s/mod_%s.php", JPATH_SITE, $this->extname,$this->extname),
						 sprintf("%s/modules/mod_%s/slidelistk2.xml", JPATH_SITE, $this->extname)
						];
        foreach ($obsloteFiles as $file) {
            if (@is_file($file)) {
                File::delete($file);
            }
        }
        $j = new Version();
        $version = $j->getShortVersion();
        $version_arr = explode('.', $version);
        if (($version_arr[0] == "4") || (($version_arr[0] == "3") && ($version_arr[1] == "10"))) {
            // Delete 3.9 and older language files
            $langFiles = [
                sprintf("%s/language/en-GB/en-GB.mod_%s.ini", JPATH_SITE, $this->extname),
                sprintf("%s/language/en-GB/en-GB.mod_%s.sys.ini", JPATH_SITE, $this->extname),
                sprintf("%s/language/fr-FR/fr-FR.mod_%s.ini", JPATH_SITE, $this->extname),
                sprintf("%s/language/fr-FR/fr-FR.mod_%s.sys.ini", JPATH_SITE, $this->extname),
            ];
            foreach ($langFiles as $file) {
                if (@is_file($file)) {
                    File::delete($file);
                }
            }
        }
        // version 2.4.0 : base_dir parameter : fix ug_big_dir
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $pages = $db->setQuery(
            $db->getQuery(true)
            ->select('id,params')
            ->from('#__modules')
            ->where($db->quoteName('module'). ' like '.$db->quote('mod_cg_gallery'))
        )->loadObjectList();
        foreach($pages as $onepage) {
            $params = $onepage->params;
            if (! strpos($params, '"ug_big_dir":"images\/')) {
                continue;
            }
            $params = str_replace('"ug_big_dir":"images\/', '"ug_big_dir":"', $params);
            $updateNulls = true;
            $data = new \StdClass();
            $data->id = $onepage->id;
            $data->params = $params;
            $result = $db->updateObject('#__modules', $data, 'id', $updateNulls);
        }
        // remove obsolete update sites
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%432473037d.url-de-test.ws/%"');
        $db->setQuery($query);
        $db->execute();
        // CG Gallery is now on Github
        $query = $db->getQuery(true)
            ->delete('#__update_sites')
            ->where($db->quoteName('location') . ' like "%conseilgouz.com/updates/cg_gallery%"');
        $db->setQuery($query);
        $db->execute();

    }

    // Check if Joomla version passes minimum requirement
    private function passMinimumJoomlaVersion()
    {
        $j = new Version();
        $version = $j->getShortVersion();
        if (version_compare($version, $this->min_joomla_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible Joomla version : found <strong>' . $version . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
                'error'
            );

            return false;
        }

        return true;
    }

    // Check if PHP version passes minimum requirement
    private function passMinimumPHPVersion()
    {

        if (version_compare(PHP_VERSION, $this->min_php_version, '<')) {
            Factory::getApplication()->enqueueMessage(
                'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
                'error'
            );
            return false;
        }

        return true;
    }
    private function uninstallInstaller()
    {
        if (! is_dir(JPATH_PLUGINS . '/system/' . $this->installerName)) {
            return;
        }
        $this->delete([
            JPATH_PLUGINS . '/system/' . $this->installerName . '/language',
            JPATH_PLUGINS . '/system/' . $this->installerName,
        ]);
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote($this->installerName))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
        $db->setQuery($query);
        $db->execute();
        Factory::getCache()->clean('_system');
    }
    public function delete($files = [])
    {
        foreach ($files as $file) {
            if (is_dir($file)) {
                Folder::delete($file);
            }

            if (is_file($file)) {
                File::delete($file);
            }
        }
    }

}
