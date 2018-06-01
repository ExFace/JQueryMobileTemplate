<?php
namespace exface\JQueryMobileTemplate\Templates\Elements;

use exface\Core\Widgets\Button;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryButtonTrait;

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
     * @see \exface\Templates\jeasyui\Widgets\abstractWidget::buildHtml()
     */
    function buildHtml()
    {
        $buttons_html = '';
        foreach ($this->getWidget()->getButtons() as $b) {
            $buttons_html .= '<li data-icon="' . $this->buildCssIconClass($b->getIcon()) . '"><a href="#" onclick="' . $this->buildJsButtonFunctionName($b) . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->getCaption() . '</a></li>';
        }
        $icon_classes = ($this->getWidget()->getIcon() ? ' ui-icon-' . $this->buildCssIconClass($this->getWidget()->getIcon()) : '') . ($this->getWidget()->getCaption() ? '' : ' ui-btn-icon-notext');
        
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
     * @see \exface\JQueryMobileTemplate\Templates\Elements\jqmAbstractElement::buildJs()
     */
    public function buildJs($jqm_page_id = null)
    {
        $output = '';
        foreach ($this->getWidget()->getButtons() as $b) {
            $output .= "\n" . $this->getTemplate()->getElement($b)->buildJs($jqm_page_id);
        }
        return $output;
    }

    function buildJsButtonFunctionName(Button $button)
    {
        return $this->getTemplate()->getElement($button)->buildJsClickFunctionName();
    }
}
?>