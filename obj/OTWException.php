<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Exception
 *
 * @author dthomas
 */
class OTWException extends Exception
{
    public function __construct($message, $code=NULL)    {
        parent::__construct($message, $code);
    }

    public function __toString(){
	    if (isProductionServer())
		    return appname.' cannot display this page due to an internal error. <a href="<?php echo jirahome ?>/secure/CreateIssue!default.jspa" target="_blank">Report an issue in <?php echo appname?></a><br>';

		else return "Code: " . $this->getCode() . "<br />Message: " . htmlentities($this->getMessage());
    }

    public function getException()    {
        print $this; // This will print the return from the above method __toString()
    }

	public static function getStaticException($exception)    {
		if ($exception instanceof OTWException)
			$exception->getException(); // $exception is an instance of this class
	}
}

set_exception_handler(array("OTWException", "getStaticException"));

?>
