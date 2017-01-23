<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 6-1-17
 * Time: 6:38
 */
class TabField extends Field
{
    const FIELD_TYPE = 'tab';

    public $fields;

    /**
     * TabField constructor.
     *
     * @param int     $id
     * @param string  $title
     * @param string  $class
     * @param string  $style
     * @param Field[] $fields
     */
    protected function __construct($id, $title, $class, $style, $fields = array())
    {
        parent::__construct($id, $title, self::FIELD_TYPE, $class, $style);
        $this->fields = $fields;
    }

    /**
     * @param Field $field
     */
    public function addField($field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param string $json
     *
     * @return TabField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->field_type != self::FIELD_TYPE) {
            throw new Exception('Incorrect field type');
        }
        return new TabField(
            $values->id,
            $values->title,
            $values->class,
            $values->style,
            isset($values->fields) ? $values->fields : array()
        );
    }

    /**
     * @param bool $encode
     *
*@return string the class as JSON object.
     */
    public function toJSON($encode = true)
    {
        $jsonFields = array();
        foreach ($this->fields as $field) {
            $jsonFields[] = $field->toJSON(false);
        }
        $values = array(
            'id'         => $this->id,
            'title'      => $this->title,
            'field_type' => $this->fieldType,
            'class'      => $this->class,
            'style'      => $this->style,
            'fields'     => $jsonFields,
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
        $class = !empty($this->class) ? 'class="tab ' . $this->class . '"' : 'class="tab"';
        $style = !empty($this->style) ? 'style="' . $this->style . '"' : '';
        ob_start();
        ?>
        <div class="col s12">
            <ul class="tabs">
                <li <?= $class ?>><a href="#test1"><?= $this->title ?></a></li>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}
