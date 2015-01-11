var statusData = function () {
    var data = [],
        statusData = $.parseJSON($('#status-data').text());

    for (var status in statusData) {
        data.push({ status: status, points: statusData[status].points });
    }

    return data;
};

var statusPieChart = (function () {
    var width, height, radius,

        svg,
        arc;

    var setSVG = function (id) {
        svg = d3.select(id).append('svg')
            .attr('width', width)
            .attr('height', height)
            .append('g')
            .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');
    };

    var addPie = function () {
        var pie = d3.layout.pie()
            .value(function (d) { return d.points; });

        svg.selectAll('.arc')
            .data(pie(statusData()))
            .enter().append('g')
            .attr('class', 'arc')
            .append('path')
                .attr('d', arc)
                .attr('class', function (d) { return 'status ' + d.data.status; });
    };

    var setArc = function () {
        arc = d3.svg.arc()
            .outerRadius(radius - 10)
            .innerRadius(0);
    };

    var addArcLables = function () {
        svg.selectAll('.arc').append('text')
            .attr('transform', function(d) { return 'translate(' + arc.centroid(d) + ')'; })
            .attr('dy', '.35em')
            .attr('class', 'status-name')
            .style('text-anchor', 'middle')
            .text(function(d) { return d.data.status; });
    };

    return {
        init: function () {

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
            addArcLables();
        }
    };
})();

statusPieChart.init(statusData());
statusPieChart.render('#pie', 250, 300);
