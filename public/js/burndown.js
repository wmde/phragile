(function () {
    var dayAfter = function (date) {
        var next = new Date(date);
        next.setDate(date.getDate() + 1);

        return next;
    };

    var burndownChart = (function () {
        var sprintData,
            dimensions,

            svg,

            x, y;

        var setSVG = function (svgElementID) {
            svg = d3.select(svgElementID)
                .append('svg')
                .attr('width', dimensions.width + dimensions.margin.left + dimensions.margin.right)
                .attr('height', dimensions.height + dimensions.margin.top + dimensions.margin.bottom)
                .append('g')
                .attr('transform', 'translate(' + dimensions.margin.left + ',' + dimensions.margin.top + ')');
        };

        var addAxes = function () {
            var xAxis = d3.svg.axis().scale(x)
                .orient('bottom')
                .ticks(sprintData.getBurndownData().length)
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

        var addActualProgressLine = function () {
            var pastSprintDays = sprintData.getBurndownData().filter(function (data) {
                return data.day <= dayAfter(new Date());
            });

            svg.append('path')
                .attr('class', 'graph actual')
                .attr('d', line(pastSprintDays));

            svg.append('path')
                .datum(pastSprintDays)
                .attr('class', 'graph-area')
                .attr('d', d3.svg.area()
                    .x(xOfDay)
                    .y0(y(0))
                    .y1(yOfPoints));

            addDataPoints('actual-data-points', pastSprintDays);
            addHoverEffects(pastSprintDays);
        };

        var addIdealProgressLine = function () {
            var graphData = sprintData.getIdealGraphData();

            svg.append('path')
                .attr('class', 'graph ideal')
                .attr('d', line(graphData));

            addDataPoints('ideal-data-points', graphData);
        };

        var xOfDay = function (d) { return x(d.day); },
            yOfPoints = function (d) { return y(d.points); };

        var line = d3.svg.line()
            .x(xOfDay)
            .y(yOfPoints);

        var addClosedTasksPerDayBars = function () {
            svg.selectAll('.daily-points')
                .data(sprintData.getPointsClosedPerDay())
                .enter().append('line')
                    .attr('class', 'daily-points')
                    .attr('x1', xOfDay)
                    .attr('y1', y(0))
                    .attr('x2', xOfDay)
                    .attr('y2', yOfPoints);
        };

        var addDataPoints = function (id, graphData) {
            svg.append('g')
                .attr('id', id)
                .selectAll('.data-point')
                    .data(graphData)
                    .enter()
                    .append('circle')
                        .attr('class', 'data-point')
                        .attr('r', 4)
                        .attr('cx', xOfDay)
                        .attr('cy', yOfPoints);
        };

        var resetHoverEffects = function () {
            svg.selectAll('.data-point')
                .attr('class', 'data-point');
            $('#graph-labels').hide();
        };

        var addHoverOverlay = function () {
            return svg.append('rect')
                .attr('id', 'burndown-overlay')
                .attr('width', dimensions.width)
                .attr('height', dimensions.height)
                .on('mouseout', resetHoverEffects);
        };

        var addHoverEffects = function () {
            var bisect = d3.bisector(function(d) { return d.day; }).left,
                idealGraphData = sprintData.getIdealGraphData(),
                actualGraphData = sprintData.getBurndownData(),
                overlay = addHoverOverlay();

            overlay.on('mousemove', function () {
                var mouse = d3.mouse(this),
                    xNearMouse = x.invert(mouse[0] - (dimensions.width / actualGraphData.length) / 2),
                    indexOfDate = bisect(idealGraphData, xNearMouse);

                resetHoverEffects();

                $('#graph-labels').show().css({
                    left: mouse[0] + 20,
                    top: mouse[1] + 30
                });
                $('#ideal-progress').text(Math.round(idealGraphData[indexOfDate].points));
                $('#actual-progress').text(actualGraphData[indexOfDate].points);
                svg.selectAll('.data-point:nth-child(' + (indexOfDate + 1) + ')')
                    .attr('class', 'data-point selected');
            });

            svg.on('mouseout', resetHoverEffects);
        };

        var setDomain = function () {
            x = d3.time.scale().range([0, dimensions.width]);
            y = d3.scale.linear().range([dimensions.height, 0]);

            x.domain(d3.extent(sprintData.getBurndownData(), function (d) { return d.day; }));
            y.domain([0, sprintData.getTotalPoints()]);
        };

        return {
            /**
             * @param {Object} data - A burndownData Object containing information for the graphs
             */
            init: function (data) {
                sprintData = data;
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
                addIdealProgressLine();
                addActualProgressLine();
                addClosedTasksPerDayBars();
            }
        };
    })();

    var burndownData = function () {
        var pointsClosedPerDay,
            remainingPointsPerDay,
            pointsClosedBeforeSprint,
            totalPoints;

        var dataToList = function (dataObject) {
            var days = [];

            for (var date in dataObject) {
                days.push({
                    day: d3.time.format('%Y-%m-%d').parse(date),
                    points: dataObject[date]
                });
            }

            return days;
        };

        var calculateActualProgressData = function (closedPerDay) {
            var remaining = totalPoints - pointsClosedBeforeSprint;

            return closedPerDay.map(function (day) {
                remaining -= day.points;

                return {
                    day: day.day,
                    points: remaining + day.points // adding the points again so the progress will not show for the previous day
                };
            }).concat([{ // adding another "day" so that the progress of the last day is not hidden
                day: dayAfter(closedPerDay[closedPerDay.length - 1].day),
                points: remaining
            }]);
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

        var calculateIdealGraph = function (actualProgress) {
            var averagePointsPerDay = totalPoints / (actualProgress.length - countWeekendDays(actualProgress) - 1),
                idealData = [],
                remaining = totalPoints;

            actualProgress.forEach(function (day) {
                idealData.push({ day: day.day, points: remaining });
                if (!isWeekend(day.day)) remaining -= averagePointsPerDay;
            });

            return idealData;
        };

        return {
            /**
             * @param {Object} closedPerDate - An object with date strings as its keys and number of closed points as values
             * @param {number} closedBeforeSprint - Number of story points that were closed before the sprint start
             * @param {number} pointsInSprint - Total number of story points in this sprint
             */
            init: function (closedPerDate, closedBeforeSprint, pointsInSprint) {
                totalPoints = pointsInSprint;
                pointsClosedBeforeSprint = closedBeforeSprint;
                pointsClosedPerDay = dataToList(closedPerDate);
                remainingPointsPerDay = calculateActualProgressData(pointsClosedPerDay);
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
                return pointsClosedPerDay;
            },

            /**
             * The data represented by the dashed green line.
             * It contains the days of the sprint as well as the ideal number of points (= total points / (number of days - weekend days)) to be closed on a day.
             * @returns {Object[]}
             */
            getIdealGraphData: function () {
                return calculateIdealGraph(remainingPointsPerDay);
            }
        };
    }();

    var $burndownData = $('#burndown-data');

    burndownData.init(
        $.parseJSON($burndownData.text()),
        +$burndownData.data('before'),
        +$burndownData.data('total')
    );

    burndownChart.init(burndownData);
    burndownChart.render(
        '#burndown',
        {
            height: 400,
            width: 600,

            margin: { top: 10, right: 10, bottom: 50, left: 30 }
        }
    );
})();
