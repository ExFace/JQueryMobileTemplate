<?php
namespace exface\JQueryMobileTemplate\Template\Elements;

use exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement;
use exface\JQueryMobileTemplate\Template\JQueryMobileTemplate;

abstract class jqmAbstractElement extends AbstractJqueryElement
{

    private $jqm_page_id = null;

    public function buildJsInitOptions()
    {
        return '';
    }

    public function buildJsInlineEditorInit()
    {
        return '';
    }

    public function escapeString($string)
    {
        return htmlentities($string, ENT_QUOTES);
    }

    public function prepareData(\exface\Core\Interfaces\DataSheets\DataSheetInterface $data_sheet)
    {
        // apply the formatters
        foreach ($data_sheet->getColumns() as $name => $col) {
            if ($formatter = $col->getFormatter()) {
                $expr = $formatter->toString();
                $function = substr($expr, 1, strpos($expr, '(') - 1);
                $formatter_class_name = 'formatters\'' . $function;
                if (class_exists($class_name)) {
                    $formatter = new $class_name($y);
                }
                // See if the formatter returned more results, than there were rows. If so, it was also performed on
                // the total rows. In this case, we need to slice them off and pass to set_column_values() separately.
                // This only works, because evaluating an expression cannot change the number of data rows! This justifies
                // the assumption, that any values after count_rows() must be total values.
                $vals = $formatter->evaluate($data_sheet, $name);
                if ($data_sheet->countRows() < count($vals)) {
                    $totals = array_slice($vals, $data_sheet->countRows());
                    $vals = array_slice($vals, 0, $data_sheet->countRows());
                }
                $data_sheet->setColumnValues($name, $vals, $totals);
            }
        }
        
        $data = array();
        $data['data'] = $data_sheet->getRows();
        $data['recordsFiltered'] = $data_sheet->countRowsAll();
        $data['recordsTotal'] = $data_sheet->countRowsAll();
        $data['footer'] = $data_sheet->getTotalsRows();
        return $data;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::getTemplate()
     * @return JQueryMobileTemplate
     */
    public function getTemplate()
    {
        return parent::getTemplate();
    }

    /**
     * Returns the id of the jQuery Mobile page holding the current widget.
     * This is very usefull if a complex widget (like a dataGrid) creates multiple pages for
     * its subwidgets. Each widget needs to know, which page it is on, to be able to bind its
     * events once this specific page ist created.
     *
     * NOTE: If the page id is not set explicitly, it is generated from the resource id
     * TODO The generation of a page id from a resource id should be some kind of parameter, so it can be controlled by the user, who also controlls the CMS-template
     *
     * @return string
     */
    public function getJqmPageId()
    {
        if ($this->jqm_page_id) {
            return $this->jqm_page_id;
        } else {
            return 'jqm' . $this->getPageId();
        }
    }

    public function setJqmPageId($value)
    {
        $this->jqm_page_id = $value;
    }

    public function buildJsBusyIconShow()
    {
        return "$.mobile.loading('show');";
    }

    public function buildJsBusyIconHide()
    {
        return "$.mobile.loading('hide');";
    }
    
    /**
     * Element ids for jQuery mobile must contain the page id as the default AJAX loading method
     * keeps pages in the DOM as long as it likes. This means, that all kinds of cheks for existing
     * or initialized elements (like wether a DataTable is initialized for a given element id)
     * may return TRUE if the previously shown page had an element with the same id. This happens
     * quite often since element ids are derived from widget types: thus, a all typical pages with a
     * DataTable will have the same id for the data table.
     *
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::getId()
     */
    public function getId()
    {
        return '_' . $this->cleanId($this->getWidget()->getPageId()) . '_' . parent::getId();
    }
}
?>