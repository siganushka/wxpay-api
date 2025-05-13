# Wxpay API

微信支付相关接口实现。基于 [siganushka/api-factory](https://github.com/siganushka/api-factory) 抽象层，可快速实现微信支付相关业务。

### 安装

```bash
$ composer require siganushka/wxpay-api
```

### 使用

具体使用参考 `./example` 示例目录，运行示例前请复制 `_config.php.dist` 文件为 `_config.php` 并修改相关参数。

该目录包含以下示例：

| 文件 | 功能 |
| ------------ | ------------ |
| example/unifiedorder.php | 微信支付统一下单 |
| example/query.php | 微信支付查询订单 |
| example/refund.php | 微信支付退款 |
| example/transfer.php | 微信支付付款到零钱 |
| example/parameter_utils.php | 生成微信支付参数 |
| example/signature_uitls.php | 生成、验证支付签名 |
| example/notify.php | 微信支付异步通知 |

### 框架集成

`Symfony` 项目已集成至 [siganushka/api-factory-bundle](https://github.com/siganushka/api-factory-bundle)，将以服务的形式使用。

安装

```bash
$ composer require siganushka/wxpay-api siganushka/api-factory-bundle
```

配置

```yaml
# config/packages/siganushka_api_factory.yaml

siganushka_api_factory:
    wxpay:
        appid: your_appid
        mchid: your_mchid
        mchkey: your_mchkey
        mch_client_cert: null
        mch_client_key: null
```

使用

```php
// src/Controller/DefaultController.php

use Siganushka\ApiFactory\Wxpay\Unifiedorder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function index(Unifiedorder $request)
    {
        $options = [
            'body' => '统一下单测试订单',
            'notify_url' => 'http://localhost',
            'out_trade_no' => uniqid(),
            'total_fee' => 1,
            'trade_type' => 'APP',
        ];

        $result = $request->send($options);
        var_dump($result);
    }
}
```

查看所有可用服务

```bash
$ php bin/console debug:container Siganushka\\ApiFactory\\Wxpay
```
