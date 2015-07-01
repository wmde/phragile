(function () {
    var dayBefore = function (date) {
        var previous = new Date(date);
        previous.setDate(date.getDate() - 1);

        return previous;
    };

    var dayAfter = function (date) {
        var next = new Date(date);
        next.setDate(date.getDate() + 1);

        return next;
    };

    var xOfDay = function (d) { return chartBasis.getX()(d.day); },
        yOfPoints = function (d) { return chartBasis.getY()(d.points);},
        id = function (val) { return val;},
        formatDate = d3.time.format('%Y-%m-%d').parse;

    var chartBasis = (function () {
        var dimensions,
            graphs = {},
            barCharts = {},
            sprintDays,
            maxPoints,

            svg,

            x, y,

            MAX_TICKS = 35;

        var setSVG = function (svgElementID) {
            svg = d3.select(svgElementID)
                .append('svg')
                    .attr('width', dimensions.width + dimensions.margin.left + dimensions.margin.right)
                    .attr('height', dimensions.height + dimensions.margin.top + dimensions.margin.bottom)
                .append('g')
                    .attr('id', 'graphs')
                    .attr('transform', 'translate(' + dimensions.margin.left + ',' + dimensions.margin.top + ')');
        };

        var addAxes = function () {
            var xAxis = d3.svg.axis().scale(x)
                .orient('bottom')
                .ticks(
                    Math.min(sprintDays.length, MAX_TICKS)
                )
                .tickFormat(d3.time.format('%b %e'));

            var yAxis = d3.svg.axis().scale(y)
                .orient('left').ticks(5);

            svg.append('g')
                .attr('class', 'x axis')
                .attr('transform', 'translate(0,' + dimensions.height + ')')
                .call(xAxis)
                .selectAll('text') // aligns the labels on the x-axis for the rotation
                    .style('text-anchor', 'end')
                    .attr('dx', '-.8em')
                    .attr('dy', '.15em');

            svg.append('g')
                .attr('class', 'y axis')
                .call(yAxis);
        };

        var loadLabels = function (index) {
            var $labelsTable = $('#graph-labels tbody');

            for (var name in graphs) {
                $labelsTable.append(graphs[name].getLabelHTML(index));
            }
        }

        var showDataPointsLabel = function (position, index) {
            $('#graph-labels').show().css({
                left: position[0] + 20,
                top: position[1] + 30
            });
            loadLabels(index);
        };

        var resetHoverEffects = function () {
            svg.selectAll('.data-point')
                .attr('class', 'data-point');
            svg.selectAll('.x.axis .tick text')
                .style('font-weight', 'normal');
            $('#graph-labels').hide().find('tbody').html('');
        };

        var bisect = d3.bisector(id).left;

        var highlightDataPoints = function (index) {
            svg.selectAll('.data-point:nth-child(' + (index + 1) + ')')
                .attr('class', 'data-point selected');
            svg.select('.x.axis .tick:nth-child(' + (index + 1) + ') text')
                .style('font-weight', 'bold');
        };

        var highlightAtMouse = function () {
            return function () {
                var mouse = d3.mouse(this),
                    xNearMouse = x.invert(mouse[0] - (dimensions.width / sprintDays.length) / 2),
                    indexAtX = bisect(sprintDays, xNearMouse);

                resetHoverEffects();
                highlightDataPoints(indexAtX, xNearMouse);
                showDataPointsLabel(mouse, indexAtX);
            };
        };

        var addHoverOverlay = function () {
            return svg.append('rect')
                .attr('id', 'burndown-overlay')
                .attr('width', dimensions.width)
                .attr('height', dimensions.height)
                .on('mouseout', resetHoverEffects);
        };

        var addHoverEffects = function () {
            var overlay = addHoverOverlay();

            overlay.on('mousemove', highlightAtMouse());
            overlay.on('mouseout', resetHoverEffects);
        };

        var setDomain = function () {
            x = d3.time.scale().range([0, dimensions.width]);
            y = d3.scale.linear().range([dimensions.height, 0]);

            x.domain(d3.extent(sprintDays, id));
            y.domain([0, maxPoints]);
        };

        var renderGraphs = function () {
            for (var name in graphs) {
                graphs[name].render();
            }
        };

        var renderBarCharts = function () {
            for (var name in barCharts) {
                barCharts[name].render();
            }
        };

        return {
            /**
             * @param {Object} data - A burndownData Object containing information for the graphs
             */
            init: function (days, max) {
                sprintDays = days;
                maxPoints = max;
            },

            addGraphs: function (lineGraphs) {
                graphs = lineGraphs;
            },

            addBarCharts: function (bars) {
                barCharts = bars;
            },

            /**
             * @param {string} id - The ID of the element where the burndown chart will be shown
             * @param {Object} chartDimensions - An object containing height, width, margin.top, margin.right, margin.bottom, margin.left
             */
            render: function (id, chartDimensions) {
                dimensions = chartDimensions;

                setSVG(id);
                setDomain();

                addAxes();
                renderBarCharts();
                renderGraphs();
                addHoverEffects();
            },

            getX: function () {
                return x;
            },

            getY: function () {
                return y;
            }
        };
    })();

    var graphsData = function () {
        var remainingPointsPerDay,
            pointsClosedBeforeSprint,
            sprintData,
            totalPoints;

        var calculateActualProgressData = function (closedPerDay) {
            return [{ // adding another "day" so that the progress of the first day is not hidden
                day: dayBefore(closedPerDay[0].date),
                points: totalPoints - pointsClosedBeforeSprint
            }].concat(closedPerDay.map(function (day) {
                return {
                    day: day.date,
                    points: totalPoints - day.points
                };
            }));
        };

        var isWeekend = function (date) {
            return date.getDay() % 6 === 0;
        };

        var countWeekendDays = function (data) {
            var count = 0;

            data.forEach(function (data, i) {
                if (isWeekend(data.day) && i !== 0) count++;
            });

            return count;
        };

        var prepareData = function (data) {
            return data.map(function (d) {
                d.date = formatDate(d.date);

                return d;
            })
        };

        var calculatePointsClosedPerDay = function (totalPoints, remainingPointsPerDay) {
            var previous = totalPoints;

            return remainingPointsPerDay.map(function (remaining) {
                var closedThatDay = previous - remaining.points;
                previous = remaining.points;

                return {
                    day: remaining.day,
                    points: closedThatDay
                };
            });
        };

        return {
            /**
             * @param {Object} closedPerDate - An object with date strings as its keys and number of closed points as values
             * @param {number} closedBeforeSprint - Number of story points that were closed before the sprint start
             * @param {number} pointsInSprint - Total number of story points in this sprint
             */
            init: function (closedPerDate, closedBeforeSprint) {
                totalPoints = closedPerDate[0].scope;
                pointsClosedBeforeSprint = closedBeforeSprint;
                sprintData = prepareData(closedPerDate);
                remainingPointsPerDay = calculateActualProgressData(sprintData);
            },

            /**
             * @returns {number} Total number of story points in this sprint
             */
            getTotalPoints: function () {
                return totalPoints;
            },

            /**
             * The data represented by the line in the burndown chart.
             * It returns a list of the days of the sprint as well as the number of unclosed points at that day.
             * @returns {Object[]} List of Objects of the form { day: 'yyyy-mm-dd', points: numberOfRemainingPoints }
             */
            getBurndownData: function () {
                return remainingPointsPerDay;
            },

            /**
             * The data represented by bar charts in the diagram.
             * It returns a list of the days of the sprint as well as the number of points closed on that day.
             * @returns {Object[]} List of Objects of the form { day: 'yyyy-mm-dd', points: numberOfClosedPoints }
             */
            getPointsClosedPerDay: function () {
                return calculatePointsClosedPerDay(totalPoints, remainingPointsPerDay);
            },

            /**
             * The data represented by the dashed green line.
             * It contains the days of the sprint as well as the ideal number of points (= total points / (number of days - weekend days)) to be closed on a day.
             * @returns {Object[]}
             */
            getIdealGraphData: function () {
                var averagePointsPerDay = totalPoints / (remainingPointsPerDay.length - countWeekendDays(remainingPointsPerDay) - 1),
                    idealData = [],
                    remaining = totalPoints;

                remainingPointsPerDay.forEach(function (day) {
                    idealData.push({ day: day.day, points: remaining });
                    if (!isWeekend(dayAfter(day.day))) remaining -= averagePointsPerDay;
                });

                return idealData;
            },

            getDaysInSprint: function () {
                return [ // adding another "day" so that the progress of the first day is not hidden
                    dayBefore(sprintData[0].date)
                ].concat(sprintData.map(function (day) {
                    return day.date;
                }));
            },

            getMaxPoints: function () {
                return d3.max(sprintData, function (day) { return day.scope; });
            },

            getScopeLine: function () {
                return [{ // adding another "day" so that the progress of the first day is not hidden
                    day: dayBefore(sprintData[0].date),
                    points: sprintData[0].scope
                }].concat(sprintData.map(function (day) {
                    return {
                        day: day.date,
                        points: day.scope
                    };
                }));
            },

            getBurnupData: function () {
                return [{ // adding another "day" so that the progress of the first day is not hidden
                    day: dayBefore(sprintData[0].date),
                    points: pointsClosedBeforeSprint
                }].concat(sprintData.map(function (day) {
                    return {
                        day: day.date,
                        points: pointsClosedBeforeSprint + day.points
                    };
                }));
            }
        };
    }();

    var Graph = function(data, id, label) {
        this.data = data;
        this.id = id;
        this.label = label;

        this.line = d3.svg.line()
            .x(xOfDay)
            .y(yOfPoints);

        this.addDataPoints = function () {
            this.plane.append('g')
                .attr('id', id + '-data-points')
                .selectAll('.data-point')
                .data(data)
                .enter()
                .append('circle')
                    .attr('class', 'data-point')
                    .attr('r', 4)
                    .attr('cx', xOfDay)
                    .attr('cy', yOfPoints);
        };
    };

    Graph.prototype = {
        constructor: Graph,

        getLabelHTML: function (i) {
            return '<tr class="' + this.id + '">'
                + '<td>' + this.label + '</td>'
                + '<td class="graph-value">' + Math.round(this.data[i].points) + '</td>'
                + '</tr>';
        },

        render: function () {
            this.plane = d3.select('#graphs');

            this.plane.append('path')
                .attr('class', 'graph ' + this.id)
                .attr('d', this.line(this.data));

            this.addDataPoints()
        }
    };

    var BarChart = function (data, id, label) {
        this.data = data;
        this.id = id;
        this.label = label;
    };

    BarChart.prototype = {
        constructor: BarChart,

        render: function () {
            d3.select('#graphs').selectAll(this.id)
                .data(this.data)
                .enter().append('line')
                    .attr('class', 'daily-points')
                    .attr('x1', xOfDay)
                    .attr('y1', chartBasis.getY()(0))
                    .attr('x2', xOfDay)
                    .attr('y2', yOfPoints);
        }
    };

    var $chartData = $('#chart-data');
    graphsData.init(
        $.parseJSON($chartData.text()),
        +$chartData.data('before')
    );

    var burndownGraph = new Graph(graphsData.getBurndownData(), 'actual', 'Remaining '),
        burnupGraph = new Graph(graphsData.getBurnupData(), 'burn-up', 'Completed'),
        graphShadow = function () {
            Graph.prototype.render.call(this);
            this.plane.append('path')
                .datum(this.data)
                .attr('class', 'graph-area')
                .attr('d', d3.svg.area()
                    .x(xOfDay)
                    .y0(chartBasis.getY()(0))
                    .y1(yOfPoints));
        };

    burndownGraph.render = graphShadow;
    burnupGraph.render = graphShadow;

    chartBasis.init(graphsData.getDaysInSprint(), graphsData.getMaxPoints());
    chartBasis.addGraphs({
        burnup: burnupGraph,
        scope: new Graph(graphsData.getScopeLine(), 'scope', 'Scope'),
        burndown: burndownGraph,
        ideal: new Graph(graphsData.getIdealGraphData(), 'ideal', 'Ideal')
    });
    chartBasis.addBarCharts({
        closedPerDay: new BarChart(graphsData.getPointsClosedPerDay(), 'daily-points', 'Closed')
    });
    chartBasis.render(
        '#burndown',
        {
            height: 400,
            width: 600,

            margin: { top: 10, right: 10, bottom: 50, left: 30 }
        }
    );
})();
