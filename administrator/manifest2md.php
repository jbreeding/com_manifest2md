<?php
/**
 * @version    2.0.0
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_manifest2md')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Manifest2md', JPATH_COMPONENT_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Manifest2md');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
