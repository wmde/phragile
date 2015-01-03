var width = 250,
    height = 250,
    radius = width / 2;

var svg = d3.select('#pie').append('svg')
    .attr('width', width)
    .attr('height', height)
    .append('g')
        .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

var arc = d3.svg.arc()
    .outerRadius(radius - 10)
    .innerRadius(0);

var pie = d3.layout.pie()
    .value(function (d) { return d.points; });

var statusData = function () {
    var data = [],
        statusData = $.parseJSON($('#status-data').text());

    for (var status in statusData) {
        data.push({ status: status, points: statusData[status].points });
    }

    return data;
};

var g = svg.selectAll('.arc')
    .data(pie(statusData()))
    .enter().append('g')
        .attr('class', 'arc');

g.append('path')
    .attr('d', arc)
    .attr('class', function (d) { return 'status ' + d.data.status; });

g.append("text")
    .attr("transform", function(d) { return "translate(" + arc.centroid(d) + ")"; })
    .attr("dy", ".35em")
    .attr('class', 'status-name')
    .style("text-anchor", "middle")
    .text(function(d) { return d.data.status; });
