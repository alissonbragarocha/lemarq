<?php

trait ListTrait
{
    use GeneralTrait;
    use Adianti\Base\AdiantiStandardListExportTrait;

    public function createSelector()
    {
        $this->selectAll = new TButton('bt_marcar');
        $this->selectAll->setAction(new TAction(array($this, 'onSelectAll')));        
        $this->filterForm->addField($this->selectAll);
        
        $this->counter = new TLabel('', NULL, 9, 'b');
        $this->counter->style = 'margin-left: 6px;';

        $selectors = new THBox;
        $selectors->style = 'width: 100%';
        $selectors->add($this->selectAll);
        $selectors->add($this->counter);

        $this->onUpdateBtnSelectAll();

        return $selectors;
    }

    public function onUpdateCounter($count)
	{
        if (TSession::getValue(__CLASS__.'selected_ids') == NULL)
        {
            $this->counter->setValue($count.' registro(s)');
        }
        else
        {
            $count_selected = count(TSession::getValue(__CLASS__.'selected_ids'));
            $this->counter->setValue($count.' registro(s) / '.$count_selected.' selecionado(s)');
        }	    
	}
	
	public function onUpdateBtnSelectAll()
	{
	    $selected_ids = TSession::getValue(__CLASS__.'selected_ids');
        
        if (isset($selected_ids) && !empty($selected_ids))
        {
            $this->selectAll->setImage('far:check-square');
            $this->selectAll->{'title'} = 'Desmarcar todos';
        }
        else
        {
            $this->selectAll->setImage('far:square');
            $this->selectAll->{'title'} = 'Marcar todos';
        }        
	}
    
    public function onSelectAll($param)
    {
        $selected_ids = TSession::getValue(__CLASS__.'selected_ids');
        
        if (isset($selected_ids) && !empty($selected_ids))
        {
            TSession::setValue(__CLASS__.'selected_ids', NULL);
        }
        else
        {
            $this->limit = 0;
            foreach ($this->onReload($param) as $item)
            {
                $selected_ids[$item->{$this->keyField}] = $item->{$this->keyField};
            }
            TSession::setValue(__CLASS__.'selected_ids', $selected_ids);
        }

        $this->onUpdateBtnSelectAll();        
        $this->onReload( func_get_arg(0) );
    }
    
    public function onSelect($param)
    {
        // get the selected objects from session 
        $selected_ids = TSession::getValue(__CLASS__.'selected_ids');
        
        if (isset($selected_ids[$param[$this->keyField]]))
        {
            unset($selected_ids[$param[$this->keyField]]);
        }
        else
        {
            $selected_ids[$param[$this->keyField]] = $param[$this->keyField];
        }
        TSession::setValue(__CLASS__.'selected_ids', $selected_ids); // put the array back to the session
        
        $this->onUpdateBtnSelectAll();
        $this->onReload( func_get_arg(0) );
    }

    public function formatRowSelected($value, $object, $row)
    {
        $selected_ids = TSession::getValue(__CLASS__.'selected_ids');
        
        if ($selected_ids)
        {
            if (in_array( (int) $value, $selected_ids ) )
            {
                $row->style = "background: #abdef9";
                
                $button = $row->find('i', ['class'=>'far fa-square fa-fw black'])[0];
                
                if ($button)
                {
                    $button->class = 'far fa-check-square fa-fw black';
                }
            }
        }
        
        return $value;
    }
    
    public function onCreateUpdateBtnFilter()
    {
        if (TSession::getValue(__CLASS__.'_filter_counter') > 0)
        {
            $this->filter_label->class = 'btn btn-primary';
            $this->filter_label->setLabel('Filtros ('. TSession::getValue(__CLASS__.'_filter_counter').')');
        }
        else
        {
            $this->filter_label->class = 'btn btn-default';
            $this->filter_label->setLabel('Filtros');
        }
    }
    
