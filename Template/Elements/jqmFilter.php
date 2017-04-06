<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryFilterTrait;

class jqmFilter extends jqmAbstractElement {
	
	use JqueryFilterTrait;
	
	/**
	 * Need to override the generate_js() method of the trait to make sure, the $jqm_page_id is allways passed along
	 * 
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generate_js()
	 */
	function generate_js($jqm_page_id = NULL){
		return $this->get_input_element()->generate_js($jqm_page_id);
	}
}
?>