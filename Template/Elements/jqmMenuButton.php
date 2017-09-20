<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\Button;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryButtonTrait;
use exface\Core\Widgets\MenuButton;
use exface\Core\CommonLogic\Constants\Icons;

/**
 * generates jQuery Mobile buttons for ExFace
 *
 * @method MenuButton getWidget()
 * 
 * @author Andrej Kabachnik
 *        
 */
class jqmMenuButton extends jqmAbstractElement
{
    
    use JqueryButtonTrait;

    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::generateHtml()
     */
    function generateHtml()
    {
        $widget = $this->getWidget();
        
        $buttons_html = '';
        foreach ($widget->getButtons() as $b) {
            $buttons_html .= '<li><a href="#" onclick="' . $this->buildJsButtonFunctionName($b) . '(); $(this).parent().parent().parent().popup(\'close\');"><i class="' . $this->buildCssIconClass($b->getIconName()) . '"></i> ' . $b->getCaption() . '</a></li>';
        }
        
        $icon = $widget->getIconName() ? $this->buildCssIconClass($widget->getIconName()) : $this->buildCssIconClass(Icons::CHEVRON_DOWN);
        
        $output = <<<HTML

<a href="#{$this->getId()}" data-rel="popup" class="ui-btn ui-btn-inline"><i class="{$icon}"></i> {$widget->getCaption()}</a>
<div data-role="popup" id="{$this->getId()}">
	<ul data-role="listview" data-inset="true">
		{$buttons_html}
	</ul>
</div>		
HTML;
        
        return $output;
    }

    /**
     * {@inheritdoc}
     *
     * @see \exface\JQueryMobileTemplate\Template\Elements\jqmAbstractElement::generateJs()
     */
    function generateJs($jqm_page_id = null)
    {
        $output = '';
        foreach ($this->getWidget()->getButtons() as $b) {
            if ($js_click_function = $this->getTemplate()->getElement($b)->buildJsClickFunction()) {
                $output .= "
					function " . $this->buildJsButtonFunctionName($b) . "(){
						" . $js_click_function . "
					}
					";
            }
        }
        return $output;
    }

    function buildJsButtonFunctionName(Button $button)
    {
        return $this->getTemplate()->getElement($button)->buildJsClickFunctionName();
    }
}
?>