    public static function onShowFilters($param = null)
    {
        try
        {
            // create empty page for right panel
            $page = new TPage;
            $page->setTargetContainer('adianti_right_panel');
            $page->setProperty('override', 'true');
            $page->setPageName(__CLASS__);
            
            $btn_close = new TButton('closeCurtain');
            $btn_close->onClick = "Template.closeRightPanel();";
            $btn_close->setLabel("Fechar");
            $btn_close->setImage('fas:times red');
            
            // instantiate self class, populate filters in construct 
            $embed = new self;
            $embed->filterForm->addHeaderWidget($btn_close);
            
            // embed form inside curtain
            $page->add($embed->filterForm);
            $page->setIsWrapped(true);
            $page->show();

            self::setCloseEscape();
        }
        catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());    
        }
    }

    public function onClearQS($param = NULL)
    {
        $this->quick_search->clear(FALSE);
        $this->onClearSessionQS();
        self::clearNavigation();
        $this->resetParamAndOnReload();
    }
    
    public function onClearSessionQS()
    {
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_input_quick_search', NULL);
        TSession::setValue(__CLASS__.'_filter_dataQS',             NULL);
    }

    public function onClear($param = NULL)
    {
        $this->filterForm->clear(FALSE);
        $this->onClearSession();
        self::clearNavigation();
        $this->onCreateUpdateBtnFilter();
        $this->resetParamAndOnReload();
    }
    
    function onClearSessionSelectList()
    {        
        TSession::setValue(__CLASS__.'selected_ids', NULL);                
        $this->onUpdateBtnSelectAll();
    }

    private function resetParamAndOnReload()
    {
        $param = [];
        $param['offset']     = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    private static function clearOrderNavigation()
    {
        self::clearNavigation();
        self::clearOrder();
    }
    
    private static function clearNavigation()
    {
        TSession::setValue(__CLASS__.'_filter_offset', NULL);
        TSession::setValue(__CLASS__.'_filter_page', NULL);
        TSession::setValue(__CLASS__.'_filter_first_page', NULL);
    }
    
    private static function clearOrder()
    {
        TSession::setValue(__CLASS__.'_filter_order', NULL);
        TSession::setValue(__CLASS__.'_filter_direction', NULL);
        TSession::setValue(__CLASS__.'_filter_limit', NULL);
    }
    
    public static function onChangeLimit($param)
    {
        TSession::setValue(__CLASS__.'_filter_limit', $param['limit'] );
        self::clearNavigation();
        AdiantiCoreApplication::loadPage(__CLASS__, 'onReload');
    }
    
    private function keepNavigation($param)
    {
        if (!isset($param['order'])){
            if (TSession::getValue(__CLASS__.'_filter_order'))
                $param['order'] = TSession::getValue(__CLASS__.'_filter_order');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_order', $param['order']);
        }
        
        if (!isset($param['offset'])){
            if (TSession::getValue(__CLASS__.'_filter_offset'))
                $param['offset'] = TSession::getValue(__CLASS__.'_filter_offset');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_offset', $param['offset']);
        }
        
        if (!isset($param['limit'])){
            if (TSession::getValue(__CLASS__.'_filter_limit'))
                $param['limit'] = TSession::getValue(__CLASS__.'_filter_limit');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_limit', $param['limit']);
        }
        
        if (!isset($param['direction'])){
            if (TSession::getValue(__CLASS__.'_filter_direction'))
                $param['direction'] = TSession::getValue(__CLASS__.'_filter_direction');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_direction', $param['direction']);
        }
        
        if (!isset($param['page'])){
            if (TSession::getValue(__CLASS__.'_filter_page'))
                $param['page'] = TSession::getValue(__CLASS__.'_filter_page');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_page', $param['page']);
        }
        
        if (!isset($param['first_page'])){
            if (TSession::getValue(__CLASS__.'_filter_first_page'))
                $param['first_page'] = TSession::getValue(__CLASS__.'_filter_first_page');
        }
        else {
            TSession::setValue(__CLASS__.'_filter_first_page', $param['first_page']);
        }

        return $param;
    }

    public function onDropdownExport()
    {        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( 'Salvar como XLS', new TAction([$this, 'onExportXLS'], ['register_state' => 'false', 'static'=>'1']), 'fa:border-none fa-fw green' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $this->panel->addHeaderWidget( $dropdown );
    }

    public function onDropdownSelectLimit()
    {
        // header actions
        $dropdown = new TDropDown( TSession::getValue(__CLASS__.'_filter_limit') ?? $this->limit_padrao, '');
        $dropdown->style = 'height:37px';
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( 10,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '10']) );
        $dropdown->addAction( 15,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '15']) );
        $dropdown->addAction( 20,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '20']) );
        $dropdown->addAction( 50,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '50']) );
        $dropdown->addAction( 100,  new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '100']) );
        $this->panel->addHeaderWidget( $dropdown );
    }
    
    public function formatarMoeda($value, $object, $row)
    {
        $valor = number_format($value, 2, ',', '.');
        return 'R$ '.$valor;
    }
    
    /**
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion('Deseja realmente excluir?', $action, NULL, 'Atenção!');
    }
    
    /**
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            $key = $param['key']; // get the parameter $key
            TTransaction::open($this->database); // open a transaction with database
            $object = new ($this->activeRecord)($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            TApplication::loadPage(__CLASS__,'onReload', $param);
            TToast::show('error', AdiantiCoreTranslator::translate('Record deleted'), 'top right', 'fa:times' );
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    public function show()
    {
        self::setFocus($this->fieldFocus);
        
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}
