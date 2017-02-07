<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
use exface\Core\Interfaces\Actions\ActionInterface;

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
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::build_js_data_getter($action, $custom_body_js)
	 */
	public function build_js_data_getter(ActionInterface $action = null){
		if ($this->get_widget()->is_readonly()){
			return '{}';
		} else {
			return parent::build_js_data_getter($action);
		}
	}
}
?>