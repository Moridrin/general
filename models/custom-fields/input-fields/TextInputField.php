<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 10-1-17
 * Time: 12:03
 */
class TextInputField extends InputField
{
    const INPUT_TYPE = 'text';

    /** @var bool $disabled */
    public $disabled;
    /** @var array $required */
    public $required;
    /** @var string $defaultValue */
    public $defaultValue;
    /** @var string $placeholder */
    public $placeholder;

    /**
     * TextInputField constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $name
     * @param string $required
     * @param string $disabled
     * @param string $defaultValue
     * @param string $placeholder
     * @param string $class
     * @param string $style
     */
    protected function __construct($id, $title, $name, $disabled, $required, $defaultValue, $placeholder, $class, $style)
    {
        parent::__construct($id, $title, self::INPUT_TYPE, $name, $class, $style);
        $this->disabled     = filter_var($disabled, FILTER_VALIDATE_BOOLEAN);
        $this->required     = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->defaultValue = $defaultValue;
        $this->placeholder  = $placeholder;
    }

    /**
     * @param string $json
     *
     * @return TextInputField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->input_type != self::INPUT_TYPE) {
            throw new Exception('Incorrect input type');
        }
        return new TextInputField(
            $values->id,
            $values->title,
            $values->name,
            $values->disabled,
            $values->required,
            $values->default_value,
            $values->placeholder,
            $values->class,
            $values->style
        );
    }

    /**
     * @param bool $encode
     *
*@return string the class as JSON object.
     */
    public function toJSON($encode = true)
    {
        $values = array(
            'id'            => $this->id,
            'title'         => $this->title,
            'field_type'    => $this->fieldType,
            'input_type'    => $this->inputType,
            'name'          => $this->name,
            'disabled'      => $this->disabled,
            'required'      => $this->required,
            'default_value' => $this->defaultValue,
            'placeholder'   => $this->placeholder,
            'class'         => $this->class,
            'style'         => $this->style,
        );
        if ($encode) {
            $values = json_encode($values);
        }
        return $values;
    }

    /**
     * @return string the field as HTML object.
     */
    public function getHTML()
    {
        $value       = isset($this->value) ? $this->value : $this->defaultValue;
        $id          = !empty($this->id) ? 'id="' . $this->id . '"' : '';
        $name        = !empty($this->name) ? 'name="' . $this->name . '"' : '';
        $class       = !empty($this->class) ? 'class="validate ' . $this->class . '"' : 'class="validate"';
        $style       = !empty($this->style) ? 'style="' . $this->style . '"' : '';
        $placeholder = !empty($this->placeholder) ? 'placeholder="' . $this->placeholder . '"' : '';
        $value       = !empty($value) ? 'value="' . $value . '"' : '';
        $display     = $this->disabled ? 'disabled' : '';
        $required    = $this->required == "true" ? 'required' : '';

        ob_start();
        if (current_theme_supports('materialize')) {
            ?>
            <div class="input-field">
                <input type="text" <?= $id ?> <?= $name ?> <?= $class ?> <?= $style ?> <?= $value ?> <?= $display ?> <?= $placeholder ?> <?= $required ?> title="<?= $this->title ?>"/>
                <label><?php echo $this->title; ?><?= $this->required == "yes" ? '*' : '' ?></label>
            </div>
            <?php
        }

        return trim(preg_replace('/\s\s+/', ' ', ob_get_clean()));
    }

    /**
     * @return Message[]|bool array of errors or true if no errors.
     */
    public function isValid()
    {
        $errors = array();
        if (($this->required && !$this->disabled) && (empty($this->value))) {
            $errors[] = new Message('This field is required but not set.', Message::ERROR_MESSAGE);
        }
        return empty($errors) ? true : $errors;
    }
}
