<?php

trait ListTrait
{
    use GeneralTrait;
    use PanelListTrait;
    use Adianti\Base\AdiantiStandardListExportTrait;

    public static function onShowFilters($param = null)
    {
        try
        {
            // create empty page for right panel
            $page = new TPage;
            $page->setTargetContainer('adianti_right_panel');
            //$page->setProperty('override', 'true');
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
        $this->onUpdateBtnFilter();
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
