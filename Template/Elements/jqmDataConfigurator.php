<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\DataConfigurator;
use exface\Core\CommonLogic\Constants\Icons;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryDataConfiguratorTrait;

/**
 * 
 * @method DataConfigurator getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class jqmDataConfigurator extends jqmTabs
{
    use JqueryDataConfiguratorTrait;
    
    public function generateHtml(){
        return '';
    }
    
    public function generateJs($jqm_page_id = null)
    {
        $widget = $this->getWidget()->getWidgetConfigured();
        
        $jqm_page_id = ! is_null($jqm_page_id) ? $jqm_page_id : $this->getId();
        
        foreach ($widget->getColumns() as $col) {
            if (! $col->isHidden()) {
                $column_triggers .= '<label for="' . $widget->getId() . '_cToggle_' . $col->getDataColumnName() . '">' . $col->getCaption() . '</label><input type="checkbox" name="' . $col->getDataColumnName() . '" id="' . $widget->getId() . '_cToggle_' . $col->getDataColumnName() . '" checked="true">';
            }
        }
        
        // Filters defined in the UXON description
        if ($widget->hasFilters()) {
            foreach ($widget->getFilters() as $fnr => $fltr) {
                // Skip promoted filters, as they are displayed next to quick search
                if ($fltr->getVisibility() == EXF_WIDGET_VISIBILITY_PROMOTED)
                    continue;
                    $filters_html .= $this->getTemplate()->generateHtml($fltr);
            }
        }
        $filters_html = trim(preg_replace('/\s+/', ' ', $filters_html));
        
        // TODO replace this custom popup with a regular tabs widget
        $output = <<<JS
$('body').append('\
<div data-role="page" id="{$this->getId()}" data-dialog="true" data-close-btn="right">\
	<div data-role="header">\
		<h1><i class="fa fa-filter"></i> Tabelleneinstellungen</h1>\
    	<ul data-role="nd2tabs" class="nd2Tabs">\
    		<li data-tab="{$this->getId()}_popup_filters" data-tab-active="true">Filter</li>\
    		<li data-tab="{$this->getId()}_popup_columns">Spalten</li>\
    		<li data-tab="{$this->getId()}_popup_sorting">Sortierung</li>\
    	</ul>\
	</div>\
\
	<div class="tabs" data-role="content">\
			<div data-role="nd2tab" data-tab="{$this->getId()}_popup_filters">\
				{$filters_html}\
			</div>\
			<div data-role="nd2tab" data-tab="{$this->getId()}_popup_columns">\
				<fieldset data-role="controlgroup">\
					{$column_triggers}\
				</fieldset>\
			</div>\
			<div data-role="nd2tab" data-tab="{$this->getId()}_popup_sorting">\
				\
			</div>\
\
			<div style="text-align:right;" class="ui-alt-icon">\
				<a href="#" data-rel="back" class="ui-btn ui-btn-inline"><i class="{$this->buildCssIconClass(Icons::TIMES)}"></i> {$this->getWorkbench()->getCoreApp()->getTranslator()->translate('ACTION.SHOWDIALOG.CANCEL_BUTTON')}</a>\
                <a href="#" data-rel="back" class="ui-btn ui-btn-inline" onclick="{$this->getTemplate()->getElement($widget)->buildJsRefresh(false)}"><i class="{$this->buildCssIconClass(Icons::SEARCH)}"></i> {$this->getWorkbench()->getCoreApp()->getTranslator()->translate('ACTION.READDATA.SEARCH')}</a>\
			</div>\
\
	</div><!-- /content -->\
</div><!-- page-->\
');
JS;
		return $output . parent::generateJs($jqm_page_id);
    }
}
?>