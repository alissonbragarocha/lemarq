<?php

trait FormTrait
{
    use GeneralTrait;

    public function show()
    {
        self::setFocus($this->fieldFocus);
        self::setCloseEscape(); // Fecha cortina lateral ao precionar ESC.

        parent::show();
    }
}
