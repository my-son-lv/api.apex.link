<?php


/**
 * 平台消息字符串定义
 * @param $data
 * @param $type
 * @return string
 */
function returnNoticeMsg($data, $type)
{
    $company_name = isset($data['company_name']) ? $data['company_name'] : '';
    $teach_name = isset($data['teach_name']) ? $data['teach_name'] : '';
    $user = isset($data['user']) ? $data['user'] : '';
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $adviser_name = isset($data['adviser_name']) ? $data['adviser_name'] : '';
    $time = isset($data['time']) ? $data['time'] : '';
    $time1 = isset($data['time1']) ? $data['time1'] : '';
    $res = isset($data['res']) ? $data['res'] : '';
    $msg = '';
    switch ($type) {
        case 1001:
            $msg = '外教' . $teach_name . '注册了平台';
            break;
        case 1002:
            $msg = '企业' . $company_name . '注册了平台';
            break;
        case 1003:
            $msg = '外教' . $teach_name . '提交了入驻申请';
            break;
        case 1004:
            $msg = '企业' . $company_name . '提交了入驻申请';
            break;
        case 1005:
            $msg = $user . '通过了外教' . $teach_name . '的入驻申请';
            break;
        case 1006:
            $msg = $user . '通过了企业' . $company_name . '的入驻申请';
            break;
        case 1007:
            $msg = $user . '驳回了外教' . $teach_name . '入驻申请';
            break;
        case 1008:
            $msg = $user . '驳回了企业' . $company_name . '入驻申请';
            break;
        case 1009:
            $msg = '外教' . $teach_name . '信息自动审核通过';
            break;
        case 2001:
            $msg = '企业' . $company_name . '修改平台信息';
            break;
        case 2002:
            $msg = '企业' . $company_name . '发布了招聘需求';
            break;
        case 2003:
            $msg = $user . '修改了外教' . $teach_name . '平台信息';
            break;
        case 2004:
            $msg = $user . '修改了企业' . $company_name . '平台信息';
            break;
        case 2005:
            $msg = $user . '为企业' . $company_name . '添加了招聘需求';
            break;
        case 2006:
            $msg = $user . '修改了企业' . $company_name . '招聘需求';
            break;
        case 2007:
            $msg = $user . '将外教' . $teach_name . '的顾问变更为' . $adviser_name;
            break;
        case 2008:
            $msg = $user . '将企业' . $company_name . '的顾问变更为' . $adviser_name;
            break;
        case 2009:
            $msg = $user . '添加了外教' . $teach_name;
            break;
        case 2010:
            $msg = $user . '添加了企业' . $company_name;
            break;
        case 2011:
            $msg = '企业用户' . $company_name . '申请购买会员,用户电话号' . $phone . '，请及时与企业联系';
            break;
        case 2012:
            $msg = $company_name . '通过官网申请购买会员,用户电话号' . $phone . '，请及时与企业联系';
            break;
        case 3001:
            $msg = '企业用户' . $company_name . '预约了' . $teach_name . '于北京时间' . $time . '进行面试';
            break;
        case 3002:
            $msg = '企业用户' . $company_name . '修改了北京时间' . $time . '与' . $teach_name . '的面试，修改后时间为：北京时间' . $time1;
            break;
        case 3003:
            $msg = '企业用户' . $company_name . '取消了北京时间' . $time . '与' . $teach_name . '的面试';
            break;
        case 3004:
            $msg = '外教用户' . $teach_name . '想修改与' . $company_name . '北京时间' . $time.'的面试';
            break;
        case 3005:
            $msg = '外教用户' . $teach_name . '想取消与' . $company_name . '北京时间' . $time;
            break;
        case 3006:
            $msg = $adviser_name . '同意了企业' . $company_name . '与' . $teach_name . '外教' . $time . '的面试';
            break;
        case 3007:
            $msg = $adviser_name . '拒绝了企业' . $company_name . '与' . $teach_name . '外教' . $time . '的面试';
            break;
        case 3008:
            $msg = $adviser_name . '变更了企业' . $company_name . '与' . $teach_name . '外教' . $time . '的面试，变更后时间为' . $time1;
            break;
        case 3009:
            $msg = $adviser_name . '取消了企业' . $company_name . '与' . $teach_name . '外教' . $time . '的面试';
            break;
        case 3010:
            $msg = '企业' . $company_name . '与' . $teach_name . '外教' . $time . '的面试已完成。结果为' . $res;
            break;
        case 3011:
            $msg = $teach_name . '外教已被' . $company_name . '企业录用';
            break;
    }
    return $msg;
}


