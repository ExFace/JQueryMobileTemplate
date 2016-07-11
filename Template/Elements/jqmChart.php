<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
use exface\Core\Widgets\ChartAxis;
class jqmChart extends jqmGrid {
	protected $paginate = false;
	
	function generate_html(){
		$widget = $this->get_widget();
		
		// first the table itself
		$output = '<div id="' . $this->get_id() . '_wrapper" style="width: ' . $widget->get_width() . 'px;">';
		
		// add filters
		if ($widget->get_data()->has_filters()){
			foreach ($widget->get_data()->get_filters() as $fltr){
				$fltr_html .= $this->get_template()->generate_html($fltr);
			}
		}

		// add buttons
		if ($widget->has_buttons()){
			foreach ($widget->get_buttons() as $button){
				$button_html .= $this->get_template()->generate_html($button);
			}
		}
		// create a container for the toolbar
		if ($widget->get_data()->has_filters() || $widget->has_buttons()){
			$output .= '<div id="' . $this->get_toolbar_id() . '">';
			if ($fltr_html){
				$output .= $fltr_html . '
							    <a style="float:right" href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="' . $this->get_function_prefix() . 'doSearch()">Search</a>';
			}
			if ($button_html) {
				$output .= '<div>' . $button_html . '</div>';
			}
			$output .= '</div>';
		}
		
		$output .= '<div id="' . $this->get_id() . '" style="width: ' . $widget->get_width() . 'px; height: ' . $widget->get_height() . 'px;"></div></div>';
		
		return $output;
	}
	
	function generate_js(){
		global $exface;
		/** @var \exface\Core\Widgets\Chart */
		$widget = $this->get_widget();
		
		$output = '';
		$series_data = '';
		$series_init = '';
		$axis_init = '';
		
		$url_params = '';
		$url_params .= '&resource=' . $this->get_page_id();
		$url_params .= '&element=' . $widget->get_id() . '-data';
		$url_params .= '&object=' . $widget->get_meta_object()->get_id();
		
		// send sort information
		if (count($widget->get_data()->get_sorters()) > 0){
			foreach ($widget->get_data()->get_sorters() as $sorter){
				$sort .= ',' . urlencode($sorter->attribute_alias);
				$order .= ',' . urldecode($sorter->direction);
			}
			$url_params .= '&sort=' . substr($sort, 1);
			$url_params .= '&order=' . substr($order, 1);	
		}
		
		// send preset filters
		if ($widget->get_data()->has_filters()){
			foreach ($widget->get_data()->get_filters() as $fltr){
				if ($fltr->get_value()){
					$url_params .= '&' . urlencode($fltr->get_id()) . '=' . urlencode($fltr->get_value());
				}
			}
		}		
		
		$output = '
			function ' . $this->get_function_prefix() . 'load(urlParams){
				if (!urlParams) urlParams = "";
				$.get("' . $this->get_ajax_url() . $url_params . '"+urlParams, function(data){
					var ds = $.parseJSON(data);
				';
		// iterate through axis
		// IDEA in theory, there could be more x-axis, than y-axis. Prehaps we should determine first, which type of axis to iterate through
		$series_axis = $widget->get_axes_y();
		$base_axis = $widget->get_axes_x();
		foreach ($series_axis as $nr => $series){
			if (isset($base_axis[$nr])){
				$base = $base_axis[$nr];
				$axis_x_init = ', {mode: "' . $base->get_axes_type() . '"}';
			}
			
			$series_id = $this->generate_series_id($series->get_data_column_id());
			$output .= '
					var ' . $series_id . ' = [];
					';
			$series_data .= $series_id . '[i] = [ (ds.rows[i]["' . $base->get_data_column_id() . '"]' . ($base->get_axes_type() == 'time' ? '*1000' : '') . '), ds.rows[i]["' . $series->get_data_column_id() . '"] ];';
			$series_init .= ', {data: ' . $series_id
						. ', label: "' . $widget->get_data()->get_column($series->get_data_column_id())->get_caption() 
						. '", yaxis:' . ($nr+1)
						. ($this->generate_options_series($series, $base) ? ', ' . $this->generate_options_series($series, $base) : '')
						. '}';
			$axis_y_init .= ', {axisLabel: "' . $widget->get_data()->get_column($series->get_data_column_id())->get_caption() . '"'
						. ', position: "' . $series->get_position() . '"' . ($series->get_position() == 'right' ? ', alignTicksWithAxis: 1' : '')
						. (is_numeric($series->get_min_value()) ? ', min: ' . $series->get_min_value() : '')
						. (is_numeric($series->get_max_value()) ? ', max: ' . $series->get_max_value() : '')
						. '}';
		}
		$series_init = substr($series_init, 2);
		$axis_y_init = substr($axis_y_init, 2);
		$axis_x_init = substr($axis_x_init, 2);
		
		$output .= '
					for (var i=0; i < ds.rows.length; i++){
						' . $series_data . '
					}
						
					$.plot("#' . $this->get_id() . '", 
						[ ' . $series_init . ' ],
						{
							grid:  { hoverable: true },
							crosshair: {mode: "x"},
							yaxes: [ ' . $axis_y_init . ' ],
							xaxes: [ ' . $axis_x_init . ' ],
							legend: { position: "nw" },
							tooltip: true,
							tooltipOpts: {
								content: "%s: %y.2"
							}
						}
					);
				});
			}
			' . $this->get_function_prefix() . 'load();';
		
		// doSearch function for the filters
		if ($widget->get_data()->has_filters()){
			foreach($widget->get_data()->get_filters() as $fltr){
				$fltr_impl = $this->get_template()->get_element($fltr, $this->get_page_id());
				$output .= $fltr_impl->generate_js();
				$fltrs[] = "'&" . urlencode($fltr->get_id()) . "='+" . $fltr_impl->get_js_value_getter();
			}
			// build JS for the search function
			$output .= '
						function ' .$this->get_function_prefix() . 'doSearch(){
							' . $this->get_function_prefix() . "load(" . implode("+", $fltrs) . ');
						}';
		}
		
		return $output;
	}
	
	public function generate_series_id($string){
		return str_replace(array('.', '(', ')', '=', ',', ' '), '_', $string);
	}
	
	public function generate_options_series(ChartAxis $series, ChartAxis $base = null){
		$options = '';
		switch ($series->get_chart_type()) {
			case 'line': 
				$options = 'lines: {show: true}'; 
				break;
			case 'bars': 
				$options = 'bars: {show: true';
				if ($base->get_axes_type() == time){
					$options .= ', barWidth: 24*60*60*1000';
				}
				$options .= '}'; 
				break;
			case 'area': $options = 'bars: {show: true, fill: true}'; break;
			case 'pie': $options = 'pie: {show: true, label: {show: true} }'; break;
		}
		return $options;		
	}
	
	public function generate_headers(){
		$includes = array(
			'<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/flot/jquery.flot.js"></script>',
			'<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/flot/plugins/tooltip/js/jquery.flot.tooltip.js"></script>',
			'<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/flot/jquery.flot.time.min.js"></script>',
			'<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/flot/jquery.flot.crosshair.min.js"></script>',
			'<script type="text/javascript" src="exface/vendor/exface/JQueryMobileTemplate/Template/js/flot/plugins/axislabels/jquery.flot.axislabels.js"></script>'
		);
		return $includes;
	}
}
?>