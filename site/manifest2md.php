<?php
/**
 * @version  V2.0
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Manifest2md', JPATH_COMPONENT);
JLoader::register('Manifest2mdController', JPATH_COMPONENT . '/controller.php');


// Execute the task.
$controller = JControllerLegacy::getInstance('Manifest2md');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
