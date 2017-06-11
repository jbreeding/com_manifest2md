<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

use Joomla\Utilities\ArrayHelper;

/**
 * Extensions list controller class.
 *
 * @since  1.6
 */
class Manifest2mdControllerExtensions extends JControllerAdmin
{
    /**
     * Method to clone existing Extensions
     *
     * @return void
     */
    public function duplicate()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Get id(s)
        $pks = $this->input->post->get('cid', array(), 'array');

        try {
            if (empty($pks)) {
                throw new Exception(JText::_('COM_MANIFEST2MD_NO_ELEMENT_SELECTED'));
            }

            ArrayHelper::toInteger($pks);
            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Jtext::_('COM_MANIFEST2MD_ITEMS_SUCCESS_DUPLICATED'));
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_manifest2md&view=extensions');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string $name Optional. Model name
     * @param   string $prefix Optional. Class prefix
     * @param   array $config Optional. Configuration array for model
     *
     * @return  object    The Model
     *
     * @since    1.6
     */
    public function getModel($name = 'extension', $prefix = 'Manifest2mdModel', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));

        return $model;
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return  void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        // Get the input
        $input = JFactory::getApplication()->input;
        $pks = $input->post->get('cid', array(), 'array');
        $order = $input->post->get('order', array(), 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }

    /**
     * Manifest2mdControllerMain::Discover()
     *
     */
    public function Discover()
    {
        $model = $this->getModel('extension');
        $msg = $model->Discover();
        $this->setRedirect('index.php?option=com_manifest2md', $msg);
}

    public function DeleteFolder($path) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::DeleteFolder($file) : 
                unlink($file);
        }
        rmdir($path);
        return;
}

    /**
     * Manifest2mdControllerMain::MakeMD()
     *
     */
    public function MakeMD()
        {
        $msg = '';
        
        require_once(JPATH_SITE . '/administrator/components/com_manifest2md/helpers/aeparam.php');
        $g_params = new Manifest2mdHelperParam();
        $params = $g_params->getGlobalParam();

        require_once(JPATH_SITE . '/administrator/components/com_manifest2md/helpers/MakeMD.php');
        $g_se_MD = new Manifest2mdClassMD();

        $g_se_MD->setParams($params);
        // language before doc_home
        $g_se_MD->setLanguage($params['doc_language']);
        $g_se_MD->setRoot(JPATH_ROOT . $params['doc_home']);
        
        $folder2del = JPATH_ROOT . $params['doc_home'].'/'.$params['doc_language'];

        // delete previous Root Language Folder
        if ($params['delete_folder'] == 'yes') 
            {
            if ( JFolder::exists($folder2del) ){
                self::DeleteFolder($folder2del);
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_FOLDER_TO_DELETE') .': </b></u>';
                $msg .='<p>=> ' .  $folder2del . '</p>' ;
                }
            }

        $model = $this->getModel('extensions');
        $items = $model->getComponentsConfig();

        foreach ($items as $item) {
            $element = $item->element;
            $type = $item->type;
            $folder = $item->folder;
            $identifier = $item->identifier;
            $msg .= '<p><big>'. ucfirst($type). ' '. ucfirst($element) .'</big></p>';
            // build directories structure with .xml
            $g_se_MD->CheckFolder($type, $element, $folder, $identifier);
                        
            if ($item->identifier == 'both') {
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [site]</u></b>';
                $msg .= $g_se_MD->MakeMDObjects($item->category, $item->element, 'site');
                
                $msg .= '<p></p>';
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [administrator]</u></b>';
                $msg .= $g_se_MD->MakeMDObjects($item->category, $item->element, 'administrator');
                
                $msg .= '<p></p>';
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [site]</u></b>';
                $msg .= $g_se_MD->MakeMDViews($item->category, $item->element, 'site');
                
                $msg .= '<p></p>';
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [administrator]</u></b>';              
                $msg .= $g_se_MD->MakeMDViews($item->category, $item->element, 'administrator');
                
            } else {
                $msg .= '<p></p>';
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [' . $item->identifier . ']</u></b>';
                $msg .= $g_se_MD->MakeMDObjects($item->category, $item->element, $item->identifier);
                
                $msg .= '<p></p>';
                $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [' . $item->identifier . ']</u></b>';     
                $msg .= $g_se_MD->MakeMDViews($item->category, $item->element, $item->identifier);
                }
            
        
            $msg .= '<p></p>';
            // fichier de config & access
            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_CONFIG') .'</u></b>';
            $msg .= '<br />=> ' .  $g_se_MD->MakeMDConfig($item->category, $item->element) ;
            $msg .= '<p></p>';
            }
        
        $items = $model->getModules();
        $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODULES') .'</u></b>';
        foreach ($items as $item) {
            $element = $item->element;
            $type = $item->type;
            $folder='';
            $identifier = $item->identifier;
            $g_se_MD->CheckFolder($type, $element, $folder, $identifier);
            $msg .=  '<br />=> ' .  $g_se_MD->MakeMDModule($item->category, $element)  ;
            }

        $msg .= '<p></p>';
        $items = $model->getPlugins();
        $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_PLUGINS') .'</u></b>';
        foreach ($items as $item) {
            $element = $item->element;
            $type = $item->type;
            $folder = $item->folder;
            $identifier = $item->identifier;          
            $g_se_MD->CheckFolder($type, $element, $folder, $identifier);
            $msg .=  '<br />=> ' .  $g_se_MD->MakeMDPlugin($item->category, $element, $folder) ;
            }
      
        $this->setRedirect('index.php?option=com_manifest2md', $msg);
        
        }
    }