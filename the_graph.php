<div id='placeholder' style="width:450px;height:300px;"></div>
<div id="tool"></div>
<script>
		
	(function ($) {
		console.log('something in the log');
		    var v1 = { 
									label: "Visitors",
									data: <?php echo json_encode($graph_visitors); ?>
								 };
				var v2 = {
									color: "blue",
									hoverable: true,
									label: "Total Visits",
									data: <?php echo json_encode($graph_visits); ?>
								 };

		    // a null signifies separate line segments
				var options = {
					grid: { hoverable:true },
					points: { show: true },
					lines: { show: true, width: '10px' },
					legend: { show: true, margin: 10, backgroundOpacity: 0.5},
					xaxis: {
						mode: "time",
						timeformat: "%m/%d"
					}
				};

		    $.plot($('#placeholder'), [v1,v2], options);
		
				$("#placeholder").bind("plothover", function (event, pos, item) {
			        console.log("You clicked at ", item);
							// var xCoord = (pos.pageX + 50) + "px";
							// 							var yCoord = (pos.pageY + 50) + "px";
							// 							$(this).html('Total: ' + item.datapoint[1]);
							// 							$(this).css({top:yCoord, left:xCoord});
			        // axis coordinates for other axes, if present, are in pos.x2, pos.x3, ...
			        // if you need global screen coordinates, they are pos.pageX, pos.pageY

			       
			    });
		
		//$('#placeholder').text('this loaded');
		})(jQuery);
</script>
<p>LOADED THIS!</p>