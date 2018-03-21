<?php
namespace exface\JQueryMobileTemplate\Templates\Elements;

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
    public function buildHtml(){
        $html = '';
        
        foreach ($this->getWidget()->getTabs() as $tab){
            $html .= $this->getTemplate()->getElement($tab)->buildHtml();
        }
        
        return $html;
    }
}
?>