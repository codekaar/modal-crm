<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Auth;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ToolsController
{
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function html()
    {
        $user = $this->auth->getUser();
        /*
                 $dbHost = config('database.host');
        $dbName = config('database.database');
        $username = config('database.username');
        $password = config('database.password');
        $conn = new \PDO("mysql:host=$dbHost;dbname=$dbName", $username, $password);
        */

        \DB::$user = config('database.username');
        \DB::$password = config('database.password');
        \DB::$dbName = config('database.database');
        \DB::$host = config('database.host');

        $instance_usage = \DB::queryRaw("SELECT count(*) as `num`, `server` FROM instances group by `server`");
        echo "Joe's password is: " . $joePassword . "\n";
        

/*
        $tickets = ServiceTicket::query();

        $tickets = $tickets->where('by', $user->id)->orWhere('assignee', $user->id)
            ->limit(20)
            ->offset(0)
            ->orderBy('open_date', 'desc')
            ->get();
            */

        return new HtmlResponse(view('tools.php', [
            'instance_usage' => $instance_usage
            //'tickets' => $tickets,
            //'ticketStatusToText' => $this->getTicketStatusToText(),
            //'ticketTypeToText' => $this->getTicketTypeToText(),
        ]));
    }

}
