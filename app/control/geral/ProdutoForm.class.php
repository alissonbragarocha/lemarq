<?php
/**
 * ProdutoForm Form
 * @author  <your name here>
 */
class ProdutoForm extends TPage
{
    protected $form; // form
    protected $datagrid;
    protected $historico;

    private $database       = 'lemarq';
    private $activeRecord   = 'Produto';
    private $listView       = 'ProdutoList';
    private $fieldFocus     = 'descricao';
    private $keyField       = 'id';
    private $formTitle      = 'Cadastro de Produto';
    private $embed;

    use FormTrait;
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param, $embed = false )
    {
        parent::__construct();
        parent::setProperty('override', 'true');

        $this->embed = $embed; // indica se o formulario está embutido noutra coisa.
                                     // Se estiver, alguns botoes e funcionalidades ficam limitados.
        if (!$this->embed) {
            parent::setTargetContainer('adianti_right_panel');
        }

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Produto');
        
        if (!$this->embed) {
            $this->form->setFormTitle(new TLabel($this->formTitle, '#000000', 14, 'b'));
        }
        $this->form->setFieldSizes('100%');

        // create the form fields
        $id = new TEntry('id');
        $descricao = new TEntry('descricao');
        $valor_compra = new TNumeric('valor_compra', 2, ',', '.', true);
        $valor_venda = new TNumeric('valor_venda', 2, ',', '.', true);
        $historico = new BPageContainer();

        // set sizes
        $id->setSize('100%');
        $descricao->setSize('100%');
        $valor_compra->setSize('100%');
        $valor_venda->setSize('100%');
        $historico->setSize('100%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }

        $historico->setAction(new TAction(['ProdutoHistoricoList', 'onShow']));
        $historico->setId('b645e36f5cc3c4');
        $historico->hide();

        $descricao->addValidation( '<b>Descrição</b>', new TRequiredValidator );
        $valor_compra->addValidation( '<b>Valor de compra</b>', new TRequiredValidator );
        $valor_venda->addValidation( '<b>Valor de venda</b>', new TRequiredValidator );
        
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
        $this->form->addFields( [ new TLabel('Descrição'), $descricao ] );
        $this->form->addFields( [ new TLabel('Valor de compra'), $valor_compra ] );
        $this->form->addFields( [ new TLabel('Valor de venda'), $valor_venda ] );
        
        $this->form->appendPage("Históricos");
        $row = $this->form->addFields([$historico]);
        $row->layout = ['col-sm-12'];

        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:check');
        $btn->class = 'btn btn-sm btn-primary';
        if (!$this->embed) {
            $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:plus green');
            $this->form->addHeaderActionLink('Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        }
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
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

            if (!$this->embed) {
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
