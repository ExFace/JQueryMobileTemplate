<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

class jqmDialog extends jqmPanel
{

    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        if ($widget->getLazyLoading()) {
            return '';
        } else {
            return $this->generateJqmPage();
        }
    }

    public function generateJqmPage()
    {
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        
        $content = $this->buildHtmlForWidgets();
        
        if ($widget->countWidgetsVisible() === 1 && $widget->getChildren()[0]->is('Tabs')){
            $tabs_element = $this->getTemplate()->getElement($widget->getChildren()[0]);
            $subtitle = $tabs_element->buildHtmlTabHeaders();
            $content = $tabs_element->buildHtmlTabBodies();
        }
        
        $output = <<<HTML
<div data-role="page" id="{$this->getId()}" data-dialog="true" data-close-btn="right">
	<div data-role="header">
		<h1>{$widget->getCaption()}</h1>
        {$subtitle}
	</div>

	<div data-role="content">
		{$content}
		<div class="dialogButtons ui-alt-icon">
			{$this->buildHtmlButtons()}
		</div>
	</div>
	
	<script type="text/javascript">
		{$this->buildJsForWidgets($this->getId())}
		{$this->buildJsButtons($this->getId())}
	</script>
				
</div>
HTML;
        return $output;
    }
}
?>