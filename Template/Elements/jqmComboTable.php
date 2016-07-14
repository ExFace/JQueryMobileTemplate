<?php
namespace exface\JQueryMobileTemplate\Template\Elements;
/**
 * In jQuery Mobile a ComboTable is represented by a filterable UL-list. The code is based on the JQM-example below.
 * jqm example: http://demos.jquerymobile.com/1.4.5/listview-autocomplete-remote/
 * @author Andrej Kabachnik
 *
 */
class jqmComboTable extends jqmInput {
	private $min_chars_to_search = 1;
	
	function generate_html(){
		$output = '	<div class="fitem exf_input" title="' . $this->get_hint() . '">
						<label for="' . $this->get_id() . '">' . $this->get_widget()->get_caption() . '</label>
						<input id="' . $this->get_id() . '_autocomplete_input"  data-type="search" placeholder="Suchen..." value="' . $this->get_widget()->get_value_text() . '" />
						<input type="hidden"		
								id="' . $this->get_id() . '" 
								name="' . $this->get_widget()->get_attribute_alias() . '"
								value="' . $this->escape_string($this->get_widget()->get_value()) . '" />
						<ul id="' . $this->get_id() . '_autocomplete" data-role="listview" data-inset="true" data-filter="true" data-input="#' . $this->get_id() . '_autocomplete_input" ></ul> 
					</div>';
		return $output;
	}
	
	function generate_js($jqm_page_id = null){
		/* @var $widget \exface\Core\Widgets\ComboTable */
		$widget = $this->get_widget();
		$output = <<<JS
		
$(document).on('pagecreate', '#{$jqm_page_id}', function() {
	$( "#{$this->get_id()}_autocomplete" ).on( "filterablebeforefilter", function ( e, data ) {
        var ul = $( this ),
            input = $( data.input ),
            value = input.val(),
            html = "";
        ul.html( "" );
        $('#{$this->get_id()}').val('');
        if ( value && value.length >= {$this->min_chars_to_search} ) {
            ul.html( "<li><div class='ui-loader'><span class='ui-icon ui-icon-loading'></span></div></li>" );
            ul.listview( "refresh" );
            $.ajax({
                url: "{$this->get_ajax_url()}",
                dataType: "json",
                data: {
                	action: "{$widget->get_lazy_loading_action()}",
                	resource: "{$this->get_page_id()}",
					element: "{$widget->get_table()->get_id()}",
					object: "{$widget->get_table()->get_meta_object()->get_id()}",
                    q: input.val()
                },
				success: function ( response ) {
					$.each( response.data, function ( i, val ) {
	                    html += '<li><a href="#" exf-value="' + val.{$widget->get_table()->get_meta_object()->get_uid_alias()} + '">' + val.{$widget->get_table()->get_meta_object()->get_label_alias()} + '</a></li>';
	                	if (response.data.length == 1){
	                		$('#{$this->get_id()}').val(val.{$widget->get_table()->get_meta_object()->get_uid_alias()});
	                		$("#{$this->get_id()}_autocomplete_input").val(val.{$widget->get_table()->get_meta_object()->get_label_alias()});
                		}
					});
	                ul.html( html );
	                ul.listview( "refresh" );
	                ul.trigger( "updatelayout");
	            },
				error: function (jqXHR, status, error) {
					ul.html( "<li>Error: " + status + "</li>" );
					ul.listview( "refresh" );		
				}
            });
        }
    });
    
    $("#{$this->get_id()}_autocomplete_input").on('input', function(event){ $( "#{$this->get_id()}_autocomplete" ).html(''); });

});
	                    		
$( document ).on('click', '#{$this->get_id()}_autocomplete li a', function(event){
	$('#{$this->get_id()}').val($(this).attr('exf-value'));
	$("#{$this->get_id()}_autocomplete_input").val($(this).html());
	$('#{$this->get_id()}_autocomplete').html('');		
	event.preventDefault();
	return false;	
});
		
JS;
		return $output;
	}
}
?>