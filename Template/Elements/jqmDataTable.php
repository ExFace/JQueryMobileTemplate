<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryDataTablesTrait;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryDataTableTrait;

/**
 *
 * @author PATRIOT
 *        
 */
class jqmDataTable extends jqmAbstractElement
{
    
    use JqueryDataTableTrait;
    use JqueryDataTablesTrait;

    private $on_load_success = '';

    private $editable = false;

    private $editors = array();

    protected function init()
    {
        parent::init();
        $this->setRowDetailsCollapseIcon('ui-icon-content-remove-circle-outline');
        $this->setRowDetailsExpandIcon('ui-icon-content-add-circle-outline');
    }

    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
        $thead = '';
        $tfoot = '';
        
        // Column headers
        /* @var $col \exface\Core\Widgets\DataColumn */
        foreach ($widget->getColumns() as $col) {
            $thead .= '<th>' . $col->getCaption() . '</th>';
            $tfoot .= '<th class="text-right"></th>';
        }
        
        if ($widget->hasRowDetails()) {
            $thead = '<th></th>' . $thead;
            if ($tfoot) {
                $tfoot = '<th></th>' . $tfoot;
            }
        }
        
        // Add promoted filters above the panel. Other filters will be displayed in a popup via JS
        if ($widget->hasFilters()) {
            foreach ($widget->getFilters() as $fltr) {
                if ($fltr->getVisibility() !== EXF_WIDGET_VISIBILITY_PROMOTED)
                    continue;
                $filters_html .= $this->getTemplate()->generateHtml($fltr);
            }
        }
        
        // add buttons
        /* @var $more_buttons_menu \exface\Core\Widgets\MenuButton */
        $more_buttons_menu = null;
        if ($widget->hasButtons()) {
            foreach ($widget->getButtons() as $button) {
                // Make pomoted and regular buttons visible right in the bottom toolbar
                // Hidden buttons also go here, because it does not make sense to put them into the menu
                if ($button->getVisibility() !== EXF_WIDGET_VISIBILITY_OPTIONAL || $button->isHidden()) {
                    $button_html .= $this->getTemplate()->generateHtml($button);
                }
                // Put all visible buttons into "more actions" menu
                // TODO do not create the more actions menu if all buttons are promoted!
                if (! $button->isHidden()) {
                    if (! $more_buttons_menu) {
                        $more_buttons_menu = $widget->getPage()->createWidget('MenuButton', $widget);
                        $more_buttons_menu->setIconName('more');
                        $more_buttons_menu->setCaption('');
                    }
                    $more_buttons_menu->addButton($button);
                }
            }
        }
        if ($more_buttons_menu) {
            $button_html .= $this->getTemplate()->getElement($more_buttons_menu)->generateHtml();
        }
        
        $bottom_toolbar = $this->buildHtmlBottomToolbar($button_html);
        $top_toolbar = $widget->getHideHeader() ? '' : $this->buildHtmlTopToolbar();
        
        // output the html code
        // TODO replace "stripe" class by a custom css class
        $output = <<<HTML
<div class="jqmDataTable">
	{$top_toolbar}
	<table id="{$this->getId()}" class="stripe" cellspacing="0" width="100%">
		<thead>
			{$thead}
		</thead>
		<tfoot>
			{$tfoot}
		</tfoot>
	</table>
	{$bottom_toolbar}
</div>
{$this->buildHtmlContextMenu()}
HTML;
        
