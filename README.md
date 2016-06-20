
Extend [unclead/yii2-multiple-input](https://github.com/unclead/yii2-multiple-input) for related model backend & client validation

Init validation:
Add to model rules:
[['attribute_related'], 'infinitydevphp\MultipleModelValidator\MultipleModelValidator']
And use widget (extend from [\unclead\widgets\MultipleInput](https://github.com/unclead/yii2-multiple-input/blob/master/src/MultipleInput.php)) in form

```php
$options = [];

$form->field($model, 'attribute_name')
    ->widget(infinitydevphp\MultipleModelValidator\MultipleModelValidator::className, $options);
```

# Widget options
| Option name | Type | Description |
| ----------- | ---- | ----------- |
| baseModel | string | base model for create instance next repeater model |
| jsOptions | array/null | event listener for add new row and delete row |
| skipAttributes | array/null | skip attribute on render if not set $columns |

JS options declared two key: 
1. afterAddRow: fired after add row
2. afterDeleteRow: fired after delete row

Other options see in [https://github.com/unclead/yii2-multiple-input](base package)