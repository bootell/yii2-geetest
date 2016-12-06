<?php
namespace app\components;

use yii\base\Action;
use Yii;
use yii\base\DynamicModel;
use app\models\GeetestLib;
use yii\base\Exception;

class GeetestCaptchaAction extends Action
{
    private $_captcha_id = 'b46d1900d0a894591916ea94ea91bd2c';
    private $_private_key = '36fc3fe98530eea08dfc6ce76e3d24c4';
    private $_mobile_captcha_id = '7c25da6fe21944cfe507d2f9876775a9';
    private $_mobile_private_key = 'f5883f4ee3bd4fa8caec67941de1b903';

    public function init()
    {
        if (!($this->_captcha_id && $this->_private_key) || !($this->_mobile_captcha_id && $this->_mobile_private_key)) {
            throw new Exception('Unset Geetest Keys');
        }
    }

    public function run()
    {
        $phone = Yii::$app->request->get('phone');
        $type = Yii::$app->request->get('type');

        $model = DynamicModel::validateData(compact('phone', 'type'), [
            [['phone'], 'required'],
            [['type'], 'in', 'range' => ['pc', 'mobile']],
        ]);
        if ($model->hasErrors()) {
            return $model->getErrors();
        }

        $geetest = $this->getGeetest($type);
        Yii::$app->session->set('geetest_phone', $phone);
        Yii::$app->session->set('geetest_status', $geetest->pre_process($phone));

        return $geetest->get_response_str();
    }

    public function validate($input, $type = 'pc')
    {
        $geetest_challenge = $input['geetest_challenge'];
        $geetest_validate = $input['geetest_validate'];
        $geetest_seccode = $input['geetest_seccode'];

        $phone = Yii::$app->session->get('geetest_phone');
        $status = Yii::$app->session->get('geetest_status');

        $geetest = $this->getGeetest($type);

        if ($status == 1) {
            $result = $geetest->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $phone);
        } else {
            $result = $geetest->fail_validate($geetest_challenge, $geetest_validate, $geetest_seccode);
        }

        return $result;
    }

    protected function getGeetest($type)
    {
        if($type == 'mobile'){
            return new GeetestLib($this->_mobile_captcha_id, $this->_mobile_private_key);
        } else {
            return new GeetestLib($this->_captcha_id, $this->_private_key);
        }
    }
}