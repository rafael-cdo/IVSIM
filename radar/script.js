var w = 500,
	h = 500;

var colorscale = d3.scale.category10();

//Legend titles
var LegendOptions = ['Ideas','Paper'];

//Data
var d = [
		  [
			{axis:"Email",value:200},
			{axis:"Social Networks",value:50},
			{axis:"Internet Banking",value:30},
			{axis:"News Sportsites",value:12},
			{axis:"Search Engine",value:19},
			{axis:"View Shopping sites",value:50},
			{axis:"Paying Online",value:100},
			{axis:"Buy Online",value:150},
			{axis:"Stream Music",value:155},
			{axis:"Online Gaming",value:69},
			{axis:"Navigation",value:75},
			{axis:"App connected to TV program",value:13},
			{axis:"Offline Gaming",value:12},
			{axis:"Photo Video",value:4},
			{axis:"Reading",value:3},
			{axis:"Listen Music",value:22},
			{axis:"Watch TV",value:3},
			{axis:"TV Movies Streaming",value:33},
			{axis:"Listen Radio",value:27},
			{axis:"Sending Money",value:18},
			{axis:"Other",value:17},
			{axis:"Use less Once week",value:18}
		  ],[
			{axis:"Internet Banking",value:211},
			{axis:"News Sportsites",value:28},
			{axis:"Search Engine",value:146},
			{axis:"View Shopping sites",value:129},
			{axis:"Paying Online",value:11},
			{axis:"Buy Online",value:74},
			{axis:"Stream Music",value:85},
			{axis:"Online Gaming",value:119},
			{axis:"Navigation",value:14},
			{axis:"App connected to TV program",value:60},
			{axis:"Offline Gaming",value:24},
			{axis:"Photo Video",value:17},
			{axis:"Reading",value:15},
			{axis:"Listen Music",value:52},
			{axis:"Watch TV",value:11},
			{axis:"TV Movies Streaming",value:14},
			{axis:"Listen Radio",value:6},
			{axis:"Sending Money",value:16},
			{axis:"Other",value:7},
			{axis:"Use less Once week",value:17}
		  ]
		];

//Options for the Radar chart, other than default
var mycfg = {
  w: w,
  h: h,
  maxValue: 0.6,
  levels: 6,
  ExtraWidthX: 300
}

//Call function to draw the Radar chart
//Will expect that data is in %'s
RadarChart.draw("#chart", d, mycfg);

////////////////////////////////////////////
/////////// Initiate legend ////////////////
////////////////////////////////////////////

var svg = d3.select('#body')
	.selectAll('svg')
	.append('svg')
	.attr("width", w+300)
	.attr("height", h)

//Create the title for the legend
var text = svg.append("text")
	.attr("class", "title")
	.attr('transform', 'translate(90,0)') 
	.attr("x", w - 70)
	.attr("y", 10)
	.attr("font-size", "12px")
	.attr("fill", "#404040")
	.text("Frequencia dos termos em ideias e papers");
		
//Initiate Legend	
var legend = svg.append("g")
	.attr("class", "legend")
	.attr("height", 100)
	.attr("width", 200)
	.attr('transform', 'translate(90,20)') 
	;
	//Create colour squares
	legend.selectAll('rect')
	  .data(LegendOptions)
	  .enter()
	  .append("rect")
	  .attr("x", w - 65)
	  .attr("y", function(d, i){ return i * 20;})
	  .attr("width", 10)
	  .attr("height", 10)
	  .style("fill", function(d, i){ return colorscale(i);})
	  ;
	//Create text next to squares
	legend.selectAll('text')
	  .data(LegendOptions)
	  .enter()
	  .append("text")
	  .attr("x", w - 52)
	  .attr("y", function(d, i){ return i * 20 + 9;})
	  .attr("font-size", "11px")
	  .attr("fill", "#737373")
	  .text(function(d) { return d; })
	  ;	