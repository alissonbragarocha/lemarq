<?php
/**
 * Cliente Active Record
 * @author  <your-name-here>
 */
class Cliente extends TRecord
{
    const TABLENAME = 'cliente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}    
    
    private $sexo;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('data_nascimento');
        parent::addAttribute('sexo_id');
    }
    
    /**
     * Method set_sexo
     * Sample of usage: $cliente->sexo = $object;
     * @param $object Instance of Sexo
     */
    public function set_sexo(Sexo $object)
    {
        $this->sexo = $object;
        $this->sexo_id = $object->id;
    }
        
    /**
     * Method get_sexo
     * Sample of usage: $cliente->sexo->attribute;
     * @returns Sexo instance
     */
    public function get_sexo()
    {
        // loads the associated object
        if (empty($this->sexo))
            $this->sexo = new Sexo($this->sexo_id);
    
        // returns the associated object
        return $this->sexo;
    }
}