<?php
/**
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */

namespace infinitydevphp\MultipleModelValidator;


use infinitydevphp\MultipleModelValidator\assets\MultipleModelValidatorAssets;
use yii\base\ErrorException;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\validators\PunycodeAsset;
use yii\validators\Validator;
use yii\web\JsExpression;

/**
 * Class MultipleModelValidator
 * @package infinitydevphp\MultipleModelValidator
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */
class MultipleModelValidator extends Validator
{
    /**
     * @var string Repeat model classname
     */
    public $baseModel;

    /**
     * @var boolean whether this validation rule should be skipped if the attribute value
     * is null or an empty string.
     */
    public $skipOnEmpty = true;

    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{value}`: the value of the attribute being validated
     *
     * Note that some validators may introduce other properties for error messages used when specific
     * validation conditions are not met. Please refer to individual class API documentation for details
     * about these properties. By convention, this property represents the primary error message
     * used when the most important validation condition is not met.
     */
    public $message = "Invalid multiple model data";
    protected $_model;
    protected $_attributeName = '';
    public $attribute = '';

    /**
     * Validates a single model attribute.
     * Child classes must implement this method to provide the actual validation logic.
     * @param \yii\base\Model $model the data model to be validated
     * @param string $attribute the name of the attribute to be validated.
     */
    public function validateAttribute($model, $attribute)
    {
        $this->_model = $model;
        $this->_attributeName = $attribute;
        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    /**
     * Validates models array.
     * @param mixed $attribute the data value to be validated.
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     */
    public function validateValue($value) {

        $model = &$this->_model;

        $attribute = $this->_attributeName;
        if ((!is_array($model->$attribute) || !count($model->$attribute)) && $this->skipOnEmpty) {
            return '';
        }

        $result = count($model->$attribute) > 0;
        $class = $this->baseModel;

        foreach ($model->$attribute as $_key => &$_model) {
            /** @var $_model Model|ActiveRecord */
            if (!is_object($_model)) {
                if (!is_array($_model)) {
                    $_model = [$this->attribute ? : $this->_attributeName => $_model];
                }
                $_model = new $class($_model);
            }

            $result = $_model->validate() && $result;
        }

        return $result || (sizeof($model->$attribute)==1 && $this->skipOnEmpty) ? null : [$this->message, $result];
    }

    /**
     * Returns the JavaScript needed for performing client-side validation.
     *
     * @param \yii\base\Model $baseModel the data model being validated
     * @param string $attribute the name of the attribute to be validated.
     * @param \yii\web\View $view the view object that is going to be used to render views or view files
     * containing a model form with this validator applied.
     * @return string the client-side validation script. Null if the validator does not support
     * client-side validation.
     * @see \yii\widgets\ActiveForm::enableClientValidation
     */
    public function clientValidateAttribute($baseModel, $attribute, $view) {

        $class = $this->baseModel;
        /** @var ActiveRecord|Model $model */
        $model = new $class();

        /** @var Validator[] $activeValidators */
        $activeValidators = $model->getActiveValidators();
        $clientValidators = [];
        foreach ($activeValidators as $_next) {
            foreach ($_next->attributes as $_attribute) {
                if (!(isset($clientValidators[$_attribute]) && is_array($clientValidators[$_attribute]))) {
                    $clientValidators[$_attribute] = [];
                }
                $clientValidators[$_attribute][$_next->className()] = new JsExpression(
                    'function (attribute, value, messages, options, deffered, $form) {' .
                    preg_replace('/;$/', '', $_next->clientValidateAttribute($model, $attribute, $view)) . '}');
            }
        }

        $options = [
            'message' => \Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], \Yii::$app->language)
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        $options['validatorList'] = $clientValidators;
        $modelName = explode("\\", $baseModel->className());
        $options['modelName'] = $modelName[sizeof($modelName) - 1];
        $modelName = explode("\\", $this->baseModel);
        $options['modelBase'] = $modelName[sizeof($modelName) - 1];
        $options['attribute'] = $attribute;

        MultipleModelValidatorAssets::register($view);

        $str =  'yii.validation.multipleModel(attribute, value, messages, ' . Json::encode($options) . ", deferred, \$form);";

        return $str;
    }
}