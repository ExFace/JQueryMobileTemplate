<?php namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryContainerTrait;

class jqmContainer extends jqmAbstractElement {
	
	use JqueryContainerTrait;
	
	public function generate_js($jqm_page_id = null){
		return $this->build_js_for_children($jqm_page_id);
	}
	
	public function build_js_for_children($jqm_page_id = null){
		foreach ($this->get_widget()->get_children() as $subw){
			$output .= $this->get_template()->generate_js($subw, $jqm_page_id) . "\n";
		};
		return $output;
	}
	
	public function build_js_for_widgets($jqm_page_id = null){
		foreach ($this->get_widget()->get_widgets() as $subw){
			$output .= $this->get_template()->generate_js($subw, $jqm_page_id) . "\n";
		};
		return $output;
	}
	
}
?>