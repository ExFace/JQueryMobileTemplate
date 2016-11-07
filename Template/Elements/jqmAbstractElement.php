<?php namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\JQueryMobileTemplate\Template\JQueryMobileTemplate;

abstract class jqmAbstractElement extends AbstractJqueryElement {
	
	private $jqm_page_id = null;
	
	public function build_js_init_options(){
		return '';
	}
	
	public function build_js_inline_editor_init(){
		return '';
	}
	
	public function escape_string($string){
		return htmlentities($string, ENT_QUOTES);
	}
	
	public function prepare_data(\exface\Core\Interfaces\DataSheets\DataSheetInterface $data_sheet){
		// apply the formatters
		foreach ($data_sheet->get_columns() as $name => $col){
			if ($formatter = $col->get_formatter()) {
				$expr = $formatter->to_string();
				$function = substr($expr, 1, strpos($expr, '(')-1);
				$formatter_class_name = 'formatters\'' . $function;
				if (class_exists($class_name)){
					$formatter = new $class_name($y);
				}
				// See if the formatter returned more results, than there were rows. If so, it was also performed on
				// the total rows. In this case, we need to slice them off and pass to set_column_values() separately.
				// This only works, because evaluating an expression cannot change the number of data rows! This justifies
				// the assumption, that any values after count_rows() must be total values.
				$vals = $formatter->evaluate($data_sheet, $name);
				if ($data_sheet->count_rows() < count($vals)) {
					$totals = array_slice($vals, $data_sheet->count_rows());
					$vals = array_slice($vals, 0, $data_sheet->count_rows());
				}
				$data_sheet->set_column_values($name, $vals, $totals);
			}
		}
		
		$data = array();
		$data['data'] = $data_sheet->get_rows();
		$data['recordsFiltered'] = $data_sheet->count_rows_all();
		$data['recordsTotal'] = $data_sheet->count_rows_all();
		$data['footer'] = $data_sheet->get_totals_rows();
		return $data;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::get_template()
	 * @return JQueryMobileTemplate
	 */
	public function get_template(){
		return parent::get_template();
	}
	
	/**
	 * Returns the id of the jQuery Mobile page holding the current widget.
	 * This is very usefull if a complex widget (like a dataGrid) creates multiple pages for
	 * its subwidgets. Each widget needs to know, which page it is on, to be able to bind its
	 * events once this specific page ist created.
	 * 
	 * NOTE: If the page id is not set explicitly, it is generated from the resource id
	 * TODO The generation of a page id from a resource id should be some kind of parameter, so it can be controlled by the user, who also controlls the CMS-template
	 * 
	 * @return string
	 */
	public function get_jqm_page_id() {
		if ($this->jqm_page_id){
			return $this->jqm_page_id;
		} else {
			return 'jqm' . $this->get_page_id();
		}
	}
	
	public function set_jqm_page_id($value) {
		$this->jqm_page_id = $value;
	}
	
	public function build_js_busy_icon_show(){
		return "$.mobile.loading('show');";
	}
	
	public function build_js_busy_icon_hide(){
		return "$.mobile.loading('hide');";
	}
}
?>