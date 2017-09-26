<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryDataTablesTrait;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryDataTableTrait;
use exface\Core\CommonLogic\Constants\Icons;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryToolbarsTrait;
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

    public function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
        
        // Toolbars
        $footer = $this->buildHtmlFooter($this->buildHtmlToolbars());
        $header = $this->buildHtmlHeader();
        
        // output the html code
        // TODO replace "stripe" class by a custom css class
        $output = <<<HTML
<div class="nd2-card">
    {$header}
    <div class="jqmDataTable">
    	{$this->buildHtmlTable('mdl-data-table stripe')}
    </div>
    {$footer}
</div>
{$this->buildHtmlContextMenu()}
HTML;
        
        return $output;
    }

    public function generateJs($jqm_page_id = null)
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
        
        if (is_null($jqm_page_id)){
            $jqm_page_id = $this->getJqmPageId();
        }
        
        $output = <<<JS
var {$this->getId()}_table;

$(document).on('pageshow', '#{$jqm_page_id}', function() {
	
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
    
    {$this->buildJsTapHandler()}

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

{$this->getTemplate()->getElement($widget->getConfiguratorWidget())->generateJs($jqm_page_id)}
    
{$this->buildJsButtons()}

JS;
        
        return $output;
    }

    public function generateHeaders()
    {
        $includes = array();
        // $includes[] = '<link rel="stylesheet" type="text/css" href="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables/media/css/jquery.dataTables.min.css">';
        // $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables/media/js/jquery.dataTables.min.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables.exface.helpers.js"></script>';
        
        return $includes;
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
                $buttons_html .= '<li ><a href="#" onclick="' . $this->getTemplate()->getElement($b)->buildJsClickFunctionName() . '(); $(this).parent().parent().parent().popup(\'close\');"><i class="' . $this->buildCssIconClass($b->getIconName()) . '"></i> ' . $b->getCaption() . '</a></li>';
            }
        }
        
        if ($buttons_html) {
            $output = <<<HTML
<div data-role="popup" id="{$this->getId()}_context_menu" class="exf-context-menu">
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
        
        $output = <<<HTML
	      <div class="ui-grid-a ui-responsive card-title has-supporting-text">
    			<div class="ui-block-a">
    				<h3 class="card-primary-title">$table_caption</h3>
    			</div>
    			<div class="ui-block-b exf-quick-search">
                    <form id="{$this->getId()}_quickSearch_form">
                        <div class="ui-grid-a pull-right" style="width: 100%">
                            <div class="ui-block-a" style="width: calc(100% - 108px); margin-right: 10px;">
        				        <input id="{$this->getId()}_quickSearch" type="text" placeholder="{$this->getQuickSearchPlaceholder()}" data-clear-btn="true" />
                            </div>
                            <div class="ui-block-b" style="width: 98px;">
                                <a href="#" data-role="button" class="ui-btn ui-btn-inline ui-btn-mini" onclick="{$this->buildJsRefresh(false)} return false;"><i class="fa fa-search"></i></a>
                                <a href="#{$this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->getId()}" data-role="button" class="ui-btn ui-btn-inline"><i class="fa fa-filter"></i></a>
                            </div>
        				</div>
                        </form>
    			</div>
		</div>
HTML;
        return $output;
    }

    protected function buildHtmlFooter($buttons_html)
    {
        $output = <<<HTML

<div class="card-action">
    <div class="box">
		<div class="pull-right text-right exf-toolbar exf-paginator" style="min-width: 350px;">
			<a href="#{$this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->getId()}" class="ui-btn ui-btn-inline" title="{$this->translate('WIDGET.DATATABLE.SETTINGS_DIALOG.TITLE')}"><i class="fa fa-filter"></i></a>
			<a href="#" class="ui-btn ui-btn-inline" onclick="{$this->buildJsRefresh(false)} return false;" title="{$this->translate('WIDGET.REFRESH')}"><i class="fa fa-refresh"></i></a>
			<a href="#{$this->getId()}_pagingPopup" id="{$this->getId()}_pageInfo" data-rel="popup" class="ui-btn ui-btn-inline"></a>
		    <div data-role="popup" id="{$this->getId()}_pagingPopup" style="width:300px; padding:10px;">
				<form>
				    <label for="{$this->getId()}_pageSlider">Page:</label>
				    <input type="range" name="{$this->getId()}_pageSlider" id="{$this->getId()}_pageSlider" min="1" max="100" value="1">
				</form>
			</div>
			<a href="#" id="{$this->getId()}_prevPage" class="ui-btn ui-btn-inline"><i class="fa fa-chevron-left"></i></a>
			<a href="#" id="{$this->getId()}_nextPage" class="ui-btn ui-btn-inline"><i class="fa fa-chevron-right"></i></a>
		</div>
        <div>{$buttons_html}</div>
	</div>
</div>
HTML;
        return $output;
    }
    
    
    
    /**
     * Renders javascript event handlers for tapping on rows.
     * A single tap (or click) selects a row, while a longtap opens the
     * context menu for the row if one is defined. The long tap also selects the row.
     */
    protected function buildJsTapHandler()
    {
        $output = '';
            // Select a row on tap. Make sure no other row is selected
            $output .= "
                $('#{$this->getId()} tbody').on( 'taphold', 'tr', function(e){
                    {$this->getId()}_table.row($(e.target).closest('tr')).select();
                } );
             ";
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