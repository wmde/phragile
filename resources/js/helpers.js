var PHRAGILE = PHRAGILE || {};

(function (PHRAGILE) {
    PHRAGILE.Helpers = {
        dayBefore: function (date) {
            var previous = new Date(date);
            previous.setDate(date.getDate() - 1);

            return previous;
        },

        dayAfter: function (date) {
            var next = new Date(date);
            next.setDate(date.getDate() + 1);

            return next;
        },

        xOfDay: function (d) { return PHRAGILE.coordinateSystem.getX()(d.day); },

        yOfPoints: function (d) { return PHRAGILE.coordinateSystem.getY()(d.points);},

        formatDate: d3.time.format('%Y-%m-%d').parse
    }
})(PHRAGILE);
