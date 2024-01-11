<?php

use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Wrapper\TDBUniqueSearch;

/**
 * PedidoList Listing
 * @author  <your name here>
 */
class PedidoList extends TPage
{
    private $quick_search; // form
    private $filterForm; // form
    private $filter_label;
    private $panel;
    private $datagrid; // listing
    private $pageNavigation;
    private $loaded;
	private $counter;
    private $selectAll;

    private $database         = 'lemarq';
    private $activeRecord     = 'Pedido';
    private $applicationTitle = 'Listagem de pedidos';
    private $filterFormTitle  = 'Filtro de pedidos';
    private $editForm         = 'PedidoForm';
    private $keyField         = 'id';
    private $fieldFocus       = 'input_quick_search';
    private $limit;
    private $limit_padrao = 10; // 10, 15, 20, 50, 100
    private $qtd_filtros = 0;

    use ListTrait;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        
        // creates the form
        $this->filterForm = new BootstrapFormBuilder('form_search_'.$this->activeRecord);
        $this->filterForm->setFormTitle('<i class="fa fa-filter"></i> '.new TLabel($this->filterFormTitle, '#000000', 12, 'b'));        
        
        // create the form fields
        $input_quick_search = new TEntry('input_quick_search');
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', $this->database, 'Cliente', 'id', 'nome');
        $usuario_cadastro_id = new TDBUniqueSearch('usuario_cadastro_id', $this->database, 'SystemUser', 'id', 'name');

        // set sizes
        $input_quick_search->setSize('200');
        $id->setSize('100%');
        $cliente_id->setSize('100%');
        $usuario_cadastro_id->setSize('100%');

        $input_quick_search->placeholder = 'Buscar';

        // add the fields
        $this->filterForm->addFields( [ new TLabel('Id'), $id ] );
        $this->filterForm->addFields( [ new TLabel('Cliente'), $cliente_id ] );
        $this->filterForm->addFields( [ new TLabel('Usuário de cadastro'), $usuario_cadastro_id ] );
        
        $btnQS = TButton::create('find', [$this, 'onSearchQS'], '', 'fa:search');
        $btnQS->style= 'height: 37px;';
        $btnQS->{'title'} = 'Buscar';
        $btnClearQS = TButton::create('clear', [$this, 'onClearQS'], '', 'fa:ban red');
        $btnClearQS->style= 'width: 32px; height: 37px;';
        $btnClearQS->{'title'} = 'Limpar filtro';
        
        $this->quick_search = new TForm('quick_search');
        $this->quick_search->style = 'float:left;display:flex';
        $this->quick_search->add($input_quick_search, true);
        $this->quick_search->add($btnQS, true);
        $this->quick_search->add($btnClearQS, true);
        
        // keep the form filled during navigation with session data
        $this->quick_search->setData( TSession::getValue(__CLASS__.'_filter_dataQS') );
        $this->filterForm->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->filterForm->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->filterForm->addAction('Limpar', new TAction([$this, 'onClear']), 'fa:times red');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';

        // creates the datagrid columns
        $column_id = new TDataGridColumn($this->keyField, 'Id', 'right', 30);
        $column_cliente_id = new TDataGridColumn('cliente->nome', 'Cliente', 'left');
        $column_usuario_cadastro_id = new TDataGridColumn('usuario_cadastro->name', 'Usuário de cadastro', 'left');
        $column_data_cadastro = new TDataGridColumn('data_cadastro', 'Data', 'center');

