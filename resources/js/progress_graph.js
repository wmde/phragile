var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    /**
     * Same as Graph but also renders graph areas under the line.
     * ProgressGraph will be limited to dates <= today.
     * @param {Object[]} data
     * @param {string} cssID - Its CSS identifier (used as class or id)
     * @param {string} label - Description text for the graph which will show in the label that appears when hovering
     * @constructor
     */
    PHRAGILE.ProgressGraph = function (data, cssID, label) {
        data = data.filter(function (d) {
            var $snapshotDate = $('#snapshot-date'),
                filterDate = $snapshotDate.length > 0 ? Date.parse($snapshotDate.text().replace(' ', 'T')) : new Date();

            return d.day <= filterDate;
        });

        PHRAGILE.Graph.call(this, data, cssID, label);

        this.addGraphArea = function () {
            this.plane.append('path')
                .datum(this.data)
                .attr('class', 'graph-area ' + this.cssID)
                .attr('d', d3.svg.area()
                    .x(PHRAGILE.Helpers.xOfDay)
                    .y0(PHRAGILE.coordinateSystem.getY()(0))
                    .y1(PHRAGILE.Helpers.yOfPoints));
        }
    };

    PHRAGILE.ProgressGraph.prototype = new PHRAGILE.Graph;
    PHRAGILE.ProgressGraph.prototype.render = function () {
        PHRAGILE.Graph.prototype.render.call(this);
        this.addGraphArea();
    };
})(PHRAGILE);
