<?php 
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
$this->layout('layout/main.php', [ 'title' => 'Service Tickets' ]) 
?>

<div class="container">
    <h1>Service Tickets</h1>

    <table class="table table-bordered mt">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>By</th>
                <!--<th>Assignee</th>-->
                <th>Open date</th>
                <!--<th>Close date</th>-->
                <th>Status</th>
                <th>Feedback</th>
            </tr>
        </thead>
            <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><a href="<?= url('service-tickets/' . $ticket->uuid) ?>"><?= $ticket->uuid ?></a></td>
                    <td><?= $this->e($ticket->title) ?></td>
                    <td><?= $ticketTypeToText($ticket->type) ?></td>
                    <td><a href="mailto:<?= $this->e("{$ticket->byUser->email}") ?>"># <?= $this->e("{$ticket->byUser->first_name} {$ticket->byUser->last_name}") ?></a></td>
                    <!--<td><?= $ticket->assigneeUser ? $this->e("{$ticket->assigneeUser->first_name} {$ticket->assigneeUser->last_name}") : '' ?></td>-->
                    <td><?= time_elapsed_string($ticket->open_date) ?></td>
                    <!--<td><?= $ticket->close_date ?></td>-->
                    <td><?= $ticketStatusToText($ticket->status) ?></td>
                    <td><?= $ticket->feedback ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
    </table>
</div>