/**
 * @param $data 数据名称
 * @param $type 类型
 * @param $flg 1企业 2管理后台
 */
function interViewLogMsg($data, $type)
{
    $company_name = isset($data['company_name']) ? $data['company_name'] : '';
    $teach_name = isset($data['teach_name']) ? $data['teach_name'] : '';
    $adviser_name = isset($data['adviser_name']) ? $data['adviser_name'] : '';
    $time = isset($data['time']) ? $data['time'] : '';
    $time1 = isset($data['time1']) ? $data['time1'] : '';
    $res = isset($data['res']) ? $data['res'] : '';
    switch ($type) {
        case 1:
            return ['我预约了 ' . $time . ' 与外教' . $teach_name . '面试。', $company_name . '预约了 ' . $time . ' 与外教' . $teach_name . '面试。'];
            break;
        case 2:
            return [$teach_name . '同意了 ' . $time . ' 的面试。', $adviser_name . '同意了 ' . $time . ' 的面试。'];
            break;
        case 3:
            return [$teach_name . '拒绝了 ' . $time . ' 的面试。', $adviser_name . '拒绝了 ' . $time . ' 的面试。'];
            break;
        case 4:
            return [$teach_name . '变更了 ' . $time . ' 的面试，变更后时间' . $time1 . '。', $adviser_name . '变更了 ' . $time . ' 的面试，变更后时间' . $time1 . '。'];
            break;
        case 5:
            return [$teach_name . '取消了 ' . $time . ' 的面试。', $adviser_name . '取消了 ' . $time . ' 的面试。'];
            break;
        case 6:
            return ['我与' . $teach_name . ' ' . $time . ' 的面试已完成。结果为' . $res . '。', $company_name . '与' . $teach_name . ' ' . $time . ' 的面试已完成。结果为' . $res . '。'];
            break;
        case 7:
            return [$teach_name . '已被我公司录用。', $teach_name . '已被' . $company_name . ' ' . $time . '录用。'];
            break;
        case 8:
            return ['我变更了与外教' . $teach_name . ' ' . $time . ' 的面试，变更后时间为' . $time1 . '。', $company_name . '变更了与外教' . $teach_name . ' ' . $time . ' 的面试，变更后时间为' . $time1 . '。'];
            break;
        case 9:
            return ['我取消了与外教' . $teach_name . ' ' . $time . ' 的面试。', $company_name . '取消了与外教' . $teach_name . ' ' . $time . '的面试。'];
            break;
        case 10:
            return ['我与外教' . $teach_name . ' ' . $time . ' 的面试超过两个小时未进入房间已过期。', $company_name . '与外教' . $teach_name . ' ' . $time . ' 的面试超过两个小时未进入房间已过期。'];
            break;
    }
}

/**
 * dst_path 图片路径
 * src_path 水印位置
 */
function createWater($dst_path, $src_path, $res_path)
{
    //创建图片的实例
    $dst = imagecreatefromstring(file_get_contents($dst_path));
    $src = imagecreatefromstring(file_get_contents($src_path));
    //获取水印图片的宽高
    list($src_w, $src_h) = getimagesize($src_path);
    //获取原图片宽高
    list($src_w_p, $src_h_p) = getimagesize($dst_path);
    //将水印图片复制到目标图片右下角，最后个参数50是设置透明度，这里实现半透明效果
    imagecopy($dst, $src, $src_w_p - $src_w - 220, $src_h_p - $src_h - 1580, 0, 0, $src_w, $src_h);
    //输出图片
    list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);
    switch ($dst_type) {
        case 1://GIF
            header('Content-Type: image/gif');
            imagegif($dst, $res_path);
            break;
        case 2://JPG
            header('Content-Type: image/jpeg');
            imagejpeg($dst, $res_path);
            break;
        case 3://PNG
            header('Content-Type: image/png');
            imagepng($dst, $res_path);
            break;
        default:
            break;
    }
    imagedestroy($dst);
    imagedestroy($src);
}

