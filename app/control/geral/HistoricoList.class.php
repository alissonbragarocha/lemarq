<?php

class HistoricoList extends TPage
{
    private $datagrid;

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

        $date       = new TDataGridColumn('logdate',    _t('Date'),   'center');
        $system_user_id = new TDataGridColumn('system_user->name',      'Usuário',   'center');
        $column     = new TDataGridColumn('columnname', _t('Column'), 'center');
        $operation  = new TDataGridColumn('operation',  _t('Operation'), 'center');
        $oldvalue   = new TDataGridColumn('oldvalue',   'Valor anterior', 'left');
        $newvalue   = new TDataGridColumn('newvalue',   _t('New value'), 'left');
        $access_ip  = new TDataGridColumn('access_ip',  'IP', 'center');
        
        $date->setTransformer( [$this, 'formatarDataHora'] );
        
        $operation->setTransformer( function($value, $object, $row) {
            $div = new TElement('span');
            $div->style="text-shadow:none; font-size:12px";
            if ($value == 'created')
            {
                $div->class="label label-success";
                $label = 'INSERIDO';
            }
            else if ($value == 'deleted')
            {
                $div->class="label label-danger";
                $label = 'DELETADO';
            }
            else if ($value == 'changed')
            {
                $div->class="label label-info";
                $label = 'ALTERADO';
            }
            $div->add($label);
            return $div;
        });
        
        $order = new TAction(array($this, 'onReload'), $param);        
        $order->setParameter('order', 'logdate');
        
        $date->setAction($order);
        
        // adiciona as colunas à DataGrid
        $this->datagrid->addColumn($date);
        $this->datagrid->addColumn($system_user_id);
        $this->datagrid->addColumn($column);
        $this->datagrid->addColumn($operation);
        $this->datagrid->addColumn($oldvalue);
        $this->datagrid->addColumn($newvalue);
        $this->datagrid->addColumn($access_ip);
        
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
            TTransaction::open('log');
            
            // creates a repository for Produto
            $repository = new TRepository('SystemChangeLog');

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

            $criteria->add(new TFilter('tablename', '=', $param['tablename']));
            $criteria->add(new TFilter('pkvalue', '=', $param['key']));
            
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