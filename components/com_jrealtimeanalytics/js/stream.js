(function(a){var b=function(){var j=this;var g=null;var e=false;var f=2000;var d=2;var c=0;var i=0;var h=null;this.showDebugMsgs=function(l,k){if(e){if(!a("div#jrealtime_msg").length){a("<div/>").attr("id","jrealtime_msg").prependTo("body").append('<div id="jrealtime_msgtitle">'+l+"</div>").append('<div id="jrealtime_msgtext">'+k+"</div>").css("margin-top",0).animate({"margin-top":"-150px"},300,"linear")}}};this.dispatch=function(m){var k=jrealtimeBaseURI+"index.php?option=com_jrealtimeanalytics&format=json";var l={};l.task="stream.display";l.nowpage=a(location).attr("href");l.initialize=m;l.module_available=parseInt(a("#jes_mod").length);a.ajax({url:k,data:l,type:"post",cache:false,dataType:"json",success:function(o,q,p){c++;if(o){if(o.configparams){d=o.configparams.daemonrefresh;f=o.configparams.daemonrefresh*1000;e=!!parseInt(o.configparams.enable_debug);if(typeof(o.configparams.daemontimeout)!=="undefined"){i=o.configparams.daemontimeout*60;h=parseInt(i/d)}}if(o.storing&&o.storing.length){a.each(o.storing,function(r,s){j.showDebugMsgs(s.corefile,s.details)})}else{var n=true;if(i){if(c>h){n=false}}if(n){setTimeout(function(){j.dispatch()},f)}}if(o.loading&&o.loading.length){a.each(o.loading,function(r,s){j.showDebugMsgs(s.corefile,s.details)})}else{if(o["data-bind"]){a.each(o["data-bind"],function(r,s){a("#jes_mod span.badge[data-bind="+r+"]").text(s)})}}if(typeof(JRealtimeHeatmap)!=="undefined"&&!g){g=new JRealtimeHeatmap(o.configparams,j);g.startListening()}}},error:function(o,p,n){text=COM_JREALTIME_NETWORK_ERROR+n;j.showDebugMsgs("Client side stream",text)}})}};window.JRealtimeStream=b;a(function(){var c=new JRealtimeStream();c.dispatch(true)})})(jQuery);