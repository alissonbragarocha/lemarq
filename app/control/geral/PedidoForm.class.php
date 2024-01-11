<?php
/**
 * PedidoForm Form
 * @author  <your name here>
 */
class PedidoForm extends TPage
{
    protected $form; // form
    protected $datagrid;
    protected $produtos;

    private $database       = 'lemarq';
    private $activeRecord   = 'Pedido';
    private $listView       = 'PedidoList';
    private $fieldFocus     = 'descricao';
    private $keyField       = 'id';
    private $formTitle      = 'Cadastro de Pedido';
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
        $this->form = new BootstrapFormBuilder('form_Pedido');
        $this->form->setFormTitle(new TLabel($this->formTitle, '#000000', 14, 'b'));
        
        // create the form fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', $this->database, 'Cliente', 'id', 'nome');
        $usuario_cadastro_id = new TDBUniqueSearch('usuario_cadastro_id', 'permission', 'SystemUser', 'id', 'name');
        $data_cadastro = new TDateTime('data_cadastro');
            
        $button = new TActionLink('', new TAction(['ClienteList', 'onReload'], array_merge($param, ['adianti_open_tab'=>1, 'adianti_tab_name'=>'Cliente'])), 'green', null, null, 'fa:plus-circle');
        $button->class = 'btn btn-default inline-button';
        $button->title = _t('New');
        $cliente_id->after($button);

        $produtos = new BPageContainer();

        // set sizes
        $id->setSize('100%');
        $cliente_id->setSize('calc(100% - 30px)');
        $usuario_cadastro_id->setSize('100%');
        $data_cadastro->setSize('100%');
        $produtos->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
            $usuario_cadastro_id->setEditable(FALSE);
            $data_cadastro->setEditable(FALSE);
        }

        $produtos->setAction(new TAction(['PedidoProdutoList', 'onShow']));
        $produtos->setId('pedido_produtos');
        $produtos->hide();

        $cliente_id->addValidation( '<b>Descrição</b>', new TRequiredValidator );
        
        $loadingContainer = new TElement('div');
        $loadingContainer->style = 'text-align:center; padding:50px';

        $icon = new TElement('i');
        $icon->class = 'fas fa-spinner fa-spin fa-3x';

        $loadingContainer->add($icon);
        $loadingContainer->add('<br>Carregando');

        $produtos->add($loadingContainer);

        $this->produtos = $produtos;

        $this->form->appendPage('Dados gerais');

        // add the fields
        $this->form->addFields( [ new TLabel('Id'), $id ] );
        $this->form->addFields( [ new TLabel('Cliente'), $cliente_id ] );
        $this->form->addFields( [ new TLabel('Usuário de cadastro'), $usuario_cadastro_id ] );
        $this->form->addFields( [ new TLabel('Data de cadastro'), $data_cadastro ] );
        
        $this->form->appendPage("Produtos");
        $row = $this->form->addFields([$produtos]);
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

            if (empty($object->id))
                $object->usuario_cadastro_id = TSession::getValue('userid');

            unset($object->data_cadastro);

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
                
                $this->produtos->unhide();
                $this->produtos->setParameter('key', $object->{$this->keyField});

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
