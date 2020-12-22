<?php
/**
 * Markdown 离线阅读工具
 *
 * 执行：php markdown_offline_reading.php [markdown_file]
 *
 * 例子1：将指定Markdown文件离线
 *       php markdown_offline_reading.php ./Examples
 * 例子2：将指定目录下的Markdown文件全部离线
 *       php markdown_offline_reading.php ./Examples/example.md
 *
 * User: mengxinxin
 * Date: 2020/12/21
 * Time: 18:41
 */

// 修正时区
date_default_timezone_set('PRC');

// 解析命令行参数
$markdown_file = $argv[1];  // 待转换文件/文件夹

// 执行离线操作
$res = offline_markdown_file_in_dir($markdown_file);
echo "$res\n\n\n";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// 离线文件夹中的markdown文件
function offline_markdown_file_in_dir($markdown_file_dir) {
  // 判断是否存在
  if (!file_exists($markdown_file_dir)) {
      return "文件不存在：$markdown_file_dir";
  }

  // 单个文件：直接离线
  if(!is_dir($markdown_file_dir)) {
    return offline_markdown_file($markdown_file_dir);
  }

  // 文件夹：遍历、离线
  $markdown_files = scandir($markdown_file_dir);
  foreach($markdown_files as $markdown_file) {
      
    // 判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
    if($markdown_file=="." || $markdown_file==".." || $markdown_file==".DS_Store") {
      continue;
    }

    // 判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
    if(end(explode('.', $markdown_file_dir))=="assets") {
      continue;
    }

    // 执行离线
    offline_markdown_file_in_dir("$markdown_file_dir/$markdown_file");
    
  }
  return "";
}

