<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
/**
 * 
 * @author PATRIOT
 *
 */
class jqmDataTable extends jqmAbstractElement {
	private $on_load_success = '';
	private $row_details_expand_icon = 'ui-icon-content-add-circle-outline';
	private $row_details_collapse_icon = 'ui-icon-content-remove-circle-outline';

	protected $editable = false;
	protected $editors = array();
	
	function generate_html(){
		/* @var $widget \exface\Core\Widgets\DataTable */
		$widget = $this->get_widget();
		$thead = '';
		$tfoot = '';
		
		// Column headers
		/* @var $col \exface\Core\Widgets\DataColumn */
		foreach ($widget->get_columns() as $col) {
			$thead .= '<th>' . $col->get_caption() . '</th>';
			$tfoot .= '<th class="text-right"></th>';
		}
		
		if ($widget->has_row_details()){
			$thead = '<th></th>' . $thead;
			if ($tfoot){
				$tfoot = '<th></th>' . $tfoot;
			}
		}
		
		// Add promoted filters above the panel. Other filters will be displayed in a popup via JS
		if ($widget->has_filters()){
			foreach ($widget->get_filters() as $fltr){
				if ($fltr->get_visibility() !== EXF_WIDGET_VISIBILITY_PROMOTED) continue;
				$filters_html .= $this->get_template()->generate_html($fltr);
			}
		}
		
		// add buttons
		/* @var $more_buttons_menu \exface\Core\Widgets\MenuButton */
		$more_buttons_menu = null;
		if ($widget->has_buttons()){
			foreach ($widget->get_buttons() as $button){
				// Make pomoted and regular buttons visible right in the bottom toolbar
				// Hidden buttons also go here, because it does not make sense to put them into the menu
				if ($button->get_visibility() !== EXF_WIDGET_VISIBILITY_OPTIONAL || $button->is_hidden()){
					$button_html .= $this->get_template()->generate_html($button);
				} 
				// Put all visible buttons into "more actions" menu
				// TODO do not create the more actions menu if all buttons are promoted!
				if (!$button->is_hidden()){
					if (!$more_buttons_menu){
						$more_buttons_menu = $widget->get_page()->create_widget('MenuButton', $widget);
						$more_buttons_menu->set_icon_name('more');
						$more_buttons_menu->set_caption('');
					}
					$more_buttons_menu->add_button($button);
				}
			}
		}
		if ($more_buttons_menu){
			$button_html .= $this->get_template()->get_element($more_buttons_menu)->generate_html();
		}
		
		$bottom_toolbar = $this->build_html_bottom_toolbar($button_html);
		$top_toolbar = $widget->get_hide_toolbar_top() ? '' : $this->build_html_top_toolbar();
		
		// output the html code
		// TODO replace "stripe" class by a custom css class
		$output = <<<HTML
<div class="jqmDataTable">
	{$top_toolbar}
	<table id="{$this->get_id()}" class="stripe" cellspacing="0" width="100%">
		<thead>
			{$thead}
		</thead>
		<tfoot>
			{$tfoot}
		</tfoot>
	</table>
	{$bottom_toolbar}
</div>
{$this->build_html_context_menu()}
HTML;
		
		return $output;
	}
	
