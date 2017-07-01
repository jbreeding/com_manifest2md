<?php
/**
 * @version  V2.1.1
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */
 
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class Manifest2mdClassMD
{
    protected $root = null;
    protected $params = [];
    protected $language = 'en-GB';

    /**
     * Manifest2mdClassMD::MakeMDViewsDev()
     *
     * @param string $extension
     * @param string $identifier
     * @return string
     */
    public function MakeMDViewsDev($extension, $identifier)
        {
        $msg = "";
        //$list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $base_dir = JPATH_ROOT . '/components/' . $extension . '/views/';
            }
        else {
            $base_dir = JPATH_ROOT . '/administrator/components/' . $extension . '/views/';
            }
    
        foreach (scandir($base_dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $msg .= self::MakeMDViewDev($extension, $file, $identifier);
            }
        }
        return $msg;
    }

    /**
     * Manifest2mdClassMD::MakeMDViewsUser(
     *
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDViewsUser($category, $extension, $identifier)
        {
        $msg = "";
        //$list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $base_dir = JPATH_ROOT . '/components/' . $extension . '/views/';
        } else {
            $base_dir = JPATH_ROOT . '/administrator/components/' . $extension . '/views/';
            }
    
        foreach (scandir($base_dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $msg .= self::MakeMDViewUser($category, $extension, $file, $identifier);
            }
        }
    return $msg;
    }
    
    /**
     * Manifest2mdClassMD::MakeMDViewDev()
     *
     * @param $extension
     * @param string $subpath
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDViewDev($extension, $subpath, $identifier)
        {
        $xml = null;
        $list_xml = null;
        $get_xml = null;
        $params = $this->params;
        
        // Load All Language files of Extension
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);        
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        // $lang = self::LoadCompLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension,'component');
        $manifestMD = self::GetMDManifest();

        if ($identifier == "site") {

            $base_dir = JPATH_ROOT . '/components/' . $extension . '/views/' . $subpath . '/tmpl/' ;
            $newfolder = $this->root .  '/components/' . $extension . '/views/' . $subpath. '/tmpl/'; 
        } else {

            $base_dir = JPATH_ROOT . '/administrator/components/' . $extension . '/views/' . $subpath . '/tmpl/' ;
            $newfolder = $this->root .  '/administrator/components/' . $extension . '/views/' . $subpath . '/tmpl/'; 
            }

        // search all the xml layouts of the Current View ...
        $list_xml = JFolder::files($base_dir, '.xml', false, true);
        
        foreach ( $list_xml as $xml) {
            if (file_exists($xml)) {
                $get_xml = simplexml_load_file($xml);

                $extension_name =  JText::_($get_xml->layout['title']);
                $filename = $newfolder . $extension_name . '.md';
                $msg .= self::CheckFolder($filename);
                $handle = fopen($filename, 'w');
                if ($handle === false)
                    {
                    $msg .= 'File: ' . $filename . ' not writable!' ;
                    return ($msg);
                    }

                $description = trim($get_xml->layout->message);
                $healthy = ["<![CDATA[", "]]>"];
                $yummy = ["", ""];
                $description = str_replace($healthy, $yummy, $description);
                $description = JText::_($description);

                //parameters
                $parameters = "";
                foreach ($get_xml->fields->fieldset as $fieldset) 
                    {

                    $parameters .= '### ' . JText::_($fieldset['name']) . PHP_EOL;
                    $parameters .= '| Name | Label | Description | Type | Required | Default |' . PHP_EOL;
                    $parameters .= '| ---- | ------| ----------- | ---- | -------- | ------- |' . PHP_EOL;
                    
                    foreach ($fieldset->field as $field) 
                        {
                        $sLine = '| ' . $field['name'] . ' | ' . JText::_($field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $field['type'] . ' | '. $field['required'] .' | ' . $field['default'] . ' |';
                        $parameters .= $sLine . PHP_EOL;
                        }
                    }

                if ($params->get('template_view')) 
                    {  $content = $params->get('template_view');  }
                else 
                    {  $content = self::GetTemplateViewParams();  }
                $extension = str_replace('com_', '', $extension);

                // merge
                $content = str_replace('{category}', $category, $content);
                $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
                $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
                $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
                $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
                
                $content = str_replace('{language}', $this->language, $content);
                $content = str_replace('{extension}', $extension, $content);
                $content = str_replace('{extension_name}', $extension_name, $content);
                $content = str_replace('{extension_version}', $manifest['version'], $content);
                $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
                $content = str_replace('{extension_author}', $manifest['author'], $content);
                $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
                $content = str_replace('{parameters}', $parameters, $content);

                if ( !fwrite($handle, $content)) 
                    {
                    $msg .= 'File: ' . $filename . ' not writed!' ;
                    return ($msg);
                    }
                else 
                   {
                    $msg .='<br /> => '. $filename ;
                    fclose($handle);
                    }
                }
           else {
                $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                return ($msg);
                }             
            }
            return ($msg);
        }

    /**
     * Manifest2mdClassMD::MakeMDViewUser()
     *
     * @param $extension
     * @param string $subpath
     * @param string $category
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDViewUser($category, $extension, $subpath, $identifier)
        {
        $xml = null;
        $list_xml = null;
        $get_xml = null;
        $params = $this->params;
        
        // Load All Language files of Extension
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);
        // $lang = self::LoadCompLanguage($extension, $this->language);

        // Get few Manifest infos
        $manifest = self::GetManifest($extension,'component');   
        $manifestMD = self::GetMDManifest();
        
        if ($identifier == "site") {
            $base_dir = JPATH_ROOT . '/components/' . $extension . '/views/' . $subpath . '/tmpl/' ;
            $extension = str_replace('com_', '', $extension);
            $newfolder = $this->root . '/' .  $category . '/FrontEnd/Components/'. ucfirst($extension).'/Views/'; 
        } else {
            $base_dir = JPATH_ROOT . '/administrator/components/' . $extension . '/views/' . $subpath . '/tmpl/' ;
            $extension = str_replace('com_', '', $extension);
            $newfolder = $this->root . '/' .  $category . '/BackEnd/Components/'. ucfirst($extension).'/Views/'; 
            }

        // search all the xml layouts of the Current View ...
        $list_xml = JFolder::files($base_dir, '.xml', false, true);
        
        foreach ( $list_xml as $xml) {
            if (file_exists($xml)) {
                $get_xml = simplexml_load_file($xml);

                $extension_name =  JText::_($get_xml->layout['title']);
                $filename = $newfolder . $extension_name . '.md';
                $msg .= self::CheckFolder($filename);
                $handle = fopen($filename, 'w');
                if ($handle === false)
                    {
                    $msg .= 'File: ' . $filename . ' not writable!' ;
                    return ($msg);
                    }

                $description = trim($get_xml->layout->message);
                $healthy = ["<![CDATA[", "]]>"];
                $yummy = ["", ""];
                $description = str_replace($healthy, $yummy, $description);
                $description = JText::_($description);

                //parameters
                $parameters = "";
                foreach ($get_xml->fields->fieldset as $fieldset) 
                    {

                    $parameters .= '### ' . JText::_($fieldset['name']) . PHP_EOL;
                    $parameters .= '| Name | Label | Description | Type | Required | Default |' . PHP_EOL;
                    $parameters .= '| ---- | ------| ----------- | ---- | -------- | ------- |' . PHP_EOL;
                    
                    foreach ($fieldset->field as $field) 
                        {
                        $sLine = '| ' . $field['name'] . ' | ' . JText::_($field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $field['type'] . ' | '. $field['required'] .' | ' . $field['default'] . ' |';
                        $parameters .= $sLine . PHP_EOL;
                        }
                    }

                if ($params->get('template_view')) 
                    {  $content = $params->get('template_view');  }
                else 
                    {  $content = self::GetTemplateViewParams();  }
                $extension = str_replace('com_', '', $extension);
                
                // merge
                $content = str_replace('{category}', $category, $content);
                $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
                $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
                $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
                $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);

                $content = str_replace('{language}', $this->language, $content);
                $content = str_replace('{extension}', $extension, $content);
                $content = str_replace('{extension_name}', $extension_name, $content);
                $content = str_replace('{description}', $description, $content);
                $content = str_replace('{extension_version}', $manifest['version'], $content);
                $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
                $content = str_replace('{extension_author}', $manifest['author'], $content);
                $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
                $content = str_replace('{parameters}', $parameters, $content);

                if ( !fwrite($handle, $content)) 
                    {
                    $msg .= 'File: ' . $filename . ' not writed!' ;
                    return ($msg);
                    }
                else 
                   {
                    $msg .='<br /> => '. $filename ;
                    fclose($handle);
                    }
                }
           else {
                $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                return ($msg);
                }             
            }
            return ($msg);
        }        
        
    /**
     * Manifest2mdClassMD::MakeMDObjectsDev(
     *
     * @param string $extension
     * @param string $identifier
     * @return string
     */
    public function MakeMDModelsDev($extension, $identifier)
        {
        $msg = "";
        $list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $path = JPATH_ROOT . '/components/' . $extension . '/models/forms/';
            if (is_dir($path))
                $original = JFolder::files(JPATH_ROOT . '/components/' . $extension . '/models/forms/', '.xml');     
        } elseif ($identifier == "administrator") {
            $path = JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/';
            if (is_dir($path))
                $original = JFolder::files(JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/', '.xml');
            }
        if ($original){
            //create the final list that contains name of files
            $total = count($original);
            // $msg .= 'Nombre de fichiers .xml: ' . $total;
            $index = 0;
            for ($i = 0; $i < $total; $i++) {
                //separate name&extension si besoin ...
                //remove the file extension and the dot from the filename
                $list[$index]['name'] = substr($original[$i], 0, -1 * (1 + strlen(JFile::getExt($original[$i]))));
                //add the extension
                // $list[$index]['ext'] = JFile::getExt($original[$i]);
                $msg .= self::MakeMDModelDev($extension, $list[$index]['name'], $identifier);
                $index++;
            }
        }
        return $msg;
    }

    /**
     * Manifest2mdClassMD::MakeMDModelsUser(
     *
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDModelsUser($category, $extension, $identifier)
        {
        $msg = "";
        $list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $path = JPATH_ROOT . '/components/' . $extension . '/models/forms/';
            if (is_dir($path))
                $original = JFolder::files(JPATH_ROOT . '/components/' . $extension . '/models/forms/', '.xml');     
        } elseif ($identifier == "administrator") {
            $path = JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/';
            if (is_dir($path))
                $original = JFolder::files(JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/', '.xml');
            }
            
        if ($original){  
            //create the final list that contains name of files
            $total = count($original);
            // $msg .= 'Nombre de fichiers .xml: ' . $total;
            $index = 0;
            for ($i = 0; $i < $total; $i++) {
                //separate name&extension si besoin ...
                //remove the file extension and the dot from the filename
                $list[$index]['name'] = substr($original[$i], 0, -1 * (1 + strlen(JFile::getExt($original[$i]))));
                //add the extension
                // $list[$index]['ext'] = JFile::getExt($original[$i]);
                $msg .= self::MakeMDModelUser($category, $extension, $list[$index]['name'], $identifier);
                $index++;
            }
        }   
        return $msg;
    }
    
    /**
     * Manifest2mdClassMD::MakeMDModelDev()
     *
     * @param string $extension
     * @param string $object
     * @param string $identifier
     * @return string
     */
    public function MakeMDModelDev($extension, $object, $identifier)
        {
        $xml = null;
        $get_xml = null;
        $params = $this->params;
        
        // Load All Language files of Extension
        $lang = JFactory::getLanguage();
        $lang->load('joomla', JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true); 
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        // $lang = self::LoadCompLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension,'component');
        $manifestMD = self::GetMDManifest();
        
        if ($identifier == "site") {

            $xml = JPATH_ROOT . '/components/' . $extension . '/models/forms/' . $object . '.xml';
            if (file_exists($xml)) {
                $get_xml = simplexml_load_file($xml);
                $filename = $this->root . '/components/' . $extension .'/models/forms/' . $object . '.md';
                $msg .= self::CheckFolder($filename);
                $handle = fopen($filename, 'w');
                if ($handle === false)
                    {
                    $msg .= 'File: ' . $filename . ' not writable!' ;
                    return ($msg);
                    }  
                }
            else {
                $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                return ($msg);
                }            
        } elseif ($identifier == "administrator") {

            $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/' . $object . '.xml';
            if (file_exists($xml)) {
                $get_xml = simplexml_load_file($xml);
                $filename = $this->root .  '/administrator/components/'. $extension . '/models/forms/' . $object . '.md';
                $msg .= self::CheckFolder($filename);
                $handle = fopen($filename, 'w');
                if ($handle === false)
                    {
                    $msg .= 'File: ' . $filename . ' not writable!' ;
                    return ($msg);
                    }  
                }
            else {
                $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                return ($msg);
                }
            }

        //parameters
        $extension_name = str_replace('com_', '', $extension);        
        foreach ($get_xml->fieldset as $fieldset) {
        // Fieldsets parameters

        $parameters .= '### ' . JText::_($fieldset['label']) . ' ['.  $fieldset['name'] . ']' . PHP_EOL ;     
        if (isset($fieldset['addfieldpath'])) 
            $parameters .=    '#### Addfieldpath: ' . $fieldset['addfieldpath'] . PHP_EOL ;
        
        // Fields parameters
        $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
        $parameters .= '| ------ | ----------- | -----|-------|' . PHP_EOL;
        
        foreach ($fieldset->field as $field) {
            $first = true;
            $str = "";
            foreach ($field->option as $option) {
                if ($first) {
                    $str .= '`' . JText::_($option) . '`';
                    $first = false;
                } else {
                    $str .= ', `' . JText::_($option) . '`';
                }
            }
            $default = (isset($field['default'])) ? ' (default: `' . JText::_($field['default']) . '`)' : '';
            $type = $field['type'];
            $sLine = '| &nbsp;' . (empty(JText::_($field['label'])) ? JText::_($field['name']) : JText::_($field['label'])) . ' | ' . JText::_($field['description']) . ' | ' . JText::_($field['type']) . ' | ' . $str . $default . '|';
            $parameters .= $sLine . PHP_EOL;
            }
        }

    if ($params->get('template_model')) 
        {  $content = $params->get('template_model');  }
    else 
        {  $content = self::GetTemplateModelParams();  }
    $extension = str_replace('com_', '', $extension);
    
    // merge
    $content = str_replace('{category}', $category, $content);
    $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
    $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
    $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
    $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
    
    $content = str_replace('{language}', $this->language, $content); 
    $content = str_replace('{extension}', $extension, $content);
    $content = str_replace('{object}', $object, $content);
    $content = str_replace('{extension_version}', $manifest['version'], $content);
    $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
    $content = str_replace('{extension_author}', $manifest['author'], $content);
    $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
    $content = str_replace('{parameters}', $parameters, $content);   

    if ( !fwrite($handle, $content)) 
        {
        $msg .= '<br /> =>File: ' . $filename . ' not writed!' ;
        return ($msg);
        }
    else 
       {
        $msg .= '<br/>=> ' . $filename;
        return ($msg);
        fclose($handle);
        }
    }

    /**
     * Manifest2mdClassMD::MakeMDModelUser()
     *
     * @param string $category
     * @param string $extension
     * @param string $object
     * @param string $identifier
     * @return string
     */
    public function MakeMDModelUser($category, $extension, $object, $identifier)
        {
        $xml = null;
        $get_xml = null;
        $params = $this->params;
        
        // load all Language Files
        $lang = JFactory::getLanguage();
        $lang->load('joomla', JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true); 
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        // $msg.= self::LoadCompLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension,'component');
        $manifestMD = self::GetMDManifest();
        
        if ($identifier == "site") {
           
            $xml = JPATH_ROOT . '/components/' . $extension . '/models/forms/' . $object . '.xml';
            if (file_exists($xml)) {
                $get_xml = simplexml_load_file($xml);
                $extension = str_replace('com_', '', $extension);
                $filename = $this->root .'/'. $category . '/FrontEnd/Components/'. ucfirst($extension).'/Models/' . $object . '.md';
                $msg .= self::CheckFolder($filename);
                $handle = fopen($filename, 'w');
                if ($handle === false)
                    {
                    $msg .= 'File: ' . $filename . ' not writable!';
                    return ($msg);
                    }                    
                }
            else {
                $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                return ($msg);
                }            
        } elseif ($identifier == "administrator") {
            $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/' . $object . '.xml';
            if (file_exists($xml)) {
                    $get_xml = simplexml_load_file($xml);
                    $extension = str_replace('com_', '', $extension);
                    $filename = $this->root .'/'. $category . '/BackEnd/Components/'. ucfirst($extension).'/Models/' . $object . '.md';
                    $msg .= self::CheckFolder($filename);
                    $handle = fopen($filename, 'w');
                    if ($handle === false)
                        {
                        $msg .= 'File: ' . $filename . ' not writable!' ;
                        return ($msg);
                        }  
                    }
                else {
                    $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
                    return ($msg);
                    }
                }
        
        foreach ($get_xml->fieldset as $fieldset) {
        // Fieldsets parameters

        $parameters .= '### ' . JText::_($fieldset['label']) . ' ['.  $fieldset['name'] . ']' . PHP_EOL ;     
        if (isset($fieldset['addfieldpath'])) 
            $parameters .=    '#### Addfieldpath: ' . $fieldset['addfieldpath'] . PHP_EOL ;
        
        // Fields parameters
        $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
        $parameters .= '| ------ | ----------- | -----|-------|' . PHP_EOL;
        
        foreach ($fieldset->field as $field) {
            $first = true;
            $str = "";
            foreach ($field->option as $option) {
                if ($first) {
                    $str .= '`' . JText::_($option) . '`';
                    $first = false;
                } else {
                    $str .= ', `' . JText::_($option) . '`';
                }
            }
            $default = (isset($field['default'])) ? ' (default: `' . JText::_($field['default']) . '`)' : '';
            $type = $field['type'];
            $sLine = '| &nbsp;' . (empty(JText::_($field['label'])) ? JText::_($field['name']) : JText::_($field['label'])) . ' | ' . JText::_($field['description']) . ' | ' . JText::_($field['type']) . ' | ' . $str . $default . '|';
            $parameters .= $sLine . PHP_EOL;
            }
        }

    if ($params->get('template_model')) 
        {  $content = $params->get('template_model');  }
    else 
        {  $content = self::GetTemplateModelParams();  }
    $extension = str_replace('com_', '', $extension);
    
    // merge
    $content = str_replace('{category}', $category, $content);
    $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
    $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
    $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
    $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
    
    $content = str_replace('{language}', $this->language, $content); 
    $content = str_replace('{extension}', $extension, $content);
    $content = str_replace('{object}', $object, $content);
    $content = str_replace('{extension_version}', $manifest['version'], $content);
    $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
    $content = str_replace('{extension_author}', $manifest['author'], $content);
    $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
    $content = str_replace('{parameters}', $parameters, $content);   

    if ( !fwrite($handle, $content)) 
        {
        $msg .= '<br /> =>File: ' . $filename . ' not writed!' ;
        return ($msg);
        }
    else 
       {
        $msg .= '<br/>=> ' . $filename;
        return ($msg);
        fclose($handle);
        }
    }
      
    /**
     * Manifest2mdClassMD::MakeMDModuleDev()
     *
     * @param string $extension
     * @return int
     * @internal param string $subpath
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDModuleDev($extension)
        {
        $xml = null;
        $get_xml = null;
        
        $params = $this->params;
        
        $lang = JFactory::getLanguage();
        // $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension.'.sys', JPATH_SITE, $this->language, true);
        $lang->load('com_modules', JPATH_ADMINISTRATOR, $this->language, true); 
        $lang->load($extension, JPATH_SITE.'/modules/'.$extension.'/', $this->language, true);  
        $lang->load($extension.'.sys', JPATH_SITE.'/modules/'.$extension.'/', $this->language, true);  
        // $lang = self::LoadModuleLanguage($extension, $this->language);

        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'module');   
        $manifestMD = self::GetMDManifest();      
        
        $xml = JPATH_ROOT . '/modules/' . $extension . '/' . $extension . '.xml';
        if (file_exists($xml)){
            $get_xml = simplexml_load_file($xml);
            $extension_name = empty($get_xml->name) ? $extension : $get_xml->name;
            $extension_name = JText::_($extension_name);

            $filename = $this->root .  '/modules/'  . $extension . '/' . $extension_name . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }

            // description
            $description = trim($get_xml->description);
            $healthy = ["<![CDATA[", "]]>"];
            $yummy = ["", ""];
            $description = str_replace($healthy, $yummy, $description);
            $description = JText::_($description);

            // parameters
            $parameters = '';

            foreach ($get_xml->config->fields->fieldset as $fieldset) {
                $parameters .= '### ' . JText::_($fieldset['name']) . PHP_EOL;
                $parameters .= '| Option | Description | Value |' . PHP_EOL;
                $parameters .= '| ------ | ----------- | ----- |' . PHP_EOL;

                foreach ($fieldset->field as $field) {
                    $first = true;
                    $str = "";
                    foreach ($field->option as $option) {
                        if ($first) {
                            $str .= '`' . JText::_($option) . '`';
                            $first = false;
                        } else {
                            $str .= ', `' . JText::_($option) . '`';
                        }
                    }
                    $str = ($field['type'] != 'hidden') ? $str : '';
                    $default = (!empty($field['default'])) ? '(default:`' . JText::_($field['default']) . '`)' : '';
                    $sLine = '| &nbsp;' . JText::_(empty($field['label']) ? $field['name'] : $field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $str . $default . '|';

                    $parameters .= $sLine . PHP_EOL;
                }
            }

        if ($params->get('template_module')) 
            {  $content = $params->get('template_module');  }
        else 
            {  $content = self::GetTemplateModuleParams();  }

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
        $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
        $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
        $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
        
        $content = str_replace('{language}', $this->language, $content); 
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);      
        $content = str_replace('{description}', $description, $content); 
        $content = str_replace('{extension_version}', $manifest['version'], $content);
        $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
        $content = str_replace('{extension_author}', $manifest['author'], $content);
        $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
        $content = str_replace('{parameters}', $parameters, $content);    

            if ( !fwrite($handle, $content)) 
                {
                $msg .= 'File: ' . $filename . ' not writed!' ;
                return ($msg);
                }
            else 
               {
                return ($filename);
                fclose($handle);
                }
            }
        else {
            $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
            return ($msg);
            }
        }

    /**
     * Manifest2mdClassMD::MakeMDModuleUser()
     *
     * @param string $extension
     * @param string $category
     * @return int
     * @internal param string $subpath
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDModuleUser($category, $extension)
        {
        $xml = null;
        $get_xml = null;
        
        $params = $this->params;
        
        // load all Language Files
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension.'.sys', JPATH_SITE, $this->language, true);
        $lang->load('com_modules', JPATH_ADMINISTRATOR, $this->language, true); 
        $lang->load($extension, JPATH_SITE.'/modules/'.$extension.'/', $this->language, true);  
        $lang->load($extension.'.sys', JPATH_SITE.'/modules/'.$extension.'/', $this->language, true); 
        // $lang = self::LoadModuleLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'module');   
        $manifestMD = self::GetMDManifest();
        
        $xml = JPATH_ROOT . '/modules/' . $extension . '/' . $extension . '.xml';
        if (file_exists($xml)) {
            $get_xml = simplexml_load_file($xml);
            $extension_name = empty($get_xml->name) ? $extension : $get_xml->name;
            $extension_name = JText::_($extension_name);

            $filename = $this->root . '/' . $category . '/FrontEnd/Modules/' . $extension_name . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }

            // description
            $description = trim($get_xml->description);
            $healthy = ["<![CDATA[", "]]>"];
            $yummy = ["", ""];
            $description = str_replace($healthy, $yummy, $description);
            $description = JText::_($description);

            // parameters
            $parameters = '';

            foreach ($get_xml->config->fields->fieldset as $fieldset) {
                $parameters .= '### ' . JText::_($fieldset['name']) . PHP_EOL;
                $parameters .= '| Option | Description | Value |' . PHP_EOL;
                $parameters .= '| ------ | ----------- | ----- |' . PHP_EOL;

                foreach ($fieldset->field as $field) {
                    $first = true;
                    $str = "";
                    foreach ($field->option as $option) {
                        if ($first) {
                            $str .= '`' . JText::_($option) . '`';
                            $first = false;
                        } else {
                            $str .= ', `' . JText::_($option) . '`';
                        }
                    }
                $str = ($field['type'] != 'hidden') ? $str : '';
                $default = (!empty($field['default'])) ? '(default:`' . JText::_($field['default']) . '`)' : '';
                $sLine = '| &nbsp;' . JText::_(empty($field['label']) ? $field['name'] : $field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $str . $default . '|';

                $parameters .= $sLine . PHP_EOL;
                }
            }

        if ($params->get('template_module')) 
            {  $content = $params->get('template_module');  }
        else 
            {  $content = self::GetTemplateModuleParams();  }
        
        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
        $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
        $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
        $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
        $content = str_replace('{extension}', $extension, $content);
        
        $content = str_replace('{language}', $this->language, $content); 
        $content = str_replace('{extension_name}', $extension_name, $content);  
        $content = str_replace('{description}', $description, $content);          
        $content = str_replace('{extension_version}', $manifest['version'], $content);
        $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
        $content = str_replace('{extension_author}', $manifest['author'], $content);
        $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
        $content = str_replace('{parameters}', $parameters, $content);    
        
        if ( !fwrite($handle, $content)) 
            {
            $msg .= 'File: ' . $filename . ' not writed!' ;
            return ($msg);
            }
        else 
           {
            return ($filename);
            fclose($handle);
            }
        }
    else {
        $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
        return ($msg);
        }
    }
    
    /**
     * Manifest2mdClassMD::MakeMDPluginDev()
     *
     * @param string $extension
     * @param string $subpath
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDPluginDev($extension, $subpath)
        {        
        $xml = null;
        $get_xml = null;
        $params = $this->params;
        
        // load all Language Files
        $lang = JFactory::getLanguage();
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_PLUGINS.'/'.$subpath.'/'.$extension.'/', $this->language, true); 
        // $lang = self::LoadPluginLanguage($extension, $subpath, $this->language);

        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'plugin');   
        $manifestMD = self::GetMDManifest();

        $xml = JPATH_ROOT . '/plugins/' . $subpath . '/' . $extension . '/' . $extension . '.xml';
        $get_xml = simplexml_load_file($xml);
        if (file_exists($xml)){
            $extension_name = $get_xml->name;
            if (empty($extension_name)) {
                $extension_name = $extension;
            }
            $extension_name = JText::_($extension_name);

            $filename = $this->root .  '/plugins/' . $subpath . '/' . $extension . '/' . $extension_name . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }  

            // description
            $description = trim($get_xml->description);
            $healthy = ["<![CDATA[", "]]>"];
            $yummy = ["", ""];
            $description = str_replace($healthy, $yummy, $description);
            $description = JText::_($description);

            // parameters
            $parameters = "";
            foreach ($get_xml->config->fields->fieldset as $fieldset) {
                $parameters .= '### ' . JText::_($fieldset['label']) . '['. $fieldset['name'] .']' . PHP_EOL;
                $parameters .= '             ' . JText::_($fieldset['description']) . PHP_EOL;            
                $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
                $parameters .= '| ------ | ----------- | ---- | ----- |' . PHP_EOL;

                foreach ($fieldset->field as $field) {
                    $first = true;
                    $str = "";
                    foreach ($field->option as $option) {
                        if ($first) {
                            $str .= '`' . JText::_($option) . '`';
                            $first = false;
                        } else {
                            $str .= ', `' . JText::_($option) . '`';
                        }
                    }

                    $default = (!empty($field['default'])) ? '(default:`' . JText::_($field['default']) . '`)' : '';
                    $sLine = '| &nbsp;' . JText::_(empty($field['label']) ? $field['name'] : $field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $field['type'] . ' | ' . $str . $default . '|';

                    $parameters .= $sLine . PHP_EOL;
                }
            }

        if ($params->get('template_plugin')) 
            {  $content = $params->get('template_plugin');  }
        else 
            {  $content = self::GetTemplatePluginParams();  }

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
        $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
        $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
        $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);

        $content = str_replace('{language}', $this->language, $content); 
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);        
        $content = str_replace('{extension_version}', $manifest['version'], $content);
        $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
        $content = str_replace('{extension_author}', $manifest['author'], $content);
        $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
        $content = str_replace('{description}', $description, $content);
        $content = str_replace('{parameters}', $parameters, $content);

        if ( !fwrite($handle, $content)) 
            {
            $msg .= 'File: ' . $filename . ' not writed!' ;
            return ($msg);
            }
        else 
           {
            return ($filename);
            fclose($handle);
            }
        }
    else {
        $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
        return ($msg);
        }
            
    }
    
    /**
     * Manifest2mdClassMD::MakeMDPluginUser()
     *
     * @param string $extension
     * @param string $subpath
     * @param string $category
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDPluginUser($category, $extension, $subpath)
        {
        
        $get_xml = null;
        $xml = null;
        $params = $this->params;
        
        // load all Language Files
        $lang = JFactory::getLanguage();
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_PLUGINS.'/'.$subpath.'/'.$extension.'/', $this->language, true); 
        // $lang = self::LoadPluginLanguage($extension, $subpath, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'plugin');  
        $manifestMD = self::GetMDManifest();
        
        $xml = JPATH_ROOT . '/plugins/' . $subpath . '/' . $extension . '/' . $extension . '.xml';
        if (file_exists($xml)){
            $get_xml = simplexml_load_file($xml);

            $extension_name = $get_xml->name;
            if (empty($extension_name)) {
                $extension_name = $extension;
            }
            $extension_name = JText::_($extension_name);

            $filename = $this->root . '/' .$category . '/FrontEnd/Plugins/' . $subpath . '_' . $extension_name . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }

            // description
            $description = trim($get_xml->description);
            $healthy = ["<![CDATA[", "]]>"];
            $yummy = ["", ""];
            $description = str_replace($healthy, $yummy, $description);
            $description = JText::_($description);

            // parameters
            $parameters = "";
            foreach ($get_xml->config->fields->fieldset as $fieldset) {
                $parameters .= '### ' . JText::_($fieldset['label']) . '['. $fieldset['name'] .']' . PHP_EOL;
                $parameters .= '             ' . JText::_($fieldset['description']) . PHP_EOL;            
                $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
                $parameters .= '| ------ | ----------- | ---- | ----- |' . PHP_EOL;

                foreach ($fieldset->field as $field) {
                    $first = true;
                    $str = "";
                    foreach ($field->option as $option) {
                        if ($first) {
                            $str .= '`' . JText::_($option) . '`';
                            $first = false;
                        } else {
                            $str .= ', `' . JText::_($option) . '`';
                        }
                    }

                    $default = (!empty($field['default'])) ? '(default:`' . JText::_($field['default']) . '`)' : '';
                    $sLine = '| &nbsp;' . JText::_(empty($field['label']) ? $field['name'] : $field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $field['type'] . ' | ' . $str . $default . '|';

                    $parameters .= $sLine . PHP_EOL;
                }
            }

        if ($params->get('template_plugin')) 
            {  $content = $params->get('template_plugin');  }
        else 
            {  $content = self::GetTemplatePluginParams();  }

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
        $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
        $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
        $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
        
        $content = str_replace('{language}', $this->language, $content); 
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);        
        $content = str_replace('{extension_version}', $manifest['version'], $content);
        $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
        $content = str_replace('{extension_author}', $manifest['author'], $content);
        $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
        $content = str_replace('{description}', $description, $content);
        $content = str_replace('{parameters}', $parameters, $content);
        

        if ( !fwrite($handle, $content)) 
            {
            $msg .= 'File: ' . $filename . ' not writed!' ;
            return ($msg);
            }
        else 
           {
            return ($filename);
            fclose($handle);
            }
        }    
    else {
        $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
        return ($msg);
        }        
    }

    /**
     * Manifest2mdClassMD::MakeMDConfigDev()
     *
     * @param string $extension
     * @return string
     */
    public function MakeMDConfigDev($extension)
        {
        $xml = null;
        $get_xml = null;
        $home = null;
        
        $params = $this->params;
        
        // load all Language Files
        $lang = JFactory::getLanguage();
        $lang->load('joomla', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('com_categories', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        // $lang = self::LoadCompLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'component');
        $manifestMD = self::GetMDManifest();

        $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/config.xml';
        if (file_exists($xml)){
            $get_xml = simplexml_load_file($xml);
            $filename = $this->root .  '/administrator/components/' . $extension . '/config_' . $extension . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }  

            $parameters = "" ; 
            foreach ($get_xml->fieldset as $fieldset) 
                {
                // Fieldsets parameters
                $parameters .= '### ' . JText::_($fieldset['label']) . ' ['.  $fieldset['name'] . ']' . PHP_EOL ;
                $parameters .=  '#### Description: ' .  JText::_($fieldset['description'])  . PHP_EOL ;      
                if (isset($fieldset['addfieldpath'])) 
                    $parameters .=    '#### Addfieldpath: ' . $fieldset['addfieldpath'] . PHP_EOL ; 

                if ($fieldset['name'] == 'permissions')
                    { // debut permissions
                    $get_rules = simplexml_load_file(JPATH_ROOT . '/administrator/components/' . $extension . '/access.xml');
                    foreach ($get_rules->section as $section){
                        $parameters .=    '#### ' . $section['name'] . PHP_EOL ; 
                        
                        $parameters .= '| Action | Description |' . PHP_EOL;
                        $parameters .= '| ------ | ----------- |' . PHP_EOL;
                        foreach ($get_rules->section->action as $action) {
                            $sLine = ' | ' . JText::_($action['title']) . ' | ' . JText::_($action['description']) . ' | ';
                            $parameters .= $sLine . PHP_EOL;
                            }
                        }
                    } // fin permissions
                else 
                    {
                    // Fields parameters
                    $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
                    $parameters .= '| ------ | ----------- | -----|-------|' . PHP_EOL;

                    foreach ($fieldset->field as $field)
                        {
                        $first = true;
                        $str = "";
                        foreach ($field->option as $option) 
                            {
                            if ($first) {
                                $str .= '`' . JText::_($option) . '`';
                                $first = false;
                            } else {
                                $str .= ', `' . JText::_($option) . '`';
                                }
                            }

                        $default = (isset($field['default'])) ? ' (default: `' . JText::_($field['default']) . '`)' : '';
                        $type = $field['type'];
                        $sLine = '| &nbsp;' . JText::_($field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $type . ' | ' . $str . $default . '|';
                        $parameters .= $sLine . PHP_EOL;

                        }
                    }
                }
            if ($params->get('template_config')) 
                {  $content = $params->get('template_config');  }
            else 
                {  $content = self::GetTemplateConfigParams();  }
            $extension = str_replace('com_', '', $extension);

            // Manifest2MD infos
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
            $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
            $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
            $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
            // Extension infos
            $content = str_replace('{language}', $this->language, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_version}', $manifest['version'], $content);
            $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
            $content = str_replace('{extension_author}', $manifest['author'], $content);
            $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
            $content = str_replace('{parameters}', $parameters, $content);
            
            if ( !fwrite($handle, $content)) 
                {
                $msg .= 'File: ' . $filename . ' not writed!' ;
                return ($msg);
                }
            else 
               {
                return ($filename);
                fclose($handle);
                }
            }
        else {
            $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
            return ($msg);
            }
        }

    /**
     * Manifest2mdClassMD::MakeMDConfig()
     *
     * @param string $extension
     * @param string $category
     * @return string
     */
    public function MakeMDConfigUser($category, $extension)
        {
        $xml = null;
        $get_xml = null;
        $home = null;
        
        $params = $this->params;
        
        $lang = JFactory::getLanguage();
        $lang->load('joomla', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('com_categories', JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
       
        //$lang = self::LoadCompLanguage($extension, $this->language);
        
        // Get few Manifest infos
        $manifest = self::GetManifest($extension, 'component');
        $manifestMD = self::GetMDManifest();
            
        $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/config.xml';
        if (file_exists($xml)){
            $get_xml = simplexml_load_file($xml);
            $filename = $this->root . '/' . $category . '/BackEnd/config_' . $extension . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }
            
            $parameters = "" ; 
            foreach ($get_xml->fieldset as $fieldset) 
                {
                // Fieldsets parameters
                $parameters .= '### ' . JText::_($fieldset['label']) . ' ['.  $fieldset['name'] . ']' . PHP_EOL ;
                $parameters .=  '#### Description: ' .  JText::_($fieldset['description'])  . PHP_EOL ;      
                if (isset($fieldset['addfieldpath'])) 
                    $parameters .=    '#### Addfieldpath: ' . $fieldset['addfieldpath'] . PHP_EOL ; 

                if ($fieldset['name'] == 'permissions')
                    { // debut permissions
                    $get_rules = simplexml_load_file(JPATH_ROOT . '/administrator/components/' . $extension . '/access.xml');
                    foreach ($get_rules->section as $section){
                        $parameters .=    '#### ' . $section['name'] . PHP_EOL ; 
                        
                        $parameters .= '| Action | Description |' . PHP_EOL;
                        $parameters .= '| ------ | ----------- |' . PHP_EOL;
                        foreach ($section->action as $action) {
                            $sLine = ' | ' . JText::_($action['title']) . ' | ' . JText::_($action['description']) . ' | ';
                            $parameters .= $sLine . PHP_EOL;
                            }
                        }
                    } // fin permissions
                else 
                    {
                    // Fields parameters
                    $parameters .= '| Option | Description | Type | Value |' . PHP_EOL;
                    $parameters .= '| ------ | ----------- | -----|-------|' . PHP_EOL;

                    foreach ($fieldset->field as $field)
                        {
                        $first = true;
                        $str = "";
                        foreach ($field->option as $option) 
                            {
                            if ($first) {
                                $str .= '`' . JText::_($option) . '`';
                                $first = false;
                            } else {
                                $str .= ', `' . JText::_($option) . '`';
                                }
                            }

                        $default = (isset($field['default'])) ? ' (default: `' . JText::_($field['default']) . '`)' : '';
                        $type = $field['type'];
                        $sLine = '| &nbsp;' . JText::_($field['label']) . ' | ' . JText::_($field['description']) . ' | ' . $type . ' | ' . $str . $default . '|';
                        $parameters .= $sLine . PHP_EOL;

                        }
                    }
                }
            if ($params->get('template_config')) 
                {  $content = $params->get('template_config');  }
            else 
                {  $content = self::GetTemplateConfigParams();  }
            $extension = str_replace('com_', '', $extension);

            // merge
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{manifest2MD_version}', $manifestMD['version'], $content);
            $content = str_replace('{manifest2MD_creation_date}', $manifestMD['creationDate'], $content);
            $content = str_replace('{manifest2MD_author}', $manifestMD['author'], $content);
            $content = str_replace('{manifest2MD_authorEmail}', $manifestMD['authorEmail'], $content);
            
            $content = str_replace('{language}', $this->language, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_version}', $manifest['version'], $content);
            $content = str_replace('{extension_creation_date}', $manifest['creationDate'], $content);
            $content = str_replace('{extension_author}', $manifest['author'], $content);
            $content = str_replace('{extension_authorEmail}', $manifest['authorEmail'], $content);
            $content = str_replace('{parameters}', $parameters, $content);

            if ( !fwrite($handle, $content)) 
                {
                $msg .= 'File: ' . $filename . ' not writed!' ;
                return ($msg);
                }
            else 
               {
                return ($filename);
                fclose($handle);
                }
            }
        else {
            $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
            return ($msg);
            }
        }
        
    /**
     * Check or Create the directories to host the .md file
     */
    public function CheckFolder($file)
        {
        $dir = dirname($file);
        $path = '';
        $folders = explode('/', $dir);
        foreach($folders as $folder)
                {
            $path .= $folder . '/';

            if(!is_dir($path))
                    JFolder::create($path);

            }               
       return;
        }
    /**
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        $this->language = (empty($lang)) ? 'en-FR' : $lang;
    }

    /**
     * @param string $url
     */
    public function setRoot($url = JPATH_ROOT . '/documentation/')
    {
        $this->root = $url;
        $this->root = rtrim($this->root, '/') . '/' . $this->language ;
    }
                
    public function GetManifest($extension, $type){    
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote($type));

        $db->setQuery($query);
        $result = $db->loadObject();

        $decode = json_decode($result->manifest_cache);
        $manifest['version'] = $decode->version;
        $manifest['creationDate'] = $decode->creationDate;
        $manifest['author'] = $decode->author;
        $manifest['authorEmail'] = $decode->authorEmail;
        
        return $manifest;
            
        }
        
    public function GetMDManifest(){    
        
        $manifestMD = array();
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('type = ' . $db->quote('component'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_manifest2md'));

        $db->setQuery($query);
        $result = $db->loadObject();

        $decode = json_decode($result->manifest_cache);
        $manifestMD['version'] = $decode->version;
        $manifestMD['author'] = $decode->author;
        $manifestMD['authorEmail'] = $decode->authorEmail;
        
        return $manifestMD;
        }
        
    public function GetTemplateViewParams (){
        $params['template_view'] = 'Document {language}';
        $params['template_view'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension} [{language}]' . PHP_EOL;
        $params['template_view'] .= '## '. JText::_('COM_MANIFEST2MD_VIEW_EXT') .' {extension_name}' . PHP_EOL;
        $params['template_view'] .= '### {description}' . PHP_EOL;
        $params['template_view'] .= PHP_EOL;
        $params['template_view'] .= '#### '. JText::_('COM_MANIFEST2MD_LINK_MENU_FILTERS') . PHP_EOL;
        $params['template_view'] .= JText::_('COM_MANIFEST2MD_LINK_MENU_OPTIONS') . PHP_EOL;
        $params['template_view'] .= '{parameters}';
        $params['template_view'] .= '<p>&nbsp;</p>' . PHP_EOL;
        $params['template_view'] .= JText::_('COM_MANIFEST2MD_THANK_YOU_SO_MUCH') . PHP_EOL;
        $params['template_view'] .= '> ###### Generated with **Manifest2md** V{manifest2MD_version} by **{manifest2MD_author}** ([{manifest2MD_authorEmail}])'. PHP_EOL;
        $params['template_view'] .= '> ###### Document of Extension **{extension}** V{extension_version} created on *{extension_creation_date}* by **{extension_author}** ([{extension_authorEmail}])'. PHP_EOL;
        
        return $params['template_view'];
        }        
        
        
    public function GetTemplateModelParams (){
        $params['template_model'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension} [{language}]' . PHP_EOL;
        $params['template_model'] .= '##  '. JText::_('COM_MANIFEST2MD_MODEL_OBJECT') .' {object}' . PHP_EOL;
        $params['template_model'] .= '{parameters}';
        $params['template_model'] .= '<p>&nbsp;</p>' . PHP_EOL;
        $params['template_model'] .= JText::_('COM_MANIFEST2MD_THANK_YOU_SO_MUCH') . PHP_EOL;
        $params['template_model'] .= '> ###### Generated with **Manifest2md** V{manifest2MD_version} by **{manifest2MD_author}** ([{manifest2MD_authorEmail}])'. PHP_EOL;
        $params['template_model'] .= '> ###### Document of Extension **{extension}** V{extension_version} created on *{extension_creation_date}* by **{extension_author}** ([{extension_authorEmail}])'. PHP_EOL;

        return $params['template_model'];
        }        
        

    public function GetTemplateConfigParams (){

        $params['template_config'] .= '# '. JText::_('COM_MANIFEST2MD_COMPONENT') .' {extension} [{language}]' . PHP_EOL;
        $params['template_config'] .= '## '. JText::_('COM_MANIFEST2MD_PARAMETERS_CONFIG') . PHP_EOL;
        $params['template_config'] .= '{parameters}';
        $params['template_config'] .= '<p>&nbsp;</p>' . PHP_EOL;
        $params['template_config'] .= JText::_('COM_MANIFEST2MD_THANK_YOU_SO_MUCH') . PHP_EOL;
        $params['template_config'] .= '> ###### Generated with **Manifest2md** V{manifest2MD_version} by **{manifest2MD_author}** ([{manifest2MD_authorEmail}])'. PHP_EOL;
        $params['template_config'] .= '> ###### Document of Extension **{extension}** V{extension_version} created on *{extension_creation_date}* by **{extension_author}** ([{extension_authorEmail}])'. PHP_EOL;

        return $params['template_config'];
       }
 
    public function GetTemplateModuleParams (){

        $params['template_module'] .= '# Module - {extension_name} [{language}]' . PHP_EOL;
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
        $params['template_module'] .= JText::_('COM_MANIFEST2MD_THANK_YOU_SO_MUCH') . PHP_EOL;
        $params['template_module'] .= '> ###### Generated with **Manifest2md** V{manifest2MD_version} by **{manifest2MD_author}** ([{manifest2MD_authorEmail}])'. PHP_EOL;
        $params['template_module'] .= '> ###### Document of Extension **{extension}** V{extension_version} created on *{extension_creation_date}* by **{extension_author}** ([{extension_authorEmail}])'. PHP_EOL;

        return $params['template_module'] ;
        }           
           
     public function GetTemplatePluginParams (){

        $params['template_plugin'] .= '# Plugin - {extension_name} [{language}]' . PHP_EOL;
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
        $params['template_plugin'] .= '> ###### Generated with **Manifest2md** V{manifest2MD_version} by **{manifest2MD_author}** ([{manifest2MD_authorEmail}])'. PHP_EOL;
        $params['template_plugin'] .= '> ###### Document of Extension **{extension}** V{extension_version} created on *{extension_creation_date}* by **{extension_author}** ([{extension_authorEmail}])'. PHP_EOL;

        return $params['template_plugin'];
        }          
                   
    public function setParams($params = [])
        {
            $this->params = $params;
        }    
        
    }

