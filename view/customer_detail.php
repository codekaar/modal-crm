<?php $this->layout('layout/main.php', [ 'title' => 'Lead Detail' ]) ?>

<div class="container">
    <h1>Customer Detail</h1>

    <div>
        Name: <?= $this->e("$user->first_name $user->last_name") ?>
    </div>
    <div>
        Email: <?= $this->e("$user->email") ?>
    </div>
    <div>
        UUID: $this->e($by->instances->first()->uuid) ?>
    </div>
    <div>
                    Grou.ps Name: <?= $this->e($by->instances->first()->groups_name) ?>
                </div>
                <div>
                    Grou.ps Title: <?= $this->e($by->instances->first()->groups_title) ?>
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
                                    (string) $by->id, 
                                    PASSWORD_BCRYPT, 
                                    ["salt"=>"nuno gomes is a great soccer player"]
                                )
                            )
                        , 3, 6), 
                        (string) $by->id
                    )
                    
                    ?>
                </div>
                <div>
                    Recreate Url: <?= 
                    
                    "https://gr.ps/generate?" . http_build_query([
                        "name" => $by->instances->first()->groups_name,
                        "title" => $by->instances->first()->groups_title,
                        "public_id" =>$by->instances->first()->uuid,
                        "theme"=>$by->instances->first()->theme,
                        "text_color"=>($by->instances->first()->theme == "dark" ) ? "white" :  'rgb(63, 95, 127)',
                        "background_color"=> ($by->instances->first()->theme == "dark" ) ? 'rgb(71, 71, 71)' :  "white",
                        "primary_color"=>$by->instances->first()->color,
                        "host"=>"https://accounts.groups2.com",
                        "secret"=>md5($by->instances->first()->groups_name.":burasi mustur yolu yokustur"),
                        "regen"=>1,
                        "git"=> "http://".$by->instances->first()->groups_name.":".substr(md5(password_hash($by->id, PASSWORD_BCRYPT, ["salt"=>"nuno gomes is a great soccer player"])), 3, 6) . (string) $by->id."@165.22.133.69/".$by->instances->first()->groups_name."/frontend",
                        "description"=>$by->instances->first()->description,
                        "module_forum"=>0,
                        "module_groups"=>0
                
                    ])
                    
                    ?>
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
</div>
