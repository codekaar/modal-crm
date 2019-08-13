<?php $this->layout('layout/main.php', [ 'title' => 'Service Tickets' ]) ?>

<div class="container">
    <h1>Cluster Situation</h1>
    <table>

    <tr>
    <td><b>Server</b></td>
    <td><b># of Instances</b></td>
    </tr>

    <?php foreach ($instance_usage as $i): ?>

    <tr>
    <td><?= $i['server'] ?></td>
    <td><?= $i['num'] ?></td>
    </tr>

    <?php endforeach ?>

    </table>

    <h1>Tools</h1>

    <div>
    <ul>
    <li>Recreate a Heroku instance only (both for Grou.ps and GraphJS) [needs email, uuid, user_id, dbid] -- for instances where we have user and instance entries in the db but no heroku</li>
    <li>Programmatic Signup (exact same as web ui signup)</li>
    <li>Create a new GraphJS instance from scratch (both database instance and Heroku instance) [requires a user_id only]</li>
    </ul>
    </div>
</div>
