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
        
        
        $output = <<<JS
$('body').append('\
<div data-role="page" id="{$this->getId()}" data-dialog="true" data-close-btn="right">\
	<div data-role="header" class="ui-alt-icon">\
		<h1>Tabelleneinstellungen</h1>\
	</div>\
\
	<div data-role="content">\
			<div data-role="navbar" class="ui-dialog-header">\
				<ul>\
					<li><a href="#{$this->getId()}_popup_filters" class="ui-btn-active">Filter</a></li>\
					<li><a href="#{$this->getId()}_popup_columns">Spalten</a></li>\
					<li><a href="#{$this->getId()}_popup_sorting">Sortierung</a></li>\
				</ul>\
			</div>\
			<div id="{$this->getId()}_popup_filters">\
				{$filters_html}\
			</div>\
			<div id="{$this->getId()}_popup_columns">\
				<fieldset data-role="controlgroup">\
					{$column_triggers}\
				</fieldset>\
			</div>\
			<div id="{$this->getId()}_popup_sorting">\
				\
			</div>\
\
			<div style="text-align:right;" class="ui-alt-icon">\
				<a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->buildCssIconClass(Icons::TIMES)} ui-btn-icon-left ui-btn-inline ui-corner-all">Abbrechen</a>\
                <a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->buildCssIconClass(Icons::CHECK)} ui-btn-icon-left ui-btn-inline ui-corner-all" onclick="{$this->getTemplate()->getElement($widget)->buildJsRefresh(false)}">OK</a>\
			</div>\
\
	</div><!-- /content -->\
</div><!-- page-->\
');

$(document).on('pagebeforeshow', '#{$this->getId()}', function(event, ui) {
	$('#{$this->getId()} *[data-role="navbar"] a').on('click',function(event){
		$(this).parent().siblings().each(function(){
			$( $(this).children('a').attr('href') ).hide();
			$(this).children('a').removeClass('ui-btn-active');
		});
		$( $(this).attr('href') ).show();
		$(this).addClass('ui-btn-active');
		event.preventDefault();
		return false;
	});
	
	var activeTab = $('#{$this->getId()} *[data-role="navbar"] a.ui-btn-active');
	if (activeTab){
		activeTab.trigger('click');	
	} else {
		
	}
});
JS;
		return $output . parent::generateJs($jqm_page_id);
    }
}
?>