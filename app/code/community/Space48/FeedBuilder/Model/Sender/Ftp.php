<?php

class Space48_FeedBuilder_Model_Sender_Ftp extends  Space48_FeedBuilder_Model_Sender_Abstract
{

    protected $_mandatoryArgs = array('host','port','user','password','local_filename');

    public function __construct()
    {
        $args = func_get_args();
        if ($args) {
           $args = $args[0];
        } else {
            $args = array();
        }

        parent::__construct($args);

        foreach ($this->_mandatoryArgs as $arg) {
            if (!$this->getData($arg)) {
                Mage::throwException('Missing Argument ' . $arg . ' in ' . get_class($this));
            }
        }
    }


    public function send()
    {
        $ftpDetails = $this->_getFtpDetails();
        $sendByFtp   = new Varien_Io_Ftp();

        if ($sendByFtp->open($ftpDetails)==true) {

            $file = Mage::getBaseDir() . DS . $this->getData('local_filename');

            $writeResult = $sendByFtp->write($this->_getFtpPath() . $this->_getUploadName(), $file);

            $sendByFtp->close();

            if ($writeResult==false) {
                Mage::throwException('Cannot Write to FTP in ' . get_class($this));
            }
            return;
        }
        Mage::throwException('Cannot Open FTP in ' . get_class($this));
    }

    protected function _getFtpDetails()
    {
        $ftpDetails = array(
                'host'     => $this->getData('host'),
                'port'     => $this->getData('port'),
                'user'     => $this->getData('user'),
                'password' => $this->getData('password'),
                'path'     => $this->_getFtpPath()
        );
        $ftpDetails['file_mode'] = $this->_getFtpMode();
        return $ftpDetails;
    }

    protected function _getFtpMode() {
        if ($this->getData('mode')) {
            if ($this->getData('mode')=='text') {
                return FTP_ASCII;
            }
        }
        return FTP_BINARY;
    }

    protected function _getFtpPath()
    {
        if ($this->getData('target_path')) {
            return '' . rtrim($this->getData('target_path'),'/');
        }
        return '';
    }

    protected function _getUploadName()
    {
        $local_name = array_pop(explode('/', rtrim($this->getData('local_filename'), '/')));

        if ($this->getData('append_upload_date_before_ftp_file_name')) {
            return date('Y-m-d-H') . '-' . $local_name;
        }

        return $local_name;
    }
}