        return $output;
    }

    function generateJs($jqm_page_id = null)
    {
        /* @var $widget \exface\Core\Widgets\DataTable */
        $widget = $this->getWidget();
        $columns = array();
        $column_triggers = '';
        $column_number_offset = 0;
        $filters_html = '';
        $filters_js = '';
        $filters_ajax = "d.q = $('#" . $this->getId() . "_quickSearch').val();\n";
        $buttons_js = '';
        $default_sorters = '';
        
        // row details
        if ($widget->hasRowDetails()) {
            $columns[] = '
					{
						"class":          "details-control",
						"orderable":      false,
						"data":           null,
						"defaultContent": \'<a class="ui-collapsible-heading ui-collapsible-heading-collapsed ' . $this->row_details_expand_icon . ' ui-btn-icon-notext" href="javascript:;"></a>\'
					}
					';
            $column_number_offset ++;
        }
        
        foreach ($widget->getSorters() as $sorter) {
            $column_exists = false;
            foreach ($widget->getColumns() as $nr => $col) {
                if ($col->getAttributeAlias() == $sorter->attribute_alias) {
                    $column_exists = true;
                    $default_sorters .= '[ ' . $nr . ', "' . $sorter->direction . '" ], ';
                }
            }
            if (! $column_exists) {
                // TODO add a hidden column
            }
        }
        // Remove tailing comma
        if ($default_sorters)
            $default_sorters = substr($default_sorters, 0, - 2);
        
        // columns
        foreach ($widget->getColumns() as $nr => $col) {
            $columns[] = $this->buildJsColumnDef($col);
            $nr = $nr + $column_number_offset;
            if ($col->getFooter()) {
                $footer_callback .= <<<JS
	            // Total over all pages
	            if (api.ajax.json().footer[0]['{$col->getDataColumnName()}']){
		            total = api.ajax.json().footer[0]['{$col->getDataColumnName()}'];
		            // Update footer
		            $( api.column( {$nr} ).footer() ).html( total );
	           	}
JS;
            }
            if (! $col->isHidden()) {
                $column_triggers .= '<label for="' . $widget->getId() . '_cToggle_' . $col->getDataColumnName() . '">' . $col->getCaption() . '</label><input type="checkbox" name="' . $col->getDataColumnName() . '" id="' . $widget->getId() . '_cToggle_' . $col->getDataColumnName() . '" checked="true">';
            }
        }
        $columns = implode(', ', $columns);
        
        if ($footer_callback) {
            $footer_callback = '
				, "footerCallback": function ( row, data, start, end, display ) {
					var api = this.api(), data;
		
		            // Remove the formatting to get integer data for summation
		            var intVal = function ( i ) {
		                return typeof i === \'string\' ?
		                    i.replace(/[\$,]/g, \'\')*1 :
		                    typeof i === \'number\' ?
		                        i : 0;
		            };
					' . $footer_callback . '
				}';
        }
        
        // Filters defined in the UXON description
        if ($widget->hasFilters()) {
            foreach ($widget->getFilters() as $fnr => $fltr) {
                // Skip promoted filters, as they are displayed next to quick search
                if ($fltr->getVisibility() == EXF_WIDGET_VISIBILITY_PROMOTED)
                    continue;
                $fltr_element = $this->getTemplate()->getElement($fltr);
                $filters_html .= $this->getTemplate()->generateHtml($fltr);
                $filters_js .= $this->getTemplate()->generateJs($fltr, $this->getId() . '_popup_config');
                $filters_ajax .= 'd.fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . $fltr->getAttributeAlias() . ' = ' . $fltr_element->buildJsValueGetter() . ";\n";
                
                // Here we generate some JS make the filter visible by default, once it gets used.
                // This code will be called when the table's config page gets closed.
                if (! $fltr->isHidden()) {
                    $filters_js_promoted .= "
							if (" . $fltr_element->buildJsValueGetter() . " && $('#" . $fltr_element->getId() . "').parents('#{$this->getId()}_popup_config').length > 0){
								var fltr = $('#" . $fltr_element->getId() . "').parents('.exf_input');
								var ui_block = $('<div></div>');
								if ($('#{$this->getId()}_filters_container').children('div').length % 2 == 0){
									ui_block.addClass('ui-block-a');
								} else {
									ui_block.addClass('ui-block-b');
								}
								ui_block.appendTo('#{$this->getId()}_filters_container');
								fltr.detach().appendTo(ui_block);
								fltr.addClass('ui-field-contain');
							}
					";
                    /*
                     * $filters_js_promoted .= "
                     * if (" . $fltr_element->buildJsValueGetter() . "){
                     * var fltr = $('#" . $fltr_element->getId() . "').parents('.exf_input');
                     * var ui_block = $('<div></div>');
                     * if ($('#{$this->getId()}_filters_container').children('div').length % 2 == 0){
                     * ui_block.addClass('ui-block-a');
                     * } else {
                     * ui_block.addClass('ui-block-b');
                     * }
                     * ui_block.appendTo('#{$this->getId()}_filters_container');
                     * fltr.detach().appendTo(ui_block);
                     * fltr.addClass('ui-field-contain');
                     * }
                     * ";
                     */
                }
            }
        }
        $filters_html = trim(preg_replace('/\s+/', ' ', $filters_html));
        
        // buttons
        if ($widget->hasButtons()) {
            foreach ($widget->getButtons() as $button) {
                $buttons_js .= $this->getTemplate()->generateJs($button);
            }
        }
        
        // configure pagination
        if ($widget->getPaginate()) {
            $paging_options = '"pageLength": ' . (!is_null($widget->getPaginatePageSize()) ? $widget->getPaginatePageSize() : $this->getTemplate()->getConfig()->getOption('WIDGET.DATATABLE.DEFAULT_PAGE_SIZE')). ',';
        } else {
            $paging_options = '"paging": false,';
        }
        
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
	
	{$this->getId()}_table = $('#{$this->getId()}').DataTable( {
		"dom": 't',
		"deferRender": true,
		"processing": true,
		"serverSide": true,
		{$paging_options}
		"scrollX": true,
		"scrollXollapse": true,
		"ajax": {
			"url": "{$this->getAjaxUrl()}",
			"type": "POST",
			"data": function ( d ) {
				d.action = '{$widget->getLazyLoadingAction()}';
				d.resource = "{$this->getPageId()}";
				d.element = "{$widget->getId()}";
				d.object = "{$this->getWidget()->getMetaObject()->getId()}";
				{$filters_ajax}
			}
		},
		"columns": [{$columns}],
		"order": [{$default_sorters}],
		"drawCallback": function(settings, json) {
			{$this->getId()}_drawPagination();
			{$this->getId()}_table.columns.adjust();
			{$this->buildJsDisableTextSelection()}
		}
		{$footer_callback}
	} );
	
	{$this->buildJsPagination()}
	
	{$this->buildJsQuicksearch()}
	
	{$this->buildJsRowSelection()}
	
	{$this->buildJsRowDetails()}

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

function {$this->getId()}_refreshPromotedFilters(){
	{$filters_js_promoted}
}

$(document).on('pagebeforeshow', '#{$this->getId()}_popup_config', function(event, ui) {
	$('#{$this->getId()}_popup_config *[data-role="navbar"] a').on('click',function(event){
		$(this).parent().siblings().each(function(){
			$( $(this).children('a').attr('href') ).hide();
			$(this).children('a').removeClass('ui-btn-active');
		});
		$( $(this).attr('href') ).show();
		$(this).addClass('ui-btn-active');
		event.preventDefault();
		return false;
	});
	
	var activeTab = $('#{$this->getId()}_popup_config *[data-role="navbar"] a.ui-btn-active');
	if (activeTab){
		activeTab.trigger('click');	
	} else {
		
	}
});

$(document).on('pagehide', '#{$this->getId()}_popup_config', function(event, ui) {
	{$this->getId()}_refreshPromotedFilters();
});


{$filters_js}

{$buttons_js}
			
			
$('body').append('\
<div data-role="page" id="{$this->getId()}_popup_config" data-dialog="true" data-close-btn="right">\
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
				<a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->buildCssIconClass('cancel')} ui-btn-icon-left ui-btn-inline ui-corner-all">Abbrechen</a><a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->buildCssIconClass('save')} ui-btn-icon-left ui-btn-inline ui-corner-all" onclick="{$this->getId()}_table.draw();">OK</a>\
			</div>\
\
	</div><!-- /content -->\
</div><!-- page-->\
');
JS;
        
        return $output;
    }

    public function buildJsColumnDef(\exface\Core\Widgets\DataColumn $col)
    {
        $editor = $this->editors[$col->getId()];
        
        $output = '{
							name: "' . $col->getDataColumnName() . '"' . ($col->getAttributeAlias() ? ', data: "' . $col->getDataColumnName() . '"' : '') . 
        // . ($col->get_colspan() ? ', colspan: "' . intval($col->get_colspan()) . '"' : '')
        // . ($col->get_rowspan() ? ', rowspan: "' . intval($col->get_rowspan()) . '"' : '')
        ($col->isHidden() ? ', visible: false' : '') . 
        // . ($editor ? ', editor: {type: "' . $editor->getElementType() . '"' . ($editor->buildJsInitOptions() ? ', options: {' . $editor->buildJsInitOptions() . '}' : '') . '}' : '')
        ', className: "' . $this->getCssColumnClass($col) . '"' . ', orderable: ' . ($col->getSortable() ? 'true' : 'false') . '}';
        
        return $output;
    }

    /**
     * Returns a list of CSS classes to be used for the specified column: e.g.
     * alignment, etc.
     *
     * @param \exface\Core\Widgets\DataColumn $col            
     * @return string
     */
    public function getCssColumnClass(\exface\Core\Widgets\DataColumn $col)
    {
        $classes = '';
        switch ($col->getAlign()) {
            case EXF_ALIGN_LEFT:
                $classes .= 'dt-body-left';
            case EXF_ALIGN_CENTER:
                $classes .= 'dt-body-center';
            case EXF_ALIGN_RIGHT:
                $classes .= 'dt-body-right';
        }
        return $classes;
    }

    public function buildJsEditModeEnabler()
    {
        return '
					var rows = $(this).' . $this->getElementType() . '("getRows");
					for (var i=0; i<rows.length; i++){
						$(this).' . $this->getElementType() . '("beginEdit", i);
					}
				';
    }

    public function addOnLoadSuccess($script)
    {
        $this->on_load_success .= $script;
    }

    public function getOnLoadSuccess()
    {
        return $this->on_load_success;
    }

    public function buildJsValueGetter($column = null, $row = null)
    {
        $output = $this->getId() . "_table";
        if (is_null($row)) {
            $output .= ".rows('.selected').data()";
        } else {
            // TODO
        }
        if (is_null($column)) {
            $column = $this->getWidget()->getMetaObject()->getUidAlias();
        } else {
            // TODO
        }
        return $output . "['" . $column . "']";
    }

    public function buildJsDataGetter(ActionInterface $action = null)
    {
        if (is_null($action)) {
            $rows = $this->getId() . "_table.rows().data()";
        } elseif ($this->isEditable() && $action->implementsInterface('iModifyData')) {
            // TODO
        } else {
            $rows = "Array.prototype.slice.call(" . $this->getId() . "_table.rows('.selected').data())";
        }
        return "{oId: '" . $this->getWidget()->getMetaObjectId() . "', rows: " . $rows . "}";
    }

    public function buildJsRefresh()
    {
        return $this->getId() . "_table.draw(false);";
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
            // Listen for a long tap to open the context menu. Also trigger a click event, but enforce row selection.
            if ($this->getWidget()->getContextMenuEnabled()) {
                $output .= "
				$('#{$this->getId()} tbody').on( 'taphold', 'tr', function (event) {
					$(this).trigger('click');
					$(this).addClass('selected');
					$('#{$this->getId()}_context_menu').popup('open', {x: exfTapCoordinates.X, y: exfTapCoordinates.Y});
				});";
            }
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
                $buttons_html .= '<li data-icon="' . $this->buildCssIconClass($b->getIconName()) . '"><a href="#" onclick="' . $this->getTemplate()->getElement($b)->buildJsClickFunctionName() . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->getCaption() . '</a></li>';
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

    protected function buildHtmlTopToolbar()
    {
        $table_caption = $this->getWidget()->getCaption() ? $this->getWidget()->getCaption() : $this->getMetaObject()->getName();
        
        $output = <<<HTML
		<form id="{$this->getId()}_quickSearch_form">
		<div class="ui-toolbar ui-bar ui-bar-a">
			<div class="ui-grid-a ui-responsive">
				<div class="ui-block-a">
					<h2 style="line-height: 2.8em;">$table_caption</h2>
				</div>
				<div class="ui-block-b" style="float: right; text-align: right;">
					<div data-role="controlgroup" data-type="horizontal" style="float: right;">
						<a href="#" data-role="button" data-icon="action-search" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon" onclick="{$this->buildJsRefresh()} return false;">Search</a>
						<a href="#{$this->getId()}_popup_config" data-role="button" data-icon="action-settings" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon">Filters & Sorting</a>
					</div>
					<div style="margin-right: 90px;">
						<input id="{$this->getId()}_quickSearch" type="text" data-mini="true" placeholder="Quick search" data-clear-btn="true" />
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

    protected function buildHtmlBottomToolbar($buttons_html)
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
				<a href="#" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-navigation-refresh" onclick="{$this->buildJsRefresh()} return false;">Reload</a>
				<a href="#{$this->getId()}_popup_config" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-action-settings">Tabelleneinstellungen</a>
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

    protected function buildJsQuicksearch()
    {
        $output = <<<JS
	$('#{$this->getId()}_quickSearch_form').on('submit', function(event) {
		{$this->getId()}_table.draw();	
		event.preventDefault();
		return false;
	});
				
	$('#{$this->getId()}_quickSearch').on('change', function(event) {
		{$this->getId()}_table.draw();	
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
}
?>