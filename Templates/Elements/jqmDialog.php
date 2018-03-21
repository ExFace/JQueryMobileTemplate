<?php
namespace exface\JQueryMobileTemplate\Templates\Elements;

class jqmDialog extends jqmPanel
{

    function buildHtml()
    {
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        if ($this->isLazyLoading()) {
            return '';
        } else {
            return $this->generateJqmPage();
        }
    }

    function buildJs($jqm_page_id = null)
    {
        return '';
    }
    
    /**
     *
     * @return boolean
     */
    protected function isLazyLoading()
    {
        return $this->getWidget()->getLazyLoading(false);
    }

    public function generateJqmPage()
    {
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        
        $output = <<<HTML
<div data-role="page" id="{$this->getId()}" data-overlay-theme="b" data-dialog="true" data-close-btn="right">
	<div data-role="header" class="ui-alt-icon">
		<h1>{$widget->getCaption()}</h1>
	</div>

	<div data-role="content">
		{$this->buildHtmlForWidgets()}
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