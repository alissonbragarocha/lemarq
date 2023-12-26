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
}
