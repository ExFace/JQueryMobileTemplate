<?php namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;

/**
 * generates jQuery Mobile buttons for ExFace
 * @author Andrej Kabachnik
 *
 */
class jqmButton extends jqmAbstractElement {
	
	use JqueryButtonTrait;

	function generate_js($jqm_page_id = null){
		$output = '';
		$hotkey_handlers = array();
		
		// Actions with template scripts may contain some helper functions or global variables.
		// Print the here first.
		if ($this->get_action() && $this->get_action()->implements_interface('iRunTemplateScript')){
			$output .= $this->get_action()->print_helper_functions();
		}
		
		if ($click = $this->build_js_click_function()) {
			
			// Generate the function to be called, when the button is clicked
			$output .= "
				function " . $this->build_js_click_function_name() . "(input){
					" . $this->build_js_click_function() . "
				}
				";
			
			// Handle hotkeys
			if ($this->get_widget()->get_hotkey()){
				$hotkey_handlers[$this->get_widget()->get_hotkey()][] = $this->build_js_click_function_name();
			}
		}
		
		foreach ($hotkey_handlers as $hotkey => $handlers){
			// TODO add hotkey detection here
		}
		
		return $output;
	}

	/**
	 * @see \exface\Templates\jeasyui\Widgets\abstractWidget::generate_html()
	 */
	function generate_html(){
		$action = $this->get_action();
		/* @var $widget \exface\Core\Widgets\Button */
		$widget = $this->get_widget();
		$icon_classes = ($widget->get_icon_name() && !$widget->get_hide_button_icon() ? ' ui-icon-' . $this->build_css_icon_class($widget->get_icon_name()) : '') . ($widget->get_caption() && !$widget->get_hide_button_text() ? ' ui-btn-icon-left' : ' ui-btn-icon-notext');
		$hidden_class = ($widget->is_hidden() ? ' exfHidden' : '');
		$output = '
				<a href="#" plain="true" ' . $this->generate_data_attributes() . ' class="ui-btn ui-btn-inline ui-corner-all' . $icon_classes . $hidden_class . '" onclick="' . $this->build_js_click_function_name() . '();">
						' . $widget->get_caption() . '
				</a>';

		return $output;
	}
	
	protected function build_js_click_show_dialog(ActionInterface $action, AbstractJqueryElement $input_element){
		$widget = $this->get_widget();
		// FIXME the request should be sent via POST to avoid length limitations of GET
		// The problem is, we would have to fetch the page via AJAX and insert it into the DOM, which
		// would probably mean, that we have to take care of removing it ourselves (to save memory)...
		return $this->build_js_request_data_collector($action, $input_element) . "
					$.mobile.changePage('" . $this->get_ajax_url() . "&resource=".$widget->get_page_id()."&element=".$widget->get_id()."&action=".$widget->get_action_alias()."&data=' + encodeURIComponent(JSON.stringify(requestData)));
					";
	}
	
	protected function build_js_click_show_widget(ActionInterface $action, AbstractJqueryElement $input_element){
		$widget = $this->get_widget();
		if ($action->get_page_id() != $this->get_page_id()){
			$output = $this->build_js_request_data_collector($action, $input_element) . "
				 	$.mobile.changePage('" . $this->get_template()->create_link_internal($action->get_page_id()) . "?prefill={\"meta_object_id\":\"" . $widget->get_meta_object_id() . "\",\"rows\":[{\"" . $widget->get_meta_object()->get_uid_alias() . "\":' + requestData.rows[0]." . $widget->get_meta_object()->get_uid_alias() . " + '}]}');";
		}
		return $output;
	}
	
	protected function build_js_click_go_back(ActionInterface $action, AbstractJqueryElement $input_element){
		return '$.mobile.back();';
	}

	protected function build_js_close_dialog($widget, $input_element){
		return ($widget->get_widget_type() == 'DialogButton' && $widget->get_close_dialog_after_action_succeeds() ? "$('#" . $input_element->get_id() . "').dialog('close');" : "" );
	}
	
	protected function generate_data_attributes(){
		$widget = $this->get_widget();
		$output = '';
		if ($widget->get_widget_type() == 'DialogButton'){
			if ($widget->get_close_dialog_after_action_succeeds()){
				$output .= ' data-rel="back" ';
			}
		}
		return $output;
	}
}
?>