var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    /**
     * Objects containing data for line charts which can be rendered.
     * @param {Object[]} data
     * @param {string} cssID - Its CSS identifier (used as class or id)
     * @param {string} label - Description text for the graph which will show in the label that appears when hovering
     * @constructor
     */
    PHRAGILE.Graph = function(data, cssID, label) {
        this.data = data;
        this.cssID = cssID;
        this.label = label;

        this.line = d3.svg.line()
            .x(PHRAGILE.Helpers.xOfDay)
            .y(PHRAGILE.Helpers.yOfPoints);

        this.addDataPoints = function () {
            this.plane.append('g')
                .attr('id', cssID + '-data-points')
                .selectAll('.data-point')
                .data(data)
                .enter()
                .append('circle')
                .attr('class', 'data-point')
                .attr('r', 4)
                .attr('cx', PHRAGILE.Helpers.xOfDay)
                .attr('cy', PHRAGILE.Helpers.yOfPoints);
        };
    };

    PHRAGILE.Graph.prototype = {
        constructor: PHRAGILE.Graph,

        /**
         * @param i - The data list index that the mouse is hovering
         * @returns {string} - The label HTML
         */
        getLabelHTML: function (i) {
            return '<tr class="' + this.cssID + '"'
                + ' style="display: ' + ($('.graph.' + this.cssID).css('display') === 'none' ? 'none' : '') + '">'
                + '<td>' + this.label + '</td>'
                + '<td class="graph-value">'
                + Math.round(this.data[Math.min(this.data.length - 1, i)].points)
                + '</td>'
                + '</tr>';
        },

        /**
         * Renders the graph on the chart
         */
        render: function () {
            this.plane = d3.select('#graphs');

            this.plane.append('path')
                .attr('class', 'graph ' + this.cssID)
                .attr('d', this.line(this.data));

            this.addDataPoints()
        }
    };
})(PHRAGILE);
