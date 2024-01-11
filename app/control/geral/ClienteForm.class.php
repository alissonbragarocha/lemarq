<?php
/**
 * ClienteForm Form
 * @author  <your name here>
 */
class ClienteForm extends TPage
{
    protected $form; // form
    protected $datagrid;
    protected $historico;

    private $database       = 'lemarq';
    private $activeRecord   = 'Cliente';
    private $listView       = 'ClienteList';
    private $fieldFocus     = 'descricao';
    private $keyField       = 'id';
    private $formTitle      = 'Cadastro de Cliente';
    private $right_panel;
    use FormTrait;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param, $right_panel = false )
    {
        parent::__construct();
        // parent::setProperty('override', 'true');

        $this->right_panel = $right_panel; // indica se o formulario está embutido noutra coisa.
                               // Se estiver, alguns botoes e funcionalidades ficam limitados.
        if ($this->right_panel) {
            parent::setTargetContainer('adianti_right_panel');
        }

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Cliente');
        $this->form->setFormTitle(new TLabel($this->formTitle, '#000000', 14, 'b'));

        $this->form->setFieldSizes('100%');

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $data_nascimento = new TDate('data_nascimento');
        $sexo_id = new TDBRadioGroup('sexo_id', $this->database, 'Sexo', 'id', 'descricao');

        $historico = new BPageContainer();

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $data_nascimento->setSize('100%');
        $sexo_id->setSize('100%');

        $historico->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }

        $nome->forceUpperCase();

        $data_nascimento->setMask('dd/mm/yyyy');
        $data_nascimento->setDatabaseMask('yyyy-mm-dd');

        $sexo_id->setLayout('horizontal');
        $sexo_id->setUseButton();

        $historico->setAction(new TAction(['HistoricoList', 'onShow']));
        $historico->setId('b645e36f5cc3c4');
        $historico->hide();

        $nome->addValidation( '<b>Nome</b>', new TRequiredValidator );
        $data_nascimento->addValidation( '<b>Data de nascimento</b>', new TRequiredValidator );
        $sexo_id->addValidation( '<b>Sexo</b>', new TRequiredValidator );
        
        $loadingContainer = new TElement('div');
        $loadingContainer->style = 'text-align:center; padding:50px';

        $icon = new TElement('i');
        $icon->class = 'fas fa-spinner fa-spin fa-3x';

        $loadingContainer->add($icon);
        $loadingContainer->add('<br>Carregando');

        $historico->add($loadingContainer);

        $this->historico = $historico;

        $this->form->appendPage('Dados gerais');

        // add the fields
        $this->form->addFields( [ new TLabel('Id'), $id ] );
        $this->form->addFields( [ new TLabel('Nome'), $nome ] );
        $this->form->addFields( [ new TLabel('Data de nascimento'), $data_nascimento ] );
        $this->form->addFields( [ new TLabel('Sexo'), $sexo_id ] );
        
        $this->form->appendPage("Históricos");
        $row = $this->form->addFields([$historico]);
        $row->layout = ['col-sm-12'];

        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:check');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus green');
        if ($this->right_panel) {
            $this->form->addHeaderActionLink('Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        } else {
            $this->form->addActionLink('Listagem',  new TAction([$this->listView, 'onReload']), 'fa:list');
        }
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        if (!$this->right_panel) {
            $container->add(new TXMLBreadCrumb('menu.xml', $this->listView));
        }
        $container->add($this->form);
        
        parent::add($container);
    }
    
    public static function onClose()
    {
        TScript::create("Template.closeRightPanel()");
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open($this->database); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new $this->activeRecord;;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->{$this->keyField};
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction

            TToast::show('success', AdiantiCoreTranslator::translate('Record saved'), 'top right', 'far:check-circle' );

            if ($this->right_panel) {
                self::onClose();
                TApplication::loadPage($this->listView, 'onReload', $param);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open($this->database); // open a transaction
                $object = new ($this->activeRecord)($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                
                $this->historico->unhide();
                $this->historico->setParameter('tablename', $this->activeRecord::TABLENAME);
                $this->historico->setParameter('key', $object->{$this->keyField});

                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
