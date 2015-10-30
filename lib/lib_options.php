<?php

class OptionTypes {
    const BINARY = 1;
    const KEY_VALUE = 2;
}

class UserOption {
    public $id;
    public $type;
    public $is_active = true;  // active options can be changed by user
    public $values;  // for KEY_VALUE type
    public $default_value = 1;
    public $caption;

    public function __construct($id, $type=OptionTypes::BINARY) {
        $this->id = $id;
        $this->type = $type;
    }
}

class UserOptionsManager {
    private $options;

    public function __construct() {
        $opt = new UserOption(1);
        $opt->caption = "Показывать русские названия граммем";
        $this->options[] = $opt;

        $opt = new UserOption(2, OptionTypes::KEY_VALUE);
        $opt->values = array(1 => "Русский", 2 => "English");
        $opt->caption = "Язык/Language";
        $opt->is_active = false;
        $this->options[] = $opt;

        $opt = new UserOption(3, OptionTypes::KEY_VALUE);
        $opt->values = array(1 => 5, 2 => 10, 3 => 20, 4 => 50);
        $opt->default_value = 2;
        $opt->caption = "Количество примеров для разметки";
        $this->options[] = $opt;

        $opt = new UserOption(4);
        $opt->caption = "Разбивать пулы на страницы при модерации";
        $this->options[] = $opt;

        $opt = new UserOption(5);
        $opt->default_value = 0;
        $opt->caption = "Быстрый режим разметки именованных сущностей";
        $this->options[] = $opt;

        $opt = new UserOption(6, OptionTypes::KEY_VALUE);
        $opt->values = array(1 => "Default (2014)", 2 => "Dialogue Eval (2016)");
        $opt->caption = "Инструкция (tagset) разметки NER";
        $this->options[] = $opt;

        $opt = new UserOption(7);
        $opt->caption = "Включить геймификацию";
        $this->options[] = $opt;
    }

    public function get_all_options($only_active=false) {
        $out = array();
        foreach ($this->options as $opt) {
            if (!$only_active || $opt->is_active)
                $out[$opt->id] = array(
                    'name' => $opt->caption,
                    'value_type' => $opt->type,
                    'values' => $opt->values
                );
        }
        return $out;
    }

    public function get_user_options($user_id) {
        if (!$user_id)
            throw new UnexpectedValueException();
        $out = array();

        $res = sql_query("SELECT option_id id, option_value value FROM user_options_values WHERE user_id=$user_id");
        while ($r = sql_fetch_array($res))
            $out[$r['id']] = $r['value'];

        //autovivify absent options
        sql_begin();
        $ins = sql_prepare("INSERT INTO user_options_values VALUES(?, ?, ?)");

        foreach ($this->options as $opt) {
            if (!in_array($opt->id, array_keys($out))) {
                $out[$opt->id] = $opt->default_value;
                sql_execute($ins, array($user_id, $opt->id, $opt->default_value));
            }
        }
        sql_commit();

        return $out;
    }
}
