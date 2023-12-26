<?php
/**
 * PedidoProduto Active Record
 * @author  <your-name-here>
 */
class PedidoProduto extends TRecord
{
    const TABLENAME = 'pedido_produto';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    private $pedido;
    private $produto;
    private $usuario_cadastro;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pedido_id');
        parent::addAttribute('produto_id');
        parent::addAttribute('valor_compra');
        parent::addAttribute('valor_venda');
        parent::addAttribute('data_cadastro');
        parent::addAttribute('usuario_cadastro_id');
    }
    
    /**
     * Method set_pedido
     * Sample of usage: $pedido_produto->pedido = $object;
     * @param $object Instance of Pedido
     */
    public function set_pedido(Pedido $object)
    {
        $this->pedido = $object;
        $this->pedido_id = $object->id;
    }
    
    /**
     * Method get_pedido
     * Sample of usage: $pedido_produto->pedido->attribute;
     * @returns Pedido instance
     */
    public function get_pedido()
    {
        // loads the associated object
        if (empty($this->pedido))
            $this->pedido = new Pedido($this->pedido_id);
    
        // returns the associated object
        return $this->pedido;
    }
    
    
    /**
     * Method set_produto
     * Sample of usage: $pedido_produto->produto = $object;
     * @param $object Instance of Produto
     */
    public function set_produto(Produto $object)
    {
        $this->produto = $object;
        $this->produto_id = $object->id;
    }
    
    /**
     * Method get_produto
     * Sample of usage: $pedido_produto->produto->attribute;
     * @returns Produto instance
     */
    public function get_produto()
    {
        // loads the associated object
        if (empty($this->produto))
            $this->produto = new Produto($this->produto_id);
    
        // returns the associated object
        return $this->produto;
    }    
    
    /**
     * Method set_usuario_cadastro
     * Sample of usage: $pedido_produto->usuario_cadastro = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario_cadastro(SystemUser $object)
    {
        $this->usuario_cadastro = $object;
        $this->usuario_cadastro_id = $object->id;
    }
    
    /**
     * Method get_usuario_cadastro
     * Sample of usage: $pedido_produto->usuario_cadastro->attribute;
     * @returns SystemUser instance
     */
    public function get_usuario_cadastro()
    {
        // loads the associated object
        if (empty($this->usuario_cadastro))
            $this->usuario_cadastro = new SystemUser($this->usuario_cadastro_id);
    
        // returns the associated object
        return $this->usuario_cadastro;
    }
}