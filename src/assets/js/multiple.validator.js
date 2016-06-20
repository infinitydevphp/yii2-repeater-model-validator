/**
 * @author infinitydevphp <infinitydevphp@gmail.com>
 */

yii.validation.multipleModel = function (attribute, value, messages, options, deferred, $form) {
    function getId(index, attributeName) {
        var parts = [
            options.modelName.toLowerCase(),
            options.attribute.toLowerCase(),
            index,
            attributeName
        ];
        return parts.join('-');
    }

    var attributesAll = $form.data('yiiActiveForm').attributes;

    function searchAttribute(id) {
        var reg = new RegExp(id),
            attrs = [];

        $.each(attributesAll, function (index, next) {
            if (reg.test(next.id)) {
                attrs.push(next);
            }
        });

        return attrs;
    }

    if (!yii.validation.multipleModel.isTrigger) {
        $.each(options.validatorList, function (NextAttribute, validator) {
            var $nextAttrElem = $('#' + getId(0, NextAttribute));
            if ($nextAttrElem.size()) {
                (function (_attribute, _validator) {
                    var id = getId('\\d+', _attribute),
                        $selector = $form.find('table input, table select, table textarea')
                        .filter(function () {
                            var r = new RegExp(id);
                            return this.id.match(r);
                        }),
                        findAttr = searchAttribute(id);

                    if (findAttr.length) {
                        var func = (function (valid) {
                            return function (attribute, value, messages, options, deferred, $form) {
                                $.each(valid, function (name, next) {
                                    next(attribute, value, messages, options, deferred, $form);
                                })
                            }
                        })(_validator);

                        $.each(findAttr, function (index, value) {
                            findAttr[index].validate = func
                        });
                    }


                })(NextAttribute, validator);
            }
        })
    }
    // yii.validation.multipleModel.isTrigger = true;
};

yii.validation.multipleModel.isTrigger = false;

yii.validation.multipleModel.addRow = function (event) {
    var $form = $('form').has(this),
        data = $form.data('yiiActiveForm'),
        $this = $(this),
        $elem = $this.find('input, select, textarea').eq(0);

    if (typeof data === "object" && data.attributes === "object") {
        $.each(data.attributes, function (index, value) {
            if (typeof value == 'object' && typeof value.id !== "undefined") {
                if (!$('#' + value.id).size()) {
                    $form.yiiActiveForm('remove', value.id);
                }
            }
        });
    }

    $elem.trigger('blur');
};
yii.validation.multipleModel.deleteRow = function () {
    var $form = $('form').has(this),
        data = $form.data('yiiActiveForm'),
        $this = $(this),
        $elem = $this.find('input, select, textarea').eq(0);

    if (typeof data === "object" && data.attributes === "object") {
        $.each(data.attributes, function (index, value) {
            if (typeof value == 'object' && typeof value.id !== "undefined") {
                if (!$('#' + value.id).size()) {
                    $form.yiiActiveForm('remove', value.id);
                }
            }
        });
    }

    $elem.find('input, select, textarea').eq(0).trigger('blur');
};
