(function () {
    var backlog = new List('backlog', {
            valueNames: ['title', 'points', 'status', 'priority', 'assignee']
        }),
        $resetBtn = $('#reset-filter'),
        currentFilters = {};

    $('.filter-backlog').on('click', function () {
        var value = $(this).data('value'),
            column = $(this).data('column');

        if (currentFilters[column] == value) {
            delete currentFilters[column];
        } else {
            currentFilters[column] = value;
        }

        backlog.filter(function(item) {
            var values = item.values();

            for (var c in currentFilters) {
                if (values[c].trim() != currentFilters[c]) {
                    return false;
                }
            }

            return true;
        });

        $resetBtn.prop('disabled', backlog.items.length === backlog.visibleItems.length);
    });

    $resetBtn.on('click', function () {
        backlog.filter();
        currentFilters = {};
        $resetBtn.prop('disabled', true);
    });
})();