/**
 * 生成唯一标识
 * @return string
 */
function makeCouponCard()
{
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0, 25)]
        . strtoupper(dechex(date('m')))
        . date('d') . substr(time(), -5)
        . substr(microtime(), 2, 5)
        . sprintf('%02d', rand(0, 99));
    for (
        $a = md5($rand, true),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord($a[$f]),
        $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
        $f++
    ) ;
    return $d;
}

/**
 * 返回飞书通知内容
 * @param $type
 * @param $data
 * @return string
 */
function returnFeiShuMsg($type, $data)
{
    $teach_name = isset($data['teach_name']) ? $data['teach_name'] : '';
    $company_name = isset($data['company_name']) ? $data['company_name'] : '';
    $time = isset($data['time']) ? $data['time'] : '';
    $time1 = isset($data['time1']) ? $data['time1'] : '';
    $phone = isset($data['phone']) ? $data['phone'] : '';
    $job_name = isset($data['job_name']) ? $data['job_name'] : '';
    $msg = '';
    switch ($type) {
        case 1:
            $msg = '【企业端】' . $company_name . '于' . $time . '提交入驻申请,请登录后台审核' . config('app.admin_url');
            break;
        case 2:
            $msg = '【企业端】' . $company_name . '于' . $time . '修改资料,请登录后台审核' . config('app.admin_url');
            break;
        case 3:
            $msg = '【企业端】' . $company_name . '于' . $time . '发布了招聘需求,请登录后台查看' . config('app.admin_url');
            break;
        case 4:
            $msg = '【外教端】' . $teach_name . '于' . $time . '提交入驻申请,请登录后台审核' . config('app.admin_url');
            break;
        case 5:
//            $msg = '外教端 '.$teach_name.' 于 '.$time.' 提交入驻申请,请登录后台审核'.config('app.admin_url');
            break;
        case 6:
            $msg = $company_name . '预约了' . $teach_name . '于北京时间' . $time . '进行面试，请与外教联系后登陆后台处理该条面试邀约';
            break;
        case 7:
            $msg = $company_name . '公司与' . $teach_name . '于北京时间' . $time . '的面试将于一小时后开始，请确认双方是否做好准备。';
            break;
        case 8:
            $msg = $company_name . '修改了北京时间' . $time . '与' . $teach_name . '的面试，修改后时间为：北京时间' . $time1 . '。';
            break;
        case 9:
            $msg = $company_name . '取消了北京时间' . $time . '与' . $teach_name . '的面试，请告知外教面试已被取消。';
            break;
        case 10:
            $msg = $teach_name . '同意了与企业' . $company_name . '北京时间' . $time . '的面试，请登录后台面试管理列表。';
            break;
        case 11:
            $msg = $teach_name . '拒绝了与企业' . $company_name . '北京时间' . $time . '的面试。';
            break;
        case 12:
            $msg = '企业用户' . $company_name . '申请购买会员 用户电话号' . $phone . '，请及时与企业联系';
            break;
        case 13:
            $msg = $company_name . '企业通过官网申请购买会员，用户电话号' . $phone . '，请及时与企业联系';
            break;
        case 14:
            $msg = $company_name . '的精准推送服务套餐额度已用完，请尽快联系升级套餐！';
            break;
        case 15:
            $msg = $company_name . '的急聘服务套餐额度已用完，请尽快联系升级套餐！';
            break;
        case 16:
            $msg = $company_name . '发布的 '.$job_name.' 已发起精准推送服务，请尽快处理！';
            break;
        case 17:
            $msg = $company_name . '发布的 '.$job_name.' 已发起急聘服务，请尽快处理！';
            break;
    }
    return $msg;
}

/**
 * 生成excel
 * @param array $stmt
 * @param array $head
 */
