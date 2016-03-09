<?php
namespace Seeker\Protocol\Base;

class Setting
{
    public static function eof()
    {
        return [
            'open_length_check'     => true,
            'package_length_type'   => 'n',
            'package_length_offset' => 0,       //第N个字节是包长度的值
            'package_body_offset'   => 24,       //第几个字节开始计算长度
            'package_max_length'    => 1024 * 8  //协议最大长度 8K
        ];
    } 
}