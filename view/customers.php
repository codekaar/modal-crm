<?php $this->layout('layout/main.php', [ 'title' => 'Leads' ]) ?>

<div class="container">
    <h1>Customers</h1>

    <div class="form-group">
        <form id="form">
            <input placeholder="Search by name or website" id="search" class="form-control">
        </form>
    </div>

    <table class="table table-bordered mt">
        <thead>
            <tr>
                <th>Name</th>
                <th>Website</th>
                <th>
                    Sign Up Date
                </th>
                <th class="sort-header" data-sort="email_count">
                    <div class="d-flex align-items-center">
                        <span class="flex-fill">Convo #</span>
                        <span class="sort-icon fa fa-sort"></span>
                    </div>
                </th>
                <th class="sort-header" data-sort="analytics_count">
                    <div class="d-flex align-items-center">
                        <span class="flex-fill">Health</span>
                        <span class="sort-icon fa fa-sort"></span>
                    </div>
                </th>
                <th class="sort-header" data-sort="login_count">
                    <div class="d-flex align-items-center">
                        <span class="flex-fill">Login #</span>
                        <span class="sort-icon fa fa-sort"></span>
                    </div>
                </th>
                <th>
                    Last Login
                </th>
            </tr>
        </thead>
        <tbody id="users"></tbody>
    </table>
    <div></div>

</div>

<div id="email-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Email</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <input type="hidden" id="to-email" />
                    <div class="form-group">
                        <label class="col-form-label">Title</label>
                        <input type="text" class="form-control" id="email-subject">
                    </div>
                    <div class="form-group">
                        <label class="col-form-label">Content</label>
                        <textarea class="form-control"  id="email-content"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="email-button">Send</button>
            </div>
        </div>
    </div>
</div>
<script src="/js/timeago.js"></script>
<?php $this->start('scripts') ?>

<script>
    
(function ($, window) {
    'use strict';

    var customerType = <?= json_encode($customerType) ?>;
    var baseUrl = <?= json_encode(url()) ?>;
    var $search = $('#search');
    var $form = $('#form');
    var $emailButton = $("#email-button")
    var currentPage = 1;
    var lastPage = null;
    var sortBy = {
        email_count: null,
        login_count: null,
        analytics_count: null,
    };

    $emailButton.on("click", function(ev) {
        $.post("/mailgun-messages", {
            "sender": $("#to-email").val(),
            "subject": $("#email-subject").val(),
            "body-plain": $("#email-content").val()
        }).done(alert("Sent"));
    });

    $form.submit(function (ev) {
        ev.preventDefault();
        loadData({ append: false, page: 1 });
    });

    $('.sort-header').each(function () {
        var $header = $(this);
        var sortKey = $header.data('sort');
        var $sortIcon = $header.find('.sort-icon');
        $header.on('click', function (ev) {
            ev.preventDefault();
            var newSort;
            switch (sortBy[sortKey]) {
                case null:
                    newSort = 'asc';
                    $sortIcon.removeClass('fa-sort').addClass('fa-sort-down');
                    break;
                case 'asc':
                    newSort = 'desc';
                    $sortIcon.removeClass('fa-sort-down').addClass('fa-sort-up');
                    break;
                case 'desc':
                    newSort = null;
                    $sortIcon.removeClass('fa-sort-up').addClass('fa-sort');
                    break;
            }
            sortBy[sortKey] = newSort;
            loadData({ append: false });
        });
    });

    function loadData({ append = true, page = null } = {}) {

        var search = $search.val();

        currentPage = page ? page : currentPage;

        var sort = {};
        if (sortBy['email_count']) {
            sort.email_count = sortBy['email_count'];
        }
        if (sortBy['login_count']) {
            sort.login_count = sortBy['login_count'];
        }
        if (sortBy['analytics_count']) {
            sort.analytics_count = sortBy['analytics_count'];
        }

        var queryParams = {
            type: customerType,
            search: search,
            page: currentPage,
            limit: 20,
            sort: sort,
        };
        $.get(<?= json_encode(url('ajax/customers')) ?> + '?' + $.param(queryParams))
        .then(function (res) {
            if (! lastPage) {
                lastPage = res.last_page;
            }
            var users = res.data;
            var $content = users.map(user => {
                var $tr = $(`
<tr>
    <td>
        <button href="#" class="btn-email btn btn-link float-right" data-email="${user.email}"><span class="fas fa-envelope"></span></button>
        <a href="${baseUrl}/customers/${user.id}">${escapeHtml((user.email || ''))}</a>
    </td>
    <td><a href="${escapeHtml((user.instances[0] && user.instances[0].site) ? user.instances[0].site.url : '')}${escapeHtml((user.instances[0] && user.instances[0].groups_name) ? user.instances[0].groups_name : '')}" target="_blank">${escapeHtml((user.instances[0] && user.instances[0].site) ? user.instances[0].site.url : '')}${escapeHtml((user.instances[0] && user.instances[0].groups_name) ? user.instances[0].groups_name : '')}</a></td>
    <td>${timeSince((new Date(user.signup_timestamp)).getTime())}</td>
    <td>${user.service_conversations_count}</td>
    <td>${user.analytics_count}</td>
    <td>${user.access_tokens_count}</td>
    <td>${timeSince((new Date(user.last_login_timestamp)).getTime())}</td>
</tr>
`);
                $tr.find('.btn-email').on('click', function (ev) {
                    ev.preventDefault();
                    $('#email-modal').modal();
                    $('#to-email').val($( this ).attr('data-email'))
                });
                return $tr;
            });
            if (append) {
                $('#users').append($content);
            }
            else {
                $('#users').html($content);
            }

            // Load more content if not reached bottom of the page
            if (hasNextPage()
                && ($('html').height() - $(window).scrollTop() < $(window).height())) {
                currentPage++;
                loadData();
            }
        }).fail(function (err) {
            console.log(err);
            alert('Error occurred');
        });
    }
    loadData({append: true});

    function hasNextPage() {
        return lastPage && currentPage < lastPage;
    }

    $(window).scroll(function () {
        if( $(window).scrollTop() == $(document).height() - $(window).height() ) {
            if (hasNextPage()) {
                currentPage++;
                loadData();
            }
        }
    });

})(jQuery, window);

</script>

<p>Please note, Login # is last week only</p>


<?php $this->end('scripts') ?>
