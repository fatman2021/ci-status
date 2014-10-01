$(function() {

    jQuery("abbr.timeago").timeago();

    $('[data-repository]').each(refreshRepository);

    sortTable();

});

function sortTable()
{
    $('#dashboard tr').tsort('', {
        data: 'status'
    }, '', {
        data: 'date',
        order: 'desc'
    });
}

function refreshRepository()
{
    var domNode = $(this);
    var repository = domNode.data('repository');
    var isPro = !!domNode.data('pro');
    var token = domNode.data('token');

    var endpoint = isPro ? 'https://api.travis-ci.com' : 'https://api.travis-ci.org';

    domNode.data('status', 999);

    var request = $.ajax({
        url: endpoint + '/repos/' + repository + '/branches/master',
        headers: {
            Accept: 'application/vnd.travis-ci.2+json',
            Authorization: 'token ' + token
        }
    });

    request.done(function(data) {
        var text, labelClass, order;
        switch (data.branch.state) {
            case 'errored':
                text = 'Build error';
                labelClass = 'danger';
                order = 1;
                break;
            case 'failed':
                text = 'Build failure';
                labelClass = 'danger';
                order = 2;
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
            default:
                console.log('Unknown build status: ' + data.branch.state);
                text = 'Status unknown';
                labelClass = 'warning';
                order = 6;
                break;
        }

        // Sort data
        domNode.data('status', order);
        domNode.data('date', data.branch.finished_at);

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

        if (data.branch.duration != null) {
            // Time taken to build
            var duration = Math.round(data.branch.duration / 60);
            domNode.find('.build-duration')
                .tooltip({ title: 'Build duration: ' + duration + ' minute(s)', placement: 'bottom' })
                .html('<i class="fa fa-clock-o"></i> ' + duration + 'min');
        }

        sortTable();
    });

    request.fail(function() {
        domNode.find('.repository-status')
            .addClass('default')
            .find('a').text('No builds on master');

        sortTable();
    });
}
