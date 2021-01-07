<?php

namespace led;

use think\Facade\Cache;

class Led
{
    static $key;
    private $sno;
//    private $device = '118C014769';
    private $device;
    private $domain = 'http://api.popled.cn';
    private $username = '15837147095';
    private $password = 'cs880222';
    static $instance;
    public $all_devices = [];

    public function __construct($device)
    {
        $this->login();
        $this->device = $device;
        $this->all_devices = $this->devices('online');
        if($this->all_devices['status'] != 'ok'){
            throw new \member\CommonException('网络错误,请稍后重试!');
        }
        if(!in_array($this->device,$this->all_devices['devdata'])){
             throw new \member\CommonException('请检查设备是否打开');
        }
//        $this->barrage();
//        $this->img();
//        $this->img('aaa.jpg');
//        $this->text();
//        $this->image();
//        $this->gif();

    }


    public static function getInstance($device){
        if(!self::$instance instanceof self){
            self::$instance = new self($device);
        }
        return self::$instance;
    }

    /**
     * @desc get devices
     * @param string $type all | online
     */
    public function devices($type = 'all')
    {
        $res = $this->http('/api/get_dev', [
            'type' => $type
        ]);
        return $res;
    }

    public function status()
    {
        $res = $this->http('/api/state', [
//            'devid' => "",
//            'sno' => "11377256",
            'sno' => "{$this->device}",
        ]);
        return $res;
    }

    public function text()
    {
        $data = [
            'pkts_program' => [
                'id_pro' => 1,
                'property_pro' => [
                    'width' => 64,
                    'height' => 64,
                ],
                'list_region' => [
                    [
                        'list_item' => [
                            [
                                "type_item" => "text",
                                "color" => 255,
                                "font" => "宋体",
                                "text" => "你看这灯多亮!",
                                "align_horizontal" => "left",
                            ]
                        ]
                    ]
                ]
            ],
            'ids_dev' => $this->device,
        ];
        $this->send($data);
    }

    public function barrage()
    {
        $data = [
            'pkts_program' => [
                'id_pro' => 4,
                'property_pro' => [
                    'width' => 64,
                    'height' => 64,
                    'type_pro' => 0,
                    'play_loop' => 1,
                    'time_sync' => 10,
                    'type_color' => 1,
                    'send_gif_src' => 1,
                ],
                'list_region' => [
                    [
                        'info_pos' => [
                            'x' => 0,
                            'y' => 0,
                            'w' => 64,
                            'h' => 64,
                        ],
                        'list_item' => [
                            [
                                'type_item' => 'text_pic',
                                'text' => '周杰伦',
                                'font' => '黑体',
                                'size' => 50,
                                'color' => 16711935,
                                'rotate' => 0,
                                'bold' => 0,
                                'italic' => 0,
                                'align_horizontal' => 'center',
                                'align_vertical' => 'center',
                                'info_animate' => [
                                    'model_continue' => 'left',
                                    'model_normal' => 0x02,
                                    'speed' => 14,
                                ],
                                'info_border' => [
                                    'type' => 'fixed',
                                    'fixed_value' => 0x01
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'ids_dev' => $this->device
        ];
        $this->send($data);
    }

    public function img()
    {
        $data = [
            'pkts_program' => [
                'id_pro' => 21,
                'property_pro' => [
                    'width' => 64,
                    'height' => 64,
                    'type_color' => 3,
                    'gray' => 4,
                    'type_pro' => 0,
                    'play_loop' => 1,
                    'send_gif_src' => 1,
                ],
                'list_region' => [
                    [
                        'info_pos' => [
                            'x' => 0,
                            'y' => 0,
                            'w' => 64,
                            'h' => 64,
                        ],
                        'list_item' => [
                            [
                                'info_animate' => [
                                    'model_normal' => 1,
                                    'speed' => 10,
                                    'time_stay' => 3,
                                ],
                                'type_item' => 'graphic',
                                'zip_bmp' => base64_encode(ToolScreen::zipBinary(file_get_contents(ToolScreen::image_resize('md.png', 'm1.bmp')))),
                            ]
                        ]
                    ]
                ]
            ],
            'ids_dev' => $this->device
        ];
        $this->send($data);
    }

    public function gif()
    {
        $data = [
            'pkts_program' => [
                'id_pro' => 21,
                'property_pro' => [
                    'width' => 64,
                    'height' => 64,
                    'type_color' => 3,
                    'type_pro' => 0,
                    'gray' => 4,
                    'play_loop' => 1,
                    'send_gif_src' => 1,
                ],
                'list_region' => [
                    [
                        'info_pos' => [
                            'x' => 0,
                            'y' => 0,
                            'w' => 64,
                            'h' => 64,
                        ],
                        'list_item' => [
                            [
                                'type_item' => 'graphic',
                                'isGif' => 1,
                                'gamma' => 1.6,
                                'zip_gif' => base64_encode(ToolScreen::zipBinary(file_get_contents('heart.gif'))),

                            ]
                        ]
                    ]
                ]
            ],
            'ids_dev' => $this->device
        ];
        $this->send($data);
    }

    public function clean()
    {
        $data = [
            'cmd' => [
                'delete' => [
                    'del_all' => 1
                ]
            ],
            'ids_dev' => $this->device
        ];
        $this->send($data, 'cmd');
        return $this;
    }

    public function upload()
    {
        move_uploaded_file($_FILES['file']['tmp_name'], 'upload.jpg');
        ToolScreen::image_resize('upload.jpg', 'upload.bmp');
        die(json_encode([
            'code' => 1,
            'data' => 'upload.bmp'
        ]));
    }

    private function login()
    {

        if(Cache::get('LedToken')){
            static::$key = Cache::pull('LedToken');
        }else{
            $data = [
                'user' => $this->username,
                'pwd' => md5($this->password),
            ];
            $res = $this->http('/api/login', $data);
            Cache::set('LedToken',$res['key']);
            static::$key = $res['key'];
        }
    }

    private function send($data = [], $type = 'pkts_program')
    {
        $res = $this->http('/api/send', [
            'type' => $type,
            'sendmsg' => json_encode($data)
        ]);
        $this->sno = $res['sno'];
        print_r($res);
    }

    private function sign($data)
    {
        $basic = [
            'ts' => time(),
            'user' => $this->username
        ];
        $data = array_merge($basic, $data);
        ksort($data);
        $str = $this->my_http_build_query($data) . "&key=".self::$key;
        $data['token'] = strtoupper(md5($str));
        return $data;
    }

    private function http($url, $data = [])
    {
        $data = $this->sign($data);

        $headers = [
            'Content-Type: application/json; charset=utf-8',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->domain . $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $data = curl_exec($ch);
        curl_close($ch);
        return json_decode($data, true);
    }

    private function my_http_build_query($data)
    {
        $string = '';
        foreach ($data as $k => $v) {
            $string .= "$k=$v&";
        }
        return rtrim($string, '&');
    }
}
////调用实例
//$obj = new \led\Led('118C014769');
//$obj->clean()->text();

