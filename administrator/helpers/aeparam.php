<?php
/**
 * @version    CVS: 2.1.1
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */
 
defined('_JEXEC') or die;

class Manifest2mdHelperParam
{
    /**
     * AllEventsHelperParam::getGlobalParam()
     *
     * @return \Joomla\Registry\Registry
     * @throws Exception
     */
    public static function getGlobalParam()
    {
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $app = JFactory::getApplication();
            $params = $app->getParams('com_manifest2md');
        } else {
            $params = JComponentHelper::getParams('com_manifest2md');
        }

        $params['doc_home'] = isset($params['doc_home']) ? $params['doc_home'] : "";
        $params['doc_language'] = isset($params['doc_language']) ? $params['doc_language'] : "en-GB";
        $params['template_component'] = isset($params['template_component']) ? $params['template_component'] : "";
        $params['template_config'] = isset($params['template_config']) ? $params['template_config'] : "";
        $params['template_model'] = isset($params['template_model']) ? $params['template_model'] : "";
        $params['template_module'] = isset($params['template_module']) ? $params['template_module'] : "";
        $params['template_plugin'] = isset($params['template_plugin']) ? $params['template_plugin'] : "";
        $params['template_view'] = isset($params['template_view']) ? $params['template_view'] : "";

        if (empty($params['template_component'])) {
            $params['template_component'] = '';
            $params['template_component'] .= '# Component {extension}' . PHP_EOL;
            $params['template_component'] .= '## Installer Definition' . PHP_EOL;
            $params['template_component'] .= '{parameters}';
            $params['template_component'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_component'] .= 'Once again, thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_component'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';
            }
        
        if (empty($params['template_config'])) {
            $params['template_config'] = '';
            $params['template_config'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension}' . PHP_EOL;
            $params['template_config'] .= '## '. JText::_('COM_MANIFEST2MD_PARAMETERS_CONFIG') . PHP_EOL;
            $params['template_config'] .= '{parameters}';
            $params['template_config'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_config'] .= 'Thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_config'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';
            }

        if (empty($params['template_model'])) {
            $params['template_model'] = '';
            $params['template_model'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension}' . PHP_EOL;
            $params['template_model'] .= '##  '. JText::_('COM_MANIFEST2MD_MODEL_OBJECT') .' {object}' . PHP_EOL;
            $params['template_model'] .= '{parameters}';
            $params['template_model'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_model'] .= 'Once again, thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_model'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';

        }
        
        if (empty($params['template_plugin'])) {
            $params['template_plugin'] = '';
            $params['template_plugin'] .= '# Plugin - {extension_name}' . PHP_EOL;
            $params['template_plugin'] .= '## Description:' . PHP_EOL;
            $params['template_plugin'] .= '### {description}' . PHP_EOL;
            $params['template_plugin'] .= '#### Install the plugin' . PHP_EOL;
            $params['template_plugin'] .= '1. Download the extension to your local machine as a zip file package.' . PHP_EOL;
            $params['template_plugin'] .= '2. From the backend of your Joomla site (administration) select **Extensions >> Manager**, then Click the <b>Browse</b> button and select the extension package on your local machine. Then click the **Upload & Install** button to install module.' . PHP_EOL;
            $params['template_plugin'] .= '3. Go to **Extensions >> Plugin**, find and click on **{extension_name}**. Then enable it.' . PHP_EOL;
            $params['template_plugin'] .= PHP_EOL;
            $params['template_plugin'] .= '### Configure the plugin' . PHP_EOL;
            $params['template_plugin'] .= '#### There are many options for you to customize your extension :' . PHP_EOL;
            $params['template_plugin'] .= '{parameters}';
            $params['template_plugin'] .= '### Frequently Asked Questions' . PHP_EOL;
            $params['template_plugin'] .= 'No questions for the moment' . PHP_EOL;
            $params['template_plugin'] .= '### Uninstall the plugin' . PHP_EOL;
            $params['template_plugin'] .= '1. Login to Joomla backend.' . PHP_EOL;
            $params['template_plugin'] .= '2. Click **Extensions >> Manager** in the top menu.' . PHP_EOL;
            $params['template_plugin'] .= '3. Click **Manage** on the left, navigate on the extension and click the Uninstall button on top.' . PHP_EOL;
            $params['template_plugin'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_plugin'] .= 'Once again, thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_plugin'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';
        }

        if (empty($params['template_module'])) {
            $params['template_module'] = '';
            $params['template_module'] .= '# Module - {extension_name}' . PHP_EOL;
            $params['template_module'] .= '## Description:' . PHP_EOL;
            $params['template_module'] .= '### {description}' . PHP_EOL;
            $params['template_module'] .= '#### Install the module' . PHP_EOL;
            $params['template_module'] .= '1. Download the extension to your local machine as a zip file package.' . PHP_EOL;
            $params['template_module'] .= '2. From the backend of your Joomla site (administration) select **Extensions >> Manager**, then Click the <b>Browse</b> button and select the extension package on your local machine. Then click the **Upload & Install** button to install module.' . PHP_EOL;
            $params['template_module'] .= '3. Go to **Extensions >> Module**, find and click on **{extension_name}**. Then enable it.' . PHP_EOL;
            $params['template_module'] .= PHP_EOL;
            $params['template_module'] .= '### Configure the module' . PHP_EOL;
            $params['template_module'] .= 'There are many options for you to customize your extension :' . PHP_EOL;
            $params['template_module'] .= '{parameters}';
            $params['template_module'] .= '### Frequently Asked Questions' . PHP_EOL;
            $params['template_module'] .= 'No questions for the moment' . PHP_EOL;
            $params['template_module'] .= '### Uninstall the module' . PHP_EOL;
            $params['template_module'] .= '1. Login to Joomla backend.' . PHP_EOL;
            $params['template_module'] .= '2. Click **Extensions >> Manager** in the top menu.' . PHP_EOL;
            $params['template_module'] .= '3. Click **Manage** on the left, navigate on the extension and click the Uninstall button on top.' . PHP_EOL;
            $params['template_module'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_module'] .= 'Once again, thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_module'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';
        }

        if (empty($params['template_view'])) {
            $params['template_view'] = '';
            $params['template_view'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension}' . PHP_EOL;
            $params['template_view'] .= '## '. JText::_('COM_MANIFEST2MD_VIEW_EXT') .' {extension_name}' . PHP_EOL;
            $params['template_view'] .= '### {description}' . PHP_EOL;
            $params['template_view'] .= PHP_EOL;
            $params['template_view'] .= '#### '. JText::_('COM_MANIFEST2MD_LINK_MENU_FILTERS') . PHP_EOL;
            $params['template_view'] .= JText::_('COM_MANIFEST2MD_LINK_MENU_OPTIONS') . PHP_EOL;
            $params['template_view'] .= '{parameters}';
            $params['template_view'] .= '<p>&nbsp;</p>' . PHP_EOL;
            $params['template_view'] .= 'Once again, thank you so much for downloading our product. As I said at the beginning, I\'d be glad to help you if you have any questions relating to this product. No guarantees, but I\'ll do my best to assist.' . PHP_EOL;
            $params['template_view'] .= '> ###### Created on *{extension_date}* by *{extension_author}* ([{extension_authorEmail}](mailto:{extension_authorEmail}))';
        }


        return $params;
    }
}
