<?php
/**
 * Produto Active Record
 * @author  <your-name-here>
 */
class Produto extends TRecord
{
    const TABLENAME = 'produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    use SystemChangeLogTrait;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
        parent::addAttribute('valor_compra');
        parent::addAttribute('valor_venda');
    }
}
