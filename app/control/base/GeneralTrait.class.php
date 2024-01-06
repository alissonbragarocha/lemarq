<?php

trait GeneralTrait
{
    private static function setFocus($campo)
    {
        TScript::create(str_replace('{campo}', $campo, 'setTimeout(function() { $("input[name=\'{campo}\']").focus() }, 500);'));
    }

    private static function setCloseEscape()
    {
        TScript::create("
            $(document).ready(function() {
                $(document).bind('keydown', function(e) {
                    if (e.which == 27) {
                        Template.closeRightPanel();
                    }
                });
            });
        "); // Fecha cortina lateral ao precionar ESC.
    }
    
    public function formatarMoeda($value, $object, $row)
    {
        if (!empty($value))
            return number_format($value, 2, ',', '.');
        return ;
    }
    
    public function formatarDataHora($value, $object, $row)
    {
        if (!empty($value))
            return (new DateTime($value))->format('d/m/Y H:i:s');
        return ;
    }
    
    public function formatarData($value, $object, $row)
    {
        if (!empty($value))
            return (new DateTime($value))->format('d/m/Y');
        return ;
    }
}
