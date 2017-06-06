<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\Button;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;

/**
 * generates jQuery Mobile buttons for ExFace
 *
 * @author Andrej Kabachnik
 *        
 */
class jqmMenuButton extends jqmAbstractElement
{
    
    use JqueryButtonTrait;

    /**
     *
     * @see \exface\Templates\jeasyui\Widgets\abstractWidget::generateHtml()
     */
    function generateHtml()
    {
        $buttons_html = '';
        foreach ($this->getWidget()->getButtons() as $b) {
            $buttons_html .= '<li data-icon="' . $this->buildCssIconClass($b->getIconName()) . '"><a href="#" onclick="' . $this->buildJsButtonFunctionName($b) . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->getCaption() . '</a></li>';
        }
        $icon_classes = ($this->getWidget()->getIconName() ? ' ui-icon-' . $this->buildCssIconClass($this->getWidget()->getIconName()) : '') . ($this->getWidget()->getCaption() ? '' : ' ui-btn-icon-notext');
        
        $output = <<<HTML

<a href="#{$this->getId()}" data-rel="popup" class="ui-btn ui-btn-inline ui-corner-all ui-alt-icon {$icon_classes}">{$this->getWidget()->getCaption()}</a>
<div data-role="popup" id="{$this->getId()}" data-theme="b">
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