<?php
/**
 * @version    2.0.0
 * @package    Com_Manifest2md
 * @author     Emmanuel Lecoester <elecoest@gmail.com>
 * @author     Marc Letouzé <marc.letouze@liubov.net>
 * @license    GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 */
defined('_JEXEC') or die;

?>
<a class="btn" type="button" onclick="document.getElementById('batch-category-id').value=''" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('extension.batch');">
    <?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>