function putCsv($name = '幼儿园', $stmt = array(), $head = array())
{
    // 输出Excel文件头，可把user.csv换成你要的文件名
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $name . '.csv"');
    header('Cache-Control: max-age=0');
    // 打开PHP文件句柄，php://output 表示直接输出到浏览器
    $fp = fopen('php://output', 'a');
    // 输出Excel列名信息
    foreach ($head as $i => $v) {
        // CSV的Excel支持GBK编码，一定要转换，否则乱码
        $head[$i] = iconv('utf-8', 'gbk', $v);
    }
    // 将数据通过fputcsv写到文件句柄
    fputcsv($fp, $head);
    // 计数器
    $cnt = 0;
    // 每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    $limit = 100000;
    // 逐行取出数据，不浪费内存
    foreach ($stmt as $row) {
        $cnt++;
        if ($limit == $cnt) { //刷新一下输出buffer，防止由于数据过多造成问题
            ob_flush();
            flush();
            $cnt = 0;
        }
        foreach ($row as $i => $v) {
            $row[$i] = iconv('utf-8', 'gbk', $v);
        }
        fputcsv($fp, $row);
    }
}


/**
 * 加密
 *
 * @param string $str
 * @param string $key
 * @return string|bool
 */
function AesEncrypt($str, $key = 'sfsdfsdffsfa2423')
{
    $iv = '1234567890123456';
    $encrypt = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return urlencode(base64_encode($encrypt));
}

/**
 * 解密
 *
 * @param string $str
 * @param string $key
 * @return string|bool
 */
function AesDecrypt($str, $key = 'sfsdfsdffsfa2423')
{
    $iv = '1234567890123456';
    $decrypt = base64_decode(str_replace(' ', '+', urldecode($str)));
    return openssl_decrypt($decrypt, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
}


function doPut($url, $data, $header)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 跳过检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 跳过检查
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    /*$appId = Config::$config['appId'];
    $token = TokenHelper::getFileToken();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Tsign-Open-App-Id:".$appId, "X-Tsign-Open-Token:".$token, "Content-Type:application/json" ));*/
    if (!empty($header)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();
//        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return $return_content;
}

/**
 * 通过uid生成token
 * @param $uid
 * @return mixed
 */
function crateToken($uid)
{
    $key = mt_rand();
    $hash = hash_hmac("sha1", $uid . mt_rand() . time(), $key, true);
    $token = str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
    return $token;
}

function sendHttpPUT($uploadUrls, $contentMd5, $fileContent)
{
    $header = array(
        'Content-Type:application/pdf',
        'Content-Md5:' . $contentMd5
    );
    $curl_handle = curl_init();
    curl_setopt($curl_handle, CURLOPT_URL, $uploadUrls);
    curl_setopt($curl_handle, CURLOPT_FILETIME, true);
    curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, false);
    curl_setopt($curl_handle, CURLOPT_HEADER, true); // 输出HTTP头 true
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, 5184000);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 120);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT');

    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $fileContent);
    $result = curl_exec($curl_handle);
    $status = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

    if ($result === false) {
        $status = curl_errno($curl_handle);
        $result = 'put file to oss - curl error :' . curl_error($curl_handle);
    }
    curl_close($curl_handle);
    return $status;
}

/**
 * CURL Post发送数据
 *
 * @param $url 地址
 * @param $option 参数数据
 * @param $header 消息头
 * @param $type 发送方式
 */
function postJsonCurl($url, $option, $header = 0, $type = 'POST')
{
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)'); // 模拟用户使用的浏览器
    if (!empty($header)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    if (!empty ($option)) {
        $options = json_encode($option);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $options); // Post提交的数据包
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
    $result = curl_exec($curl); // 执行操作
    curl_close($curl); // 关闭CURL会话
    return $result;
}

function curl_get($url)
{
    $header = array(
        'Accept: application/json',
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($curl);
    return $data;
    // 显示错误信息
    // if (curl_error($curl)) {
    //     print "Error: " . curl_error($curl);
    // } else {
    //     var_dump($data);
    //     curl_close($curl);
    // }
}