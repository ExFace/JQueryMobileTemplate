	/**
	 * @param nbNames: Kommaseparierte Liste mit den IDs der Inputboxen, f�r die das Keypad aktiviert werden soll.
	 */
	function setNumberboxes(nbNames) {
		var nbNamesArr = nbNames.split(",");
		
		for(var i=0; i<nbNamesArr.length; i++) {
			var nbName = nbNamesArr[i];
			if (nbName.length>0) {
    		setNumberbox($('#'+nbName));
    	}
		};
	}
	
	/**
	 * @param numberbox: Element f�r das Keypad aktiviert werden soll
	 */
	function setNumberbox(numberbox) {
		numberbox.attr("readonly", true);
		numberbox.unbind('click');
		numberbox.bind('click',function(){
				$('#n_keypad_b').fadeToggle('fast');
        $('#n_keypad').fadeToggle('fast');
				$('#kp_inp').val(numberbox.val());
      		
     		$('.done').unbind('click');
     		$('.done').bind('click', function(){
          	$('#n_keypad').hide('fast');
          	$('#n_keypad_b').hide('fast');
          	numberbox.val($('#kp_inp').val());
      		});
      });
    }
    
    /**
     * Generiert das Keypad (unsichtbar)
     * @param parentId (Id von dem Container, in dem das Keypad generiert werden soll.
     */
    function createKeypad(parentEl) {
    	if ($('#n_keypad_b').length == 0) {
	    	parentEl.append(
  '<div class="ui-popup-screen ui-overlay-a modal-backdrop in" id="n_keypad_b" style="z-index:4999; display: none;"/>&nbsp;</div>'+
  '<table class="ui-bar-a modal-content calc table" id="n_keypad" style="position:absolute; left:50px; top:50px; z-index:5000; display: none; -khtml-user-select: none; border-radius:10px; padding:10px;">'+
  '  <tr><td colspan=4><input data-theme="b" type="text" id="kp_inp" style="text-align: right;"/></td></tr>'+
  '  <tr>'+
  '     <td><a data-role="button" data-theme="b" class="numero">7</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">8</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">9</a></td>'+
  '     <td><a data-role="button" data-theme="a" class="del">Del</a></td>'+
  '  </tr>'+
  '  <tr>'+
  '     <td><a data-role="button" data-theme="b" class="numero">4</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">5</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">6</a></td>'+
  '     <td><a data-role="button" data-theme="a" class="clear">Clear</a></td>'+
  '  </tr>'+
  '  <tr>'+
  '     <td><a data-role="button" data-theme="b" class="numero">1</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">2</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">3</a></td>'+
  '     <td><a data-role="button" data-theme="a" class="cancel">Cancel</a></td>'+
  '  </tr>'+
  '  <tr>'+
  '     <td><a data-role="button" data-theme="a" class="neg">-</a></td>'+
  '     <td><a data-role="button" data-theme="b" class="numero">0</a></td>'+
  '     <td><a data-role="button" data-theme="a" class="pos">+</a></td>'+
  '     <td><a data-role="button" data-theme="a" class="done">Done</a></td>'+
  '  </tr>'+
  '</table>'
  	  	);    
  	  	
      $('.numero').bind('click', function(){
    	  /*var press = jQuery.Event("keypress");
			press.ctrlKey = false;
			press.which = $(this).text().charCodeAt(0);
			$('#kp_inp').select().trigger(press);*/
         $('#kp_inp').val($('#kp_inp').val() + $(this).text());
      });

      $('.del').bind('click', function(){
          $('#kp_inp').val($('#kp_inp').val().substring(0,$('#kp_inp').val().length - 1));
      });

      $('.neg').bind('click', function(){
          if (!isNaN($('#kp_inp').val()) && $('#kp_inp').val().length > 0) {
            if (parseInt($('#kp_inp').val()) > 0) {
              $('#kp_inp').val(parseInt($('#kp_inp').val()) - 1);
            }
          }
      });      

      $('.pos').bind('click', function(){
          if (!isNaN($('#kp_inp').val())) {
          	if ($('#kp_inp').val().length == 0) {
          		$('#kp_inp').val(1);
          	} else {
            	$('#kp_inp').val(parseInt($('#kp_inp').val()) + 1);
            }
          }
      });      
      
      $('.clear').bind('click', function(){
          $('#kp_inp').val('');
      });
      
     	$('.cancel').bind('click', function(){
         	$('#n_keypad').hide('fast');
         	$('#n_keypad_b').hide('fast');
      });        	  	
  	  }    	
    }