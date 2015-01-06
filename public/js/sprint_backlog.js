var options = {
    valueNames: ['title', 'points', 'status', 'priority']
};

var backlog = new List('backlog', options),
    $resetBtn = $('#reset-filter');

$('.filter-backlog').on('click', function () {
    backlog.search($(this).data('value'), [$(this).data('column')]);
    $resetBtn.prop('disabled', backlog.items.length === backlog.visibleItems.length);
});

$resetBtn.on('click', function () {
    backlog.search();
    $resetBtn.prop('disabled', true);
});
