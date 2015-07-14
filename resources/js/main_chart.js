var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    var $chartData = $('#chart-data');
    PHRAGILE.chartData.init(
        $.parseJSON($chartData.text()),
        +$chartData.data('before')
    );

    PHRAGILE.coordinateSystem.init(PHRAGILE.chartData.getDaysInSprint(), PHRAGILE.chartData.getMaxPoints());
    PHRAGILE.coordinateSystem.addGraphs({
        burnup: new PHRAGILE.ProgressGraph(PHRAGILE.chartData.getBurnupData(), 'burnup', 'Completed'),
        scope: new PHRAGILE.Graph(PHRAGILE.chartData.getScopeLine(), 'scope', 'Scope'),
        burndown: new PHRAGILE.ProgressGraph(PHRAGILE.chartData.getBurndownData(), 'burndown', 'Remaining '),
        ideal: new PHRAGILE.Graph(PHRAGILE.chartData.getIdealGraphData(), 'ideal', 'Ideal')
    });
    PHRAGILE.coordinateSystem.addBarCharts({
        closedPerDay: new PHRAGILE.BarChart(PHRAGILE.chartData.getPointsClosedPerDay(), 'daily-points', 'Closed')
    });
    PHRAGILE.coordinateSystem.render(
        '#burndown',
        {
            height: 400,
            width: 600,

            margin: { top: 10, right: 10, bottom: 50, left: 30 }
        }
    );
})(PHRAGILE);
