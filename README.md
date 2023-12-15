##### 

##### 支付目录文件说明

1. 支付目录结构说明

    ```
    ├─src
    │  ├─gzh 		公众号接口目录
    │  │	├─xml         公众号消息回复模板目录
    │  ├─pay 		微信支付接口目录
    │  │	├─wxpay         微信各个支付类型文件目录
    │  │	│	├─H5Pay.php         H5支付文件
    │  │	│	├─JsapiPay.php         JSAPI支付文件
    │  │	│	├─NativePay.php         NATIVE支付文件
    │  │	├─Pay.php         支付接口定义文件
    │  │	├─WxPayBase.php         微信支付继承类文件, wxpay目录下的文件均继承此文件
    │  ├─xcx 		小程序接口目录
    ```

##### 公众号接口使用(需要开启redis拓展)

1. 需要在.env文件下配置如下参数

    ```
    WECHAT_GZH_APPID = 公众号APPID
    WECHAT_GZH_APPSECRET = 公众号秘钥
    ```

##### 小程序接口使用(需要开启redis拓展)

##### 小程序接口使用(需要开启redis拓展)

1. 需要在.env文件下配置如下参数

    ```
    WECHAT_XCX_APPID = 小程序APPID
    WECHAT_XCX_APPSECRET = 小程序秘钥
    ```

    

##### 微信支付V3使用 (目前仅支持, JSAPI支付和NATIVE支付, JSAPI支付也可以用在小程序上)

1. 需要在.env文件下配置如下参数

    ```sh
    WECHAT_PAY_APPID = appid
    WECHAT_PAY_MCH_ID = 商户号
    WECHAT_PAY_MCH_KEY = 商户号秘钥
    WECHAT_PAY_NOTIFY_URL = 支付通知地址
    WECHAT_PAY_SERIAL_NUMBER = 支付证书序列号
    WECHAT_PAY_APICLIENT_KEY_PATH = apiclient_key.pem文件的相对路径如: ./../cert/apiclient_key.pem
    WECHAT_PAY_WECHATPAY_PATH = wechatpay.pem文件的相对路径如: ./../cert/wechatpay.pem
    ```

2. 其中除WECHAT_PAY_WECHATPAY_PATH参数外,其他参数都可以在商户号根据操作得到, 下面是WECHAT_PAY_WECHATPAY_PATH参数的生成过程

    ```shell
    1. 打开命令行工具, 输入以下命令
    -k 商户号秘钥
    -m 商户号
    -f apiclient_key.pem文件的相对路径
    -s 支付证书序列号
    -o 生成文件的目录
    composer exec CertificateDownloader.php -- -k 商户号秘钥 -m 商户号id -f apiclient_key.pem文件的相对路径 -s 支付证书序列号 -o ./cert/
    ```

3. 如何使用微信支付

    ```php
    // 1. 实例化支付类型, 以下以jsapi支付为例, 如果在第一步没有配置.env的配置项,那么就挨个传参
    $pay = new JsapiPay();
    
    // 2. 使用统一下单
    $params = [
        'out_trade_no' => md5(microtime()),
        'description' => '测试商品',
        'total' => 1,
        'create_time' => time(),
        'openid' => $xcx_openid
    ];
    
    $result = $pay->getPrePay($params);
    
    // 3. 订单通知
    $inBody = file_get_contents('php://input');//返回的数据流
    
    $result = $pay->notify($inBody); // $inBody此参数可传可不传
    //为数组, 就返回支付的内容, 验证失败是false
    if($result){
        $pay->result(); //通知微信, 不传参数代表success
    }else{
        $pay->result('ERROR'); // 失败通知
    }
    
    // 4. 订单查询  , 支持第二个参数, 默认是微信订单号 transaction_id, 可以传平台订单号 out_trade_no
    $result = $pay->orderQuery('78d73823a8929ea06a8110039f459fa0');
    
    // 5. 订单关闭 , 参数: 平台订单号, 正常返回null
    $result = $pay->closeOrder('78d73823a8929ea06a8110039f459fa0');
    
    // 6. 订单退款
    $out_trade_no = '78d73823a8929ea06a8110039f459fa0'; // 平台支付订单号
    $out_refund_no = md5(time()); // 退款订单号
    $refund = [
        'out_trade_no' => $out_trade_no,
        'out_refund_no' => $out_refund_no,
        'total' => 1, // 支付总金额, 单位: 分
        'refund' => 1, // 退款金额, 单位: 分
        'reason' => '测试退款',
        'notify_url' => 'https://xxx/index/refundNotify', // 退款回调地址, 可选参数
    ];
    
    $result = $pay->refunds($refund);
    
    // 7. 退款查询
    $result = $pay->refundQuery('平台订单号');
    
    // 8. 退款通知
    $inBody = file_get_contents('php://input'); //返回的是json
    
    $result = $pay->refundNotify($inBody);// $inBody此参数可传可不传
    //为数组, 就返回支付的内容, 验证失败是false
    if($result){
        $pay->result(); //通知微信, 不传参数代表success
    }else{
        $pay->result('ERROR'); // 失败通知
    }
    ```

    

​		