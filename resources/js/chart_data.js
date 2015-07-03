var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    PHRAGILE.chartData = function () {
        var remainingPointsPerDay,
            pointsClosedBeforeSprint,
            sprintData,
            totalPoints;

        var calculateActualProgressData = function (closedPerDay) {
            return [{ // adding another "day" so that the progress of the first day is not hidden
                day: PHRAGILE.Helpers.dayBefore(closedPerDay[0].date),
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
                d.date = PHRAGILE.Helpers.formatDate(d.date);

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
                    if (!isWeekend(PHRAGILE.Helpers.dayAfter(day.day))) remaining -= averagePointsPerDay;
                });

                return idealData;
            },

            /**
             * @returns {Date[]}
             */
            getDaysInSprint: function () {
                return [ // adding another "day" so that the progress of the first day is not hidden
                    PHRAGILE.Helpers.dayBefore(sprintData[0].date)
                ].concat(sprintData.map(function (day) {
                        return day.date;
                    }));
            },

            /**
             * @returns {int} - Maximal total number of points in the sprint
             */
            getMaxPoints: function () {
                return d3.max(sprintData, function (day) { return day.scope; });
            },

            /**
             * @returns {Object[]} - Returns line chart data for the scope line
             */
            getScopeLine: function () {
                return [{ // adding another "day" so that the progress of the first day is not hidden
                    day: PHRAGILE.Helpers.dayBefore(sprintData[0].date),
                    points: sprintData[0].scope
                }].concat(sprintData.map(function (day) {
                        return {
                            day: day.date,
                            points: day.scope
                        };
                    }));
            },

            /**
             * @returns {Object[]} - Returns line chart data for the burnup chart
             */
            getBurnupData: function () {
                return [{ // adding another "day" so that the progress of the first day is not hidden
                    day: PHRAGILE.Helpers.dayBefore(sprintData[0].date),
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
})(PHRAGILE);
