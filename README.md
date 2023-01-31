# Wechat API

基于 `siganushka/api-factory` 抽象层的微信相关接口实现，可快速实现微信相关业务。

### 安装

```bash
$ composer require siganushka/wechat-api dev-main
```

### 使用

具体使用参考 `./example` 示例目录，运行示例前请复制 `_config.php.dist` 文件为 `_config.php` 并修改相关参数，由于示例中包含了小程序、公众号和开放平台相关功能，因此对应功能修改对应参数即可。

该目录包含以下示例：

| 文件 | 功能 |
| ------------ | ------------ |
| ./example/core_callback_ip.php | 获取微信 callback IP 地址 |
| ./example/core_server_ip.php | 获取微信服务器 IP 地址 |
| ./example/core_ticket.php | 获取微信临时票据，用于微信 JS API |
| ./example/core_token.php | 获取微信全局 access_token |
| ./example/jsapi_config_utils.php | 生成微信 JS API 配置参数 |
| ./example/miniapp_qrcode.php | 获取小程序二维码 |
| ./example/miniapp_session_key.php | 获取小程序 session_key |
| ./example/miniapp_wxacode.php | 获取小程序码 |
| ./example/miniapp_wxacode_unlimit.php | 获取不限制的小程序码 |
| ./example/oauth_client.php | 网页授权 |
| ./example/oauth_client_qrcode.php | 网页授权（扫码登录） |
| ./example/oauth_user_info.php | 根据用户 access_token 获取用户信息 |
| ./example/oauth_check_token.php | 检验用户授权凭证 access_token 是否有效 |
| ./example/oauth_refresh_token.php | 刷新用户 access_token |
| ./example/template_message.php | 发送模板消息 |

### 框架集成

该 SDK 包已集成至 `siganushka/api-factory-bundle`，适用于 `Symfony` 框架，以上所有示例将以服务的形式在框架中使用。

安装

```bash
$ composer require  siganushka/api-factory-bundle dev-main
$ composer require  siganushka/wechat-api dev-main
```

配置

```yaml
# ./config/packages/siganushka_api_factory.yaml

siganushka_api_factory:
    wechat:
        appid: your_appid
        secret: your_secret
```

使用

```php
// ./src/Controller/DefaultController.php

use Siganushka\ApiFactory\Wechat\Core\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function getToken(Token $request)
    {
        $result = $request->send();
        var_dump($result);
    }
}
```

查看所有可用服务

```bash
$ php bin/console debug:container Siganushka\ApiFactory\Wechat
```
