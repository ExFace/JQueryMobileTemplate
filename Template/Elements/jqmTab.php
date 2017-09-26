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
class jqmTab extends jqmPanel
{    
    public function generateHtml()
    {
        return $this->buildHtmlBody();
    }
    
    public function buildHtmlHeader()
    {
        $widget = $this->getWidget();
        // der erste Tab ist aktiv
        $active = $widget === $widget->getParent()->getChildren()[0] ? 'data-tab-active="true"' : '';
        $disabled_class = $widget->isDisabled() ? 'disabled' : '';
        $icon = $widget->getIconName() ? '<i class="' . $this->buildCssIconClass($widget->getIconName()) . '"></i>' : '';
        
        $output = <<<HTML
        
            <li data-tab="{$this->getId()}" {$active} {$disabled_class}>
                {$icon} {$this->getWidget()->getCaption()}
            </li>
HTML;
        return $output;
    }
    
    public function buildHtmlBody()
    {
        
        $output = <<<HTML
        
    <div data-role="nd2tab" data-tab="{$this->getId()}">
            <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%;">
                {$this->buildHtmlForChildren()}
            </div>
    </div>
HTML;
                
                return $output;
    }
}
?>