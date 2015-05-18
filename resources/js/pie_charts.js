(function () {
    var statusData = function () {
        var data = [],
            statusData = $.parseJSON($('#status-data').text());

        for (var status in statusData) {
            data.push({ status: status, points: statusData[status].points, cssClass: statusData[status].cssClass });
        }

        return data;
    };

    var statusPieChart = (function () {
        var width, height, radius,

            svg,
            arc,

            pieData;

        var setSVG = function (id) {
            svg = d3.select(id).append('svg')
                .attr('width', width)
                .attr('height', height)
                .append('g')
                .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');
        };

        var addPie = function () {
            var pie = d3.layout.pie()
                .value(function (d) { return d.points; })
                .sort(function (a, b) { return d3.ascending(a.cssClass, b.cssClass); });

            svg.selectAll('.arc')
                .data(pie(pieData))
                .enter().append('g')
                .attr('class', 'arc')
                .append('path')
                    .attr('d', arc)
                    .attr('class', function (d) { return 'status filter-backlog ' + d.data.cssClass; })
                    .attr('data-column', 'status')
                    .attr('data-value', function (d) { return d.data.status; });
        };

        var setArc = function () {
            arc = d3.svg.arc()
                .outerRadius(radius - 10)
                .innerRadius(0);
        };

        var showTooltipOnMouseOver = function () {
            svg.selectAll('.status')
                .on('mousemove', function (d) {
                    svg.select('#label-tooltip')
                        .text(d.data.status)
                        .style('display', 'block')
                        .attr('x', d3.mouse(this)[0] + 10)
                        .attr('y', d3.mouse(this)[1] + 10);
                })
                .on('mouseout', function () {
                    svg.select('#label-tooltip').style('display', 'none');
                });
        };

        var addLabelToolTip = function () {
            svg.append('text')
                .attr('id', 'label-tooltip')
                .style('display', 'none');

            showTooltipOnMouseOver();
        };

        return {
            /**
             * @param {Object[]} data - List of objects containing status name and number of points for the respective status.
             */
            init: function (data) {
                pieData = data;
            },

            /**
             * @param id - ID of the element containing the pie chart
             * @param width - width of the pie chart area
             * @param height - height of the pie chart area
             */
            render: function (id, pieWidth, pieHeight) {
                width = pieWidth;
                height = pieHeight;
                radius = width / 2;

                setSVG(id);
                setArc();

                addPie();
                addLabelToolTip();
            }
        };
    })();

    statusPieChart.init(statusData());
    statusPieChart.render('#pie', 250, 250);
})();
