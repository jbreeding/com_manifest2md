<?php
/**
 * @version2.0.0
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');
// jimport('joomla.language.language');

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

        if (is_dir($path)) rmdir( $path ); 

        return;
}

    /**
     * Manifest2mdControllerMain::MakeMD()
     *
     */
    public function MakeMD()
        {
        $msg = '';
        
        // require_once(JPATH_SITE . '/administrator/components/com_manifest2md/helpers/aeparam.php');
        // $g_params = new Manifest2mdHelperParam();
        // $params = $g_params->getGlobalParam();
        
        require_once(JPATH_SITE . '/administrator/components/com_manifest2md/helpers/MakeMD.php');
        
        $params	= JComponentHelper::getParams( 'com_manifest2md' );
        $display_mod = $params->get('display_mod');

        $g_se_MD = new Manifest2mdClassMD();

        $g_se_MD->setParams($params);
        // $display_mod = $params['display_mod'];
        
        // Loop installed Languages
        // $languages = JLanguageHelper::getLanguages();
        // foreach($languages as $language){
        
        $doc_home =  $params->get('doc_home');
        $lang_code = $params->get('doc_language');
          
        // Previous Folder to be deleted
        $folder2del = JPATH_ROOT . $doc_home .'/'. $lang_code;

        // langugag before doc_home
        $g_se_MD->setLanguage($lang_code);
        $g_se_MD->setRoot(JPATH_ROOT . $doc_home);
            
            // delete previous Root Language Folder
            if ($params['delete_folder'] == 'yes') 
                {
                if ( JFolder::exists($folder2del) ) {
                    self::DeleteFolder($folder2del);
                    if ( JFolder::exists($folder2del) ) {
                        $msg .= '<p><b><u>'. JText::_('COM_MANIFEST2MD_FOLDER_NOT_DELETED') .': </b></u>'
                              . '<br />=> ' .  $folder2del . '</p>';   
                        } else {
                        $msg .= '<p><b><u>'. JText::_('COM_MANIFEST2MD_FOLDER_DELETED') .': </b></u>'
                                  . '<br />=> ' .  $folder2del . '</p>'; 
                        }
                    }
                }

            $model = $this->getModel('extensions');
            $items = $model->getComponentsConfig();

            // check Display_mod for Components
            switch ($display_mod){
                case 'user':
                foreach ($items as $item) {
                    $category = $item->category;
                    $element = $item->element;
                    $doc_element = $item->doc_element;
                    $type = $item->type;
                    $folder='';
                    $identifier = $item->identifier;
                    $msg .= '<br /><big>'. JText::_( 'COM_MANIFEST2MD_'. strtoupper($type)). ' '. ucfirst($element) .' ['.$lang_code.']</big></p>';

                    if ($identifier == 'both') {
                        
                        switch ($doc_element){
                        case 'all':
                        case 'Items':
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [site]</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsUser($category, $element, 'site');
                  
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [administrator]</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsUser($category, $element, 'administrator');
                            break;

                        case 'all':
                        case 'Views':  
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [site]</u></b>';
                            $msg .= $g_se_MD->MakeMDViewsUser($category, $element, 'site');

                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [administrator]</u></b>';              
                            $msg .= $g_se_MD->MakeMDViewsUser($category, $element, 'administrator');
                            break;
                        }

                    } else {
                        switch ($doc_element){
                        case 'all':
                        case 'Items':                        
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [' . $identifier . ']</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsUser($category, $element, $identifier);
                            break;
                        
                        case 'all':
                        case 'Views':                         
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [' . $identifier . ']</u></b>';     
                            $msg .= $g_se_MD->MakeMDViewsUser($category, $element, $identifier);
                            break;
                            }
                        }
                    switch ($doc_element){
                        case 'all':
                        case 'config':                        
                        $msg .= '<p></p>';
                        // fichier de config & access
                        $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_CONFIG') .'</u></b>';
                        $msg .= '<br />=> ' .  $g_se_MD->MakeMDConfigUser($category, $element) ;
                        $msg .= '<p></p>';
                        break;
                        }
                    }
                break;
            
                case 'dev':
                foreach ($items as $item) {
                    $element = $item->element;
                    $type = $item->type;
                    $identifier = $item->identifier;
                    $doc_element =$item->doc_element;
                    $folder='';
                    $msg .= '<p><big>'. ucfirst($type). ' '. ucfirst($element) .'</big></p>';

                    if ($item->identifier == 'both') {
                        switch ($doc_element){
                        case 'all':
                        case 'Items':                        
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [site]</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsDev($element, 'site');

                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [administrator]</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsDev($element, 'administrator');
                            break;
                        case 'all':
                        case 'Views':                         
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [site]</u></b>';
                            $msg .= $g_se_MD->MakeMDViewsDev($element, 'site');

                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [administrator]</u></b>';              
                            $msg .= $g_se_MD->MakeMDViewsDev($element, 'administrator');
                            break;
                            }
                    } else {
                        switch ($doc_element){
                        case 'all':
                        case 'Items':                            
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODELS') .' [' . $identifier . ' - '.$lang_code.']</u></b>';
                            $msg .= $g_se_MD->MakeMDModelsDev($element, $identifier);
                            break;
                        case 'all':
                        case 'Views':                          
                            $msg .= '<p></p>';
                            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_VIEWS') .' [' . $identifier . ' - '.$lang_code.']</u></b>';     
                            $msg .= $g_se_MD->MakeMDViewsDev($element, $identifier);
                            break;
                            }
                        }
                    switch ($doc_element){
                        case 'all':
                        case 'config':                          
                        $msg .= '<p></p>';
                        // fichier de config & access
                        $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_CONFIG') .'</u></b>';
                        $msg .= '<br />=> ' .  $g_se_MD->MakeMDConfigDev($element) ;
                        $msg .= '<p></p>';
                        break;
                        }
                    }
                break;            
                }
                
            $msg .= '<p></p>';               
            $items = $model->getModules();
            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_MODULES') .'</u></b>';
            // check Display_mod for Modules            
            switch ($display_mod){
                case 'user':            
                foreach ($items as $item) {
                    $category = $item->category;
                    $element = $item->element;
                    $type = $item->type;
                    $folder='';
                    $identifier = $item->identifier;
                    
                    $msg .=  '<br />=> ' .  $g_se_MD->MakeMDModuleUser($category, $element)  ;
                    }
                break;
                case 'dev':            
                foreach ($items as $item) {
                    $element = $item->element;
                    $type = $item->type;
                    $folder='';
                    $identifier = $item->identifier;
                    
                    $msg .=  '<br />=> ' .  $g_se_MD->MakeMDModuleDev($element)  ;
                    }
                break;            
                }     

            $msg .= '<p></p>';
            $items = $model->getPlugins();
            $msg .= '<b><u>'. JText::_('COM_MANIFEST2MD_CHECK_COMPONENT_PLUGINS') .'</u></b>';
            // check Display_mod for Plugins
            switch ($display_mod){
                case 'user':   
                foreach ($items as $item) {
                    $category = $item->category;
                    $element = $item->element;
                    $type = $item->type;
                    $folder = $item->folder;
                    $identifier = $item->identifier; 
                    
                    $msg .=  '<br />=> ' .  $g_se_MD->MakeMDPluginUser($category, $element, $folder) ;
                    }
                break;
                case 'dev':   
                foreach ($items as $item) {
                    $element = $item->element;
                    $type = $item->type;
                    $folder = $item->folder;
                    $identifier = $item->identifier;     
                    
                    $msg .=  '<br />=> ' .  $g_se_MD->MakeMDPluginDev($element, $folder) ;
                    }
                break;
                }
                
           // } END Languages Loop
      
        $this->setRedirect('index.php?option=com_manifest2md', $msg);
        
        }
    }