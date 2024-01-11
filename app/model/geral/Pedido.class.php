<?php
/**
 * Pedido Active Record
 * @author  <your-name-here>
 */
class Pedido extends TRecord
{
    const TABLENAME = 'pedido';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}

    use SystemChangeLogTrait;
    
    private $cliente;
    private $usuario_cadastro;
    private $pedido_produtos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cliente_id');
        parent::addAttribute('data_cadastro');
        parent::addAttribute('usuario_cadastro_id');
    }
    
    /**
     * Method set_cliente
     * Sample of usage: $pedido->cliente = $object;
     * @param $object Instance of Cliente
     */
    public function set_cliente(Cliente $object)
    {
        $this->cliente = $object;
        $this->cliente_id = $object->id;
    }
    
    /**
     * Method get_cliente
     * Sample of usage: $pedido->cliente->attribute;
     * @returns Cliente instance
     */
    public function get_cliente()
    {
        // loads the associated object
        if (empty($this->cliente))
            $this->cliente = new Cliente($this->cliente_id);
    
        // returns the associated object
        return $this->cliente;
    }    
    
    /**
     * Method set_usuario_cadastro
     * Sample of usage: $pedido->usuario_cadastro = $object;
     * @param $object Instance of SystemUser
     */
    public function set_usuario_cadastro(SystemUser $object)
    {
        $this->usuario_cadastro = $object;
        $this->usuario_cadastro_id = $object->id;
    }
    
    /**
     * Method get_usuario_cadastro
     * Sample of usage: $pedido->usuario_cadastro->attribute;
     * @returns SystemUser instance
     */
    public function get_usuario_cadastro()
    {
        // loads the associated object
        if (empty($this->usuario_cadastro)) {
            TTransaction::open('permission');
            $this->usuario_cadastro = new SystemUser($this->usuario_cadastro_id);
            TTransaction::close();
        }
    
        // returns the associated object
        return $this->usuario_cadastro;
    }
    
    /**
     * Method addPedidoProduto
     * Add a PedidoProduto to the Pedido
     * @param $object Instance of PedidoProduto
     */
    public function addPedidoProduto(PedidoProduto $object)
    {
        $this->pedido_produtos[] = $object;
        $object->pedido_id = $this->id;
        $object->store();
    }
    
    /**
     * Method getPedidoProdutos
     * Return the Pedido' PedidoProduto's
     * @return Collection of PedidoProduto
     */
    public function getPedidoProdutos()
    {
        return $this->pedido_produtos;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->pedido_produtos = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {    
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pedido_id', '=', $id));
        
        // load the related PedidoProduto objects
        $repository = new TRepository('PedidoProduto');
        $this->pedido_produtos = $repository->load($criteria);
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pedido_id', '=', $id));

        // delete the related PedidoProduto objects
        $repository = new TRepository('PedidoProduto');
        $repository->delete($criteria);        
    
        // delete the object itself
        parent::delete($id);
    }
}