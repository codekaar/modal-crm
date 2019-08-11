<?php

namespace Pho\Crm\Controller;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Auth;
use Pho\Crm\Model\ServiceConversation;
use Pho\Crm\Model\ServiceTicket;
use Pho\Crm\Model\User;
use Pho\Crm\Service\EmailService;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
use Teapot\StatusCode;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class ServiceTicketController
{
    private $auth;
    private $emailService;

    public function __construct(Auth $auth, EmailService $emailService)
    {
        $this->auth = $auth;
        $this->emailService = $emailService;
    }

    # https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
    public static function genuuid()
    {
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        
                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),
        
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,
        
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,
        
                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
    }

    public function create()
    {
        // $to, $subject, $content
        $queryParams = $request->getQueryParams();

        $to = $queryParams['to'];
        $subject = $queryParams['subject'];
        $content = $queryParams['content'];

        $user = $this->auth->getUser();
        $uuid = self::genuuid();
        $by = User::query()->where("email", $to);

        $ticket = new ServiceTicket;
        $ticket->uuid = $uuid;
        $ticket->title = "[ admin initiated ]";
        $ticket->type = ServiceTicket::TYPE_RETENTION; // not 1
        $ticket->by = $by->id;
        $ticket->assignee =  $user->id;
        $ticket->open_date = DB::raw("NOW()");
        $ticket->status = 0;
        $ticket->save();

        $convo = new ServiceConversation;
        $convo->uuid = $uuid;
        $convo->user_id = $user->id;
        $convo->text = $subject."\n\n".$content;
        //$convo->source = ;
        $convo->created_at = DB::raw("NOW()");
        $convo->save();

        return json_encode(["success"=>true]);

    }

    public function ticketList()
    {
        $user = $this->auth->getUser();

        $tickets = ServiceTicket::query()->where("status", 1);


        error_log("Crm Role is: ".$user->crm_role);

        if($user->crm_role > 1)
            $tickets = $tickets->where('by', $user->id)->orWhere('assignee', $user->id);

        $tickets = $tickets->limit(50)
                ->offset(0)
                ->orderBy('open_date', 'desc')
                ->get();

        error_log("ticket count is: ".count($tickets));
        error_log("tickets are: ".print_r($tickets, true));

        return new HtmlResponse(view('tickets.php', [
            'tickets' => $tickets,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
        ]));
    }

    public function conversation($uuid)
    {
        $ticket = ServiceTicket::where('uuid', $uuid)
            ->with([
                'serviceConversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'serviceConversations.user',
                'assigneeUser',
            ])
            ->firstOrFail();
        $by = User::where('id', $ticket->by)
            ->with([
                'instances.site',
            ])
            ->withCount([
                'accessTokens' => function ($query) {
                    $query->whereRaw('created_at > (NOW() - INTERVAL 30 DAY)');
                },
                'serviceConversations',
            ])->first();
        $conversations = $ticket->serviceConversations;

        return new HtmlResponse(view('ticket_conversation.php', [
            'ticket' => $ticket,
            'by' => $by,
            'conversations' => $conversations,
            'ticketStatusToText' => $this->getTicketStatusToText(),
            'ticketTypeToText' => $this->getTicketTypeToText(),
            'cannedResponses' => config('crm.canned_responses'),
        ]));
    }

    public function replyPost($uuid, ServerRequestInterface $request)
    {
        $ticket = ServiceTicket::where('uuid', $uuid)
            ->with([
                'serviceConversations' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'serviceConversations.user',
                'assigneeUser',
                'byUser',
            ])
            ->firstOrFail();
       
      $conversations = $ticket->serviceConversations;

        if ($ticket->status === ServiceTicket::STATUS_CLOSED) {
            return new HtmlResponse(view('ticket_conversation.php', [
                'ticket' => $ticket,
                'conversations' => $conversations,
                'ticketStatusToText' => $this->getTicketStatusToText(),
                'ticketTypeToText' => $this->getTicketTypeToText(),
                'cannedResponses' => config('crm.canned_responses'),
                'fail_message' => 'Ticket already closed',
            ]));
        }

        $body = $request->getParsedBody();

        $validator = new Validator();
        $validation = $validator->validate($body, [
            'text' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            return new HtmlResponse(view('ticket_conversation.php', [
                'ticket' => $ticket,
                'conversations' => $conversations,
                'ticketStatusToText' => $this->getTicketStatusToText(),
                'ticketTypeToText' => $this->getTicketTypeToText(),
                'cannedResponses' => config('crm.canned_responses'),
                'body' => $body,
                'errors' => $errors,
            ]));
        }

        $text = $body['text'];
        $currentUser = $this->auth->getUser();
        $isRepliedByCreator = $ticket->byUser->id === $currentUser->id;
        $now = Carbon::now();

        Manager::connection()->beginTransaction();
        ServiceConversation::create([
            'uuid' => $uuid,
            'user_id' => $this->auth->getUser()->id,
            'text' => $text,
            'source' => ServiceConversation::SOURCE_WEBSITE,
            'created_at' => $now,
        ]);
        if ($isRepliedByCreator) {
            $ticket->status = ServiceTicket::STATUS_OPEN;
            $ticket->save();
        }
        else {
            $ticket->status = ServiceTicket::STATUS_WAITING_RESPONSE;
            if ($ticket->first_response_date === null) {
                $ticket->first_response_date = $now;
            }
            $ticket->save();
        }
        Manager::connection()->commit();

        //if (! $isRepliedByCreator) {
            $this->emailService->sendTicketReplied($ticket->uuid, $ticket->byUser->email, "{$currentUser->first_name} {$currentUser->last_name}", $currentUser->email, $text);
        //}

        return new RedirectResponse(url("service-tickets/{$uuid}"));
    }

    public function getTicketTypeToText()
    {
        return function ($type) {
            $text = '';
            switch ($type) {
                case ServiceTicket::TYPE_SUPPORT:
                    $text = 'Support';
                    break;
            }
            return $text;
        };
    }

    public function getTicketStatusToText()
    {
        return function ($status) {
            $text = '';
            switch ($status) {
                case ServiceTicket::STATUS_OPEN:
                    $text = 'Open';
                    break;
                case ServiceTicket::STATUS_WAITING_RESPONSE:
                    $text = 'Waiting Response';
                    break;
                case ServiceTicket::STATUS_CLOSED:
                    $text = 'Closed';
                    break;
            }
            return $text;
        };
    }

    public function close($uuid, ServerRequestInterface $request, \PDO $pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM `service-tickets` WHERE `uuid` = ?");
        $stmt->execute([ $uuid ]);
        $ticket = $stmt->fetch(\PDO::FETCH_OBJ);

        if (! $ticket) {
            return new HtmlResponse('Ticket Not Found', StatusCode::NOT_FOUND);
        }
        if ($ticket->status == ServiceTicket::STATUS_CLOSED) {
            return new HtmlResponse('Ticket already closed', StatusCode::BAD_REQUEST);
        }


        $stmt = $pdo->prepare("UPDATE `service-tickets` SET `status` = " . ServiceTicket::STATUS_CLOSED . " WHERE `uuid` = ?");
        $stmt->execute([ $uuid ]);

        $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `id` = ?");
        $stmt->execute([ $ticket->by ]);
        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if (! $user) {
            return new HtmlResponse('User Not Found', StatusCode::NOT_FOUND);
        }

        $this->emailService->sendTicketClosed($ticket->uuid, $user->email);

        return new RedirectResponse(url("service-tickets/{$uuid}"));
    }

}
