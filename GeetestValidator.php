<?php
namespace app\components;

use yii\captcha\CaptchaValidator;
use Yii;

class GeetestValidator extends CaptchaValidator
{
    public $type = 'pc';

    public $captchaAction = 'site/geetest';

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $captcha = $this->createCaptchaAction();
        $valid = is_array($value) && $captcha->validate($value, $this->type);

        return $valid ? null : [$this->message, []];
    }
}