<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmInput extends jqmAbstractElement {
	
	protected function init(){
		parent::init();
		$this->set_element_type('text');
	}
	
	public function generate_html(){
		$output = '	<div class="fitem exf_input" title="' . $this->build_hint_text() . '">
						<label for="' . $this->get_id() . '">' . $this->get_widget()->get_caption() . '</label>
						<input data-clear-btn="true"
								type="' . $this->get_element_type() . '"
								name="' . $this->get_widget()->get_attribute_alias() . '" 
								value="' . $this->escape_string($this->get_widget()->get_value()) . '" 
								id="' . $this->get_id() . '"  
								' . ($this->get_widget()->is_required() ? 'required="true" ' : '') . '
								' . ($this->get_widget()->is_disabled() ? 'disabled="disabled" ' : '') . '/>
					</div>';
		return $output;
	}
	
	public function generate_js($jqm_page_id = null){
		return '';
	}
}
?>