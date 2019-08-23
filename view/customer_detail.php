<?php $this->layout('layout/main.php', [ 'title' => 'Lead Detail' ]) ?>

<div class="container">
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


    <h1>Customer Detail</h1>

    <div><button href="#" class="btn-email btn btn-link float-right" data-email="${user.email}">Email</button></div>

    <div>
        Name: <?= $this->e("$user->first_name $user->last_name") ?>
    </div>
    <div>
        Email: <?= $this->e("$user->email") ?>
    </div>
    <div>
        Signed up On: <?= $this->e("$user->signup_timestamp") ?>
    </div>
    <div>
        UUID:  <?=  $this->e($user->instances->first()->uuid) ?>
    </div>
    <div>
                    Grou.ps Name: <?= $this->e($user->instances->first()->groups_name) ?>
                </div>
                <div>
                    Grou.ps Title: <?= $this->e($user->instances->first()->groups_title) ?>
                </div>
    <div>
        Website: <?= $this->e($user->instances->first()->site->url) ?>
    </div>
    <div>
                    Admin Password: <?= 
                    
                    sprintf(
                        "%s%s", 
                        substr(
                            md5(
                                password_hash(
                                    (string) $user->id, 
                                    PASSWORD_BCRYPT, 
                                    ["salt"=>"nuno gomes is a great soccer player"]
                                )
                            )
                        , 3, 6), 
                        (string) $user->id
                    )
                    
                    ?>
                </div>
                <div>
                    Recreate Url: <a href="<?= $this->e(
                    
                    "https://gr.ps/generate?" . http_build_query([
                        "name" => $user->instances->first()->groups_name,
                        "title" => $user->instances->first()->groups_title,
                        "public_id" =>$user->instances->first()->uuid,
                        "theme"=>$user->instances->first()->theme,
                        "text_color"=>($user->instances->first()->theme == "dark" ) ? "white" :  'rgb(63, 95, 127)',
                        "background_color"=> ($user->instances->first()->theme == "dark" ) ? 'rgb(71, 71, 71)' :  "white",
                        "primary_color"=>$user->instances->first()->color,
                        "host"=>"https://accounts.groups2.com",
                        "secret"=>md5($user->instances->first()->groups_name.":burasi mustur yolu yokustur"),
                        "regen"=>1,
                        "git"=> "http://".$user->instances->first()->groups_name.":".substr(md5(password_hash($user->id, PASSWORD_BCRYPT, ["salt"=>"nuno gomes is a great soccer player"])), 3, 6) . (string) $user->id."@165.22.133.69/".$user->instances->first()->groups_name."/frontend",
                        "description"=>$user->instances->first()->description,
                        "module_forum"=>0,
                        "module_groups"=>0
                
                    ]))
                    
                    ?>">Click</a>
                </div>
    <div>
        # of email conversation: <?= $user->service_conversations_count ?>
    </div>
    <div>
        Site Health Score:
    </div>
    <div>
        # of times logged in the last week: <?= $user->access_tokens_count ?>
    </div>
    <div>
        Last Log-in: <?= $this->e("$user->last_login_timestamp") ?>
    </div>
</div>


<script>

$('.btn-email').on('click', function (ev) {
                    ev.preventDefault();
                    $('#email-modal').modal();
                    $('#to-email').val($( this ).attr('data-email'))
                });

                </script>