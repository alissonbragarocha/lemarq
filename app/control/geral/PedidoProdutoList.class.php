<?php

class PedidoProdutoList extends TPage
{
    private $datagrid;
    private $database     = 'lemarq';
    private $activeRecord = 'Pedidoproduto';
    
    use ListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct($param)
    {
        parent::__construct();

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_produto_id = new TDataGridColumn('produto->descricao', 'Produto', 'ledt');
        $column_valor_compra = new TDataGridColumn('valor_compra', 'Valor de Compra', 'right');
        $column_valor_venda = new TDataGridColumn('valor_venda', 'Valor de Venda', 'right');
        $column_usuario_cadastro_id = new TDataGridColumn('usuario_cadastro->name', 'Usuario de cadastro', 'left');
        $column_data_cadastro = new TDataGridColumn('data_cadastro', 'Data de Cadastro', 'left');
        
        $column_data_cadastro->setTransformer( [$this, 'formatarDataHora'] );

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => $this->keyField]);

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_produto_id);
        $this->datagrid->addColumn($column_valor_compra);
        $this->datagrid->addColumn($column_valor_venda);
        $this->datagrid->addColumn($column_data_cadastro);
        $this->datagrid->addColumn($column_usuario_cadastro_id);
        
        // cria o modelo da DataGrid, montando sua estrutura
        $this->datagrid->createModel();
        
        // cria o paginador
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload'), $param));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid)->style='overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add($panel);
        
        parent::add($container);
    }
    
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            // creates a repository for Produto
            $repository = new TRepository($this->activeRecord);

            $limit = 10;

            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'desc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            $criteria->add(new TFilter('pedido_id', '=', $param['key']));
            
            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);
            
            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }
            
            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count = $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onShow($param = null)
    {

    }
}