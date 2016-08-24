<?php
/**
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */

namespace infinitydevphp\MultipleModelValidator\widgets;


use unclead\widgets\MultipleInputColumn;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\web\JsExpression;
use \unclead\widgets\MultipleInput as BaseMultipleInput;

/**
 * Class MultipleInput
 * @package infinitydevphp\MultipleModelValidator\widgets
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */
class MultipleInput extends BaseMultipleInput
{
    public $baseModel = null;
    public $skipAttributes = ['id'];
    public $jsOptions = [];
    protected $_realAttribute;

    /**
     * Init data
     * @throws \ErrorException
     */
    protected function initData()
    {
        if (is_null($this->data) && is_null($this->baseModel)) {
            throw new \ErrorException("Error initialize multiple model input. Base model or data not set");
        }

        $attrPart = explode(']', $this->attribute);
        $this->_realAttribute = $this->attribute; //end($attrPart);

        if (is_null($this->data) && $this->model instanceof Model) {
            $this->data = [];
            foreach ((array)$this->model->{$this->_realAttribute} as $index => $value) {
                $this->data[$index] = [];

                $class = $this->baseModel;
                /** @var ActiveRecord|Model $model */
                $model = new $class($value);

                if (method_exists($model, 'setIsNewRecord')) {
                    $pkName = $model->getPrimaryKey(true);
                    $model->setIsNewRecord(count($pkName) == 0);
                }

                foreach ($model->attributes as $_name => $_value) {
                    $this->data[$index][$_name] = $_value;
                }
            }
        }
    }

    /**
     * Run widget.
     */
    public function run()
    {
        $this->jsOptions['afterAddRow'] = isset($this->jsOptions['afterAddRow']) ? $this->jsOptions['afterAddRow'] : new JsExpression('function (event) {
            yii.validation.multipleModel.addRow.call(this, arguments);
        }');
        $this->jsOptions['afterDeleteRow'] = isset($this->jsOptions['afterDeleteRow']) ? $this->jsOptions['afterDeleteRow'] : new JsExpression('function (event) {
            yii.validation.multipleModel.addRow.call(this, arguments);
        }');

        foreach ($this->jsOptions as $_name => $func) {
            $this->view->registerJs("$('#{$this->options['id']}').on('{$_name}', {$func});");
        }
        
        return parent::run();
    }

    /**
     * This function tries to guess the columns to show from the given data
     * if [[columns]] are not explicitly specified.
     */
    protected function guessColumns()
    {
        if (empty($this->columns)) {
            $class = $this->baseModel;
            /** @var Model $model */
            $model = new $class();
            $attributes = $model->attributes;

            $this->skipAttributes = is_array($this->skipAttributes) ? $this->skipAttributes : [$this->skipAttributes];

            foreach ($attributes as $_name => $_value) {
                if (!in_array($_name, $this->skipAttributes)) {
                    $this->columns[] = [
                        'name' => $_name,
                        'type' => MultipleInputColumn::TYPE_TEXT_INPUT,
                        'title' => $model->getAttributeLabel($_name)
                    ];
                }
            }
        }
    }


}