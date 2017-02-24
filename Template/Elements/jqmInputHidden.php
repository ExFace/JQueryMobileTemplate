<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmInputHidden extends jqmInput {
	
	protected function init(){
		parent::init();
		$this->set_element_type('hidden');
	}
	
	public function generate_html(){
		$output = '<input type="hidden" 
								name="' . $this->get_widget()->get_attribute_alias() . '" 
								value="' . addslashes($this->get_widget()->get_value()) . '" 
								id="' . $this->get_id() . '" />';
		return $output;
	}

}