// 离线单个markdown文件
function offline_markdown_file($markdown_file) {
  // 解析文件属性
  $markdown_file_name = basename($markdown_file);
  $markdown_file_dir = dirname($markdown_file);  
  $markdown_assets_dir = $markdown_file_dir . "/" . explode('.', $markdown_file_name)[0] . ".assets";
  $markdown_file_components = explode('.', $markdown_file);
  $markdown_file_ext = end($markdown_file_components);
  // echo "markdown_file = $markdown_file\n";
  // echo "markdown_file_name = $markdown_file_name\n";
  // echo "markdown_file_ext = $markdown_file_ext\n";
  // echo "markdown_file_dir = $markdown_file_dir\n";
  // echo "markdown_assets_dir = $markdown_assets_dir\n";

  // 若不是文件，则跳过
  if (is_dir($markdown_file)) {
    echo ("⚠️ 非Markdown文件，跳过：$markdown_file\n");
    return;
  }

  // 若不是 md 格式，则跳过
  if (strtolower($markdown_file_ext) != "md") {
    echo ("⚠️ 非Markdown文件，无需离线：$markdown_file\n");
    return;
  }

  // 源文件，则跳过
  if ($markdown_file_components[count($markdown_file_components)-2] == "ori") {
    echo ("⚠️ 已离线Markdown文件的源文件，直接跳过：$markdown_file\n");
    return;
  }

  // 若资源文件夹已存在 且不是文件夹，则删除
  if (file_exists($markdown_assets_dir) && !is_dir($markdown_assets_dir)) {
      $unlink_success = unlink($markdown_assets_dir);
      echo ($unlink_success ? "✅" : "❌") . " 目录删除" . ($unlink_success ? "成功" : "失败") . ":\t" . $markdown_assets_dir . "\n";
  }

  // 若资源文件夹不存在 则创建
  if (!file_exists($markdown_assets_dir)) {
      $mkdir_success = mkdir($markdown_assets_dir, 0777, true);
      echo ($mkdir_success ? "✅" : "❌") . " 目录创建" . ($mkdir_success ? "成功" : "失败") . ":\t" . $markdown_assets_dir . "\n";
  }

  // 加载 merkdown 内容
  $markdown_file_content = file_get_contents($markdown_file);

  // 找出所有 ![title](path) 图片标签 
  $markdown_img_pattern = '/\!\[[^\]]*\]\(http[^\)]+\)/';
  preg_match_all($markdown_img_pattern, $markdown_file_content, $markdown_img_tags);
  $markdown_img_tags = $markdown_img_tags[0]; // 二维数组 降为 一维数组
  $markdown_img_tags = array_unique($markdown_img_tags); // 数组去重
  // print_r($markdown_img_tags);
  if (empty($markdown_img_tags)) {
    echo ("✅ Markdown文件离线结束(无网络图片需要离线)：$markdown_file\n");
    return;
  }

  // 遍历图片标签
  for ($index=0; $index<count($markdown_img_tags); $index++) {
    $markdown_img_tag = $markdown_img_tags[$index];
    // echo($markdown_img_tag."\n");

    // 提取 path
    $img_title_pattern = '/\!\[[^\]]*\]/';
    $markdown_img_path = preg_replace($img_title_pattern, '', $markdown_img_tag);
    // print_r($markdown_img_path."\n");

    // 提取URL
    $img_url_pattern = '/[a-zA-z]+:[^\s\)]*/';
    preg_match($img_url_pattern, $markdown_img_path, $markdown_img_url);
    $markdown_img_url = $markdown_img_url[0];
    // print_r($markdown_img_url."\n");

    // 提取URL，可能带有参数
    $img_url_pattern_with_params = '/[a-zA-z]+:[^\)]*/'; //'/[a-zA-z]+:[^\s\)]*/';
    preg_match($img_url_pattern_with_params, $markdown_img_path, $markdown_img_url_with_params);
    $markdown_img_url_with_params = $markdown_img_url_with_params[0];
    // print_r($markdown_img_url_with_params."\n");

    // 下载图片
    $markdown_img_url_cache_key = md5($markdown_img_url); //$index; //urlencode($markdown_img_url);
    $markdown_img_url_cache_file_name = "$markdown_img_url_cache_key.png";
    $markdown_img_url_cache_file = "$markdown_assets_dir/$markdown_img_url_cache_file_name";
    $markdown_img_url_cache_file_relative_path = basename($markdown_assets_dir) . "/" . $markdown_img_url_cache_file_name;
    $markdown_img_url_cache_file_download_success = download_image($markdown_img_url, $markdown_img_url_cache_file);
    echo ($markdown_img_url_cache_file_download_success==true ? "✅" : "❌") . " No.$index\t$markdown_img_url\n\t=> $markdown_img_url_cache_file_relative_path " . "\n";

    // 替换为本地图片
    $markdown_file_content = str_replace($markdown_img_url_with_params, $markdown_img_url_cache_file_relative_path . " \"$markdown_img_url_with_params\"", $markdown_file_content);
  }

  // 备份原版
  $markdown_file_ori = substr($markdown_file, 0, -3) .".ori.md";
  $markdown_file_rename_success = rename($markdown_file, $markdown_file_ori);
  echo ($markdown_file_rename_success==true ? "✅" : "❌") . " 将文件重命名为：$markdown_file_ori\n";

  // 保存修改
  $file_put_contents_success = file_put_contents($markdown_file, $markdown_file_content);
  echo ($file_put_contents_success==true ? "✅" : "❌") . " Markdown文件离线结束：$markdown_file\n";
  echo "\n\n\n\n";
}

// 下载图片
function download_image($url, $file) {
  if (empty($url)) {
    return false;
  }

  $state = @file_get_contents($url,0,null,0,1);//获取网络资源的字符内容
  if($state) {
    ob_start();//打开输出
    readfile($url);//输出图片文件
    $img = ob_get_contents();//得到浏览器输出
    ob_end_clean();//清除输出并关闭
    $fp2 = @fopen($file, "a");
    fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名
    fclose($fp2);
    return true;
  } else {
    return false;
  }
}
