var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    PHRAGILE.coordinateSystem = (function () {
        var dimensions,
            graphs = {},
            barCharts = {},
            sprintDays,
            maxPoints,

            svg,

            x, y,

            tickDateFormat = d3.time.format('%b %e'),
            tickClassFormat = d3.time.format('%b-%d'),
            MAX_TICKS = 35;

        var id = function (val) { return val; };

        var setSVG = function (svgElementID) {
            svg = d3.select(svgElementID)
                .append('svg')
                .attr('width', dimensions.width + dimensions.margin.left + dimensions.margin.right)
                .attr('height', dimensions.height + dimensions.margin.top + dimensions.margin.bottom)
                .append('g')
                .attr('id', 'graphs')
                .attr('transform', 'translate(' + dimensions.margin.left + ',' + dimensions.margin.top + ')');
        };

        var addTicksClass = function () {
            svg.selectAll('.x.axis .tick')
                .attr('class', function(d) {
                    return 'tick ' + tickClassFormat(d);
                });
        };

        var addAxes = function () {
            var xAxis = d3.svg.axis().scale(x)
                .orient('bottom')
                .ticks(
                Math.min(sprintDays.length, MAX_TICKS)
            )
                .tickFormat(tickDateFormat);

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

            addTicksClass();
        };

        var loadLabels = function (index) {
            var $labelsTable = $('#graph-labels tbody');

            for (var name in graphs) {
                $labelsTable.append(graphs[name].getLabelHTML(index));
            }

            for (var name in barCharts) {
                $labelsTable.append(barCharts[name].getLabelHTML(index));
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

        var highlightDataPoints = function (index, x) {
            svg.selectAll('.data-point:nth-child(' + (index + 1) + ')')
                .attr('class', 'data-point selected');
            svg.select(
                '.x.axis .tick.'
                + tickClassFormat(PHRAGILE.Helpers.dayAfter(x))
                + ' text'
            ).style('font-weight', 'bold');
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
             * @param {Date[]} days - List of days in the sprint
             * @param {int} max - The maximal total number of story points in the sprint
             */
            init: function (days, max) {
                sprintDays = days;
                maxPoints = max;
            },

            /**
             * @param {Graph[]} lineGraphs - Graphs to be rendered in the chart
             */
            addGraphs: function (lineGraphs) {
                graphs = lineGraphs;
            },

            /**
             * @param {BarChart[]} bars - BarCharts to be displayed on the bottom of the chart
             */
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

            /**
             * @returns Returns the d3 function which translates a date to a point in the chart
             */
            getX: function () {
                return x;
            },

            /**
             * @returns Returns the d3 function which translates a number of story points to a point in the chart
             */
            getY: function () {
                return y;
            }
        };
    })();
})(PHRAGILE);
