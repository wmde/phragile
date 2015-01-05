var options = {
    valueNames: ['title', 'points', 'status', 'priority']
};

var backlog = new List('backlog', options);

$('.filter-backlog').on('click', function () {
    backlog.search($(this).data('value'), [$(this).data('column')]);
});
