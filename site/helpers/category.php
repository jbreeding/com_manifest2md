<?php
/**
 * @version    CVS: 2.1.1
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */
 
// No direct access
defined('_JEXEC') or die;

/**
 * Content Component Category Tree
 *
 * @since  1.6
 */
class Manifest2mdCategories extends JCategories
{
    /**
     * Class constructor
     *
     * @param   array $options Array of options
     *
     * @since   11.1
     */
    public function __construct($options = array())
    {
        $options['table'] = '#__manifest2md_extensions';
        $options['extension'] = 'com_manifest2md';

        parent::__construct($options);
    }
}
