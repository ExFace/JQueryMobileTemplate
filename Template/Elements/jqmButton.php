<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryButtonTrait;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;
use exface\Core\Interfaces\Actions\iShowWidget;

/**
 * generates jQuery Mobile buttons for ExFace
 *
 * @author Andrej Kabachnik
 *        
 */
class jqmButton extends jqmAbstractElement
{
    
    use JqueryButtonTrait;

    function generateJs($jqm_page_id = null)
    {
        $output = '';
        $hotkey_handlers = array();
        
        // Actions with template scripts may contain some helper functions or global variables.
        // Print the here first.
        if ($this->getAction() && $this->getAction()->implementsInterface('iRunTemplateScript')) {
            $output .= $this->getAction()->printHelperFunctions();
        }
        
        if ($click = $this->buildJsClickFunction()) {
            
            // Generate the function to be called, when the button is clicked
            $output .= "
				function " . $this->buildJsClickFunctionName() . "(input){
					" . $this->buildJsClickFunction() . "
				}
				";
            
            // Handle hotkeys
            if ($this->getWidget()->getHotkey()) {
                $hotkey_handlers[$this->getWidget()->getHotkey()][] = $this->buildJsClickFunctionName();
            }
        }
        
        foreach ($hotkey_handlers as $hotkey => $handlers) {
            // TODO add hotkey detection here
        }
        
        return $output;
    }

    /**
     *
     * @see \exface\Templates\jeasyui\Widgets\abstractWidget::generateHtml()
     */
    function generateHtml()
    {
        $action = $this->getAction();
        /* @var $widget \exface\Core\Widgets\Button */
        $widget = $this->getWidget();
        $icon_classes = ($widget->getIconName() && ! $widget->getHideButtonIcon() ? ' ui-icon-' . $this->buildCssIconClass($widget->getIconName()) : '') . ($widget->getCaption() && ! $widget->getHideButtonText() ? ' ui-btn-icon-left' : ' ui-btn-icon-notext');
        $hidden_class = ($widget->isHidden() ? ' exfHidden' : '');
        $output = '
				<a href="#" plain="true" ' . $this->generateDataAttributes() . ' class="ui-btn ui-btn-inline ui-corner-all' . $icon_classes . $hidden_class . '" onclick="' . $this->buildJsClickFunctionName() . '();">
						' . $widget->getCaption() . '
				</a>';
        
        return $output;
    }

    protected function buildJsClickShowDialog(ActionInterface $action, AbstractJqueryElement $input_element)
    {
        $widget = $this->getWidget();
        // FIXME the request should be sent via POST to avoid length limitations of GET
        // The problem is, we would have to fetch the page via AJAX and insert it into the DOM, which
        // would probably mean, that we have to take care of removing it ourselves (to save memory)...
        return $this->buildJsRequestDataCollector($action, $input_element) . "
					$.mobile.changePage('" . $this->getAjaxUrl() . "&resource=" . $widget->getPageId() . "&element=" . $widget->getId() . "&action=" . $widget->getActionAlias() . "&data=' + encodeURIComponent(JSON.stringify(requestData)));
					";
    }

    protected function buildJsClickShowWidget(iShowWidget $action, AbstractJqueryElement $input_element)
    {
        $widget = $this->getWidget();
        if ($action->getPageId() != $this->getPageId()) {
            $output = $this->buildJsRequestDataCollector($action, $input_element) . "
				 	$.mobile.changePage('" . $this->getTemplate()->createLinkInternal($action->getPageId()) . "?prefill={\"meta_object_id\":\"" . $widget->getMetaObjectId() . "\",\"rows\":[{\"" . $widget->getMetaObject()->getUidAttributeAlias() . "\":' + requestData.rows[0]." . $widget->getMetaObject()->getUidAttributeAlias() . " + '}]}');";
        }
        return $output;
    }

    protected function buildJsClickGoBack(ActionInterface $action, AbstractJqueryElement $input_element)
    {
        return '$.mobile.back();';
    }

    protected function buildJsCloseDialog($widget, $input_element)
    {
        return ($widget->getWidgetType() == 'DialogButton' && $widget->getCloseDialogAfterActionSucceeds() ? "$('#" . $input_element->getId() . "').dialog('close');" : "");
    }

    protected function generateDataAttributes()
    {
        $widget = $this->getWidget();
        $output = '';
        if ($widget->getWidgetType() == 'DialogButton') {
            if ($widget->getCloseDialogAfterActionSucceeds()) {
                $output .= ' data-rel="back" ';
            }
        }
        return $output;
    }
}
?>