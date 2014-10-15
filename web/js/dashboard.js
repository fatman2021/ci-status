var currentSort = 'status';
var reverseSort = false;
var totalBuilds = 0;
var buildsFailing = 0;

$(function() {
    $('#refresh-repositories').click(refreshRepositories);

    $('#sort-status').click(function (e) {
        e.preventDefault();
        reverseSort = (currentSort === 'status') ? !reverseSort : false;
        currentSort = 'status';
        sortTable()
    });
    $('#sort-recent').click(function (e) {
        e.preventDefault();
        reverseSort = (currentSort === 'recent') ? !reverseSort : false;
        currentSort = 'recent';
        sortTable()
    });
    $('#sort-name').click(function (e) {
        e.preventDefault();
        reverseSort = (currentSort === 'name') ? !reverseSort : false;
        currentSort = 'name';
        sortTable()
    });

    refreshRepositories();
});

function sortTable()
{
    switch (currentSort) {
        case 'recent':
            $('#dashboard tr').tsort('', {
                data: 'date',
                order: !reverseSort ? 'desc' : 'asc'
            });
            break;
        case 'name':
            $('#dashboard tr').tsort('', {
                data: 'repository',
                order: !reverseSort ? 'asc' : 'desc'
            });
            break;
        case 'status':
        default:
            $('#dashboard tr').tsort('', {
                data: 'status',
                order: !reverseSort ? 'asc' : 'desc'
            }, '', {
                data: 'date',
                order: !reverseSort ? 'desc' : 'asc'
            });
            break;
    }
}

function refreshSummary()
{
    var summary = $('#summary');

    summary.find('.failing').text(buildsFailing);
    summary.find('.total').text(totalBuilds);

    if (buildsFailing > 0) {
        summary.removeClass('alert-build-success')
            .addClass('alert-build-fail');
    } else {
        summary.removeClass('alert-build-fail')
            .addClass('alert-build-success');
    }
    summary.removeClass('hide');
}

function refreshRepositories()
{
    // Clear
    var dashboard = $('#dashboard');
    dashboard.find('.repository-status')
        .removeClass('danger warning success info')
        .find('a')
        .html('<i class="fa fa-spinner fa-spin"></i>');
    dashboard.find('.author')
        .tooltip('destroy')
        .empty();
    dashboard.find('.build-time')
        .tooltip('destroy')
        .empty();
    totalBuilds = 0;
    buildsFailing = 0;

    $('[data-repository]').each(refreshRepository);
}

function refreshRepository()
{
    var domNode = $(this);
    var repository = domNode.data('repository');
    var isPro = !!domNode.data('pro');
    var token = domNode.data('token');

    var endpoint = isPro ? 'https://api.travis-ci.com' : 'https://api.travis-ci.org';

    domNode.data('status', 999);
    domNode.data('date', '1970-01-01T00:00:00');

    var request = $.ajax({
        url: endpoint + '/repos/' + repository + '/branches/master',
        headers: {
            Accept: 'application/vnd.travis-ci.2+json',
            Authorization: 'token ' + token
        }
    });

    request.done(function(data) {
        var text, labelClass, order;

        totalBuilds++;

        switch (data.branch.state) {
            case 'errored':
                text = 'Build error';
                labelClass = 'danger';
                order = 1;
                buildsFailing++;
                break;
            case 'failed':
                text = 'Build failure';
                labelClass = 'danger';
                order = 2;
                buildsFailing++;
                break;
            case 'started':
                text = 'Running';
                labelClass = 'info';
                order = 3;
                break;
            case 'created':
                text = 'Waiting';
                labelClass = 'info';
                order = 4;
                break;
            case 'passed':
                text = 'Build success';
                labelClass = 'success';
                order = 5;
                break;
            case 'canceled':
                text = 'Canceled';
                labelClass = 'default';
                order = 6;
                break;
            default:
                console.log('Unknown build status: ' + data.branch.state);
                text = 'Status unknown';
                labelClass = 'warning';
                order = 7;
                break;
        }

        // Sort data
        var startedAt = (!!data.branch.started_at) ? data.branch.started_at : data.commit.committed_at;
        domNode.data('status', order);
        domNode.data('date', startedAt);

        // Status
        domNode.find('.repository-status')
            .addClass(labelClass)
            .find('a')
                .text(text);

        // Author
        var commitUrl = data.commit.compare_url;
        var commitTime = $.timeago(data.commit.committed_at);
        domNode.find('.author')
            .tooltip({ title: commitTime + ': ' + data.commit.message, placement: 'bottom' })
            .html('<a href="' + commitUrl + '"><i class="fa fa-user"></i> ' + data.commit.author_name + '</a>');

        // Build date
        domNode.find('.build-time')
            .html('<i class="fa fa-clock-o"></i> ' + $.timeago(startedAt));

        if (!! data.branch.finished_at) {
            // Build duration
            var started = new Date(data.branch.started_at);
            var finished = new Date(data.branch.finished_at);
            var duration = Math.round((finished.getTime() - started.getTime()) / 60000);
            domNode.find('.build-time')
                .tooltip({title: 'Build ran for ' + duration + ' minute(s)', placement: 'bottom'});
        }

        sortTable();
        refreshSummary();
    });

    request.fail(function() {
        domNode.find('.repository-status')
            .addClass('default')
            .find('a').text('No builds on master');

        sortTable();
    });
}
