<?php

/**
 * @version    CVS: 2.1.1
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Class JFormFieldSubmit
 *
 * @since  1.6
 */
class JFormFieldSubmit extends JFormField
{
    protected $type = 'submit';

    protected $value;

    protected $for;

    /**
     * Get a form field markup for the input
     *
     * @return string
     */
    public function getInput()
    {
        $this->value = $this->getAttribute('value');

        return '<button id="' . $this->id . '"'
        . ' name="submit_' . $this->for . '"'
        . ' value="' . $this->value . '"'
        . ' title="' . JText::_('JSEARCH_FILTER_SUBMIT') . '"'
        . ' class="btn" style="margin-top: -10px;">'
        . JText::_('JSEARCH_FILTER_SUBMIT')
        . ' </button>';
    }
}
