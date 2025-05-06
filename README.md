# Wechat API

微信公众号、小程序和开放平台相关接口实现，基于 [siganushka/api-factory](https://github.com/siganushka/api-factory) 抽象层，可快速实现微信相关业务。

### 安装

```bash
$ composer require siganushka/wechat-api dev-main
```

### 使用

具体使用参考 `./example` 示例，运行示例前复制 `_config.php.dist` 文件为 `_config.php` 并修改相关参数，由于示例中包含了小程序、公众号和开放平台相关功能，因此对应功能修改对应参数即可。

目录包含以下示例：

| 文件                                | 功能                               |
| ----------------------------------- | ---------------------------------- |
| example/affiaccount_user.php        | 微信公众号获取用户列表             |
| example/affiaccount_userinfo.php    | 微信公众号获取用户基本信息         |
| example/core_callback_ip.php        | 获取微信 callback IP 地址          |
| example/core_server_ip.php          | 获取微信服务器 IP 地址             |
| example/core_ticket.php             | 获取微信临时票据，用于微信 JS API  |
| example/core_token.php              | 获取全局 access_token              |
| example/core_token_stable.php       | 获取全局稳定版 access_token        |
| example/jsapi_config_utils.php      | 生成微信 JS API 配置参数           |
| example/miniapp_qrcode.php          | 获取小程序二维码                   |
| example/miniapp_session_key.php     | 获取小程序 session_key             |
| example/miniapp_wxacode.php         | 获取小程序码                       |
| example/miniapp_wxacode_unlimit.php | 获取不限制的小程序码               |
| example/oauth_client.php            | 网页授权                           |
| example/oauth_client_qrcode.php     | 网页授权（扫码登录）               |
| example/oauth_user_info.php         | 根据用户 access_token 获取用户信息 |
| example/oauth_check_token.php       | 检验用户 access_token 是否有效     |
| example/oauth_refresh_token.php     | 刷新用户 access_token              |
| example/message_subscribe.php       | 发送小程序订阅消息                 |
| example/template_message.php        | 发送模板消息                       |

### 框架集成

项目已集成至 `siganushka/api-factory-bundle`，适用于 `Symfony` 框架，以上所有示例将以服务的形式在框架中提供服务。

安装

```bash
$ composer require siganushka/api-factory-bundle siganushka/wechat-api dev-main
```

单个应用配置

```yaml
# config/packages/siganushka_api_factory.yaml

siganushka_api_factory:
  wechat:
    appid: your_appid
    secret: your_secret
```

多个应用配置

```yaml
# config/packages/siganushka_api_factory.yaml

siganushka_api_factory:
  wechat:
    default_configuration: foo
    configurations:
      # 将以 Siganushka\ApiFactory\Wechat\Configuration $fooConfiguration 被注入到服务
      foo:
        appid: your_appid
        secret: your_secret
      # 将以 Siganushka\ApiFactory\Wechat\Configuration $barConfiguration 被注入到服务
      bar:
        appid: your_appid
        secret: your_secret
```

使用

```php
// src/Controller/DefaultController.php

use Siganushka\ApiFactory\Wechat\Core\Token;
use Siganushka\ApiFactory\Wechat\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function index(Token $request)
    {
        // 使用默认配置，即 foo
        $result = $request->send();
        var_dump($result);
    }

    public function useBarConfig(Token $request, Configuration $barConfiguration)
    {
        // 使用 bar 覆盖默认配置
        $request->extend(new ConfigurationExtension($barConfiguration));

        $result = $request->send();
        var_dump($result);
    }
}
```

查看所有可用服务

```bash
$ php bin/console debug:container Siganushka\\ApiFactory\\Wechat
```
