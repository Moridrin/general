<?php

/**
 * Created by PhpStorm.
 * User: moridrin
 * Date: 6-1-17
 * Time: 6:38
 */
class HeaderField extends Field
{
    const FIELD_TYPE = 'header';

    /**
     * HeaderField constructor.
     *
     * @param int    $id
     * @param string $title
     */
    protected function __construct($id, $title)
    {
        parent::__construct($id, $title, self::FIELD_TYPE);
    }

    /**
     * @param string $json
     *
     * @return HeaderField
     * @throws Exception
     */
    public static function fromJSON($json)
    {
        $values = json_decode($json);
        if ($values->field_type != self::FIELD_TYPE) {
            throw new Exception('Incorrect field type');
        }
        return new HeaderField(
            $values->id,
            $values->title
        );
    }

    /**
     * @return string the class as JSON object.
     */
    public function toJSON()
    {
        $values = array(
            'id'        => $this->id,
            'title'     => $this->title,
            'field_type' => $this->fieldType,
        );
        return json_encode($values);
    }
}
