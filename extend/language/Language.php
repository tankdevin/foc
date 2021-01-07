<?php
namespace Language;
if (!defined('LANGUAGE_ROOT')) {
    define('LANGUAGE_ROOT', dirname(__FILE__) . '/');
}

/****
 * 存放在TP5的extend目录
 * 语言类使用方法，在对应的控制器 $language = new \language\Language('zh-cn','exchange');
 * 其他的开发框架相同
 * @author 陈奎宗
 *
 */
class Language {
    
    /**语言包目录*/
    private $local;    
    /**缺省值语言包，如果不存在则引用对应的包数据，主要防止包更新了之后，其他语言的未更新上，或者是变量名不符的情况下，出现数据的丢失问题**/
    private $default_local = 'zh-cn';
    /**对应的模块名*/
    private $module;
    /**获取的数据集*/
    private $data = array();
    /**如果获取的数据结果不存在的时候，临时存储的默认数据结果*/
    private $default_data = array();
    /**该参数默认为真，即如果对应的语言包不存在的时候，默认使用系统语言包*/
    private $null_get_default;

    /*****
     * 语言扩展的初始化数据
     * @param string $local 必填参数，否则使用简体中文
     * @param string $module 必填参数，否则不知道使用哪一个语言模块包
     */
    public function __construct($local = 'zh-cn',$module = '',$null_get_default = true) {
        $this->local = $local;
        $this->module = $module;
        $this->data = $this->load($local);
        $this->null_get_default = $null_get_default;
    }


    /****
     * 独立获取一个语言信息对应下标数组信息
     * @param String $key
     */
    public function get($key) {
        //如果存在当前语言的数据
        $arr = $this->getInArray($this->data, $key);        
        if($arr==false){
            //是否空值使用默认语言包
            if($this->null_get_default==true){
                //如果引用语言包的数据不存在，检测是否存在默认语言的下标
                $this->default_data = $this->load($this->default_local); 
                //直接返回，如果不存在为false
                return $this->getInArray($this->default_data, $key); 
            } else {
                return false;
            }
        } else {
            //返回对应语言数据
            return $arr; 
        }
    }
    
    /****
     * get获取下标的时候，可能传输不需要太多的下标，进行多维度数组字段匹配，注：检测的数组维度最大2维
     * @param unknown $data
     * @param unknown $key
     */
    private function getInArray($data,$key){
        if(is_array($data)){  //如果数据是数据
            if(isset($data[$key])){ //如果数据存在对应键名
                return $data[$key]; //直接返回
            }
            //否则循环数组
            foreach ($data as $k => $v){
                //如果循环的参数为数组
                if(is_array($v)){
                    //如果存在2维数组对应的下标数组或参数
                    if(isset($v[$key])){
                        return $v[$key];
                    }
                }
            }
        }
        return false;
    }



    /****
     * 获取对应模块的全部语言数组信息
     * @return 返回对应语言包模块的全部语言数据或者是默认的语言数据，如果需要返回默认语言包数据的时候，null_get_default必须为true
     */
    public function all() {
        if(!empty($this->data)){
            return $this->data;
        } else {
            if($this->null_get_default==true){
                if(!empty($this->default_data)){
                    return $this->default_data;
                }
                return $this->load($this->default_local);
            } else {
                return false;
            }
        }
    }
    

    /****
     * 加载语言包，需要一个本地化参数
     * @param String $local
     * @return multitype:
     */
    private function load($local) {
        $_ = array(); 
        $file = LANGUAGE_ROOT . $local . '/' . $this->module . '.php';
        if (is_file($file)) {
            require($file);
        }
        $data = array();
        return array_merge($data, $_);
    }
}