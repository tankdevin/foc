<?php

include('Translator.class.php');

//demo 1
$Google = new GoogleTranslate();
$Google->text = '这是一个基于Google在线翻译的工具';
$Google->from = 'zh-CN';
$Google->to = 'en';
$Google->translate();
echo $Google->result;

//demo 2
$Google = new GoogleTranslate('这是一个基于Google在线翻译的工具', 'zh-CN', 'en');
echo $Google->result;

//demo 3
$Google = new GoogleTranslate();
$Google->translate('这是一个基于Google在线翻译的工具', 'zh-CN', 'en');
echo $Google->result;
?>