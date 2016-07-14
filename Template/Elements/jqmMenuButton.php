<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
use exface\Core\Widgets\Button;
/**
 * generates jQuery Mobile buttons for ExFace
 * @author Andrej Kabachnik
 *
 */
class jqmMenuButton extends jqmAbstractElement {

	/**
	 * @see \exface\Templates\jeasyui\Widgets\abstractWidget::generate_html()
	 */
	function generate_html(){
		$buttons_html = '';
		foreach ($this->get_widget()->get_buttons() as $b){
			$buttons_html .= '<li data-icon="' . $this->get_icon_class($b->get_icon_name()) . '"><a href="#" onclick="' . $this->generate_js_button_function_name($b) . '(); $(this).parent().parent().parent().popup(\'close\');">' . $b->get_caption() . '</a></li>';
		}
		$icon_classes = ($this->get_widget()->get_icon_name() ? ' ui-icon-' . $this->get_icon_class($this->get_widget()->get_icon_name()) : '') . ($this->get_widget()->get_caption() ? '' : ' ui-btn-icon-notext');
		
		$output = <<<HTML

<a href="#{$this->get_id()}" data-rel="popup" class="ui-btn ui-btn-inline ui-corner-all ui-alt-icon {$icon_classes}">{$this->get_widget()->get_caption()}</a>
<div data-role="popup" id="{$this->get_id()}" data-theme="b">
	<ul data-role="listview" data-inset="true">
		{$buttons_html}
	</ul>
</div>		
HTML;
		
		return $output;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \exface\JQueryMobileTemplate\Template\Elements\jqmAbstractElement::generate_js()
	 */
	function generate_js($jqm_page_id = null){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $b){
			if ($click = $b->generate_js_click_function()) {
				$output .= "
					function " . $this->generate_js_button_function_name($b) . "(){
						" . $b->generate_js_click_function() . "
					}
					";
			}
		}
		return $output;
	}
	
	function generate_js_button_function_name(Button $button){
		return $this->get_template()->get_element($button)->generate_js_click_function_name();
	}
}
?>