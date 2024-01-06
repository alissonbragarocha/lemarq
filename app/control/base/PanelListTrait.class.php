<?php

trait PanelListTrait
{
    public function onEmptySpace()
    {
        $div = new TElement('div');
        $div->style = 'width:20px;';
        return $div;
    }
    
    public function onCreateSelectAll()
    {
        $this->selectAll = new TButton('bt_marcar');
        $this->selectAll->setAction(new TAction(array($this, 'onSelectAll')));        
        $this->filterForm->addField($this->selectAll);
        return $this->selectAll;
    }
    
    public function onUpdateBtnFilter()
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

    public function onCreateCounter()
    {
        $this->counter = new TLabel('', NULL, 9, 'b');
        $this->counter->style = 'margin-left: 6px;';
        return $this->counter;
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

    public function onUpdateCounter($count)
	{
        if (TSession::getValue(__CLASS__.'selected_ids') == NULL)
        {
            $this->counter->setValue($count.' registro(s).');
        }
        else
        {
            $count_selected = count(TSession::getValue(__CLASS__.'selected_ids'));
            $this->counter->setValue($count.' registro(s) / '.$count_selected.' selecionado(s).');
        }	    
	}

    public function formatRowSelected($value, $object, $row)
    {
        $selected_ids = TSession::getValue(__CLASS__.'selected_ids');
        
        if ($selected_ids)
        {
            if (in_array( (int) $object->id, $selected_ids ) )
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

    public function onCreatePanelList()
    {
        $this->panel = new TPanelGroup(new TLabel($this->applicationTitle, '#000000', 14, 'b'), 'white');
        $this->panel->addHeaderWidget($this->quick_search);
        $this->panel->addHeaderWidget($this->onEmptySpace());
        $this->filter_label = $this->panel->addHeaderActionLink('Filtros', new TAction([$this, 'onShowFilters']), 'fa:filter');
        
        $dropdownExport = new TDropDown(_t('Export'), 'fa:list');
        $dropdownExport->style = 'height:37px;';
        $dropdownExport->setPullSide('right');
        $dropdownExport->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdownExport->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdownExport->addAction( 'Salvar como XLS', new TAction([$this, 'onExportXLS'], ['register_state' => 'false', 'static'=>'1']), 'fa:border-none fa-fw green' );
        $dropdownExport->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdownExport->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $this->panel->addHeaderWidget( $dropdownExport );
        
        $this->panel->addHeaderWidget($this->onEmptySpace());

        $this->panel->addHeaderActionLink(_t('New'), new TAction([$this->editForm, 'onEdit']), 'fa:plus green');        
        
        $selector = new THBox;
        $selector->style = 'width: 100%';
        $selector->add($this->onCreateSelectAll());
        $selector->add($this->onCreateCounter());
        $this->panel->add($selector);
        $this->onUpdateBtnSelectAll();

        $this->panel->add($this->datagrid);

        $dropdownLimit = new TDropDown( TSession::getValue(__CLASS__.'_filter_limit') ?? $this->limit_padrao, '');
        $dropdownLimit->style = 'height:37px;';
        $dropdownLimit->setPullSide('left');
        $dropdownLimit->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdownLimit->addAction( 10,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '10']) );
        $dropdownLimit->addAction( 15,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '15']) );
        $dropdownLimit->addAction( 20,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '20']) );
        $dropdownLimit->addAction( 50,   new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '50']) );
        $dropdownLimit->addAction( 100,  new TAction([$this, 'onChangeLimit'], ['register_state' => 'false', 'static'=>'1', 'limit' => '100']) );
        $dropdownLimit->{'title'} = 'Registros por página';

        $thbox = new THBox;
        $thbox->style = 'width: 100%';
        $thbox->add($dropdownLimit);
        $thbox->add($this->onEmptySpace());
        $thbox->add($this->pageNavigation);
        $this->panel->addFooter($thbox);

        $this->onUpdateBtnFilter();
    }
}