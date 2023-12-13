##### 微信支付V3使用

1. 需要在.env目录下配置如下参数

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