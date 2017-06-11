<?php
/**
 * @version  V2.0
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
     * Manifest2mdClassMD::MakeMDViews(
     *
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDViews($category = "Manifest2md", $extension = "com_manifest2md", $identifier = "site")
    {
        $msg = "";
        //$list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $base_dir = JPATH_ROOT . '/components/' . $extension . '/views/';
            
            $folder[6][0] = 'components/' . $extension;
            $folder[6][1] = $this->root . '/components/' . $extension . '/';  
            
            $folder[7][0] = 'components/' .  $extension . '/views';
            $folder[7][1] = $this->root . '/components/' .  $extension . '/views/';
            
        } else {
            $base_dir = JPATH_ROOT . '/administrator/components/' . $extension . '/views/';
            
            $folder[6][0] = 'administrator/components/' . $extension;
            $folder[6][1] = $this->root . '/administrator/components/' . $extension . '/';  
            
            $folder[7][0] = 'administrator/components/' .  $extension . '/views';
            $folder[7][1] = $this->root . '/administrator/components/' .  $extension . '/views/';
        }
    
        foreach (scandir($base_dir) as $file) {
            if ($file == '.' || $file == '..') continue;
            $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $msg .= self::MakeMDView($category, $extension, $file, $identifier);
            }
        }
       //  $msg .= '<p>&nbsp</p>';
        return $msg;
    }


    /**
     * Manifest2mdClassMD::MakeMD()
     *
     * @param $extension
     * @param string $subpath
     * @param string $category
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDView($category = "Manifest2md", $extension, $subpath = "", $identifier="site")
        {
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);

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

                // final writing
                $handle = fopen($filename, 'w');
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
     * Manifest2mdClassMD::MakeMDObjects(
     *
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDObjects($category = "Manifest2md", $extension = "com_manifest2md", $identifier = "site")
    {
        $msg = "";
        $list = array();

        //get the list of all .xml files in the folder
        if ($identifier == "site") {
            $original = JFolder::files(JPATH_ROOT . '/components/' . $extension . '/models/forms/', '.xml');
            
            $folder[8][0] = 'components/' . $extension . '/models';
            $folder[8][1] = $this->root . '/components/' . $extension  . '/models/';
            
            $folder[9][0] = 'components/' . $extension . '/models/forms';
            $folder[9][1] = $this->root . '/components/' . $extension  . '/models/forms/';
            
        } elseif ($identifier == "administrator") {
            $original = JFolder::files(JPATH_ROOT . '/administrator/components/' . $extension . '/models/forms/', '.xml');
            
            $folder[8][0] = 'administrator/components/' . $extension . '/models';
            $folder[8][1] = $this->root . '/administrator/components/' . $extension  . '/models/';
            
            $folder[9][0] = 'administrator/components/' . $extension . '/models/forms';
            $folder[9][1] = $this->root . '/administrator/components/' . $extension  . '/models/forms/';
        }
    
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
            $msg .= self::MakeMDObject($category, $extension, $list[$index]['name'], $identifier);
            $index++;
        }
        return $msg;
    }

    /**
     * Manifest2mdClassMD::MakeMDObject()
     *
     * @param string $extension
     * @param string $object
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDObject($category = "Manifest2md", $extension = "com_manifest2md", $object = "event", $identifier = "site")
    {
        $xml = null;
        $get_xml = null;
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);

        if ($identifier == "site") {
            $xml = JPATH_ROOT . '/components/' . $extension . '/models/forms/' . $object . '.xml';
            if (file_exists($xml)) {
                    $get_xml = simplexml_load_file($xml);
                    $filename = $this->root . '/components/' . $extension .'/models/forms/' . $object . '.md';
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

    // merge
    $content = str_replace('{extension}', $extension_name, $content);
    $content = str_replace('{object}', $object, $content);
    $content = str_replace('{parameters}', $parameters, $content);
    $content = str_replace('{language}', $this->language, $content);

    // final writing
    $handle = fopen($filename, 'w');
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
     * Manifest2mdClassMD::MakeMDComponent(
     *
     * @param string $extension
     * @param string $category
     * @param string $identifier
     * @return string
     */
    public function MakeMDComponent($category = "Manifest2md", $extension = "com_manifest2md", $identifier = "site")
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
            $msg .= self::MakeMDExtension($category, $extension, $list[$index]['name']);
            $index++;
        }
        return $msg;
    }

    /**
     * Manifest2mdClassMD::MakeMDExtension()
     *
     * @param string $extension
     * @param string $category
     * @param string $name
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDExtension($category = "Manifest2md", $extension = "com_manifest2md", $name = 'allevents')
    {
        $get_xml = simplexml_load_file(JPATH_ROOT . '/administrator/components/' . $extension . '/' . $name . '.xml');
        $filename = $this->root .  '/' . $extension . '.md';
        $handle = fopen($filename, 'w');

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

    /**
     * Manifest2mdClassMD::MakeMD()
     *
     * @param string $extension
     * @param string $category
     * @return int
     * @internal param string $subpath
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDModule($category = "Manifest2md", $extension = "mod_aesearch")
    {
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension, JPATH_SITE, $this->language, true);

        $db = JFactory::getDbo();
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

        $get_xml = simplexml_load_file(JPATH_ROOT . '/modules/' . $extension . '/' . $extension . '.xml');
        $extension_name = empty($get_xml->name) ? $extension : $get_xml->name;
        $extension_name = JText::_($extension_name);

        $filename = $this->root .  '/modules/'  . $extension . '/' . $extension_name . '.md';

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

    /**
     * Manifest2mdClassMD::MakeMDPlugin()
     *
     * @param string $extension
     * @param string $subpath
     * @param string $category
     * @return int
     * @internal param mixed $entity
     * @internal param mixed $entities
     */
    public function MakeMDPlugin($category = "Manifest2md", $extension = "", $subpath = "")
    {
        $lang = JFactory::getLanguage();
        // $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        // $lang->load($extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_SITE, $this->language, true);
        $lang->load('plg_' . $subpath . '_' . $extension, JPATH_ADMINISTRATOR, $this->language, true);

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

        $get_xml = simplexml_load_file(JPATH_ROOT . '/plugins/' . $subpath . '/' . $extension . '/' . $extension . '.xml');

        $extension_name = $get_xml->name;
        if (empty($extension_name)) {
            $extension_name = $extension;
        }
        $extension_name = JText::_($extension_name);

        $filename = $this->root .  '/plugins/' . $subpath . '/' . $extension . '/' . $extension_name . '.md';

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

    /**
     * Manifest2mdClassMD::MakeMDConfig()
     *
     * @param string $extension
     * @param string $category
     * @return string
     */
    public function MakeMDConfig($category = "Manifest2md", $extension = "com_manifest2md")
        {
        $lang = JFactory::getLanguage();
        $lang->load($extension, JPATH_ADMINISTRATOR, $this->language, true);
        $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $this->language, true);
        
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
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $result) {
            $decode = json_decode($result->manifest_cache);
            $extension_date = $decode->creationDate;
            $extension_author = $decode->author;
            $extension_authorEmail = $decode->authorEmail;
            }
        
        $get_xml = simplexml_load_file(JPATH_ROOT . '/administrator/components/' . $extension . '/config.xml');
        $filename = $this->root .  '/administrator/components/' . $extension . '/config_' . $extension . '.md';

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

        // merge
        $content = str_replace('{category}', $category, $content);
        $content = str_replace('{extension}', $extension, $content);
        $content = str_replace('{extension_date}', $extension_date, $content);
        $content = str_replace('{extension_author}', $extension_author, $content);
        $content = str_replace('{extension_authorEmail}', $extension_authorEmail, $content);
        $content = str_replace('{parameters}', $parameters, $content);
        $content = str_replace('{language}', $this->language, $content);

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

    /**
     * Find define the search structure
     */
    public function CheckFolder($type, $element, $folder, $identifier)
    {
     $current_path = array();
     switch ($type) {
        case 'component':
        switch ($identifier) {
                // $current_path[0][0] => path to search xml files recursively
                // $current_path[0][1] => path to create if xml exists
              case 'site':
                $current_path[0][0] = JPATH_ROOT .'/components/'.$element;
                $current_path[0][1] = 'components/'.$element;
                break;
             case 'administrator':
                $current_path[0][0] = JPATH_ROOT .'/administrator/components/'.$element;
                $current_path[0][1] = 'administrator/components/'.$element;         
                break;
             case 'both':
                $current_path[0][0] = JPATH_ROOT .'/components/'.$element;
                $current_path[0][1] = 'components/'.$element;
                $current_path[1][0] = JPATH_ROOT .'/administrator/components/'.$element;
                $current_path[1][1] = 'administrator/components/'.$element;   
                 
                break;
            }
         break;   

         case 'module':
         $current_path[0][0] = JPATH_ROOT .'/modules/'.$element;    
         $current_path[0][1] = 'modules/'.$element;
         break;         
  
         case 'plugin':
         $current_path[0][0] = JPATH_ROOT .'/plugins/'. $folder .'/' . $element;
         $current_path[0][1] = 'plugins/'. $folder .'/' . $element;             
         break;       
         }
        
            
         foreach ($current_path as $key => $value) {   
            $files =  JFolder::files( $value[0], '.xml', true, true );
            $base = $this->root;
            
            foreach ($files as $file) {
                $dir = dirname($file);
                $dir = str_replace(JPATH_ROOT, $base, $dir);
                $path = '';
 		// Create blank index.html files to every sub folder
		$folders = explode('/', $dir);
		foreach($folders as $folder)
		{
                    $path .= $folder . '/';

                    if(!is_dir($path))
                            JFolder::create($path);

		}               
            }
         }
       
       return $msg;
    }
	
	
    /**
     * @param string $lang
     */
    public function setLanguage($lang = 'en-GB')
    {
        $this->language = (empty($lang)) ? 'en-GB' : $lang;
    }

    /**
     * @param string $url
     */
    public function setRoot($url = JPATH_ROOT . '/documentation/docs/')
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