	function generate_js($jqm_page_id = null){
		/* @var $widget \exface\Core\Widgets\DataTable */
		$widget = $this->get_widget();
		$columns = array();
		$column_triggers = '';
		$column_number_offset = 0;
		$filters_html = '';
		$filters_js = '';
		$filters_ajax = "d.q = $('#" . $this->get_id() . "_quickSearch').val();\n";
		$buttons_js = '';
		$default_sorters = '';
		
		
		// row details
		if ($widget->has_row_details()){
			$columns[] = '
					{
						"class":          "details-control",
						"orderable":      false,
						"data":           null,
						"defaultContent": \'<a class="ui-collapsible-heading ui-collapsible-heading-collapsed ' . $this->row_details_expand_icon . ' ui-btn-icon-notext" href="javascript:;"></a>\'
					}
					';
			$column_number_offset++;
		}
		
		foreach ($widget->get_sorters() as $sorter){
			$column_exists = false;
			foreach ($widget->get_columns() as $nr => $col){
				if ($col->get_attribute_alias() == $sorter->attribute_alias){
					$column_exists = true;
					$default_sorters .= '[ ' . $nr . ', "' . $sorter->direction . '" ], ';
				}
			}
			if (!$column_exists){
				// TODO add a hidden column
			}
		}
		// Remove tailing comma
		if ($default_sorters) $default_sorters = substr($default_sorters, 0, -2);
		
		// columns
		foreach ($widget->get_columns() as $nr => $col){
			$columns[] = $this->build_js_column_def($col);
			$nr = $nr + $column_number_offset;
			if ($col->get_footer()){
				$footer_callback .= <<<JS
	            // Total over all pages
	            if (api.ajax.json().footer[0]['{$col->get_data_column_name()}']){
		            total = api.ajax.json().footer[0]['{$col->get_data_column_name()}'];
		            // Update footer
		            $( api.column( {$nr} ).footer() ).html( total );
	           	}
JS;
			}
			if (!$col->is_hidden()){
				$column_triggers .= '<label for="' . $widget->get_id() . '_cToggle_' . $col->get_data_column_name() . '">' . $col->get_caption() . '</label><input type="checkbox" name="' . $col->get_data_column_name() . '" id="' . $widget->get_id() . '_cToggle_' . $col->get_data_column_name() . '" checked="true">';
			}
		}
		$columns = implode(', ', $columns);
		
		if ($footer_callback){
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
		if ($widget->has_filters()){
			foreach ($widget->get_filters() as $fnr => $fltr){
				// Skip promoted filters, as they are displayed next to quick search
				if ($fltr->get_visibility() == EXF_WIDGET_VISIBILITY_PROMOTED) continue;
				$fltr_element = $this->get_template()->get_element($fltr);
				$filters_html .= $this->get_template()->generate_html($fltr);
				$filters_js .= $this->get_template()->generate_js($fltr, $this->get_id().'_popup_config');
				$filters_ajax .= 'd.fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . $fltr->get_attribute_alias() . ' = ' . $fltr_element->build_js_value_getter() . ";\n";
				
				// Here we generate some JS make the filter visible by default, once it gets used.
				// This code will be called when the table's config page gets closed.
				if (!$fltr->is_hidden()){
					$filters_js_promoted .= "
							if (" . $fltr_element->build_js_value_getter() . " && $('#" . $fltr_element->get_id() . "').parents('#{$this->get_id()}_popup_config').length > 0){
								var fltr = $('#" . $fltr_element->get_id() . "').parents('.exf_input');
								var ui_block = $('<div></div>');
								if ($('#{$this->get_id()}_filters_container').children('div').length % 2 == 0){
									ui_block.addClass('ui-block-a');
								} else {
									ui_block.addClass('ui-block-b');
								}
								ui_block.appendTo('#{$this->get_id()}_filters_container');
								fltr.detach().appendTo(ui_block);
								fltr.addClass('ui-field-contain');
							}
					";
					/*$filters_js_promoted .= "
							if (" . $fltr_element->build_js_value_getter() . "){
								var fltr = $('#" . $fltr_element->get_id() . "').parents('.exf_input');
								var ui_block = $('<div></div>');
								if ($('#{$this->get_id()}_filters_container').children('div').length % 2 == 0){
									ui_block.addClass('ui-block-a');
								} else {
									ui_block.addClass('ui-block-b');
								}
								ui_block.appendTo('#{$this->get_id()}_filters_container');
								fltr.detach().appendTo(ui_block);
								fltr.addClass('ui-field-contain');
							}
							";*/
				}
			}
		}
		$filters_html = trim(preg_replace('/\s+/', ' ', $filters_html));
		
		// buttons
		if ($widget->has_buttons()){
			foreach ($widget->get_buttons() as $button){
				$buttons_js .= $this->get_template()->generate_js($button);
			}
		}
		
		// configure pagination
		if ($widget->get_paginate()){
			$paging_options = '"pageLength": ' . $widget->get_paginate_default_page_size() . ','; 
		} else {
			$paging_options = '"paging": false,';
		}
		
		$output = <<<JS
var {$this->get_id()}_table;

$(document).on('pageshow', '#{$this->get_jqm_page_id()}', function() {
	
	if ({$this->get_id()}_table && $.fn.DataTable.isDataTable( '#{$this->get_id()}' )) {
		{$this->get_id()}_table.columns.adjust();
		return;
	}	
	
	$('#{$this->get_id()}_popup_columns input').click(function(){
		setColumnVisibility(this.name, (this.checked ? true : false) );
	});
	
	{$this->get_id()}_table = $('#{$this->get_id()}').DataTable( {
		"dom": 't',
		"deferRender": true,
		"processing": true,
		"serverSide": true,
		{$paging_options}
		"scrollX": true,
		"scrollXollapse": true,
		"ajax": {
			"url": "{$this->get_ajax_url()}",
			"type": "POST",
			"data": function ( d ) {
				d.action = '{$widget->get_lazy_loading_action()}';
				d.resource = "{$this->get_page_id()}";
				d.element = "{$widget->get_id()}";
				d.object = "{$this->get_widget()->get_meta_object()->get_id()}";
				{$filters_ajax}
			}
		},
		"columns": [{$columns}],
		"order": [{$default_sorters}],
		"drawCallback": function(settings, json) {
			{$this->get_id()}_drawPagination();
			{$this->get_id()}_table.columns.adjust();
			{$this->build_js_disable_text_selection()}
		}
		{$footer_callback}
	} );
	
	{$this->build_js_pagination()}
	
	{$this->build_js_quicksearch()}
	
	{$this->build_js_row_selection()}
	
	{$this->build_js_row_details()}

} );
	
function setColumnVisibility(name, visible){
	{$this->get_id()}_table.column(name+':name').visible(visible);
	$('#columnToggle_'+name).attr("checked", visible);
	try {
		$('#columnToggle_'+name).checkboxradio('refresh');
	} catch (ex) {}
}

function {$this->get_id()}_drawPagination(){
	var pages = {$this->get_id()}_table.page.info();
	if (pages.page == 0) {
		$('#{$this->get_id()}_prevPage').addClass('ui-disabled');
	} else {
		$('#{$this->get_id()}_prevPage').removeClass('ui-disabled');
	}
	if (pages.page == pages.pages-1 || pages.end == pages.recordsDisplay) {
		$('#{$this->get_id()}_nextPage').addClass('ui-disabled');
	} else {
		$('#{$this->get_id()}_nextPage').removeClass('ui-disabled');	
	}
	$('#{$this->get_id()}_pageInfo').html(pages.page*pages.length+1 + ' - ' + (pages.recordsDisplay < (pages.page+1)*pages.length || pages.end == pages.recordsDisplay ? pages.recordsDisplay : (pages.page+1)*pages.length) + ' / ' + pages.recordsDisplay);
	
}

function {$this->get_id()}_refreshPromotedFilters(){
	{$filters_js_promoted}
}

$(document).on('pagebeforeshow', '#{$this->get_id()}_popup_config', function(event, ui) {
	$('#{$this->get_id()}_popup_config *[data-role="navbar"] a').on('click',function(event){
		$(this).parent().siblings().each(function(){
			$( $(this).children('a').attr('href') ).hide();
			$(this).children('a').removeClass('ui-btn-active');
		});
		$( $(this).attr('href') ).show();
		$(this).addClass('ui-btn-active');
		event.preventDefault();
		return false;
	});
	
	var activeTab = $('#{$this->get_id()}_popup_config *[data-role="navbar"] a.ui-btn-active');
	if (activeTab){
		activeTab.trigger('click');	
	} else {
		
	}
});

$(document).on('pagehide', '#{$this->get_id()}_popup_config', function(event, ui) {
	{$this->get_id()}_refreshPromotedFilters();
});


{$filters_js}

{$buttons_js}
			
			
$('body').append('\
<div data-role="page" id="{$this->get_id()}_popup_config" data-dialog="true" data-close-btn="right">\
	<div data-role="header" class="ui-alt-icon">\
		<h1>Tabelleneinstellungen</h1>\
	</div>\
\
	<div data-role="content">\
			<div data-role="navbar" class="ui-dialog-header">\
				<ul>\
					<li><a href="#{$this->get_id()}_popup_filters" class="ui-btn-active">Filter</a></li>\
					<li><a href="#{$this->get_id()}_popup_columns">Spalten</a></li>\
					<li><a href="#{$this->get_id()}_popup_sorting">Sortierung</a></li>\
				</ul>\
			</div>\
			<div id="{$this->get_id()}_popup_filters">\
				{$filters_html}\
			</div>\
			<div id="{$this->get_id()}_popup_columns">\
				<fieldset data-role="controlgroup">\
					{$column_triggers}\
				</fieldset>\
			</div>\
			<div id="{$this->get_id()}_popup_sorting">\
				\
			</div>\
\
			<div style="text-align:right;" class="ui-alt-icon">\
				<a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->get_icon_class('cancel')} ui-btn-icon-left ui-btn-inline ui-corner-all">Abbrechen</a><a href="#" data-rel="back" data-inline="true" class="ui-btn ui-icon-{$this->get_icon_class('save')} ui-btn-icon-left ui-btn-inline ui-corner-all" onclick="{$this->get_id()}_table.draw();">OK</a>\
			</div>\
\
	</div><!-- /content -->\
</div><!-- page-->\
');
JS;
		
		return $output;
	}
	
	public function build_js_column_def (\exface\Core\Widgets\DataColumn $col){
		$editor = $this->editors[$col->get_id()];
	
		$output = '{
							name: "' . $col->get_data_column_name() . '"'
							. ($col->get_attribute_alias() ? ', data: "' . $col->get_data_column_name() . '"' : '')
							//. ($col->get_colspan() ? ', colspan: "' . intval($col->get_colspan()) . '"' : '')
							//. ($col->get_rowspan() ? ', rowspan: "' . intval($col->get_rowspan()) . '"' : '')
							. ($col->is_hidden() ? ', visible: false' :  '')
							//. ($editor ? ', editor: {type: "' . $editor->get_element_type() . '"' . ($editor->build_js_init_options() ? ', options: {' . $editor->build_js_init_options() . '}' : '') . '}' : '')
							. ', className: "' . $this->get_css_column_class($col) . '"'
							. ', orderable: ' . ($col->get_sortable() ? 'true' : 'false')
							. '}';
	
		return $output;
	}
	
	/**
	 * Returns a list of CSS classes to be used for the specified column: e.g. alignment, etc.
	 * @param \exface\Core\Widgets\DataColumn $col
	 * @return string
	 */
	public function get_css_column_class(\exface\Core\Widgets\DataColumn $col){
		$classes = '';
		switch ($col->get_align()){
			case EXF_ALIGN_LEFT : $classes .= 'dt-body-left';
			case EXF_ALIGN_CENTER : $classes .= 'dt-body-center';
			case EXF_ALIGN_RIGHT : $classes .= 'dt-body-right';
		}
		return $classes;
	}
	
	public function build_js_edit_mode_enabler(){
		return '
					var rows = $(this).' . $this->get_element_type() . '("getRows");
					for (var i=0; i<rows.length; i++){
						$(this).' . $this->get_element_type() . '("beginEdit", i);
					}
				';
	}
	
	public function add_on_load_success($script){
		$this->on_load_success .= $script;
	}
	
	public function get_on_load_success(){
		return $this->on_load_success;
	}
	
	public function build_js_value_getter($row=null, $column=null){
		$output = $this->get_id()."_table";
		if (is_null($row)){
			$output .= ".rows('.selected').data()";
		} else {
			// TODO
		}
		if (is_null($column)){
			$column = $this->get_widget()->get_meta_object()->get_uid_alias();
		} else {
			// TODO
		}
		return $output . "['" . $column . "']";
	}
	
	public function build_js_data_getter(){
		if ($this->is_editable()){
			// TODO
		} else {
			return $this->get_id() . "_table.rows('.selected').data()";
		}
	}
	
	public function build_js_refresh(){
		return $this->get_id() . "_table.draw(false);";
	}
	
	public function generate_headers(){
		$includes = array();
		//$includes[] = '<link rel="stylesheet" type="text/css" href="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables/media/css/jquery.dataTables.min.css">';
		//$includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables/media/js/jquery.dataTables.min.js"></script>';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/DataTables.exface.helpers.js"></script>';
		
		return $includes;
	}
	
	/**
	 * Renders javascript event handlers for tapping on rows. A single tap (or click) selects a row, while a longtap opens the
	 * context menu for the row if one is defined. The long tap also selects the row.
	 */
	protected function build_js_row_selection(){
		$output = '';		
		if ($this->get_widget()->get_multi_select()){
			$output .= "
					$('#{$this->get_id()} tbody').on( 'click', 'tr', function () {
				        $(this).toggleClass('selected');
				    } );
					";
		} else {
			// Listen for a long tap to open the context menu. Also trigger a click event, but enforce row selection.
			if ($this->get_widget()->get_context_menu_enabled()){
				$output .= "
				$('#{$this->get_id()} tbody').on( 'taphold', 'tr', function (event) {
					$(this).trigger('click');
					$(this).addClass('selected');
					$('#{$this->get_id()}_context_menu').popup('open', {x: exfTapCoordinates.X, y: exfTapCoordinates.Y});
				});";
			}
			// Select a row on tap. Make sure no other row is selected
			$output .= "
					$('#{$this->get_id()} tbody').on( 'click', 'tr', function (event) {
				        if ( $(this).hasClass('selected') ) {
				            $(this).removeClass('selected');
				        }
				        else {
				            {$this->get_id()}_table.$('tr.selected').removeClass('selected');
				             $(this).addClass('selected');
				        }
				    } );
					";
		}
		return $output;
	}
	
	/**
	 * Generates a popup context menu with actions available upon selection of a row. The menu contains all buttons, that do
	 * something with a specific object (= the corresponding action has input_rows_min = 1) or do not have an action at all.
	 * This way, the user really only sees the buttons, that perform an action with the selected object and not those, that
	 * do something with all object, create new objects, etc.
	 */
	private function build_html_context_menu(){
		if (!$this->get_widget()->get_context_menu_enabled()) return '';
		$buttons_html = '';
		foreach ($this->get_widget()->get_buttons() as $b){
			/* @var $b \exface\Core\Widgets\Button */
			if (!$b->is_hidden() && (!$b->get_action() || $b->get_action()->get_input_rows_min() === 1)){
				$buttons_html .= '<li data-icon="' . $this->get_icon_class($b->get_icon_name()) . '"><a href="#" onclick="' . $this->get_template()->get_element($b)->build_js_click_function_name() . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->get_caption() . '</a></li>';
			}
		}
		
		if ($buttons_html){
			$output = <<<HTML
<div data-role="popup" id="{$this->get_id()}_context_menu" data-theme="b">
	<ul data-role="listview" data-inset="true">
		{$buttons_html}
	</ul>
</div>
HTML;
		}
		return $output;
	}
	
	protected function build_html_top_toolbar(){
		$table_caption = $this->get_widget()->get_caption() ? $this->get_widget()->get_caption() : $this->get_meta_object()->get_name();
		
		$output = <<<HTML
		<form id="{$this->get_id()}_quickSearch_form">
		<div class="ui-toolbar ui-bar ui-bar-a">
			<div class="ui-grid-a ui-responsive">
				<div class="ui-block-a">
					<h2 style="line-height: 2.8em;">$table_caption</h2>
				</div>
				<div class="ui-block-b" style="float: right; text-align: right;">
					<div data-role="controlgroup" data-type="horizontal" style="float: right;">
						<a href="#" data-role="button" data-icon="action-search" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon" onclick="{$this->get_id()}_table.draw();return false;">Search</a>
						<a href="#{$this->get_id()}_popup_config" data-role="button" data-icon="action-settings" data-iconpos="notext" data-shadow="false" class="ui-corner-all ui-nodisc-icon ui-alt-icon">Filters & Sorting</a>
					</div>
					<div style="margin-right: 90px;">
						<input id="{$this->get_id()}_quickSearch" type="text" data-mini="true" placeholder="Quick search" data-clear-btn="true" />
					</div>
				</div>
			</div>
		</div>
		<div class="ui-grid-a ui-responsive" id="{$this->get_id()}_filters_container" style="padding: 0 1em;">
		</div>
	</form>
HTML;
		return $output;
	}
	
	protected function build_html_bottom_toolbar($buttons_html){
		$output = <<<HTML
		<div class="ui-bar ui-toolbar ui-bar-a tableFooter">
		<div style="float:left" class="ui-alt-icon">{$buttons_html}</div>
		<div style="float:right">
			<div data-role="controlgroup" data-type="horizontal" style="float:left;margin-right:10px;">
				<a href="#" id="{$this->get_id()}_prevPage" class="ui-btn ui-corner-all ui-btn-icon-notext ui-icon-navigation-arrow-back ui-nodisc-icon ui-alt-icon">&lt;</a>
				<a href="#{$this->get_id()}_pagingPopup" id="{$this->get_id()}_pageInfo" data-rel="popup" class="ui-btn ui-corner-all"></a>
				<a href="#" id="{$this->get_id()}_nextPage" class="ui-btn ui-corner-all ui-btn-icon-notext ui-icon-navigation-arrow-forward ui-nodisc-icon ui-alt-icon">&gt;</a>
			</div>
			<div style="float:right;">
				<a href="#" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-navigation-refresh" onclick="{$this->get_id()}_table.draw(false); return false;">Reload</a>
				<a href="#{$this->get_id()}_popup_config" class="ui-btn ui-btn-inline ui-btn-icon-notext ui-corner-all ui-alt-icon ui-icon-action-settings">Tabelleneinstellungen</a>
			</div>
			<div data-role="popup" id="{$this->get_id()}_pagingPopup" style="width:300px; padding:10px;">
				<form>
				    <label for="{$this->get_id()}_pageSlider">Page:</label>
				    <input type="range" name="{$this->get_id()}_pageSlider" id="{$this->get_id()}_pageSlider" min="1" max="100" value="1">
				</form>
			</div>
		</div>
	</div>
HTML;
		return $output;
	}
	
	protected function build_js_pagination(){
		$output = <<<JS
	$('#{$this->get_id()}_prevPage').on('click', function(){{$this->get_id()}_table.page('previous'); {$this->get_id()}_table.draw(false);});
	$('#{$this->get_id()}_nextPage').on('click', function(){{$this->get_id()}_table.page('next'); {$this->get_id()}_table.draw(false);});
	
	$('#{$this->get_id()}_pagingPopup').on('popupafteropen', function(){
		$('#{$this->get_id()}_pageSlider').val({$this->get_id()}_table.page()+1).attr('max', {$this->get_id()}_table.page.info().pages).slider('refresh');
	});
	
	$('#{$this->get_id()}_pagingPopup').on('popupafterclose', function(){
		{$this->get_id()}_table.page(parseInt($('#{$this->get_id()}_pageSlider').val())-1).draw(false);
	});
JS;
		return $output;
	}
	
	protected function build_js_quicksearch(){
		$output = <<<JS
	$('#{$this->get_id()}_quickSearch_form').on('submit', function(event) {
		{$this->get_id()}_table.draw();	
		event.preventDefault();
		return false;
	});
				
	$('#{$this->get_id()}_quickSearch').on('change', function(event) {
		{$this->get_id()}_table.draw();	
	});
JS;
		return $output;
	}
	
	protected function build_js_row_details(){
		$output = '';
		/* @var $widget \exface\Core\Widgets\DataTable */
		$widget = $this->get_widget();
		if ($widget->has_row_details()){
			$output = <<<JS
	// Add event listener for opening and closing details
	$('#{$this->get_id()} tbody').on('click', 'td.details-control', function () {
		var tr = $(this).closest('tr');
		var row = {$this->get_id()}_table.row( tr );
		
		if ( row.child.isShown() ) {
			// This row is already open - close it
			row.child.hide();
			tr.removeClass('shown');
			tr.find('.{$this->row_details_collapse_icon}').removeClass('{$this->row_details_collapse_icon}').addClass('{$this->row_details_expand_icon}');
			$('#detail'+row.data().id).remove();
			{$this->get_id()}_table.columns.adjust();
		}
		else {
			// Open this row
			row.child('<div id="detail'+row.data().{$widget->get_meta_object()->get_uid_alias()}+'"></div>').show();
			$.get('{$this->get_ajax_url()}&action={$widget->get_row_details_action()}&resource={$this->get_page_id()}&element={$widget->get_row_details_container()->get_id()}&prefill={"meta_object_id":"{$widget->get_meta_object_id()}","rows":[{"{$widget->get_meta_object()->get_uid_alias()}":' + row.data().{$widget->get_meta_object()->get_uid_alias()} + '}]}'+'&exfrid='+row.data().{$widget->get_meta_object()->get_uid_alias()}, 
				function(data){
					$('#detail'+row.data().{$widget->get_meta_object()->get_uid_alias()}).append(data).enhanceWithin();
					{$this->get_id()}_table.columns.adjust();
				}
			);
			tr.next().addClass('detailRow');
			tr.addClass('shown');
			tr.find('.{$this->row_details_expand_icon}').removeClass('{$this->row_details_expand_icon}').addClass('{$this->row_details_collapse_icon}');
		}
	} );
JS;
		}
		return $output;
	}
	
	/**
	 * Generates JS to disable text selection on the rows of the table. If not done so, every time you longtap a row, something gets selected along
	 * with the context menu being displayed. It look awful. 
	 * @return string
	 */
	private function build_js_disable_text_selection(){
		return "$('#{$this->get_id()} tbody tr td').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);";
	}
	
	public function is_editable() {
		return $this->editable;
	}
	
	public function set_editable($value) {
		$this->editable = $value;
	}
	
	public function get_editors(){
		return $this->editors;
	}
}
?>