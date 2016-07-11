<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmInputHidden extends jqmInput {
	protected $element_type = 'hidden';
	
	function generate_html(){
		$output = '<input type="hidden" 
								name="' . $this->get_widget()->get_attribute_alias() . '" 
								value="' . addslashes($this->get_widget()->get_value()) . '" 
								id="' . $this->get_id() . '" />';
		return $output;
	}

}