
    (function() {

     var scripts = document.getElementsByTagName('script');
			var myScript = scripts[ scripts.length - 1 ];
			var queryString = myScript.src.replace(/^[^\?]+\??/,'');
			var params = parseQuery( queryString );

			function parseQuery ( query ) {
				 var Params = new Object ();
				 if ( ! query ) return Params; // return empty object
				 var Pairs = query.split(/[;&]/);
				 for ( var i = 0; i < Pairs.length; i++ ) {
						var KeyVal = Pairs[i].split('=');
						if ( ! KeyVal || KeyVal.length != 2 ) continue;
						var key = unescape( KeyVal[0] );
						var val = unescape( KeyVal[1] );
						val = val.replace(/\+/g, ' ');
						Params[key] = val;

						}
					 return Params;
		   }

    // Localize jQuery variable
    var jQuery;

    /******** Load jQuery if not present *********/
    if (window.jQuery === undefined) {

        var script_tag = document.createElement('script');
        script_tag.setAttribute("type","text/javascript");
        script_tag.setAttribute("src",
            "http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js");
        script_tag.onload = scriptLoadHandler;
        script_tag.onreadystatechange = function () { // Same thing but for IE
            if (this.readyState == 'complete' || this.readyState == 'loaded') {
                scriptLoadHandler();
            }
        };
        // Try to find the head, otherwise default to the documentElement

        (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
    } else {
        // The jQuery version on the window is the one we want to use
        jQuery = window.jQuery;
        main();
    }

    /******** Called once jQuery has loaded ******/
    function scriptLoadHandler() {
        // Restore $ and window.jQuery to their previous values and store the
        // new jQuery in our local jQuery variable
        jQuery = window.jQuery.noConflict(true);
        // Call our main function
        main();
    }

    /******** Our main function ********/
    function main() {
        jQuery(document).ready(function() {
            /******* Load CSS *******/
            var css_link = jQuery("<link>", {
                rel: "stylesheet",
                type: "text/css",
                href: params.surl+"media/com_invitex/css/invitex.css"
            });
            css_link.appendTo('head');


            /******* Load HTML *******/
        //console.log(params);
         var jsonp_url = params.surl+"index.php?option=com_invitex&view=namecard&pid="+params.pid+"&tmpl=component&itemid="+params.itemid+"&namecard_template="+params.namecard_template+"&callback=?";

        //$('#example-widget-container').load(jsonp_url);
           jQuery.get(jsonp_url, function(data) {
  				  jQuery('#example-widget-container').html(data);
  				  jQuery('#example-widget-container input').remove();
						//$('#example-widget-container').attr("id","newid");

         });



        });
    }

    })(); // We call our anonymous function immediately

