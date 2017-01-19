Yii2 Geetest
===

极验在Yii2下的封装


Usage
---

```php
Controller

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