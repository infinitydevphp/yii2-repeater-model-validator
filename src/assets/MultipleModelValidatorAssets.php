<?php
/**
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */

namespace infinitydevphp\MultipleModelValidator\assets;

use yii\web\AssetBundle;
use yii\validators\ValidationAsset;

/**
 * Multiple model validator assets register
 * Class MultipleModelValidatorAssets
 * @package infinitydevphp\MultipleModelValidator\assets
 */
class MultipleModelValidatorAssets extends AssetBundle
{
    public $js = [
        'multiple.validator.' . ((YII_DEBUG || YII_ENV == YII_ENV_DEV) ? '' : 'min.') . 'js'
    ];

    public $depends = [
        'yii\validators\ValidationAsset'
    ];

    public function init() {
        $this->sourcePath = __DIR__ . DIRECTORY_SEPARATOR . 'js';
    }
}