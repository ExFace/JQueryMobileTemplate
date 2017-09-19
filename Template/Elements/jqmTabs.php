<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\Tabs;

/**
 * 
 * @method Tabs getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class jqmTabs extends jqmContainer
{
    public function generateHtml(){
        $html = '';
        
        foreach ($this->getWidget()->getTabs() as $tab){
            $html .= $this->getTemplate()->getElement($tab)->generateHtml();
        }
        
        return $html;
    }
}
?>