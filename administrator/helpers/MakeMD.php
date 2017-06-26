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
     * Manifest2mdClassMD::MakeMDViewsDev(
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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);

        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $xml = null;
        $list_xml = null;
        $get_xml = null;
        $home = null;
        $msg ='';
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }

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

                $content = $this->params['template_view'];
                $extension = str_replace('com_', '', $extension);
                
                // merge
                $content = str_replace('{category}', $category, $content);
                $content = str_replace('{extension}', $extension, $content);
                $content = str_replace('{extension_name}', $extension_name, $content);
                $content = str_replace('{extension_date}', $extension_date, $content);
                $content = str_replace('{extension_author}', $extension_author, $content);
                $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
                $content = str_replace('{description}', $description, $content);
                $content = str_replace('{parameters}', $parameters, $content);
                $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);

        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);

        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $xml = null;
        $list_xml = null;
        $get_xml = null;
        $home = null;
        $msg ='';
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
            
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

                $content = $this->params['template_view'];
                
                // merge
                $content = str_replace('{category}', $category, $content);
                $content = str_replace('{extension}', $extension, $content);
                $content = str_replace('{extension_name}', $extension_name, $content);
                $content = str_replace('{extension_date}', $extension_date, $content);
                $content = str_replace('{extension_author}', $extension_author, $content);
                $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
                $content = str_replace('{description}', $description, $content);
                $content = str_replace('{parameters}', $parameters, $content);
                $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);
        
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

    $content = $this->params['template_model'];
    $extension = str_replace('com_', '', $extension);
    
    // merge
    $content = str_replace('{extension}', $extension_name, $content);
    $content = str_replace('{object}', $object, $content);
    $content = str_replace('{parameters}', $parameters, $content);
    $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        
        $lang->load($extension, JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_COMPONENT_ADMINISTRATOR, $this->language, true);

        $lang->load($extension, JPATH_COMPONENT_SITE, $this->language, true);
        
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

    $content = $this->params['template_model'];
    
    // merge
    $content = str_replace('{extension}', $extension, $content);
    $content = str_replace('{object}', $object, $content);
    $content = str_replace('{parameters}', $parameters, $content);
    $content = str_replace('{language}', $this->language, $content);

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
     * Manifest2mdClassMD::MakeMDComponentDev(
     *
     * @param string $extension
     * @param string $identifier
     * @return string
     */
    public function MakeMDComponentDev($extension, $identifier)
    {
        $msg = "";
        $list = array();

        //get the list of all .xml files in the folder
        $original = JFolder::files(JPATH_ROOT . '/administrator/components/' . $extension . '/', '.xml');
        
        $msg .= $original;

        //create the final list that contains name of files
        $total = count($original);
        $index = 0;
        for ($i = 0; $i < $total; $i++) {
            //separate name&extension si besoin ...
            //remove the file extension and the dot from the filename
            $list[$index]['name'] = substr($original[$i], 0, -1 * (1 + strlen(JFile::getExt($original[$i]))));
            //add the extension
            // $list[$index]['ext'] = JFile::getExt($original[$i]);
            $msg .= self::MakeMDExtensionDev($extension, $list[$index]['name']);
            $index++;
        }
        return $msg;
    }

    /**
     * Manifest2mdClassMD::MakeMDComponentUser(
     *  UNUSED FUNCTION FOR THE MOMENT
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDComponentUser($category, $extension, $identifier)
    {
        $msg = "";
        $list = array();

        //get the list of all .xml files in the folder
        $original = JFolder::files(JPATH_ROOT . '/administrator/components/' . $extension . '/', '.xml');
        
        $msg .= $original;

        //create the final list that contains name of files
        $total = count($original);
        $index = 0;
        for ($i = 0; $i < $total; $i++) {
            //separate name&extension si besoin ...
            //remove the file extension and the dot from the filename
            $list[$index]['name'] = substr($original[$i], 0, -1 * (1 + strlen(JFile::getExt($original[$i]))));
            //add the extension
            // $list[$index]['ext'] = JFile::getExt($original[$i]);
            $msg .= self::MakeMDExtensionUser($category, $extension, $list[$index]['name']);
            $index++;
        }
        return $msg;
    }    
    
    /**
     * Manifest2mdClassMD::MakeMDExtensionDev()
     *
     * @param string $extension
     * @param string $name
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDExtensionDev($extension, $name)
        {
        $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/' . $name . '.xml';
        if (file_exists($xml)) {
            $get_xml = simplexml_load_file();
            $filename = $this->root .  '/' . $extension . '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }
        
            fwrite($handle, '# ' . $extension . ' Component' . PHP_EOL);

            fwrite($handle, '## Modules' . PHP_EOL);
            foreach ($get_xml->modules->module as $module) {
                fwrite($handle, '### ' . $module['name'] . PHP_EOL);
                }

            fwrite($handle, '## Plugins' . PHP_EOL);
            foreach ($get_xml->plugins->plugin as $plugin) {
                fwrite($handle, '### ' . $plugin['name'] . PHP_EOL);
                }
            fclose($handle);
            return (JPATH_ROOT . '/administrator/components/' . $extension . '/' . $name . '.xml');
            }
        else {
            $msg .= '<br /> => XML File: ' . $xml . ' not found!' ;
            return ($msg);
            }
        }

    /**
     * Manifest2mdClassMD::MakeMDExtensionUser()
     *
     * @param string $extension
     * @param string $category
     * @param string $name
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDExtensionUser($category, $extension, $name)
        {
        $xml = JPATH_ROOT . '/administrator/components/' . $extension . '/' . $name . '.xml';
        if (file_exists($xml)) {
            $get_xml = simplexml_load_file($xml);
            $filename = $this->root .  '/' . $category .  '/BackEnd/' . $name .  '.md';
            $msg .= self::CheckFolder($filename);
            $handle = fopen($filename, 'w');
            if ($handle === false)
                {
                $msg .= 'File: ' . $filename . ' not writable!' ;
                return ($msg);
                }

            $content = '# ' . $extension . ' Component' . PHP_EOL;
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

                    $parameters .= '| Action | Description |' . PHP_EOL;
                    $parameters .= '| ------ | ----------- |' . PHP_EOL;
                    foreach ($get_rules->section->action as $action) {
                        $sLine = ' | ' . JText::_($action['title']) . ' | ' . JText::_($action['description']) . ' | ';
                        $parameters .= $sLine . PHP_EOL;
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
            $content = $this->params['template_component'];

            // merge
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_date}', $extension_date, $content);
            $content = str_replace('{extension_author}', $extension_author, $content);
            $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
            $content = str_replace('{parameters}', $parameters, $content);
            $content = str_replace('{language}', $this->language, $content);

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

            fwrite($handle, '## Modules' . PHP_EOL);
            foreach ($get_xml->modules->module as $module) {
                $content .=  '### ' . $module['name'] . PHP_EOL;
            }

            fwrite($handle, '## Plugins' . PHP_EOL);
            foreach ($get_xml->plugins->plugin as $plugin) {
                $content .=  '### ' . $plugin['name'] . PHP_EOL;
            }
           // final writing
            $handle = fopen($filename, 'w');
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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load('mod_'. $extension, JPATH_MODULES.'/'.$extension.'/', $this->language, true); 

        $db = JFactory::getDbo();
        $xml = null;
        $get_xml = null;
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('module'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
        }
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

            if (!empty($get_xml->creationDate)) {
                $extension_date = $get_xml->creationDate;
            }
            if (!empty($get_xml->author)) {
                $extension_author = $get_xml->author;
            }
            if (!empty($get_xml->authorEmail)) {
                $extension_authorEmail = $get_xml->authorEmail;
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

            $content = $this->params['template_module'];

            // merge
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_name}', $extension_name, $content);
            $content = str_replace('{extension_date}', $extension_date, $content);
            $content = str_replace('{extension_author}', $extension_author, $content);
            $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
            $content = str_replace('{description}', $description, $content);
            $content = str_replace('{parameters}', $parameters, $content);
            $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load('mod_'. $extension, JPATH_MODULES.'/'.$extension.'/', $this->language, true); 

        $db = JFactory::getDbo();
        $xml = null;
        $get_xml = null;
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('module'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
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

            if (!empty($get_xml->creationDate)) {
                $extension_date = $get_xml->creationDate;
            }
            if (!empty($get_xml->author)) {
                $extension_author = $get_xml->author;
            }
            if (!empty($get_xml->authorEmail)) {
                $extension_authorEmail = $get_xml->authorEmail;
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

        $content = $this->params['template_module'];

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);
        $content = str_replace('{extension_date}', $extension_date, $content);
        $content = str_replace('{extension_author}', $extension_author, $content);
        $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
        $content = str_replace('{description}', $description, $content);
        $content = str_replace('{parameters}', $parameters, $content);
        $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_PLUGINS.'/'.$subpath.'/'.$extension.'/', $this->language, true); 
        
        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $xml = null;
        $get_xml = null;
        $home = null;
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
        }
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

            if (!empty($get_xml->creationDate)) {
                $extension_date = JText::_($get_xml->creationDate);
            }
            if (!empty($get_xml->author)) {
                $extension_author = JText::_($get_xml->author);
            }
            if (!empty($get_xml->authorEmail)) {
                $extension_authorEmail = JText::_($get_xml->authorEmail);
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

        $content = $this->params['template_plugin'];

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);
        $content = str_replace('{extension_date}', $extension_date, $content);
        $content = str_replace('{extension_author}', $extension_author, $content);
        $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
        $content = str_replace('{description}', $description, $content);
        $content = str_replace('{parameters}', $parameters, $content);
        $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_PLUGINS.'/'.$subpath.'/'.$extension.'/', $this->language, true); 
                
        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $get_xml = null;
        $home = null;
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
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

            if (!empty($get_xml->creationDate)) {
                $extension_date = JText::_($get_xml->creationDate);
            }
            if (!empty($get_xml->author)) {
                $extension_author = JText::_($get_xml->author);
            }
            if (!empty($get_xml->authorEmail)) {
                $extension_authorEmail = JText::_($get_xml->authorEmail);
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

        $content = $this->params['template_plugin'];

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_name}', $extension_name, $content);
        $content = str_replace('{extension_date}', $extension_date, $content);
        $content = str_replace('{extension_author}', $extension_author, $content);
        $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
        $content = str_replace('{description}', $description, $content);
        $content = str_replace('{parameters}', $parameters, $content);
        $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        
        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $xml = null;
        $get_xml = null;
        $home = null;
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
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

                    $parameters .= '| Action | Description |' . PHP_EOL;
                    $parameters .= '| ------ | ----------- |' . PHP_EOL;
                    foreach ($get_rules->section->action as $action) {
                        $sLine = ' | ' . JText::_($action['title']) . ' | ' . JText::_($action['description']) . ' | ';
                        $parameters .= $sLine . PHP_EOL;
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
            $content = $this->params['template_config'];
            $extension = str_replace('com_', '', $extension);

            // merge
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_date}', $extension_date, $content);
            $content = str_replace('{extension_author}', $extension_author, $content);
            $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
            $content = str_replace('{parameters}', $parameters, $content);
            $content = str_replace('{language}', $this->language, $content);

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
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        
        $db = JFactory::getDbo();
        $extension_date = "";
        $extension_author = "";
        $extension_authorEmail = "";
        $xml = null;
        $get_xml = null;
        $home = null;
        $query = $db->getQuery(true);

        $query->select($db->quoteName(['name', 'manifest_cache']))
            ->from($db->quoteName('#__extensions'))
            ->where('element = ' . $db->quote($extension))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
            
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

                    $parameters .= '| Action | Description |' . PHP_EOL;
                    $parameters .= '| ------ | ----------- |' . PHP_EOL;
                    foreach ($get_rules->section->action as $action) {
                        $sLine = ' | ' . JText::_($action['title']) . ' | ' . JText::_($action['description']) . ' | ';
                        $parameters .= $sLine . PHP_EOL;
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
            $content = $this->params['template_config'];
            $extension = str_replace('com_', '', $extension);

            // merge
            $content = str_replace('{category}', $category, $content);
            $content = str_replace('{extension}', $extension, $content);
            $content = str_replace('{extension_date}', $extension_date, $content);
            $content = str_replace('{extension_author}', $extension_author, $content);
            $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
            $content = str_replace('{parameters}', $parameters, $content);
            $content = str_replace('{language}', $this->language, $content);

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

    /**
     * @param array $url
     */
    public function setParams($params = [])
    {
        $this->params = $params;
    }

}

