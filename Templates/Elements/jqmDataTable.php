<?php
namespace exface\JQueryMobileFacade\Facades\Elements;

use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryDataTablesTrait;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryDataTableTrait;
use exface\Core\CommonLogic\Constants\Icons;
use exface\Core\Facades\AbstractAjaxFacade\Elements\JqueryToolbarsTrait;
use exface\Core\Widgets\Button;
use exface\Core\Widgets\MenuButton;
use exface\Core\Widgets\ButtonGroup;

/**
 *
 * @author PATRIOT
 *        
 */
class jqmDataTable extends jqmAbstractElement
{
    
    use JqueryDataTableTrait;
    
    use JqueryDataTablesTrait;
    
    use JqueryToolbarsTrait;

    private $on_load_success = '';

    private $editable = false;

    private $editors = array();
    
    protected function init()
    {
        parent::init();
        // Do not render the search action in the main toolbar. We will add custom
        // buttons via HTML instead.
        $this->getWidget()->getToolbarMain()->setIncludeSearchActions(false);
    }

    public function buildHtml()
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
        
        // Toolbars
        $footer = $this->buildHtmlFooter($this->buildHtmlToolbars());
        $header = $this->buildHtmlHeader();
        
        // output the html code
        // TODO replace "stripe" class by a custom css class
        $output = <<<HTML
<div class="jqmDataTable">
	{$header}
	{$this->buildHtmlTable('stripe')}
	{$footer}
</div>
{$this->buildHtmlContextMenu()}
HTML;
        
        return $output;
    }

    public function buildJs($jqm_page_id = null)
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
                
        $output = <<<JS
var {$this->getId()}_table;

$(document).on('pageshow', '#{$this->getJqmPageId()}', function() {
	
	if ({$this->getId()}_table && $.fn.DataTable.isDataTable( '#{$this->getId()}' )) {
		{$this->getId()}_table.columns.adjust();
		return;
	}	
	
	$('#{$this->getId()}_popup_columns input').click(function(){
		setColumnVisibility(this.name, (this.checked ? true : false) );
	});
	
	{$this->getId()}_table = {$this->buildJsTableInit()}
	
    {$this->buildJsClickListeners()}
    
    {$this->buildJsInitialSelection()}
    
    {$this->buildJsPagination()}
    
    {$this->buildJsQuicksearch()}
    
    {$this->buildJsRowDetails()}
	
	{$this->buildJsRowSelection()}

} );
	
function setColumnVisibility(name, visible){
	{$this->getId()}_table.column(name+':name').visible(visible);
	$('#columnToggle_'+name).attr("checked", visible);
	try {
		$('#columnToggle_'+name).checkboxradio('refresh');
	} catch (ex) {}
}

function {$this->getId()}_drawPagination(){
	var pages = {$this->getId()}_table.page.info();
	if (pages.page == 0) {
		$('#{$this->getId()}_prevPage').addClass('ui-disabled');
	} else {
		$('#{$this->getId()}_prevPage').removeClass('ui-disabled');
	}
	if (pages.page == pages.pages-1 || pages.end == pages.recordsDisplay) {
		$('#{$this->getId()}_nextPage').addClass('ui-disabled');
	} else {
		$('#{$this->getId()}_nextPage').removeClass('ui-disabled');	
	}
	$('#{$this->getId()}_pageInfo').html(pages.page*pages.length+1 + ' - ' + (pages.recordsDisplay < (pages.page+1)*pages.length || pages.end == pages.recordsDisplay ? pages.recordsDisplay : (pages.page+1)*pages.length) + ' / ' + pages.recordsDisplay);
	
}

{$this->getFacade()->getElement($widget->getConfiguratorWidget())->buildJs($jqm_page_id)}
    
{$this->buildJsButtons()}

