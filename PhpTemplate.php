<?php // $Id$

// vim: expandtab sw=4 ts=4 sts=4:

/**
 * PHP Template
 *
 * PHP-based templating system.
 *
 * @version     1.0
 * @copyright   (c) 2001-2017, Universite catholique de Louvain (UCL)
 * @author      Claroline Team <info@claroline.net>
 * @author      Frederic Minne <zefredz@claroline.net>
 * @license     http://www.gnu.org/copyleft/gpl.html
 *              GNU GENERAL PUBLIC LICENSE version 3 or later
 * @package     display
 */
 
/**
 * @return bool, true if the platform is in debug mode, false else
 */
function phptemplate_debug_mode()
{
    return ( defined ( 'phptemplate_DEBUG_MODE' ) && phptemplate_DEBUG_MODE );
}

/**
 * Exception handler to be used inside an output buffer
 * @param Exception $e
 */
function phptemplate_ob_exception_handler( $e )
{
    // get buffer contents
    $buffer = ob_get_contents();
    // close the output buffer
    ob_end_clean();
    // display the buffer contents
    echo $buffer;
    // display the exception
    if ( phptemplate_debug_mode() )
    {
        echo '<pre>' . $e->__toString() . '</pre>';
    }
    else
    {
        echo '<p>' . $e->getMessage() . '</p>';
    }
}

/**
 * Start output buffering
 */
function phptemplate_ob_start()
{
    // set error handlers for output buffering :
    set_error_handler('phptemplate_exception_error_handler', error_reporting() & ~E_STRICT);
    set_exception_handler('phptemplate_ob_exception_handler');
    // start output buffering
    ob_start();
}

/**
 * Stop output buffering
 */
function phptemplate_ob_end_clean()
{
    // end output buffering
    ob_end_clean();
    // restore original error handlers
    restore_exception_handler();
    restore_error_handler();
}
/**
 * Return buffer contents
 */
function phptemplate_ob_get_contents()
{
    return ob_get_contents();
}

/**
 * Class to convert a PHP error to an Exception
 *
 * taken from php.net online PHP manual
 */
class PHPErrorException extends Exception
{
   public function __construct ( $code, $message, $file, $line )
   {
       parent::__construct($message, $code);
       $this->file = $file;
       $this->line = $line;
   }
}
/**
 * Error handler to convert PHP errors to Exceptions and so have
 * only one error handling system to handle
 *
 * taken from php.net online PHP manual
 */
function phptemplate_exception_error_handler( $code, $message, $file, $line )
{
    throw new PHPErrorException( $code, $message, $file, $line );
}

/**
 * Simple PHP-based template class
 */
class PhpTemplate implements Display
{
    protected $_templatePath;
    
    /**
     * Constructor
     * @param   string $templatePath path to the php template file
     */
    public function __construct( $templatePath )
    {
        $this->_templatePath = $templatePath;
    }
    
    /**
     * Assign a value to a variable of the template
     * @param   string $name
     * @param   mixed $value
     */
    public function assign( $name, $value )
    {
        $this->$name = $value;
    }
    
    /**
     * Render the template
     * @return  string
     * @throws  Exception if file not found or error/exception in the template
     */
    public function render()
    {
        if ( file_exists( $this->_templatePath ) )
        {
            $claroline = Claroline::getInstance();
            phptemplate_ob_start();
            include $this->_templatePath;
            $render = phptemplate_ob_get_contents();
            phptemplate_ob_end_clean();
            
            return $render;
        }
        else
        {
            throw new Exception("Template file not found {$this->_templatePath}");
        }
    }
    
    /**
     * Show a block in the template given its name
     * (ie set the variable with the block name to true)
     * @param   string $blockName
     */
    public function showBlock( $blockName )
    {
        $this->$blockName = true;
    }
    
    /**
     * Hide a block in the template given its name
     * (ie set the variable with the block name to false)
     * @param   string $blockName
     */
    public function hideBlock( $blockName )
    {
        $this->$blockName = false;
    }
}
