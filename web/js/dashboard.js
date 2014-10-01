$(function() {

    jQuery("abbr.timeago").timeago();

    $('[data-repository]').each(refreshRepository);

});

function refreshRepository() {
    var domNode = $(this);
    var repository = domNode.data('repository');
    var isPro = !!domNode.data('pro');
    var token = domNode.data('token');

    var endpoint = isPro ? 'https://api.travis-ci.com' : 'https://api.travis-ci.org';

    var request = $.ajax({
        url: endpoint + '/repos/' + repository + '/branches/master',
        headers: {
            Accept: 'application/vnd.travis-ci.2+json',
            Authorization: 'token ' + token
        }
    });

    request.done(function(data) {
        var text, labelClass;
        switch (data.branch.state) {
            case 'passed':
                text = 'Build success';
                labelClass = 'success';
                break;
            case 'failed':
                text = 'Build failure';
                labelClass = 'danger';
                break;
            case 'errored':
                text = 'Build error';
                labelClass = 'danger';
                break;
            case 'started':
                text = 'Running';
                labelClass = 'info';
                break;
            default:
                console.log('Unknown build status: ' + data.branch.state);
                text = 'Status unknown';
                labelClass = 'warning';
                break;
        }

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
    });

    request.fail(function() {
        domNode.find('.repository-status')
            .addClass('default')
            .find('a').text('No builds on master');
    });
}
