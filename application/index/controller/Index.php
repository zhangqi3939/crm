<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Index extends Controller
{
    //登录页
    public function index()
    {
        return view('signin');
    }
    //执行登录
    public function login()
    {
        $userName = input('post.userName');
        $userPassword = input('post.userPassword');
        $channel = input('post.channel');
        empty($channel) && $channel = 'web';
        $where = array( 'USER_NAME'=>$userName, 'STATUS'=>0);
        $User =  new UserModel();
        $userExit  = $User->userList($where);
        if(!empty($userExit) && count($userExit)>0){
            if($userExit['USER_PASSWORD']  == $userPassword){
                if($channel == 'app'){
                    $uuid = input('post.uuid');
                    $token = $User->newToken($userExit["ID"],$channel,$uuid);
                }else{
                    $token = $User->newToken($userExit["ID"],$channel);
                }
                if(!empty($token)){
                    $userdata = array(
                        'UID' => $userExit["ID"],
                        'USER_ID' => $userExit["ID"],
                        'USER_NAME' =>$userExit["USER_NAME"],
                        'REAL_NAME' => $userExit["REAL_NAME"],
                        'AGENT_ID' => $userExit["AGENT_ID"],
                        'CLIENT_ID' => $userExit["CLIENT_ID"],
                        'USER_LEVEL' => $userExit["USER_LEVEL"],
                        'IS_ADMIN' => $userExit["IS_ADMIN"],
                        'TEL'=> $userExit['TEL'],
                        'TOKEN'=> $token
                    );
                    $User->saveLoginLog($userdata['UID']);
                    app_send(arraykeyToLower($userdata));
                }else{
                    app_send('','400','token error.');
                }
            }else{
                app_send('','400','password error1!');
                exit();
            }
        }else{
            app_send('','400','user error!');
            exit();
        }
    }
    //退出登录
    public function user_login_out()
    {
        $User =  new UserModel();
        $channel = 'web';
        $result = $User->deleteToken($channel);
        if($result>0){
            app_send();
        }else{
            app_send('','400','退出失败，请联系管理员');
        }
    }
    //首页
    public function show_index()
    {
        return view('index');
    }
    //404
    public function show_404()
    {
        return view('404');
    }
    //主分支页
    public function show_master()
    {
        return view('master');
    }
}