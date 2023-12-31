<?php
/**
 * SystemTableList
 *
 * @version    7.6
 * @package    control
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class SystemTableList extends TPage
{
    private $datagrid;
    
    /**
     * Constructor method
     */
    public function __construct($param)
    {
        parent::__construct();
        
        // define the ID of target container
        $this->adianti_target_container = 'table_list_container';
        $this->style = 'height: 100%';
        
        // create datagrid
        $this->cards = new TIconView;
        $this->cards->setIconAttribute('icon');
        $this->cards->setLabelAttribute('title');
        $this->cards->enableDoubleClick();
        $this->cards->setInfoAttributes(['table', 'id', 'database']);
        $this->cards->setItemTemplate(file_get_contents( 'app/resources/system_file_item.html'));

        $action1 = new TAction(array('SystemDataBrowser', 'onLoad'));
        $action1->setParameter('register_state', 'false');
        $action1->setParameter('table', '{table}');
        $action1->setParameter('database', '{database}');

        $action2 = new TAction(array($this, 'onExportCSV'));
        $action2->setParameter('register_state', 'false');
        $action2->setParameter('table', '{table}');
        $action2->setParameter('database', '{database}');

        $action3 = new TAction(array($this, 'onExportSQL'));
        $action3->setParameter('register_state', 'false');
        $action3->setParameter('table', '{table}');
        $action3->setParameter('database', '{database}');
        
        $this->cards->addContextMenuOption('View', $action1, 'fa:table');
        $this->cards->addContextMenuOption('CSV', $action2, 'fa:download');
        $this->cards->addContextMenuOption('SQL', $action3, 'fa:code');
        
        $input_search = new TEntry('input_search');
        $input_search->placeholder = _t('Search');
        $input_search->setSize('100%');
        
        $hbox = new THBox;
        $hbox->style = 'display:flex; flex-direction: row;';
        $hbox->add( _t('Tables') )->style = 'flex: 1;';
        $hbox->add( $input_search )->style = 'flex: 1;';
        
        // panel group around datagrid
        $panel = new TPanelGroup( $hbox );
        $panel->style = 'padding-bottom:8px; height: 100%; position: unset';
        $panel->getBody()->style = 'overflow-y:auto';
        $panel->add($this->cards);
        
        parent::add($panel);
    }
    
    /**
     * Load tables into datagrid
     */
    public function onLoad($param)
    {
        try
        {
            $tables = SystemDatabaseInformationService::getDatabaseTables( $param['database'] );
            if ($tables)
            {
                foreach ($tables as $table)
                {
                    $this->cards->addItem( (object) ['title' => $table, 'id' => $table, 'table' => $table, 'database' => $param['database'], 'icon' => 'fas fa-table' ]);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     *
     */
    public static function onExportCSV($param)
    {
        try
        {
            $database = $param['database'];
            $table    = $param['table'];
            
            if (!is_writable('tmp'))
            {
                throw new Exception( _t('Permission denied') . ': tmp');
            }
            
            // open transaction
            TTransaction::open( $database );
            $conn = TTransaction::get();
            
            // run the main query
            $sql = new TSqlSelect;
            $sql->setCriteria(new TCriteria);
            $sql->addColumn('*');
            $sql->setEntity($table);
            $result = $conn->query( $sql->getInstruction() );
            
            $file = 'tmp/' . $table . '.csv';
            $handler = fopen($file, 'w');
            
            $first_row = $result->fetch( PDO::FETCH_ASSOC );
            if ($first_row)
            {
                // CSV headers
                fputcsv($handler, array_keys($first_row));
                fputcsv($handler, $first_row);
                
                // add other rows
                while ($row = $result->fetch( PDO::FETCH_ASSOC ))
                {
                    fputcsv($handler, $row);
                }
                
                fclose($handler);
                parent::openFile($file);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function show()
    {
        parent::show();

        TScript::create('$(".item-ellipsis").on("click", function(event) {
            $(".popover").popover("hide");
            $(".dropdown-iconview").hide();
            var context_menu = $(this).closest("li").next("ul");
            context_menu.css("left", ticonview_mouseX(event));
            context_menu.css("top", ticonview_mouseY(event));
            context_menu.show();
            event.preventDefault();
            event.stopPropagation();
            return false;
            });

            __adianti_input_fuse_search("[name=input_search]", "table", ".ticonview li");
        ');
    }
    
    /**
     *
     */
    public static function onExportSQL($param)
    {
        try
        {
            $database = $param['database'];
            $table    = $param['table'];
            
            if (!is_writable('tmp'))
            {
                throw new Exception( _t('Permission denied') . ': tmp');
            }
            
            // open transaction
            TTransaction::open( $database );
            $conn = TTransaction::get();
            
            // run the main query
            $sql = new TSqlSelect;
            $sql->setCriteria(new TCriteria);
            $sql->addColumn('*');
            $sql->setEntity($table);
            $result = $conn->query( $sql->getInstruction() );
            
            $file = 'tmp/' . $table . '.sql.txt';
            $handler = fopen($file, 'w');
            
            $addquotes = function($value) {
                            if(!is_numeric($value)) {
                                return "'{$value}'";
                            } else {
                                return $value;
                            }
                        };
                        
            $first_row = $result->fetch( PDO::FETCH_ASSOC );
            if ($first_row)
            {
                $columns = implode(',', array_keys($first_row));
                $values  = implode(',', array_map($addquotes, array_values($first_row)));
                fwrite($handler, "INSERT INTO {$table} ({$columns}) VALUES ({$values});\n");
                
                // add other rows
                while ($row = $result->fetch( PDO::FETCH_ASSOC ))
                {
                    $values  = implode(',', array_map($addquotes, array_values($row)));
                    fwrite($handler, "INSERT INTO {$table} ({$columns}) VALUES ({$values});\n");
                }
                
                fclose($handler);
                parent::openFile($file);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
