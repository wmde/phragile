var margin = { top: 30, right: 30, bottom: 50, left: 30 },
    width = 600 - margin.left - margin.right,
    height = 400 - margin.top - margin.bottom,

    parseDate = d3.time.format('%Y-%m-%d').parse,

    x = d3.time.scale().range([0, width]),
    y = d3.scale.linear().range([height, 0]),

    xAxis = d3.svg.axis().scale(x)
                .orient('bottom')
                .ticks(10)
                    .tickFormat(d3.time.format('%b %e')),
    yAxis = d3.svg.axis().scale(y)
                .orient('left').ticks(5),

    line = d3.svg.line()
                .x(function (d) { return x(d.day); })
                .y(function (d) { return y(d.points); }),

    svg = d3.select('#burndown')
            .append('svg')
                .attr('width', width + margin.left + margin.right)
                .attr('height', height + margin.top + margin.bottom)
            .append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

var prepareData = function () {
    var data = [],
        $burndownData = $('#burndown-data'),
        days = $.parseJSON($burndownData.text()),
        remaining = $burndownData.data('total') - days.before;

    for (var day in days) {
        data.push({
            day: parseDate(day),
            points: remaining
        });

        remaining -= days[day];
    }

    data.pop(); data.pop(); // TODO: figure out what to do with `before` and `after`.

    return data;
};

var data = prepareData();

x.domain(d3.extent(data, function (d) { return d.day; }));
y.domain([0, d3.max(data, function (d) { return d.points; })]);

svg.append('path')
    .attr('class', 'graph')
    .attr('d', line(data));

svg.append('g')
    .attr('class', 'x axis')
    .attr('transform', 'translate(0,' + height + ')')
    .call(xAxis)
    .selectAll("text") // aligns the labels on the x-axis for the rotation
        .style("text-anchor", "end")
        .attr("dx", "-.8em")
        .attr("dy", ".15em");

svg.append('g')
    .attr('class', 'y axis')
    .call(yAxis);
