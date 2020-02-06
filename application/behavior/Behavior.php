<?php
namespace app\behavior;
use think\Controller;
use think\Exception;
use think\Db;
use app\index\model\RbacModel;
class Behavior extends Controller{
    // 定义需要排除的权限路由
    protected $exclude = [

    ];
    /**
     * 权限验证
     */
    public function run(&$params){
        // 行为逻辑
        try {
            // 获取当前访问路由;
            $url  = $this->getActionUrl();
            $Rbac =  new RbacModel();
            $token_exit = $Rbac->getTokenFromHttp();
            $token_exit = trim($token_exit,'"');
            $uid = Db::name('token')->where('TOKEN',$token_exit)->find();
            $user_info = Db::name('user')->where('ID',$uid['USER_ID'])->find();
            if(empty($user_info['ID']) && !in_array($url,$this->exclude)){
                $this->error('请先登录',    '/welcome/user_login');
            }
            $role_id = Db::name('rbac_user_role_relation')->field('ROLE_ID')->where('USER_ID',$uid['USER_ID'])->find();
            $role_id = explode(",", $role_id['ROLE_ID']);
            $limit_id = Db::name('rbac_role')->field('ROLE_IN_LIMIT')->where('ID','IN',$role_id)->select();
            $data = array_column($limit_id,"ROLE_IN_LIMIT");
            $mod = array();
            foreach($data as $key=>$value){
                if(strpos($value,',') != false){
                    $array = explode(',',$value);
                    $array = array_filter($array);
                    $mod = $array+$mod;
                }
            }
            $data  = array(
                "ROLE_IN_LIMIT"=>$mod,
                "NUMROW"=>""
            );
            $auth = Db::name('rbac_limit')->field('URL')->where('PARENT_ID','IN',$data['ROLE_IN_LIMIT'])->select();
            // 用户所拥有的权限路由
            //$auth = Db::name('rbac_limit')->field('URL')->where('ID','in',$limit_id_total['ID'])->select();
            //$auth = array_column($auth, 'URL','NUMROW');
            if(!in_array($url, $auth) && !in_array($url, $this->exclude)) {
                app_send('','400','您没有操作权限，请联系管理员');
            }

        }catch (Exception $ex) {
            //print_r($ex);
        }
    }
    /**
     * 获取当前访问路由
     * @param $Request
     * @return string
     */
    private function getActionUrl()
    {
        $module     = request()->module();
        $controller = request()->controller();
        $action     = request()->action();
        $url        = $module.'/'.$controller.'/'.$action;
        return strtolower($url);
    }
}