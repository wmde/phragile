var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    /**
     * Objects that contain data for bars that will be rendered at the bottom of the chart
     * @param {Object[]} data
     * @param {string} cssID - Its CSS identifier (used as class or id)
     * @param {string} label - Description text for the graph which will show in the label that appears when hovering
     * @constructor
     */
    PHRAGILE.BarChart = function (data, cssID, label) {
        this.data = data;
        this.cssID = cssID;
        this.label = label;
    };

    PHRAGILE.BarChart.prototype = {
        constructor: PHRAGILE.BarChart,

        /**
         * @param i - The data list index that the mouse is hovering
         * @returns {string} - The label HTML
         */
        getLabelHTML: function (i) {
            return '<tr class="' + this.cssID + '"'
                + ' style="display: ' + ($('.bar.' + this.cssID).css('display') === 'none' ? 'none' : '') + '">'
                + '<td>' + this.label + '</td>'
                + '<td class="graph-value">'
                + Math.round(this.data[Math.min(this.data.length - 1, i)].points)
                + '</td>'
                + '</tr>';
        },

        /**
         * Renders the bar charts inside the burndown/burnup chart
         */
        render: function () {
            d3.select('#graphs').selectAll(this.cssID)
                .data(this.data)
                .enter().append('line')
                .attr('class', 'bar ' + this.cssID)
                .attr('x1', PHRAGILE.Helpers.xOfDay)
                .attr('y1', PHRAGILE.coordinateSystem.getY()(0))
                .attr('x2', PHRAGILE.Helpers.xOfDay)
                .attr('y2', PHRAGILE.Helpers.yOfPoints);
        }
    };
})(PHRAGILE);
