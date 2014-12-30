var burndownDataToList = function (burndownData) {
    var days = [];

    for (var day in burndownData) {
        days.push({
            day: d3.time.format('%Y-%m-%d').parse(day),
            points: burndownData[day]
        });
    }

    return days;
};

var $burndownData = $('#burndown-data'),
    totalPoints = $burndownData.data('total'),
    closedBefore = $burndownData.data('before'),
    closedPerDay = burndownDataToList($.parseJSON($burndownData.text()));

var dayBefore = function (date) {
    var previous = new Date(date);
    previous.setDate(previous.getDate() - 1);

    return previous;
};

var sprintData = function () {
    var remaining = totalPoints - closedBefore;

    return closedPerDay.map(function (day) {
            remaining -= day.points;

            return {
                day: day.day,
                points: remaining + day.points // adding the points again so the progress will not show for the previous day
            };
        });
};

var isWeekend = function (date) {
    return date.getDay() % 6 === 0;
};

var countWeekendDays = function (data) {
    var count = 0;

    data.forEach(function (data) {
        if (isWeekend(data.day)) count++;
    });

    return count;
};

var calculateIdealGraph = function (data) {
    var averagePointsPerDay = totalPoints / (data.length - countWeekendDays(data) - 1),
        idealData = [],
        remaining = totalPoints;

    data.forEach(function (day) {
        idealData.push({ day: day.day, points: remaining });
        if (!isWeekend(day.day)) remaining -= averagePointsPerDay;
    });

    return idealData;
};

var actualGraphData = sprintData(),
    idealGraphData = calculateIdealGraph(actualGraphData),

    margin = { top: 10, right: 10, bottom: 50, left: 30 },
    width = 600 - margin.left - margin.right,
    height = 400 - margin.top - margin.bottom,

    x = d3.time.scale().range([0, width]),
    y = d3.scale.linear().range([height, 0]),

    xOfDay = function (d) { return x(d.day); },
    yOfPoints = function (d) { return y(d.points); },

    xAxis = d3.svg.axis().scale(x)
                .orient('bottom')
                .ticks(actualGraphData.length)
                    .tickFormat(d3.time.format('%b %e')),
    yAxis = d3.svg.axis().scale(y)
                .orient('left').ticks(5),

    line = d3.svg.line()
                .x(xOfDay)
                .y(yOfPoints),

    svg = d3.select('#burndown')
            .append('svg')
                .attr('width', width + margin.left + margin.right)
                .attr('height', height + margin.top + margin.bottom)
            .append('g')
                .attr('transform', 'translate(' + margin.left + ',' + margin.top + ')');

x.domain(d3.extent(actualGraphData, function (d) { return d.day; }));
y.domain([0, totalPoints]);

svg.append('path')
    .attr('class', 'graph ideal')
    .attr('d', line(idealGraphData));

svg.append('path')
    .attr('class', 'graph actual')
    .attr('d', line(actualGraphData.filter(function (data) {
        return data.day <= new Date();
    })));

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