JS;
        
        return $output;
    }

    public function buildHtmlHeadTags()
    {
        $includes = array();
        // $includes[] = '<link rel="stylesheet" type="text/css" href="exface/vendor/exface/JQueryMobileFacade/Facades/js/DataTables/media/css/jquery.dataTables.min.css">';
        // $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileFacade/Facades/js/DataTables/media/js/jquery.dataTables.min.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileFacade/Facades/js/DataTables.exface.helpers.js"></script>';
        
        return $includes;
    }

    /**
     * Renders javascript event handlers for tapping on rows.
     * A single tap (or click) selects a row, while a longtap opens the
     * context menu for the row if one is defined. The long tap also selects the row.
     */
    protected function buildJsRowSelection()
    {
        $output = '';
        if ($this->getWidget()->getMultiSelect()) {
            $output .= "
					$('#{$this->getId()} tbody').on( 'click', 'tr', function () {
				        $(this).toggleClass('selected');
				    } );
					";
        } else {
            // Select a row on tap. Make sure no other row is selected
            $output .= "
					$('#{$this->getId()} tbody').on( 'click', 'tr', function (event) {
				        if ( $(this).hasClass('selected') ) {
				            $(this).removeClass('selected');
				        }
				        else {
				            {$this->getId()}_table.$('tr.selected').removeClass('selected');
				             $(this).addClass('selected');
				        }
				    } );
					";
        }
        return $output;
    }

    /**
     * Generates a popup context menu with actions available upon selection of a row.
     * The menu contains all buttons, that do
     * something with a specific object (= the corresponding action has input_rows_min = 1) or do not have an action at all.
     * This way, the user really only sees the buttons, that perform an action with the selected object and not those, that
     * do something with all object, create new objects, etc.
     */
    private function buildHtmlContextMenu()
    {
        if (! $this->getWidget()->getContextMenuEnabled())
            return '';
        $buttons_html = '';
        foreach ($this->getWidget()->getButtons() as $b) {
            /* @var $b \exface\Core\Widgets\Button */
            if (! $b->isHidden() && (! $b->getAction() || $b->getAction()->getInputRowsMin() === 1)) {
                $buttons_html .= '<li data-icon="' . $this->buildCssIconClass($b->getIcon()) . '"><a href="#" onclick="' . $this->getFacade()->getElement($b)->buildJsClickFunctionName() . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->getCaption() . '</a></li>';
            }
        }
        
        if ($buttons_html) {
            $output = <<<HTML
<div data-role="popup" id="{$this->getId()}_context_menu" data-theme="b">
	<ul data-role="listview" data-inset="true">
		{$buttons_html}
	</ul>
</div>
HTML;
        }
        return $output;
    }

    protected function buildHtmlHeader()
    {
        $table_caption = $this->getWidget()->getCaption() ? $this->getWidget()->getCaption() : $this->getMetaObject()->getName();
        $widget = $this->getWidget();
        
        $output = <<<HTML
		<form id="{$this->getId()}_quickSearch_form">
		<div class="ui-toolbar ui-bar ui-bar-a">
			<div class="ui-grid-a ui-responsive">
				<div class="ui-block-a">
					<h2 style="line-height: 2.8em;">$table_caption</h2>
				</div>
				<div class="ui-block-b" style="float: right; text-align: right;">
					<div data-role="controlgroup" data-type="horizontal" style="float: right;">
						<a href="#" data-role="button" data-icon="action-search" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon" onclick="{$this->buildJsRefresh(false)} return false;">Search</a>
						<a href="#{$this->getFacade()->getElement($widget->getConfiguratorWidget())->getId()}" data-role="button" data-icon="action-settings" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon">Filters & Sorting</a>
					</div>
					<div style="margin-right: 90px;">
						<input id="{$this->getFacade()->getElement($widget->getQuickSearchWidget())->getId()}" type="text" data-mini="true" placeholder="Quick search" data-clear-btn="true" />
					</div>
				</div>
			</div>
		</div>
		<div class="ui-grid-a ui-responsive" id="{$this->getId()}_filters_container" style="padding: 0 1em;">
		</div>
	</form>
HTML;
        return $output;
    }

    protected function buildHtmlFooter($buttons_html)
    {
        $output = <<<HTML
		<div class="ui-bar ui-toolbar ui-bar-a tableFooter">
		<div style="float:left" class="ui-alt-icon">{$buttons_html}</div>
		<div style="float:right">
			<div data-role="controlgroup" data-type="horizontal" style="float:left;margin-right:10px;">
				<a href="#" id="{$this->getId()}_prevPage" class="ui-btn ui-corner-all ui-btn-icon-notext ui-icon-navigation-arrow-back ui-nodisc-icon ui-alt-icon">&lt;</a>
				<a href="#{$this->getId()}_pagingPopup" id="{$this->getId()}_pageInfo" data-rel="popup" class="ui-btn ui-corner-all"></a>
				<a href="#" id="{$this->getId()}_nextPage" class="ui-btn ui-corner-all ui-btn-icon-notext ui-icon-navigation-arrow-forward ui-nodisc-icon ui-alt-icon">&gt;</a>
			</div>
			<div style="float:right;">
				<a href="#" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-navigation-refresh" onclick="{$this->buildJsRefresh(false)} return false;">Reload</a>
				<a href="#{$this->getFacade()->getElement($this->getWidget()->getConfiguratorWidget())->getId()}" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-action-settings">Tabelleneinstellungen</a>
			</div>
			<div data-role="popup" id="{$this->getId()}_pagingPopup" style="width:300px; padding:10px;">
				<form>
				    <label for="{$this->getId()}_pageSlider">Page:</label>
				    <input type="range" name="{$this->getId()}_pageSlider" id="{$this->getId()}_pageSlider" min="1" max="100" value="1">
				</form>
			</div>
		</div>
	</div>
HTML;
        return $output;
    }

    protected function buildJsPagination()
    {
        $output = <<<JS
	$('#{$this->getId()}_prevPage').on('click', function(){{$this->getId()}_table.page('previous'); {$this->buildJsRefresh()}});
	$('#{$this->getId()}_nextPage').on('click', function(){{$this->getId()}_table.page('next'); {$this->buildJsRefresh()}});
	
	$('#{$this->getId()}_pagingPopup').on('popupafteropen', function(){
		$('#{$this->getId()}_pageSlider').val({$this->getId()}_table.page()+1).attr('max', {$this->getId()}_table.page.info().pages).slider('refresh');
	});
	
	$('#{$this->getId()}_pagingPopup').on('popupafterclose', function(){
		{$this->getId()}_table.page(parseInt($('#{$this->getId()}_pageSlider').val())-1).draw(false);
	});
JS;
        return $output;
    }

    /**
     * Generates JS to disable text selection on the rows of the table.
     * If not done so, every time you longtap a row, something gets selected along
     * with the context menu being displayed. It look awful.
     *
     * @return string
     */
    private function buildJsDisableTextSelection()
    {
        return "$('#{$this->getId()} tbody tr td').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);";
    }

    public function isEditable()
    {
        return $this->editable;
    }

    public function setEditable($value)
    {
        $this->editable = $value;
    }

    public function getEditors()
    {
        return $this->editors;
    }
    
    public function getRowDetailsExpandIcon()
    {
        return 'ui-icon-content-add-circle-outline';
    }
    
    public function getRowDetailsCollapseIcon()
    {
        return 'ui-icon-content-remove-circle-outline';
    }
    
    public function buildJsFilterIndicatorUpdater()
    {
        // TODO
    }
    
    /**
     *
     * @param Button[] $buttons
     * @return string
     */
    protected function buildJsContextMenu()
    {
        $output = '';
        // Listen for a long tap to open the context menu. Also trigger a click event, but enforce row selection.
        if ($this->getWidget()->getContextMenuEnabled()) {
            $output .= "
				$('#{$this->getId()} tbody').on( 'taphold', 'tr', function (event) {
					$(this).trigger('click');
					$(this).addClass('selected');
					$('#{$this->getId()}_context_menu').popup('open', {x: exfTapCoordinates.X, y: exfTapCoordinates.Y});
				});";
        }
        
        return $output;
    }
}
?>