        $column_id->setTransformer([$this, 'formatRowSelected'] );
        $column_data_cadastro->setTransformer( [$this, 'formatarDataHora'] );

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_id);
        $this->datagrid->addColumn($column_usuario_cadastro_id);
        $this->datagrid->addColumn($column_data_cadastro);

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => $this->keyField]);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'descricao']);
        $column_usuario_cadastro_id->setAction(new TAction([$this, 'onReload']), ['order' => 'valor_compra']);
        $column_data_cadastro->setAction(new TAction([$this, 'onReload']), ['order' => 'valor_venda']);

        $action_select = new TDataGridAction([$this, 'onSelect'], [$this->keyField => '{'.$this->keyField.'}', 'register_state' => 'false']);
        $action_edit   = new TDataGridAction([$this->editForm, 'onEdit'], [$this->keyField => '{'.$this->keyField.'}', 'register_state' => 'false']);
        $action_delete = new TDataGridAction([$this, 'onDelete'], [$this->keyField => '{'.$this->keyField.'}', 'register_state' => 'false']);
        
        $this->datagrid->addAction($action_select, 'Selecionar', 'far:square fa-fw black');
        $this->datagrid->addAction($action_edit,   _t('Edit'),   'far:edit blue');
        $this->datagrid->addAction($action_delete, _t('Delete'), 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $this->onCreatePanelList();
                
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->panel);
        
        parent::add($container);
    }
    
    public function onClearSession()
    {
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_id',                  NULL);
        TSession::setValue(__CLASS__.'_filter_cliente_id',          NULL);
        TSession::setValue(__CLASS__.'_filter_usuario_cadastro_id', NULL);

        TSession::setValue(__CLASS__.'_filter_data',               NULL);
        TSession::setValue(__CLASS__.'_filter_counter',            0);
    }
    
    public static function onClose()
    {
        TScript::create("Template.closeRightPanel()");
        TScript::create('$("input[name=\'input_quick_search\']").focus();');
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearchQS()
    {
        // get the search form data
        $dataQS = $this->quick_search->getData();
        
        $this->onClearSessionQS();
        $this->onClearSessionSelectList();
        self::clearNavigation();

        if (isset($dataQS->input_quick_search) AND ($dataQS->input_quick_search)) {
            $filterQS = [];
            $filterQS[] = new TFilter($this->keyField, '=',    $dataQS->input_quick_search); // create the filter
            TSession::setValue(__CLASS__.'_filter_input_quick_search',        $filterQS); // stores the filter in the session
        }
        
        // fill the form with data again
        $this->quick_search->setData($dataQS);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_dataQS', $dataQS);

        $this->resetParamAndOnReload();
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->filterForm->getData();
        
        $this->onClearSession();
        $this->onClearSessionSelectList();
        self::clearNavigation();

        if (isset($data->id) AND ($data->id)) {
            $filter = new TFilter($this->keyField, '=', $data->id); // create the filter
            TSession::setValue(__CLASS__.'_filter_id',   $filter); // stores the filter in the session
            $this->qtd_filtros++;
        }

        if (isset($data->cliente_id) AND ($data->cliente_id)) {
            $filter = new TFilter('cliente_id', '=', $data->cliente_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_cliente_id',   $filter); // stores the filter in the session
            $this->qtd_filtros++;           
        }

        if (isset($data->usuario_cadastro_id) AND ($data->usuario_cadastro_id)) {
            $filter = new TFilter('usuario_cadastro_id', '=', $data->usuario_cadastro_id); // create the filter
            TSession::setValue(__CLASS__.'_filter_usuario_cadastro_id',   $filter); // stores the filter in the session
            $this->qtd_filtros++;
        }
        
        TSession::setValue(__CLASS__.'_filter_counter', $this->qtd_filtros);
        
        // fill the form with data again
        $this->filterForm->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__.'_filter_data', $data);
        
        $this->onUpdateBtnFilter();
        $this->resetParamAndOnReload();
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            // creates a repository for Pedido
            $repository = new TRepository($this->activeRecord);

            $limit = TSession::getValue(__CLASS__.'_filter_limit') ?? $this->limit_padrao;

            // creates a criteria
            $criteria = new TCriteria;
            $criteria_or = new TCriteria;
            
            // atualiza ou recupera os parametros de paginação com dados da sessão
            $param = $this->keepNavigation($param);
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = $this->keyField;
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            if (TSession::getValue(__CLASS__.'_filter_input_quick_search')) {
                foreach (TSession::getValue(__CLASS__.'_filter_input_quick_search') as $filter) {
                    $criteria_or->add($filter, TExpression::OR_OPERATOR); // add the session filter
                }
                $criteria->add($criteria_or, TExpression::AND_OPERATOR);
            }

            if (TSession::getValue(__CLASS__.'_filter_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_id')); // add the session filter
            }

            if (TSession::getValue(__CLASS__.'_filter_cliente_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_cliente_id')); // add the session filter
            }

            if (TSession::getValue(__CLASS__.'_filter_usuario_cadastro_id')) {
                $criteria->add(TSession::getValue(__CLASS__.'_filter_usuario_cadastro_id')); // add the session filter
            }
            
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

            $this->onUpdateCounter($count);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            if ($this->limit === 0)
                $objects = $repository->load($criteria, FALSE);
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;

            return $objects;
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onShow()
    {
        
    }
}
