<?php
/**
 * Sexo Active Record
 * @author  <your-name-here>
 */
class Sexo extends TRecord
{
    const TABLENAME = 'sexo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('abreviacao');
    }
}