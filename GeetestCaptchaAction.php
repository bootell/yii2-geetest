<?php
namespace app\components;

use yii\base\Action;
use Yii;
use yii\base\DynamicModel;
use app\models\GeetestLib;
use yii\base\Exception;
use yii\web\Response;

class GeetestCaptchaAction extends Action
{
    private $_captcha_id = 'b46d1900d0a894591916ea94ea91bd2c';
    private $_private_key = '36fc3fe98530eea08dfc6ce76e3d24c4';
    private $_mobile_captcha_id = '7c25da6fe21944cfe507d2f9876775a9';
    private $_mobile_private_key = 'f5883f4ee3bd4fa8caec67941de1b903';

    public function init()
    {
        if (!($this->_captcha_id && $this->_private_key) || !($this->_mobile_captcha_id && $this->_mobile_private_key)) {
            throw new Exception('Geetest Is Not Available');
        }
    }

    public function run()
    {
        $action = Yii::$app->request->get('action');
        $type = Yii::$app->request->get('type');

        $model = DynamicModel::validateData(compact('action', 'type'), [
            [['action'], 'required'],
            [['action'], 'string', 'length' => [1, 32]],
            [['type'], 'in', 'range' => ['pc', 'mobile']],
        ]);
        if ($model->hasErrors()) {
            return $this->reply(['info' => $model->getErrors()], 'failed', 1);
        }

        try {
            $geetest = $this->getGeetest($type);
            $user_id = uniqid();
            Yii::$app->session->set('geetest_user' . $action, $user_id);
            Yii::$app->session->set('geetest_status' . $action, $geetest->pre_process($user_id));
        } catch (Exception $e) {
            return $this->reply([], 'failed', 1);
        }

        return $this->reply($geetest->get_response_str(), 'success', 0);
    }

    public function validate($input, $type = 'pc')
    {
        $geetest_challenge = $input['geetest_challenge'];
        $geetest_validate = $input['geetest_validate'];
        $geetest_seccode = $input['geetest_seccode'];
        $action = $input['action'];

        $phone = Yii::$app->session->get('geetest_user' . $action);
        $status = Yii::$app->session->get('geetest_status' . $action);

        try {
            $geetest = $this->getGeetest($type);

            if ($status == 1) {
                $result = $geetest->success_validate($geetest_challenge, $geetest_validate, $geetest_seccode, $phone);
            } else {
                $result = $geetest->fail_validate($geetest_challenge, $geetest_validate, $geetest_seccode);
            }
        } catch (Exception $e) {
            return false;
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

    protected function reply($data, $message, $code)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'data' => $data,
            'message' => $message,
            'code' => $code,
        ];
    }
}
