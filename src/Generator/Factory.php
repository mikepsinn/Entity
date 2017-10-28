<?php

namespace GooGee\Entity\Generator;


class Factory
{

    public $fileName;
    public $filePath;
    public $table;
    public $modelName;
    public $fieldList = [];

    function __construct($entity)
    {
        $factory = $entity->factory;
        $this->fileName = $factory->name . '.php';
        $this->filePath = base_path($factory->path . DIRECTORY_SEPARATOR) . $this->fileName;

        $this->table = $entity->table;
        $this->modelName = $entity->model->nameSpace . '\\' . $entity->model->name;
        $this->loadField($factory->field->list);
    }

    function loadField($list)
    {
        foreach ($list as $field) {
            if ('property' == $field->type) {
                $this->setProperty($field);
            } else if ('method' == $field->type) {
                $this->setMethod($field);
            } else {
                $this->setRaw($field);
            }
        }
    }

    function setProperty($field)
    {
        if (empty($field->property)) {
            return;
        }
        $this->fieldList[] = "'$field->name' => \$faker->$field->property,";
    }

    function setMethod($field)
    {
        if (empty($field->method)) {
            return;
        }

        $parameters = '';
        if (isset($field->parameters)) {
            $parameters = $field->parameters;
        }
        $this->fieldList[] = "'$field->name' => \$faker->$field->method($parameters),";
    }

    function setRaw($field)
    {
        if (empty($field->raw)) {
            return;
        }
        $this->fieldList[] = "'$field->name' => $field->raw,";
    }

    public function save()
    {
        $factory = $this;
        $view = view('template::factory', compact('factory'));

        $file = new File($this->filePath);
        $file->save("<?php \n" . $view->render());
    }

}