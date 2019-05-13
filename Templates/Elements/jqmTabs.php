<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

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
            $html .= $this->getFacade()->getElement($tab)->buildHtml();
        }
        
        return $html;
    }
}
?>