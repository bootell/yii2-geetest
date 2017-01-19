Yii2 Geetest
===

极验在Yii2下的封装


Usage
---

Controller

```php
public function actions()
{
    return [
        'geetest' => [
            'class' => 'app\components\GeetestCaptchaAction',
        ],
    ];
}
```

Model

```php
public function rules()
{
    return [
        ['captcha', app\components\GeetestValidator::className()],
    ];
}
```

在前端实例化时,需要传递参数:

| params | describe | value |
| ------ |:--------:|:-----:|
| action | (必须)当前用户的标识 | string(32)] |
| type   | (可选)调用的设备类型 | `pc` 或 `mobile` |

提交时,需提交参数 `action`, `geetest_challenge`, `geetest_validate`, `geetest_seccode`.