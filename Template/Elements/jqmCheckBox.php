<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
class jqmCheckBox extends jqmAbstractElement {
	
	protected function init(){
		parent::init();
		$this->set_element_type('checkbox');
	}
	
	function generate_html(){
		$output = '	<div class="fitem exf_input" title="' . $this->build_hint_text() . '">
						<label>' . $this->get_widget()->get_caption() . '</label>
						<input type="checkbox" value="1" 
								form="" 
								id="' . $this->get_widget()->get_id() . '_checkbox"
								onchange="$(\'#' . $this->get_widget()->get_id() . '\').val(this.checked);"' . '
								' . ($this->get_widget()->get_value() ? 'checked="checked" ' : '') . '
								' . ($this->get_widget()->is_disabled() ? 'disabled="disabled"' : '') . ' />
						<input type="hidden" name="' . $this->get_widget()->get_attribute_alias() . '" id="' . $this->get_widget()->get_id() . '" value="' . $this->get_widget()->get_value() . '" />
					</div>';
		return $output;
	}
	
	function generate_js($jqm_page_id = null){
		return '';
	}
	
	function build_js_init_options(){
		$options = 'on: "&#10004;"'
				. ', off: ""';
		return $options;
	}
}
?>